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

            {{-- 3. Lampiran (editable upload) --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden mb-5">
                <div class="px-4 py-3 border-b border-outline-variant/40">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">attach_file</span>
                        Lampiran
                    </h3>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 p-4">
                    @foreach ($lampiranFields as $field => $config)
                        @php $file = $data->lampiran->$field ?? null; @endphp
                        <div class="border border-outline-variant/40 rounded-lg overflow-hidden">
                            <a @if ($file) href="{{ asset('storage/' . $file) }}" target="_blank" @endif
                                class="block hover:border-primary transition-colors">
                                <div class="aspect-square bg-surface-container flex items-center justify-center">
                                    @if ($file)
                                        <img src="{{ asset('storage/' . $file) }}" class="w-full h-full object-cover"
                                            alt="{{ $config['label'] }}">
                                    @else
                                        <span
                                            class="material-symbols-outlined text-[28px] text-on-surface-variant/50">image_not_supported</span>
                                    @endif
                                </div>
                            </a>
                            <p class="text-[11px] text-center px-1 py-1.5 text-on-surface-variant">
                                {{ $config['label'] }}
                                @if ($config['wajib'])
                                    <span class="text-error">*</span>
                                @endif
                            </p>
                            @if ($canAct)
                                <div class="px-1 pb-1.5">
                                    <input type="file" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf"
                                        class="w-full text-[10px]" />
                                </div>
                            @endif
                            @error($field)
                                <p class="text-[10px] text-error px-1 pb-1.5">{{ $message }}</p>
                            @enderror
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
                    @if ($canAct)
                        <input type="file" name="surat_pengantar_kelurahan" accept=".jpg,.jpeg,.png,.pdf"
                            class="block text-xs" />
                        @error('surat_pengantar_kelurahan')
                            <p class="text-xs text-error">{{ $message }}</p>
                        @enderror
                    @endif
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
                    @if ($canAct)
                        <input type="file" name="surat_keterangan_dinsos" accept=".jpg,.jpeg,.png,.pdf"
                            class="block text-xs" />
                        @error('surat_keterangan_dinsos')
                            <p class="text-xs text-error">{{ $message }}</p>
                        @enderror
                    @endif
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
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-outline-variant/40">
                <h3 class="text-sm font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-secondary">history</span>
                    Log Proses
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
                        <tr>
                            <th class="text-left px-4 py-2">Tanggal Proses</th>
                            <th class="text-left px-4 py-2">Username</th>
                            <th class="text-left px-4 py-2">Role</th>
                            <th class="text-left px-4 py-2">Aktivitas</th>
                            <th class="text-left px-4 py-2">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        @forelse ($data->logs as $log)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($log->tanggal_proses)->format('d-m-Y H:i') }}</td>
                                <td class="px-4 py-2">{{ $log->username }}</td>
                                <td class="px-4 py-2">{{ $log->rolename }}</td>
                                <td class="px-4 py-2">{{ $log->taskname }}</td>
                                <td class="px-4 py-2 text-on-surface-variant">{{ $log->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-on-surface-variant">Belum ada log proses.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 7. Aksi & Catatan --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-outline-variant/40">
                <h3 class="text-sm font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-secondary">alt_route</span>
                    Proses Permohonan
                </h3>
            </div>

            @if ($isFinal)
                <p class="px-4 py-6 text-sm text-on-surface-variant">
                    Permohonan ini sudah final dengan status
                    <strong>{{ $statusLabels[$data->status] ?? $data->status }}</strong>. Tidak ada aksi lanjutan.
                </p>
            @elseif (!$canAct)
                <p class="px-4 py-6 text-sm text-on-surface-variant">
                    Permohonan ini sedang berada di tahap
                    <strong>{{ $statusLabels[$data->status] ?? $data->status }}</strong>.
                    Anda tidak berwenang memproses pada tahap ini — hanya bisa melihat.
                </p>
            @else
                <form method="POST" action="{{ route('kartu-kks.proses', $data) }}" class="p-4 space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">Catatan</label>
                        <textarea name="catatan" rows="3" required placeholder="Tulis catatan untuk tahap ini..."
                            class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex flex-wrap gap-2">

                        @if ($returnLabel)
                            <button type="submit" name="action" value="kembalikan"
                                onclick="return confirm('{{ $returnLabel }}?');"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high text-sm font-medium transition-colors">
                                <span class="material-symbols-outlined text-[18px]">undo</span>
                                {{ $returnLabel }}
                            </button>
                        @endif

                        <button type="submit" name="action" value="simpan_catatan"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary/10 text-secondary hover:bg-secondary hover:text-on-secondary text-sm font-medium transition-colors">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Catatan
                        </button>

                        <button type="submit" name="action" value="lanjut"
                            onclick="return confirm('Teruskan permohonan ini ke tahap berikutnya{{ $nextTaskLabel ? ' (' . $nextTaskLabel . ')' : '' }}?');"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-medium transition-colors">
                            <span class="material-symbols-outlined text-[18px]">redo</span>
                            @if ($nextTaskLabel)
                                Teruskan ke {{ $nextTaskLabel }}
                            @else
                                Teruskan
                            @endif
                        </button>

                        <button type="submit" name="action" value="tolak"
                            onclick="return confirm('Yakin ingin menolak permohonan ini? Catatan alasan wajib diisi.');"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-error/10 text-error hover:bg-error hover:text-on-error text-sm font-medium transition-colors ml-auto">
                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                            Reject
                        </button>
                    </div>

                    <p class="text-[11px] text-on-surface-variant italic">
                        Catatan wajib diisi untuk aksi Reject. Alur Reject masih menunggu konfirmasi lebih lanjut dari pihak
                        terkait.
                    </p>
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