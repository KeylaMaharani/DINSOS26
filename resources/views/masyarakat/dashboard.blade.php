{{-- resources/views/masyarakat/dashboard.blade.php --}}
@extends('layouts.masyarakat')

@section('title', 'Beranda - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Beranda')
@section('container', 'max-w-5xl')

@php
    $card = 'rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30';
    $user = auth()->user();
    $firstName = \Illuminate\Support\Str::of($user->name ?? 'Pengguna')->before(' ')->title();

    $semua = collect($layanan);
    $terdaftar = $semua->where('terdaftar', true);

    // Layanan yang recordnya sudah ada tetapi warga belum mulai mengisi (belum dihitung terdaftar)
    $belumMulai = $semua->filter(fn($l) => ($l['punya_record'] ?? false) && ! $l['terdaftar'])->values();

    $butuhAksi = $terdaftar->filter(fn($l) => $l['bisa_edit'] && ($l['dikembalikan'] || !$l['lengkap']));
    $belumLengkap = $butuhAksi->where('dikembalikan', false);
    $diperbaiki = $butuhAksi->where('dikembalikan', true);
    $sedangProses = $terdaftar->where('final', false);

    // Persentase kelengkapan per layanan; yang sudah 100% tidak ditampilkan di kartu atas
    $pctOf = fn($l) => (int) ($l['persen'] ?? ($l['lengkap'] ? 100 : 0));
    $belumPenuh = $terdaftar->filter(fn($l) => $pctOf($l) < 100)->take(2)->values();

    $gabung = fn($c) => $c->pluck('label')->join(', ', ' dan ');

    $badge = fn($status) => match (true) {
        in_array($status, ['disetujui', 'selesai'], true) => 'bg-success-container text-on-success-container',
        $status === 'ditolak' => 'bg-error-container text-on-error-container',
        default => 'bg-surface-container text-secondary',
    };

    // Judul section dengan garis aksen di kiri (gaya "Fitur Utama")
    $sectionTitle = 'flex items-center gap-2.5 text-lg font-extrabold text-on-surface before:content-[\'\'] before:block before:w-1 before:h-6 before:rounded-full before:bg-brand';
@endphp

@section('content')
    <div class="space-y-4 sm:space-y-5">

        {{-- ===== Kartu identitas ===== --}}
        <section class="rounded-3xl bg-surface-container-lowest shadow-lg ring-1 ring-outline-variant/30 overflow-hidden">
            <div class="flex items-center gap-3 px-4 py-3.5">
                <span class="w-11 h-11 rounded-xl bg-primary text-on-primary flex items-center justify-center text-sm font-extrabold shrink-0">DS</span>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-secondary truncate">Dinas Sosial Kota Bogor</p>
                    <p class="text-sm font-bold text-on-surface truncate">SOLID • Masyarakat</p>
                </div>
                <span class="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-warning-container text-on-warning-container text-xs font-bold">
                    <span class="material-symbols-outlined text-[16px]">verified_user</span>
                    Resmi
                </span>
            </div>

            <div class="border-t-4 border-secondary-container bg-gradient-to-br from-primary to-secondary text-on-primary">
                <div class="flex items-center gap-4 px-4 pt-5 pb-4">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-full ring-4 ring-secondary-container bg-primary flex items-center justify-center text-3xl font-extrabold">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-lg sm:text-xl font-extrabold leading-tight break-words">{{ $user->name ?? 'Pengguna' }}</p>
                        @if (!empty($user->username))
                            <p class="mt-0.5 text-sm font-bold text-secondary-container break-all">{{ $user->username }}</p>
                        @endif
                        <span class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-xs font-bold">
                            <span class="material-symbols-outlined text-[16px]">task_alt</span>
                            {{ $terdaftar->count() }} Layanan Terdaftar
                        </span>
                    </div>
                </div>

                {{-- Kelengkapan data: hanya persentase, layanan yang sudah 100% hilang dari sini --}}
                <div class="px-4 pb-5">
                    <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-secondary-container">Kelengkapan Data</p>

                    @if ($terdaftar->isEmpty())
                        <div class="rounded-2xl bg-white/15 px-4 py-4 text-center text-sm font-semibold">
                            Kamu belum mendaftar pelayanan apapun.
                            @foreach ($belumMulai as $l)
                                @if (($l['lengkapi_url'] ?? null) && $l['lengkapi_url'] !== '#')
                                    <div class="mt-3">
                                        <a href="{{ $l['lengkapi_url'] }}"
                                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-secondary-container text-on-secondary-container text-xs font-bold">
                                            <span class="material-symbols-outlined text-[16px]">edit_note</span>
                                            Mulai isi data {{ $l['label'] }}
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @elseif ($belumPenuh->isEmpty())
                        <div class="rounded-2xl bg-white/15 px-4 py-4 flex items-center justify-center gap-2 text-sm font-semibold">
                            <span class="material-symbols-outlined text-[20px] text-secondary-container">task_alt</span>
                            Semua data kamu sudah lengkap.
                        </div>
                    @else
                        <div class="grid gap-3 {{ $belumPenuh->count() > 1 ? 'grid-cols-2' : 'grid-cols-1' }}">
                            @foreach ($belumPenuh as $l)
                                @php
                                    $pct = $pctOf($l);
                                    $urlLengkapi = ($l['lengkapi_url'] ?? null) && $l['lengkapi_url'] !== '#' ? $l['lengkapi_url'] : '#';
                                @endphp
                                <a href="{{ $urlLengkapi }}" class="rounded-2xl bg-white/15 px-3.5 py-3 hover:bg-white/25 transition-colors">
                                    <div class="flex items-center gap-1.5 text-secondary-container">
                                        <span class="material-symbols-outlined text-[18px]">{{ $l['icon'] }}</span>
                                        <p class="text-xs font-bold truncate">{{ $l['label'] }}</p>
                                        @if ($l['draft'] ?? false)
                                            <span class="ml-auto shrink-0 rounded-full bg-secondary-container px-2 py-0.5 text-[10px] font-extrabold uppercase text-on-secondary-container">Draft</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-3xl font-extrabold leading-none">{{ $pct }}%</p>
                                    <div class="mt-2 h-1.5 rounded-full bg-white/25 overflow-hidden">
                                        <div class="h-full rounded-full bg-secondary-container" style="width: {{ $pct }}%"></div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-3 px-4 py-2.5 bg-black/20">
                    <p class="text-xs italic font-semibold text-secondary-container truncate">"Bersama SOLID, Melayani Warga Bogor"</p>
                    <a href="{{ route('masyarakat.profil') }}"
                        class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 text-sm font-semibold hover:bg-white/25 transition-colors">
                        <span class="material-symbols-outlined text-[16px]">person</span>
                        Profil
                    </a>
                </div>
            </div>
        </section>

        {{-- ===== Fitur Utama: grid ikon (otomatis menyesuaikan jumlah layanan, 4 kolom lalu turun ke baris berikutnya) ===== --}}
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="{{ $sectionTitle }}">Fitur Utama</h3>
                <span class="text-sm font-medium text-on-surface-variant">{{ $semua->count() }} Layanan →</span>
            </div>
            <div class="grid grid-cols-4 gap-2.5 sm:gap-3">
                @foreach ($semua as $l)
                    @php
                        $urlLengkapiFitur = ($l['lengkapi_url'] ?? null) && $l['lengkapi_url'] !== '#' ? $l['lengkapi_url'] : null;

                        if ($l['terdaftar']) {
                            $href = $urlLengkapiFitur ?? '#progress-' . $loop->index;
                        } elseif (($l['punya_record'] ?? false) && $urlLengkapiFitur) {
                            // Record sudah ada tapi belum mulai diisi: langsung ke form, jangan ke pendaftaran baru
                            $href = $urlLengkapiFitur;
                        } else {
                            $href = $l['shortcut'];
                        }
                    @endphp
                    <a href="{{ $href }}" class="relative {{ $card }} px-1 py-4 flex flex-col items-center gap-2 text-center hover:bg-surface-container-low transition-colors">
                        <span class="w-12 h-12 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                            <span class="material-symbols-outlined text-[26px]">{{ $l['icon'] }}</span>
                        </span>
                        <span class="text-[13px] font-medium text-on-surface leading-tight break-words px-1">{{ $l['label'] }}</span>
                        @if ($l['terdaftar'] && $l['bisa_edit'] && ($l['dikembalikan'] || !$l['lengkap']))
                            <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-warning ring-2 ring-white"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ===== Keterangan: bagian di bawah ini adalah progress pengajuan ===== --}}
        @if ($terdaftar->isNotEmpty())
            <div>
                <h3 class="{{ $sectionTitle }}">Progress Pengajuan</h3>
            </div>
        @endif

        {{-- ===== Progress pengajuan: terpisah per layanan ===== --}}
        @foreach ($terdaftar as $l)
            <section id="progress-{{ $loop->index }}" class="{{ $card }} p-4 sm:p-5 scroll-mt-20">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">alt_route</span>
                        Progress Pengajuan {{ $l['label'] }}
                    </h3>
                    <span class="text-xs text-on-surface-variant break-all">{{ $l['no'] }}</span>
                </div>

                @if ($l['draft'] ?? false)
                    {{-- Draft: baru terisi sebagian & belum diajukan ke petugas, jadi belum ada tahapan yang berjalan --}}
                    <div class="border border-warning/40 bg-warning-container/40 rounded-xl p-4 flex items-start gap-3">
                        <span class="material-symbols-outlined text-[18px] mt-0.5 shrink-0 text-on-warning-container">edit_note</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-on-warning-container">
                                Data {{ $l['label'] }} masih berupa <strong>draft</strong> ({{ $pctOf($l) }}% terisi) dan belum diajukan ke petugas.
                            </p>
                            @if (($l['lengkapi_url'] ?? null) && $l['lengkapi_url'] !== '#')
                                <a href="{{ $l['lengkapi_url'] }}"
                                   class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-primary text-on-primary text-xs font-semibold">
                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                    Lanjutkan mengisi
                                </a>
                            @endif
                        </div>
                    </div>
                @elseif (!$l['final'])
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
