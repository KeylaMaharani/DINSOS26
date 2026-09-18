<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'SOLID v4 Dinas Sosial Kota Bogor')</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/logo.png') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />

    <link href="{{ asset('css/style.css') }}" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "outline-variant": "#c2c7cf",
                        surface: "#f7f9ff",
                        "primary-fixed-dim": "#a0caf9",
                        "on-tertiary-fixed-variant": "#005049",
                        "surface-variant": "#d9e3f1",
                        tertiary: "#00403a",
                        "on-primary": "#ffffff",
                        "tertiary-fixed-dim": "#6bd8cb",
                        "inverse-primary": "#a0caf9",
                        "on-primary-container": "#9bc5f3",
                        "on-error-container": "#93000a",
                        "surface-container-highest": "#d9e3f1",
                        "surface-container-high": "#dfe9f7",
                        "on-tertiary": "#ffffff",
                        "surface-container": "#e4effd",
                        "on-secondary": "#ffffff",
                        "primary-container": "#24527a",
                        "on-error": "#ffffff",
                        error: "#ba1a1a",
                        "surface-dim": "#d1dbe8",
                        "surface-container-low": "#edf4ff",
                        "on-tertiary-container": "#65d2c5",
                        "surface-container-lowest": "#ffffff",
                        "secondary-fixed": "#cfe5ff",
                        "secondary-container": "#82c1fd",
                        primary: "#003b62",
                        "inverse-surface": "#27313c",
                        "on-surface": "#121d26",
                        outline: "#72777f",
                        "on-surface-variant": "#42474e",
                        secondary: "#136299",
                        "on-primary-fixed": "#001d34",
                        "secondary-fixed-dim": "#98cbff",
                        "on-secondary-container": "#004e7e",
                        "on-secondary-fixed": "#001d33",
                        "surface-bright": "#f7f9ff",
                        "on-background": "#121d26",
                        "on-secondary-fixed-variant": "#004a77",
                        "tertiary-fixed": "#89f5e7",
                        "error-container": "#ffdad6",
                        "on-primary-fixed-variant": "#194a71",
                        "surface-tint": "#36618a",
                        "primary-fixed": "#cfe5ff",
                        "on-tertiary-fixed": "#00201d",
                        background: "#f7f9ff",
                        "inverse-on-surface": "#e8f2ff",
                        "tertiary-container": "#005951",
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px",
                    },
                    spacing: {
                        "gutter-mobile": "1rem",
                        "space-2xs": "0.25rem",
                        "space-3xl": "4rem",
                        "space-lg": "1.5rem",
                        "space-sm": "0.75rem",
                        "space-xs": "0.5rem",
                        "space-2xl": "3rem",
                        "margin-mobile": "1rem",
                        "margin-tablet": "1.5rem",
                        "space-xl": "2rem",
                        "space-md": "1rem",
                        "gutter-desktop": "1.5rem",
                        "margin-desktop": "2rem",
                    },
                    fontFamily: {
                        "body-lg": ["Plus Jakarta Sans"],
                        "body-sm": ["Plus Jakarta Sans"],
                        "display-lg-mobile": ["Plus Jakarta Sans"],
                        "headline-lg-mobile": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"],
                        "headline-sm": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"],
                        "label-lg": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                    },
                    fontSize: {
                        "body-lg": ["18px", {
                            lineHeight: "28px",
                            fontWeight: "400"
                        }],
                        "body-sm": ["14px", {
                            lineHeight: "20px",
                            fontWeight: "400"
                        }],
                        "display-lg-mobile": ["32px", {
                            lineHeight: "40px",
                            letterSpacing: "-0.01em",
                            fontWeight: "700"
                        }],
                        "headline-lg-mobile": ["26px", {
                            lineHeight: "34px",
                            letterSpacing: "-0.01em",
                            fontWeight: "700"
                        }],
                        "label-sm": ["12px", {
                            lineHeight: "16px",
                            letterSpacing: "0.02em",
                            fontWeight: "500"
                        }],
                        "headline-sm": ["20px", {
                            lineHeight: "28px",
                            fontWeight: "600"
                        }],
                        "label-md": ["14px", {
                            lineHeight: "20px",
                            letterSpacing: "0.01em",
                            fontWeight: "600"
                        }],
                        "label-lg": ["16px", {
                            lineHeight: "22px",
                            letterSpacing: "0.01em",
                            fontWeight: "600"
                        }],
                        "headline-md": ["24px", {
                            lineHeight: "32px",
                            letterSpacing: "-0.01em",
                            fontWeight: "600"
                        }],
                        "body-md": ["16px", {
                            lineHeight: "24px",
                            fontWeight: "400"
                        }],
                        "display-lg": ["44px", {
                            lineHeight: "54px",
                            letterSpacing: "-0.02em",
                            fontWeight: "700"
                        }],
                        "headline-lg": ["32px", {
                            lineHeight: "40px",
                            letterSpacing: "-0.015em",
                            fontWeight: "700"
                        }],
                    },
                },
            },
        };
    </script>

    {{-- Tempat tiap halaman menambahkan <style>/meta/library tambahan (Leaflet, Highcharts, dll) --}}
    @stack('head')
</head>

<body class="@yield('body_class', 'bg-surface font-body-md text-on-surface antialiased')">

    {{--
        ============================================================
        NAVBAR — SATU-SATUNYA TEMPAT navbar didefinisikan.
        File halaman (home, cek-desil, tutorial-pendaftaran, dst) TIDAK
        BOLEH lagi punya <header> atau <div class="mobile-nav-panel">
        sendiri. Cukup @extends('layouts.app'), navbar ini otomatis
        muncul di halaman manapun.

        Status "aktif" dihitung otomatis dari nama route, tidak perlu
        di-hardcode manual per halaman.
        ============================================================
    --}}
    @php
        $isHome = request()->routeIs('home');
        $isPelayanan = request()->routeIs('login', 'tutorial-pendaftaran');
        $isInformasi = request()->routeIs('cek-densil');
    @endphp

    <header id="site-header"
        class="fixed top-0 w-full z-50 bg-surface/95 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] {{ $isPelayanan || $isInformasi ? 'header-scrolled' : '' }}">
        <div class="w-full bg-surface-container-low border-b border-outline-variant/20"></div>

        <div
            class="h-24 w-full max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop flex items-center justify-between gap-space-md">

            <a href="{{ route('home') }}"
                class="flex items-center gap-space-sm min-w-0 shrink mr-space-lg lg:mr-space-xl">
                <img alt="Logo Dinas Sosial Kota Bogor" class="h-16 w-auto object-contain shrink-0"
                    src="{{ asset('assets/img/logo/logo.png') }}" />
                <div class="flex flex-col min-w-0">
                    <span
                        class="font-label-lg text-label-lg text-primary tracking-tight leading-tight uppercase whitespace-nowrap">
                        SOLID V.3
                    </span>
                    <!-- Tambahkan whitespace-nowrap di sini, dan gunakan <br/> -->
                    <span
                        class="font-label-sm text-label-sm text-secondary leading-snug hidden sm:block whitespace-nowrap">
                        Sosial Integrasi Data <br />
                        Pelayanan Sosial Kota Bogor
                    </span>
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-1 xl:gap-2 shrink-0">

                <a href="{{ route('home') }}"
                    class="nav-link px-2 py-1.5 text-[13px] flex items-center whitespace-nowrap transition-colors {{ $isHome ? 'text-primary font-bold active' : 'text-on-surface-variant font-medium hover:text-primary' }}">
                    Home
                </a>

                {{-- Pelayanan (dropdown) --}}
                <div class="relative group">
                    <button type="button"
                        class="nav-link px-2 py-1.5 text-[13px] flex items-center gap-1 whitespace-nowrap transition-colors {{ $isPelayanan ? 'text-primary font-bold active' : 'text-on-surface-variant font-medium hover:text-primary' }}">
                        <span>Pelayanan</span>
                        <span
                            class="material-symbols-outlined text-[16px] transition-transform group-hover:rotate-180">expand_more</span>
                    </button>
                    <div
                        class="absolute left-0 top-full pt-2 w-64 opacity-0 invisible translate-y-1 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-150 z-50">
                        <div
                            class="bg-surface-container-lowest rounded-xl shadow-md border border-outline-variant/30 py-2 overflow-hidden">
                            <a href="{{ route('login') }}"
                                class="block px-4 py-2 text-[13px] hover:bg-surface-container-low hover:text-secondary transition-colors {{ request()->routeIs('login') ? 'bg-surface-container-low text-secondary font-bold' : 'text-on-surface font-medium' }}">Pendaftaran
                                BPJS PBI APBD</a>
                            <a href="{{ route('tutorial-pendaftaran') }}"
                                class="block px-4 py-2 text-[13px] hover:bg-surface-container-low hover:text-secondary transition-colors {{ request()->routeIs('tutorial-pendaftaran') ? 'bg-surface-container-low text-secondary font-bold' : 'text-on-surface font-medium' }}">Tutorial
                                Pendaftaran BPJS PBI</a>
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">Informasi
                                BPJS Kesehatan</a>
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">Permohonan
                                Kunjungan Dinas</a>
                        </div>
                    </div>
                </div>

                {{-- Informasi (dropdown) --}}
                <div class="relative group">
                    <button type="button"
                        class="nav-link px-2 py-1.5 text-[13px] flex items-center gap-1 whitespace-nowrap transition-colors {{ $isInformasi ? 'text-primary font-bold active' : 'text-on-surface-variant font-medium hover:text-primary' }}">
                        <span>Informasi</span>
                        <span
                            class="material-symbols-outlined text-[16px] transition-transform group-hover:rotate-180">expand_more</span>
                    </button>
                    <div
                        class="absolute left-0 top-full pt-2 w-56 opacity-0 invisible translate-y-1 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-150 z-50">
                        <div
                            class="bg-surface-container-lowest rounded-xl shadow-md border border-outline-variant/30 py-2 overflow-visible">
                            <a href="{{ route('cek-densil') }}"
                                class="block px-4 py-2 text-[13px] hover:bg-surface-container-low hover:text-secondary transition-colors {{ request()->routeIs('cek-densil') ? 'bg-surface-container-low text-secondary font-bold' : 'text-on-surface font-medium' }}">Cek
                                Desil</a>
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">DTSEN</a>
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">PPKS
                                Dan PSKS</a>
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">LKS</a>
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">Penonaktifan
                                Kartu BPJS</a>
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">E-Warong</a>
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">Data
                                Suplier</a>
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">SK
                                PBI APBD</a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('home') }}#berita"
                    class="nav-link px-2 py-1.5 text-[13px] text-on-surface-variant font-medium hover:text-primary transition-colors flex items-center whitespace-nowrap">Berita</a>
                <a href="#"
                    class="nav-link px-2 py-1.5 text-[13px] text-on-surface-variant font-medium hover:text-primary transition-colors flex items-center whitespace-nowrap">Gallery</a>
                <a href="#"
                    class="nav-link px-2 py-1.5 text-[13px] text-on-surface-variant font-medium hover:text-primary transition-colors flex items-center whitespace-nowrap">Hubungi
                    Kami</a>
                <a href="#"
                    class="nav-link px-2 py-1.5 text-[13px] text-on-surface-variant font-medium hover:text-primary transition-colors flex items-center whitespace-nowrap">Bahan
                    Paparan</a>
                <a href="#"
                    class="nav-link px-2 py-1.5 text-[13px] text-on-surface-variant font-medium hover:text-primary transition-colors flex items-center whitespace-nowrap">Regulasi</a>

                {{-- Cek Bansos (dropdown) --}}
                <div class="relative group">
                    <button type="button"
                        class="nav-link px-2 py-1.5 text-[13px] text-on-surface-variant font-medium hover:text-primary transition-colors flex items-center gap-1 whitespace-nowrap">
                        <span>Cek Bansos</span>
                        <span
                            class="material-symbols-outlined text-[16px] transition-transform group-hover:rotate-180">expand_more</span>
                    </button>
                    <div
                        class="absolute right-0 top-full pt-2 w-48 opacity-0 invisible translate-y-1 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-150 z-50">
                        <div
                            class="bg-surface-container-lowest rounded-xl shadow-md border border-outline-variant/30 py-2 overflow-hidden">
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">BLT
                                BBM</a>
                            <a href="#"
                                class="block px-4 py-2 text-[13px] text-on-surface font-medium hover:bg-surface-container-low hover:text-secondary transition-colors">PKH
                                Dan BPNT</a>
                        </div>
                    </div>
                </div>

                {{-- Pemilih bahasa (desktop) --}}
                <div class="relative group pl-3 ml-2 border-l border-outline-variant/30">
                    <button id="lang-current-btn"
                        class="px-2 py-1.5 text-[12px] text-primary font-bold inline-flex items-center gap-1.5"
                        type="button">
                        <img id="lang-current-flag" src="{{ asset('assets/img/bendera/id.png') }}" alt="Indonesia"
                            class="w-4 h-4 rounded-full object-cover" />
                        <span id="lang-current-label">ID</span>
                        <span class="material-symbols-outlined text-[16px]">expand_more</span>
                    </button>
                    <div
                        class="absolute right-0 top-full pt-2 w-32 opacity-0 invisible translate-y-1 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-150 z-50">
                        <div
                            class="bg-surface-container-lowest rounded-xl shadow-md border border-outline-variant/30 py-1 overflow-hidden">
                            <button
                                class="lang-option w-full text-left px-3 py-2 text-[12px] font-medium text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors inline-flex items-center gap-2"
                                data-lang="id" data-flag="assets/img/bendera/id.png" type="button">
                                <img src="{{ asset('assets/img/bendera/id.png') }}" alt="Indonesia"
                                    class="w-4 h-4 rounded-full object-cover" />
                                <span>Indonesia</span>
                            </button>
                            <button
                                class="lang-option w-full text-left px-3 py-2 text-[12px] font-medium text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors inline-flex items-center gap-2"
                                data-lang="en" data-flag="assets/img/bendera/en.png" type="button">
                                <img src="{{ asset('assets/img/bendera/en.png') }}" alt="English"
                                    class="w-4 h-4 rounded-full object-cover" />
                                <span>English</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tombol Login --}}
                <a href="{{ route('login') }}"
                    class="nav-login-btn ml-2 shrink-0 inline-flex items-center justify-center px-4 py-1.5 rounded-md bg-primary hover:bg-secondary text-on-primary text-[13px] font-semibold transition-colors whitespace-nowrap">
                    <span>Login</span>
                </a>
            </nav>

            <button class="mobile-menu-toggle" id="mobile-menu-toggle" type="button"
                aria-label="Buka Menu Navigasi" aria-controls="mobile-nav-panel" aria-expanded="false">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </header>

    {{-- Panel Navigasi Mobile --}}
    <div class="mobile-nav-panel" id="mobile-nav-panel">
        <div class="mobile-nav-sheet">
            <div class="mobile-nav-header">
                <span class="mobile-nav-header-title">Menu</span>
                <button class="mobile-nav-close" id="mobile-nav-close" type="button" aria-label="Tutup Menu">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="mobile-nav-search">
                <span class="material-symbols-outlined">search</span>
                <input id="mobile-nav-search-input" type="text" placeholder="Cari layanan, berita..."
                    aria-label="Cari layanan, berita" />

                {{-- Pemilih bahasa (mobile) --}}
                <div class="relative pl-space-2xs" id="lang-mobile-wrap">
                    <button id="lang-mobile-btn" type="button"
                        class="flex items-center gap-1.5 px-space-xs py-1.5 rounded-lg bg-surface-container text-on-surface font-label-sm text-label-sm border border-outline-variant/30">
                        <img id="lang-mobile-flag" src="{{ asset('assets/img/bendera/id.png') }}" alt="Indonesia"
                            class="w-4 h-4 rounded-full object-cover" />
                        <span id="lang-mobile-label">ID</span>
                        <span class="material-symbols-outlined text-[16px]">expand_more</span>
                    </button>
                    <div id="lang-mobile-menu"
                        class="hidden absolute right-0 top-full mt-1 w-36 bg-surface-container-lowest rounded-xl shadow-md border border-outline-variant/30 py-space-xs overflow-hidden z-50">
                        <button
                            class="lang-option-mobile w-full text-left px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low inline-flex items-center gap-space-xs"
                            data-lang="id" data-flag="assets/img/bendera/id.png" type="button">
                            <img src="{{ asset('assets/img/bendera/id.png') }}" alt="Indonesia"
                                class="w-4 h-4 rounded-full object-cover" />
                            <span>Indonesia</span>
                        </button>
                        <button
                            class="lang-option-mobile w-full text-left px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low inline-flex items-center gap-space-xs"
                            data-lang="en" data-flag="assets/img/bendera/en.png" type="button">
                            <img src="{{ asset('assets/img/bendera/en.png') }}" alt="English"
                                class="w-4 h-4 rounded-full object-cover" />
                            <span>English</span>
                        </button>
                    </div>
                </div>
            </div>

            <a href="{{ route('home') }}" class="mobile-nav-link {{ $isHome ? 'active' : '' }}">Home</a>

            <div class="mobile-nav-group {{ $isPelayanan ? 'open' : '' }}">
                <button class="mobile-nav-group-toggle" type="button"
                    aria-expanded="{{ $isPelayanan ? 'true' : 'false' }}">
                    <span>Pelayanan</span>
                    <span class="material-symbols-outlined">expand_more</span>
                </button>
                <div class="mobile-nav-submenu">
                    <a href="{{ route('login') }}"
                        class="{{ request()->routeIs('login') ? 'text-secondary font-bold' : '' }}">Pendaftaran BPJS
                        PBI APBD</a>
                    <a href="{{ route('tutorial-pendaftaran') }}"
                        class="{{ request()->routeIs('tutorial-pendaftaran') ? 'text-secondary font-bold' : '' }}">Tutorial
                        Pendaftaran BPJS PBI</a>
                    <a href="#">Informasi BPJS Kesehatan</a>
                    <a href="#">Permohonan Kunjungan Dinas</a>
                </div>
            </div>

            <div class="mobile-nav-group {{ $isInformasi ? 'open' : '' }}">
                <button class="mobile-nav-group-toggle" type="button"
                    aria-expanded="{{ $isInformasi ? 'true' : 'false' }}">
                    <span>Informasi</span>
                    <span class="material-symbols-outlined">expand_more</span>
                </button>
                <div class="mobile-nav-submenu">
                    <a href="{{ route('cek-densil') }}"
                        class="{{ request()->routeIs('cek-densil') ? 'text-secondary font-bold' : '' }}">Cek Desil</a>
                    <a href="#">DTSEN</a>
                    <a href="#">PPKS Dan PSKS</a>
                    <a href="#">LKS</a>
                    <a href="#">Penonaktifan Kartu BPJS</a>
                    <a href="#">E-Warong</a>
                    <a href="#">Data Suplier</a>
                    <a href="#">SK PBI APBD</a>
                </div>
            </div>

            <a class="mobile-nav-link" href="{{ route('home') }}#berita">Berita</a>
            <a class="mobile-nav-link" href="#">Gallery</a>
            <a class="mobile-nav-link" href="#">Hubungi Kami</a>
            <a class="mobile-nav-link" href="#">Bahan Paparan</a>
            <a class="mobile-nav-link" href="#">Regulasi</a>

            <div class="mobile-nav-group">
                <button class="mobile-nav-group-toggle" type="button" aria-expanded="false">
                    <span>Cek Bansos</span>
                    <span class="material-symbols-outlined">expand_more</span>
                </button>
                <div class="mobile-nav-submenu">
                    <a href="#">BLT BBM</a>
                    <a href="#">PKH Dan BPNT</a>
                </div>
            </div>

            <a href="{{ route('login') }}"
                class="mt-space-sm inline-flex items-center justify-center gap-space-2xs px-space-md py-3 rounded-lg bg-primary hover:bg-secondary text-on-primary font-label-md text-label-md transition-colors">
                <span>Login</span>
            </a>
        </div>
    </div>
    {{-- ============================================================ END NAVBAR ============================================================ --}}

    <main class="w-full bg-surface">
        @yield('content')
    </main>


    <footer class="w-full bg-primary pt-space-2xl pb-space-lg text-on-primary">
        <div class="w-full max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-space-xl mb-space-2xl">
                <div class="flex flex-col items-start gap-space-md">
                    <img alt="Aplikasi SOLID Kota Bogor" class="w-40 md:w-44 h-auto object-contain"
                        src="{{ asset('assets/img/apk-solid/solid-mobile.png') }}" />
                    <a href="#" rel="noreferrer" target="_blank">
                        <img alt="Get it on Google Play" class="h-10 w-auto object-contain"
                            src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" />
                    </a>
                </div>

                <div class="space-y-space-sm">
                    <h4 class="font-label-lg text-label-lg text-on-primary font-bold">Pelayanan</h4>
                    <ul class="space-y-space-xs font-body-sm text-body-sm text-on-primary/80">
                        <li><a class="hover:text-secondary-fixed-dim transition-colors"
                                href="{{ route('login') }}">Pendaftaran BPJS PBI APBD</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors"
                                href="{{ route('tutorial-pendaftaran') }}">Tutorial Pendaftaran BPJS PBI</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">Informasi BPJS
                                Kesehatan</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">Permohonan
                                Kunjungan Dinas</a></li>
                    </ul>
                </div>

                <div class="space-y-space-sm">
                    <h4 class="font-label-lg text-label-lg text-on-primary font-bold">Informasi</h4>
                    <ul class="space-y-space-xs font-body-sm text-body-sm text-on-primary/80">
                        <li><a class="hover:text-secondary-fixed-dim transition-colors"
                                href="{{ route('cek-densil') }}">Cek Desil</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">DTSEN</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">PPKS Dan
                                PSKS</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">LKS</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">Penonaktifan
                                Kartu BPJS</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">E-Warong</a>
                        </li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">Data
                                Suplier</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">SK PBI APBD</a>
                        </li>
                    </ul>
                </div>

                <div class="space-y-space-sm">
                    <h4 class="font-label-lg text-label-lg text-on-primary font-bold">Cek Bansos</h4>
                    <ul class="space-y-space-xs font-body-sm text-body-sm text-on-primary/80">
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">BLT BBM</a>
                        </li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">PKH Dan
                                BPNT</a></li>
                    </ul>
                </div>

                <div class="space-y-space-sm">
                    <h4 class="font-label-lg text-label-lg text-on-primary font-bold">Tentang Kami</h4>
                    <ul class="space-y-space-xs font-body-sm text-body-sm text-on-primary/80">
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">Berita</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">Gallery</a>
                        </li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">Hubungi
                                Kami</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">Bahan
                                Paparan</a></li>
                        <li><a class="hover:text-secondary-fixed-dim transition-colors" href="#">Regulasi</a>
                        </li>
                    </ul>
                </div>

                <div class="space-y-space-sm footer-address-block">
                    <h4 class="font-label-lg text-label-lg text-on-primary font-bold">Alamat Kantor</h4>
                    <p class="font-body-sm text-body-sm text-on-primary/80 leading-relaxed">
                        Jl. Merdeka No.142, RT.01/RW.01, Ciwaringin, Kecamatan Bogor Tengah, Kota Bogor, Jawa Barat
                        16124
                    </p>
                    <div class="pt-space-sm space-y-space-xs">
                        <span
                            class="flex items-center gap-space-2xs font-body-sm text-body-sm text-on-primary/80"><span
                                class="material-symbols-outlined text-[16px]">call</span><span>(0251)
                                8332315</span></span>
                        <span
                            class="flex items-center gap-space-2xs font-body-sm text-body-sm text-on-primary/80"><span
                                class="material-symbols-outlined text-[16px]">mail</span><span>dinsoskotabogor@gmail.com</span></span>
                    </div>
                </div>
            </div>

            <div
                class="pt-space-lg border-t border-on-primary/15 flex flex-col md:flex-row items-center justify-between gap-space-md text-on-primary/70 font-label-sm text-label-sm">
                <p>© 2026 Pemerintah Kota Bogor. All right reserved</p>
                <div class="flex items-center gap-space-md">
                    <a class="hover:text-on-primary transition-colors">Privacy &amp; Policy.</a>
                    <a class="hover:text-on-primary transition-colors">Source Credit icon &amp; background.</a>
                </div>
            </div>
        </div>
    </footer>

    <div class="social-fab" id="social-fab">
        <button class="social-fab-toggle" id="social-fab-toggle" type="button" aria-label="Tampilkan Media Sosial"
            title="Media Sosial">
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
        </button>
        <div class="social-fab-icons">
            <a class="social-icon fb" href="https://web.facebook.com/p/Dinas-Sosial-100069127423257" target="_blank"
                rel="noreferrer" title="Facebook">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.91h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94z" />
                </svg>
            </a>
            <a class="social-icon ig" href="https://www.instagram.com/dinsoskotabogor/" target="_blank"
                rel="noreferrer" title="Instagram">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12 2c2.72 0 3.06.01 4.12.06 1.06.05 1.79.22 2.43.47.66.26 1.21.6 1.76 1.15.55.55.89 1.1 1.15 1.76.25.64.42 1.37.47 2.43.05 1.06.06 1.4.06 4.12s-.01 3.06-.06 4.12c-.05 1.06-.22 1.79-.47 2.43a4.9 4.9 0 01-1.15 1.76 4.9 4.9 0 01-1.76 1.15c-.64.25-1.37.42-2.43.47-1.06.05-1.4.06-4.12.06s-3.06-.01-4.12-.06c-1.06-.05-1.79-.22-2.43-.47a4.9 4.9 0 01-1.76-1.15 4.9 4.9 0 01-1.15-1.76c-.25-.64-.42-1.37-.47-2.43C2.01 15.06 2 14.72 2 12s.01-3.06.06-4.12c.05-1.06.18-1.34.31-1.66.16-.42.35-.72.67-1.03A4.9 4.9 0 015.44 2.53c.64-.25 1.37-.42 2.43-.47C8.94 2.01 9.28 2 12 2zm0 1.8c-2.67 0-2.99.01-4.04.06-.87.04-1.34.18-1.66.31-.42.16-.72.35-1.03.67-.32.31-.51.61-.67 1.03-.13.32-.27.79-.31 1.66C4.24 8.98 4.23 9.3 4.23 12s.01 3.02.06 4.07c.04.87.18 1.34.31 1.66.16.42.35.72.67 1.03.31.32.61.51 1.03.67.32.13.79.27 1.66.31 1.05.05 1.37.06 4.04.06s2.99-.01 4.04-.06c.87-.04 1.34-.18 1.66-.31.42-.16.72-.35 1.03-.67.32-.31.51-.61.67-1.03.13-.32.27-.79.31-1.66.05-1.05.06-1.37.06-4.07s-.01-3.02-.06-4.07c-.04-.87-.18-1.34-.31-1.66a2.77 2.77 0 00-.67-1.03 2.77 2.77 0 00-1.03-.67c-.32-.13-.79-.27-1.66-.31C14.99 3.81 14.67 3.8 12 3.8zm0 3.06a5.14 5.14 0 110 10.28 5.14 5.14 0 010-10.28zm0 1.8a3.34 3.34 0 100 6.68 3.34 3.34 0 000-6.68zm5.34-1.98a1.2 1.2 0 11-2.4 0 1.2 1.2 0 012.4 0z" />
                </svg>
            </a>
            <a class="social-icon tiktok" href="https://www.tiktok.com/@dinsoskotabogor" target="_blank"
                rel="noreferrer" title="TikTok">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M16.6 5.82c-.9-.99-1.4-2.29-1.4-3.65h-3.14v13.7c0 1.63-1.33 2.96-2.96 2.96a2.96 2.96 0 01-2.96-2.96 2.96 2.96 0 012.96-2.96c.27 0 .53.03.78.1v-3.2a6.1 6.1 0 00-.78-.05A6.14 6.14 0 003 15.87a6.14 6.14 0 006.1 6.13 6.14 6.14 0 006.1-6.13V9.02a8.65 8.65 0 004.8 1.46V7.34a5.3 5.3 0 01-3.4-1.52z" />
                </svg>
            </a>
            <a class="social-icon web" href="#" target="_blank" rel="noreferrer" title="Website Resmi">
                <span class="material-symbols-outlined text-[22px]">language</span>
            </a>
        </div>
    </div>

    <a aria-label="Chat WhatsApp Dinsos" class="wa-fab" href="https://wa.me/6285333395667" rel="noreferrer"
        target="_blank" title="Chat WhatsApp Dinsos">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.876 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
            <path
                d="M12.004 2.003c-5.514 0-9.997 4.483-9.997 9.997 0 1.762.462 3.485 1.34 5.003l-1.423 5.194 5.316-1.394a9.96 9.96 0 004.764 1.213h.004c5.514 0 9.997-4.483 9.997-9.997 0-2.67-1.04-5.18-2.929-7.07a9.935 9.935 0 00-7.072-2.946zm5.85 15.848a8.29 8.29 0 01-5.85 2.42h-.003a8.31 8.31 0 01-4.234-1.157l-.304-.18-3.157.828.843-3.078-.198-.317a8.264 8.264 0 01-1.267-4.42c0-4.582 3.729-8.311 8.314-8.311a8.257 8.257 0 015.876 2.437 8.26 8.26 0 012.434 5.878 8.29 8.29 0 01-2.454 5.9z" />
        </svg>
    </a>

    <div class="a11y-fab" id="a11y-fab">
        <div class="a11y-fab-panel bg-surface-container-lowest rounded-xl shadow-md p-space-md w-64 space-y-space-sm">
            <div class="flex items-center justify-between">
                <span class="font-label-md text-label-md text-primary font-bold">Aksesibilitas</span>
                <button
                    class="w-6 h-6 flex items-center justify-center rounded text-on-surface-variant hover:bg-surface-container"
                    id="a11y-fab-close" title="Tutup" type="button">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
            <div
                class="flex items-center justify-between gap-space-xs bg-surface-container px-space-xs py-1.5 rounded-lg">
                <span class="font-label-sm text-label-sm text-on-surface-variant">Ukuran Fon</span>
                <div class="flex items-center gap-space-2xs">
                    <button
                        class="w-7 h-7 flex items-center justify-center rounded bg-surface-container-lowest text-primary font-bold hover:bg-primary hover:text-on-primary transition-colors"
                        title="Perkecil" type="button">A-</button>
                    <button
                        class="w-7 h-7 flex items-center justify-center rounded bg-surface-container-lowest text-primary font-bold hover:bg-primary hover:text-on-primary transition-colors"
                        title="Perbesar" type="button">A+</button>
                </div>
            </div>
            <button
                class="w-full flex items-center gap-space-xs px-space-sm py-2 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm transition-colors"
                type="button">
                <span class="material-symbols-outlined text-[18px] text-secondary">contrast</span>
                <span>Mode Kontras Tinggi</span>
            </button>
            <button
                class="w-full flex items-center gap-space-xs px-space-sm py-2 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm transition-colors"
                type="button">
                <span class="material-symbols-outlined text-[18px] text-secondary">motion_photos_off</span>
                <span>Kurangi Animasi</span>
            </button>
            <div
                class="flex items-center gap-space-2xs text-on-surface-variant font-label-sm text-label-sm pt-space-2xs">
                <span class="material-symbols-outlined text-[16px] text-tertiary">record_voice_over</span>
                <span>Kompatibel NVDA/TalkBack</span>
            </div>
        </div>
        <button aria-label="Buka Menu Aksesibilitas"
            class="w-11 h-11 rounded-full bg-primary hover:bg-secondary text-on-primary flex items-center justify-center shadow-lg transition-colors"
            id="a11y-fab-toggle" type="button">
            <span class="material-symbols-outlined text-[20px]">accessibility_new</span>
        </button>
    </div>

    <button aria-label="Kembali ke Atas" class="back-to-top" id="back-to-top" title="Kembali ke Atas"
        type="button">
        <span class="material-symbols-outlined text-[22px]">arrow_upward</span>
    </button>

    <script src="{{ asset('js/script.js') }}"></script>
    @stack('scripts')
</body>

</html>
