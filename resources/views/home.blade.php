@extends('layouts.app')

@section('title', 'Beranda - SOLID v4 Dinas Sosial Kota Bogor')

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
        <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
        <link
          rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""
        />
        <script
          src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
          integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
          crossorigin=""
        ></script>
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
             Tambahan untuk Tracking Permohonan (pola sama dengan
             halaman Cek Desil): captcha huruf kode + badge status.
             ============================================================ */
          .tr-captcha {
            font-family: "Georgia", serif;
            font-weight: 700;
            font-size: 26px;
            letter-spacing: 0.35em;
            color: #003b62;
            background:
              repeating-linear-gradient(
                115deg,
                rgba(19, 98, 153, 0.08) 0 2px,
                transparent 2px 10px
              ),
              #edf4ff;
            border: 1px dashed #98cbff;
            border-radius: 0.6rem;
            padding: 0.85rem 1rem;
            user-select: none;
            text-align: center;
            position: relative;
          }
          .tr-captcha::before,
          .tr-captcha::after {
            content: "";
            position: absolute;
            height: 1px;
            left: 8%;
            right: 8%;
            background: rgba(0, 59, 98, 0.35);
          }
          .tr-captcha::before {
            top: 38%;
            transform: rotate(-3deg);
          }
          .tr-captcha::after {
            top: 62%;
            transform: rotate(2deg);
          }
          .tr-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 700;
            white-space: nowrap;
          }
          .tr-badge-diterima { background: #d7f3df; color: #146c2e; }
          .tr-badge-diverifikasi { background: #fff2cc; color: #8a5a00; }
          .tr-badge-ditolak { background: #ffdad6; color: #93000a; }
          .tr-badge-selesai { background: #d6e9ff; color: #003b62; }
          #tracking-alert { display: none; }
          #tracking-results { display: none; }
        </style>

@endsection

@section('body_class', 'bg-surface font-body-md text-on-surface antialiased')

@section('content')
    <main class="w-full bg-surface">
      <div class="flex flex-col w-full">
        <section
          class="relative w-full bg-primary text-on-primary"
          id="hero-carousel"
        >
          <div
            class="relative w-full h-[500px] md:h-[600px] lg:h-[695px] overflow-hidden"
          >
            <div
              class="hero-slide-bg absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-700 ease-in-out opacity-100"
              data-slide="0"
              style="
                background-image: url(&quot;assets/img/carousel/carousel1.jpg&quot;);
              "
            ></div>

            <div
              class="hero-slide-bg absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-700 ease-in-out opacity-0"
              data-slide="1"
              style="
                background-image: url(&quot;assets/img/carousel/carousel2.jpg&quot;);
              "
            ></div>

            <div
              class="hero-slide-bg absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-700 ease-in-out opacity-0"
              data-slide="2"
              style="
                background-image: url(&quot;assets/img/carousel/carousel3.jpg&quot;);
              "
            ></div>

            <div
              class="absolute inset-0 bg-gradient-to-r from-primary via-primary/85 to-transparent"
            ></div>

            <div
              class="absolute inset-0 bg-gradient-to-t from-primary/95 via-transparent to-transparent"
            ></div>

            <div
              class="relative z-10 max-w-[1400px] mx-auto h-full px-margin-mobile md:px-margin-tablet lg:px-margin-desktop flex flex-col justify-center pb-16 md:pb-24 lg:pb-32"
            >
              <div class="max-w-2xl space-y-space-md">
                <h1
                  id="hero-subtitle"
                  class="font-display-lg text-display-lg-mobile md:text-display-lg text-on-primary font-bold tracking-tight transition-opacity duration-300"
                >
                  SIBADRA-Dinas Sosial Kota Bogor
                </h1>

                <p
                  id="hero-desc"
                  class="font-body-md text-body-md text-on-primary/90 max-w-xl transition-opacity duration-300"
                >
                  layanan aduan pemerintah kota bogor bisa di akses melalui
                  aplikasi SIBADRA terkait permasalahan sosial.
                </p>

                <div class="relative pt-space-xs flex flex-col items-start gap-space-lg">
  <a
    class="inline-flex items-center gap-space-xs text-on-primary font-label-md text-label-md no-underline transition-all"
    href="#"
  >
    <span> Baca Selengkapnya </span>
    <span class="material-symbols-outlined text-[20px]">
      arrow_circle_right
    </span>
  </a>
</div>
              </div>
            </div>

            <div class="fab-radial" id="fab-radial">
              <a
                class="fab-radial-item"
                href="{{ route('tutorial-pendaftaran') }}"
                title="Tutorial Pendaftaran"
              >
                <span class="fab-radial-item-icon">
                  <img
                    src="{{ asset('assets/img/layanan/book.gif') }}"
                    alt="Tutorial Pendaftaran"
                  />
                </span>

                <span class="fab-radial-item-label">
                  Pendaftaran
                </span>
              </a>

              <a
                class="fab-radial-item"
                href="#tracking-cepat"
                title="Tracking Permohonan BPJS PBI APBD"
              >
                <span class="fab-radial-item-icon">
                  <img
                    src="{{ asset('assets/img/layanan/tracing.gif') }}"
                    alt="Tracking Permohonan BPJS PBI APBD"
                  />
                </span>

                <span class="fab-radial-item-label">
                 BPJS PBI APBD
                </span>
              </a>

              <a
                class="fab-radial-item"
                href="#peta-sebaran"
                title="Peta Penerima Bantuan Sosial"
              >
                <span class="fab-radial-item-icon">
                  <img
                    src="{{ asset('assets/img/layanan/peta.gif') }}"
                    alt="Peta Penerima Bantuan Sosial"
                  />
                </span>

                <span class="fab-radial-item-label">
                  Peta
                </span>
              </a>

              <a
                class="fab-radial-item"
                href="https://dinsos.kotabogor.go.id/"
                rel="noreferrer"
                target="_blank"
                title="Website DINSOS Kota Bogor"
              >
                <span class="fab-radial-item-icon">
                  <img
                    src="{{ asset('assets/img/layanan/browser.gif') }}"
                    alt="Website DINSOS Kota Bogor"
                  />
                </span>

                <span class="fab-radial-item-label">
                  Website
                </span>
              </a>

              <a
                class="fab-radial-item"
                href="{{ route('kesan') }}"
                title="KESAN Kolaborasi Entaskan Kemiskinan"
              >
                <span class="fab-radial-item-icon">
                  <img
                    src="{{ asset('assets/img/layanan/kesan.gif') }}"
                    alt="KESAN Kolaborasi Entaskan Kemiskinan"
                  />
                </span>

                <span class="fab-radial-item-label">
                  Kesan
                </span>
              </a>

              <a
                class="fab-radial-item"
                href="{{ route('kejadian-bencana') }}"
                title="Bantuan Sosial Pasca Bencana"
              >
                <span class="fab-radial-item-icon">
                  <img
                    src="{{ asset('assets/img/layanan/bantuan.gif') }}"
                    alt="Bantuan Sosial Pasca Bencana"
                  />
                </span>

                <span class="fab-radial-item-label">
                  Bantuan
                </span>
              </a>
            </div>

            <div
              class="absolute bottom-6 left-0 right-0 z-20 flex items-center justify-center gap-space-xs"
            >
              <button
                class="hero-indicator"
                data-slide-index="0"
                aria-label="Slide 1"
                type="button"
              ></button>

              <button
                class="hero-indicator"
                data-slide-index="1"
                aria-label="Slide 2"
                type="button"
              ></button>

              <button
                class="hero-indicator"
                data-slide-index="2"
                aria-label="Slide 3"
                type="button"
              ></button>
            </div>
          </div>
        </section>

        <section class="w-full pt-24 md:pt-28 pb-space-3xl">
          <div
            class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop"
          >
            <div
              class="bg-surface-container-low rounded-xl p-space-xl md:p-space-2xl grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-center"
            >
              <div class="lg:col-span-4 flex flex-col items-center text-center">
                <div
                  class="relative w-64 h-80 rounded-xl overflow-hidden shadow-md bg-surface-container-high"
                >
                  <img
                    class="w-full h-full object-cover"
                    src="{{ asset('assets/img/kepala-dinas/kepala-dinas.jpg') }}"
                    alt="Foto Kepala Dinas"
                  />
                  <div
                    class="absolute inset-0 bg-gradient-to-t from-primary/80 via-transparent to-transparent"
                  ></div>
                  <div class="absolute bottom-3 inset-x-3 text-on-primary">
                    <span
                      class="font-label-sm text-label-sm block uppercase tracking-wide opacity-90"
                      >Kepala Dinas Sosial</span
                    >
                    <span class="font-label-lg text-label-lg font-bold"
                      >Atep Budiman, S.STP., M.M.</span
                    >
                  </div>
                </div>
              </div>
                           <div class="lg:col-span-8 space-y-space-md">
                <div class="space-y-space-xs">
                  <h2
                    class="font-headline-lg text-headline-lg text-primary font-bold"
                  >
                    Sambutan Resmi Kepala Dinas
                  </h2>
                </div>
                <p
                  class="font-body-md text-body-md text-on-surface font-medium italic"
                >
                  "Assalamualaikum Warahmatullahi Wabarakatuh."
                </p>

                <div
                  id="sambutan-body"
                  class="sambutan-body sambutan-collapsed space-y-space-md"
                >
                  <p
                    class="font-body-md text-body-md text-on-surface-variant leading-relaxed"
                  >
                   Puji syukur kita panjatkan kehadirat Allah Yang Maha Esa, atas rahmat dan hidayah-Nya saat ini kami telah menghadirkan website resmi milik Pemerintah Kota Bogor dengan alamat website pelayanansosial.kotabogor.go.id dimana dengan diresmikannya Website Pelayanan Dinas Sosial secara otomatis Kota Bogor telah ikut hadir ke tengah dunia internasional melalui pemanfaatan sarana teknologi informatika.
                  </p>
                  <p
                    class="font-body-md text-body-md text-on-surface-variant leading-relaxed"
                  >
                    Aplikasi ini berisi mengenai pelayanan Jaminan Kesehatan yang dianggarkan oleh Pemerintah Kota Bogor dan berita informasi Data Center tentang Fase Kemiskinan di Kota Bogor,  sehingga dengan adanya website ini, Kota Bogor akan terpublikasi ke tengah masyarakat serta dunia internasional.
                  </p>
                  <p
                    class="font-body-md text-body-md text-on-surface-variant leading-relaxed"
                  >
                    Selain sebagai promosi daerah kepada pihak luar, melalui website ini masyarakat juga bisa mengakses Pendaftaran BPJS Kehatan PBI secara Online dan mencari informasi penerima bantuan sosial dari Pemerintah Pusat maupun Daerah yang dibutuhkan masyarakat.
                  </p>
                  <p
                    class="font-body-md text-body-md text-on-surface-variant leading-relaxed"
                  >
                    Selanjutnya, hadirnya website ini merupakan salah satu langkah yang ditempuh Pemerintah Kota Bogor untuk dijadikan alat ukur dalam mengentaskan Kemiskinan di Kota Bogor.
                  </p>
                   <p
                    class="font-body-md text-body-md text-on-surface font-medium italic"
                  >
                    "Wabillahi Taufik Walhhidayah Wassalamualaikum Warahmatulahi Wabarakatu."
                  </p>
                </div>

                <div
                  class="pt-space-sm flex flex-wrap items-center gap-space-md"
                >
                  <button
                    id="sambutan-toggle-btn"
                    type="button"
                    class="px-space-md py-2.5 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-secondary transition-colors inline-flex items-center gap-space-xs"
                  >
                    <span id="sambutan-toggle-label">Baca Selengkapnya</span>
                    <span
                      class="material-symbols-outlined text-[18px]"
                      id="sambutan-toggle-icon"
                      >expand_more</span
                    >
                  </button>
                  <a
                    class="px-space-md py-2.5 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-secondary transition-colors inline-flex items-center gap-space-xs"
                    href="https://dinsos.kotabogor.go.id/"
                    rel="noreferrer"
                    target="_blank"
                  >
                    <span>Profil Lengkap</span>
                    <span class="material-symbols-outlined text-[18px]"
                      >open_in_new</span
                    >
                  </a>
                </div>
              </div>
            </div>
          </div>
        </section>

       <section
  class="w-full py-space-2xl bg-surface-container-low"
  id="tracking-cepat"
>
  <div
    class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-lg"
  >
    <div class="max-w-2xl space-y-space-xs">
      <h2 class="font-headline-lg text-headline-lg text-primary font-bold">
        Tracking Permohonan BPJS PBI APBD
      </h2>
      <p class="font-body-md text-body-md text-on-surface-variant">
        Lacak permohonan BPJS PBI APBD Kota Bogor
      </p>
    </div>

    <div
      class="bg-surface-container-lowest rounded-xl p-space-xl md:p-space-2xl shadow-sm grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-start"
      id="tracking-3col"
    >
      <div class="lg:col-span-3 flex items-center justify-center">
        <img
          alt="Maskot Dinsos Kota Bogor"
          class="w-40 md:w-full max-w-[220px] h-auto object-contain"
          src="{{ asset('assets/img/rubo-mencari/rubo-mencari.png') }}"
        />
      </div>

      <div class="lg:col-span-5 space-y-space-md">
        <div
          id="tracking-alert"
          class="flex items-start gap-space-sm rounded-xl border border-error-container bg-error-container/60 px-space-md py-space-sm font-body-sm text-body-sm text-on-error-container"
        >
          <span class="material-symbols-outlined text-[20px]">error</span>
          <span id="tracking-alert-text"
            >Data permohonan tidak ditemukan. Silakan periksa kembali No
            Register / NIK Anda.</span
          >
        </div>

        <form class="space-y-space-md" id="tracking-form">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
            <div class="space-y-space-xs">
              <label
                class="block font-label-sm text-label-sm text-on-surface-variant"
                >No Register / NIK</label
              >
              <div class="relative">
                <span
                  class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline text-[22px]"
                  >badge</span
                >
                <input
                  class="w-full pl-12 pr-4 py-3.5 rounded-lg bg-surface-container text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-secondary transition-all"
                  id="tracking-input"
                  placeholder="Masukkan No Register / NIK"
                  type="text"
                />
              </div>
            </div>

            <div class="space-y-space-xs">
              <label
                class="block font-label-sm text-label-sm text-on-surface-variant"
                >Huruf Kode</label
              >
              <div class="flex items-center gap-space-sm">
                <div class="tr-captcha flex-1" id="tracking-captcha-text">
                  7Q2 KX
                </div>
                <button
                  class="w-11 h-11 shrink-0 rounded-lg border border-outline-variant flex items-center justify-center text-secondary hover:bg-surface-container-low transition-colors"
                  id="tracking-captcha-refresh"
                  title="Muat ulang kode"
                  type="button"
                >
                  <span class="material-symbols-outlined text-[20px]"
                    >refresh</span
                  >
                </button>
              </div>
              <input
                class="w-full px-4 py-3 rounded-lg bg-surface-container text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-secondary transition-all"
                id="tracking-captcha-input"
                placeholder="Ketik huruf kode di atas"
                type="text"
              />
            </div>
          </div>

          <div class="flex items-center gap-space-sm pt-space-xs">
            <button
              class="flex-1 sm:flex-none inline-flex items-center justify-center gap-space-xs px-space-xl py-3 rounded-lg bg-secondary hover:bg-primary text-on-primary font-label-md text-label-md transition-colors shadow-sm"
              id="tracking-submit-btn"
              type="submit"
            >
              <span
                class="material-symbols-outlined text-[20px]"
                id="tracking-submit-icon"
                >search</span
              >
              <span id="tracking-submit-label">Lacak Berkas</span>
            </button>
            <button
              class="inline-flex items-center justify-center gap-space-xs px-space-md py-3 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low font-label-md text-label-md transition-colors"
              id="tracking-reset-btn"
              type="button"
            >
              <span class="material-symbols-outlined text-[18px]"
                >restart_alt</span
              >
              Batal
            </button>
          </div>
        </form>
      </div>

      <div
        class="lg:col-span-4 h-full"
        id="tracking-results-wrapper"
      >


        <div
          id="tracking-results"
          class="h-full rounded-xl border border-outline-variant/30 bg-surface-container-low p-space-lg space-y-space-sm"
        >
          <div class="flex items-center justify-between gap-space-sm">
            <h3 class="font-headline-sm text-headline-sm text-primary font-bold">
              Status Berkas
            </h3>
            <span class="tr-badge" id="tracking-status">-</span>
          </div>

          <dl class="space-y-space-xs font-body-sm text-body-sm">
            <div class="flex items-start justify-between gap-space-sm">
              <dt class="text-on-surface-variant shrink-0">No Register/NIK</dt>
              <dd class="text-on-surface font-semibold text-right" id="tracking-no-register">-</dd>
            </div>
            <div class="flex items-start justify-between gap-space-sm">
              <dt class="text-on-surface-variant shrink-0">Nama Pemohon</dt>
              <dd class="text-on-surface font-bold text-right" id="tracking-nama">-</dd>
            </div>
            <div class="flex items-start justify-between gap-space-sm">
              <dt class="text-on-surface-variant shrink-0">Tgl Pengajuan</dt>
              <dd class="text-on-surface font-semibold text-right" id="tracking-tanggal">-</dd>
            </div>
          </dl>

          <div class="pt-space-xs border-t border-outline-variant/30">
            <p class="font-body-sm text-body-sm text-on-surface-variant" id="tracking-keterangan">-</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
            <section class="w-full py-space-2xl" id="peta-sebaran">
          <div
            class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-lg"
          >
            <div class="max-w-2xl space-y-space-xs">
              <h2
                class="font-headline-lg text-headline-lg text-primary font-bold"
              >
                Peta Penerima Bantuan Sosial Kota Bogor
              </h2>
            </div>

            <div
              class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12"
              id="peta-app"
            >
              <div
                class="lg:col-span-3 bg-surface-container-low p-space-md space-y-space-sm border-b lg:border-b-0 lg:border-r border-outline-variant/30"
              >
                <div class="flex items-center gap-space-xs">
                  <span
                    class="material-symbols-outlined text-primary text-[22px]"
                    >map</span
                  >
                  <span
                    class="font-label-lg text-label-lg text-primary font-bold"
                    >SOLID Peta</span
                  >
                </div>

                <div class="space-y-1">
                  <label
                    class="block font-label-sm text-label-sm text-on-surface-variant"
                    >Kecamatan</label
                  >
                  <select
                    id="peta-kecamatan"
                    class="w-full py-2 px-space-sm rounded-lg bg-surface-container-lowest border border-outline-variant/40 text-on-surface font-label-md text-label-md focus:outline-none focus:ring-2 focus:ring-secondary"
                  >
                    <option value="">-Pilih-</option>
                  </select>
                </div>

                <div class="space-y-1">
                  <label
                    class="block font-label-sm text-label-sm text-on-surface-variant"
                    >Desa/Kelurahan</label
                  >
                  <select
                    id="peta-kelurahan"
                    class="w-full py-2 px-space-sm rounded-lg bg-surface-container-lowest border border-outline-variant/40 text-on-surface font-label-md text-label-md focus:outline-none focus:ring-2 focus:ring-secondary"
                    disabled
                  >
                    <option value="">-Pilih-</option>
                  </select>
                </div>

                <div class="space-y-1">
                  <label
                    class="block font-label-sm text-label-sm text-on-surface-variant"
                    >Tahun</label
                  >
                  <select
                    id="peta-tahun"
                    class="w-full py-2 px-space-sm rounded-lg bg-surface-container-lowest border border-outline-variant/40 text-on-surface font-label-md text-label-md focus:outline-none focus:ring-2 focus:ring-secondary"
                  ></select>
                </div>

                <div class="flex items-center gap-space-xs pt-space-2xs">
                  <button
                    id="peta-tampilkan"
                    type="button"
                    class="flex-1 px-space-md py-2 rounded-lg bg-secondary hover:bg-primary text-on-primary font-label-md text-label-md transition-colors"
                  >
                    Tampilkan
                  </button>
                  <button
                    id="peta-reset"
                    type="button"
                    class="px-space-md py-2 rounded-lg bg-surface-container-lowest border border-outline-variant/40 hover:bg-surface-container text-on-surface-variant font-label-md text-label-md transition-colors"
                  >
                    Reset
                  </button>
                </div>
                 <p
                    class="font-body-sm text-body-sm text-on-surface-variant/70 italic"
                  >
                    *Data pada peta ini adalah data ilustrasi untuk kebutuhan
                    tampilan.
                  </p>
              </div>

              <div class="lg:col-span-9 grid grid-cols-1 xl:grid-cols-12">
                <div class="xl:col-span-8 relative isolate">
                  <div id="peta-map" class="w-full h-[340px] md:h-[420px] xl:h-[480px] bg-surface-container relative overflow-hidden">
                    <div id="peta-leaflet" class="w-full h-full"></div>
                  </div>
                  <div
                    class="absolute top-4 left-4 z-20 bg-surface-container-lowest/95 backdrop-blur-md px-space-sm py-2 rounded-lg shadow-sm flex items-center gap-space-xs"
                  >
                    <span
                      class="w-3 h-3 rounded-full bg-secondary animate-ping"
                    ></span>
                    <span
                      class="font-label-sm text-label-sm text-primary font-bold"
                      >Pusat Data Dinsos Kota Bogor</span
                    >
                  </div>
                  <div
                    class="absolute bottom-4 right-4 z-20 bg-surface-container-lowest/95 backdrop-blur-md px-space-sm py-space-xs rounded-lg shadow-sm space-y-1"
                  >
                    <span
                      class="block font-label-sm text-label-sm text-on-surface font-bold mb-1"
                      >Legend</span
                    >
                    <span
                      class="flex items-center gap-space-2xs font-body-sm text-body-sm text-on-surface-variant"
                      ><span
                        class="w-3 h-3 rounded-sm"
                        style="background: #4caf50"
                      ></span
                      >Sedikit</span
                    >
                    <span
                      class="flex items-center gap-space-2xs font-body-sm text-body-sm text-on-surface-variant"
                      ><span
                        class="w-3 h-3 rounded-sm"
                        style="background: #ffca28"
                      ></span
                      >Sedang</span
                    >
                    <span
                      class="flex items-center gap-space-2xs font-body-sm text-body-sm text-on-surface-variant"
                      ><span
                        class="w-3 h-3 rounded-sm"
                        style="background: #fb8c00"
                      ></span
                      >Banyak</span
                    >
                    <span
                      class="flex items-center gap-space-2xs font-body-sm text-body-sm text-on-surface-variant"
                      ><span
                        class="w-3 h-3 rounded-sm"
                        style="background: #e53935"
                      ></span
                      >Sangat Banyak</span
                    >
                  </div>
                </div>

                <div
                  class="xl:col-span-4 bg-surface-container p-space-md flex flex-col justify-between space-y-space-sm xl:h-[480px]"
                >
                  <div class="space-y-2">
  <div class="flex items-center justify-between">
    <span class="font-label-sm text-label-sm text-secondary font-bold uppercase">Informasi</span>
    <span id="peta-info-tahun" class="px-space-xs py-0.5 rounded bg-surface-container-lowest font-label-sm text-label-sm text-on-surface font-semibold">Tahun 2026</span>
  </div>
  <h3 id="peta-info-wilayah" class="font-headline-sm text-headline-sm text-primary font-bold">
    Semua Wilayah Kota Bogor
  </h3>
  <div class="space-y-1 font-body-sm text-body-sm text-on-surface-variant">
    <div class="flex items-center justify-between">
      <span>Kab/Kota</span><span id="peta-info-kabkota" class="font-semibold text-on-surface">KOTA BOGOR</span>
    </div>
    <div class="flex items-center justify-between">
      <span>Kecamatan</span><span id="peta-info-kecamatan-txt" class="font-semibold text-on-surface">–</span>
    </div>
    <div class="flex items-center justify-between">
      <span>Kelurahan/Desa</span><span id="peta-info-kelurahan-txt" class="font-semibold text-on-surface">–</span>
    </div>
  </div>

  <div class="space-y-1 pt-1.5 border-t border-outline-variant/30">
    <span class="block font-label-sm text-label-sm text-secondary font-bold uppercase">Jumlah Penduduk</span>
    <div class="p-1.5 px-space-sm rounded-lg bg-surface-container-lowest flex items-center justify-between">
      <span class="font-body-sm text-body-sm text-on-surface-variant">Laki-laki:</span>
      <span id="peta-info-laki" class="font-label-md text-label-md text-on-surface font-bold">–</span>
    </div>
    <div class="p-1.5 px-space-sm rounded-lg bg-surface-container-lowest flex items-center justify-between">
      <span class="font-body-sm text-body-sm text-on-surface-variant">Perempuan:</span>
      <span id="peta-info-perempuan" class="font-label-md text-label-md text-on-surface font-bold">–</span>
    </div>
    <div class="p-1.5 px-space-sm rounded-lg bg-surface-container-lowest flex items-center justify-between">
      <span class="font-body-sm text-body-sm text-on-surface-variant">Total:</span>
      <span id="peta-info-penduduk" class="font-label-md text-label-md text-tertiary font-bold">–</span>
    </div>
  </div>

  <div class="space-y-1 pt-1.5 border-t border-outline-variant/30">
    <span class="block font-label-sm text-label-sm text-secondary font-bold uppercase">Jumlah Penerima Bantuan</span>
    <div class="p-1.5 px-space-sm rounded-lg bg-surface-container-lowest flex items-center justify-between">
      <span class="font-body-sm text-body-sm text-on-surface-variant">PKH:</span>
      <span id="peta-info-pkh" class="font-label-md text-label-md text-primary font-bold">–</span>
    </div>
    <div class="p-1.5 px-space-sm rounded-lg bg-surface-container-lowest flex items-center justify-between">
      <span class="font-body-sm text-body-sm text-on-surface-variant">PBI APBD:</span>
      <span id="peta-info-pbi" class="font-label-md text-label-md text-primary font-bold">–</span>
    </div>
    <div class="p-1.5 px-space-sm rounded-lg bg-surface-container-lowest flex items-center justify-between">
      <span class="font-body-sm text-body-sm text-on-surface-variant">BPNT:</span>
      <span id="peta-info-bpnt" class="font-label-md text-label-md text-primary font-bold">–</span>
    </div>
  </div>
</div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section
          class="w-full py-space-3xl bg-surface-container-low"
          id="berita"
        >
          <div
            class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-2xl"
          >
            <div class="max-w-2xl space-y-space-xs">
              <h2
                class="font-headline-lg text-headline-lg text-primary font-bold"
              >
                Berita Terbaru
              </h2>
            </div>

            <div class="space-y-space-lg">
              <div class="flex items-center justify-between">
                <div class="space-y-space-2xs">
                  <h3
                    class="font-headline-md text-headline-md text-primary font-bold"
                  >
                    Berita Dinas Sosial Kota Bogor
                  </h3>
                  <span class="block w-10 h-1 rounded-full bg-secondary"></span>
                </div>
                <a
                  class="text-secondary font-label-md text-label-md font-bold hover:underline inline-flex items-center gap-space-2xs"
                  href="https://pelayanansosial.kotabogor.go.id/index.php/portal/berita"
                >
                  <span>Lihat Semua</span>
                  <span class="material-symbols-outlined text-[18px]"
                    >arrow_forward</span
                  >
                </a>
              </div>

              <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-lg"
              >
                <a class="berita-card group" href="#">
                  <div class="berita-card-img">
                    <img
                     src="{{ asset('assets/img/berita/berita-1.jpg') }}"
                      alt="Penyerahan simbolis BPJS PBI dan bantuan sosial"
                    />
                    <span class="berita-card-date">26 Januari 2026</span>
                  </div>
                  <div class="berita-card-body">
                    <h4 class="berita-card-title">
                      Pada Hari Senin Seluruh Pegawai Dinas Sosial Melaksanakan Penandatanganan Pakta Integritas Tahun 2026 untuk Menjaga Komitmen Bersama Agar Menjadi Lebik Baik dan Optimal
                    </h4>
                  </div>
                </a>
                <a class="berita-card group" href="#">
                  <div class="berita-card-img">
                    <img
                      src="{{ asset('assets/img/berita/berita-2.jpg') }}"
                      alt="Sosialisasi verifikasi desil"
                    />
                    <span class="berita-card-date">10 Juni 2025</span>
                  </div>
                  <div class="berita-card-body">
                    <h4 class="berita-card-title">
                      SP4N-LAPOR! adalah Sistem Pengelolaan Pengaduan Pelayanan Publik Nasional – Layanan Aspirasi dan Pengaduan Online Rakyat, sebuah platform terintegrasi yang memungkinkan masyarakat menyampaikan aspirasi dan pengaduan terkait layanan publik pemerintah secara online. Platform ini dikelola oleh Kementerian PANRB, Kantor Staf Presiden (KSP), dan Ombudsman RI
                    </h4>
                  </div>
                </a>
                <a class="berita-card group" href="#">
                  <div class="berita-card-img">
                    <img
                       src="{{ asset('assets/img/berita/berita-3.png') }}"
                      alt="Kolaborasi entaskan kemiskinan"
                    />
                    <span class="berita-card-date">17 September 2025</span>
                  </div>
                  <div class="berita-card-body">
                    <h4 class="berita-card-title">
                      Komitmen Dinas Sosial Kota Bogor untuk menginternalisasi budaya BerAKHLAK
                    </h4>
                  </div>
                </a>
                <a class="berita-card group" href="#">
                  <div class="berita-card-img">
                    <img
                       src="{{ asset('assets/img/berita/berita-4.png') }}"
                      alt="Pembaruan alur pelayanan pengaduan"
                    />
                    <span class="berita-card-date">15 Oktober 2025</span>
                  </div>
                  <div class="berita-card-body">
                    <h4 class="berita-card-title">
                     hallo sobat sosial berikut layanan aduan pemerintah kota bogor bisa di akses melalui aplikasi SIBADRA terkait permasalahan sosial
                    </h4>
                  </div>
                </a>
              </div>
            </div>

            <div class="space-y-space-lg">
              <div class="flex items-center justify-between">
                <div class="space-y-space-2xs">
                  <h3
                    class="font-headline-md text-headline-md text-primary font-bold"
                  >
                    Berita Kota Bogor
                  </h3>
                  <span class="block w-10 h-1 rounded-full bg-secondary"></span>
                </div>
                <a
                  class="text-secondary font-label-md text-label-md font-bold hover:underline inline-flex items-center gap-space-2xs"
                  href="#"
                >
                  <span>Lihat Semua</span>
                  <span class="material-symbols-outlined text-[18px]"
                    >arrow_forward</span
                  >
                </a>
              </div>

              <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-lg"
              >
                <a class="berita-card group" href="#">
                  <div class="berita-card-img">
                    <img
                     src="{{ asset('assets/img/berita/berita-5.jpg') }}"
                    />
                    <span class="berita-card-date">07 September2026</span>
                  </div>
                  <div class="berita-card-body">
                    <h4 class="berita-card-title">
                     Pemkot Bogor dan PT KAI Teken MoU Penataan dan Pengembangan Kawasan Stasiun Bogor
                    </h4>
                  </div>
                </a>
                <a class="berita-card group" href="#">
                  <div class="berita-card-img">
                    <img
                      src="{{ asset('assets/img/berita/berita-6.jpg') }}"
                      alt="Rakor percepatan penyaluran bansos"
                    />
                    <span class="berita-card-date">07 September2026</span>
                  </div>
                  <div class="berita-card-body">
                    <h4 class="berita-card-title">
                      Water Mist Terus Dilakukan, Pemkot Bogor Minimalisir Sebaran Abu Vulkanik
                    </h4>
                  </div>
                </a>
                <a class="berita-card group" href="#">
                  <div class="berita-card-img">
                    <img
                      src="{{ asset('assets/img/berita/berita-7.jpg') }}"
                      alt="Pelatihan petugas PSM"
                    />
                    <span class="berita-card-date">07 September2026</span>
                  </div>
                  <div class="berita-card-body">
                    <h4 class="berita-card-title">
                      Pemanfaatan Sampah Plastik dan Minyak Jelantah, Pemkot Bogor Perkuat Ekonomi Sirkular
                    </h4>
                  </div>
                </a>
                <a class="berita-card group" href="#">
                  <div class="berita-card-img">
                    <img
                      src="{{ asset('assets/img/berita/berita-8.jpg') }}"
                      alt="Audiensi Dinsos dengan BAZNAS"
                    />
                    <span class="berita-card-date">07 September2026</span>
                  </div>
                  <div class="berita-card-body">
                    <h4 class="berita-card-title">
                      Tekan Dampak Abu Vulkanik, Pemkot Bogor Semprot Jalan Protokol dengan Water Mist
                    </h4>
                  </div>
                </a>
              </div>
            </div>
        </section>

        <section class="w-full py-space-2xl bg-surface-container-lowest">
          <div
            class="max-w-[1450px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-lg text-center"
          >
            <div class="flex items-center justify-center gap-space-xs">
              <span class="material-symbols-outlined text-primary text-[28px]"
                >handshake</span
              >
              <h2
                class="font-headline-lg text-headline-lg text-primary font-bold"
              >
                Kolaborasi &amp; Integrasi
              </h2>
            </div>

            <div class="mitra-carousel" id="mitra-carousel">
              <div class="mitra-track" id="mitra-track">
                <div class="mitra-slide">
                  <div class="mitra-logo">
                    <img
                      src="{{ asset('assets/img/mitra/dinkes.png') }}"
                      alt="Dinas Kesehatan"
                    />
                  </div>
                  <div class="mitra-logo">
                    <img
                      src="{{ asset('assets/img/mitra/disdukcapil.png') }}"
                      alt="Disdukcapil"
                    />
                  </div>
                  <div class="mitra-logo">
                    <img
                      src="{{ asset('assets/img/mitra/diskominfo.png') }}"
                      alt="Diskominfo"
                    />
                  </div>
                  <div class="mitra-logo">
                    <img src="{{ asset('assets/img/mitra/dinsos.png') }}" alt="Dinas Sosial" />
                  </div>
                  <div class="mitra-logo">
                    <img
                      src="{{ asset('assets/img/mitra/disdik.png') }}"
                      alt="Dinas Pendidikan"
                    />
                  </div>
                </div>

                <div class="mitra-slide">
                  <div class="mitra-logo">
                    <img src="{{ asset('assets/img/mitra/bpjs.png') }}" alt="BPJS" />
                  </div>
                  <div class="mitra-logo">
                    <img
                      src="{{ asset('assets/img/mitra/kemendagri-2.png') }}"
                      alt="Kemendagri"
                    />
                  </div>
                  <div class="mitra-logo">
                    <img src="{{ asset('assets/img/mitra/kemensos.png') }}" alt="Kemensos" />
                  </div>
                  <div class="mitra-logo">
                    <img src="{{ asset('assets/img/mitra/kemdikbud.png') }}" alt="Kemdikbud" />
                  </div>
                  <div class="mitra-logo">
                    <img
                      src="{{ asset('assets/img/mitra/kotabogor-rusa.png') }}"
                      alt="Kota Bogor"
                    />
                  </div>
                </div>

                <div class="mitra-slide">
                  <div class="mitra-logo">
                    <img
                      src="{{ asset('assets/img/mitra/bogor-berlari.jpeg') }}"
                      alt="Bogor Berlari"
                    />
                  </div>
                  <div class="mitra-logo">
                    <img
                      src="{{ asset('assets/img/mitra/bkad-bogor.png') }}"
                      alt="BKAD Bogor"
                    />
                  </div>
                  <div class="mitra-logo">
                    <img src="{{ asset('assets/img/mitra/tagana.png') }}" alt="Tagana" />
                  </div>
                  <div class="mitra-logo">
                    <img src="{{ asset('assets/img/mitra/bni46.png') }}" alt="BNI" />
                  </div>
                  <div class="mitra-logo">
                    <img src="{{ asset('assets/img/mitra/logo-bsre.png') }}" alt="BSRE" />
                  </div>
                </div>

                <div class="mitra-slide">
                  <div class="mitra-logo">
                    <img src="{{ asset('assets/img/mitra/pkh-logo.png') }}" alt="PKH" />
                  </div>
                  <div class="mitra-logo">
                    <img src="{{ asset('assets/img/mitra/rz.jpg') }}" alt="Rumah Zakat" />
                  </div>
                  <div class="mitra-logo">
                    <img
                      src="{{ asset('assets/img/mitra/baznas.png') }}"
                      alt="BAZNAS Jawa Barat"
                    />
                  </div>
                  <div class="mitra-logo">
                    <img
                      src="{{ asset('assets/img/mitra/dhuafa.png') }}"
                      alt="Dompet Dhuafa"
                    />
                  </div>
                  <div class="mitra-logo">
                    <img src="{{ asset('assets/img/mitra/salamaid.png') }}" alt="SalamAid" />
                  </div>
                </div>
              </div>
            </div>

            <div
              class="flex items-center justify-center gap-space-2xs pt-space-xs"
              id="mitra-dots"
            >
              <button
                class="mitra-dot is-active"
                data-slide-index="0"
                type="button"
                aria-label="Slide mitra 1"
              ></button>
              <button
                class="mitra-dot"
                data-slide-index="1"
                type="button"
                aria-label="Slide mitra 2"
              ></button>
              <button
                class="mitra-dot"
                data-slide-index="2"
                type="button"
                aria-label="Slide mitra 3"
              ></button>
              <button
                class="mitra-dot"
                data-slide-index="3"
                type="button"
                aria-label="Slide mitra 4"
              ></button>
            </div>
          </div>
        </section>
      </div>
    </main>

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
      </div>
      <button
        aria-label="Buka Menu Aksesibilitas"
        class="w-11 h-11 rounded-full bg-primary hover:bg-secondary text-on-primary flex items-center justify-center shadow-lg transition-colors"
        id="a11y-fab-toggle"
        type="button"
      >
        <span class="material-symbols-outlined text-[20px]"
          >accessibility</span
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

@endsection
