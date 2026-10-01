@extends('layouts.app')

@section('title', 'Tutorial Pendaftaran BPJS PBI - SOLID v4 Dinas Sosial Kota Bogor')

{{-- CSS khusus halaman ini. WAJIB pakai push('head'), karena layout memakai stack('head'). --}}
@push('head')
    <style>
        [id] {
            scroll-margin-top: 7.5rem;
        }

        /* --- Hero --- */
        .tp-hero {
            background:
                radial-gradient(1100px 480px at 12% -10%, rgba(19, 98, 153, 0.55), transparent 60%),
                radial-gradient(900px 420px at 90% 120%, rgba(0, 64, 58, 0.35), transparent 55%),
                linear-gradient(135deg, #002845 0%, #003b62 45%, #0c527f 100%);
            position: relative;
            overflow: hidden;
        }
        .tp-hero::before,
        .tp-hero::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.14);
        }
        .tp-hero::before {
            width: 22rem;
            height: 22rem;
            right: -6rem;
            top: -8rem;
        }
        .tp-hero::after {
            width: 12rem;
            height: 12rem;
            left: 8%;
            bottom: -4rem;
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* --- Sidebar Daftar Isi --- */
        .tp-toc-link {
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
        .tp-toc-link:hover,
        .tp-toc-link.is-active {
            background: #edf4ff;
            color: #003b62;
        }
        .tp-toc-link .tp-toc-dot {
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 9999px;
            background: #c2c7cf;
            flex-shrink: 0;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .tp-toc-link:hover .tp-toc-dot,
        .tp-toc-link.is-active .tp-toc-dot {
            background: #136299;
            transform: scale(1.3);
        }

        /* --- Card konten utama (hanya visual; lebar diatur wrapper di luarnya) --- */
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

        /* --- Kartu dokumen --- */
        .tp-doc-card {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 1.1rem;
            border-radius: 0.85rem;
            background: #ffffff;
            border: 1px solid #e4e9f0;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }
        .tp-doc-card:hover {
            border-color: #98cbff;
            transform: translateY(-2px);
        }
        .tp-doc-icon {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.65rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e4effd;
            color: #003b62;
        }

        /* --- Stepper --- */
        .tp-steps {
            position: relative;
        }
        .tp-step {
            position: relative;
            display: grid;
            grid-template-columns: 2.5rem 1fr;
            gap: 1rem;
            padding-bottom: 1.5rem;
        }
        .tp-step:last-child {
            padding-bottom: 0;
        }
        .tp-step-num {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            background: #003b62;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            position: relative;
            z-index: 2;
        }
        .tp-step::before {
            content: "";
            position: absolute;
            left: 1.25rem;
            top: 2.5rem;
            bottom: -0.1rem;
            width: 2px;
            background: #d9e3f1;
            z-index: 1;
        }
        .tp-step:last-child::before {
            display: none;
        }
        .tp-step-body {
            padding-top: 0.3rem;
        }
        .tp-step-body p {
            color: #42474e;
        }

        .tp-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.7rem;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 700;
            background: #fbbf24;
            color: #4a3200;
        }
        .tp-chip.tp-chip-blue {
            background: #136299;
            color: #ffffff;
        }

        /* --- Catatan --- */
        .tp-note {
            border-radius: 0.85rem;
            border: 1px solid #f6dd9a;
            background: #fff7e1;
            padding: 1.1rem 1.25rem;
        }
        .tp-note-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: #7a4d00;
            margin-bottom: 0.55rem;
        }
        .tp-note ol {
            color: #6b5417;
        }

        @media (max-width: 1023px) {
            .tp-toc {
                display: none;
            }
        }
    </style>
@endpush

@section('content')
    {{-- ============ HERO ============ --}}
    <section class="tp-hero w-full text-on-primary pt-32 pb-16 md:pt-40 md:pb-20">
        <div
            class="relative z-10 max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-sm text-center">
            <h1
                class="font-headline-lg-mobile text-headline-lg-mobile md:font-display-lg md:text-display-lg font-bold tracking-tight max-w-3xl mx-auto">
                Tutorial Pendaftaran BPJS PBI
            </h1>
        </div>
    </section>

    {{-- ============ BREADCRUMB ============ --}}
    <div class="w-full bg-surface-container-lowest border-b border-outline-variant/20">
        <div
            class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop py-space-sm flex items-center gap-space-2xs font-label-sm text-label-sm text-on-surface-variant">
            <a href="{{ route('home') }}" class="hover:text-secondary transition-colors">Home</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-primary font-bold">Tutorial Pendaftaran BPJS PBI</span>
        </div>
    </div>

    {{-- ============ KONTEN UTAMA ============ --}}
    <section class="w-full py-space-2xl md:py-space-3xl">
        <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
            <div class="tp-page-card grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-start">

                {{-- Sidebar Daftar Isi (sticky, desktop) --}}
                <aside class="tp-toc lg:col-span-3 sticky top-28 space-y-space-sm">
                    <div
                        class="bg-surface-container-lowest rounded-xl border border-outline-variant/20 p-space-md space-y-1">
                        <a class="tp-toc-link" data-toc href="#dokumen"><span class="tp-toc-dot"></span>Dokumen yang
                            Disiapkan</a>
                        <a class="tp-toc-link" data-toc href="#buka-akun"><span class="tp-toc-dot"></span>Membuka
                            Formulir Akun</a>
                        <a class="tp-toc-link" data-toc href="#lengkapi-data"><span class="tp-toc-dot"></span>Melengkapi
                            Data &amp; Aktivasi</a>
                        <a class="tp-toc-link" data-toc href="#video"><span class="tp-toc-dot"></span>Video
                            Tutorial</a>
                        <a class="tp-toc-link" data-toc href="#lokasi"><span class="tp-toc-dot"></span>Lokasi Kantor</a>
                    </div>
                    <a href="{{ route('login') }}"
                        class="w-full flex items-center justify-center gap-space-xs px-space-md py-3 rounded-lg bg-secondary hover:bg-primary text-on-primary font-label-md text-label-md transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">how_to_reg</span>
                        <span>Daftar Akun Sekarang</span>
                    </a>
                </aside>

                {{-- Isi tutorial --}}
                <div class="lg:col-span-9 space-y-space-2xl">
                    <div class="space-y-space-xs max-w-3xl">
                        <h2 class="font-headline-lg text-headline-lg text-primary font-bold leading-snug">
                            PANDUAN PENDAFTARAN AKUN PELAYANAN SOSIAL JAMINAN KESEHATAN PENERIMA BANTUAN IURAN DINAS
                            SOSIAL KOTA BOGOR
                        </h2>
                    </div>

                    {{-- A. Dokumen --}}
                    <div id="dokumen" class="space-y-space-md">
                        <div class="flex items-center gap-space-xs">
                            <span class="tp-step-num"
                                style="width: 2.25rem; height: 2.25rem; font-size: 13px">A</span>
                            <h3 class="font-headline-md text-headline-md text-primary font-bold">
                                Dokumen yang Perlu Disiapkan
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-sm">
                            <div class="tp-doc-card">
                                <span class="tp-doc-icon"><span
                                        class="material-symbols-outlined text-[22px]">badge</span></span>
                                <div>
                                    <p class="font-label-md text-label-md text-on-surface font-bold">Scan KTP</p>
                                    <p class="font-body-sm text-body-sm text-on-surface-variant">Khusus warga ber-KTP
                                        Kota Bogor.</p>
                                </div>
                            </div>
                            <div class="tp-doc-card">
                                <span class="tp-doc-icon"><span
                                        class="material-symbols-outlined text-[22px]">groups</span></span>
                                <div>
                                    <p class="font-label-md text-label-md text-on-surface font-bold">Scan Kartu Keluarga
                                    </p>
                                    <p class="font-body-sm text-body-sm text-on-surface-variant">Kartu Keluarga yang
                                        masih berlaku.</p>
                                </div>
                            </div>
                            <div class="tp-doc-card">
                                <span class="tp-doc-icon"><span
                                        class="material-symbols-outlined text-[22px]">mail</span></span>
                                <div>
                                    <p class="font-label-md text-label-md text-on-surface font-bold">Alamat E-Mail
                                        Perorangan</p>
                                    <p class="font-body-sm text-body-sm text-on-surface-variant">Email pribadi yang
                                        aktif digunakan.</p>
                                </div>
                            </div>
                            <div class="tp-doc-card">
                                <span class="tp-doc-icon"><span
                                        class="material-symbols-outlined text-[22px]">call</span></span>
                                <div>
                                    <p class="font-label-md text-label-md text-on-surface font-bold">Nomor Telepon</p>
                                    <p class="font-body-sm text-body-sm text-on-surface-variant">Nomor aktif dan dapat
                                        dihubungi.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- B. Membuka formulir akun --}}
                    <div id="buka-akun" class="space-y-space-md">
                        <div class="flex items-center gap-space-xs">
                            <span class="tp-step-num"
                                style="width: 2.25rem; height: 2.25rem; font-size: 13px">B</span>
                            <h3 class="font-headline-md text-headline-md text-primary font-bold">
                                Bagaimana Cara Mendaftar / Memperoleh Akun?
                            </h3>
                        </div>

                        <div
                            class="bg-surface-container-lowest rounded-xl border border-outline-variant/20 p-space-lg">
                            <div class="tp-steps">
                                <div class="tp-step">
                                    <span class="tp-step-num">1</span>
                                    <div class="tp-step-body">
                                        <p>
                                            Silakan mengisi form pendaftaran pada link berikut:
                                            <a href="{{ route('login') }}" class="tp-chip">
                                                <span class="material-symbols-outlined text-[15px]">link</span>
                                                Daftar Akun
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">2</span>
                                    <div class="tp-step-body">
                                        <p>
                                            Klik tulisan
                                            <span class="tp-chip tp-chip-blue">Register</span>
                                            pada halaman yang terbuka.
                                        </p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">3</span>
                                    <div class="tp-step-body">
                                        <p>Isi form Kartu Keluarga (KK) pendaftar.</p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">4</span>
                                    <div class="tp-step-body">
                                        <p>Isi kode captcha yang tertera pada layar.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tp-note">
                            <p class="tp-note-title">
                                <span class="material-symbols-outlined text-[20px]">info</span>
                                Catatan #1
                            </p>
                            <ol class="list-decimal list-inside space-y-1 font-body-sm text-body-sm">
                                <li>
                                    Jika NIK calon peserta <strong>terdaftar</strong> di Disdukcapil, calon peserta bisa
                                    melanjutkan pendaftaran dan mengisi form berikutnya.
                                </li>
                                <li>
                                    Jika NIK calon peserta <strong>tidak ditemukan</strong>, calon peserta harus
                                    mengurus NIK / Data Kependudukan ke Disdukcapil terlebih dahulu.
                                </li>
                            </ol>
                        </div>

                        <div class="tp-note">
                            <p class="tp-note-title">
                                <span class="material-symbols-outlined text-[20px]">checklist</span>
                                Catatan #2
                            </p>
                            <ol class="list-decimal list-inside space-y-1 font-body-sm text-body-sm">
                                <li>KTP menjadi parameter Nomor Induk Kependudukan berdomisili di Kota Bogor.</li>
                                <li>Nomor HP diisi dengan nomor yang saat mendaftar masih aktif dan dapat dihubungi.
                                </li>
                                <li>E-mail diisi dengan email pribadi yang saat ini digunakan untuk menerima informasi
                                    aktivasi akun.</li>
                            </ol>
                        </div>
                    </div>

                    {{-- C. Melengkapi data & aktivasi --}}
                    <div id="lengkapi-data" class="space-y-space-md">
                        <div class="flex items-center gap-space-xs">
                            <span class="tp-step-num"
                                style="width: 2.25rem; height: 2.25rem; font-size: 13px">C</span>
                            <h3 class="font-headline-md text-headline-md text-primary font-bold">
                                Melengkapi Data &amp; Aktivasi Akun
                            </h3>
                        </div>

                        <div
                            class="bg-surface-container-lowest rounded-xl border border-outline-variant/20 p-space-lg">
                            <div class="tp-steps">
                                <div class="tp-step">
                                    <span class="tp-step-num">1</span>
                                    <div class="tp-step-body">
                                        <p>Lengkapi data calon peserta.</p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">2</span>
                                    <div class="tp-step-body">
                                        <p>Isi password akun.</p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">3</span>
                                    <div class="tp-step-body">
                                        <p>Ulangi password pertama untuk konfirmasi.</p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">4</span>
                                    <div class="tp-step-body">
                                        <p>Isi nomor telepon aktif calon peserta.</p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">5</span>
                                    <div class="tp-step-body">
                                        <p>Isi e-mail aktif calon peserta untuk keperluan aktivasi akun.</p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">6</span>
                                    <div class="tp-step-body">
                                        <p>Periksa kembali data Anda sebagai calon peserta BPJS PBI APBD Kota Bogor.
                                        </p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">7</span>
                                    <div class="tp-step-body">
                                        <p>
                                            Jika sudah yakin dengan data yang diisi, klik / tekan tombol
                                            <span class="tp-chip tp-chip-blue">Submit</span>.
                                        </p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">8</span>
                                    <div class="tp-step-body">
                                        <p>Periksa dan buka e-mail masuk dari Pelayanan Sosial.</p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">9</span>
                                    <div class="tp-step-body">
                                        <p>Klik / tekan link aktivasi akun yang dikirim oleh sistem.</p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">10</span>
                                    <div class="tp-step-body">
                                        <p>Jika berhasil, aktivasi akun peserta selesai dibuat.</p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">11</span>
                                    <div class="tp-step-body">
                                        <p>
                                            Anda akan diarahkan ke halaman
                                            <span class="tp-chip tp-chip-blue">Login</span>
                                            untuk mulai menggunakan akun.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Video Tutorial --}}
                    <div id="video" class="space-y-space-md">
                        <h3 class="font-headline-md text-headline-md text-primary font-bold">
                            Tutorial Video Pendaftaran Akun Peserta PBI BPJS APBD Kota Bogor
                        </h3>
                        <div class="rounded-xl overflow-hidden border border-outline-variant/20 shadow-sm"
                            style="aspect-ratio: 16/9">
                            <iframe title="Tutorial Video Pendaftaran Akun Peserta PBI BPJS APBD Kota Bogor"
                                src="https://www.youtube.com/embed/k284d9-GLlA" width="100%" height="100%"
                                style="border: 0; display: block" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture;"
                                allowfullscreen loading="lazy"></iframe>
                        </div>
                    </div>

                    {{-- Lokasi Kantor --}}
                    <div id="lokasi" class="space-y-space-md">
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
                            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-space-sm bg-surface-container-lowest rounded-xl border border-outline-variant/20 p-space-md">
                            <div class="flex items-start gap-space-xs">
                                <span class="material-symbols-outlined text-primary text-[22px]">location_on</span>
                                <div>
                                    <p class="font-label-md text-label-md text-on-surface font-bold">Dinas Sosial Kota
                                        Bogor</p>
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
                        class="bg-primary rounded-xl p-space-xl flex flex-col md:flex-row items-center justify-between gap-space-md text-on-primary">
                        <div class="space-y-1">
                            <h4 class="font-headline-sm text-headline-sm font-bold">Sudah siap mendaftar?</h4>
                            <p class="font-body-sm text-body-sm text-on-primary/80">
                                Siapkan dokumen Anda dan mulai pendaftaran akun sekarang.
                            </p>
                        </div>
                        <a href="{{ route('login') }}"
                            class="shrink-0 inline-flex items-center gap-space-xs px-space-lg py-3 rounded-lg bg-surface-container-lowest text-primary font-label-md text-label-md hover:bg-white transition-colors">
                            <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
                            Daftar Akun
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

{{-- Script khusus halaman; dirender layout SETELAH js/script.js lewat stack('scripts') --}}
@push('scripts')
    <script>
        /* Scrollspy: sorot item Daftar Isi sesuai section yang sedang dibaca */
        (function () {
            var tocLinks = Array.prototype.slice.call(document.querySelectorAll(".tp-toc-link[data-toc]"));
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
    </script>
@endpush
