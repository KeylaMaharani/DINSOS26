@extends('layouts.app')

@section('title', 'Kejadian Bencana - SOLID v4 Dinas Sosial Kota Bogor')

@section('head')
        <meta charset="utf-8" />
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="https://fonts.googleapis.com" rel="preconnect" />
        <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/logo.png') }}" />
        <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
        <link
          href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;display=swap"
          rel="stylesheet"
        />
        <link
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
          rel="stylesheet"
        />
        <link
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
          rel="stylesheet"
        />
        <!-- CSS terpisah (dipakai bersama seluruh halaman) -->
        <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="{{ asset('js/highcharts.bundle.js') }}"></script>
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
                  "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
                  "body-sm": ["14px", { lineHeight: "20px", fontWeight: "400" }],
                  "display-lg-mobile": [
                    "32px",
                    {
                      lineHeight: "40px",
                      letterSpacing: "-0.01em",
                      fontWeight: "700",
                    },
                  ],
                  "headline-lg-mobile": [
                    "26px",
                    {
                      lineHeight: "34px",
                      letterSpacing: "-0.01em",
                      fontWeight: "700",
                    },
                  ],
                  "label-sm": [
                    "12px",
                    {
                      lineHeight: "16px",
                      letterSpacing: "0.02em",
                      fontWeight: "500",
                    },
                  ],
                  "headline-sm": [
                    "20px",
                    { lineHeight: "28px", fontWeight: "600" },
                  ],
                  "label-md": [
                    "14px",
                    {
                      lineHeight: "20px",
                      letterSpacing: "0.01em",
                      fontWeight: "600",
                    },
                  ],
                  "label-lg": [
                    "16px",
                    {
                      lineHeight: "22px",
                      letterSpacing: "0.01em",
                      fontWeight: "600",
                    },
                  ],
                  "headline-md": [
                    "24px",
                    {
                      lineHeight: "32px",
                      letterSpacing: "-0.01em",
                      fontWeight: "600",
                    },
                  ],
                  "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
                  "display-lg": [
                    "44px",
                    {
                      lineHeight: "54px",
                      letterSpacing: "-0.02em",
                      fontWeight: "700",
                    },
                  ],
                  "headline-lg": [
                    "32px",
                    {
                      lineHeight: "40px",
                      letterSpacing: "-0.015em",
                      fontWeight: "700",
                    },
                  ],
                },
              },
            },
          };
        </script>
        <style>
          /* ============================================================
             Halaman Kejadian Bencana
             Token & pola visual mengikuti SOLID v4 (selaras dengan
             tutorial-pendaftaran.html), disesuaikan untuk halaman grafik
             data kebencanaan.
             ============================================================ */

          [id] {
            scroll-margin-top: 6.5rem;
          }

          /* --- Hero, disamakan persis dengan tp-hero (tutorial-pendaftaran) --- */
          .kb-hero {
            background:
              radial-gradient(
                1100px 480px at 12% -10%,
                rgba(19, 98, 153, 0.55),
                transparent 60%
              ),
              radial-gradient(
                900px 420px at 90% 120%,
                rgba(0, 64, 58, 0.35),
                transparent 55%
              ),
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

          .kb-chart-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 14px rgba(55, 65, 81, 0.1);
          }
         .kb-data-section {
      background: #f7f9ff;
    }
          .kb-chart-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 14px rgba(55, 65, 81, 0.1);
          }
               /* --- Kartu grafik --- */
        .kb-data-section {
      background: #f7f9ff;
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
            background: #ffffff
              url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2342474e'><path d='M5.25 7.5l4.75 5 4.75-5z'/></svg>")
              no-repeat right 0.7rem center;
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
            transition:
              transform 0.15s ease,
              box-shadow 0.15s ease,
              background 0.2s ease;
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

          @media (max-width: 1023px) {
            .kb-chart-card {
              width: 100%;
            }
            .kb-toc {
              display: none;
            }
          }
        </style>

@endsection

@section('body_class', 'bg-surface font-body-md text-on-surface antialiased')

@section('content')

    <header
      class="fixed top-0 w-full z-50 bg-surface/95 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)]"
    >
      <div
        class="w-full bg-surface-container-low border-b border-outline-variant/20"
      ></div>
      <div
        class="h-24 w-full max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop flex items-center justify-between gap-space-md"
      >
        <div
          class="flex items-center gap-space-sm min-w-0 shrink mr-space-lg lg:mr-space-xl"
        >
          <img
            alt="Logo Dinas Sosial Kota Bogor"
            class="h-16 w-auto object-contain shrink-0"
            src="{{ asset('assets/img/logo/logo.png') }}"
          />
          <div class="flex flex-col min-w-0">
            <span
              class="font-label-lg text-label-lg text-primary tracking-tight leading-tight uppercase whitespace-nowrap"
            >
              SOLID V.3
            </span>
            <span
              class="font-label-sm text-label-sm text-secondary leading-snug hidden sm:block"
            >
              <span class="whitespace-nowrap">Sosial Integrasi Data </span
              ><br />
              Pelayanan Sosial Kota Bogor
            </span>
          </div>
        </div>
        <nav
          class="hidden lg:flex items-center gap-0.5 2xl:gap-space-2xs shrink-0"
        >
          <a
            class="nav-link px-space-xs py-space-xs font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors flex items-center whitespace-nowrap"
            data-path="beranda"
            href="{{ route('home') }}"
            >Home</a
          >
          <!-- Pelayanan (dropdown) -->
          <div class="relative group">
            <button
              class="nav-link px-space-xs py-space-xs font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors flex items-center gap-space-2xs whitespace-nowrap"
              type="button"
            >
              <span>Pelayanan</span>
              <span
                class="material-symbols-outlined text-[18px] transition-transform group-hover:rotate-180"
                >expand_more</span
              >
            </button>
            <div
              class="absolute left-0 top-full pt-2 w-72 opacity-0 invisible translate-y-1 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-150 z-50"
            >
              <div
                class="bg-surface-container-lowest rounded-xl shadow-md border border-outline-variant/30 py-space-xs overflow-hidden"
              >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="{{ route('login') }}"
                  >Pendaftaran BPJS PBI APBD</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="{{ route('tutorial-pendaftaran') }}"
                  >Tutorial Pendaftaran BPJS PBI</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >Informasi BPJS Kesehatan</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >Permohonan Kunjungan Dinas</a
                >
              </div>
            </div>
          </div>
          <!-- Informasi (dropdown) -->
          <div class="relative group">
            <button
              class="nav-link px-space-xs py-space-xs font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors flex items-center gap-space-2xs whitespace-nowrap"
              type="button"
            >
              <span>Informasi</span>
              <span
                class="material-symbols-outlined text-[18px] transition-transform group-hover:rotate-180"
                >expand_more</span
              >
            </button>
            <div
              class="absolute left-0 top-full pt-2 w-64 opacity-0 invisible translate-y-1 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-150 z-50"
            >
              <div
                class="bg-surface-container-lowest rounded-xl shadow-md border border-outline-variant/30 py-space-xs overflow-visible"
              >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="{{ route('cek-densil') }}"
                  >Cek Desil</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >DTSEN</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >PPKS Dan PSKS</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >LKS</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >Penonaktifan Kartu BPJS</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >E-Warong</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >Data Suplier</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >SK PBI APBD</a
                >
              </div>
            </div>
          </div>
          <a
            class="nav-link px-space-xs py-space-xs font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors flex items-center whitespace-nowrap"
            data-path="berita"
            href="#berita"
            >Berita</a
          >
          <a
            class="nav-link px-space-xs py-space-xs font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors flex items-center whitespace-nowrap"
            data-path="gallery"
            href="#"
            >Gallery</a
          >
          <a
            class="nav-link px-space-xs py-space-xs font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors flex items-center whitespace-nowrap"
            data-path="hubungi-kami"
            href="#"
            >Hubungi Kami</a
          >
          <a
            class="nav-link px-space-xs py-space-xs font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors flex items-center whitespace-nowrap"
            data-path="bahan-paparan"
            href="#"
            >Bahan Paparan</a
          >
          <a
            class="nav-link px-space-xs py-space-xs font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors flex items-center whitespace-nowrap"
            data-path="regulasi"
            href="#"
            >Regulasi</a
          >
          <!-- Cek Bansos (dropdown, dipindahkan ke navbar utama) -->
          <div class="relative group">
            <button
              class="nav-link px-space-xs py-space-xs font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors flex items-center gap-space-2xs whitespace-nowrap"
              type="button"
            >
              <span>Cek Bansos</span>
              <span
                class="material-symbols-outlined text-[18px] transition-transform group-hover:rotate-180"
                >expand_more</span
              >
            </button>
            <div
              class="absolute left-0 top-full pt-2 w-56 opacity-0 invisible translate-y-1 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-150 z-50"
            >
              <div
                class="bg-surface-container-lowest rounded-xl shadow-md border border-outline-variant/30 py-space-xs overflow-hidden"
              >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >BLT BBM</a
                >
                <a
                  class="block px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors"
                  href="#"
                  >PKH Dan BPNT</a
                >
              </div>
            </div>
          </div>
          <div
            class="relative group pl-space-sm ml-space-xs border-l border-outline-variant/30"
          >
            <button
              id="lang-current-btn"
              class="px-space-sm py-1.5 font-label-sm text-label-sm text-primary font-bold inline-flex items-center gap-1.5"
              type="button"
            >
              <img
                id="lang-current-flag"
                src="{{ asset('assets/img/bendera/id.png') }}"
                alt="Indonesia"
                class="w-4 h-4 rounded-full object-cover"
              />
              <span id="lang-current-label">ID</span>
              <span class="material-symbols-outlined text-[16px]"
                >expand_more</span
              >
            </button>
            <div
              class="absolute right-0 top-full pt-2 w-36 opacity-0 invisible translate-y-1 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-150 z-50"
            >
              <div
                class="bg-surface-container-lowest rounded-xl shadow-md border border-outline-variant/30 py-space-xs overflow-hidden"
              >
                <button
                  class="lang-option w-full text-left px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors inline-flex items-center gap-space-xs"
                  data-lang="id"
                  data-flag="assets/img/bendera/id.png"
                  type="button"
                >
                  <img
                    src="{{ asset('assets/img/bendera/id.png') }}"
                    alt="Indonesia"
                    class="w-4 h-4 rounded-full object-cover"
                  />
                  <span>Indonesia</span>
                </button>
                <button
                  class="lang-option w-full text-left px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low hover:text-secondary transition-colors inline-flex items-center gap-space-xs"
                  data-lang="en"
                  data-flag="assets/img/bendera/en.png"
                  type="button"
                >
                  <img
                    src="{{ asset('assets/img/bendera/en.png') }}"
                    alt="English"
                    class="w-4 h-4 rounded-full object-cover"
                  />
                  <span>English</span>
                </button>
              </div>
            </div>
          </div>
          <a
  href="{{ route('login') }}"
  class="nav-login-btn ml-space-sm shrink-0 inline-flex items-center gap-space-2xs px-space-md py-2 rounded-lg bg-primary hover:bg-secondary text-on-primary font-label-md text-label-md transition-colors whitespace-nowrap"
>
  <span>Login</span>
</a>
        </a>

        <!-- Tombol Hamburger (tampil di bawah breakpoint xl) -->
        <button
          class="mobile-menu-toggle"
          id="mobile-menu-toggle"
          type="button"
          aria-label="Buka Menu Navigasi"
          aria-controls="mobile-nav-panel"
          aria-expanded="false"
        >
          <span class="material-symbols-outlined">menu</span>
        </button>
      </div>
    </header>

    <!-- ============================================================
         Panel Navigasi Mobile (hamburger menu)
         ============================================================ -->
    <div class="mobile-nav-panel" id="mobile-nav-panel">
      <div class="mobile-nav-sheet">
        <div class="mobile-nav-header">
          <span class="mobile-nav-header-title">Menu</span>
          <button
            class="mobile-nav-close"
            id="mobile-nav-close"
            type="button"
            aria-label="Tutup Menu"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <div class="mobile-nav-search">
          <span class="material-symbols-outlined">search</span>
          <input
            id="mobile-nav-search-input"
            type="text"
            placeholder="Cari layanan, berita..."
            aria-label="Cari layanan, berita"
          />
          <div class="relative pl-space-2xs" id="lang-mobile-wrap">
            <button
              id="lang-mobile-btn"
              type="button"
              class="flex items-center gap-1.5 px-space-xs py-1.5 rounded-lg bg-surface-container text-on-surface font-label-sm text-label-sm border border-outline-variant/30"
            >
              <img
                id="lang-mobile-flag"
                src="{{ asset('assets/img/bendera/id.png') }}"
                alt="Indonesia"
                class="w-4 h-4 rounded-full object-cover"
              />
              <span id="lang-mobile-label">ID</span>
              <span class="material-symbols-outlined text-[16px]"
                >expand_more</span
              >
            </button>
            <div
              id="lang-mobile-menu"
              class="hidden absolute right-0 top-full mt-1 w-36 bg-surface-container-lowest rounded-xl shadow-md border border-outline-variant/30 py-space-xs overflow-hidden z-50"
            >
              <button
                class="lang-option-mobile w-full text-left px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low inline-flex items-center gap-space-xs"
                data-lang="id"
                data-flag="assets/img/bendera/id.png"
                type="button"
              >
                <img
                  src="{{ asset('assets/img/bendera/id.png') }}"
                  alt="Indonesia"
                  class="w-4 h-4 rounded-full object-cover"
                />
                <span>Indonesia</span>
              </button>
              <button
                class="lang-option-mobile w-full text-left px-space-md py-space-sm font-label-md text-label-md text-on-surface hover:bg-surface-container-low inline-flex items-center gap-space-xs"
                data-lang="en"
                data-flag="assets/img/bendera/en.png"
                type="button"
              >
                <img
                  src="{{ asset('assets/img/bendera/en.png') }}"
                  alt="English"
                  class="w-4 h-4 rounded-full object-cover"
                />
                <span>English</span>
              </button>
            </div>
          </div>
        </div>

<a class="mobile-nav-link" href="{{ route('home') }}">Home</a>

        <!-- Pelayanan (accordion) -->
        <div class="mobile-nav-group">
          <button
            class="mobile-nav-group-toggle"
            type="button"
            aria-expanded="false"
          >
            <span>Pelayanan</span>
            <span class="material-symbols-outlined">expand_more</span>
          </button>
          <div class="mobile-nav-submenu">
            <a href="{{ route('login') }}">Pendaftaran BPJS PBI APBD</a>
            <a href="{{ route('tutorial-pendaftaran') }}"
              >Tutorial Pendaftaran BPJS PBI</a
            >
            <a href="#">Informasi BPJS Kesehatan</a>
            <a href="#">Permohonan Kunjungan Dinas</a>
          </div>
        </div>

        <!-- Informasi (accordion) -->
        <div class="mobile-nav-group">
          <button
            class="mobile-nav-group-toggle"
            type="button"
            aria-expanded="false"
          >
            <span>Informasi</span>
            <span class="material-symbols-outlined">expand_more</span>
          </button>
          <div class="mobile-nav-submenu">
            <a href="{{ route('cek-densil') }}">Cek Desil</a>
            <a href="#">DTSEN</a>
            <a href="#">PPKS Dan PSKS</a>
            <a href="#">LKS</a>
            <a href="#">Penonaktifan Kartu BPJS</a>
            <a href="#">E-Warong</a>
            <a href="#">Data Suplier</a>
            <a href="#">SK PBI APBD</a>
          </div>
        </div>

        <a class="mobile-nav-link" href="#">Berita</a>
        <a class="mobile-nav-link" href="#">Gallery</a>
        <a class="mobile-nav-link" href="#">Hubungi Kami</a>
        <a class="mobile-nav-link" href="#">Bahan Paparan</a>
        <a class="mobile-nav-link" href="#">Regulasi</a>

        <!-- Cek Bansos (accordion, dipindahkan ke navbar utama) -->
        <div class="mobile-nav-group">
          <button
            class="mobile-nav-group-toggle"
            type="button"
            aria-expanded="false"
          >
            <span>Cek Bansos</span>
            <span class="material-symbols-outlined">expand_more</span>
          </button>
          <div class="mobile-nav-submenu">
            <a href="#">BLT BBM</a>
            <a href="#">PKH Dan BPNT</a>
          </div>
        </div>
      </div>
    </div>
    <main class="w-full bg-surface">
      <!-- ============================================================
           HERO — Judul halaman & breadcrumb
           ============================================================ -->
      <section
        class="kb-hero w-full text-on-primary pt-32 pb-16 md:pt-40 md:pb-20"
      >
        <div
          class="relative z-10 max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-sm text-center"
        >
          <h1
            class="font-headline-lg-mobile text-headline-lg-mobile md:font-display-lg md:text-display-lg font-bold tracking-tight max-w-3xl mx-auto"
          >
            Kejadian Bencana
          </h1>
        </div>
      </section>

      <!-- Breadcrumb -->
      <div
        class="w-full bg-surface-container-lowest border-b border-outline-variant/20"
      >
        <div
          class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop py-space-sm flex items-center gap-space-2xs font-label-sm text-label-sm text-on-surface-variant"
        >
          <a href="{{ route('home') }}" class="hover:text-secondary transition-colors"
            >Home</a
          >
          <span class="material-symbols-outlined text-[16px]"
            >chevron_right</span
          >
          <span class="text-primary font-bold">Kejadian Bencana</span>
        </div>
      </div>

      <!-- ============================================================
           KONTEN UTAMA — TOC + Grafik & Peta
           ============================================================ -->
      <section class="kb-data-section w-full py-space-2xl md:py-space-3xl">
        <div
          class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop"
        >
          <!-- Isi halaman -->
          <div class="kb-page-card space-y-space-2xl">
            <div class="space-y-space-xs max-w-3xl mx-auto text-center">
              <h2
                class="font-headline-lg text-headline-lg text-primary font-bold leading-snug"
              >
                GRAFIK DATA KEJADIAN BENCANA DI WILAYAH KOTA BOGOR JAWA BARAT
              </h2>
            </div>

            <!-- Filter tahun -->
            <div class="flex items-center justify-center gap-space-sm">
              <span class="font-label-md text-label-md text-on-surface-variant"
                >Tahun</span
              >
              <div class="kb-select-wrap">
                <select id="filter-tahun" class="kb-select">
                  <option value="2026" selected>2026</option>
                  <option value="2025">2025</option>
                </select>
              </div>
            </div>

            <!-- Grafik per Bulan -->
            <div id="grafik-bulan" class="space-y-space-md">
              <div class="kb-chart-card text-center">
                <div class="kb-chart-head justify-center relative">
                  <span class="kb-chart-title" id="judul-chart-bulan"
                    >Grafik Kejadian Bencana Tahun 2026 di Bulan</span
                  >
                  <span class="kb-menu-dots absolute right-0"
                    ><span class="material-symbols-outlined text-[20px]"
                      >menu</span
                    ></span
                  >
                </div>
                <div id="chart-bulan" style="height: 380px"></div>
              </div>
            </div>

            <!-- Grafik per Kecamatan -->
            <div id="grafik-kecamatan" class="space-y-space-md">
              <div class="kb-chart-card text-center">
                <div class="kb-chart-head justify-center relative">
                  <span class="kb-chart-title"
                    >Grafik Kejadian Bencana Tahun di Kecamatan</span
                  >
                  <span class="kb-menu-dots absolute right-0"
                    ><span class="material-symbols-outlined text-[20px]"
                      >menu</span
                    ></span
                  >
                </div>
                <div id="chart-kecamatan" style="height: 380px"></div>
              </div>
            </div>

            <!-- Tombol aksi -->
            <div class="flex flex-col sm:flex-row justify-center gap-space-sm">
              <a
                href="https://pelayanansosial.kotabogor.go.id/index.php/menu/home"
                rel="noreferrer"
                target="_blank"
                class="kb-btn kb-btn-primary"
              >
                <span class="material-symbols-outlined text-[18px]"
                  >admin_panel_settings</span
                >
                Operator Login
              </a>
              <a
                href="{{ asset('assets/pdf/Modul_Pembelajaran_Dokumen.pdf') }}"
                target="_blank"
                rel="noreferrer"
                class="kb-btn kb-btn-accent"
              >
                <span class="material-symbols-outlined text-[18px]"
                  >download</span
                >
                Tutorial Menu Kebencanaan
              </a>
            </div>

            <!-- ============================================================
                 Lokasi Kantor
                 ============================================================ -->
            <div id="lokasi" class="space-y-space-md text-center">
              <h3
                class="font-headline-md text-headline-md text-primary font-bold"
              >
                Lokasi Kantor Dinas Sosial Kota Bogor
              </h3>
              <div
                class="rounded-xl overflow-hidden border border-outline-variant/20 shadow-sm"
              >
                <iframe
                  title="Peta Lokasi Dinas Sosial Kota Bogor"
                  src="https://www.google.com/maps?q=Dinas%20Sosial%20Kota%20Bogor%2C%20Jl.%20Merdeka%20No.142%2C%20Ciwaringin%2C%20Bogor%20Tengah%2C%20Kota%20Bogor&output=embed"
                  width="100%"
                  height="380"
                  style="border: 0; display: block"
                  loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
              </div>
              <div
                class="flex flex-col sm:flex-row sm:items-center justify-center gap-space-sm bg-surface-container-lowest rounded-xl border border-outline-variant/20 p-space-md"
              >
                <div class="flex items-start gap-space-xs text-left">
                  <span
                    class="material-symbols-outlined text-primary text-[22px]"
                    >location_on</span
                  >
                  <div>
                    <p
                      class="font-label-md text-label-md text-on-surface font-bold"
                    >
                      Dinas Sosial Kota Bogor
                    </p>
                    <p
                      class="font-body-sm text-body-sm text-on-surface-variant"
                    >
                      Jl. Merdeka No.142, RT.01/RW.01, Ciwaringin, Kecamatan
                      Bogor Tengah, Kota Bogor, Jawa Barat 16124
                    </p>
                  </div>
                </div>
                <a
                  href="https://www.google.com/maps?q=Dinas+Sosial+Kota+Bogor"
                  target="_blank"
                  rel="noreferrer"
                  class="shrink-0 inline-flex items-center justify-center gap-space-xs px-space-md py-2.5 rounded-lg bg-primary hover:bg-secondary text-on-primary font-label-sm text-label-sm transition-colors"
                >
                  <span class="material-symbols-outlined text-[18px]"
                    >directions</span
                  >
                  Buka di Google Maps
                </a>
              </div>
            </div>

            <!-- CTA penutup -->
            <div
              class="bg-primary rounded-xl p-space-xl flex flex-col md:flex-row items-center justify-center gap-space-md text-on-primary text-center md:text-left"
            >
              <div class="space-y-1">
                <h4 class="font-headline-sm text-headline-sm font-bold">
                  Ingin lihat data lebih detail?
                </h4>
                <p class="font-body-sm text-body-sm text-on-primary/80">
                  Masuk sebagai operator untuk mengakses data kebencanaan
                  lengkap.
                </p>
              </div>
              <a
                href="https://pelayanansosial.kotabogor.go.id/index.php/menu/home"
                rel="noreferrer"
                target="_blank"
                class="shrink-0 inline-flex items-center gap-space-xs px-space-lg py-3 rounded-lg bg-surface-container-lowest text-primary font-label-md text-label-md hover:bg-white transition-colors"
              >
                <span class="material-symbols-outlined text-[20px]"
                  >admin_panel_settings</span
                >
                Operator Login
              </a>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- ============================================================
         FOOTER (versi 6 kolom, selaras dengan halaman lain di SOLID v4)
         ============================================================ -->

    <div class="social-fab" id="social-fab">
      <button
        class="social-fab-toggle"
        id="social-fab-toggle"
        type="button"
        aria-label="Tampilkan Media Sosial"
        title="Media Sosial"
      >
        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
      </button>
      <div class="social-fab-icons">
        <a
          class="social-icon fb"
          href="https://web.facebook.com/p/Dinas-Sosial-100069127423257"
          target="_blank"
          rel="noreferrer"
          title="Facebook"
        >
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.91h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94z"
            />
          </svg>
        </a>
        <a
          class="social-icon ig"
          href="https://www.instagram.com/dinsoskotabogor/"
          target="_blank"
          rel="noreferrer"
          title="Instagram"
        >
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M12 2c2.72 0 3.06.01 4.12.06 1.06.05 1.79.22 2.43.47.66.26 1.21.6 1.76 1.15.55.55.89 1.1 1.15 1.76.25.64.42 1.37.47 2.43.05 1.06.06 1.4.06 4.12s-.01 3.06-.06 4.12c-.05 1.06-.22 1.79-.47 2.43a4.9 4.9 0 01-1.15 1.76 4.9 4.9 0 01-1.76 1.15c-.64.25-1.37.42-2.43.47-1.06.05-1.4.06-4.12.06s-3.06-.01-4.12-.06c-1.06-.05-1.79-.22-2.43-.47a4.9 4.9 0 01-1.76-1.15 4.9 4.9 0 01-1.15-1.76c-.25-.64-.42-1.37-.47-2.43C2.01 15.06 2 14.72 2 12s.01-3.06.06-4.12c.05-1.06.18-1.34.31-1.66.16-.42.35-.72.67-1.03A4.9 4.9 0 015.44 2.53c.64-.25 1.37-.42 2.43-.47C8.94 2.01 9.28 2 12 2zm0 1.8c-2.67 0-2.99.01-4.04.06-.87.04-1.34.18-1.66.31-.42.16-.72.35-1.03.67-.32.31-.51.61-.67 1.03-.13.32-.27.79-.31 1.66C4.24 8.98 4.23 9.3 4.23 12s.01 3.02.06 4.07c.04.87.18 1.34.31 1.66.16.42.35.72.67 1.03.31.32.61.51 1.03.67.32.13.79.27 1.66.31 1.05.05 1.37.06 4.04.06s2.99-.01 4.04-.06c.87-.04 1.34-.18 1.66-.31.42-.16.72-.35 1.03-.67.32-.31.51-.61.67-1.03.13-.32.27-.79.31-1.66.05-1.05.06-1.37.06-4.07s-.01-3.02-.06-4.07c-.04-.87-.18-1.34-.31-1.66a2.77 2.77 0 00-.67-1.03 2.77 2.77 0 00-1.03-.67c-.32-.13-.79-.27-1.66-.31C14.99 3.81 14.67 3.8 12 3.8zm0 3.06a5.14 5.14 0 110 10.28 5.14 5.14 0 010-10.28zm0 1.8a3.34 3.34 0 100 6.68 3.34 3.34 0 000-6.68zm5.34-1.98a1.2 1.2 0 11-2.4 0 1.2 1.2 0 012.4 0z"
            />
          </svg>
        </a>
        <a
          class="social-icon tiktok"
          href="https://www.tiktok.com/@dinsoskotabogor"
          target="_blank"
          rel="noreferrer"
          title="TikTok"
        >
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M16.6 5.82c-.9-.99-1.4-2.29-1.4-3.65h-3.14v13.7c0 1.63-1.33 2.96-2.96 2.96a2.96 2.96 0 01-2.96-2.96 2.96 2.96 0 012.96-2.96c.27 0 .53.03.78.1v-3.2a6.1 6.1 0 00-.78-.05A6.14 6.14 0 003 15.87a6.14 6.14 0 006.1 6.13 6.14 6.14 0 006.1-6.13V9.02a8.65 8.65 0 004.8 1.46V7.34a5.3 5.3 0 01-3.4-1.52z"
            />
          </svg>
        </a>
        <a
          class="social-icon web"
          href="#"
          target="_blank"
          rel="noreferrer"
          title="Website Resmi"
        >
          <span class="material-symbols-outlined text-[22px]">language</span>
        </a>
      </div>
    </div>

    <a
      aria-label="Chat WhatsApp Dinsos"
      class="wa-fab"
      href="https://wa.me/6285333395667"
      rel="noreferrer"
      target="_blank"
      title="Chat WhatsApp Dinsos"
    >
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.876 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"
        />
        <path
          d="M12.004 2.003c-5.514 0-9.997 4.483-9.997 9.997 0 1.762.462 3.485 1.34 5.003l-1.423 5.194 5.316-1.394a9.96 9.96 0 004.764 1.213h.004c5.514 0 9.997-4.483 9.997-9.997 0-2.67-1.04-5.18-2.929-7.07a9.935 9.935 0 00-7.072-2.946zm5.85 15.848a8.29 8.29 0 01-5.85 2.42h-.003a8.31 8.31 0 01-4.234-1.157l-.304-.18-3.157.828.843-3.078-.198-.317a8.264 8.264 0 01-1.267-4.42c0-4.582 3.729-8.311 8.314-8.311a8.257 8.257 0 015.876 2.437 8.26 8.26 0 012.434 5.878 8.29 8.29 0 01-2.454 5.9z"
        />
      </svg>
    </a>

    <div class="a11y-fab" id="a11y-fab">
      <div
        class="a11y-fab-panel bg-surface-container-lowest rounded-xl shadow-md p-space-md w-64 space-y-space-sm"
      >
        <div class="flex items-center justify-between">
          <span class="font-label-md text-label-md text-primary font-bold"
            >Aksesibilitas</span
          >
          <button
            class="w-6 h-6 flex items-center justify-center rounded text-on-surface-variant hover:bg-surface-container"
            id="a11y-fab-close"
            title="Tutup"
            type="button"
          >
            <span class="material-symbols-outlined text-[16px]">close</span>
          </button>
        </div>
        <div
          class="flex items-center justify-between gap-space-xs bg-surface-container px-space-xs py-1.5 rounded-lg"
        >
          <span class="font-label-sm text-label-sm text-on-surface-variant"
            >Ukuran Fon</span
          >
          <div class="flex items-center gap-space-2xs">
            <button
              class="w-7 h-7 flex items-center justify-center rounded bg-surface-container-lowest text-primary font-bold hover:bg-primary hover:text-on-primary transition-colors"
              title="Perkecil"
              type="button"
            >
              A-
            </button>
            <button
              class="w-7 h-7 flex items-center justify-center rounded bg-surface-container-lowest text-primary font-bold hover:bg-primary hover:text-on-primary transition-colors"
              title="Perbesar"
              type="button"
            >
              A+
            </button>
          </div>
        </div>
        <button
          class="w-full flex items-center gap-space-xs px-space-sm py-2 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm transition-colors"
          type="button"
        >
          <span class="material-symbols-outlined text-[18px] text-secondary"
            >contrast</span
          >
          <span>Mode Kontras Tinggi</span>
        </button>
        <button
          class="w-full flex items-center gap-space-xs px-space-sm py-2 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm transition-colors"
          type="button"
        >
          <span class="material-symbols-outlined text-[18px] text-secondary"
            >motion_photos_off</span
          >
          <span>Kurangi Animasi</span>
        </button>
        <div
          class="flex items-center gap-space-2xs text-on-surface-variant font-label-sm text-label-sm pt-space-2xs"
        >
          <span class="material-symbols-outlined text-[16px] text-tertiary"
            >record_voice_over</span
          >
          <span>Kompatibel NVDA/TalkBack</span>
        </div>
      </div>
      <button
        aria-label="Buka Menu Aksesibilitas"
        class="w-11 h-11 rounded-full bg-primary hover:bg-secondary text-on-primary flex items-center justify-center shadow-lg transition-colors"
        id="a11y-fab-toggle"
        type="button"
      >
        <span class="material-symbols-outlined text-[20px]"
          >accessibility_new</span
        >
      </button>
    </div>

    <button
      aria-label="Kembali ke Atas"
      class="back-to-top"
      id="back-to-top"
      title="Kembali ke Atas"
      type="button"
    >
      <span class="material-symbols-outlined text-[22px]">arrow_upward</span>
    </button>

    <script src="{{ asset('js/script.js') }}"></script>
    <script>
      /* ============================================================
         Data grafik per tahun (dummy — sambungkan ke backend
         SOLID v4 untuk data real melalui endpoint API kebencanaan).
         Angka mengikuti contoh tampilan: total 221 kejadian pada 2025,
         puncak di bulan Maret (77), dan wilayah terdampak terbanyak
         Bogor Selatan (48).
         ============================================================ */
      var dataPerTahun = {
        2026: {
          bulan: {
            categories: [
              "Januari",
              "Februari",
              "Maret",
              "April",
              "Mei",
              "Juni",
            ],
            values: [25, 20, 65, 40, 30, 12],
          },
        },
        2025: {
          bulan: {
            categories: [
              "Januari",
              "Februari",
              "Maret",
              "April",
              "Mei",
              "Juni",
            ],
            values: [30, 24, 77, 47, 33, 10],
          },
        },
      };

      var dataKecamatan = {
        categories: [
          "Bogor Barat",
          "Bogor Selatan",
          "Bogor Tengah",
          "Bogor Timur",
          "Bogor Utara",
          "Tanah Sareal",
        ],
        values: [22, 48, 23, 9, 19, 25],
      };

      var barColors = [
        "#29b6f6",
        "#3f2b96",
        "#22c55e",
        "#fb7f2e",
        "#5b7fa6",
        "#d63bd6",
      ];

      var chartBulan = Highcharts.chart("chart-bulan", {
        chart: {
          type: "column",
          options3d: {
            enabled: true,
            alpha: 10,
            beta: 15,
            depth: 50,
            viewDistance: 25,
          },
          backgroundColor: "transparent",
        },
        title: { text: null },
        credits: { text: "Highcharts.com" },
        xAxis: {
          categories: dataPerTahun["2026"].bulan.categories,
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
              style: {
                fontWeight: "700",
                color: "#121d26",
                textOutline: "none",
              },
            },
          },
        },
        series: [
          {
            name: "Total Bencana",
            data: dataPerTahun["2026"].bulan.values,
          },
        ],
      });

      var chartKecamatan = Highcharts.chart("chart-kecamatan", {
        chart: {
          type: "column",
          options3d: {
            enabled: true,
            alpha: 10,
            beta: 15,
            depth: 50,
            viewDistance: 25,
          },
          backgroundColor: "transparent",
        },
        title: { text: null },
        credits: { text: "Highcharts.com" },
        xAxis: {
          categories: dataKecamatan.categories,
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
              style: {
                fontWeight: "700",
                color: "#121d26",
                textOutline: "none",
              },
            },
          },
        },
        series: [
          {
            name: "Total Bencana",
            data: dataKecamatan.values,
          },
        ],
      });

      document
        .getElementById("filter-tahun")
        .addEventListener("change", function (e) {
          var tahun = e.target.value;
          var data = dataPerTahun[tahun];
          if (!data) return;
          chartBulan.update({
            xAxis: { categories: data.bulan.categories },
          });
          chartBulan.series[0].setData(data.bulan.values);
          var judulChart = document.getElementById("judul-chart-bulan");
          if (judulChart) {
            judulChart.textContent =
              "Grafik Kejadian Bencana Tahun " + tahun + " di Bulan";
          }
        });

      /* Scrollspy sederhana: sorot item Daftar Isi sesuai section yang
         sedang dibaca, supaya sidebar terasa hidup mengikuti scroll. */
      (function () {
        var tocLinks = Array.prototype.slice.call(
          document.querySelectorAll(".tp-toc-link[data-toc]"),
        );
        if (!tocLinks.length) return;

        var sections = tocLinks
          .map(function (link) {
            var id = link.getAttribute("href").replace("#", "");
            return document.getElementById(id);
          })
          .filter(Boolean);

        function setActive(id) {
          tocLinks.forEach(function (link) {
            link.classList.toggle(
              "is-active",
              link.getAttribute("href") === "#" + id,
            );
          });
        }

        var observer = new IntersectionObserver(
          function (entries) {
            entries.forEach(function (entry) {
              if (entry.isIntersecting) setActive(entry.target.id);
            });
          },
          { rootMargin: "-45% 0px -50% 0px", threshold: 0 },
        );

        sections.forEach(function (section) {
          observer.observe(section);
        });
      })();
    </script>

@endsection
