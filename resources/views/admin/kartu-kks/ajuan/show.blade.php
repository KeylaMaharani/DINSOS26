@extends('layouts.admin')

@section('title', 'Detail Ajuan Kartu KKS')
@section('page_title', 'Kartu KKS — Detail Ajuan')

@php
    $statusColor = match ($data->status) {
        \App\Models\KartuKksPermohonan::STATUS_SELESAI => 'bg-success-container text-on-success-container',
        \App\Models\KartuKksPermohonan::STATUS_DITOLAK => 'bg-error-container text-on-error-container',
        default => 'bg-secondary-fixed text-on-secondary-container',
    };

    $detail = $data->detail;

    $lampiranFields = [
        'scan_ktp' => ['label' => 'Scan KTP', 'wajib' => false],
        'scan_kartu_keluarga' => ['label' => 'Scan Kartu Keluarga', 'wajib' => false],
        'surat_kehilangan_polisi' => ['label' => 'Surat Kehilangan Dari Polisi', 'wajib' => false],
        'scan_kartu_kks' => ['label' => 'Scan Kartu KKS', 'wajib' => false],
        'screenshot_dtsen' => ['label' => 'Screenshot DTsen', 'wajib' => true],
    ];

    $inputFileClass =
        'mt-2 block w-full text-[10px] text-on-surface-variant file:mr-2 file:rounded file:border-0 file:bg-brand/10 file:px-2 file:py-1 file:text-brand';
@endphp

@section('content')
    <div class="space-y-5 max-w-5xl">

        <a href="{{ route('kartu-kks.ajuan') }}"
            class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-brand">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar Ajuan
        </a>

        @if ($errors->any())
            <div class="flex flex-col gap-1 px-4 py-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        {{-- Header ringkas --}}
        <div
            class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] p-5 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-[18px] text-brand">assignment</span>
                    Data Permohonan
                </h3>
                <p class="text-xs text-on-surface-variant">NIK</p>
                <h2 class="text-lg font-extrabold text-brand">{{ $data->nik }}</h2>
                <p class="text-sm text-on-surface-variant mt-1">{{ $data->nama_pemohon }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $statusColor }}">
                {{ $statusLabels[$data->status] ?? $data->status }}
            </span>
        </div>

        <form method="POST" action="{{ route('kartu-kks.detail.update', $data) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- 2. Data Detail (editable oleh Operator Dinsos / Super Admin) --}}
            <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden mb-5">
                <div class="px-4 py-3 border-b border-outline-variant/40 flex items-center justify-between">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-brand">badge</span>
                        Data Detail
                    </h3>
                    @if ($canEdit)
                        <span class="text-xs text-brand">Dapat diedit</span>
                    @endif
                </div>

                <div class="p-4">
                    @php
                        $fields = [
                            'nik' => ['label' => 'NIK', 'type' => 'text', 'wajib' => true],
                            'nama' => ['label' => 'Nama', 'type' => 'text', 'wajib' => true],
                            'jenis_kelamin' => [
                                'label' => 'Jenis Kelamin',
                                'type' => 'select',
                                'wajib' => true,
                                'options' => ['Laki-laki', 'Perempuan'],
                            ],
                            'tempat_lahir' => ['label' => 'Tempat Lahir', 'type' => 'text', 'wajib' => true],
                            'tanggal_lahir' => ['label' => 'Tanggal Lahir', 'type' => 'date', 'wajib' => true],
                            'agama' => [
                                'label' => 'Agama',
                                'type' => 'select',
                                'wajib' => true,
                                'options' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghuchu'],
                            ],
                            'status_perkawinan' => [
                                'label' => 'Status Perkawinan',
                                'type' => 'select',
                                'wajib' => true,
                                'options' => ['Kawin', 'Belum Kawin', 'Cerai Hidup', 'Cerai Mati'],
                            ],
                            'no_pkh' => ['label' => 'No PKH', 'type' => 'text', 'wajib' => true],
                            'no_kk' => ['label' => 'No KK', 'type' => 'text', 'wajib' => true],
                            'no_kartu_kks' => ['label' => 'No Kartu/KKS', 'type' => 'text', 'wajib' => true],
                            'no_rekening' => ['label' => 'No Rekening', 'type' => 'text', 'wajib' => true],
                            'pekerjaan' => ['label' => 'Pekerjaan', 'type' => 'text', 'wajib' => true],
                            'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'wajib' => true],
                            'masalah_kartu' => [
                                'label' => 'Masalah Kartu',
                                'type' => 'select',
                                'wajib' => true,
                                'options' => [
                                    'terblokir',
                                    'rusak',
                                    'hilang',
                                    'masa berlaku habis',
                                    'nama tidak sesuai',
                                    'keterangan lainnya',
                                ],
                            ],
                            'nomor_kehilangan_polisi' => [
                                'label' => 'Nomor Kehilangan Polisi',
                                'type' => 'text',
                                'wajib' => false,
                            ],
                        ];
                    @endphp

                    <div class="grid grid-cols-1 gap-y-4 max-w-2xl">
                        @foreach ($fields as $field => $config)
                            <div @if ($field === 'nomor_kehilangan_polisi') id="wrap-nomor-kehilangan" @endif
                                class="{{ $field === 'nomor_kehilangan_polisi' && ($detail->masalah_kartu ?? null) !== 'hilang' ? 'hidden' : '' }}">
                                <label class="block text-xs text-on-surface-variant mb-1">
                                    {{ $config['label'] }}
                                    @if ($config['wajib'])
                                        <span class="text-error">*</span>
                                    @endif
                                </label>

                                @if (!$canEdit)
                                    <p class="font-medium text-sm px-1 py-1.5">
                                        {{ $detail->$field ?? null
                                            ? ($config['type'] === 'date'
                                                ? \Carbon\Carbon::parse($detail->$field)->format('d-m-Y')
                                                : $detail->$field)
                                            : '-' }}
                                    </p>
                                @elseif ($config['type'] === 'textarea')
                                    <textarea name="{{ $field }}" rows="2"
                                        class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">{{ old($field, $detail->$field ?? '') }}</textarea>
                                @elseif ($config['type'] === 'select')
                                    <select name="{{ $field }}" @if ($field === 'masalah_kartu') id="masalah_kartu" @endif
                                        class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                                        <option value="">-Pilih-</option>
                                        @foreach ($config['options'] as $opt)
                                            <option value="{{ $opt }}" @selected(old($field, $detail->$field ?? '') === $opt)>
                                                {{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $config['type'] }}" name="{{ $field }}"
                                        value="{{ old($field, $config['type'] === 'date' ? optional($detail->$field ?? null)->format('Y-m-d') : $detail->$field ?? '') }}"
                                        class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30" />
                                @endif
                                @error($field)
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. Lampiran (editable upload) --}}
            <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden mb-5">
                <div class="px-5 py-4 border-b border-outline-variant/40 flex items-center justify-between">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-brand">attach_file</span>
                        Lampiran
                    </h3>
                    @if ($canUploadLampiran)
                        <span class="text-[11px] text-on-surface-variant">Pilih file untuk mengunggah / mengganti
                            (JPG, PNG, PDF, maks. 2 MB)</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
                    @foreach ($lampiranFields as $field => $config)
                        @php
                            $file = $data->lampiran->$field ?? null;
                            $isPdf = $file && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf';
                        @endphp
                        <div @if ($field === 'surat_kehilangan_polisi') id="wrap-surat-kehilangan" @endif
                            class="group border border-outline-variant/40 rounded-lg overflow-hidden hover:border-brand transition-all {{ $field === 'surat_kehilangan_polisi' && ($detail->masalah_kartu ?? null) !== 'hilang' ? 'hidden' : '' }}">
                            <a @if ($file) href="{{ asset('storage/' . $file) }}" target="_blank" @endif
                                class="block">
                                <div class="aspect-square bg-surface-container/50 flex items-center justify-center p-2">
                                    @if ($file && !$isPdf)
                                        <img src="{{ asset('storage/' . $file) }}"
                                            class="w-full h-full object-contain mix-blend-multiply transition-transform group-hover:scale-105"
                                            alt="{{ $config['label'] }}">
                                    @elseif ($file && $isPdf)
                                        <span class="material-symbols-outlined text-[40px] text-error/70">picture_as_pdf</span>
                                    @else
                                        <span
                                            class="material-symbols-outlined text-[32px] text-on-surface-variant/40">image_not_supported</span>
                                    @endif
                                </div>
                            </a>
                            <div class="bg-white border-t border-outline-variant/40 p-2">
                                <p class="text-[11px] text-center text-on-surface-variant font-medium">
                                    {{ $config['label'] }}
                                    @if ($config['wajib'])
                                        <span class="text-error">*</span>
                                    @endif
                                </p>
                                @if ($canUploadLampiran)
                                    <input type="file" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf"
                                        class="{{ $inputFileClass }}" />
                                @endif
                                @error($field)
                                    <p class="text-[10px] text-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($canEdit)
                <div class="mb-5">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand text-white hover:bg-brand text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan Semua Data
                    </button>
                </div>
            @endif
        </form>

        {{-- 4. Surat Keterangan KKS Dinas Sosial --}}
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden mb-5">
                <div class="px-4 py-3 border-b border-outline-variant/40">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-brand">verified</span>
                        Surat Keterangan KKS Dinas Sosial
                    </h3>
                </div>
                @php
                    $suratDinsos = $data->surat->surat_keterangan_dinsos ?? null;
                    $suratAda = $suratDinsos && \Illuminate\Support\Facades\Storage::disk('public')->exists($suratDinsos);
                @endphp
                <div class="p-4" x-data="{ preview: false }">
                    @if ($suratAda)
                        <div class="flex flex-wrap items-center gap-3 border border-outline-variant/40 rounded-xl px-4 py-3 w-fit">
                            <span class="material-symbols-outlined text-[28px] text-error/70">picture_as_pdf</span>
                            <div class="mr-2">
                                <p class="text-sm font-semibold">Surat Keterangan KKS Digital</p>
                                <p class="text-[11px] text-on-surface-variant">
                                    Ditandatangani secara elektronik
                                    @if ($data->surat?->surat_keterangan_dinsos_at)
                                        &middot; {{ $data->surat->surat_keterangan_dinsos_at->format('d M Y - H:i') }}
                                    @endif
                                </p>
                            </div>
                            <button type="button" @click="preview = true" title="Lihat surat"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-brand/10 text-brand hover:bg-brand hover:text-white text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[16px]">visibility</span> Lihat
                            </button>
                            <a href="{{ asset('storage/' . $suratDinsos) }}" download="Surat-Keterangan-KKS-{{ $data->no_permohonan }}.pdf"
                                title="Unduh PDF"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand/10 text-brand hover:bg-brand hover:text-white transition-colors">
                                <span class="material-symbols-outlined text-[18px]">download</span>
                            </a>
                        </div>

                        <div x-show="preview" x-cloak @keydown.escape.window="preview = false"
                            class="fixed inset-0 z-[9000] flex items-center justify-center bg-black/50 p-4">
                            <div @click.outside="preview = false"
                                class="bg-white w-full max-w-4xl h-[90vh] rounded-2xl shadow-xl flex flex-col overflow-hidden">
                                <div class="shrink-0 flex items-center justify-between px-5 py-3 border-b border-outline-variant/40">
                                    <h4 class="text-sm font-bold">Surat Keterangan KKS</h4>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ asset('storage/' . $suratDinsos) }}"
                                            download="Surat-Keterangan-KKS-{{ $data->no_permohonan }}.pdf" title="Unduh PDF"
                                            class="w-9 h-9 rounded-full flex items-center justify-center text-brand hover:bg-brand/10 transition-colors">
                                            <span class="material-symbols-outlined text-[22px]">download</span>
                                        </a>
                                        <button type="button" @click="preview = false" title="Tutup"
                                            class="w-9 h-9 rounded-full flex items-center justify-center text-error hover:bg-error-container/50 transition-colors">
                                            <span class="material-symbols-outlined text-[22px]">close</span>
                                        </button>
                                    </div>
                                </div>
                                <iframe x-show="preview" src="{{ asset('storage/' . $suratDinsos) }}"
                                    class="flex-1 w-full" title="Preview Surat Keterangan KKS"></iframe>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2 border border-dashed border-outline-variant/60 rounded-xl px-4 py-3 w-fit text-sm text-on-surface-variant">
                                <span class="material-symbols-outlined text-[20px] opacity-50">description</span>
                                @if ($bisaBuatSurat)
                                    Surat belum tersedia untuk ajuan ini.
                                @else
                                    Surat dibuat otomatis setelah Kepala Dinas menandatangani.
                                @endif
                            </div>

                            @if ($bisaBuatSurat)
                                {{-- Tombol ini memakai form terpisah di bawah (atribut form=...) karena
                                     blok ini berada di dalam form Simpan Data -- form tidak boleh bersarang. --}}
                                <button type="submit" form="form-buat-surat"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand text-white hover:opacity-90 text-xs font-bold shadow-md transition-all">
                                    <span class="material-symbols-outlined text-[16px]">description</span>
                                    Buat Surat Sekarang
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        @if ($bisaBuatSurat)
            <form id="form-buat-surat" method="POST" action="{{ route('kartu-kks.surat.buat', $data) }}" class="hidden">
                @csrf
            </form>
        @endif

                {{-- 5. Tanda Tangan Digital Kepala Dinas (muncul setelah disetujui) --}}
        @if ($ttdKadis)
            <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-brand">draw</span>
                        Tanda Tangan Digital Kepala Dinas
                    </h3>
                </div>
                <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    @if ($ttdKadis['path'])
                        <img src="{{ asset('storage/' . $ttdKadis['path']) }}" alt="Tanda tangan Kepala Dinas"
                            class="h-20 max-w-[12rem] object-contain rounded border border-outline-variant/40 bg-white p-2">
                    @else
                        <div class="h-20 w-48 flex items-center justify-center rounded border border-dashed border-outline-variant/60 text-xs text-on-surface-variant">
                            Gambar TTD tidak tersedia
                        </div>
                    @endif
                    <div class="text-sm">
                        <p class="font-semibold">{{ $ttdKadis['nama'] }}</p>
                        <p class="text-xs text-on-surface-variant">
                            Disetujui pada
                            {{ \Carbon\Carbon::parse($ttdKadis['tanggal'])->format('d M Y - H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- 6. Log Proses --}}
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-brand">history</span>
                    Log Proses
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#e6edf8] text-on-surface-variant text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 font-medium">Tanggal Proses</th>
                            <th class="px-5 py-3 font-medium">Username</th>
                            <th class="px-5 py-3 font-medium">Role</th>
                            <th class="px-5 py-3 font-medium">Aktivitas</th>
                            <th class="px-5 py-3 font-medium">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        @forelse ($data->logs as $log)
                            <tr class="hover:bg-white/50 transition-colors">
                                <td class="px-5 py-3 whitespace-nowrap text-on-surface-variant">
                                    {{ \Carbon\Carbon::parse($log->tanggal_proses)->format('d M Y - H:i') }}</td>
                                <td class="px-5 py-3 font-medium text-on-surface">{{ $log->username }}</td>
                                <td class="px-5 py-3"><span class="bg-surface-container px-2 py-1 rounded text-xs">{{ $log->rolename }}</span></td>
                                <td class="px-5 py-3 font-medium">{{ $log->taskname }}</td>
                                <td class="px-5 py-3 text-on-surface-variant">{{ $log->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-on-surface-variant">Belum ada log proses.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 7. Aksi & Catatan --}}
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden mb-10">
            <div class="px-5 py-4 border-b border-outline-variant/40 bg-[#eef4fd]">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-brand">alt_route</span>
                    Proses Permohonan
                </h3>
            </div>

            @if ($isFinal)
                <div class="p-6 text-center bg-white">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full {{ $data->status === \App\Models\KartuKksPermohonan::STATUS_SELESAI ? 'bg-success/20 text-success' : 'bg-error/20 text-error' }} mb-3">
                        <span class="material-symbols-outlined text-[24px]">{{ $data->status === \App\Models\KartuKksPermohonan::STATUS_SELESAI ? 'check_circle' : 'cancel' }}</span>
                    </div>
                    <h4 class="text-lg font-bold mb-1">Permohonan Final</h4>
                    <p class="text-sm text-on-surface-variant">
                        Permohonan ini sudah final dengan status
                        <strong>{{ $statusLabels[$data->status] ?? $data->status }}</strong>. Tidak ada aksi lanjutan.
                    </p>
                </div>
            @elseif (!$canAct)
                <div class="p-6 text-center bg-white">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-warning/20 text-warning mb-3">
                        <span class="material-symbols-outlined text-[24px]">hourglass_empty</span>
                    </div>
                    <h4 class="text-lg font-bold mb-1">Menunggu Tahap Selanjutnya</h4>
                    <p class="text-sm text-on-surface-variant">
                        Permohonan ini sedang berada di tahap
                        <strong>{{ $statusLabels[$data->status] ?? $data->status }}</strong>.<br>
                        Anda tidak berwenang memproses pada tahap ini.
                    </p>
                </div>
            @else
                <form method="POST" action="{{ route('kartu-kks.proses', $data) }}" class="p-5">
                    @csrf

                    {{-- Pratinjau TTD digital: hanya di tahap Kepala Dinas --}}
                    @if ($butuhTtd)
                        <div class="mb-5 rounded-xl border border-outline-variant/40 bg-slate-50 p-4">
                            <p class="text-sm font-bold text-on-surface mb-1 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-brand">draw</span>
                                Tanda Tangan Digital Anda
                            </p>
                            @if ($ttdSaatIni)
                                <p class="text-xs text-on-surface-variant mb-3">
                                    Tanda tangan ini akan otomatis dibubuhkan saat Anda menekan tombol
                                    "Setujui &amp; Tanda Tangani".
                                </p>
                                <img src="{{ asset('storage/' . $ttdSaatIni) }}" alt="Tanda tangan Anda"
                                    class="h-16 max-w-[10rem] object-contain rounded border border-outline-variant/40 bg-white p-1">
                            @else
                                <p class="text-xs text-error font-medium">
                                    Tanda tangan digital belum tersedia. Unggah dulu lewat menu
                                    <b>Edit Profil</b> (klik nama Anda di pojok kanan atas), lalu muat ulang halaman ini.
                                </p>
                            @endif

                             @if ($tteAktif)
                                <div class="mt-4">
                                    <label class="block text-xs font-bold text-on-surface mb-1">Password TTE <span class="text-error">*</span></label>
                                    <input type="password" name="password" autocomplete="new-password"
                                        class="w-full max-w-xs px-3 py-2 rounded-xl border border-outline-variant/40 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                                    <p class="text-[11px] text-on-surface-variant mt-1">Dipakai sekali untuk menandatangani dokumen dan tidak disimpan.</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-on-surface mb-2">Catatan Proses <span class="text-error">*</span></label>
                        <textarea name="catatan" rows="3" required placeholder="Tulis catatan (wajib diisi untuk semua aksi)..."
                            class="w-full px-4 py-3 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand shadow-sm">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3">

                        @if ($returnLabel)
                            <button type="submit" name="action" value="kembalikan"
                                data-confirm="{{ $returnLabel }}?"
                                data-confirm-variant="primary"
                                class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-outline-variant hover:bg-surface-container-highest text-on-surface text-sm font-bold transition-all">
                                <span class="material-symbols-outlined text-[18px]">undo</span>
                                {{ $returnLabel }}
                            </button>
                        @endif

                        <button type="submit" name="action" value="simpan_catatan"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg bg-surface-container hover:bg-secondary/20 text-brand text-sm font-bold transition-all">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Catatan Saja
                        </button>

                        <button type="submit" name="action" value="lanjut"
                            data-confirm="{{ $lanjutLabel }}?"
                            data-confirm-variant="primary"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-xl bg-brand text-white hover:bg-brand text-sm font-bold shadow-md transition-all sm:ml-auto">
                            <span class="material-symbols-outlined text-[18px]">{{ $butuhTtd ? 'draw' : 'redo' }}</span>
                            {{ $lanjutLabel }}
                        </button>

                        <button type="submit" name="action" value="tolak"
                            data-confirm="Yakin ingin menolak permohonan ini? Catatan alasan wajib diisi."
                            data-confirm-variant="error"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-sm font-bold transition-all">
                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                            Tolak (Reject)
                        </button>
                    </div>

                    <p class="text-[11px] text-on-surface-variant italic mt-3">
                        Catatan wajib diisi untuk semua aksi (Lanjut, Kembalikan, Simpan, maupun Reject).
                    </p>
                </form>
            @endif
        </div>

    </div>

    @push('scripts')
        <script>
            (function () {
                var sel = document.getElementById('masalah_kartu');
                if (!sel) return;
                var wrapNomor = document.getElementById('wrap-nomor-kehilangan');
                var wrapSurat = document.getElementById('wrap-surat-kehilangan');
                sel.addEventListener('change', function () {
                    var hilang = this.value === 'hilang';
                    if (wrapNomor) wrapNomor.classList.toggle('hidden', !hilang);
                    if (wrapSurat) wrapSurat.classList.toggle('hidden', !hilang);
                });
            })();
        </script>
    @endpush
@endsection