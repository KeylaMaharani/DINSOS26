@extends('layouts.admin')

@section('title', 'Data Desil Kependudukan')
@section('page_title', 'Entri Data')

@use('App\Models\DataPendudukAsli', 'P')

@section('content')
    @php
        $fmt = fn ($n) => number_format((int) $n, 0, ',', '.');
        $pct = fn ($n) => $stats['total'] > 0 ? number_format($n / $stats['total'] * 100, 1, ',', '.') : '0';

        // warna per desil (badge + segmen bar)
        $warnaDesil = [
            0 => 'bg-slate-500', 1 => 'bg-red-700', 2 => 'bg-red-500', 3 => 'bg-orange-500', 4 => 'bg-amber-500',
            5 => 'bg-blue-500', 6 => 'bg-indigo-500', 7 => 'bg-teal-500', 8 => 'bg-emerald-500',
            9 => 'bg-green-500', 10 => 'bg-green-700',
        ];
        $chip = [
            'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'sky' => 'bg-sky-50 text-sky-700 border-sky-200',
            'amber' => 'bg-amber-50 text-amber-700 border-amber-200', 'slate' => 'bg-slate-100 text-slate-700 border-slate-200',
            'purple' => 'bg-purple-50 text-purple-700 border-purple-200', 'rose' => 'bg-rose-50 text-rose-700 border-rose-200',
            'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'orange' => 'bg-orange-50 text-orange-700 border-orange-200',
            'red' => 'bg-red-50 text-red-700 border-red-200',
        ];
        $labelKolom = P::KOLOM;

        // kelas dasar input (focus ring lembut agar terasa "hidup")
        $input = 'w-full rounded-xl border border-outline-variant/50 bg-white px-3 py-2.5 text-sm text-on-surface placeholder:text-on-surface-variant/50 outline-none transition-all duration-200 focus:border-brand focus:ring-4 focus:ring-brand/15';

        // kartu statistik: [judul, angka, sub, ikon, gradien ikon, warna dekor]
        $kartu = [
            ['Total Data Penduduk', $fmt($stats['total']), 'Non-Desil (Desil 0): ' . $fmt($stats['non_desil']) . ' jiwa', 'groups', 'from-sky-500 to-blue-600', 'bg-sky-200'],
            ['Prioritas Desil 1 - 4', $fmt($stats['prioritas']), $pct($stats['prioritas']) . '% dari total', 'target', 'from-rose-500 to-red-600', 'bg-rose-200'],
            ['Desil 5 - 10 (Mampu)', $fmt($stats['mampu']), $pct($stats['mampu']) . '% dari total', 'trending_up', 'from-indigo-500 to-violet-600', 'bg-indigo-200'],
            ['Verifikasi Lapangan', $fmt($stats['gc_kk']), 'Keluarga Ground Check 2026', 'fact_check', 'from-emerald-500 to-teal-600', 'bg-emerald-200'],
            ['Penerima PBI-JK', $fmt($stats['pbi']), 'Penerima PKH: ' . $fmt($stats['pkh']) . ' jiwa', 'volunteer_activism', 'from-amber-500 to-orange-600', 'bg-amber-200'],
        ];

        // desil dengan populasi terbanyak (keterangan di bawah bar)
        $desilMax = 0;
        $nMax = 0;
        foreach (range(0, 10) as $d) {
            $v = (int) ($stats['sebaran'][$d] ?? 0);
            if ($v > $nMax) {
                $nMax = $v;
                $desilMax = $d;
            }
        }
    @endphp

    <style>
        @media (prefers-reduced-motion: no-preference) {
            @keyframes desilFadeUp {
                from { opacity: 0; transform: translateY(16px) scale(.98); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            .anim-up { animation: desilFadeUp .55s cubic-bezier(.22, 1, .36, 1) backwards; }
            @keyframes desilFloat {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                50% { transform: translateY(-10px) rotate(8deg); }
            }
            .anim-float { animation: desilFloat 7s ease-in-out infinite; }
        }
        /* progress bar tipis saat data dimuat */
        @keyframes desilBar {
            from { transform: translateX(-100%); }
            to { transform: translateX(300%); }
        }
        .bar-load { animation: desilBar 1s linear infinite; }
        .nice-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
        .nice-scroll::-webkit-scrollbar-thumb { background: rgb(148 163 184 / .4); border-radius: 999px; }
        .nice-scroll::-webkit-scrollbar-thumb:hover { background: rgb(148 163 184 / .65); }
        .nice-scroll::-webkit-scrollbar-track { background: transparent; }
        [x-cloak] { display: none !important; }
    </style>

    <div id="desil-root" x-data="{ detail: null, data: @js($detail), labels: @js($labelKolom), loading: false }">

        {{-- ===== Hero header ===== --}}
        <div class="anim-up relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-600 via-teal-700 to-cyan-800 px-6 py-7 pb-16 shadow-xl shadow-emerald-900/20 sm:px-8">
            <div class="pointer-events-none absolute -top-20 -right-14 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/4 h-56 w-56 rounded-full bg-teal-300/20 blur-3xl"></div>
            <div class="anim-float pointer-events-none absolute top-9 right-1/3 hidden h-16 w-16 rounded-full border-2 border-dashed border-white/25 sm:block"></div>
            <span class="material-symbols-outlined pointer-events-none absolute -bottom-7 right-10 hidden select-none text-[130px] leading-none text-white/10 lg:block" aria-hidden="true">groups</span>

            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.25em] text-teal-100/90">
                        <span class="material-symbols-outlined text-[15px]">insights</span>
                        Dashboard Kesejahteraan Sosial &bull; Kota Bogor
                    </p>
                    <h2 class="mt-2 flex flex-wrap items-center gap-3 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        Data Desil Kependudukan
                        <span class="rounded-full border border-white/25 bg-white/15 px-3 py-1 text-[11px] font-semibold text-white shadow-sm backdrop-blur">Master Desil 1 - 10</span>
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm text-emerald-50/85">
                        Basis data seluruh penduduk Kota Bogor (<b class="font-bold text-white">{{ $fmt($stats['total']) }}</b> jiwa), terintegrasi peringkat desil &amp; hasil Ground Check.
                    </p>
                </div>
                <div class="relative flex flex-wrap gap-2">
                    <a id="link-export" data-base="{{ route('data-desil.export') }}" href="{{ route('data-desil.export', request()->query()) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-teal-700 shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:bg-teal-50 active:translate-y-0">
                        <span class="material-symbols-outlined text-[18px]">download</span> Export Excel (.csv)
                    </a>
                    <a id="link-refresh" data-base="{{ route('data-desil.index') }}" href="{{ route('data-desil.index', array_merge(request()->query(), ['refresh' => 1])) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-xs font-bold text-white backdrop-blur transition-all duration-200 hover:-translate-y-0.5 hover:bg-white/20 active:translate-y-0">
                        <span class="material-symbols-outlined text-[18px]">refresh</span> Segarkan
                    </a>
                </div>
            </div>
        </div>

        {{-- ===== Kartu statistik (mengapung di atas hero) ===== --}}
        <div class="relative z-10 -mt-10 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-5">
            @foreach ($kartu as $idx => [$judul, $angka, $sub, $ikon, $grad, $tint])
                <div class="anim-up group relative overflow-hidden rounded-2xl bg-white p-4 shadow-lg ring-1 ring-black/5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                    style="animation-delay: {{ $idx * 70 }}ms">
                    <div class="pointer-events-none absolute -right-7 -top-9 h-24 w-24 rounded-full {{ $tint }} opacity-50 blur-2xl transition-transform duration-500 group-hover:scale-125"></div>
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">{{ $judul }}</p>
                            <p class="mt-1 bg-gradient-to-r {{ $grad }} bg-clip-text text-2xl font-extrabold tracking-tight text-transparent">{{ $angka }}</p>
                            <p class="mt-1.5 text-[11px] leading-snug text-on-surface-variant">{{ $sub }}</p>
                        </div>
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br {{ $grad }} text-white shadow-md transition-transform duration-300 group-hover:rotate-6 group-hover:scale-110">
                            <span class="material-symbols-outlined text-[22px]">{{ $ikon }}</span>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ===== Filter (satu form GET, diterapkan via AJAX tanpa reload halaman) ===== --}}
        <form method="GET" action="{{ route('data-desil.index') }}" id="form-desil"
            @submit.prevent="clearTimeout(timer); desilMuat(desilUrlForm($el))"
            @change="auto($event)" @input.debounce.700ms="autoTeks($event)"
            x-data="{
                desil: @js($filters['desil']),
                desilAwal: @js($filters['desil']),
                bukaDesil: false,
                timer: null,
                // Terapkan filter otomatis (tanpa reload halaman)
                kirim() {
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => desilMuat(desilUrlForm(document.getElementById('form-desil'))), 60);
                },
                auto(e) {
                    const t = e.target;
                    if (t.closest('[data-desil-dropdown]')) return; // multi-pilih desil: dikirim saat ditutup
                    if (t.tagName === 'SELECT' || t.type === 'checkbox') this.kirim();
                },
                autoTeks(e) {
                    const t = e.target;
                    if (t.tagName === 'INPUT' && (t.type === 'text' || t.type === 'number')) this.kirim();
                },
                tutupDesil() {
                    this.bukaDesil = false;
                    if (JSON.stringify(this.desil) !== JSON.stringify(this.desilAwal)) {
                        this.desilAwal = [...this.desil];
                        this.kirim();
                    }
                },
                kec: @js($filters['kecamatan']),
                kel: @js($filters['kelurahan']),
                wil: @js($wilayah),
                get daftarKel() { return this.kec && this.wil[this.kec] ? this.wil[this.kec] : [...new Set(Object.values(this.wil).flat())].sort(); },
                toggle(d) { this.desil = this.desil.includes(d) ? this.desil.filter(x => x !== d) : [...this.desil, d].sort((a, b) => a - b); },
                set(arr) { this.desil = arr; this.desilAwal = [...arr]; this.$nextTick(() => this.kirim()); }
            }"
            class="anim-up mt-5 rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-md sm:p-6">

            {{-- Header filter --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-md">
                        <span class="material-symbols-outlined text-[20px]">tune</span>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-on-surface">Filter &amp; Pencarian Data</h3>
                        <p class="text-[11px] text-on-surface-variant">Semua perubahan langsung diterapkan otomatis, tanpa perlu klik tombol</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                    <span class="material-symbols-outlined text-[14px]">bolt</span> Auto-Filter Aktif
                </span>
            </div>

            {{-- Sebaran desil (klik untuk filter cepat) --}}
            <div class="mt-5">
                <div class="mb-2.5 flex flex-wrap items-center justify-between gap-2">
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-on-surface">
                        <span class="grid h-6 w-6 place-items-center rounded-lg bg-brand-soft text-brand">
                            <span class="material-symbols-outlined text-[15px]">stacked_bar_chart</span>
                        </span>
                        Distribusi peringkat desil kependudukan (0 s.d. 10)
                    </p>
                    <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                        <button type="button" @click="set([])" class="rounded-full bg-gradient-to-r from-slate-700 to-slate-900 px-3 py-1.5 text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">Semua</button>
                        <button type="button" @click="set([1,2])" class="rounded-full border border-brand/20 bg-brand-soft px-3 py-1.5 text-brand transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">D1 &amp; D2</button>
                        <button type="button" @click="set([1,2,3,4])" class="rounded-full bg-gradient-to-r from-rose-500 to-red-600 px-3 py-1.5 text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">Prioritas (D1-D4)</button>
                        <button type="button" @click="set([0])" class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-slate-600 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm">Non Desil (0)</button>
                        @for ($d = 1; $d <= 10; $d++)
                            <button type="button" @click="toggle({{ $d }}); desilAwal = [...desil]; $nextTick(() => kirim())"
                                :class="desil.includes({{ $d }}) ? 'ring-2 ring-brand shadow-md bg-white' : ''"
                                class="rounded-full border border-outline-variant/50 bg-white px-2.5 py-1.5 text-on-surface-variant transition-all duration-200 hover:-translate-y-0.5 hover:border-brand/50 hover:text-brand">D{{ $d }}</button>
                        @endfor
                    </div>
                </div>
                <div class="flex h-3.5 w-full gap-[3px]">
                    @foreach (range(0, 10) as $d)
                        @php $n = $stats['sebaran'][$d] ?? 0; @endphp
                        @if ($n > 0)
                            <button type="button" @click="set([{{ $d }}])"
                                class="{{ $warnaDesil[$d] }} min-w-[6px] origin-bottom cursor-pointer rounded-full transition-all duration-300 hover:scale-y-125 hover:brightness-110"
                                style="width: {{ $n / max($stats['total'], 1) * 100 }}%"
                                title="Desil {{ $d }}: {{ $fmt($n) }} jiwa ({{ $pct($n) }}%) - klik untuk filter"></button>
                        @endif
                    @endforeach
                </div>
                <div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-1 text-[10px] text-on-surface-variant">
                    @if ($nMax > 0)
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full {{ $warnaDesil[$desilMax] }}"></span>
                            Desil terbanyak: <b class="text-on-surface">D{{ $desilMax }}</b> ({{ $fmt($nMax) }} jiwa)
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px] text-brand">touch_app</span> Klik chip atau bar untuk memfilter cepat
                    </span>
                </div>
            </div>

            <template x-for="d in desil" :key="d"><input type="hidden" name="desil[]" :value="d"></template>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">Pencarian (NIK / No. KK / Nama)</label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant/60">person_search</span>
                        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Ketik NIK, No. KK, atau nama penduduk..."
                            class="{{ $input }} pl-10" />
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">Kecamatan</label>
                    <select name="kecamatan" x-model="kec" @change="kel = ''" class="{{ $input }} cursor-pointer">
                        <option value="">-- Semua Kecamatan --</option>
                        <template x-for="k in Object.keys(wil)" :key="k">
                            <option :value="k" x-text="k" :selected="k === kec"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">Kelurahan / Desa</label>
                    <select name="kelurahan" x-model="kel" class="{{ $input }} cursor-pointer">
                        <option value="">-- Semua Kelurahan --</option>
                        <template x-for="k in daftarKel" :key="k">
                            <option :value="k" x-text="k" :selected="k === kel"></option>
                        </template>
                    </select>
                </div>

                {{-- Multi-pilih desil --}}
                <div class="relative md:col-span-2" data-desil-dropdown @click.outside="if (bukaDesil) tutupDesil()">
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">Peringkat Desil (Multi-Pilih)</label>
                    <button type="button" @click="bukaDesil ? tutupDesil() : (bukaDesil = true)"
                        class="flex w-full items-center justify-between rounded-xl border border-outline-variant/50 bg-white px-3 py-2.5 text-left text-sm transition-all duration-200 hover:border-brand/60 focus:ring-4 focus:ring-brand/15">
                        <span class="truncate text-on-surface" x-text="desil.length ? desil.length + ' desil terpilih (' + desil.map(d => 'D' + d).join(', ') + ')' : '-- Semua Desil (0 - 10) --'"></span>
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant transition-transform duration-300" :class="bukaDesil ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="bukaDesil" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="nice-scroll absolute z-20 mt-1.5 max-h-72 w-full space-y-1 overflow-y-auto rounded-2xl border border-outline-variant/40 bg-white p-2 shadow-2xl">
                        @foreach (range(0, 10) as $d)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 transition-colors hover:bg-surface-container">
                                <input type="checkbox" :checked="desil.includes({{ $d }})" @change="toggle({{ $d }})" class="h-4 w-4 rounded accent-brand" />
                                <span class="w-8 rounded-md py-0.5 text-center text-[11px] font-bold text-white {{ $warnaDesil[$d] }}">D{{ $d }}</span>
                                <span class="flex-1 text-sm">
                                    <span class="font-semibold text-on-surface">{{ $d === 0 ? 'Non Desil (0)' : 'Desil ' . $d }}</span>
                                    <span class="block text-[11px] text-on-surface-variant">{{ P::DESIL_LABEL[$d] }}</span>
                                </span>
                                <span class="rounded-full bg-surface-container px-2 py-0.5 text-xs tabular-nums text-on-surface-variant">{{ $fmt($stats['sebaran'][$d] ?? 0) }}</span>
                            </label>
                        @endforeach
                        <div class="sticky bottom-0 flex items-center justify-between rounded-xl border-t border-outline-variant/30 bg-white/95 px-3 pt-2 backdrop-blur">
                            <span class="text-xs font-medium text-on-surface-variant" x-text="desil.length + ' desil dipilih'"></span>
                            <button type="button" @click="tutupDesil()" class="rounded-lg bg-gradient-to-r from-teal-500 to-emerald-600 px-4 py-1.5 text-xs font-bold text-white shadow-md transition-all hover:brightness-105 hover:shadow-lg">Selesai</button>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">RW / RT</label>
                    <div class="flex gap-2">
                        <input type="text" name="rw" value="{{ $filters['rw'] }}" placeholder="No RW..." class="{{ $input }}" />
                        <input type="text" name="rt" value="{{ $filters['rt'] }}" placeholder="No RT..." class="{{ $input }}" />
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">Rentang Umur (Tahun)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="umur_min" value="{{ $filters['umur_min'] }}" min="0" placeholder="Min" class="{{ $input }}" />
                        <span class="text-on-surface-variant/60">-</span>
                        <input type="number" name="umur_max" value="{{ $filters['umur_max'] }}" min="0" placeholder="Max" class="{{ $input }}" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-6 md:col-span-2">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-on-surface transition-colors hover:text-brand"><input type="checkbox" name="kepala_kk" value="1" @checked($filters['kepala_kk']) class="h-4 w-4 rounded accent-brand"> Berdasarkan KK (Kepala)</label>
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-on-surface transition-colors hover:text-brand"><input type="checkbox" name="usia_kerja" value="1" @checked($filters['usia_kerja']) class="h-4 w-4 rounded accent-brand"> Usia Kerja (15-64)</label>
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">Status Ground Check</label>
                    <select name="gc" class="{{ $input }} cursor-pointer">
                        <option value="">-- Semua Status GC --</option>
                        <option value="sudah" @selected($filters['gc'] === 'sudah')>Terverifikasi</option>
                        <option value="belum" @selected($filters['gc'] === 'belum')>Belum GC</option>
                    </select>
                </div>
            </div>

            {{-- Program bansos aktif --}}
            <div class="mt-5">
                <p class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">
                    <span class="material-symbols-outlined text-[15px] text-brand">volunteer_activism</span> Program Bansos Aktif
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach (P::BANSOS as $key => [$kolom, $label, $warna])
                        <label class="cursor-pointer">
                            <input type="checkbox" name="bansos[]" value="{{ $key }}" class="sr-only peer" @checked(in_array($key, $filters['bansos'], true)) />
                            <span class="inline-block rounded-full border border-outline-variant/50 bg-surface-container-low px-3 py-1.5 text-[11px] font-semibold text-on-surface-variant transition-all duration-200 hover:-translate-y-0.5 hover:border-brand/50 hover:text-brand peer-checked:border-transparent peer-checked:bg-brand peer-checked:text-white peer-checked:shadow-md">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-end gap-3 border-t border-dashed border-outline-variant/40 pt-4">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <span class="mr-auto inline-flex items-center gap-1.5 rounded-full bg-surface-container px-3 py-1.5 text-[11px] font-medium text-on-surface-variant">
                    <span class="material-symbols-outlined text-[14px] text-emerald-500">bolt</span>
                    Filter diterapkan otomatis
                </span>
                <a href="{{ route('data-desil.index') }}" class="text-sm font-semibold text-on-surface-variant underline-offset-4 transition-colors hover:text-rose-600 hover:underline">Reset</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 transition-all duration-200 hover:-translate-y-0.5 hover:brightness-105 hover:shadow-xl active:translate-y-0">
                    <span class="material-symbols-outlined text-[18px]">filter_alt</span> Terapkan Filter
                </button>
            </div>
        </form>

        {{-- ===== Tabel (bagian dalam #hasil-desil diganti via AJAX) ===== --}}
        <div id="tabel-desil" class="anim-up relative mt-5 overflow-hidden rounded-2xl border border-outline-variant/40 bg-white shadow-md">
            {{-- progress bar loading --}}
            <div x-show="loading" x-cloak class="pointer-events-none absolute inset-x-0 top-0 z-10 h-1 overflow-hidden bg-emerald-100">
                <div class="bar-load h-full w-1/3 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500"></div>
            </div>

            <div id="hasil-desil" @click="desilLink($event)"
                class="transition-opacity duration-200" :class="loading ? 'opacity-60' : 'opacity-100'">

                <script type="application/json" id="detail-json">{!! json_encode($detail, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/30 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-md">
                            <span class="material-symbols-outlined text-[20px]">table_chart</span>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-on-surface">Daftar Penduduk &amp; Peringkat Desil</h3>
                            <p class="text-[11px] text-on-surface-variant">Menampilkan <b class="text-on-surface">{{ $fmt($rows->total()) }}</b> jiwa penduduk terdaftar</p>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('data-desil.index') }}" @submit.prevent="desilMuat(desilUrlForm($el))" class="flex flex-wrap items-center gap-2 text-xs text-on-surface-variant">
                        @foreach (request()->except(['per_page', 'page', 'refresh']) as $k => $v)
                            @if (is_array($v))
                                @foreach ($v as $vv)<input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">@endforeach
                            @else
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endif
                        @endforeach
                        <span class="hidden sm:inline">Tampilkan:</span>
                        <select name="per_page" @change="$el.form.requestSubmit()" class="cursor-pointer rounded-lg border border-outline-variant/50 bg-white px-2.5 py-1.5 text-xs font-semibold text-on-surface outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20">
                            @foreach ([15, 25, 50, 100] as $n)
                                <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }} baris</option>
                            @endforeach
                        </select>
                        <span class="rounded-full bg-brand-soft px-3 py-1.5 text-[11px] font-bold text-brand">Hal {{ $rows->currentPage() }} dari {{ $fmt($rows->lastPage()) }}</span>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-surface-container-low text-[10px] uppercase tracking-wide text-on-surface-variant">
                            <tr>
                                <th class="px-4 py-3 font-bold">No</th>
                                <th class="px-4 py-3 font-bold">Nomor KK &amp; NIK</th>
                                <th class="px-4 py-3 font-bold">Nama Penduduk</th>
                                <th class="px-4 py-3 font-bold">Usia &amp; Hubungan</th>
                                <th class="px-4 py-3 font-bold">Wilayah (RT/RW/Kel/Kec)</th>
                                <th class="px-4 py-3 text-center font-bold">Desil</th>
                                <th class="px-4 py-3 font-bold">Bansos Aktif</th>
                                <th class="px-4 py-3 text-center font-bold">Ground Check</th>
                                <th class="px-4 py-3 text-center font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20">
                            @forelse ($rows as $i => $r)
                                @php
                                    $d = (int) ($r->desil_nasional ?? 0);
                                    $bansos = $r->bansosAktif();
                                    $gc = isset($gcSelesai[$r->nomor_kartu_keluarga]);
                                    $lk = stripos((string) $r->jenis_kelamin, 'laki') !== false;
                                    $rw = $r->rw !== null && $r->rw !== '' ? $r->rw : '-';
                                    $rt = $r->rt !== null && $r->rt !== '' ? $r->rt : '-';
                                    $inisial = mb_strtoupper(mb_substr(trim((string) $r->nama) !== '' ? trim((string) $r->nama) : '?', 0, 1));
                                @endphp
                                <tr class="group align-middle transition-colors duration-150 hover:bg-emerald-50/50">
                                    <td class="px-4 py-3 font-medium tabular-nums text-on-surface-variant">{{ $rows->firstItem() + $i }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono">
                                        <div class="flex items-center gap-1.5 font-semibold text-on-surface">
                                            <span class="material-symbols-outlined text-[14px] text-on-surface-variant/50">badge</span>{{ $r->nomor_kartu_keluarga }}
                                        </div>
                                        <div class="text-[11px] text-on-surface-variant">NIK: {{ $r->nomor_induk_kependudukan }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-gradient-to-br {{ $lk ? 'from-sky-500 to-blue-600' : 'from-pink-500 to-rose-600' }} text-sm font-bold text-white shadow-sm">{{ $inisial }}</span>
                                            <div class="min-w-0">
                                                <div class="max-w-[180px] truncate font-bold uppercase text-on-surface" title="{{ $r->nama }}">{{ $r->nama }}</div>
                                                <div class="text-[11px] text-on-surface-variant">
                                                    <span class="font-semibold {{ $lk ? 'text-blue-600' : 'text-pink-600' }}">{{ $r->jenis_kelamin }}</span>
                                                    &bull; {{ trim((string) $r->pekerjaan) ?: '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-on-surface">{{ $r->usia !== null ? $r->usia . ' Tahun' : '-' }}</div>
                                        <span class="mt-0.5 inline-block rounded-md border border-emerald-200/70 bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">{{ $r->hubungan_keluarga ?: '-' }}</span>
                                    </td>
                                    <td class="max-w-[260px] px-4 py-3">
                                        <div class="truncate font-semibold uppercase text-on-surface" title="{{ $r->alamat }}">{{ $r->alamat ?: '-' }}</div>
                                        <div class="text-[11px] text-on-surface-variant">RT {{ $rt }} / RW {{ $rw }}, {{ $r->desa_kelurahan }}, {{ $r->kecamatan }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-white shadow-sm {{ $warnaDesil[$d] }}">
                                            {{ $d === 0 ? 'NON DESIL' : 'DESIL ' . $d }}
                                        </span>
                                        <div class="mt-0.5 text-[10px] text-on-surface-variant">{{ P::DESIL_LABEL[$d] }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($bansos)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($bansos as [$label, $warna])
                                                    <span class="rounded border px-1.5 py-0.5 text-[10px] font-semibold {{ $chip[$warna] }}">{{ $label }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="italic text-on-surface-variant/60">Non-Bansos</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($gc)
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span> Terverifikasi
                                            </span>
                                        @else
                                            <span class="inline-block rounded-full border border-dashed border-outline-variant/60 bg-surface-container/50 px-2.5 py-1 text-[10px] text-on-surface-variant">Belum GC</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" @click="detail = data[{{ $r->id }}]"
                                            class="inline-flex items-center gap-1 rounded-xl border border-brand/20 bg-brand-soft px-3 py-1.5 text-[11px] font-bold text-brand transition-all duration-200 hover:-translate-y-0.5 hover:bg-brand hover:text-white hover:shadow-lg active:translate-y-0">
                                            <span class="material-symbols-outlined text-[15px]">visibility</span> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-16 text-center">
                                        <span class="material-symbols-outlined mx-auto text-[56px] text-slate-300">search_off</span>
                                        <p class="mt-3 text-sm font-bold text-on-surface">Tidak ada data yang cocok dengan filter</p>
                                        <p class="mt-1 text-xs text-on-surface-variant">Coba ubah kata kunci atau longgarkan filter yang sedang aktif.</p>
                                        <a href="{{ route('data-desil.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-outline-variant/50 bg-white px-4 py-2 text-xs font-bold text-brand transition-colors hover:bg-brand-soft">
                                            <span class="material-symbols-outlined text-[16px]">restart_alt</span> Reset Semua Filter
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-outline-variant/30 bg-surface-container-low/50 px-5 py-4 text-xs text-on-surface-variant">
                    <span>Menampilkan data ke <b class="text-on-surface">{{ $fmt($rows->firstItem() ?? 0) }}</b> sampai <b class="text-on-surface">{{ $fmt($rows->lastItem() ?? 0) }}</b> dari total <b class="text-on-surface">{{ $fmt($rows->total()) }}</b> jiwa</span>
                    <div>{{ $rows->links() }}</div>
                </div>
            </div>
        </div>

        {{-- ===== Modal Detail: semua kolom ===== --}}
        <div x-show="detail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm" @keydown.escape.window="detail = null"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.outside="detail = null" x-show="detail"
                class="flex max-h-[88vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90 translate-y-6" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div class="relative flex items-center justify-between gap-3 bg-gradient-to-r from-teal-600 via-emerald-600 to-teal-600 px-5 py-4 text-white">
                    <div class="pointer-events-none absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10 blur-xl"></div>
                    <div class="relative flex min-w-0 items-center gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-white/30 bg-white/20 text-lg font-extrabold backdrop-blur" x-text="detail && detail.nama ? detail.nama.charAt(0).toUpperCase() : '?'">&nbsp;</span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/70">Detail Data Penduduk</p>
                            <h3 class="truncate text-base font-bold" x-text="detail ? detail.nama : ''"></h3>
                        </div>
                    </div>
                    <button type="button" @click="detail = null" class="relative grid h-9 w-9 shrink-0 place-items-center rounded-full bg-white/15 text-white transition-all duration-300 hover:rotate-90 hover:bg-white/30">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <div class="nice-scroll flex-1 overflow-y-auto bg-surface-container/30 p-5 sm:p-6">
                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-x-6">
                        <template x-for="(label, key) in labels" :key="key">
                            <div class="rounded-xl border border-outline-variant/30 bg-white px-3.5 py-2.5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-brand/40 hover:shadow-md">
                                <dt class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/80" x-text="label"></dt>
                                <dd class="mt-1 break-words text-sm font-semibold text-on-surface" x-text="detail && detail[key] !== '' ? detail[key] : '-'"></dd>
                            </div>
                        </template>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Loader AJAX: ganti tabel tanpa reload halaman ===== --}}
    <script>
        (function () {
            let ctl = null;

            // Susun URL dari form (parameter kosong dibuang supaya URL rapi)
            window.desilUrlForm = function (form) {
                const p = new URLSearchParams();
                new FormData(form).forEach(function (v, k) {
                    if (String(v).trim() !== '') p.append(k, v);
                });
                const base = form.action.split('?')[0];
                const qs = p.toString();
                return qs ? base + '?' + qs : base;
            };

            // Klik link pagination -> muat via AJAX
            window.desilLink = function (e) {
                const a = e.target.closest('a[href]');
                if (!a || !a.closest('nav')) return;
                e.preventDefault();
                window.desilMuat(a.href, { gulir: true });
            };

            window.desilMuat = async function (url, opsi) {
                opsi = opsi || {};
                const root = document.getElementById('desil-root');
                const state = root && window.Alpine ? window.Alpine.$data(root) : null;
                if (ctl) ctl.abort();
                const mine = ctl = new AbortController();
                if (state) state.loading = true;

                try {
                    const res = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        credentials: 'same-origin',
                        signal: mine.signal
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);

                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const baru = doc.getElementById('hasil-desil');
                    const lama = document.getElementById('hasil-desil');
                    if (!baru || !lama) throw new Error('Struktur halaman tidak cocok');

                    lama.innerHTML = baru.innerHTML;

                    const json = doc.getElementById('detail-json');
                    if (json && state) state.data = JSON.parse(json.textContent);

                    // Sinkronkan URL, link export/segarkan, dan per_page
                    history.replaceState(null, '', url);
                    const qs = new URL(url, location.href).search;
                    const ex = document.getElementById('link-export');
                    if (ex) ex.href = ex.dataset.base + qs;
                    const rf = document.getElementById('link-refresh');
                    if (rf) {
                        const p = new URLSearchParams(qs);
                        p.set('refresh', '1');
                        rf.href = rf.dataset.base + '?' + p.toString();
                    }
                    const pp = new URLSearchParams(qs).get('per_page');
                    const inp = document.querySelector('#form-desil [name="per_page"]');
                    if (pp && inp) inp.value = pp;

                    if (opsi.gulir) {
                        document.getElementById('tabel-desil').scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } catch (err) {
                    if (err && err.name === 'AbortError') return;
                    window.location.href = url; // fallback: reload biasa
                } finally {
                    if (ctl === mine && state) state.loading = false;
                }
            };
        })();
    </script>
@endsection