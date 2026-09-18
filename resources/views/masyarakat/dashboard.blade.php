@extends('layouts.masyarakat')

@section('title', 'Pengajuan Saya - SOLID Dinas Sosial Kota Bogor')

@section('content')
    <h1 class="text-xl font-bold text-primary mb-1">Pengajuan Saya</h1>
    <p class="text-sm text-on-surface-variant mb-6">Status &amp; riwayat pengajuan PBI APBN Anda.</p>

    @if (! $pbiApbn)
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-6 text-center">
            <p class="text-sm text-on-surface-variant">Anda belum memiliki pengajuan PBI APBN.</p>
        </div>
    @else
        <div class="space-y-5">

            {{-- Header --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <p class="text-xs text-on-surface-variant">No. Registrasi</p>
                        <p class="font-bold text-primary text-lg">{{ $pbiApbn->no_registrasi }}</p>
                        <p class="text-sm text-on-surface-variant mt-1">
                            {{ $pbiApbn->nama_kepala_keluarga }} &mdash; NIK {{ $pbiApbn->nik_kepala_keluarga }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase
                        {{ $pbiApbn->status === 'disetujui' ? 'bg-success-container text-on-success-container'
                            : ($pbiApbn->status === 'ditolak' ? 'bg-error-container text-on-error-container'
                            : 'bg-secondary/10 text-secondary') }}">
                        {{ $pbiApbn->statusLabel() }}
                    </span>
                </div>
            </div>

            {{-- Peringatan data belum lengkap --}}
            @if (! $isLengkap)
                <div class="border border-warning/40 bg-warning-container/40 rounded-lg p-4">
                    <p class="text-sm text-on-warning-container font-semibold mb-1">Data Anda belum lengkap</p>
                    <p class="text-xs text-on-warning-container mb-3">
                        Lengkapi titik lokasi dan lampiran berkas persyaratan agar pengajuan bisa diproses.
                    </p>
                    <a href="{{ route('masyarakat.pbi-apbn.lengkapi', $pbiApbn) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">
                        <span class="material-symbols-outlined text-[16px]">edit_document</span>
                        Lengkapi Data Sekarang
                    </a>
                </div>
            @endif

            {{-- Progress Stepper --}}
            @if (! $pbiApbn->isFinal())
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                    <h3 class="text-sm font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">alt_route</span>
                        Progress Pengajuan
                    </h3>
                    <div class="flex items-center overflow-x-auto pb-2">
                        @foreach ($pbiApbn->progressSteps() as $i => $step)
                            <div class="flex items-center {{ $loop->last ? '' : 'flex-1 min-w-[90px]' }}">
                                <div class="flex flex-col items-center gap-1 shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $step['state'] === 'done' ? 'bg-success text-white'
                                            : ($step['state'] === 'current' ? 'bg-primary text-white ring-4 ring-primary/20'
                                            : 'bg-surface-container text-on-surface-variant') }}">
                                        @if ($step['state'] === 'done')
                                            <span class="material-symbols-outlined text-[16px]">check</span>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-center w-20 {{ $step['state'] === 'current' ? 'font-bold text-primary' : 'text-on-surface-variant' }}">
                                        {{ $step['label'] }}
                                    </span>
                                </div>
                                @if (!$loop->last)
                                    <div class="flex-1 h-0.5 mx-1 {{ $step['state'] === 'done' ? 'bg-success' : 'bg-outline-variant/50' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif ($pbiApbn->status === 'ditolak')
                <div class="border border-error/40 bg-error-container/30 rounded-lg p-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-error text-[18px]">cancel</span>
                    <p class="text-sm text-on-error-container">Pengajuan Anda <strong>ditolak</strong>. Lihat riwayat proses di bawah untuk catatan alasannya.</p>
                </div>
            @endif

            {{-- Data Diri Ringkas --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-secondary">assignment</span>
                    Data Permohonan
                </h3>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    @foreach ([
                        'No KK' => $pbiApbn->no_kk,
                        'Nama Kepala Keluarga' => $pbiApbn->nama_kepala_keluarga,
                        'Alamat' => $pbiApbn->alamat,
                        'Kecamatan' => $pbiApbn->kecamatan,
                        'Desa/Kelurahan' => $pbiApbn->desa_kelurahan,
                        'No Telp' => $pbiApbn->no_telp,
                    ] as $label => $value)
                        <div>
                            <p class="text-xs text-on-surface-variant">{{ $label }}</p>
                            <p class="font-medium">{{ $value ?: '-' }}</p>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Lampiran yang sudah diupload --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">attach_file</span>
                        Lampiran Terunggah
                    </h3>
                    <a href="{{ route('masyarakat.pbi-apbn.lengkapi', $pbiApbn) }}"
                       class="text-xs font-semibold text-primary underline">
                        Perbarui / Unggah Ulang
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ([
                        'scan_ktp' => 'Scan KTP',
                        'scan_kk' => 'Scan KK',
                        'foto_rumah' => 'Foto Rumah',
                        'foto_kamar_mandi' => 'Foto Kamar Mandi',
                        'foto_selfie_ktp' => 'Selfie + KTP',
                        'surat_rawat_inap' => 'Surat Rawat Inap',
                        'screenshot_pembaharuan_desil' => 'Screenshot Desil',
                        'screenshot_dtsen' => 'Screenshot DTSEN',
                    ] as $field => $label)
                        <x-lampiran-preview
                            :src="$pbiApbn->{$field} ? asset('storage/' . $pbiApbn->{$field}) : null"
                            :label="$label" />
                    @endforeach
                </div>
            </div>

            {{-- Informasi tambahan dari petugas (read-only) --}}
            @if ($pbiApbn->catatan_berkas || $pbiApbn->kesimpulan_rekomendasi || $pbiApbn->nama_faskes)
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                    <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">fact_check</span>
                        Informasi dari Petugas
                    </h3>
                    <dl class="space-y-3 text-sm">
                        @foreach ([
                            'Catatan Berkas' => $pbiApbn->catatan_berkas,
                            'Kesimpulan/Rekomendasi Kelurahan' => $pbiApbn->kesimpulan_rekomendasi,
                            'Fasilitas Kesehatan' => $pbiApbn->nama_faskes,
                        ] as $label => $value)
                            @if ($value)
                                <div class="p-3 bg-surface-container/30 rounded-lg border border-outline-variant/30">
                                    <dt class="text-xs text-on-surface-variant mb-0.5">{{ $label }}</dt>
                                    <dd class="font-medium text-on-surface">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>
            @endif

            {{-- Riwayat Diagnosa (bisa berisi banyak entri, tidak menimpa) --}}
            @if ($pbiApbn->diagnosaLogs->isNotEmpty())
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-outline-variant/40">
                        <h3 class="text-sm font-bold flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-secondary">medical_information</span>
                            Riwayat Diagnosa
                        </h3>
                    </div>
                    <div class="divide-y divide-outline-variant/30">
                        @foreach ($pbiApbn->diagnosaLogs as $log)
                            <div class="px-5 py-3 text-sm">
                                <div class="flex items-center justify-between gap-3 flex-wrap">
                                    <span class="text-xs text-on-surface-variant">{{ $log->role_name }}</span>
                                    <span class="text-xs text-on-surface-variant whitespace-nowrap">{{ $log->created_at->format('d M Y - H:i') }}</span>
                                </div>
                                <p class="mt-1 text-on-surface">{{ $log->diagnosa }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Riwayat Proses --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">history</span>
                        Riwayat Proses
                    </h3>
                </div>
                <div class="divide-y divide-outline-variant/30">
                    @forelse ($pbiApbn->logs as $log)
                        <div class="px-5 py-3 text-sm">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <p class="font-semibold text-on-surface">{{ $log->task_name }}</p>
                                <span class="text-xs text-on-surface-variant whitespace-nowrap">
                                    {{ $log->created_at->format('d M Y - H:i') }}
                                </span>
                            </div>
                            <p class="text-xs text-on-surface-variant mt-0.5">{{ $log->role_name }}</p>
                            @if ($log->catatan)
                                <p class="text-sm mt-1 text-on-surface-variant">{{ $log->catatan }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-sm text-on-surface-variant">
                            Belum ada riwayat proses.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    @endif
@endsection
