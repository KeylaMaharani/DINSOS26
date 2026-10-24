{{-- Satu baris isian pada form Ground Check: label di kiri, input di kanan (mengikuti tampilan SOLID).
     Parameter bersumber master (lapangan_usaha | pekerjaan | keterampilan) memakai _field_master.
     Semua isian pilihan berbentuk dropdown (pilihan ganda = dropdown multi-pilih, tanpa checkbox).
     Isian foto HANYA bisa lewat kamera langsung (tidak ada pilihan galeri). --}}
@php
    use Illuminate\Support\Str;

    $id = $p->id;
    $val = old("jawaban.{$id}", $nilai[$id] ?? null);
    $tipe = $p->tipe_input;
    // Semua isian wajib (tanda * merah + border merah saat kosong),
    // kecuali pilihan ganda yang belum punya opsi (tidak ada yang bisa dipilih).
    $wajib = $tipe !== 'pilihan_ganda' || $p->opsis->isNotEmpty();
    $namaLower = Str::lower($p->nama);
    $lat = $tipe === 'angka' && Str::contains($namaLower, 'lintang');
    $lng = $tipe === 'angka' && Str::contains($namaLower, 'bujur');
    $angkaKoordinat = $lat || $lng;

    if ($tipe === 'pilihan_ganda' && is_string($val)) {
        $val = json_decode($val, true) ?: [];
    }
    $val = $tipe === 'pilihan_ganda' ? array_values(array_filter((array) $val, fn ($x) => is_string($x) && $x !== '')) : $val;

    $pesan = collect($errors->getMessages())
        ->filter(fn ($m, $k) => $k === "jawaban.{$id}" || str_starts_with($k, "jawaban.{$id}."))
        ->flatten()->first();

    $input = 'w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-white';
@endphp

{{-- Pilihan ganda memakai penanda tersembunyi (lihat _multi_dropdown), jadi dihitung sebagai isian 'pilihan' --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 sm:gap-6 py-3 px-2 border-b border-outline-variant/30 items-start rounded-lg"
     @if ($wajib) data-wajib="{{ $tipe === 'pilihan_ganda' ? 'pilihan' : $tipe }}" @endif
     @if ($tipe === 'foto' && ! empty($nilai[$id])) data-ada="1" @endif>

    <label class="text-xs font-medium text-on-surface pt-2">
        {{ $p->nama }} @if ($wajib)<span class="text-error">*</span>@endif
    </label>

    <div>
        @switch($tipe)
            @case('pilihan')
                @if ($p->opsis->isNotEmpty())
                    <select name="jawaban[{{ $id }}]" class="{{ $input }}">
                        <option value="">-- Pilih --</option>
                        @foreach ($p->opsis as $o)
                            <option value="{{ $o->label }}" @selected((string) $val === $o->label)>{{ $o->label }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="jawaban[{{ $id }}]" value="{{ $val }}" class="{{ $input }}" />
                @endif
                @break

            @case('pilihan_ganda')
                @php
                    $opsiMulti = $p->opsis->map(fn ($o) => [
                        'nama' => $o->label,
                        'eksl' => in_array(Str::lower(trim($o->label)), ['tidak ada', 'belum ada'], true),
                    ])->values();
                @endphp
                @include('admin.ground-check._multi_dropdown', ['nm' => "jawaban[{$id}]", 'opsiMulti' => $opsiMulti, 'dipilih' => $val])
                @break

            @case('angka')
                <input type="number" step="{{ $angkaKoordinat ? 'any' : '1' }}" @if (! $angkaKoordinat) min="0" @endif
                       name="jawaban[{{ $id }}]" value="{{ $val }}"
                       @if ($lat) data-gc-lat @endif @if ($lng) data-gc-lng @endif
                       class="{{ $input }}" />
                @break

            @case('tanggal')
                <input type="date" name="jawaban[{{ $id }}]" value="{{ $val }}" class="{{ $input }}" />
                @break

            @case('koordinat')
                <div class="space-y-2">
                    <div id="gc-map" class="w-full h-64 rounded-lg border border-outline-variant/50 z-0"></div>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly data-gc-koordinat name="jawaban[{{ $id }}]" value="{{ $val }}"
                               placeholder="Klik peta untuk menandai lokasi" class="{{ $input }} bg-surface-container-low" />
                        <button type="button" id="gc-lokasi-saya"
                                class="shrink-0 inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-xs font-medium transition-colors">
                            <span class="material-symbols-outlined text-[16px]">my_location</span> Lokasi saya
                        </button>
                    </div>
                </div>
                @break

            @case('foto')
                <div>
                    {{-- Input disembunyikan & diisi lewat kamera (tanpa galeri).
                         capture = cadangan khusus HP bila getUserMedia tidak tersedia. --}}
                    <input type="file" name="jawaban[{{ $id }}]" accept="image/*" capture="environment"
                           class="hidden" data-gc-file onchange="gcPreview(this)" />
                    <button type="button" onclick="gcBukaKamera(this)"
                            class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-xs font-medium transition-colors">
                        <span class="material-symbols-outlined text-[16px]">photo_camera</span> Ambil Foto
                    </button>
                    <img @if (! empty($nilai[$id])) src="{{ asset('storage/' . $nilai[$id]) }}" @else hidden @endif
                         alt="Pratinjau {{ $p->nama }}" class="gc-preview mt-2 h-32 rounded-lg border border-outline-variant/50 object-cover" />
                </div>
                @break

            @default
                <input type="text" name="jawaban[{{ $id }}]" value="{{ $val }}" class="{{ $input }}" />
        @endswitch

        @if ($pesan)
            <p class="text-xs text-error mt-1">{{ $pesan }}</p>
        @endif
    </div>
</div>

{{-- Kamera realtime: dimuat sekali saja walau partial ini dipanggil berkali-kali --}}
@once
@push('scripts')
<script>
(function () {
    let stream = null, targetInput = null, modal = null, video = null;

    function buatModal() {
        modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[9999] hidden items-center justify-center bg-black/80 p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-xl overflow-hidden w-full max-w-lg">
                <video autoplay playsinline muted class="w-full bg-black max-h-[70vh] object-contain"></video>
                <div class="flex justify-between gap-2 p-3">
                    <button type="button" data-batal class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</button>
                    <button type="button" data-ambil class="px-5 py-2 rounded-lg text-sm bg-primary text-on-primary font-semibold">Ambil Foto</button>
                </div>
            </div>`;
        document.body.appendChild(modal);
        video = modal.querySelector('video');
        modal.querySelector('[data-batal]').onclick = tutup;
        modal.querySelector('[data-ambil]').onclick = jepret;
    }

    function tutup() {
        if (stream) { stream.getTracks().forEach((t) => t.stop()); stream = null; }
        if (video) video.srcObject = null;
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function jepret() {
        if (!video || !video.videoWidth) return;
        const c = document.createElement('canvas');
        c.width = video.videoWidth;
        c.height = video.videoHeight;
        c.getContext('2d').drawImage(video, 0, 0);
        c.toBlob((blob) => {
            if (!blob) { alert('Gagal mengambil foto. Coba lagi.'); return; }
            const file = new File([blob], 'kamera_' + Date.now() + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
            const dt = new DataTransfer();
            dt.items.add(file);
            targetInput.files = dt.files;
            // memicu pratinjau (onchange) + menghapus border merah wizard
            targetInput.dispatchEvent(new Event('change', { bubbles: true }));
            tutup();
        }, 'image/jpeg', 0.85);
    }

    window.gcBukaKamera = async function (btn) {
        targetInput = btn.parentElement.querySelector('[data-gc-file]');

        // getUserMedia hanya tersedia di HTTPS / localhost.
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            // Cadangan khusus HP: atribut capture langsung membuka kamera.
            if (navigator.maxTouchPoints > 0) {
                targetInput.click();
            } else {
                alert('Kamera hanya dapat dipakai lewat koneksi HTTPS. Hubungi admin sistem.');
            }
            return;
        }

        if (!modal) buatModal();

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });
        } catch (e) {
            alert('Kamera tidak dapat diakses. Izinkan akses kamera pada peramban lalu coba lagi.');
            return;
        }

        video.srcObject = stream;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        try { await video.play(); } catch (e) { /* autoplay sudah diatur di elemen */ }
    };
})();
</script>
@endpush
@endonce