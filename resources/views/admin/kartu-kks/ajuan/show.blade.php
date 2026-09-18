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
@endphp

@section('content')
    <div class="space-y-5 max-w-5xl">

        <a href="{{ route('kartu-kks.ajuan') }}"
            class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar Ajuan
        </a>

        @if (session('success'))
            <div id="alert-success"
                class="flex items-center gap-2 px-4 py-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-700">
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-2 px-4 py-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700">
                <span class="material-symbols-outlined text-[18px]">error</span>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="flex flex-col gap-1 px-4 py-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        {{-- Header ringkas --}}
        <div
            class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <p class="text-xs text-on-surface-variant">NIK</p>
                <h2 class="text-lg font-bold text-primary">{{ $data->nik }}</h2>
                <p class="text-sm text-on-surface-variant mt-1">{{ $data->nama_pemohon }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $statusColor }}">
                {{ $statusLabels[$data->status] ?? $data->status }}
            </span>
        </div>

        {{-- 1. Data Permohonan --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-outline-variant/40">
                <h3 class="text-sm font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-secondary">assignment</span>
                    Data Permohonan
                </h3>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 p-4 text-sm">
                <div>
                    <dt class="text-xs text-on-surface-variant">NIK</dt>
                    <dd class="font-medium">{{ $data->nik ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <form method="POST" action="{{ route('kartu-kks.detail.update', $data) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- 2. Data Detail (editable) --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden mb-5">
                <div class="px-4 py-3 border-b border-outline-variant/40 flex items-center justify-between">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">badge</span>
                        Data Detail
                    </h3>
                    @if ($canAct)
                        <span class="text-xs text-primary">Dapat diedit</span>
                    @endif
                </div>

                <div class="p-4">
                    @php
                        $fields = [
                            'nik' => ['label' => 'NIK', 'type' => 'text', 'wajib' => true],
                            'nama' => ['label' => 'Nama', 'type' => 'text', 'wajib' => true],
                            'jenis_kelamin' => [
                                'label' => 'Jenis Kelamin', 'type' => 'select', 'wajib' => true,
                                'options' => ['Laki-laki', 'Perempuan'],
                            ],
                            'tempat_lahir' => ['label' => 'Tempat Lahir', 'type' => 'text', 'wajib' => true],
                            'tanggal_lahir' => ['label' => 'Tanggal Lahir', 'type' => 'date', 'wajib' => true],
                            'agama' => [
                                'label' => 'Agama', 'type' => 'select', 'wajib' => true,
                                'options' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghuchu'],
                            ],
                            'status_perkawinan' => [
                                'label' => 'Status Perkawinan', 'type' => 'select', 'wajib' => true,
                                'options' => ['Kawin', 'Belum Kawin', 'Cerai Hidup', 'Cerai Mati'],
                            ],
                            'no_pkh' => ['label' => 'No PKH', 'type' => 'text', 'wajib' => true],
                            'no_kk' => ['label' => 'No KK', 'type' => 'text', 'wajib' => true],
                            'no_kartu_kks' => ['label' => 'No Kartu/KKS', 'type' => 'text', 'wajib' => true],
                            'no_rekening' => ['label' => 'No Rekening', 'type' => 'text', 'wajib' => true],
                            'pekerjaan' => ['label' => 'Pekerjaan', 'type' => 'text', 'wajib' => true],
                            'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'wajib' => true],
                            'masalah_kartu' => [
                                'label' => 'Masalah Kartu', 'type' => 'select', 'wajib' => true,
                                'options' => [
                                    'terblokir', 'rusak', 'hilang', 'masa berlaku habis',
                                    'nama tidak sesuai', 'keterangan lainnya',
                                ],
                            ],
                            'nomor_kehilangan_polisi' => ['label' => 'Nomor Kehilangan Polisi', 'type' => 'text', 'wajib' => false],
                        ];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        @foreach ($fields as $field => $config)
                            <div class="{{ $config['type'] === 'textarea' ? 'sm:col-span-2' : '' }}">
                                <label class="block text-xs text-on-surface-variant mb-1">
                                    {{ $config['label'] }}
                                    @if ($config['wajib'])
                                        <span class="text-error">*</span>
                                    @endif
                                </label>

                                @if (!$canAct)
                                    <p class="font-medium text-sm px-1 py-1.5">
                                        {{ $detail->$field ?? null
                                            ? ($config['type'] === 'date'
                                                ? \Carbon\Carbon::parse($detail->$field)->format('d-m-Y')
                                                : $detail->$field)
                                            : '-' }}
                                    </p>
                                @elseif ($config['type'] === 'textarea')
                                    <textarea name="{{ $field }}" rows="2"
                                        class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old($field, $detail->$field ?? '') }}</textarea>
                                @elseif ($config['type'] === 'select')
                                    <select name="{{ $field }}"
                                        class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                                        <option value="">-Pilih-</option>
                                        @foreach ($config['options'] as $opt)
                                            <option value="{{ $opt }}" @selected(old($field, $detail->$field ?? '') === $opt)>
                                                {{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $config['type'] }}" name="{{ $field }}"
                                        value="{{ old($field, $config['type'] === 'date' ? optional($detail->$field ?? null)->format('Y-m-d') : $detail->$field ?? '') }}"
                                        class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                                @endif
                                @error($field)
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. Lampiran (editable upload) — disamakan gaya dengan PBI APBN --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden mb-5 shadow-sm">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-secondary">attach_file</span>
                        Lampiran
                    </h3>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
                    @foreach ($lampiranFields as $field => $config)
                        @php $file = $data->lampiran->$field ?? null; @endphp
                        <div class="group border border-outline-variant/40 rounded-lg overflow-hidden hover:border-primary transition-all">
                            <a @if ($file) href="{{ asset('storage/' . $file) }}" target="_blank" @endif
                                class="block">
                                <div class="aspect-square bg-surface-container/50 flex items-center justify-center p-2">
                                    @if ($file)
                                        <img src="{{ asset('storage/' . $file) }}"
                                            class="w-full h-full object-contain mix-blend-multiply transition-transform group-hover:scale-105"
                                            alt="{{ $config['label'] }}">
                                    @else
                                        <span
                                            class="material-symbols-outlined text-[32px] text-on-surface-variant/40">image_not_supported</span>
                                    @endif
                                </div>
                            </a>
                            <div class="bg-surface-container-lowest border-t border-outline-variant/40 p-2">
                                <p class="text-[11px] text-center text-on-surface-variant font-medium">
                                    {{ $config['label'] }}
                                    @if ($config['wajib'])
                                        <span class="text-error">*</span>
                                    @endif
                                </p>
                                @error($field)
                                    <p class="text-[10px] text-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 4. Surat Keterangan --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden mb-5">
                <div class="px-4 py-3 border-b border-outline-variant/40">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">description</span>
                        Surat Keterangan
                    </h3>
                </div>
                <div class="p-4 space-y-2">
                    @php $suratKelurahan = $data->surat->surat_pengantar_kelurahan ?? null; @endphp
                    <a @if ($suratKelurahan) href="{{ asset('storage/' . $suratKelurahan) }}" target="_blank" @endif
                        class="flex items-center gap-2 border border-outline-variant/40 rounded-lg px-3 py-2 text-sm hover:border-primary transition-colors w-fit">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">description</span>
                        Surat Pengantar KKS Kelurahan Digital
                        @unless ($suratKelurahan)
                            <span class="text-xs text-on-surface-variant">(belum ada)</span>
                        @endunless
                    </a>
                </div>
            </div>

            {{-- 5. Surat Keterangan KKS Dinas Sosial --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden mb-5">
                <div class="px-4 py-3 border-b border-outline-variant/40">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">verified</span>
                        Surat Keterangan KKS Dinas Sosial
                    </h3>
                </div>
                <div class="p-4 space-y-2">
                    @php $suratDinsos = $data->surat->surat_keterangan_dinsos ?? null; @endphp
                    <a @if ($suratDinsos) href="{{ asset('storage/' . $suratDinsos) }}" target="_blank" @endif
                        class="flex items-center gap-2 border border-outline-variant/40 rounded-lg px-3 py-2 text-sm hover:border-primary transition-colors w-fit">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">description</span>
                        Surat Keterangan KKS Digital
                        @unless ($suratDinsos)
                            <span class="text-xs text-on-surface-variant">(belum ada)</span>
                        @endunless
                    </a>
                </div>
            </div>

            @if ($canAct)
                <div class="mb-5">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan Semua Data
                    </button>
                </div>
            @endif
        </form>

        {{-- 6. Log Proses --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-secondary">history</span>
                    Log Proses
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase tracking-wider">
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
                            <tr class="hover:bg-surface-container-lowest/50 transition-colors">
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
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm mb-10">
            <div class="px-5 py-4 border-b border-outline-variant/40 bg-surface-container/20">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-secondary">alt_route</span>
                    Proses Permohonan
                </h3>
            </div>

            @if ($isFinal)
                <div class="p-6 text-center bg-surface-container-lowest">
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
                <div class="p-6 text-center bg-surface-container-lowest">
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

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-on-surface mb-2">Catatan Proses <span class="text-error">*</span></label>
                        <textarea name="catatan" rows="3" required placeholder="Tulis catatan (wajib diisi untuk semua aksi)..."
                            class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary shadow-sm">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3">

                        @if ($returnLabel)
                            <button type="submit" name="action" value="kembalikan"
                                onclick="return confirm('{{ $returnLabel }}?');"
                                class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-outline-variant hover:bg-surface-container-highest text-on-surface text-sm font-bold transition-all">
                                <span class="material-symbols-outlined text-[18px]">undo</span>
                                {{ $returnLabel }}
                            </button>
                        @endif

                        <button type="submit" name="action" value="simpan_catatan"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg bg-surface-container hover:bg-secondary/20 text-secondary text-sm font-bold transition-all">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Catatan Saja
                        </button>

                        <button type="submit" name="action" value="lanjut"
                            onclick="return confirm('Teruskan permohonan ini ke tahap berikutnya{{ $nextTaskLabel ? ' (' . $nextTaskLabel . ')' : '' }}?');"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-bold shadow-md transition-all sm:ml-auto">
                            <span class="material-symbols-outlined text-[18px]">redo</span>
                            @if ($nextTaskLabel)
                                Teruskan ke {{ $nextTaskLabel }}
                            @else
                                Teruskan
                            @endif
                        </button>

                        <button type="submit" name="action" value="tolak"
                            onclick="return confirm('Yakin ingin menolak permohonan ini?');"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-sm font-bold transition-all">
                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                            Tolak (Reject)
                        </button>
                    </div>
                </form>
            @endif
        </div>

    </div>

    <script>
        const alertBox = document.getElementById('alert-success');
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
            }, 3000);
        }
    </script>
@endsection
