@extends('layouts.masyarakat')

@section('title', 'Tracking Permohonan - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Tracking Permohonan')
@section('container', 'max-w-5xl')

@section('content')
    @php
        $tone = [
            'success' => 'bg-success-container text-on-success-container',
            'error' => 'bg-error-container text-on-error-container',
            'warning' => 'bg-warning-container text-on-warning-container',
        ];
    @endphp

    <div class="space-y-6">

        {{-- Pilih permohonan --}}
        <section class="rounded-2xl bg-white ring-1 ring-outline-variant/40 shadow-sm p-5">
            <h2 class="text-sm font-bold text-brand">Pilih Permohonan</h2>

            @if ($daftar->isEmpty())
                <div class="mt-4 py-8 text-center">
                    <span class="material-symbols-outlined text-[44px] text-outline-variant">inbox</span>
                    <p class="mt-2 text-sm font-semibold text-on-surface">Belum ada permohonan</p>
                    <p class="mt-1 text-sm text-on-surface-variant">Permohonan yang Anda ajukan akan muncul di sini.</p>
                </div>
            @else
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($daftar as $d)
                        @php $dipilih = $d['no'] === $no; @endphp
                        <a href="{{ route('masyarakat.tracking', ['no' => $d['no']]) }}"
                            class="flex items-center gap-3 rounded-xl border px-4 py-3 transition-colors
                                {{ $dipilih
                                    ? 'border-brand bg-brand-soft ring-2 ring-brand/30'
                                    : 'border-outline-variant hover:border-brand/50 hover:bg-surface' }}">
                            <span class="w-10 h-10 shrink-0 rounded-lg flex items-center justify-center
                                {{ $dipilih ? 'bg-brand text-white' : 'bg-brand-soft text-brand' }}">
                                <span class="material-symbols-outlined text-[22px]">
                                    {{ $d['jenis'] === 'DTSEN' ? 'groups' : 'health_and_safety' }}
                                </span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">{{ $d['jenis'] }}</p>
                                <p class="text-sm font-extrabold text-brand truncate">{{ $d['no'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $tone[$d['status_tone']] }}">
                                {{ $d['status_label'] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Tidak ditemukan --}}
        @if ($no !== '' && ! $hasil)
            <section class="rounded-2xl bg-white ring-1 ring-outline-variant/40 shadow-sm px-5 py-12 text-center">
                <span class="material-symbols-outlined text-[44px] text-outline-variant">search_off</span>
                <p class="mt-2 text-sm font-semibold text-on-surface">Permohonan tidak ditemukan</p>
                <p class="mt-1 text-sm text-on-surface-variant break-words">
                    Tidak ada permohonan dengan nomor <span class="font-semibold">{{ $no }}</span> di akun Anda.
                    Silakan pilih salah satu permohonan di atas.
                </p>
            </section>
        @endif

        {{-- Hasil --}}
        @if ($hasil)
            {{-- Ringkasan --}}
            <section class="rounded-2xl bg-white ring-1 ring-outline-variant/40 shadow-sm overflow-hidden">
                <header class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-outline-variant/40">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">{{ $hasil['jenis'] }}</p>
                        <h2 class="text-lg font-extrabold text-brand break-words">{{ $hasil['no'] }}</h2>
                    </div>
                    <span class="shrink-0 rounded-full px-4 py-1.5 text-xs font-bold {{ $tone[$hasil['status_tone']] }}">
                        {{ $hasil['status_label'] }}
                    </span>
                </header>

                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 px-5 py-4 text-sm">
                    <div class="min-w-0">
                        <dt class="text-xs text-on-surface-variant">Pemohon</dt>
                        <dd class="mt-0.5 font-semibold text-on-surface break-words">{{ $hasil['pemohon'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-on-surface-variant">Tgl Permohonan</dt>
                        <dd class="mt-0.5 font-semibold text-on-surface">{{ $hasil['tanggal']?->format('d/m/Y H:i') ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="text-xs text-on-surface-variant">Proses Saat Ini</dt>
                        <dd class="mt-0.5 font-semibold text-on-surface break-words">{{ $hasil['proses'] }}</dd>
                    </div>
                </dl>

                {{-- Dikembalikan petugas (belum berstatus ditolak) --}}
                @if (! empty($hasil['dikembalikan']) && $hasil['status_tone'] !== 'error')
                    <div class="mx-5 mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-warning-container px-4 py-3 text-sm text-on-warning-container">
                        <span>Permohonan dikembalikan oleh petugas. Silakan periksa dan perbaiki data Anda.</span>
                        <a href="{{ $hasil['url'] }}"
                            class="rounded-lg bg-brand px-3 py-1.5 text-xs font-semibold text-white">Perbaiki Data</a>
                    </div>
                @endif

                @if ($hasil['status_tone'] === 'error')
                    <div class="mx-5 mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-error-container px-4 py-3 text-sm text-on-error-container">
                        <span>Permohonan ditolak. Silakan periksa dan perbaiki data Anda.</span>
                        <a href="{{ $hasil['url'] }}"
                            class="rounded-lg bg-error px-3 py-1.5 text-xs font-semibold text-white">Perbaiki Data</a>
                    </div>
                @endif
            </section>

            {{-- Progres tahapan (disembunyikan untuk yang ditolak) --}}
            @if (! empty($hasil['steps']) && $hasil['status_tone'] !== 'error')
                <section class="rounded-2xl bg-white ring-1 ring-outline-variant/40 shadow-sm p-5">
                    <h3 class="text-sm font-extrabold text-brand mb-4">Progres Permohonan</h3>
                    <ol class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                        @foreach ($hasil['steps'] as $s)
                            <li class="flex sm:flex-1 sm:flex-col items-center sm:text-center gap-2">
                                <span class="w-7 h-7 shrink-0 rounded-full flex items-center justify-center text-xs font-bold
                                    {{ $s['state'] === 'done'
                                        ? 'bg-success-container text-on-success-container'
                                        : ($s['state'] === 'current'
                                            ? 'bg-brand text-white ring-4 ring-brand/20'
                                            : 'bg-surface text-on-surface-variant ring-1 ring-outline-variant') }}">
                                    @if ($s['state'] === 'done')
                                        <span class="material-symbols-outlined text-[16px]">check</span>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </span>
                                <span class="text-xs {{ $s['state'] === 'current' ? 'font-bold text-brand' : 'text-on-surface-variant' }}">
                                    {{ $s['label'] }}
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- Lokasi pengiriman permohonan (titik koordinat) — hanya PBI APBD,
                 karena form DTSEN tidak mengambil koordinat --}}
            {{-- @if ($hasil['jenis'] === 'PBI APBD')
                @php $lok = $hasil['lokasi']; @endphp
                <section class="rounded-2xl bg-white ring-1 ring-outline-variant/40 shadow-sm overflow-hidden">
                    <header class="flex items-center gap-3 px-5 py-4 border-b border-outline-variant/40">
                        <span class="w-10 h-10 shrink-0 rounded-xl bg-brand-soft text-brand flex items-center justify-center">
                            <span class="material-symbols-outlined text-[22px]">location_on</span>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-extrabold text-brand leading-tight">Lokasi Pengiriman Permohonan</h3>
                            <p class="text-xs text-on-surface-variant">Titik koordinat saat permohonan Anda dikirim.</p>
                        </div>
                    </header>

                    @if ($lok)
                        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,20rem)_minmax(0,1fr)]">
                            <dl class="p-5 space-y-4 text-sm">
                                <div>
                                    <dt class="text-xs text-on-surface-variant">Latitude</dt>
                                    <dd class="mt-0.5 font-semibold text-on-surface">{{ number_format($lok['lat'], 6, '.', '') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-on-surface-variant">Longitude</dt>
                                    <dd class="mt-0.5 font-semibold text-on-surface">{{ number_format($lok['lng'], 6, '.', '') }}</dd>
                                </div>
                                @if ($lok['waktu'])
                                    <div>
                                        <dt class="text-xs text-on-surface-variant">Waktu Dikirim</dt>
                                        <dd class="mt-0.5 font-semibold text-on-surface">{{ $lok['waktu']->format('d/m/Y H:i') }}</dd>
                                    </div>
                                @endif
                                <a href="https://www.google.com/maps?q={{ $lok['lat'] }},{{ $lok['lng'] }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-2 h-10 px-4 rounded-lg bg-brand text-white text-xs font-semibold hover:bg-primary transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                                    Buka di Google Maps
                                </a>
                            </dl>

                            <div class="border-t lg:border-t-0 lg:border-l border-outline-variant/40">
                                <iframe title="Peta lokasi permohonan"
                                    class="w-full h-64 lg:h-full min-h-[16rem]" loading="lazy"
                                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $lok['lng'] - 0.005 }}%2C{{ $lok['lat'] - 0.003 }}%2C{{ $lok['lng'] + 0.005 }}%2C{{ $lok['lat'] + 0.003 }}&layer=mapnik&marker={{ $lok['lat'] }}%2C{{ $lok['lng'] }}"></iframe>
                            </div>
                        </div>
                    @else
                        <div class="px-5 py-10 text-center">
                            <span class="material-symbols-outlined text-[40px] text-outline-variant">location_off</span>
                            <p class="mt-2 text-sm font-semibold text-on-surface">Lokasi belum tercatat</p>
                            <p class="mt-1 text-sm text-on-surface-variant">Permohonan ini belum memiliki titik koordinat.</p>
                        </div>
                    @endif
                </section>
            @endif --}}

            <div class="grid grid-cols-1 gap-6">

                {{-- Riwayat --}}
                <section class="rounded-2xl bg-white ring-1 ring-outline-variant/40 shadow-sm p-5">
                    <h3 class="text-sm font-extrabold text-brand mb-4">Riwayat Permohonan</h3>

                    @if ($hasil['logs']->isEmpty())
                        <p class="py-6 text-center text-sm text-on-surface-variant">Belum ada riwayat.</p>
                    @else
                        <ul class="space-y-4">
                            @foreach ($hasil['logs'] as $l)
                                <li class="flex gap-3">
                                    <span class="mt-1.5 w-2.5 h-2.5 shrink-0 rounded-full {{ $loop->first ? 'bg-brand' : 'bg-outline-variant' }}"></span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-on-surface break-words">{{ $l['tugas'] }}</p>
                                        <p class="text-xs text-on-surface-variant">
                                            {{ $l['waktu']?->format('d/m/Y H:i') ?? '-' }}
                                            @if ($l['peran'])
                                                &middot; {{ $l['peran'] }}
                                            @endif
                                        </p>
                                        @if ($l['catatan'])
                                            <p class="mt-1 rounded-lg bg-surface px-3 py-2 text-xs text-on-surface-variant break-words">
                                                {{ $l['catatan'] }}
                                            </p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        @endif
    </div>
@endsection
