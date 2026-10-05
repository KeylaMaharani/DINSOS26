@extends('layouts.masyarakat')

@section('title', 'Pengajuan Saya - SOLID Dinas Sosial Kota Bogor')

@section('content')
    <h1 class="text-lg sm:text-xl font-bold text-primary mb-1">Pengajuan Saya</h1>
    <p class="text-sm text-on-surface-variant mb-5 sm:mb-6">Status &amp; riwayat pengajuan PBI APBN Anda.</p>

    @if (!$pbiApbn)
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 sm:p-6 text-center">
            <p class="text-sm text-on-surface-variant">Anda belum memiliki pengajuan PBI APBN.</p>
        </div>
    @else
        <div class="space-y-4 sm:space-y-5">

            {{-- Header --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div class="min-w-0">
                        <p class="text-xs text-on-surface-variant">No. Registrasi</p>
                        <p class="font-bold text-primary text-base sm:text-lg break-all">{{ $pbiApbn->no_registrasi }}</p>
                        <p class="text-sm text-on-surface-variant mt-1 break-words">
                            {{ $pbiApbn->nama_kepala_keluarga }}<span class="hidden sm:inline"> &mdash; </span><span
                                class="block sm:inline">NIK {{ $pbiApbn->nik_kepala_keluarga }}</span>
                        </p>
                    </div>
                    <span
                        class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold uppercase
                        {{ $pbiApbn->status === 'disetujui'
                            ? 'bg-success-container text-on-success-container'
                            : ($pbiApbn->status === 'ditolak'
                                ? 'bg-error-container text-on-error-container'
                                : 'bg-secondary/10 text-secondary') }}">
                        {{ $pbiApbn->statusLabel() }}
                    </span>
                </div>
            </div>

            {{-- Peringatan / ajakan melengkapi data --}}
            @if ($pbiApbn->dikembalikan_ke_masyarakat && $bisaEdit)
                <div class="border border-warning/40 bg-warning-container/40 rounded-lg p-4">
                    <p class="text-sm text-on-warning-container font-semibold mb-1">Petugas kelurahan meminta Anda
                        memperbaiki data</p>
                    @if ($pbiApbn->catatan_kelurahan)
                        <p class="text-xs text-on-warning-container mb-3 break-words">Catatan:
                            {{ $pbiApbn->catatan_kelurahan }}</p>
                    @endif
                    <a href="{{ route('masyarakat.pbi-apbn.lengkapi', $pbiApbn) }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 sm:py-2 rounded-full bg-primary text-white text-sm font-semibold">
                        <span class="material-symbols-outlined text-[16px]">edit_document</span> Perbaiki Data
                    </a>
                </div>
            @elseif (!$isLengkap && $bisaEdit)
                <div class="border border-warning/40 bg-warning-container/40 rounded-lg p-4">
                    <p class="text-sm text-on-warning-container font-semibold mb-1">Data Anda belum lengkap</p>
                    <p class="text-xs text-on-warning-container mb-3">
                        Ambil titik lokasi dan jawab pertanyaan kondisi rumah tangga
                        ({{ $terjawab }}/{{ $totalParameter }} terjawab).
                        Lampiran berkas akan dilengkapi oleh petugas kelurahan saat kunjungan.
                    </p>
                    <a href="{{ route('masyarakat.pbi-apbn.lengkapi', $pbiApbn) }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 sm:py-2 rounded-full bg-primary text-white text-sm font-semibold">
                        <span class="material-symbols-outlined text-[16px]">edit_document</span> Lengkapi Data Sekarang
                    </a>
                </div>
            @endif

            {{-- Progress Stepper: vertikal di HP, horizontal mulai layar >= 768px --}}
            @if (!$pbiApbn->isFinal())
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 sm:p-5">
                    <h3 class="text-sm font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">alt_route</span>
                        Progress Pengajuan
                    </h3>
                    <ol class="flex flex-col md:flex-row">
                        @foreach ($pbiApbn->progressSteps() as $i => $step)
                            <li class="relative flex items-start gap-3 md:flex-1 md:flex-col md:items-center md:gap-1.5 {{ $loop->last ? '' : 'pb-5 md:pb-0' }}"
                                @if ($step['state'] === 'current') aria-current="step" @endif>

                                {{-- Garis penghubung ke langkah berikutnya --}}
                                @if (!$loop->last)
                                    <span aria-hidden="true"
                                        class="absolute left-[15px] top-8 bottom-0 w-0.5
                                                 md:bottom-auto md:left-1/2 md:top-[15px] md:h-0.5 md:w-full
                                                 {{ $step['state'] === 'done' ? 'bg-success' : 'bg-outline-variant/50' }}"></span>
                                @endif

                                <div
                                    class="relative z-10 w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-bold
                                    {{ $step['state'] === 'done'
                                        ? 'bg-success text-white'
                                        : ($step['state'] === 'current'
                                            ? 'bg-primary text-white ring-4 ring-primary/20'
                                            : 'bg-surface-container text-on-surface-variant') }}">
                                    @if ($step['state'] === 'done')
                                        <span class="material-symbols-outlined text-[16px]">check</span>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </div>

                                <span
                                    class="text-sm pt-1 md:pt-0 md:text-[11px] md:leading-tight md:text-center md:px-1
                                    {{ $step['state'] === 'current' ? 'font-bold text-primary' : 'text-on-surface-variant' }}">
                                    {{ $step['label'] }}
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @elseif ($pbiApbn->status === 'ditolak')
                <div class="border border-error/40 bg-error-container/30 rounded-lg p-4 flex items-start gap-2">
                    <span class="material-symbols-outlined text-error text-[18px] mt-0.5 shrink-0">cancel</span>
                    <p class="text-sm text-on-error-container">Pengajuan Anda <strong>ditolak</strong>. Lihat riwayat proses
                        di bawah untuk catatan alasannya.</p>
                </div>
            @endif

            {{-- Data Diri Ringkas --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 sm:p-5">
                <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-secondary">assignment</span>
                    Data Permohonan
                </h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    @foreach ([
                        'No KK' => $pbiApbn->no_kk,
                        'Nama Kepala Keluarga' => $pbiApbn->nama_kepala_keluarga,
                        'Alamat' => $pbiApbn->alamat,
                        'Kecamatan' => $pbiApbn->kecamatan,
                        'Desa/Kelurahan' => $pbiApbn->desa_kelurahan,
                        'No Telp' => $pbiApbn->no_telp,
                    ] as $label => $value)
                        <div class="min-w-0 {{ $label === 'Alamat' ? 'sm:col-span-2' : '' }}">
                            <dt class="text-xs text-on-surface-variant">{{ $label }}</dt>
                            <dd class="font-medium break-words">{{ $value ?: '-' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Jawaban kondisi rumah tangga (tanpa indeks/bobot/skor) --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 sm:p-5">
                <div class="flex items-center justify-between gap-x-3 gap-y-1 flex-wrap mb-3">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">checklist</span>
                        Jawaban Kondisi Rumah Tangga
                    </h3>
                    @if ($bisaEdit)
                        <a href="{{ route('masyarakat.pbi-apbn.lengkapi', $pbiApbn) }}"
                            class="text-xs font-semibold text-primary underline py-1">Ubah Jawaban</a>
                    @endif
                </div>
                @php $jawabans = $pbiApbn->jawabans->sortBy('urutan'); @endphp
                @if ($jawabans->isEmpty())
                    <p class="text-sm text-on-surface-variant">Belum ada jawaban.</p>
                @else
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        @foreach ($jawabans as $j)
                            <div class="min-w-0">
                                <dt class="text-xs text-on-surface-variant">{{ $j->parameter_nama }}</dt>
                                <dd class="font-medium break-words">{{ $j->jawaban_label }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>

            {{-- Informasi tambahan dari petugas (read-only) --}}
            @if ($pbiApbn->catatan_berkas || $pbiApbn->kesimpulan_rekomendasi || $pbiApbn->nama_faskes)
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 sm:p-5">
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
                                    <dd class="font-medium text-on-surface break-words">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>
            @endif

            {{-- Riwayat Diagnosa (bisa berisi banyak entri, tidak menimpa) --}}
            @if ($pbiApbn->diagnosaLogs->isNotEmpty())
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
                    <div class="px-4 sm:px-5 py-4 border-b border-outline-variant/40">
                        <h3 class="text-sm font-bold flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-secondary">medical_information</span>
                            Riwayat Diagnosa
                        </h3>
                    </div>
                    <div class="divide-y divide-outline-variant/30">
                        @foreach ($pbiApbn->diagnosaLogs as $log)
                            <div class="px-4 sm:px-5 py-3 text-sm">
                                <div class="flex items-center justify-between gap-x-3 gap-y-0.5 flex-wrap">
                                    <span class="text-xs text-on-surface-variant">{{ $log->role_name }}</span>
                                    <span
                                        class="text-xs text-on-surface-variant whitespace-nowrap">{{ $log->created_at->format('d M Y - H:i') }}</span>
                                </div>
                                <p class="mt-1 text-on-surface break-words">{{ $log->diagnosa }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Riwayat Proses --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
                <div class="px-4 sm:px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">history</span>
                        Riwayat Proses
                    </h3>
                </div>
                <div class="divide-y divide-outline-variant/30">
                    @forelse ($pbiApbn->logs as $log)
                        <div class="px-4 sm:px-5 py-3 text-sm">
                            <div class="flex items-start justify-between gap-x-3 gap-y-0.5 flex-wrap">
                                <p class="font-semibold text-on-surface min-w-0 break-words">{{ $log->task_name }}</p>
                                <span class="text-xs text-on-surface-variant whitespace-nowrap">
                                    {{ $log->created_at->format('d M Y - H:i') }}
                                </span>
                            </div>
                            <p class="text-xs text-on-surface-variant mt-0.5">{{ $log->role_name }}</p>
                            @if ($log->catatan)
                                <p class="text-sm mt-1 text-on-surface-variant break-words">{{ $log->catatan }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="px-4 sm:px-5 py-6 text-center text-sm text-on-surface-variant">
                            Belum ada riwayat proses.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    @endif
@endsection
