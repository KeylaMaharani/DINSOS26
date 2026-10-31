@extends('layouts.masyarakat')

@section('title', 'Permohonan Saya - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Permohonan Saya')
@section('back', route('masyarakat.dashboard'))
@section('container', 'max-w-6xl')

@section('content')
    @php
        $tone = [
            'success' => 'bg-success-container text-on-success-container',
            'error' => 'bg-error-container text-on-error-container',
            'warning' => 'bg-warning-container text-on-warning-container',
        ];
        $adaTracking = \Illuminate\Support\Facades\Route::has('masyarakat.tracking');
        $sections = [
            ['judul' => 'PBI APBD', 'icon' => 'health_and_safety', 'rows' => $pbi],
            ['judul' => 'DTSEN', 'icon' => 'groups', 'rows' => $dtsen],
        ];
    @endphp

    <div class="space-y-6">
        @foreach ($sections as $sec)
            <section x-data="{ q: '' }"
                class="rounded-3xl bg-white ring-1 ring-outline-variant/40 shadow-sm overflow-hidden">

                {{-- Judul + pencarian --}}
                <header class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-outline-variant/40">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-10 h-10 shrink-0 rounded-xl bg-brand-soft text-brand flex items-center justify-center">
                            <span class="material-symbols-outlined text-[22px]">{{ $sec['icon'] }}</span>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-extrabold text-brand leading-tight">{{ $sec['judul'] }}</h2>
                            <p class="text-xs text-on-surface-variant">{{ $sec['rows']->count() }} permohonan</p>
                        </div>
                    </div>

                    @if ($sec['rows']->isNotEmpty())
                        <div class="relative w-full sm:w-64">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant">search</span>
                            <input type="search" x-model="q" placeholder="Cari no. register, nama, alamat..."
                                class="w-full h-10 pl-10 pr-3 rounded-xl border border-outline-variant bg-surface text-sm
                                       focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" />
                        </div>
                    @endif
                </header>

                @if ($sec['rows']->isEmpty())
                    {{-- Kosong --}}
                    <div class="px-5 py-12 text-center">
                        <span class="material-symbols-outlined text-[40px] text-outline-variant">folder_open</span>
                        <p class="mt-2 text-sm text-on-surface-variant">Belum ada permohonan {{ $sec['judul'] }}.</p>
                    </div>
                @else
                    {{-- Tabel (layar >= md) --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-surface-container-low text-left text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">
                                    <th class="px-5 py-3">Aksi</th>
                                    <th class="px-4 py-3">No Register</th>
                                    <th class="px-4 py-3">Tgl Permohonan</th>
                                    <th class="px-4 py-3">Pemohon</th>
                                    <th class="px-4 py-3">Alamat</th>
                                    <th class="px-4 py-3">Proses Saat Ini</th>
                                    <th class="px-5 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/30">
                                @foreach ($sec['rows'] as $r)
                                    <tr x-show="!q || $el.textContent.toLowerCase().includes(q.toLowerCase())"
                                        class="hover:bg-surface transition-colors align-top">
                                        <td class="px-5 py-3">
                                            <div class="flex flex-col gap-1.5 w-32">
                                                <a href="{{ $r['url'] }}"
                                                    class="inline-flex items-center justify-center gap-1 rounded-lg bg-brand px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary transition-colors">
                                                    <span class="material-symbols-outlined text-[16px]">edit_note</span>
                                                    Lihat / Lengkapi
                                                </a>
                                                @if ($adaTracking)
                                                    <a href="{{ route('masyarakat.tracking') }}"
                                                        class="inline-flex items-center justify-center gap-1 rounded-lg border border-brand/30 px-3 py-1.5 text-xs font-semibold text-brand hover:bg-brand-soft transition-colors">
                                                        <span class="material-symbols-outlined text-[16px]">search</span>
                                                        Tracking
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap">{{ $r['no'] }}</td>
                                        <td class="px-4 py-3 text-on-surface-variant whitespace-nowrap">
                                            {{ $r['tanggal']?->format('d/m/Y H:i') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-on-surface">{{ $r['pemohon'] }}</td>
                                        <td class="px-4 py-3 text-on-surface-variant max-w-xs">{{ $r['alamat'] }}</td>
                                        <td class="px-4 py-3 text-on-surface-variant">{{ $r['proses'] ?? '-' }}</td>
                                        <td class="px-5 py-3">
                                            <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $tone[$r['status_tone']] }}">
                                                {{ $r['status_label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Kartu (layar kecil) --}}
                    <div class="md:hidden divide-y divide-outline-variant/30">
                        @foreach ($sec['rows'] as $r)
                            <article x-show="!q || $el.textContent.toLowerCase().includes(q.toLowerCase())"
                                class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-on-surface break-words">{{ $r['no'] }}</p>
                                        <p class="text-xs text-on-surface-variant">{{ $r['tanggal']?->format('d/m/Y H:i') ?? '-' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $tone[$r['status_tone']] }}">
                                        {{ $r['status_label'] }}
                                    </span>
                                </div>

                                <dl class="text-sm space-y-1">
                                    <div class="flex gap-2">
                                        <dt class="w-20 shrink-0 text-on-surface-variant">Pemohon</dt>
                                        <dd class="text-on-surface break-words min-w-0">{{ $r['pemohon'] }}</dd>
                                    </div>
                                    <div class="flex gap-2">
                                        <dt class="w-20 shrink-0 text-on-surface-variant">Alamat</dt>
                                        <dd class="text-on-surface break-words min-w-0">{{ $r['alamat'] }}</dd>
                                    </div>
                                    @if ($r['proses'])
                                        <div class="flex gap-2">
                                            <dt class="w-20 shrink-0 text-on-surface-variant">Proses</dt>
                                            <dd class="text-on-surface break-words min-w-0">{{ $r['proses'] }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                <div class="flex gap-2">
                                    <a href="{{ $r['url'] }}"
                                        class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-white">
                                        <span class="material-symbols-outlined text-[16px]">edit_note</span>
                                        Lihat / Lengkapi
                                    </a>
                                    @if ($adaTracking)
                                        <a href="{{ route('masyarakat.tracking') }}"
                                            class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg border border-brand/30 px-3 py-2 text-xs font-semibold text-brand">
                                            <span class="material-symbols-outlined text-[16px]">search</span>
                                            Tracking
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach
    </div>
@endsection
