@extends('layouts.app')

@section('title', 'PPID - SOLID v4 Dinas Sosial Kota Bogor')

@push('head')
    <style>
        [id] {
            scroll-margin-top: 7.5rem;
        }

        /* --- Hero (gaya sama dengan halaman Informasi PBI) --- */
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

        /* --- Ringkasan angka --- */
        .ppid-stat {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 1rem 1.1rem;
            border-radius: 0.85rem;
            background: #f8fafc;
            border: 1px solid #e4e9f0;
        }
        .ppid-stat-icon {
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

        /* --- Tab kategori (pill) --- */
        .ppid-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .ppid-tab {
            appearance: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.95rem;
            border-radius: 9999px;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            font-size: 13px;
            font-weight: 600;
            color: #42474e;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .ppid-tab .ppid-count {
            min-width: 1.35rem;
            padding: 0 0.4rem;
            border-radius: 9999px;
            background: #eef2f7;
            color: #5b6470;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.35rem;
            text-align: center;
        }
        .ppid-tab:hover {
            border-color: #121358;
            color: #121358;
        }
        .ppid-tab.is-active {
            background: #121358;
            border-color: #121358;
            color: #ffffff;
            box-shadow: 0 6px 14px rgba(18, 19, 88, 0.22);
        }
        .ppid-tab.is-active .ppid-count {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        /* --- Filter (input pill) --- */
        .ppid-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #121d26;
            margin-bottom: 0.5rem;
        }
        .ppid-field-wrap {
            position: relative;
        }
        .ppid-field-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #72777f;
            pointer-events: none;
            display: flex;
        }
        .ppid-field input,
        .ppid-field select {
            width: 100%;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            border-radius: 9999px;
            padding: 0.7rem 1rem 0.7rem 2.8rem;
            font-size: 14px;
            color: #121d26;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .ppid-field input::placeholder {
            color: #9aa2ab;
            font-size: 13px;
        }
        .ppid-field input:focus,
        .ppid-field select:focus {
            outline: none;
            border-color: #121358;
            box-shadow: 0 0 0 4px rgba(18, 19, 88, 0.12);
        }

        /* --- Kartu dokumen --- */
        .ppid-doc {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.1rem;
            border-radius: 0.85rem;
            background: #ffffff;
            border: 1px solid #e4e9f0;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }
        .ppid-doc:hover {
            border-color: #98cbff;
            transform: translateY(-2px);
        }
        .ppid-doc-icon {
            width: 2.9rem;
            height: 2.9rem;
            border-radius: 0.7rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e4effd;
            color: #121358;
        }
        .ppid-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 700;
            background: #fbbf24;
            color: #4a3200;
        }
        .ppid-chip.ppid-chip-blue {
            background: #121358;
            color: #ffffff;
        }
        .ppid-act {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .ppid-act-primary {
            background: linear-gradient(135deg, #121358 0%, #1a233a 100%);
            color: #ffffff;
            box-shadow: 0 6px 14px rgba(18, 19, 88, 0.28);
        }
        .ppid-act-primary:hover {
            box-shadow: 0 8px 18px rgba(18, 19, 88, 0.38);
        }
        .ppid-act-outline {
            border: 1.5px solid #121358;
            color: #121358;
            background: #ffffff;
        }
        .ppid-act-outline:hover {
            background: #f8fafc;
        }
        .ppid-act:active {
            transform: scale(0.98);
        }

        .ppid-empty {
            border: 1.5px dashed #d1d5db;
            border-radius: 0.85rem;
            background: #f8fafc;
            padding: 2.5rem 1rem;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }

        @media (max-width: 639px) {
            .ppid-doc {
                flex-wrap: wrap;
            }
            .ppid-doc-actions {
                width: 100%;
            }
            .ppid-doc-actions .ppid-act {
                flex: 1;
            }
        }
    </style>
@endpush

@section('content')
    @php
        /*
         |--------------------------------------------------------------
         | DATA DUMMY
         |--------------------------------------------------------------
         | Nanti ganti dengan data dari database. Kirim variabel
         | $dokumenPpid dari PageController@ppid dengan struktur:
         | [ ['kategori' => 'PERDA', 'tahun' => 2024, 'judul' => '...',
         |    'preview' => url, 'download' => url], ... ]
         | Jika $dokumenPpid dikirim dari controller, data dummy dilewati.
         */
        $kategoriPpid = ['HAKI', 'Insidentil', 'PERWALI', 'PERDA', 'RKA', 'DPA', 'LRA', 'SOP', 'RENSTRA', 'LKIP', 'Rencana Aksi', 'RENJA', 'Laporan SKM', 'IKU'];

        $dokumenPpid = $dokumenPpid ?? [
            ['kategori' => 'HAKI', 'tahun' => 2026, 'judul' => 'Surat Pencatatan Ciptaan SOLID v4 Dinas Sosial'],

            ['kategori' => 'PERWALI', 'tahun' => 2024, 'judul' => 'Peraturan Wali Kota tentang Penyelenggaraan Kesejahteraan Sosial (contoh)'],
            ['kategori' => 'PERWALI', 'tahun' => 2023, 'judul' => 'Peraturan Wali Kota tentang Bantuan Sosial Daerah (contoh)'],
            ['kategori' => 'PERWALI', 'tahun' => 2022, 'judul' => 'Peraturan Wali Kota tentang Tugas dan Fungsi Dinas Sosial (contoh)'],

            ['kategori' => 'PERDA', 'tahun' => 2023, 'judul' => 'Peraturan Daerah tentang Penyelenggaraan Kesejahteraan Sosial (contoh)'],
            ['kategori' => 'PERDA', 'tahun' => 2021, 'judul' => 'Peraturan Daerah tentang Perlindungan Penyandang Disabilitas (contoh)'],
            ['kategori' => 'PERDA', 'tahun' => 2019, 'judul' => 'Peraturan Daerah tentang Penanganan Fakir Miskin (contoh)'],

            ['kategori' => 'RKA', 'tahun' => 2026, 'judul' => 'Rencana Kerja dan Anggaran Dinas Sosial Tahun 2026 (contoh)'],
            ['kategori' => 'RKA', 'tahun' => 2025, 'judul' => 'Rencana Kerja dan Anggaran Dinas Sosial Tahun 2025 (contoh)'],
            ['kategori' => 'RKA', 'tahun' => 2024, 'judul' => 'Rencana Kerja dan Anggaran Dinas Sosial Tahun 2024 (contoh)'],

            ['kategori' => 'DPA', 'tahun' => 2026, 'judul' => 'Dokumen Pelaksanaan Anggaran Tahun 2026 (contoh)'],
            ['kategori' => 'DPA', 'tahun' => 2025, 'judul' => 'Dokumen Pelaksanaan Anggaran Tahun 2025 (contoh)'],

            ['kategori' => 'LRA', 'tahun' => 2025, 'judul' => 'Laporan Realisasi Anggaran Semester II Tahun 2025 (contoh)'],
            ['kategori' => 'LRA', 'tahun' => 2025, 'judul' => 'Laporan Realisasi Anggaran Semester I Tahun 2025 (contoh)'],
            ['kategori' => 'LRA', 'tahun' => 2024, 'judul' => 'Laporan Realisasi Anggaran Tahun 2024 (contoh)'],

            ['kategori' => 'SOP', 'tahun' => 2025, 'judul' => 'SOP Pendaftaran dan Verifikasi BPJS PBI (contoh)'],
            ['kategori' => 'SOP', 'tahun' => 2025, 'judul' => 'SOP Penyaluran Bantuan Sosial (contoh)'],
            ['kategori' => 'SOP', 'tahun' => 2024, 'judul' => 'SOP Pelayanan Informasi Publik (contoh)'],

            ['kategori' => 'RENSTRA', 'tahun' => 2024, 'judul' => 'Rencana Strategis Dinas Sosial 2024-2029 (contoh)'],
            ['kategori' => 'RENSTRA', 'tahun' => 2019, 'judul' => 'Rencana Strategis Dinas Sosial 2019-2024 (contoh)'],

            ['kategori' => 'LKIP', 'tahun' => 2025, 'judul' => 'Laporan Kinerja Instansi Pemerintah Tahun 2025 (contoh)'],

            ['kategori' => 'Rencana Aksi', 'tahun' => 2026, 'judul' => 'Rencana Aksi Kinerja Tahun 2026 (contoh)'],

            ['kategori' => 'RENJA', 'tahun' => 2026, 'judul' => 'Rencana Kerja Dinas Sosial Tahun 2026 (contoh)'],
            ['kategori' => 'RENJA', 'tahun' => 2025, 'judul' => 'Rencana Kerja Dinas Sosial Tahun 2025 (contoh)'],
            ['kategori' => 'RENJA', 'tahun' => 2024, 'judul' => 'Rencana Kerja Dinas Sosial Tahun 2024 (contoh)'],

            ['kategori' => 'Laporan SKM', 'tahun' => 2025, 'judul' => 'Laporan Survei Kepuasan Masyarakat Tahun 2025 (contoh)'],

            ['kategori' => 'IKU', 'tahun' => 2025, 'judul' => 'Indikator Kinerja Utama Dinas Sosial Tahun 2025 (contoh)'],
        ];

        $tahunPpid = collect($dokumenPpid)->pluck('tahun')->unique()->sortDesc()->values();
        $kategoriTerisi = collect($dokumenPpid)->pluck('kategori')->unique()->count();
    @endphp

    {{-- ============ HERO ============ --}}
    <section class="tp-hero w-full text-white pt-32 pb-20 md:pt-40 md:pb-24">
        <div class="relative z-10 max-w-[1400px] mx-auto px-6 md:px-12 text-center flex flex-col items-center justify-center">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold tracking-wide">
                <span class="material-symbols-outlined text-[16px]">verified_user</span>
                Keterbukaan Informasi Publik
            </span>
            <h1 class="mt-5 text-3xl md:text-5xl lg:text-6xl font-bold tracking-tight max-w-4xl drop-shadow-md">
                PPID Dinas Sosial Kota Bogor
            </h1>
            <p class="mt-5 text-sm md:text-base text-gray-300 max-w-2xl font-light leading-relaxed">
                Pejabat Pengelola Informasi dan Dokumentasi. Akses layanan informasi publik secara cepat,
                transparan, dan akuntabel di lingkungan Dinas Sosial Kota Bogor.
            </p>
        </div>
    </section>

    {{-- ============ BREADCRUMB ============ --}}
    <div class="tp-breadcrumb-wrapper w-full">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12 py-4 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('home') }}" class="hover:text-[#121358] transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">home</span>
                Beranda
            </a>
            <span class="material-symbols-outlined text-[16px] text-gray-400">chevron_right</span>
            <span class="text-[#121358] font-bold">PPID</span>
        </div>
    </div>

    {{-- ============ KONTEN UTAMA ============ --}}
    <section class="w-full py-12 md:py-16">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12">
            <div class="tp-page-card space-y-8">

                <div class="space-y-2">
                    <h2 class="text-2xl text-[#121358] font-bold leading-snug">Daftar Informasi &amp; Dokumen Publik</h2>
                    <p class="text-gray-600 leading-relaxed max-w-3xl">
                        Pilih kategori, lalu saring berdasarkan tahun atau cari judul dokumen yang Anda butuhkan.
                    </p>
                </div>

                {{-- Ringkasan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="ppid-stat">
                        <span class="ppid-stat-icon"><span class="material-symbols-outlined text-[22px]">description</span></span>
                        <div>
                            <p class="text-gray-800 font-bold text-lg leading-none">{{ count($dokumenPpid) }}</p>
                            <p class="text-gray-500 text-xs mt-1">Total Dokumen</p>
                        </div>
                    </div>
                    <div class="ppid-stat">
                        <span class="ppid-stat-icon"><span class="material-symbols-outlined text-[22px]">folder_open</span></span>
                        <div>
                            <p class="text-gray-800 font-bold text-lg leading-none">{{ $kategoriTerisi }}</p>
                            <p class="text-gray-500 text-xs mt-1">Kategori</p>
                        </div>
                    </div>
                </div>

                {{-- Tab kategori --}}
                <div class="ppid-tabs" role="tablist" id="ppid-tabs">
                    @foreach ($kategoriPpid as $i => $kat)
                        @php $jumlah = collect($dokumenPpid)->where('kategori', $kat)->count(); @endphp
                        <button type="button" role="tab" data-kategori="{{ $kat }}"
                            class="ppid-tab {{ $i === 0 ? 'is-active' : '' }}"
                            aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                            {{ $kat }}
                            <span class="ppid-count">{{ $jumlah }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Filter --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="ppid-field md:col-span-4">
                        <label for="ppid-tahun">Filter Tahun</label>
                        <div class="ppid-field-wrap">
                            <span class="ppid-field-icon material-symbols-outlined text-[18px]">calendar_month</span>
                            <select id="ppid-tahun">
                                <option value="">Semua Tahun</option>
                                @foreach ($tahunPpid as $th)
                                    <option value="{{ $th }}">{{ $th }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="ppid-field md:col-span-8">
                        <label for="ppid-cari">Cari Dokumen</label>
                        <div class="ppid-field-wrap">
                            <span class="ppid-field-icon material-symbols-outlined text-[18px]">search</span>
                            <input id="ppid-cari" type="search" placeholder="Cari berdasarkan judul atau tahun..."
                                autocomplete="off" />
                        </div>
                    </div>
                </div>

                {{-- Daftar dokumen --}}
                <div class="space-y-3" id="ppid-list">
                    @foreach ($dokumenPpid as $d)
                        <div class="ppid-doc" data-kategori="{{ $d['kategori'] }}" data-tahun="{{ $d['tahun'] }}"
                            data-judul="{{ \Illuminate\Support\Str::lower($d['judul']) }}">
                            <span class="ppid-doc-icon"><span class="material-symbols-outlined text-[24px]">picture_as_pdf</span></span>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="ppid-chip">{{ $d['tahun'] }}</span>
                                    <span class="ppid-chip ppid-chip-blue">{{ $d['kategori'] }}</span>
                                </div>
                                <p class="text-gray-800 font-bold text-sm leading-snug">{{ $d['judul'] }}</p>
                            </div>
                            <div class="ppid-doc-actions flex items-center gap-2">
                                <a href="{{ $d['preview'] ?? '#' }}" target="_blank" rel="noopener"
                                    class="ppid-act ppid-act-outline">
                                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    Preview
                                </a>
                                <a href="{{ $d['download'] ?? '#' }}" download class="ppid-act ppid-act-primary">
                                    <span class="material-symbols-outlined text-[16px]">download</span>
                                    Download
                                </a>
                            </div>
                        </div>
                    @endforeach

                    <div id="ppid-empty" class="ppid-empty" hidden>
                        <span class="material-symbols-outlined text-[28px] block mb-1 text-gray-400">search_off</span>
                        Tidak ada dokumen yang cocok dengan pencarian Anda.
                    </div>
                </div>

                <p class="text-sm text-gray-500" id="ppid-info">Menampilkan 0 dari 0 dokumen</p>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var tabs = document.querySelectorAll('#ppid-tabs .ppid-tab');
            var items = document.querySelectorAll('#ppid-list .ppid-doc');
            var selTahun = document.getElementById('ppid-tahun');
            var inpCari = document.getElementById('ppid-cari');
            var info = document.getElementById('ppid-info');
            var empty = document.getElementById('ppid-empty');
            var aktif = tabs.length ? tabs[0].getAttribute('data-kategori') : '';

            function terapkan() {
                var th = selTahun.value;
                var q = inpCari.value.trim().toLowerCase();
                var tampil = 0, total = 0;

                items.forEach(function (el) {
                    var cocokKat = el.getAttribute('data-kategori') === aktif;
                    if (cocokKat) total++;
                    var cocokTh = !th || el.getAttribute('data-tahun') === th;
                    var cocokQ = !q ||
                        el.getAttribute('data-judul').indexOf(q) !== -1 ||
                        el.getAttribute('data-tahun').indexOf(q) !== -1;
                    var show = cocokKat && cocokTh && cocokQ;
                    el.style.display = show ? '' : 'none';
                    if (show) tampil++;
                });

                info.textContent = 'Menampilkan ' + tampil + ' dari ' + total + ' dokumen';
                empty.hidden = tampil !== 0;
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) {
                        t.classList.remove('is-active');
                        t.setAttribute('aria-selected', 'false');
                    });
                    tab.classList.add('is-active');
                    tab.setAttribute('aria-selected', 'true');
                    aktif = tab.getAttribute('data-kategori');
                    terapkan();
                });
            });

            selTahun.addEventListener('change', terapkan);
            inpCari.addEventListener('input', terapkan);
            terapkan();
        })();
    </script>
@endpush
