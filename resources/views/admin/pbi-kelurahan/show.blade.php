@extends('layouts.admin')

@section('title', 'Verifikasi Kelurahan')
@section('page_title', 'PBI APBD — Verifikasi Kelurahan')

@php
    $bisaProses = $data->status === 'kelurahan';
    // Isian masyarakat = jawaban Ground Check yang berlaku untuk PBI (target pbi + pbi_gc), sama dengan hitungan kelengkapan di controller
    $jawabans = $gcJawabans->sortBy('urutan')->values();
    $dataLengkap = $totalParameter > 0 && $terjawab >= $totalParameter;
    // Jawaban berupa foto ditampilkan terpisah sebagai kartu lampiran (urutan sama dengan daftar isian)
    // Tanda "Verifikasi Data" oleh petugas kelurahan (disimpan di kolom verifikasi_berkas); wajib sebelum diteruskan ke Lurah
    $verifData = $data->verifikasi_berkas['data_pemohon'] ?? null;
    $fotos = $jawabans->filter(fn ($j) => $j->tipe_input === 'foto' && filled($j->jawaban))->values();
@endphp

@section('content')
<div class="space-y-5 max-w-5xl">

    <a href="{{ route('pbi-kelurahan.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar
    </a>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-sm">
        <div>
            <p class="text-xs text-on-surface-variant uppercase tracking-wider font-medium">No. Registrasi</p>
            <h2 class="text-xl font-bold text-primary mt-0.5">{{ $data->no_registrasi }}</h2>
            <p class="text-sm text-on-surface-variant mt-1">{{ $data->pemohonLabel() }} — No KK {{ $data->no_kk }}</p>
        </div>
        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-secondary-fixed text-on-secondary-container">{{ $data->statusLabel() }}</span>
    </div>

    @if ($bisaProses && $data->dikembalikan_ke_masyarakat)
        <div class="px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm">
            <strong>Sudah dikembalikan ke masyarakat</strong> dan menunggu perbaikan data.
            @if ($data->catatan_kelurahan)<div class="mt-1">Catatan: {{ $data->catatan_kelurahan }}</div>@endif
        </div>
    @endif

    {{-- Data permohonan --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40"><h3 class="text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">assignment</span> Data Pemohon</h3></div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
            @foreach ([
                'No KK' => $data->no_kk, 'Nama Kepala Keluarga' => $data->nama_kepala_keluarga,
                'NIK Kepala Keluarga' => $data->nik_kepala_keluarga, 'Alamat' => $data->alamat,
                'RT/RW' => ($data->rt ?: '-') . ' / ' . ($data->rw ?: '-'), 'Kecamatan' => $data->kecamatan,
                'Desa/Kelurahan' => $data->desa_kelurahan, 'No Telp' => $data->no_telp, 'Email' => $data->email,
            ] as $label => $value)
                <div><dt class="text-xs text-on-surface-variant mb-0.5">{{ $label }}</dt><dd class="font-semibold">{{ $value ?: '-' }}</dd></div>
            @endforeach
            <div>
                <dt class="text-xs text-on-surface-variant mb-0.5">Titik Lokasi (dari masyarakat)</dt>
                <dd class="font-semibold">
                    @if ($data->latitude && $data->longitude)
                        <a class="text-primary underline" target="_blank" rel="noopener"
                           href="https://www.google.com/maps?q={{ $data->latitude }},{{ $data->longitude }}">Buka di Google Maps</a>
                    @else - @endif
                </dd>
            </div>
        </dl>
    </div>

    {{-- Anggota keluarga --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40"><h3 class="text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">group</span> Anggota Keluarga Yang Didaftarkan</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase">
                    <tr><th class="px-5 py-3">NIK</th><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Hubungan</th><th class="px-5 py-3">BPJS</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($data->daftarPeserta() as $a)
                        <tr><td class="px-5 py-3">{{ $a->nik }}</td><td class="px-5 py-3 font-medium">{{ $a->nama }}</td>
                            <td class="px-5 py-3">{{ ucwords(str_replace('_', ' ', (string) $a->hubungan_keluarga)) }}</td><td class="px-5 py-3">{{ $a->keanggotaan_bpjs ?: '-' }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-on-surface-variant">Belum ada peserta yang didaftarkan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Jawaban masyarakat: HANYA pertanyaan & jawaban (tanpa indeks/bobot/skor) --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40 flex items-center justify-between gap-3 flex-wrap">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">checklist</span> Isian Masyarakat (untuk dicek di lapangan)
            </h3>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $dataLengkap ? 'bg-success-container text-on-success-container' : 'bg-warning-container text-on-warning-container' }}">
                {{ $terjawab }}/{{ $totalParameter }} terjawab
            </span>
        </div>
        @if ($jawabans->isEmpty())
            <p class="p-6 text-sm text-on-surface-variant text-center">Masyarakat belum mengisi parameter.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase">
                        <tr><th class="px-5 py-3 w-14 text-center">No</th><th class="px-5 py-3">Parameter</th><th class="px-5 py-3">Jawaban Masyarakat</th></tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        @foreach ($jawabans as $j)
                            @continue($j->tipe_input === 'foto')
                            @php
                                $nilai = $j->jawaban;
                                $pilihan = null;
                                if ($j->tipe_input === 'pilihan_ganda' && filled($nilai)) {
                                    $dec = json_decode($nilai, true);
                                    $pilihan = is_array($dec) ? $dec : [$nilai];
                                }
                            @endphp
                            <tr>
                                <td class="px-5 py-3 text-center">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3">
                                    {{ $j->nama_parameter }}
                                    @if ($j->parameter_id && ! in_array($j->parameter_id, $idAktif, true))
                                        <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-surface-container text-on-surface-variant">Disembunyikan dari warga</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 font-semibold">
                                    @if (blank($nilai))
                                        <span class="font-normal text-on-surface-variant">Belum diisi</span>
                                    @elseif ($pilihan)
                                        {{ implode(', ', $pilihan) }}
                                    @else
                                        {{ $nilai }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Layanan kesehatan & berkas pemohon: kolom pbi_apbds (bukan jawaban Ground Check) --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">local_hospital</span> Layanan Kesehatan & Berkas Pemohon
            </h3>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
            <div class="sm:col-span-2"><dt class="text-xs text-on-surface-variant mb-0.5">Diagnosa</dt><dd class="font-semibold">{{ $data->diagnosa ?: '-' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs text-on-surface-variant mb-0.5">Nama Faskes</dt><dd class="font-semibold">{{ $data->nama_faskes ?: '-' }}</dd></div>
        </dl>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 px-5 pb-5">
            @foreach (['scan_ktp' => 'Scan KTP', 'scan_kk' => 'Scan Kartu Keluarga', 'surat_rawat_inap' => 'Surat Ket. Rawat Inap'] as $field => $label)
                <x-lampiran-preview :src="$data->$field ? asset('storage/' . $data->$field) : null" :label="$label" />
            @endforeach
        </div>
    </div>

    {{-- Verifikasi data: wajib diklik sebelum berkas bisa diteruskan ke Lurah --}}
    @if ($bisaProses)
        <div class="bg-surface-container-lowest border rounded-xl shadow-sm p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4
                    {{ $verifData ? 'border-success/50' : 'border-outline-variant/40' }}">
            <div class="flex items-start gap-3 min-w-0">
                <span class="material-symbols-outlined text-[26px] {{ $verifData ? 'text-success' : 'text-secondary' }}">{{ $verifData ? 'task_alt' : 'fact_check' }}</span>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold">Verifikasi Data Pemohon</h3>
                    @if ($verifData)
                        <p class="text-xs text-success mt-0.5">Data sudah diverifikasi pada {{ \Illuminate\Support\Carbon::parse($verifData)->format('d M Y - H:i') }}. Berkas bisa diteruskan ke Lurah.</p>
                    @else
                        <p class="text-xs text-on-surface-variant mt-0.5">Cek data pemohon, isian, dan lampiran di halaman ini, lalu klik Verifikasi. Sebelum itu, berkas belum bisa diteruskan ke Lurah.</p>
                    @endif
                </div>
            </div>
            @unless ($verifData)
                <form method="POST" action="{{ route('pbi-kelurahan.aksi', $data) }}" class="shrink-0">
                    @csrf
                    <button type="submit" name="aksi" value="verifikasi_data"
                        data-confirm="Pastikan data pemohon, isian, dan lampiran sudah dicek. Tandai data ini sudah diverifikasi?" data-confirm-title="Verifikasi Data"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-bold shadow-md">
                        <span class="material-symbols-outlined text-[18px]">verified</span> Verifikasi
                    </button>
                </form>
            @endunless
        </div>
    @endif

    {{-- Lampiran Persyaratan: foto jawaban masyarakat. Klik kartu -> pilihan Lihat / Unduh --}}
    @if ($fotos->isNotEmpty())
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl shadow-sm">
            <div class="px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-secondary">attach_file</span> Lampiran Persyaratan
                </h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
                @foreach ($fotos as $f)
                    @php
                        $url = asset('storage/' . $f->jawaban);
                        $ext = pathinfo((string) $f->jawaban, PATHINFO_EXTENSION) ?: 'jpg';
                        $namaUnduh = \Illuminate\Support\Str::slug($f->nama_parameter) . '.' . $ext;
                    @endphp
                    <div class="relative min-w-0" x-data="{ buka: false, gagal: false }"
                         @click.outside="buka = false" @keydown.escape.window="buka = false">
                        <button type="button" @click="buka = !buka"
                                class="w-full text-left rounded-xl border border-outline-variant/40 overflow-hidden bg-surface-container-lowest hover:shadow-md transition"
                                :aria-expanded="buka" aria-haspopup="true">
                            <div class="aspect-square bg-[#eef5ff] flex items-center justify-center">
                                <img src="{{ $url }}" alt="{{ $f->nama_parameter }}" loading="lazy"
                                     class="w-full h-full object-cover" x-show="!gagal" x-on:error="gagal = true">
                                <span x-show="gagal" x-cloak class="material-symbols-outlined text-[44px] text-outline">image_not_supported</span>
                            </div>
                            <div class="px-3 py-3 text-center text-sm text-on-surface border-t border-outline-variant/40">
                                {{ $f->nama_parameter }}
                                @if ($f->parameter_id && ! in_array($f->parameter_id, $idAktif, true))
                                    <span class="block mt-0.5 text-[10px] text-on-surface-variant">Disembunyikan dari warga</span>
                                @endif
                            </div>
                        </button>

                        {{-- Pilihan: Lihat / Unduh --}}
                        <div x-show="buka" x-cloak x-transition.opacity
                             class="absolute z-20 left-3 right-3 top-1/2 -translate-y-1/2 rounded-lg border border-outline-variant/40 bg-white shadow-lg overflow-hidden text-sm">
                            <a href="{{ $url }}" target="_blank" rel="noopener" @click="buka = false"
                               class="flex items-center gap-2 px-3 py-2.5 hover:bg-surface-container">
                                <span class="material-symbols-outlined text-[18px] text-secondary">visibility</span> Lihat
                            </a>
                            <a href="{{ $url }}" download="{{ $namaUnduh }}" @click="buka = false"
                               class="flex items-center gap-2 px-3 py-2.5 hover:bg-surface-container border-t border-outline-variant/30">
                                <span class="material-symbols-outlined text-[18px] text-secondary">download</span> Unduh
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($bisaProses)
        {{-- Pengompres & pengambil foto (kamera / file). Foto dikecilkan agar muat batas 4 MB. --}}
        <script>
            window.fotoLapangan = function (hanyaKamera) {
                return {
                    hanyaKamera,
                    nama: null,
                    pratinjau: null,
                    async olah(file) {
                        if (!file || !file.type.startsWith('image/')) return file;
                        try {
                            const bmp = await createImageBitmap(file);
                            const skala = Math.min(1, 1600 / Math.max(bmp.width, bmp.height));
                            const c = document.createElement('canvas');
                            c.width = Math.round(bmp.width * skala);
                            c.height = Math.round(bmp.height * skala);
                            c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);
                            const blob = await new Promise(r => c.toBlob(r, 'image/jpeg', 0.85));
                            if (!blob) return file;
                            return new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' });
                        } catch (e) { return file; }
                    },
                    async terima(event) {
                        const asli = event.target.files[0];
                        event.target.value = '';
                        if (!asli) return;
                        const f = await this.olah(asli);
                        if (f.size > 4 * 1024 * 1024) { alert('Ukuran file maksimal 4 MB.'); return; }
                        const dt = new DataTransfer();
                        dt.items.add(f);
                        this.$refs.utama.files = dt.files;
                        this.nama = f.name;
                        if (this.pratinjau) URL.revokeObjectURL(this.pratinjau);
                        this.pratinjau = f.type.startsWith('image/') ? URL.createObjectURL(f) : null;
                    },
                };
            };
        </script>

        <form method="POST" action="{{ route('pbi-kelurahan.aksi', $data) }}" enctype="multipart/form-data" class="space-y-5 mb-10">
            @csrf

            @if ($errors->any())
                <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
                    <p class="font-semibold mb-1">Periksa kembali:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Lampiran hasil turun lapangan (diisi Kelurahan) --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">attach_file</span> Lampiran Hasil Verifikasi Lapangan</h3>
                    <p class="text-xs text-on-surface-variant mt-1">Bertanda <span class="text-error">*</span> wajib ada sebelum diverifikasi. Maks. 4 MB (foto dikecilkan otomatis). Di ponsel, tombol kamera langsung membuka kamera.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5">
                    @foreach ($lampiranKelurahan as $field => [$label, $wajib, $mimes, $hanyaKamera])
                        @php $adaKamera = $field !== 'screenshot_dtsen'; @endphp
                        <div class="min-w-0" x-data="fotoLapangan({{ $hanyaKamera ? 'true' : 'false' }})">
                            <p class="block text-xs font-semibold mb-1">{{ $label }} @if($wajib)<span class="text-error">*</span>@endif</p>

                            @if ($data->{$field})
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-24 shrink-0"><x-lampiran-preview :src="asset('storage/' . $data->{$field})" :label="$label" /></div>
                                    <span class="flex items-center gap-1 text-xs text-success"><span class="material-symbols-outlined text-[15px]">check_circle</span> Sudah diunggah</span>
                                </div>
                            @endif

                            {{-- Input yang dikirim ke server (diisi lewat kamera / pilih file) --}}
                            <input type="file" name="{{ $field }}" x-ref="utama" class="hidden" tabindex="-1">

                            <div class="flex flex-wrap gap-2">
                                @if ($adaKamera)
                                    <input type="file" accept="image/*" capture="{{ $hanyaKamera ? 'user' : 'environment' }}" x-ref="kamera" class="hidden" @change="terima($event)">
                                    <button type="button" @click="$refs.kamera.click()"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-primary text-on-primary text-xs font-semibold">
                                        <span class="material-symbols-outlined text-[16px]">photo_camera</span> {{ $hanyaKamera ? 'Ambil Foto (Kamera)' : 'Ambil Foto' }}
                                    </button>
                                @endif
                                @unless ($hanyaKamera)
                                    <input type="file" accept="image/jpeg,image/png" x-ref="berkas" class="hidden" @change="terima($event)">
                                    <button type="button" @click="$refs.berkas.click()"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-outline-variant text-xs font-semibold hover:bg-surface-container">
                                        <span class="material-symbols-outlined text-[16px]">upload_file</span> Pilih File
                                    </button>
                                @endunless
                            </div>
                            @if ($hanyaKamera)
                                <p class="text-[11px] text-on-surface-variant mt-1">Wajib foto langsung dari kamera, bukan dari galeri.</p>
                            @endif

                            <div x-show="nama" x-cloak class="mt-2 flex items-center gap-2 text-[11px] text-primary">
                                <img x-show="pratinjau" :src="pratinjau" class="w-14 h-14 object-cover rounded border border-outline-variant/40" alt="">
                                <span class="truncate"><strong x-text="nama"></strong> <span class="text-on-surface-variant">(belum disimpan)</span></span>
                            </div>
                            @error($field)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
                <div class="px-5 py-4 border-t border-outline-variant/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-xs text-on-surface-variant">Foto yang bertanda "belum disimpan" baru tersimpan setelah tombol Simpan diklik.</p>
                    <button type="submit" name="aksi" value="simpan"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-bold shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">save</span> Simpan Lampiran
                    </button>
                </div>
            </div>

            {{-- Verifikasi berkas --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Catatan <span class="font-normal text-on-surface-variant">(wajib untuk Kembalikan ke Masyarakat)</span></label>
                        <textarea name="catatan" rows="2" class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm">{{ old('catatan') }}</textarea>
                        @error('catatan')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3">
                        <button type="submit" name="aksi" value="kembalikan"
                            data-confirm="Kembalikan berkas ke masyarakat untuk memperbaiki data?" data-confirm-title="Kembalikan ke Masyarakat"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-outline-variant hover:bg-surface-container-highest text-sm font-bold">
                            <span class="material-symbols-outlined text-[18px]">undo</span> Kembalikan ke Masyarakat
                        </button>
                        <button type="submit" name="aksi" value="simpan"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg bg-surface-container hover:bg-secondary/20 text-secondary text-sm font-bold">
                            <span class="material-symbols-outlined text-[18px]">save</span> Simpan Draft
                        </button>
                        <button type="submit" name="aksi" value="validasi" @disabled(! $verifData)
                            @if (! $verifData) title="Klik tombol Verifikasi pada kartu Verifikasi Data Pemohon terlebih dahulu" @endif
                            data-confirm="Verifikasi berkas ini dan teruskan ke Lurah? Setelah diteruskan, Kelurahan tidak bisa mengubahnya lagi." data-confirm-title="Verifikasi Berkas"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-bold shadow-md sm:ml-auto disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary">
                            <span class="material-symbols-outlined text-[18px]">verified</span> Verifikasi Berkas & Teruskan ke Lurah
                        </button>
                        <button type="submit" name="aksi" value="tolak"
                            data-confirm="Yakin menolak permohonan ini?" data-confirm-variant="error" data-confirm-title="Tolak Permohonan"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-sm font-bold">
                            <span class="material-symbols-outlined text-[18px]">cancel</span> Tolak
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-6 text-center shadow-sm">
            <span class="material-symbols-outlined text-[32px] text-success">task_alt</span>
            <h4 class="text-base font-bold mt-1">Sudah diverifikasi Kelurahan</h4>
            <p class="text-sm text-on-surface-variant">Status saat ini: <strong>{{ $data->statusLabel() }}</strong>.</p>

        </div>

        {{-- Lampiran tetap bisa dilihat (termasuk Surat Pengantar) setelah berkas diteruskan --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">attach_file</span> Lampiran</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
                @foreach ($lampiran as $field => [$label])
                    <div>
                        <x-lampiran-preview :src="$data->{$field} ? asset('storage/' . $data->{$field}) : null" :label="$label" />
                        @php $cek = $data->verifikasi_berkas[$field] ?? null; @endphp
                        @if ($cek)
                            <p class="mt-1 text-[11px] font-semibold {{ $cek === 'sesuai' ? 'text-success' : 'text-error' }}">{{ $cek === 'sesuai' ? 'Sesuai' : 'Tidak sesuai' }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Log --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm mb-10">
        <div class="px-5 py-4 border-b border-outline-variant/40"><h3 class="text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">history</span> Log Proses</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase">
                    <tr><th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Username</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Aktivitas</th><th class="px-5 py-3">Catatan</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($data->logs as $log)
                        <tr><td class="px-5 py-3 whitespace-nowrap text-on-surface-variant">{{ $log->created_at->format('d M Y - H:i') }}</td>
                            <td class="px-5 py-3 font-medium">{{ $log->username }}</td>
                            <td class="px-5 py-3"><span class="bg-surface-container px-2 py-1 rounded text-xs">{{ $log->role_name }}</span></td>
                            <td class="px-5 py-3 font-medium">{{ $log->task_name }}</td>
                            <td class="px-5 py-3 text-on-surface-variant">{{ $log->catatan ?: '-' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-on-surface-variant">Belum ada log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection