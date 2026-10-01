@extends('layouts.app')

@section('title', 'Kesan & Filantropi Peduli - SOLID v4 Dinas Sosial Kota Bogor')

{{-- CSS khusus halaman ini. WAJIB pakai push('head'), karena layout memakai stack('head'). --}}
@push('head')
    <meta name="description"
        content="Program Filantropi Dinas Sosial Kota Bogor untuk berbagi dan menebar manfaat." />
    <style>
        [id] {
            scroll-margin-top: 7.5rem;
        }

        /* --- Hero: foto latar + overlay gradient biru.
           Jika file gambar tidak ada, gradient tetap tampil (tidak broken). --- */
        .fp-hero {
            background:
                linear-gradient(110deg, rgba(0, 40, 69, 0.86) 10%, rgba(0, 59, 98, 0.72) 55%, rgba(19, 98, 153, 0.5) 100%),
                url("{{ asset('assets/img/kesan/kesan.jpeg') }}") center/cover no-repeat,
                #003b62;
            position: relative;
            overflow: hidden;
        }
        .fp-hero::before,
        .fp-hero::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }
        .fp-hero::before {
            width: 22rem;
            height: 22rem;
            right: -6rem;
            top: -8rem;
        }
        .fp-hero::after {
            width: 12rem;
            height: 12rem;
            left: 8%;
            bottom: -4rem;
            border-color: rgba(255, 255, 255, 0.12);
        }
        .fp-hero-icon {
            position: absolute;
            right: 4%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.14;
            pointer-events: none;
            line-height: 0;
        }
        .fp-hero-icon .material-symbols-outlined {
            font-size: 260px;
            color: #ffffff;
        }
        @media (max-width: 900px) {
            .fp-hero-icon {
                display: none;
            }
        }

        /* --- Sidebar Daftar Isi --- */
        .fp-toc-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 0.75rem;
            border-radius: 0.6rem;
            font-size: 13.5px;
            font-weight: 600;
            color: #42474e;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .fp-toc-link:hover,
        .fp-toc-link.is-active {
            background: #edf4ff;
            color: #003b62;
        }
        .fp-toc-link .fp-toc-dot {
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 9999px;
            background: #c2c7cf;
            flex-shrink: 0;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .fp-toc-link:hover .fp-toc-dot,
        .fp-toc-link.is-active .fp-toc-dot {
            background: #136299;
            transform: scale(1.3);
        }

        /* --- Card konten utama --- */
        .fp-page-card {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 6px 20px rgba(55, 65, 81, 0.08);
        }
        @media (min-width: 768px) {
            .fp-page-card {
                padding: 2rem;
            }
        }

        /* --- Kartu jenis bantuan --- */
        .fp-help-card {
            background: #ffffff;
            border: 1px solid #d9e3f1;
            border-radius: 0.9rem;
            padding: 1.4rem 1rem;
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.65rem;
            box-shadow: 0 4px 14px rgba(0, 59, 98, 0.05);
            transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
        }
        .fp-help-card:hover {
            box-shadow: 0 10px 22px rgba(0, 59, 98, 0.13);
            transform: translateY(-4px);
            border-color: #a0caf9;
        }
        .fp-help-icon {
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 9999px;
            background: #e4effd;
            color: #136299;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fp-help-icon .material-symbols-outlined {
            font-size: 26px;
        }
        .fp-help-title {
            font-weight: 700;
            font-size: 14px;
            color: #003b62;
            line-height: 1.3;
        }

        /* --- Kartu grafik (SVG) --- */
        .fp-chart-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.9rem;
            padding: 1.25rem 1.25rem 0.75rem;
            box-shadow: 0 4px 14px rgba(55, 65, 81, 0.1);
            width: 100%;
            box-sizing: border-box;
        }
        #chart-sebaran-wrap {
            width: 100%;
            height: 380px;
        }
        #chart-sebaran-wrap svg {
            width: 100%;
            height: 100%;
            display: block;
        }
        .fp-chart-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }
        .fp-chart-title {
            font-weight: 700;
            font-size: 15px;
            color: #121d26;
        }
        .fp-menu-dots {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #42474e;
        }
        .fp-menu-dots:hover {
            background: #edf4ff;
            color: #003b62;
        }

        /* --- Tombol aksi --- */
        .fp-btn {
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
        .fp-btn:hover {
            transform: translateY(-1px);
        }
        .fp-btn-primary {
            background: #136299;
            color: #ffffff;
        }
        .fp-btn-primary:hover {
            background: #003b62;
        }
        .fp-btn-accent {
            background: #f5820a;
            color: #ffffff;
        }
        .fp-btn-accent:hover {
            background: #cc6900;
        }

        @media (max-width: 1023px) {
            .fp-toc {
                display: none;
            }
        }
    </style>
@endpush

@section('content')
    {{-- ============ HERO ============ --}}
    <section class="fp-hero w-full text-on-primary pt-32 pb-16 md:pt-40 md:pb-20">
        <div class="fp-hero-icon" aria-hidden="true">
            <span class="material-symbols-outlined">volunteer_activism</span>
        </div>
        <div
            class="relative z-10 max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-sm text-center">
            <span class="inline-block font-label-sm text-label-sm tracking-widest uppercase text-secondary-fixed-dim">
                Filantropi Peduli Kota Bogor
            </span>
            <h1
                class="font-headline-lg-mobile text-headline-lg-mobile md:font-display-lg md:text-display-lg font-bold tracking-tight max-w-3xl mx-auto">
                Pilih Donasi Kebaikanmu di Sini
            </h1>
            <p class="max-w-xl mx-auto font-body-md text-body-md text-on-primary/85">
                Berani berbuat baik, karena kebaikan itu akan kembali kepadamu suatu saat nanti.
            </p>
            <div class="pt-space-sm">
                <a href="#penerima" class="fp-btn fp-btn-accent">
                    <span class="material-symbols-outlined text-[18px]">volunteer_activism</span>
                    Lihat Penerima Bantuan
                </a>
            </div>
        </div>
    </section>

    {{-- ============ BREADCRUMB ============ --}}
    <div class="w-full bg-surface-container-lowest border-b border-outline-variant/20">
        <div
            class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop py-space-sm flex items-center gap-space-2xs font-label-sm text-label-sm text-on-surface-variant">
            <a href="{{ route('home') }}" class="hover:text-secondary transition-colors">Home</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-primary font-bold">Kesan &amp; Filantropi Peduli</span>
        </div>
    </div>

    {{-- ============ KONTEN UTAMA ============ --}}
    <section class="w-full py-space-2xl md:py-space-3xl">
        <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
            <div class="fp-page-card grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-start">

                {{-- Sidebar Daftar Isi (sticky, desktop) --}}
                <aside class="fp-toc lg:col-span-3 sticky top-28 space-y-space-sm">
                    <div
                        class="bg-surface-container-lowest rounded-xl border border-outline-variant/20 p-space-md space-y-1">
                        <a class="fp-toc-link" data-toc href="#tentang"><span class="fp-toc-dot"></span>Tentang
                            Program</a>
                        <a class="fp-toc-link" data-toc href="#bantuan"><span class="fp-toc-dot"></span>Jenis Bantuan</a>
                        <a class="fp-toc-link" data-toc href="#penerima"><span class="fp-toc-dot"></span>Sebaran Calon
                            Penerima</a>
                        <a class="fp-toc-link" data-toc href="#kontak"><span class="fp-toc-dot"></span>Hubungi Kami</a>
                    </div>
                    <a href="#penerima"
                        class="w-full flex items-center justify-center gap-space-xs px-space-md py-3 rounded-lg bg-secondary hover:bg-primary text-on-primary font-label-md text-label-md transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">volunteer_activism</span>
                        <span>Lihat Penerima Bantuan</span>
                    </a>
                </aside>

                {{-- Isi konten --}}
                <div class="lg:col-span-9 space-y-space-2xl">

                    {{-- Tentang Program --}}
                    <div id="tentang" class="space-y-space-md">
                        <div class="space-y-space-2xs max-w-3xl">
                            <span class="font-label-sm text-label-sm text-secondary tracking-widest uppercase">Tentang
                                Program</span>
                            <h2
                                class="font-headline-lg text-headline-lg text-primary font-bold leading-snug flex items-center gap-space-xs">
                                <span class="material-symbols-outlined text-[28px] text-secondary">eco</span>
                                Bersama Kita Hadirkan Harapan
                            </h2>
                        </div>

                        <div class="max-w-3xl space-y-space-sm font-body-md text-body-md text-on-surface-variant pt-5">
                            <p>
                                Di setiap sudut Kota Bogor, masih ada saudara-saudara kita yang membutuhkan uluran
                                tangan dan kepedulian bersama. Satu kebaikan yang kita berikan hari ini, dapat menjadi
                                harapan besar bagi kehidupan mereka esok hari.
                            </p>
                            <p>
                                Melalui Program Filantropi Dinas Sosial Kota Bogor, kami mengajak para dermawan,
                                donatur, dan seluruh masyarakat untuk bersama-sama berbagi dan menebar manfaat melalui
                                berbagai program kepedulian sosial, antara lain:
                            </p>
                            <ul class="space-y-space-xs max-w-2xl">
                                <li class="flex items-start gap-space-xs">
                                    <span
                                        class="material-symbols-outlined text-[20px] text-secondary shrink-0">redeem</span>
                                    <span><strong class="text-on-surface">Paket Sembako</strong> untuk meringankan
                                        beban kebutuhan pangan masyarakat kurang mampu.</span>
                                </li>
                                <li class="flex items-start gap-space-xs">
                                    <span
                                        class="material-symbols-outlined text-[20px] text-secondary shrink-0">backpack</span>
                                    <span><strong class="text-on-surface">Peralatan Sekolah</strong> demi mendukung
                                        semangat belajar generasi penerus bangsa.</span>
                                </li>
                                <li class="flex items-start gap-space-xs">
                                    <span
                                        class="material-symbols-outlined text-[20px] text-secondary shrink-0">school</span>
                                    <span><strong class="text-on-surface">Bantuan Biaya Pendidikan</strong> agar
                                        keterbatasan ekonomi tidak memutus cita-cita.</span>
                                </li>
                                <li class="flex items-start gap-space-xs">
                                    <span
                                        class="material-symbols-outlined text-[20px] text-secondary shrink-0">medical_services</span>
                                    <span><strong class="text-on-surface">Bantuan Kesehatan</strong> untuk membantu
                                        mendapatkan layanan kesehatan yang layak.</span>
                                </li>
                                <li class="flex items-start gap-space-xs">
                                    <span
                                        class="material-symbols-outlined text-[20px] text-secondary shrink-0">favorite</span>
                                    <span><strong class="text-on-surface">Bentuk Kepedulian Lainnya</strong> sesuai
                                        kebutuhan masyarakat yang terdampak.</span>
                                </li>
                            </ul>
                            <p>
                                Setiap donasi yang Anda berikan bukan sekadar bantuan materi, tetapi juga wujud kasih
                                sayang, kepedulian, dan harapan baru bagi sesama.
                            </p>
                            <p class="font-bold text-secondary">
                                Mari bergandeng tangan, menyalakan harapan, dan mewujudkan Kota Bogor yang lebih
                                peduli, berkeadilan, dan berperikemanusiaan.
                            </p>
                            <p class="italic">
                                Karena berbagi bukan tentang seberapa besar yang kita miliki, tetapi seberapa tulus
                                kita peduli.
                            </p>
                        </div>
                    </div>

                    {{-- Jenis Bantuan --}}
                    <div id="bantuan" class="space-y-space-md">
                        <div class="space-y-space-2xs">
                            <h3 class="font-headline-md text-headline-md text-primary font-bold">
                                Jenis Bantuan yang Dapat Disalurkan
                            </h3>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">
                                Pilih bentuk kebaikan yang ingin Anda salurkan.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-space-md">
                            <a class="fp-help-card" href="#penerima">
                                <span class="fp-help-icon"><span class="material-symbols-outlined">redeem</span></span>
                                <span class="fp-help-title">Paket Sembako</span>
                            </a>
                            <a class="fp-help-card" href="#penerima">
                                <span class="fp-help-icon"><span class="material-symbols-outlined">backpack</span></span>
                                <span class="fp-help-title">Peralatan Sekolah</span>
                            </a>
                            <a class="fp-help-card" href="#penerima">
                                <span class="fp-help-icon"><span class="material-symbols-outlined">school</span></span>
                                <span class="fp-help-title">Biaya Pendidikan</span>
                            </a>
                            <a class="fp-help-card" href="#penerima">
                                <span class="fp-help-icon"><span
                                        class="material-symbols-outlined">medical_services</span></span>
                                <span class="fp-help-title">Bantuan Kesehatan</span>
                            </a>
                            <a class="fp-help-card" href="#penerima">
                                <span class="fp-help-icon"><span class="material-symbols-outlined">favorite</span></span>
                                <span class="fp-help-title">Bentuk Kepedulian Lain</span>
                            </a>
                        </div>
                    </div>

                    {{-- Sebaran Calon Penerima (grafik SVG murni) --}}
                    <div id="penerima" class="space-y-space-md">
                        <div class="space-y-space-2xs">
                            <h3 class="font-headline-md text-headline-md text-primary font-bold">
                                Sebaran Calon Penerima Bantuan di Kota Bogor
                            </h3>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">
                                Data warga desil 1 yang belum menerima bantuan apa pun.
                            </p>
                        </div>

                        <div class="fp-chart-card">
                            <div class="fp-chart-head relative">
                                <span class="fp-chart-title">Distribusi Calon Penerima per Kecamatan</span>
                                <span class="fp-menu-dots"><span
                                        class="material-symbols-outlined text-[20px]">menu</span></span>
                            </div>
                            <div id="chart-sebaran-wrap">
                                <svg id="chart-sebaran" viewBox="0 0 720 380" preserveAspectRatio="xMidYMid meet"
                                    role="img"
                                    aria-label="Grafik distribusi calon penerima bantuan per kecamatan di Kota Bogor"></svg>
                            </div>
                        </div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">
                            Data diambil dari Sistem Informasi SOLID Dinas Sosial Kota Bogor
                        </p>
                    </div>

                    {{-- CTA penutup --}}
                    <div id="kontak"
                        class="bg-primary rounded-xl p-space-xl flex flex-col md:flex-row items-center justify-between gap-space-md text-on-primary">
                        <div class="space-y-1">
                            <h4 class="font-headline-sm text-headline-sm font-bold">Ingin ikut berbagi kebaikan?</h4>
                            <p class="font-body-sm text-body-sm text-on-primary/80">
                                Hubungi Dinas Sosial Kota Bogor untuk informasi lebih lanjut seputar program filantropi
                                dan penyaluran donasi.
                            </p>
                        </div>
                        <a href="https://wa.me/6285333395667" rel="noreferrer" target="_blank"
                            class="shrink-0 inline-flex items-center gap-space-xs px-space-lg py-3 rounded-lg bg-surface-container-lowest text-primary font-label-md text-label-md hover:bg-white transition-colors">
                            <span class="material-symbols-outlined text-[20px]">chat</span>
                            Hubungi Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        /* Scrollspy: sorot item Daftar Isi sesuai section yang sedang dibaca */
        (function () {
            var tocLinks = Array.prototype.slice.call(document.querySelectorAll(".fp-toc-link[data-toc]"));
            if (!tocLinks.length) return;

            var sections = tocLinks
                .map(function (link) {
                    return document.getElementById(link.getAttribute("href").replace("#", ""));
                })
                .filter(Boolean);

            function setActive(id) {
                tocLinks.forEach(function (link) {
                    link.classList.toggle("is-active", link.getAttribute("href") === "#" + id);
                });
            }

            var observer = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) setActive(entry.target.id);
                    });
                },
                { rootMargin: "-45% 0px -50% 0px", threshold: 0 }
            );

            sections.forEach(function (section) {
                observer.observe(section);
            });
        })();

        /* ============================================================
           Grafik "Sebaran Calon Penerima" — SVG murni, tanpa library.
           Ubah dataSebaran untuk memperbarui data (mis. dari API).
           ============================================================ */
        var dataSebaran = {
            categories: ["Bogor Selatan", "Bogor Barat", "Bogor Utara", "Tanah Sareal", "Bogor Timur", "Bogor Tengah"],
            values: [2050, 1670, 1380, 1150, 1020, 950],
        };

        var barColors = ["#4f46e5", "#ec4899", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6"];

        function renderBarChart(svgId, categories, values, colors) {
            var svg = document.getElementById(svgId);
            if (!svg) return;

            var NS = "http://www.w3.org/2000/svg";
            var width = 720;
            var height = 380;
            var padding = { top: 20, right: 16, bottom: 46, left: 60 };
            var chartW = width - padding.left - padding.right;
            var chartH = height - padding.top - padding.bottom;

            var maxVal = Math.max.apply(null, values);
            var niceMax = Math.ceil(maxVal / 500) * 500 || 1;
            var steps = 5;
            var barGap = 22;
            var barW = (chartW - barGap * (categories.length - 1)) / categories.length;

            function el(tag, attrs, text) {
                var node = document.createElementNS(NS, tag);
                for (var key in attrs) {
                    node.setAttribute(key, attrs[key]);
                }
                if (text !== undefined) node.textContent = text;
                return node;
            }

            while (svg.firstChild) svg.removeChild(svg.firstChild);
            svg.setAttribute("viewBox", "0 0 " + width + " " + height);

            /* Grid horizontal + label sumbu Y */
            for (var s = 0; s <= steps; s++) {
                var val = Math.round((niceMax / steps) * s);
                var y = padding.top + chartH - (val / niceMax) * chartH;

                svg.appendChild(el("line", {
                    x1: padding.left, x2: width - padding.right, y1: y, y2: y,
                    stroke: "#e4e9f0", "stroke-width": 1,
                }));
                svg.appendChild(el("text", {
                    x: padding.left - 10, y: y + 4, "text-anchor": "end", "font-size": "11",
                    fill: "#42474e", "font-family": "'Plus Jakarta Sans', sans-serif",
                }, val.toLocaleString("id-ID")));
            }

            /* Batang + label */
            categories.forEach(function (cat, idx) {
                var value = values[idx] || 0;
                var barH = (value / niceMax) * chartH;
                var x = padding.left + idx * (barW + barGap);
                var y = padding.top + chartH - barH;
                var color = colors[idx % colors.length];

                var rect = el("rect", {
                    x: x, y: y, width: barW, height: Math.max(barH, 1), rx: 8, ry: 8, fill: color,
                });
                rect.appendChild(el("title", {}, cat + ": " + value.toLocaleString("id-ID") + " calon penerima"));
                svg.appendChild(rect);

                svg.appendChild(el("text", {
                    x: x + barW / 2, y: y - 8, "text-anchor": "middle", "font-size": "12", "font-weight": "700",
                    fill: "#121d26", "font-family": "'Plus Jakarta Sans', sans-serif",
                }, value.toLocaleString("id-ID")));

                svg.appendChild(el("text", {
                    x: x + barW / 2, y: height - padding.bottom + 20, "text-anchor": "middle", "font-size": "11",
                    "font-weight": "700", fill: "#003b62", "font-family": "'Plus Jakarta Sans', sans-serif",
                }, cat));
            });

            /* Garis dasar sumbu X */
            svg.appendChild(el("line", {
                x1: padding.left, x2: width - padding.right,
                y1: padding.top + chartH, y2: padding.top + chartH,
                stroke: "#c2c7cf", "stroke-width": 1.5,
            }));
        }

        renderBarChart("chart-sebaran", dataSebaran.categories, dataSebaran.values, barColors);

        window.addEventListener("resize", function () {
            renderBarChart("chart-sebaran", dataSebaran.categories, dataSebaran.values, barColors);
        });
    </script>
@endpush
