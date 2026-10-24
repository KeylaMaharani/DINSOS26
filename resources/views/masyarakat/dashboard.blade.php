{{-- resources/views/masyarakat/dashboard.blade.php --}}
@extends('layouts.masyarakat')

@section('title', 'Beranda - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Beranda')
@section('container', 'max-w-5xl')

@php
    $card = 'rounded-2xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30';
    $firstName = \Illuminate\Support\Str::of(auth()->user()->name ?? 'Pengguna')->before(' ')->title();

    $semua = collect($layanan);
    $terdaftar = $semua->where('terdaftar', true);

    // Layanan yang datanya belum lengkap / diminta diperbaiki (dibedakan per layanan)
    $butuhAksi = $terdaftar->filter(fn($l) => $l['bisa_edit'] && ($l['dikembalikan'] || !$l['lengkap']));
    $belumLengkap = $butuhAksi->where('dikembalikan', false);
    $diperbaiki = $butuhAksi->where('dikembalikan', true);
    $sedangProses = $terdaftar->where('final', false);

    $gabung = fn($c) => $c->pluck('label')->join(', ', ' dan ');

    $badge = fn($status) => match (true) {
        in_array($status, ['disetujui', 'selesai'], true) => 'bg-success-container text-on-success-container',
        $status === 'ditolak' => 'bg-error-container text-on-error-container',
        default => 'bg-surface-container text-secondary',
    };
@endphp

@section('content')
    <div class="space-y-4 sm:space-y-5">

        {{-- ===== Sapaan: kalimat menyebut layanan mana yang perlu perhatian ===== --}}
        <section class="{{ $card }} p-5 sm:p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-secondary">Selamat datang</p>
            <h2 class="mt-1 text-xl sm:text-2xl font-extrabold text-brand break-words">Halo, {{ $firstName }}!</h2>
            <div class="mt-1 text-sm text-on-surface-variant space-y-0.5">
                @if ($terdaftar->isEmpty())
                    <p>Anda belum mendaftar layanan apa pun. Pilih layanan di bawah untuk mulai mendaftar.</p>
                @else
                    @if ($belumLengkap->isNotEmpty())
                        <p>Data pengajuan <strong class="text-on-warning-container">{{ $gabung($belumLengkap) }}</strong> Anda belum lengkap.</p>
                    @endif
                    @if ($diperbaiki->isNotEmpty())
                        <p>Petugas meminta Anda memperbaiki data pengajuan <strong class="text-on-warning-container">{{ $gabung($diperbaiki) }}</strong>.</p>
                    @endif
                    @if ($butuhAksi->isEmpty())
                        @if ($sedangProses->isNotEmpty())
                            <p>Pengajuan <strong class="text-on-surface">{{ $gabung($sedangProses) }}</strong> Anda sedang diproses.</p>
                        @else
                            <p>Semua pengajuan Anda sudah selesai ditangani. Terima kasih telah menggunakan layanan SOLID.</p>
                        @endif
                    @endif
                @endif
            </div>
        </section>

        {{-- ===== Kelengkapan data: satu kartu per layanan ===== --}}
        <section>
            <h3 class="mb-3 text-sm font-bold text-on-surface">Kelengkapan Data</h3>
            <div class="grid md:grid-cols-2 gap-3 sm:gap-4">
                @foreach ($layanan as $l)
                    <div class="{{ $card }} p-4 sm:p-5">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-surface-container text-secondary flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[22px]">{{ $l['icon'] }}</span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-on-surface">{{ $l['label'] }}</p>
                                @if ($l['terdaftar'])
                                    <p class="text-xs text-on-surface-variant break-all">{{ $l['no'] }}</p>
                                @endif
                            </div>
                            @if ($l['terdaftar'])
                                <span class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase {{ $badge($l['status']) }}">
                                    {{ $l['status_label'] }}
                                </span>
                            @endif
                        </div>

                        @if ($l['terdaftar'])
                            @if ($l['persen'] !== null)
                                <div class="mt-4 flex items-end justify-between gap-2">
                                    <p class="text-2xl font-extrabold text-on-surface">{{ $l['persen'] }}%</p>
                                    @if ($l['total'])
                                        <p class="text-[11px] text-on-surface-variant">{{ $l['terisi'] }}/{{ $l['total'] }} pertanyaan</p>
                                    @endif
                                </div>
                                <div class="mt-1 h-1.5 rounded-full bg-surface-container overflow-hidden">
                                    <div class="h-full rounded-full {{ $l['lengkap'] ? 'bg-success' : 'bg-brand' }}" style="width: {{ $l['persen'] }}%"></div>
                                </div>
                            @endif

                            @if ($l['lokasi'] !== null)
                                <p class="mt-3 flex items-center gap-1.5 text-xs {{ $l['lokasi'] ? 'text-success' : 'text-on-warning-container' }}">
                                    <span class="material-symbols-outlined text-[16px]">location_on</span>
                                    Titik lokasi rumah {{ $l['lokasi'] ? 'sudah terambil' : 'belum diambil' }}
                                </p>
                            @endif
                            <p class="mt-1 text-[11px] text-on-surface-variant">Diperbarui {{ optional($l['updated_at'])->diffForHumans() }}</p>

                            {{-- Tombol lengkapi / perbaiki: hanya muncul kalau memang perlu --}}
                            @if ($l['bisa_edit'] && ($l['dikembalikan'] || !$l['lengkap']))
                                @if ($l['dikembalikan'] && !empty($l['record']->catatan_kelurahan))
                                    <p class="mt-3 text-xs text-on-warning-container break-words">Catatan petugas: {{ $l['record']->catatan_kelurahan }}</p>
                                @elseif (!$l['dikembalikan'] && $l['label'] === 'PBI APBD')
                                    <p class="mt-3 text-xs text-on-surface-variant">
                                        Ambil titik lokasi dan jawab pertanyaan kondisi rumah tangga. Lampiran berkas dilengkapi petugas kelurahan saat kunjungan.
                                    </p>
                                @endif
                                @if ($l['lengkapi_url'] && $l['lengkapi_url'] !== '#')
                                    <a href="{{ $l['lengkapi_url'] }}"
                                        class="mt-3 w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 sm:py-2 rounded-full bg-primary text-white text-sm font-semibold hover:bg-secondary transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">edit_document</span>
                                        {{ $l['dikembalikan'] ? 'Perbaiki Data' : 'Lengkapi Data Sekarang' }}
                                    </a>
                                @endif
                            @endif
                        @else
                            {{-- Belum mendaftar layanan ini: cukup teks + shortcut --}}
                            <p class="mt-4 text-sm text-on-surface-variant">Anda belum pernah mendaftar layanan ini.</p>
                            <a href="{{ $l['shortcut'] }}"
                                class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold hover:bg-secondary transition-colors">
                                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                Daftar {{ $l['label'] }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===== Progress pengajuan: terpisah per layanan ===== --}}
        @foreach ($terdaftar as $l)
            <section class="{{ $card }} p-4 sm:p-5">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">alt_route</span>
                        Progress Pengajuan {{ $l['label'] }}
                    </h3>
                    <span class="text-xs text-on-surface-variant break-all">{{ $l['no'] }}</span>
                </div>

                @if (!$l['final'])
                    <ol class="flex flex-col md:flex-row">
                        @foreach ($l['steps'] as $i => $step)
                            <li class="relative flex items-start gap-3 md:flex-1 md:flex-col md:items-center md:gap-1.5 {{ $loop->last ? '' : 'pb-5 md:pb-0' }}"
                                @if ($step['state'] === 'current') aria-current="step" @endif>
                                @if (!$loop->last)
                                    <span aria-hidden="true"
                                        class="absolute left-[15px] top-8 bottom-0 w-0.5 md:bottom-auto md:left-1/2 md:top-[15px] md:h-0.5 md:w-full
                                               {{ $step['state'] === 'done' ? 'bg-success' : 'bg-outline-variant/50' }}"></span>
                                @endif
                                <div class="relative z-10 w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-bold
                                    {{ $step['state'] === 'done'
                                        ? 'bg-success text-white'
                                        : ($step['state'] === 'current'
                                            ? 'bg-brand text-white ring-4 ring-brand/20'
                                            : 'bg-surface-container text-on-surface-variant') }}">
                                    @if ($step['state'] === 'done')
                                        <span class="material-symbols-outlined text-[16px]">check</span>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </div>
                                <span class="text-sm pt-1 md:pt-0 md:text-[11px] md:leading-tight md:text-center md:px-1
                                    {{ $step['state'] === 'current' ? 'font-bold text-brand' : 'text-on-surface-variant' }}">
                                    {{ $step['label'] }}
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @elseif ($l['status'] === 'ditolak')
                    <div class="border border-error/40 bg-error-container/30 rounded-xl p-4 flex items-start gap-2">
                        <span class="material-symbols-outlined text-error text-[18px] mt-0.5 shrink-0">cancel</span>
                        <p class="text-sm text-on-error-container">Pengajuan {{ $l['label'] }} Anda <strong>ditolak</strong>.</p>
                    </div>
                @else
                    <div class="border border-success/40 bg-success-container/40 rounded-xl p-4 flex items-start gap-2">
                        <span class="material-symbols-outlined text-success text-[18px] mt-0.5 shrink-0">task_alt</span>
                        <p class="text-sm text-on-success-container">Pengajuan {{ $l['label'] }} Anda <strong>telah selesai</strong>.</p>
                    </div>
                @endif
            </section>
        @endforeach
    </div>
@endsection
