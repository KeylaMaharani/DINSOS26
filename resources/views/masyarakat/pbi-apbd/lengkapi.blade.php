@extends('layouts.masyarakat')

@section('title', 'Lengkapi Data - SOLID Dinas Sosial Kota Bogor')

@section('content')
    <a href="{{ route('masyarakat.dashboard') }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant mb-3 sm:mb-4 py-1">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali
    </a>

    <h1 class="text-lg sm:text-xl font-bold text-primary mb-1">Lengkapi Data Pengajuan</h1>
    <p class="text-sm text-on-surface-variant mb-5 sm:mb-6 break-words">No. Registrasi: <strong>{{ $pbiApbd->no_registrasi }}</strong></p>

    @if ($pbiApbd->dikembalikan_ke_masyarakat)
        <div class="mb-4 px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm">
            <p class="font-semibold">Petugas kelurahan meminta Anda memperbaiki data.</p>
            @if ($pbiApbd->catatan_kelurahan)<p class="mt-1 break-words">Catatan: {{ $pbiApbd->catatan_kelurahan }}</p>@endif
        </div>
    @endif

    @if ($pbiApbd->status === 'ditolak' && $bisaEdit)
        <div class="mb-4 px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm">
            <p class="font-semibold">Pengajuan Anda ditolak.</p>
            <p class="mt-1">Anda dapat memperbaiki data lalu menyimpannya kembali untuk diajukan ulang.</p>
            @if ($pbiApbd->catatan_kelurahan)<p class="mt-1 break-words">Catatan: {{ $pbiApbd->catatan_kelurahan }}</p>@endif
        </div>
    @endif

    @unless ($bisaEdit)
        <div class="mb-4 px-4 py-3 rounded-lg bg-success-container text-on-success-container text-sm flex items-start gap-2">
            <span class="material-symbols-outlined text-[18px] shrink-0 mt-px">lock</span>
            <p>Pengajuan sudah disetujui, sehingga data tidak dapat diubah lagi.</p>
        </div>
    @endunless

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <p class="font-semibold mb-1">Periksa kembali data Anda:</p>
            <ul class="list-disc list-inside space-y-0.5 break-words">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- pb-28 di mobile: ruang kosong supaya konten terakhir tidak tertutup tombol Simpan yang fixed --}}
    <form method="POST" action="{{ route('masyarakat.pbi-apbd.lengkapi.store', $pbiApbd) }}"
          class="space-y-4 sm:space-y-6 pb-28 sm:pb-0" id="form-lengkapi">
        @csrf

        {{-- ===== KOORDINAT ===== --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 sm:p-5">
            <h2 class="font-semibold text-sm mb-1 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px] text-secondary">location_on</span>
                Titik Lokasi Rumah
            </h2>
            <p class="text-xs text-on-surface-variant mb-3">
                Pastikan Anda berada di rumah/lokasi yang didaftarkan, lalu izinkan akses lokasi di browser.
            </p>

            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $pbiApbd->latitude) }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $pbiApbd->longitude) }}">

            @if ($bisaEdit)
                <div id="geo-status" class="flex items-start gap-2 text-sm p-3 rounded-lg bg-surface-container">
                    <span class="material-symbols-outlined text-[18px] shrink-0 mt-px animate-spin" id="geo-icon">progress_activity</span>
                    <span id="geo-text" class="min-w-0 break-words">Mengambil lokasi Anda...</span>
                </div>

                <button type="button" id="btn-retry-geo"
                        class="hidden mt-3 w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-full border border-primary text-primary text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">refresh</span>
                    Coba ambil ulang lokasi
                </button>
            @else
                <div class="flex items-start gap-2 text-sm p-3 rounded-lg bg-surface-container">
                    <span class="material-symbols-outlined text-[18px] shrink-0 mt-px">location_on</span>
                    <span class="min-w-0 break-words">
                        @if ($pbiApbd->latitude && $pbiApbd->longitude)
                            Lokasi tersimpan ({{ $pbiApbd->latitude }}, {{ $pbiApbd->longitude }})
                        @else
                            Lokasi belum tersimpan.
                        @endif
                    </span>
                </div>
            @endif
        </div>

        {{-- ===== PERTANYAAN KONDISI RUMAH TANGGA ===== --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 sm:p-5">
            <h2 class="font-semibold text-sm mb-1 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px] text-secondary">home</span>
                Kondisi Rumah Tangga
            </h2>
            <p class="text-xs text-on-surface-variant mb-4">Jawab sesuai keadaan sebenarnya. Petugas kelurahan akan mengecek langsung ke lapangan.</p>

            @if ($parameters->isEmpty())
                <p class="text-sm text-on-surface-variant">Pertanyaan belum tersedia. Silakan hubungi petugas.</p>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-5">
                @foreach ($parameters as $p)
                    @php $sel = old('jawaban.' . $p->id, $jawabanTersimpan[$p->id] ?? ''); @endphp
                    <div class="min-w-0">
                        <label for="jawaban-{{ $p->id }}" class="block text-xs font-semibold mb-1">{{ $loop->iteration }}. {{ $p->nama }}</label>
                        <select id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" required
                                @disabled(! $bisaEdit)
                                class="w-full rounded-lg border border-outline-variant/50 px-3 py-2.5 text-sm bg-white disabled:bg-surface-container disabled:text-on-surface-variant">
                            <option value="">- Pilih -</option>
                            @foreach ($p->opsis as $o)
                                <option value="{{ $o->id }}" @selected((string) $sel === (string) $o->id)>{{ $o->label }}</option>
                            @endforeach
                        </select>
                        @error('jawaban.' . $p->id)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Mobile: bar fixed selebar layar, latar solid + bayangan. Desktop: tombol biasa. --}}
        @if ($bisaEdit)
            <div class="fixed inset-x-0 bottom-0 z-30 px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]
                        bg-surface border-t border-outline-variant/40 shadow-[0_-4px_12px_rgba(0,0,0,0.08)]
                        sm:static sm:inset-auto sm:z-auto sm:p-0 sm:bg-transparent sm:border-0 sm:shadow-none">
                <button type="submit" id="btn-submit" disabled
                        class="w-full sm:w-auto sm:px-8 flex items-center justify-center gap-2 py-3 rounded-full bg-primary text-white font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    {{ $pbiApbd->status === 'ditolak' ? 'Simpan & Ajukan Ulang' : 'Simpan Data' }}
                </button>
            </div>
        @endif
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');
    var statusText = document.getElementById('geo-text');
    var statusIcon = document.getElementById('geo-icon');
    var statusBox = document.getElementById('geo-status');
    var btnRetry = document.getElementById('btn-retry-geo');
    var btnSubmit = document.getElementById('btn-submit');

    // Mode baca saja (sudah disetujui): tidak ada tombol simpan / pengambilan lokasi
    if (!btnSubmit) { return; }

    function setStatus(text, icon, spin, state) {
        statusText.textContent = text;
        statusIcon.textContent = icon;
        statusIcon.classList.toggle('animate-spin', !!spin);

        // warna kotak status: normal / sukses / gagal
        statusBox.classList.remove('bg-surface-container', 'bg-error-container', 'text-on-error-container', 'bg-secondary-container');
        if (state === 'error') {
            statusBox.classList.add('bg-error-container', 'text-on-error-container');
        } else {
            statusBox.classList.add('bg-surface-container');
        }
    }

    function ambilLokasi() {
        btnRetry.classList.add('hidden');

        if (!navigator.geolocation) {
            setStatus('Browser Anda tidak mendukung pengambilan lokasi otomatis.', 'error', false, 'error');
            btnRetry.classList.remove('hidden');
            return;
        }

        setStatus('Mengambil lokasi Anda...', 'progress_activity', true);

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                latInput.value = pos.coords.latitude;
                lngInput.value = pos.coords.longitude;
                setStatus('Lokasi berhasil diambil (' + pos.coords.latitude.toFixed(5) + ', ' + pos.coords.longitude.toFixed(5) + ')', 'check_circle', false);
                btnSubmit.disabled = false;
            },
            function (err) {
                var msg = 'Gagal mengambil lokasi. Coba lagi.';
                if (!window.isSecureContext) {
                    msg = 'Lokasi hanya bisa diambil lewat koneksi aman (HTTPS). Hubungi petugas.';
                } else if (err.code === 1) {
                    msg = 'Izin lokasi ditolak. Aktifkan izin lokasi untuk browser ini di Pengaturan, lalu coba lagi.';
                } else if (err.code === 2) {
                    msg = 'Lokasi tidak tersedia. Pastikan GPS/Location Services aktif.';
                } else if (err.code === 3) {
                    msg = 'Waktu pengambilan lokasi habis. Coba lagi di tempat yang sinyalnya lebih baik.';
                }
                setStatus(msg, 'error', false, 'error');
                btnRetry.classList.remove('hidden');
                if (latInput.value && lngInput.value) { btnSubmit.disabled = false; }
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }

    btnRetry.addEventListener('click', ambilLokasi);
    if (latInput.value && lngInput.value) { btnSubmit.disabled = false; }
    ambilLokasi();
})();
</script>
@endpush
