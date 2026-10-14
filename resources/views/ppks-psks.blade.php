@extends('layouts.app')

@section('title', 'PPKS & PSKS - SOLID v4 Dinas Sosial Kota Bogor')

@push('head')
    <style>
        [id] {
            scroll-margin-top: 7.5rem;
        }

        /* --- Hero (gaya sama dengan halaman lain) --- */
        .tp-hero {
            background:
                radial-gradient(circle at 15% 50%, rgba(255, 255, 255, 0.04), transparent 30%),
                radial-gradient(circle at 85% 30%, rgba(255, 255, 255, 0.06), transparent 30%),
                linear-gradient(135deg, #121358 0%, #1a233a 50%, #0f172a 100%);
            position: relative;
            overflow: hidden;
        }
        .tp-hero::before,
        .tp-hero::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(4px);
        }
        .tp-hero::before {
            width: 32rem;
            height: 32rem;
            right: -12rem;
            top: -14rem;
        }
        .tp-hero::after {
            width: 18rem;
            height: 18rem;
            left: -6rem;
            bottom: -8rem;
        }
        .tp-breadcrumb-wrapper {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            position: relative;
            z-index: 20;
        }
        .tp-page-card {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 6px 20px rgba(55, 65, 81, 0.08);
        }
        @media (min-width: 768px) {
            .tp-page-card {
                padding: 2rem;
            }
        }

        /* --- Ringkasan --- */
        .pp-stat {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 1rem 1.1rem;
            border-radius: 0.85rem;
            background: #f8fafc;
            border: 1px solid #e4e9f0;
        }
        .pp-stat-icon {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.65rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e4effd;
            color: #121358;
        }

        /* --- Tab jenis data --- */
        .pp-tabs {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            .pp-tabs {
                grid-template-columns: 1fr 1fr;
            }
        }
        .pp-tab {
            appearance: none;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            width: 100%;
            text-align: left;
            padding: 0.9rem 1.1rem;
            border-radius: 0.85rem;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .pp-tab:hover {
            border-color: #121358;
        }
        .pp-tab-icon {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.65rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e4effd;
            color: #121358;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .pp-tab-title {
            font-weight: 800;
            font-size: 15px;
            color: #121358;
        }
        .pp-tab-sub {
            font-size: 12px;
            color: #64748b;
            margin-top: 0.1rem;
        }
        .pp-tab.is-active {
            background: #121358;
            border-color: #121358;
            box-shadow: 0 8px 18px rgba(18, 19, 88, 0.25);
        }
        .pp-tab.is-active .pp-tab-icon {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
        }
        .pp-tab.is-active .pp-tab-title {
            color: #ffffff;
        }
        .pp-tab.is-active .pp-tab-sub {
            color: #cbd5e1;
        }

        /* --- Filter --- */
        .pp-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #121d26;
            margin-bottom: 0.5rem;
        }
        .pp-field-wrap {
            position: relative;
        }
        .pp-field-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #72777f;
            pointer-events: none;
            display: flex;
        }
        .pp-field input,
        .pp-field select {
            width: 100%;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            border-radius: 9999px;
            padding: 0.7rem 1rem 0.7rem 2.8rem;
            font-size: 14px;
            color: #121d26;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .pp-field input::placeholder {
            color: #9aa2ab;
            font-size: 13px;
        }
        .pp-field input:focus,
        .pp-field select:focus {
            outline: none;
            border-color: #121358;
            box-shadow: 0 0 0 4px rgba(18, 19, 88, 0.12);
        }

        /* --- Ringkasan per kecamatan (bar) --- */
        .pp-bars {
            display: grid;
            gap: 0.6rem;
        }
        .pp-bar-row {
            appearance: none;
            display: grid;
            grid-template-columns: 7.5rem 1fr 4.5rem;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.2rem 0;
            background: none;
            border: 0;
            cursor: pointer;
            text-align: left;
        }
        .pp-bar-name {
            font-size: 13px;
            font-weight: 600;
            color: #42474e;
        }
        .pp-bar-track {
            height: 0.6rem;
            border-radius: 9999px;
            background: #eef2f7;
            overflow: hidden;
        }
        .pp-bar-fill {
            height: 100%;
            border-radius: 9999px;
            background: linear-gradient(90deg, #121358, #3b5bdb);
        }
        .pp-bar-val {
            font-size: 13px;
            font-weight: 700;
            color: #121358;
            text-align: right;
        }
        .pp-bar-row:hover .pp-bar-name {
            color: #121358;
        }

        /* --- Accordion kecamatan --- */
        .pp-kec {
            border: 1px solid #e4e9f0;
            border-radius: 0.85rem;
            background: #ffffff;
            overflow: hidden;
        }
        .pp-kec + .pp-kec {
            margin-top: 0.75rem;
        }
        .pp-kec > summary {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.9rem 1.1rem;
            cursor: pointer;
            background: #f8fafc;
            user-select: none;
        }
        .pp-kec > summary::-webkit-details-marker {
            display: none;
        }
        .pp-kec[open] > summary {
            border-bottom: 1px solid #e4e9f0;
        }
        .pp-kec-chev {
            transition: transform 0.2s ease;
            color: #64748b;
        }
        .pp-kec[open] .pp-kec-chev {
            transform: rotate(90deg);
        }
        .pp-kec-name {
            font-weight: 800;
            font-size: 15px;
            color: #121358;
        }
        .pp-kec-meta {
            font-size: 12px;
            color: #64748b;
        }
        .pp-badge {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.75rem;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 700;
            background: #121358;
            color: #ffffff;
            white-space: nowrap;
        }

        .pp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }
        .pp-table th {
            text-align: left;
            padding: 0.6rem 1.1rem;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #64748b;
            border-bottom: 1px solid #eef2f7;
        }
        .pp-table td {
            padding: 0.65rem 1.1rem;
            border-bottom: 1px solid #f3f5f9;
            color: #334155;
        }
        .pp-table tr:last-child td {
            border-bottom: 0;
        }
        .pp-table tbody tr:hover td {
            background: #fafcff;
        }
        .pp-table .num {
            width: 3.5rem;
            color: #94a3b8;
        }
        .pp-table .val {
            width: 38%;
            text-align: right;
        }
        .pp-val-cell {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        .pp-mini-track {
            flex: 1;
            max-width: 9rem;
            height: 0.4rem;
            border-radius: 9999px;
            background: #eef2f7;
            overflow: hidden;
        }
        .pp-mini-fill {
            height: 100%;
            background: #3b5bdb;
            border-radius: 9999px;
        }
        .pp-val-num {
            min-width: 3rem;
            font-weight: 700;
            color: #121358;
        }
        .pp-val-num.is-zero {
            color: #94a3b8;
            font-weight: 600;
        }
        .pp-table tfoot td {
            background: #f8fafc;
            font-weight: 800;
            color: #121358;
            border-top: 1px solid #e4e9f0;
        }

        .pp-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            border: 1.5px solid #121358;
            background: #ffffff;
            color: #121358;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .pp-btn:hover {
            background: #f8fafc;
        }
        .pp-sample {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.7rem;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 700;
            background: #fbbf24;
            color: #4a3200;
        }
        .pp-empty {
            border: 1.5px dashed #d1d5db;
            border-radius: 0.85rem;
            background: #f8fafc;
            padding: 2.5rem 1rem;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }

        @media (max-width: 639px) {
            .pp-bar-row {
                grid-template-columns: 6rem 1fr 3.6rem;
                gap: 0.5rem;
            }
            .pp-table th,
            .pp-table td {
                padding: 0.6rem 0.75rem;
            }
            .pp-table .val {
                width: auto;
            }
            .pp-mini-track {
                display: none;
            }
        }
    </style>
@endpush

@section('content')
    @php
        /*
         |--------------------------------------------------------------
         | DATA
         |--------------------------------------------------------------
         | Daftar wilayah Kota Bogor (6 kecamatan, 68 kelurahan).
         | Angka di bawah adalah DATA CONTOH (dibuat otomatis) agar tampilan
         | bisa dilihat. Untuk data asli, kirim dari PageController@ppksPsks:
         |   'dataPpks' => ['Bogor Barat' => ['Bubulak' => 120, ...], ...]
         |   'dataPsks' => (struktur sama)
         | Jika kedua variabel itu dikirim, data contoh dilewati dan
         | label "Data contoh" otomatis hilang.
         */
        $daftarTahun = [2025, 2024, 2023];
        $tahun = in_array((int) ($tahun ?? 2025), $daftarTahun, true) ? (int) ($tahun ?? 2025) : 2025;
        $tabAktif = request('tab') === 'psks' ? 'psks' : 'ppks';

        $wilayah = [
            'Bogor Barat' => ['Balumbang Jaya', 'Bubulak', 'Cilendek Barat', 'Cilendek Timur', 'Curug', 'Curug Mekar', 'Gunung Batu', 'Loji', 'Margajaya', 'Menteng', 'Pasir Jaya', 'Pasir Kuda', 'Pasir Mulya', 'Semplak', 'Sindang Barang', 'Situ Gede'],
            'Bogor Selatan' => ['Batutulis', 'Bojongkerta', 'Bondongan', 'Cikaret', 'Cipaku', 'Empang', 'Genteng', 'Harjasari', 'Kertamaya', 'Lawang Gintung', 'Muarasari', 'Mulyaharja', 'Pakuan', 'Pamoyanan', 'Rancamaya', 'Ranggamekar'],
            'Bogor Tengah' => ['Babakan', 'Babakan Pasar', 'Cibogor', 'Ciwaringin', 'Gudang', 'Kebon Kalapa', 'Pabaton', 'Paledang', 'Panaragan', 'Sempur', 'Tegallega'],
            'Bogor Timur' => ['Baranangsiang', 'Katulampa', 'Sindangrasa', 'Sindangsari', 'Sukasari', 'Tajur'],
            'Bogor Utara' => ['Bantarjati', 'Cibuluh', 'Ciluar', 'Cimahpar', 'Ciparigi', 'Kedunghalang', 'Tanah Baru', 'Tegal Gundil'],
            'Tanah Sareal' => ['Cibadak', 'Kayu Manis', 'Kebon Pedes', 'Kedung Badak', 'Kedung Jaya', 'Kedung Waringin', 'Kencana', 'Mekarwangi', 'Sukadamai', 'Sukaresmi', 'Tanah Sareal'],
        ];

        $dataContoh = ! (isset($dataPpks) && isset($dataPsks));

        if ($dataContoh) {
            $buat = function (string $jenis) use ($wilayah, $tahun) {
                $out = [];
                foreach ($wilayah as $kec => $kelList) {
                    foreach ($kelList as $kel) {
                        $h = crc32($tahun . '|' . $jenis . '|' . $kel);
                        $out[$kec][$kel] = $jenis === 'ppks'
                            ? 80 + ($h % 520)
                            : ($h % 4 === 0 ? 1 + ($h % 2) : 0);
                    }
                }
                return $out;
            };
            $dataPpks = $buat('ppks');
            $dataPsks = $buat('psks');
        }

        $panels = [
            'ppks' => [
                'singkat' => 'PPKS',
                'judul' => 'Pemerlu Pelayanan Kesejahteraan Sosial',
                'sub' => 'Warga yang memerlukan layanan sosial',
                'ikon' => 'diversity_3',
                'data' => $dataPpks,
            ],
            'psks' => [
                'singkat' => 'PSKS',
                'judul' => 'Potensi dan Sumber Kesejahteraan Sosial',
                'sub' => 'Lembaga dan relawan penyelenggara sosial',
                'ikon' => 'volunteer_activism',
                'data' => $dataPsks,
            ],
        ];

        $fmt = fn ($n) => number_format((int) $n, 0, ',', '.');
        $totalKelurahan = collect($wilayah)->flatten()->count();
    @endphp

    {{-- ============ HERO ============ --}}
    <section class="tp-hero w-full text-white pt-32 pb-20 md:pt-40 md:pb-24">
        <div class="relative z-10 max-w-[1400px] mx-auto px-6 md:px-12 text-center flex flex-col items-center justify-center">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold tracking-wide">
                <span class="material-symbols-outlined text-[16px]">monitoring</span>
                Data Kesejahteraan Sosial
            </span>
            <h1 class="mt-5 text-3xl md:text-5xl lg:text-6xl font-bold tracking-tight max-w-4xl drop-shadow-md">
                PPKS &amp; PSKS
            </h1>
            <p class="mt-5 text-sm md:text-base text-gray-300 max-w-2xl font-light leading-relaxed">
                Sebaran Pemerlu Pelayanan Kesejahteraan Sosial dan Potensi serta Sumber Kesejahteraan Sosial
                di seluruh kecamatan dan kelurahan Kota Bogor.
            </p>
        </div>
    </section>

    {{-- ============ BREADCRUMB ============ --}}
    <div class="tp-breadcrumb-wrapper w-full">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12 py-4 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('home') }}" class="hover:text-[#121358] transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">home</span>
                Home
            </a>
            <span class="material-symbols-outlined text-[16px] text-gray-400">chevron_right</span>
            <span class="text-[#121358] font-bold">PPKS &amp; PSKS</span>
        </div>
    </div>

    {{-- ============ KONTEN ============ --}}
    <section class="w-full py-12 md:py-16">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12">
            <div class="tp-page-card space-y-8">

                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                    <div class="space-y-2">
                        <h2 class="text-2xl text-[#121358] font-bold leading-snug">
                            Data Kesejahteraan Sosial Tahun {{ $tahun }}
                        </h2>
                        <p class="text-gray-600 leading-relaxed max-w-3xl">
                            Pilih jenis data, lalu telusuri per kecamatan atau cari nama kelurahan.
                        </p>
                    </div>
                    @if ($dataContoh)
                        <span class="pp-sample self-start md:self-auto">
                            <span class="material-symbols-outlined text-[15px]">science</span>
                            Data contoh
                        </span>
                    @endif
                </div>

                {{-- Filter tahun + cari --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <form method="GET" action="{{ route('ppks-psks') }}" class="pp-field md:col-span-4" id="pp-form">
                        <input type="hidden" name="tab" id="pp-tab-input" value="{{ $tabAktif }}">
                        <label for="pp-tahun">Tahun</label>
                        <div class="pp-field-wrap">
                            <span class="pp-field-icon material-symbols-outlined text-[18px]">calendar_month</span>
                            <select id="pp-tahun" name="tahun" onchange="this.form.submit()">
                                @foreach ($daftarTahun as $th)
                                    <option value="{{ $th }}" {{ $th === $tahun ? 'selected' : '' }}>{{ $th }}</option>
                                @endforeach
                            </select>
                        </div>
                        <noscript><button type="submit" class="pp-btn mt-2">Tampilkan</button></noscript>
                    </form>
                    <div class="pp-field md:col-span-8">
                        <label for="pp-cari">Cari Kelurahan / Kecamatan</label>
                        <div class="pp-field-wrap">
                            <span class="pp-field-icon material-symbols-outlined text-[18px]">search</span>
                            <input id="pp-cari" type="search" placeholder="Contoh: Bubulak, Bogor Timur..."
                                autocomplete="off" />
                        </div>
                    </div>
                </div>

                {{-- Tab jenis data --}}
                <div class="pp-tabs" role="tablist" id="pp-tabs">
                    @foreach ($panels as $key => $p)
                        @php $totalP = collect($p['data'])->flatten()->sum(); @endphp
                        <button type="button" role="tab" data-panel="{{ $key }}"
                            class="pp-tab {{ $key === $tabAktif ? 'is-active' : '' }}"
                            aria-selected="{{ $key === $tabAktif ? 'true' : 'false' }}">
                            <span class="pp-tab-icon"><span class="material-symbols-outlined text-[22px]">{{ $p['ikon'] }}</span></span>
                            <span class="flex-1 min-w-0">
                                <span class="pp-tab-title block">{{ $p['singkat'] }} &middot; {{ $fmt($totalP) }}</span>
                                <span class="pp-tab-sub block">{{ $p['sub'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                {{-- Panel per jenis --}}
                @foreach ($panels as $key => $p)
                    @php
                        $kecTotals = collect($p['data'])->map(fn ($kels) => collect($kels)->sum());
                        $totalP = $kecTotals->sum();
                        $maxKec = max(1, (int) $kecTotals->max());
                        $maxKel = max(1, (int) collect($p['data'])->flatten()->max());
                        $topKec = $kecTotals->sortDesc()->keys()->first();
                    @endphp

                    <div class="space-y-6" data-panel-body="{{ $key }}" {{ $key === $tabAktif ? '' : 'hidden' }}>

                        <div>
                            <h3 class="text-xl text-[#121358] font-bold">{{ $p['judul'] }}</h3>
                            <p class="text-sm text-gray-500 mt-1">Tahun {{ $tahun }} &middot; Total {{ $fmt($totalP) }}</p>
                        </div>

                        {{-- Angka ringkas --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="pp-stat">
                                <span class="pp-stat-icon"><span class="material-symbols-outlined text-[22px]">groups</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-lg leading-none">{{ $fmt($totalP) }}</p>
                                    <p class="text-gray-500 text-xs mt-1">Total {{ $p['singkat'] }}</p>
                                </div>
                            </div>
                            <div class="pp-stat">
                                <span class="pp-stat-icon"><span class="material-symbols-outlined text-[22px]">map</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-lg leading-none">{{ count($wilayah) }}</p>
                                    <p class="text-gray-500 text-xs mt-1">Kecamatan</p>
                                </div>
                            </div>
                            <div class="pp-stat">
                                <span class="pp-stat-icon"><span class="material-symbols-outlined text-[22px]">location_city</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-lg leading-none">{{ $totalKelurahan }}</p>
                                    <p class="text-gray-500 text-xs mt-1">Kelurahan</p>
                                </div>
                            </div>
                            <div class="pp-stat">
                                <span class="pp-stat-icon"><span class="material-symbols-outlined text-[22px]">trending_up</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-lg leading-none">{{ $topKec }}</p>
                                    <p class="text-gray-500 text-xs mt-1">Jumlah Terbanyak</p>
                                </div>
                            </div>
                        </div>

                        {{-- Sebaran per kecamatan --}}
                        <div class="rounded-xl border border-gray-200 bg-[#f8fafc] p-5 space-y-3">
                            <p class="text-sm font-bold text-gray-800">Sebaran per Kecamatan</p>
                            <div class="pp-bars">
                                @foreach ($kecTotals as $kec => $tot)
                                    <button type="button" class="pp-bar-row" data-open="{{ $key }}-{{ \Illuminate\Support\Str::slug($kec) }}"
                                        title="Lihat daftar kelurahan {{ $kec }}">
                                        <span class="pp-bar-name">{{ $kec }}</span>
                                        <span class="pp-bar-track"><span class="pp-bar-fill block" style="width: {{ round($tot / $maxKec * 100) }}%"></span></span>
                                        <span class="pp-bar-val">{{ $fmt($tot) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Rincian per kecamatan --}}
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-bold text-gray-800">Rincian per Kelurahan</p>
                            <button type="button" class="pp-btn" data-toggle-all="{{ $key }}">
                                <span class="material-symbols-outlined text-[16px]">unfold_more</span>
                                <span data-toggle-label>Buka semua</span>
                            </button>
                        </div>

                        <div data-kec-list="{{ $key }}">
                            @foreach ($p['data'] as $kec => $kels)
                                <details class="pp-kec" id="{{ $key }}-{{ \Illuminate\Support\Str::slug($kec) }}"
                                    data-kec="{{ \Illuminate\Support\Str::lower($kec) }}" {{ $loop->first ? 'open' : '' }}>
                                    <summary>
                                        <span class="material-symbols-outlined pp-kec-chev text-[20px]">chevron_right</span>
                                        <span>
                                            <span class="pp-kec-name block">{{ $kec }}</span>
                                            <span class="pp-kec-meta block">{{ count($kels) }} kelurahan</span>
                                        </span>
                                        <span class="pp-badge">{{ $fmt(collect($kels)->sum()) }}</span>
                                    </summary>
                                    <div class="overflow-x-auto">
                                        <table class="pp-table">
                                            <thead>
                                                <tr>
                                                    <th>NO</th>
                                                    <th>KELURAHAN</th>
                                                    <th style="text-align:right">TOTAL DATA</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($kels as $kel => $n)
                                                    <tr data-nama="{{ \Illuminate\Support\Str::lower($kel) }}">
                                                        <td class="num">{{ $loop->iteration }}</td>
                                                        <td>{{ $kel }}</td>
                                                        <td class="val">
                                                            <div class="pp-val-cell">
                                                                <span class="pp-mini-track"><span class="pp-mini-fill block" style="width: {{ round($n / $maxKel * 100) }}%"></span></span>
                                                                <span class="pp-val-num {{ $n === 0 ? 'is-zero' : '' }}">{{ $fmt($n) }}</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2">Total {{ $kec }}</td>
                                                    <td style="text-align:right">{{ $fmt(collect($kels)->sum()) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </details>
                            @endforeach
                        </div>

                        <div class="pp-empty" data-empty="{{ $key }}" hidden>
                            <span class="material-symbols-outlined text-[28px] block mb-1 text-gray-400">search_off</span>
                            Tidak ada kelurahan yang cocok dengan pencarian Anda.
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var tabs = document.querySelectorAll('#pp-tabs .pp-tab');
            var bodies = document.querySelectorAll('[data-panel-body]');
            var tabInput = document.getElementById('pp-tab-input');
            var cari = document.getElementById('pp-cari');
            var aktif = '{{ $tabAktif }}';

            function bodyOf(key) {
                return document.querySelector('[data-panel-body="' + key + '"]');
            }

            function tampilPanel(key) {
                aktif = key;
                tabs.forEach(function (t) {
                    var on = t.getAttribute('data-panel') === key;
                    t.classList.toggle('is-active', on);
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                bodies.forEach(function (b) {
                    b.hidden = b.getAttribute('data-panel-body') !== key;
                });
                if (tabInput) tabInput.value = key;
                saring();
            }

            /* Pencarian kelurahan / kecamatan pada panel aktif */
            function saring() {
                var q = cari.value.trim().toLowerCase();
                var body = bodyOf(aktif);
                if (!body) return;
                var adaHasil = false;

                body.querySelectorAll('details.pp-kec').forEach(function (det) {
                    var namaKec = det.getAttribute('data-kec');
                    var kecCocok = q && namaKec.indexOf(q) !== -1;
                    var cocokDalam = 0;

                    det.querySelectorAll('tbody tr').forEach(function (tr) {
                        var cocok = !q || kecCocok || tr.getAttribute('data-nama').indexOf(q) !== -1;
                        tr.style.display = cocok ? '' : 'none';
                        if (cocok) cocokDalam++;
                    });

                    det.style.display = cocokDalam ? '' : 'none';
                    if (q && cocokDalam) det.open = true;
                    if (cocokDalam) adaHasil = true;
                });

                var kosong = body.querySelector('[data-empty]');
                if (kosong) kosong.hidden = adaHasil;
            }

            tabs.forEach(function (t) {
                t.addEventListener('click', function () {
                    tampilPanel(t.getAttribute('data-panel'));
                });
            });
            cari.addEventListener('input', saring);

            /* Klik bar kecamatan -> buka dan gulir ke rincian */
            document.querySelectorAll('[data-open]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var det = document.getElementById(btn.getAttribute('data-open'));
                    if (!det) return;
                    det.style.display = '';
                    det.open = true;
                    det.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            /* Buka / tutup semua kecamatan */
            document.querySelectorAll('[data-toggle-all]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var key = btn.getAttribute('data-toggle-all');
                    var list = document.querySelectorAll('[data-kec-list="' + key + '"] details.pp-kec');
                    var semuaTerbuka = Array.prototype.every.call(list, function (d) { return d.open; });
                    list.forEach(function (d) { d.open = !semuaTerbuka; });
                    btn.querySelector('[data-toggle-label]').textContent = semuaTerbuka ? 'Buka semua' : 'Tutup semua';
                });
            });

            saring();
        })();
    </script>
@endpush
