@extends('layouts.app')

@section('title', 'Kejadian Bencana - SOLID v4 Dinas Sosial Kota Bogor')

{{-- CSS + library khusus halaman ini. WAJIB pakai push('head'), karena layout memakai stack('head'). --}}
@push('head')
    {{-- Highcharts core + modul 3D (satu file). Harus dimuat sebelum script inisialisasi grafik. --}}
    <script src="{{ asset('js/highcharts.bundle.js') }}"></script>
    <style>
        [id] {
            scroll-margin-top: 6.5rem;
        }

        /* --- Hero (sama dengan hero halaman tutorial) --- */
        .kb-hero {
            background:
                radial-gradient(1100px 480px at 12% -10%, rgba(19, 98, 153, 0.55), transparent 60%),
                radial-gradient(900px 420px at 90% 120%, rgba(0, 64, 58, 0.35), transparent 55%),
                linear-gradient(135deg, #002845 0%, #003b62 45%, #0c527f 100%);
            position: relative;
            overflow: hidden;
        }
        .kb-hero::before,
        .kb-hero::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }
        .kb-hero::before {
            width: 22rem;
            height: 22rem;
            right: -6rem;
            top: -8rem;
        }
        .kb-hero::after {
            width: 12rem;
            height: 12rem;
            left: 8%;
            bottom: -4rem;
            border-color: rgba(255, 255, 255, 0.12);
        }

        .kb-data-section {
            background: #f7f9ff;
        }

        /* --- Card halaman & kartu grafik --- */
        .kb-page-card {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 6px 20px rgba(55, 65, 81, 0.08);
        }
        @media (min-width: 768px) {
            .kb-page-card {
                padding: 2rem;
            }
        }
        .kb-chart-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.9rem;
            padding: 1.25rem 1.25rem 0.75rem;
            box-shadow: 0 4px 14px rgba(55, 65, 81, 0.1);
            width: 100%;
            box-sizing: border-box;
        }
        #chart-bulan,
        #chart-kecamatan {
            width: 100%;
        }
        .kb-chart-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        .kb-chart-title {
            font-weight: 700;
            font-size: 15px;
            color: #121d26;
        }
        .kb-menu-dots {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #42474e;
        }
        .kb-menu-dots:hover {
            background: #edf4ff;
            color: #003b62;
        }

        /* --- Filter tahun --- */
        .kb-select-wrap {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }
        .kb-select {
            appearance: none;
            border: 1px solid #c2c7cf;
            border-radius: 0.6rem;
            padding: 0.5rem 2.2rem 0.5rem 0.9rem;
            font-size: 14px;
            font-weight: 600;
            color: #121d26;
            background: #ffffff url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2342474e'><path d='M5.25 7.5l4.75 5 4.75-5z'/></svg>") no-repeat right 0.7rem center;
            background-size: 16px;
            cursor: pointer;
        }
        .kb-select:focus {
            outline: none;
            border-color: #136299;
            box-shadow: 0 0 0 3px rgba(19, 98, 153, 0.15);
        }

        /* --- Tombol aksi --- */
        .kb-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 1.5rem;
            border-radius: 0.6rem;
            font-weight: 700;
            font-size: 14px;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .kb-btn:hover {
            transform: translateY(-1px);
        }
        .kb-btn-primary {
            background: #136299;
            color: #ffffff;
        }
        .kb-btn-primary:hover {
            background: #003b62;
        }
        .kb-btn-accent {
            background: #f5820a;
            color: #ffffff;
        }
        .kb-btn-accent:hover {
            background: #cc6900;
        }
    </style>
@endpush

@section('content')
    {{-- ============ HERO ============ --}}
    <section class="kb-hero w-full text-on-primary pt-32 pb-16 md:pt-40 md:pb-20">
        <div
            class="relative z-10 max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-sm text-center">
            <h1
                class="font-headline-lg-mobile text-headline-lg-mobile md:font-display-lg md:text-display-lg font-bold tracking-tight max-w-3xl mx-auto">
                Kejadian Bencana
            </h1>
        </div>
    </section>

    {{-- ============ BREADCRUMB ============ --}}
    <div class="w-full bg-surface-container-lowest border-b border-outline-variant/20">
        <div
            class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop py-space-sm flex items-center gap-space-2xs font-label-sm text-label-sm text-on-surface-variant">
            <a href="{{ route('home') }}" class="hover:text-secondary transition-colors">Home</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-primary font-bold">Kejadian Bencana</span>
        </div>
    </div>

    {{-- ============ KONTEN UTAMA ============ --}}
    <section class="kb-data-section w-full py-space-2xl md:py-space-3xl">
        <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
            <div class="kb-page-card space-y-space-2xl">

                <div class="space-y-space-xs max-w-3xl mx-auto text-center">
                    <h2 class="font-headline-lg text-headline-lg text-primary font-bold leading-snug">
                        GRAFIK DATA KEJADIAN BENCANA DI WILAYAH KOTA BOGOR JAWA BARAT
                    </h2>
                </div>

                {{-- Filter tahun --}}
                <div class="flex items-center justify-center gap-space-sm">
                    <span class="font-label-md text-label-md text-on-surface-variant">Tahun</span>
                    <div class="kb-select-wrap">
                        <select id="filter-tahun" class="kb-select">
                            <option value="2026" selected>2026</option>
                            <option value="2025">2025</option>
                        </select>
                    </div>
                </div>

                {{-- Grafik per Bulan --}}
                <div id="grafik-bulan" class="space-y-space-md">
                    <div class="kb-chart-card text-center">
                        <div class="kb-chart-head justify-center relative">
                            <span class="kb-chart-title" id="judul-chart-bulan">Grafik Kejadian Bencana Tahun 2026 di
                                Bulan</span>
                            <span class="kb-menu-dots absolute right-0"><span
                                    class="material-symbols-outlined text-[20px]">menu</span></span>
                        </div>
                        <div id="chart-bulan" style="height: 380px"></div>
                    </div>
                </div>

                {{-- Grafik per Kecamatan --}}
                <div id="grafik-kecamatan" class="space-y-space-md">
                    <div class="kb-chart-card text-center">
                        <div class="kb-chart-head justify-center relative">
                            <span class="kb-chart-title">Grafik Kejadian Bencana Tahun di Kecamatan</span>
                            <span class="kb-menu-dots absolute right-0"><span
                                    class="material-symbols-outlined text-[20px]">menu</span></span>
                        </div>
                        <div id="chart-kecamatan" style="height: 380px"></div>
                    </div>
                </div>

                {{-- Tombol aksi --}}
                <div class="flex flex-col sm:flex-row justify-center gap-space-sm">
                    <a href="https://pelayanansosial.kotabogor.go.id/index.php/menu/home" rel="noreferrer"
                        target="_blank" class="kb-btn kb-btn-primary">
                        <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                        Operator Login
                    </a>
                    <a href="{{ asset('assets/pdf/Modul_Pembelajaran_Dokumen.pdf') }}" target="_blank"
                        rel="noreferrer" class="kb-btn kb-btn-accent">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Tutorial Menu Kebencanaan
                    </a>
                </div>

                {{-- Lokasi Kantor --}}
                <div id="lokasi" class="space-y-space-md text-center">
                    <h3 class="font-headline-md text-headline-md text-primary font-bold">
                        Lokasi Kantor Dinas Sosial Kota Bogor
                    </h3>
                    <div class="rounded-xl overflow-hidden border border-outline-variant/20 shadow-sm">
                        <iframe title="Peta Lokasi Dinas Sosial Kota Bogor"
                            src="https://www.google.com/maps?q=Dinas%20Sosial%20Kota%20Bogor%2C%20Jl.%20Merdeka%20No.142%2C%20Ciwaringin%2C%20Bogor%20Tengah%2C%20Kota%20Bogor&output=embed"
                            width="100%" height="380" style="border: 0; display: block" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-center gap-space-sm bg-surface-container-lowest rounded-xl border border-outline-variant/20 p-space-md">
                        <div class="flex items-start gap-space-xs text-left">
                            <span class="material-symbols-outlined text-primary text-[22px]">location_on</span>
                            <div>
                                <p class="font-label-md text-label-md text-on-surface font-bold">Dinas Sosial Kota Bogor
                                </p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">
                                    Jl. Merdeka No.142, RT.01/RW.01, Ciwaringin, Kecamatan Bogor Tengah, Kota Bogor,
                                    Jawa Barat 16124
                                </p>
                            </div>
                        </div>
                        <a href="https://www.google.com/maps?q=Dinas+Sosial+Kota+Bogor" target="_blank"
                            rel="noreferrer"
                            class="shrink-0 inline-flex items-center justify-center gap-space-xs px-space-md py-2.5 rounded-lg bg-primary hover:bg-secondary text-on-primary font-label-sm text-label-sm transition-colors">
                            <span class="material-symbols-outlined text-[18px]">directions</span>
                            Buka di Google Maps
                        </a>
                    </div>
                </div>

                {{-- CTA penutup --}}
                <div
                    class="bg-primary rounded-xl p-space-xl flex flex-col md:flex-row items-center justify-center gap-space-md text-on-primary text-center md:text-left">
                    <div class="space-y-1">
                        <h4 class="font-headline-sm text-headline-sm font-bold">Ingin lihat data lebih detail?</h4>
                        <p class="font-body-sm text-body-sm text-on-primary/80">
                            Masuk sebagai operator untuk mengakses data kebencanaan lengkap.
                        </p>
                    </div>
                    <a href="https://pelayanansosial.kotabogor.go.id/index.php/menu/home" rel="noreferrer"
                        target="_blank"
                        class="shrink-0 inline-flex items-center gap-space-xs px-space-lg py-3 rounded-lg bg-surface-container-lowest text-primary font-label-md text-label-md hover:bg-white transition-colors">
                        <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                        Operator Login
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        /* ============================================================
           Data grafik (dummy — sambungkan ke backend untuk data real).
           ============================================================ */
        var dataPerTahun = {
            2026: {
                bulan: {
                    categories: ["Januari", "Februari", "Maret", "April", "Mei", "Juni"],
                    values: [25, 20, 65, 40, 30, 12],
                },
            },
            2025: {
                bulan: {
                    categories: ["Januari", "Februari", "Maret", "April", "Mei", "Juni"],
                    values: [30, 24, 77, 47, 33, 10],
                },
            },
        };

        var dataKecamatan = {
            categories: ["Bogor Barat", "Bogor Selatan", "Bogor Tengah", "Bogor Timur", "Bogor Utara", "Tanah Sareal"],
            values: [22, 48, 23, 9, 19, 25],
        };

        var barColors = ["#29b6f6", "#3f2b96", "#22c55e", "#fb7f2e", "#5b7fa6", "#d63bd6"];

        /* Opsi dasar kedua grafik (kolom 3D) */
        function buildColumnOptions(categories, values) {
            return {
                /* Highcharts v13 ikut mode gelap/terang sistem operasi. Halaman ini selalu
                   berlatar terang, jadi paksa skema terang supaya label sumbu tetap terbaca. */
                palette: { colorScheme: "light" },
                chart: {
                    type: "column",
                    options3d: { enabled: true, alpha: 10, beta: 15, depth: 50, viewDistance: 25 },
                    backgroundColor: "transparent",
                },
                title: { text: null },
                credits: { text: "Highcharts.com" },
                xAxis: {
                    categories: categories,
                    labels: { style: { fontWeight: "700", color: "#003b62" } },
                },
                yAxis: {
                    title: { text: "Total Bencana" },
                    gridLineColor: "#e4e9f0",
                },
                legend: { enabled: false },
                tooltip: {
                    headerFormat: "<b>{point.key}</b><br/>",
                    pointFormat: "Total Bencana: <b>{point.y}</b>",
                },
                plotOptions: {
                    column: {
                        depth: 35,
                        colorByPoint: true,
                        colors: barColors,
                        dataLabels: {
                            enabled: true,
                            style: { fontWeight: "700", color: "#121d26", textOutline: "none" },
                        },
                    },
                },
                series: [{ name: "Total Bencana", data: values }],
            };
        }

        var chartBulan = Highcharts.chart(
            "chart-bulan",
            buildColumnOptions(dataPerTahun["2026"].bulan.categories, dataPerTahun["2026"].bulan.values)
        );

        var chartKecamatan = Highcharts.chart(
            "chart-kecamatan",
            buildColumnOptions(dataKecamatan.categories, dataKecamatan.values)
        );

        /* Filter tahun -> update grafik per bulan */
        document.getElementById("filter-tahun").addEventListener("change", function (e) {
            var tahun = e.target.value;
            var data = dataPerTahun[tahun];
            if (!data) return;

            chartBulan.update({ xAxis: { categories: data.bulan.categories } });
            chartBulan.series[0].setData(data.bulan.values);

            var judulChart = document.getElementById("judul-chart-bulan");
            if (judulChart) {
                judulChart.textContent = "Grafik Kejadian Bencana Tahun " + tahun + " di Bulan";
            }
        });
    </script>
@endpush
