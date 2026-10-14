@extends('layouts.app')

@section('title', 'Regulasi - SOLID v4 Dinas Sosial Kota Bogor')

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
        .rg-stat {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 1rem 1.1rem;
            border-radius: 0.85rem;
            background: #f8fafc;
            border: 1px solid #e4e9f0;
        }
        .rg-stat-icon {
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

        /* --- Pill jenis --- */
        .rg-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .rg-pill {
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
        .rg-pill .rg-count {
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
        .rg-pill:hover {
            border-color: #121358;
            color: #121358;
        }
        .rg-pill.is-active {
            background: #121358;
            border-color: #121358;
            color: #ffffff;
            box-shadow: 0 6px 14px rgba(18, 19, 88, 0.22);
        }
        .rg-pill.is-active .rg-count {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        /* --- Kontrol --- */
        .rg-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #121d26;
            margin-bottom: 0.5rem;
        }
        .rg-field-wrap {
            position: relative;
        }
        .rg-field-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #72777f;
            pointer-events: none;
            display: flex;
        }
        .rg-field input,
        .rg-field select {
            width: 100%;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            border-radius: 9999px;
            padding: 0.7rem 1rem 0.7rem 2.8rem;
            font-size: 14px;
            color: #121d26;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .rg-field input::placeholder {
            color: #9aa2ab;
            font-size: 13px;
        }
        .rg-field input:focus,
        .rg-field select:focus {
            outline: none;
            border-color: #121358;
            box-shadow: 0 0 0 4px rgba(18, 19, 88, 0.12);
        }

        /* --- Tabel --- */
        .rg-card {
            border: 1px solid #e4e9f0;
            border-radius: 0.85rem;
            overflow: hidden;
            background: #ffffff;
        }
        .rg-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }
        .rg-table thead th {
            text-align: left;
            padding: 0.8rem 1rem;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #ffffff;
            background: linear-gradient(135deg, #121358 0%, #1a233a 100%);
            white-space: nowrap;
        }
        .rg-table tbody td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #eef2f7;
            color: #334155;
            vertical-align: top;
            line-height: 1.45;
        }
        .rg-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .rg-table tbody tr:hover td {
            background: #fafcff;
        }
        .rg-table .no {
            width: 3.5rem;
            color: #94a3b8;
        }
        .rg-nama {
            font-weight: 700;
            color: #121358;
        }
        .rg-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.6rem;
            margin-bottom: 0.35rem;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            background: #fbbf24;
            color: #4a3200;
        }
        .rg-nomor {
            font-weight: 600;
            white-space: nowrap;
        }
        .rg-muted {
            color: #94a3b8;
        }
        .rg-dl {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            color: #ffffff;
            background: linear-gradient(135deg, #121358 0%, #1a233a 100%);
            box-shadow: 0 6px 14px rgba(18, 19, 88, 0.28);
            transition: transform 0.15s ease, box-shadow 0.2s ease;
            white-space: nowrap;
        }
        .rg-dl:hover {
            box-shadow: 0 8px 18px rgba(18, 19, 88, 0.38);
        }
        .rg-dl:active {
            transform: scale(0.98);
        }
        .rg-dl.is-off {
            background: #e2e8f0;
            color: #94a3b8;
            box-shadow: none;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* --- Pagination --- */
        .rg-pager {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            align-items: center;
        }
        .rg-page {
            appearance: none;
            min-width: 2.2rem;
            height: 2.2rem;
            padding: 0 0.7rem;
            border-radius: 9999px;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            font-size: 13px;
            font-weight: 700;
            color: #42474e;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .rg-page:hover:not(:disabled) {
            border-color: #121358;
            color: #121358;
        }
        .rg-page.is-active {
            background: #121358;
            border-color: #121358;
            color: #ffffff;
        }
        .rg-page:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }
        .rg-empty {
            padding: 2.5rem 1rem;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }
        .rg-sample {
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

        @media (max-width: 767px) {
            .rg-table thead th,
            .rg-table tbody td {
                padding: 0.7rem 0.75rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        /*
         |--------------------------------------------------------------
         | DATA REGULASI
         |--------------------------------------------------------------
         | Sepuluh data di bawah diambil dari tampilan lama. Untuk menambah
         | atau mengganti, edit array ini ATAU kirim variabel
         | $daftarRegulasi dari PageController@regulasi (struktur sama).
         |
         | 'jenis'  : dipakai untuk tombol filter
         | 'file'   : URL/path berkas PDF. Isi null jika belum ada
         |            (tombol Unduh otomatis nonaktif), mis.
         |            asset('storage/regulasi/pergub-11-2021.pdf')
         */
        $daftarRegulasi = $daftarRegulasi ?? [
            ['nama' => 'Peraturan Wali Kota Bogor', 'jenis' => 'Peraturan Wali Kota', 'nomor' => 'Nomor 460/KEP.214-DINSOS/2021', 'tentang' => 'Standar Operasional Prosedur Pengajuan Permohonan Jaminan Kesehatan Penerima Bantuan Iuran', 'file' => null],
            ['nama' => 'Keputusan Wali Kota Bogor', 'jenis' => 'Keputusan Wali Kota', 'nomor' => 'Nomor 460/KEP. 196-DINSOS/2021', 'tentang' => 'Penetapan Kriteria Fakir Miskin dan Orang Tidak Mampu di Kota Bogor', 'file' => null],
            ['nama' => 'Peraturan Wali Kota Bogor', 'jenis' => 'Peraturan Wali Kota', 'nomor' => 'Nomor 11 Tahun 2021', 'tentang' => 'Penyelenggaraan Sistem Elektronik Terintegrasi dalam Pendaftaran Penerima Bantuan Iuran Jaminan Kesehatan dan Pendataan Fakir Miskin dan Orang Tidak Mampu', 'file' => null],
            ['nama' => 'Peraturan Menteri Sosial Republik Indonesia Pengelolaan DTKS', 'jenis' => 'Peraturan Menteri', 'nomor' => 'Nomor 5 Tahun 2019', 'tentang' => 'Pengelolaan Data Terpadu Kesejahteraan Sosial', 'file' => null],
            ['nama' => 'Peraturan Presiden Republik Indonesia', 'jenis' => 'Peraturan Presiden', 'nomor' => 'Nomor 82 Tahun 2018', 'tentang' => 'Jaminan Kesehatan', 'file' => null],
            ['nama' => 'Peraturan Menteri Sosial Republik Indonesia', 'jenis' => 'Peraturan Menteri', 'nomor' => 'Nomor 5 Tahun 2016', 'tentang' => 'Pelaksanaan Peraturan Pemerintah Nomor 76 Tahun 2015 tentang Perubahan atas Peraturan Pemerintah Nomor 101 Tahun 2012 tentang Penerima Bantuan Iuran Jaminan Kesehatan', 'file' => null],
            ['nama' => 'Peraturan Menteri Sosial Republik Indonesia', 'jenis' => 'Peraturan Menteri', 'nomor' => 'Nomor 15 Tahun 2018', 'tentang' => 'Sistem Layanan dan Rujukan Terpadu untuk Penanganan Fakir Miskin dan Orang Tidak Mampu', 'file' => null],
            ['nama' => 'Peraturan Menteri Sosial Republik Indonesia', 'jenis' => 'Peraturan Menteri', 'nomor' => 'Nomor 1 Tahun 2018', 'tentang' => 'Program Keluarga Harapan', 'file' => null],
            ['nama' => 'Peraturan Menteri Sosial Republik Indonesia', 'jenis' => 'Peraturan Menteri', 'nomor' => 'Nomor 20 Tahun 2019', 'tentang' => 'Penyaluran Bantuan Nontunai', 'file' => null],
            ['nama' => 'Pedoman Umum Bantuan Non Tunai 2019', 'jenis' => 'Pedoman', 'nomor' => null, 'tentang' => null, 'file' => null],
        ];

        $jenisList = collect($daftarRegulasi)->pluck('jenis')->unique()->values();
        $jumlahBerkas = collect($daftarRegulasi)->filter(fn ($r) => ! empty($r['file']))->count();
    @endphp

    {{-- ============ HERO ============ --}}
    <section class="tp-hero w-full text-white pt-32 pb-20 md:pt-40 md:pb-24">
        <div class="relative z-10 max-w-[1400px] mx-auto px-6 md:px-12 text-center flex flex-col items-center justify-center">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold tracking-wide">
                <span class="material-symbols-outlined text-[16px]">gavel</span>
                Dasar Hukum Pelayanan
            </span>
            <h1 class="mt-5 text-3xl md:text-5xl lg:text-6xl font-bold tracking-tight max-w-4xl drop-shadow-md">
                Regulasi
            </h1>
            <p class="mt-5 text-sm md:text-base text-gray-300 max-w-2xl font-light leading-relaxed">
                Kumpulan peraturan dan pedoman yang menjadi dasar penyelenggaraan pelayanan sosial dan jaminan
                kesehatan di Kota Bogor.
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
            <span class="text-[#121358] font-bold">Regulasi</span>
        </div>
    </div>

    {{-- ============ KONTEN ============ --}}
    <section class="w-full py-12 md:py-16">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12">
            <div class="tp-page-card space-y-8">

                <div class="space-y-2">
                    <h2 class="text-2xl text-[#121358] font-bold leading-snug">Daftar Regulasi</h2>
                    <p class="text-gray-600 leading-relaxed max-w-3xl">
                        Cari regulasi berdasarkan nama, nomor, atau perihal, lalu unduh berkasnya.
                    </p>
                </div>

                {{-- Ringkasan --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rg-stat">
                        <span class="rg-stat-icon"><span class="material-symbols-outlined text-[22px]">description</span></span>
                        <div>
                            <p class="text-gray-800 font-bold text-lg leading-none">{{ count($daftarRegulasi) }}</p>
                            <p class="text-gray-500 text-xs mt-1">Total Regulasi</p>
                        </div>
                    </div>
                    <div class="rg-stat">
                        <span class="rg-stat-icon"><span class="material-symbols-outlined text-[22px]">category</span></span>
                        <div>
                            <p class="text-gray-800 font-bold text-lg leading-none">{{ $jenisList->count() }}</p>
                            <p class="text-gray-500 text-xs mt-1">Jenis Regulasi</p>
                        </div>
                    </div>
                    <div class="rg-stat">
                        <span class="rg-stat-icon"><span class="material-symbols-outlined text-[22px]">download_done</span></span>
                        <div>
                            <p class="text-gray-800 font-bold text-lg leading-none">{{ $jumlahBerkas }}</p>
                            <p class="text-gray-500 text-xs mt-1">Berkas Tersedia</p>
                        </div>
                    </div>
                </div>

                {{-- Filter jenis --}}
                <div class="rg-pills" id="rg-pills" role="tablist">
                    <button type="button" class="rg-pill is-active" data-jenis="">
                        Semua <span class="rg-count">{{ count($daftarRegulasi) }}</span>
                    </button>
                    @foreach ($jenisList as $j)
                        <button type="button" class="rg-pill" data-jenis="{{ $j }}">
                            {{ $j }}
                            <span class="rg-count">{{ collect($daftarRegulasi)->where('jenis', $j)->count() }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Kontrol --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="rg-field md:col-span-3">
                        <label for="rg-ukuran">Tampilkan</label>
                        <div class="rg-field-wrap">
                            <span class="rg-field-icon material-symbols-outlined text-[18px]">format_list_numbered</span>
                            <select id="rg-ukuran">
                                <option value="5">5 data</option>
                                <option value="10" selected>10 data</option>
                                <option value="25">25 data</option>
                                <option value="50">50 data</option>
                            </select>
                        </div>
                    </div>
                    <div class="rg-field md:col-span-9">
                        <label for="rg-cari">Cari Regulasi</label>
                        <div class="rg-field-wrap">
                            <span class="rg-field-icon material-symbols-outlined text-[18px]">search</span>
                            <input id="rg-cari" type="search" placeholder="Cari nama, nomor, atau perihal..."
                                autocomplete="off" />
                        </div>
                    </div>
                </div>

                {{-- Tabel --}}
                <div class="rg-card">
                    <div class="overflow-x-auto">
                        <table class="rg-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NAMA REGULASI</th>
                                    <th>NOMOR REGULASI</th>
                                    <th>TENTANG</th>
                                    <th style="text-align:center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="rg-body">
                                @foreach ($daftarRegulasi as $r)
                                    <tr data-jenis="{{ $r['jenis'] }}"
                                        data-cari="{{ \Illuminate\Support\Str::lower(($r['nama'] ?? '') . ' ' . ($r['nomor'] ?? '') . ' ' . ($r['tentang'] ?? '') . ' ' . ($r['jenis'] ?? '')) }}">
                                        <td class="no"></td>
                                        <td style="min-width: 14rem">
                                            <span class="rg-chip">{{ $r['jenis'] }}</span>
                                            <div class="rg-nama">{{ $r['nama'] }}</div>
                                        </td>
                                        <td class="rg-nomor">
                                            @if (! empty($r['nomor']))
                                                {{ $r['nomor'] }}
                                            @else
                                                <span class="rg-muted">-</span>
                                            @endif
                                        </td>
                                        <td style="min-width: 16rem">
                                            @if (! empty($r['tentang']))
                                                {{ $r['tentang'] }}
                                            @else
                                                <span class="rg-muted">-</span>
                                            @endif
                                        </td>
                                        <td style="text-align:center">
                                            @if (! empty($r['file']))
                                                <a href="{{ $r['file'] }}" target="_blank" rel="noopener" download class="rg-dl">
                                                    <span class="material-symbols-outlined text-[16px]">download</span>
                                                    Unduh
                                                </a>
                                            @else
                                                <span class="rg-dl is-off" title="Berkas belum tersedia">
                                                    <span class="material-symbols-outlined text-[16px]">download</span>
                                                    Unduh
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div id="rg-kosong" class="rg-empty" hidden>
                        <span class="material-symbols-outlined text-[28px] block mb-1 text-gray-400">search_off</span>
                        Tidak ada regulasi yang cocok dengan pencarian Anda.
                    </div>
                </div>

                {{-- Info + pagination --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <p class="text-sm text-gray-500" id="rg-info">Menampilkan 0 dari 0 data</p>
                    <nav class="rg-pager" id="rg-pager" aria-label="Halaman"></nav>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var rows = Array.prototype.slice.call(document.querySelectorAll('#rg-body tr'));
            var pills = document.querySelectorAll('#rg-pills .rg-pill');
            var cari = document.getElementById('rg-cari');
            var ukuran = document.getElementById('rg-ukuran');
            var info = document.getElementById('rg-info');
            var pager = document.getElementById('rg-pager');
            var kosong = document.getElementById('rg-kosong');
            var jenisAktif = '';
            var halaman = 1;

            function cocok() {
                var q = cari.value.trim().toLowerCase();
                return rows.filter(function (tr) {
                    var okJenis = !jenisAktif || tr.getAttribute('data-jenis') === jenisAktif;
                    var okQ = !q || tr.getAttribute('data-cari').indexOf(q) !== -1;
                    return okJenis && okQ;
                });
            }

            function tombol(label, hal, opsi) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'rg-page' + (opsi && opsi.aktif ? ' is-active' : '');
                b.textContent = label;
                if (opsi && opsi.nonaktif) b.disabled = true;
                b.addEventListener('click', function () { halaman = hal; render(); });
                return b;
            }

            function render() {
                var hasil = cocok();
                var per = parseInt(ukuran.value, 10);
                var totalHal = Math.max(1, Math.ceil(hasil.length / per));
                if (halaman > totalHal) halaman = totalHal;
                var mulai = (halaman - 1) * per;
                var akhir = Math.min(mulai + per, hasil.length);

                rows.forEach(function (tr) { tr.style.display = 'none'; });
                hasil.slice(mulai, akhir).forEach(function (tr, i) {
                    tr.style.display = '';
                    tr.querySelector('.no').textContent = mulai + i + 1;
                });

                kosong.hidden = hasil.length !== 0;
                info.textContent = hasil.length
                    ? 'Menampilkan ' + (mulai + 1) + ' sampai ' + akhir + ' dari ' + hasil.length + ' data'
                    : 'Menampilkan 0 dari 0 data';

                pager.innerHTML = '';
                if (hasil.length > per) {
                    pager.appendChild(tombol('Sebelumnya', halaman - 1, { nonaktif: halaman === 1 }));
                    for (var p = 1; p <= totalHal; p++) {
                        pager.appendChild(tombol(String(p), p, { aktif: p === halaman }));
                    }
                    pager.appendChild(tombol('Berikutnya', halaman + 1, { nonaktif: halaman === totalHal }));
                }
            }

            pills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    pills.forEach(function (x) { x.classList.remove('is-active'); });
                    pill.classList.add('is-active');
                    jenisAktif = pill.getAttribute('data-jenis');
                    halaman = 1;
                    render();
                });
            });
            cari.addEventListener('input', function () { halaman = 1; render(); });
            ukuran.addEventListener('change', function () { halaman = 1; render(); });

            render();
        })();
    </script>
@endpush
