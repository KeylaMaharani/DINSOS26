@extends('layouts.app')

@section('title', 'Beranda - SOLID v4 Dinas Sosial Kota Bogor')

{{--
  PENTING: layout memakai @stack('head'), jadi di sini WAJIB pakai
  @push('head') ... @endpush (bukan @section('head')).
  Yang di-push hanya library khusus halaman ini (Leaflet).
  Font, style.css, Tailwind CDN + config sudah dimuat di layout.
--}}
@push('head')
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
@endpush

@section('body_class', 'bg-surface font-body-md text-on-surface antialiased')

@section('content')
  <div class="flex flex-col w-full">

    {{-- ================= HERO CAROUSEL ================= --}}
    <section class="relative w-full bg-primary text-on-primary" id="hero-carousel">
      <div class="relative w-full h-[500px] md:h-[600px] lg:h-[695px] overflow-hidden">
        <div
          class="hero-slide-bg absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-700 ease-in-out opacity-100"
          data-slide="0"
          style="background-image: url('{{ asset('assets/img/carousel/carousel1.jpg') }}');"
        ></div>

        <div
          class="hero-slide-bg absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-700 ease-in-out opacity-0"
          data-slide="1"
          style="background-image: url('{{ asset('assets/img/carousel/carousel2.jpg') }}');"
        ></div>

        <div
          class="hero-slide-bg absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-700 ease-in-out opacity-0"
          data-slide="2"
          style="background-image: url('{{ asset('assets/img/carousel/carousel3.jpg') }}');"
        ></div>

        <div class="absolute inset-0 bg-gradient-to-r from-primary via-primary/85 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-primary/95 via-transparent to-transparent"></div>

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
              layanan aduan pemerintah kota bogor bisa di akses melalui aplikasi
              SIBADRA terkait permasalahan sosial.
            </p>

            <div class="relative pt-space-xs flex flex-col items-start gap-space-lg">
              <a
                class="inline-flex items-center gap-space-xs text-on-primary font-label-md text-label-md no-underline transition-all"
                href="#"
              >
                <span>Baca Selengkapnya</span>
                <span class="material-symbols-outlined text-[20px]">arrow_circle_right</span>
              </a>
            </div>
          </div>
        </div>

        {{-- Akses cepat 6 ikon --}}
        <div class="fab-radial" id="fab-radial">
          <a class="fab-radial-item" href="{{ route('tutorial-pendaftaran') }}" title="Tutorial Pendaftaran">
            <span class="fab-radial-item-icon">
              <img src="{{ asset('assets/img/layanan/book.gif') }}" alt="Tutorial Pendaftaran" />
            </span>
            <span class="fab-radial-item-label">Pendaftaran</span>
          </a>

          <a class="fab-radial-item" href="#tracking-cepat" title="Tracking Permohonan BPJS PBI APBD">
            <span class="fab-radial-item-icon">
              <img src="{{ asset('assets/img/layanan/tracing.gif') }}" alt="Tracking Permohonan BPJS PBI APBD" />
            </span>
            <span class="fab-radial-item-label">BPJS PBI APBD</span>
          </a>

          <a class="fab-radial-item" href="#peta-sebaran" title="Peta Penerima Bantuan Sosial">
            <span class="fab-radial-item-icon">
              <img src="{{ asset('assets/img/layanan/peta.gif') }}" alt="Peta Penerima Bantuan Sosial" />
            </span>
            <span class="fab-radial-item-label">Peta</span>
          </a>

          <a
            class="fab-radial-item"
            href="https://dinsos.kotabogor.go.id/"
            rel="noreferrer"
            target="_blank"
            title="Website DINSOS Kota Bogor"
          >
            <span class="fab-radial-item-icon">
              <img src="{{ asset('assets/img/layanan/browser.gif') }}" alt="Website DINSOS Kota Bogor" />
            </span>
            <span class="fab-radial-item-label">Website</span>
          </a>

          <a class="fab-radial-item" href="{{ route('kesan') }}" title="KESAN Kolaborasi Entaskan Kemiskinan">
            <span class="fab-radial-item-icon">
              <img src="{{ asset('assets/img/layanan/kesan.gif') }}" alt="KESAN Kolaborasi Entaskan Kemiskinan" />
            </span>
            <span class="fab-radial-item-label">Kesan</span>
          </a>

          <a class="fab-radial-item" href="{{ route('kejadian-bencana') }}" title="Bantuan Sosial Pasca Bencana">
            <span class="fab-radial-item-icon">
              <img src="{{ asset('assets/img/layanan/bantuan.gif') }}" alt="Bantuan Sosial Pasca Bencana" />
            </span>
            <span class="fab-radial-item-label">Bantuan</span>
          </a>
        </div>

        <div class="absolute bottom-6 left-0 right-0 z-20 flex items-center justify-center gap-space-xs">
          <button class="hero-indicator" data-slide-index="0" aria-label="Slide 1" type="button"></button>
          <button class="hero-indicator" data-slide-index="1" aria-label="Slide 2" type="button"></button>
          <button class="hero-indicator" data-slide-index="2" aria-label="Slide 3" type="button"></button>
        </div>
      </div>
    </section>

    {{-- ================= SAMBUTAN KEPALA DINAS ================= --}}
    <section class="w-full pt-24 md:pt-28 pb-space-3xl">
      <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
        <div
          class="bg-surface-container-low rounded-xl p-space-xl md:p-space-2xl grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-center"
        >
          <div class="lg:col-span-4 flex flex-col items-center text-center">
            <div class="relative w-64 h-80 rounded-xl overflow-hidden shadow-md bg-surface-container-high">
              <img
                class="w-full h-full object-cover"
                src="{{ asset('assets/img/kepala-dinas/kepala-dinas.jpg') }}"
                alt="Foto Kepala Dinas"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-primary/80 via-transparent to-transparent"></div>
              <div class="absolute bottom-3 inset-x-3 text-on-primary">
                <span class="font-label-sm text-label-sm block uppercase tracking-wide opacity-90">
                  Kepala Dinas Sosial
                </span>
                <span class="font-label-lg text-label-lg font-bold">Atep Budiman, S.STP., M.M.</span>
              </div>
            </div>
          </div>

          <div class="lg:col-span-8 space-y-space-md">
            <div class="space-y-space-xs">
              <h2 class="font-headline-lg text-headline-lg text-primary font-bold">
                Sambutan Resmi Kepala Dinas
              </h2>
            </div>

            <p class="font-body-md text-body-md text-on-surface font-medium italic">
              "Assalamualaikum Warahmatullahi Wabarakatuh."
            </p>

            <div id="sambutan-body" class="sambutan-body sambutan-collapsed space-y-space-md">
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Puji syukur kita panjatkan kehadirat Allah Yang Maha Esa, atas rahmat dan hidayah-Nya
                saat ini kami telah menghadirkan website resmi milik Pemerintah Kota Bogor dengan
                alamat website pelayanansosial.kotabogor.go.id dimana dengan diresmikannya Website
                Pelayanan Dinas Sosial secara otomatis Kota Bogor telah ikut hadir ke tengah dunia
                internasional melalui pemanfaatan sarana teknologi informatika.
              </p>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Aplikasi ini berisi mengenai pelayanan Jaminan Kesehatan yang dianggarkan oleh
                Pemerintah Kota Bogor dan berita informasi Data Center tentang Fase Kemiskinan di Kota
                Bogor, sehingga dengan adanya website ini, Kota Bogor akan terpublikasi ke tengah
                masyarakat serta dunia internasional.
              </p>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Selain sebagai promosi daerah kepada pihak luar, melalui website ini masyarakat juga
                bisa mengakses Pendaftaran BPJS Kehatan PBI secara Online dan mencari informasi
                penerima bantuan sosial dari Pemerintah Pusat maupun Daerah yang dibutuhkan masyarakat.
              </p>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Selanjutnya, hadirnya website ini merupakan salah satu langkah yang ditempuh Pemerintah
                Kota Bogor untuk dijadikan alat ukur dalam mengentaskan Kemiskinan di Kota Bogor.
              </p>
              <p class="font-body-md text-body-md text-on-surface font-medium italic">
                "Wabillahi Taufik Walhhidayah Wassalamualaikum Warahmatulahi Wabarakatu."
              </p>
            </div>

            <div class="pt-space-sm flex flex-wrap items-center gap-space-md">
              <button
                id="sambutan-toggle-btn"
                type="button"
                class="px-space-md py-2.5 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-secondary transition-colors inline-flex items-center gap-space-xs"
              >
                <span id="sambutan-toggle-label">Baca Selengkapnya</span>
                <span class="material-symbols-outlined text-[18px]" id="sambutan-toggle-icon">expand_more</span>
              </button>

              <a
                class="px-space-md py-2.5 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-secondary transition-colors inline-flex items-center gap-space-xs"
                href="https://dinsos.kotabogor.go.id/"
                rel="noreferrer"
                target="_blank"
              >
                <span>Profil Lengkap</span>
                <span class="material-symbols-outlined text-[18px]">open_in_new</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- ================= TRACKING PERMOHONAN ================= --}}
    <section class="w-full py-space-2xl bg-surface-container-low" id="tracking-cepat">
      <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-lg">
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
              <span id="tracking-alert-text">
                Data permohonan tidak ditemukan. Silakan periksa kembali No Register / NIK Anda.
              </span>
            </div>

            <form class="space-y-space-md" id="tracking-form">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
                <div class="space-y-space-xs">
                  <label class="block font-label-sm text-label-sm text-on-surface-variant">
                    No Register / NIK
                  </label>
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
                  <label class="block font-label-sm text-label-sm text-on-surface-variant">Huruf Kode</label>
                  <div class="flex items-center gap-space-sm">
                    <div class="tr-captcha flex-1" id="tracking-captcha-text">7Q2 KX</div>
                    <button
                      class="w-11 h-11 shrink-0 rounded-lg border border-outline-variant flex items-center justify-center text-secondary hover:bg-surface-container-low transition-colors"
                      id="tracking-captcha-refresh"
                      title="Muat ulang kode"
                      type="button"
                    >
                      <span class="material-symbols-outlined text-[20px]">refresh</span>
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
                  <span class="material-symbols-outlined text-[20px]" id="tracking-submit-icon">search</span>
                  <span id="tracking-submit-label">Lacak Berkas</span>
                </button>
                <button
                  class="inline-flex items-center justify-center gap-space-xs px-space-md py-3 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low font-label-md text-label-md transition-colors"
                  id="tracking-reset-btn"
                  type="button"
                >
                  <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                  Batal
                </button>
              </div>
            </form>
          </div>

          <div class="lg:col-span-4 h-full" id="tracking-results-wrapper">
            <div
              id="tracking-results"
              class="h-full rounded-xl border border-outline-variant/30 bg-surface-container-low p-space-lg space-y-space-sm"
            >
              <div class="flex items-center justify-between gap-space-sm">
                <h3 class="font-headline-sm text-headline-sm text-primary font-bold">Status Berkas</h3>
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

    {{-- ================= PETA SEBARAN ================= --}}
    <section class="w-full py-space-2xl" id="peta-sebaran">
      <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-lg">
        <div class="max-w-2xl space-y-space-xs">
          <h2 class="font-headline-lg text-headline-lg text-primary font-bold">
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
              <span class="material-symbols-outlined text-primary text-[22px]">map</span>
              <span class="font-label-lg text-label-lg text-primary font-bold">SOLID Peta</span>
            </div>

            <div class="space-y-1">
              <label class="block font-label-sm text-label-sm text-on-surface-variant">Kecamatan</label>
              <select
                id="peta-kecamatan"
                class="w-full py-2 px-space-sm rounded-lg bg-surface-container-lowest border border-outline-variant/40 text-on-surface font-label-md text-label-md focus:outline-none focus:ring-2 focus:ring-secondary"
              >
                <option value="">-Pilih-</option>
              </select>
            </div>

            <div class="space-y-1">
              <label class="block font-label-sm text-label-sm text-on-surface-variant">Desa/Kelurahan</label>
              <select
                id="peta-kelurahan"
                class="w-full py-2 px-space-sm rounded-lg bg-surface-container-lowest border border-outline-variant/40 text-on-surface font-label-md text-label-md focus:outline-none focus:ring-2 focus:ring-secondary"
                disabled
              >
                <option value="">-Pilih-</option>
              </select>
            </div>

            <div class="space-y-1">
              <label class="block font-label-sm text-label-sm text-on-surface-variant">Tahun</label>
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

            <p class="font-body-sm text-body-sm text-on-surface-variant/70 italic">
              *Data pada peta ini adalah data ilustrasi untuk kebutuhan tampilan.
            </p>
          </div>

          <div class="lg:col-span-9 grid grid-cols-1 xl:grid-cols-12">
            <div class="xl:col-span-8 relative isolate">
              <div
                id="peta-map"
                class="w-full h-[340px] md:h-[420px] xl:h-[480px] bg-surface-container relative overflow-hidden"
              >
                <div id="peta-leaflet" class="w-full h-full"></div>
              </div>

              <div
                class="absolute top-4 left-4 z-20 bg-surface-container-lowest/95 backdrop-blur-md px-space-sm py-2 rounded-lg shadow-sm flex items-center gap-space-xs"
              >
                <span class="w-3 h-3 rounded-full bg-secondary animate-ping"></span>
                <span class="font-label-sm text-label-sm text-primary font-bold">
                  Pusat Data Dinsos Kota Bogor
                </span>
              </div>

              <div
                class="absolute bottom-4 right-4 z-20 bg-surface-container-lowest/95 backdrop-blur-md px-space-sm py-space-xs rounded-lg shadow-sm space-y-1"
              >
                <span class="block font-label-sm text-label-sm text-on-surface font-bold mb-1">Legend</span>
                <span class="flex items-center gap-space-2xs font-body-sm text-body-sm text-on-surface-variant">
                  <span class="w-3 h-3 rounded-sm" style="background: #4caf50"></span>Sedikit
                </span>
                <span class="flex items-center gap-space-2xs font-body-sm text-body-sm text-on-surface-variant">
                  <span class="w-3 h-3 rounded-sm" style="background: #ffca28"></span>Sedang
                </span>
                <span class="flex items-center gap-space-2xs font-body-sm text-body-sm text-on-surface-variant">
                  <span class="w-3 h-3 rounded-sm" style="background: #fb8c00"></span>Banyak
                </span>
                <span class="flex items-center gap-space-2xs font-body-sm text-body-sm text-on-surface-variant">
                  <span class="w-3 h-3 rounded-sm" style="background: #e53935"></span>Sangat Banyak
                </span>
              </div>
            </div>

            <div
              class="xl:col-span-4 bg-surface-container p-space-md flex flex-col justify-between space-y-space-sm xl:h-[480px]"
            >
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="font-label-sm text-label-sm text-secondary font-bold uppercase">Informasi</span>
                  <span
                    id="peta-info-tahun"
                    class="px-space-xs py-0.5 rounded bg-surface-container-lowest font-label-sm text-label-sm text-on-surface font-semibold"
                    >Tahun 2026</span
                  >
                </div>

                <h3 id="peta-info-wilayah" class="font-headline-sm text-headline-sm text-primary font-bold">
                  Semua Wilayah Kota Bogor
                </h3>

                <div class="space-y-1 font-body-sm text-body-sm text-on-surface-variant">
                  <div class="flex items-center justify-between">
                    <span>Kab/Kota</span>
                    <span id="peta-info-kabkota" class="font-semibold text-on-surface">KOTA BOGOR</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span>Kecamatan</span>
                    <span id="peta-info-kecamatan-txt" class="font-semibold text-on-surface">–</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span>Kelurahan/Desa</span>
                    <span id="peta-info-kelurahan-txt" class="font-semibold text-on-surface">–</span>
                  </div>
                </div>

                <div class="space-y-1 pt-1.5 border-t border-outline-variant/30">
                  <span class="block font-label-sm text-label-sm text-secondary font-bold uppercase">
                    Jumlah Penduduk
                  </span>
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
                  <span class="block font-label-sm text-label-sm text-secondary font-bold uppercase">
                    Jumlah Penerima Bantuan
                  </span>
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

    {{-- ================= BERITA ================= --}}
    <section class="w-full py-space-3xl bg-surface-container-low" id="berita">
      <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-2xl">
        <div class="max-w-2xl space-y-space-xs">
          <h2 class="font-headline-lg text-headline-lg text-primary font-bold">Berita Terbaru</h2>
        </div>

        <div class="space-y-space-lg">
          <div class="flex items-center justify-between">
            <div class="space-y-space-2xs">
              <h3 class="font-headline-md text-headline-md text-primary font-bold">
                Berita Dinas Sosial Kota Bogor
              </h3>
              <span class="block w-10 h-1 rounded-full bg-secondary"></span>
            </div>
            <a
              class="text-secondary font-label-md text-label-md font-bold hover:underline inline-flex items-center gap-space-2xs"
              href="https://pelayanansosial.kotabogor.go.id/index.php/portal/berita"
            >
              <span>Lihat Semua</span>
              <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-lg">
            <a class="berita-card group" href="#">
              <div class="berita-card-img">
                <img src="{{ asset('assets/img/berita/berita-1.jpg') }}" alt="Penandatanganan Pakta Integritas" />
                <span class="berita-card-date">26 Januari 2026</span>
              </div>
              <div class="berita-card-body">
                <h4 class="berita-card-title">
                  Pada Hari Senin Seluruh Pegawai Dinas Sosial Melaksanakan Penandatanganan Pakta
                  Integritas Tahun 2026 untuk Menjaga Komitmen Bersama Agar Menjadi Lebik Baik dan Optimal
                </h4>
              </div>
            </a>

            <a class="berita-card group" href="#">
              <div class="berita-card-img">
                <img src="{{ asset('assets/img/berita/berita-2.jpg') }}" alt="SP4N-LAPOR!" />
                <span class="berita-card-date">10 Juni 2025</span>
              </div>
              <div class="berita-card-body">
                <h4 class="berita-card-title">
                  SP4N-LAPOR! adalah Sistem Pengelolaan Pengaduan Pelayanan Publik Nasional – Layanan
                  Aspirasi dan Pengaduan Online Rakyat, sebuah platform terintegrasi yang memungkinkan
                  masyarakat menyampaikan aspirasi dan pengaduan terkait layanan publik pemerintah
                  secara online. Platform ini dikelola oleh Kementerian PANRB, Kantor Staf Presiden
                  (KSP), dan Ombudsman RI
                </h4>
              </div>
            </a>

            <a class="berita-card group" href="#">
              <div class="berita-card-img">
                <img src="{{ asset('assets/img/berita/berita-3.png') }}" alt="Budaya BerAKHLAK" />
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
                <img src="{{ asset('assets/img/berita/berita-4.png') }}" alt="Layanan aduan SIBADRA" />
                <span class="berita-card-date">15 Oktober 2025</span>
              </div>
              <div class="berita-card-body">
                <h4 class="berita-card-title">
                  hallo sobat sosial berikut layanan aduan pemerintah kota bogor bisa di akses melalui
                  aplikasi SIBADRA terkait permasalahan sosial
                </h4>
              </div>
            </a>
          </div>
        </div>

        <div class="space-y-space-lg">
          <div class="flex items-center justify-between">
            <div class="space-y-space-2xs">
              <h3 class="font-headline-md text-headline-md text-primary font-bold">Berita Kota Bogor</h3>
              <span class="block w-10 h-1 rounded-full bg-secondary"></span>
            </div>
            <a
              class="text-secondary font-label-md text-label-md font-bold hover:underline inline-flex items-center gap-space-2xs"
              href="#"
            >
              <span>Lihat Semua</span>
              <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-lg">
            <a class="berita-card group" href="#">
              <div class="berita-card-img">
                <img src="{{ asset('assets/img/berita/berita-5.jpg') }}" alt="MoU Pemkot Bogor dan PT KAI" />
                <span class="berita-card-date">07 September 2026</span>
              </div>
              <div class="berita-card-body">
                <h4 class="berita-card-title">
                  Pemkot Bogor dan PT KAI Teken MoU Penataan dan Pengembangan Kawasan Stasiun Bogor
                </h4>
              </div>
            </a>

            <a class="berita-card group" href="#">
              <div class="berita-card-img">
                <img src="{{ asset('assets/img/berita/berita-6.jpg') }}" alt="Water mist abu vulkanik" />
                <span class="berita-card-date">07 September 2026</span>
              </div>
              <div class="berita-card-body">
                <h4 class="berita-card-title">
                  Water Mist Terus Dilakukan, Pemkot Bogor Minimalisir Sebaran Abu Vulkanik
                </h4>
              </div>
            </a>

            <a class="berita-card group" href="#">
              <div class="berita-card-img">
                <img src="{{ asset('assets/img/berita/berita-7.jpg') }}" alt="Ekonomi sirkular" />
                <span class="berita-card-date">07 September 2026</span>
              </div>
              <div class="berita-card-body">
                <h4 class="berita-card-title">
                  Pemanfaatan Sampah Plastik dan Minyak Jelantah, Pemkot Bogor Perkuat Ekonomi Sirkular
                </h4>
              </div>
            </a>

            <a class="berita-card group" href="#">
              <div class="berita-card-img">
                <img src="{{ asset('assets/img/berita/berita-8.jpg') }}" alt="Semprot jalan protokol" />
                <span class="berita-card-date">07 September 2026</span>
              </div>
              <div class="berita-card-body">
                <h4 class="berita-card-title">
                  Tekan Dampak Abu Vulkanik, Pemkot Bogor Semprot Jalan Protokol dengan Water Mist
                </h4>
              </div>
            </a>
          </div>
        </div>
      </div>
    </section>

    {{-- ================= KOLABORASI & INTEGRASI ================= --}}
    <section class="w-full py-space-2xl bg-surface-container-lowest">
      <div
        class="max-w-[1450px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-lg text-center"
      >
        <div class="flex items-center justify-center gap-space-xs">
          <span class="material-symbols-outlined text-primary text-[28px]">handshake</span>
          <h2 class="font-headline-lg text-headline-lg text-primary font-bold">Kolaborasi &amp; Integrasi</h2>
        </div>

        <div class="mitra-carousel" id="mitra-carousel">
          <div class="mitra-track" id="mitra-track">
            <div class="mitra-slide">
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/dinkes.png') }}" alt="Dinas Kesehatan" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/disdukcapil.png') }}" alt="Disdukcapil" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/diskominfo.png') }}" alt="Diskominfo" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/dinsos.png') }}" alt="Dinas Sosial" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/disdik.png') }}" alt="Dinas Pendidikan" /></div>
            </div>

            <div class="mitra-slide">
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/bpjs.png') }}" alt="BPJS" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/kemendagri-2.png') }}" alt="Kemendagri" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/kemensos.png') }}" alt="Kemensos" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/kemdikbud.png') }}" alt="Kemdikbud" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/kotabogor-rusa.png') }}" alt="Kota Bogor" /></div>
            </div>

            <div class="mitra-slide">
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/bogor-berlari.jpeg') }}" alt="Bogor Berlari" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/bkad-bogor.png') }}" alt="BKAD Bogor" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/tagana.png') }}" alt="Tagana" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/bni46.png') }}" alt="BNI" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/logo-bsre.png') }}" alt="BSRE" /></div>
            </div>

            <div class="mitra-slide">
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/pkh-logo.png') }}" alt="PKH" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/rz.jpg') }}" alt="Rumah Zakat" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/baznas.png') }}" alt="BAZNAS Jawa Barat" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/dhuafa.png') }}" alt="Dompet Dhuafa" /></div>
              <div class="mitra-logo"><img src="{{ asset('assets/img/mitra/salamaid.png') }}" alt="SalamAid" /></div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-center gap-space-2xs pt-space-xs" id="mitra-dots">
          <button class="mitra-dot is-active" data-slide-index="0" type="button" aria-label="Slide mitra 1"></button>
          <button class="mitra-dot" data-slide-index="1" type="button" aria-label="Slide mitra 2"></button>
          <button class="mitra-dot" data-slide-index="2" type="button" aria-label="Slide mitra 3"></button>
          <button class="mitra-dot" data-slide-index="3" type="button" aria-label="Slide mitra 4"></button>
        </div>
      </div>
    </section>

  </div>
@endsection
