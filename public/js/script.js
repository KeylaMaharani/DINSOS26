/* =========================================================
   script.js
   Dinas Sosial Kota Bogor - SOLID v3
   REVISI: alert & hasil tracking dipaksa hidden saat pertama load
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {
  /* --- Header: transparan di hero, solid saat discroll --- */
  var siteHeader = document.querySelector("header");
  if (siteHeader) {
    var HEADER_SCROLL_THRESHOLD = 40;
    var toggleHeaderScrolled = function () {
      if (window.scrollY > HEADER_SCROLL_THRESHOLD) {
        siteHeader.classList.add("header-scrolled");
      } else {
        siteHeader.classList.remove("header-scrolled");
      }
    };
    window.addEventListener("scroll", toggleHeaderScrolled, { passive: true });
    toggleHeaderScrolled();
  }

  /* --- Hero Photo Carousel (3 slide, teks singkat) --- */
  var heroSection = document.getElementById("hero-carousel");

  if (heroSection) {
    var slideBgs = Array.prototype.slice.call(
      heroSection.querySelectorAll(".hero-slide-bg"),
    );
    var indicators = Array.prototype.slice.call(
      heroSection.querySelectorAll(".hero-indicator"),
    );
    var subtitleEl = document.getElementById("hero-subtitle");
    var descEl = document.getElementById("hero-desc");

    var slideContent = [
      {
        subtitle: "Peresmian Rumah Layak Huni",
        desc: "Komunitas Perempuan Peduli Aksi Sosial (KPPAS) Indonesia dalam agenda Peresmian Rumah Layak Huni.",
      },
      {
        subtitle: "Audiensi Pokmas",
        desc: "Kendala kendala permakanan lansia selama tahun 2025 untuk perbaikan di tahun 2026.",
      },
      {
        subtitle: "TAWADHU (Tangani Warga Dhuafa)",
        desc: "Bidang PFM dan Jaminan Sosial Melaksanakan Kegiatan TAWADHU (Tangani Warga Dhuafa) di Kelurahan Tanah Baru.",
      },
    ];

    var currentSlide = 0;
    var totalSlides = slideBgs.length;
    var autoplayTimer = null;
    var AUTOPLAY_DELAY = 5000;

    function renderSlide(index) {
      slideBgs.forEach(function (bg, i) {
        if (i === index) {
          bg.classList.remove("opacity-0");
          bg.classList.add("opacity-100");
        } else {
          bg.classList.remove("opacity-100");
          bg.classList.add("opacity-0");
        }
      });
      indicators.forEach(function (btn) {
        var idx = parseInt(btn.getAttribute("data-slide-index"), 10);
        btn.classList.toggle("is-active", idx === index);
      });
      if (subtitleEl && descEl && slideContent[index]) {
        subtitleEl.style.opacity = 0;
        descEl.style.opacity = 0;
        window.setTimeout(function () {
          subtitleEl.textContent = slideContent[index].subtitle;
          descEl.textContent = slideContent[index].desc;
          subtitleEl.style.opacity = 1;
          descEl.style.opacity = 1;
        }, 250);
      }
    }

    function goToSlide(index) {
      currentSlide = (index + totalSlides) % totalSlides;
      renderSlide(currentSlide);
      resetAutoplay();
    }

    function startAutoplay() {
      stopAutoplay();
      autoplayTimer = window.setInterval(function () {
        currentSlide = (currentSlide + 1) % totalSlides;
        renderSlide(currentSlide);
      }, AUTOPLAY_DELAY);
    }

    function stopAutoplay() {
      if (autoplayTimer) {
        window.clearInterval(autoplayTimer);
        autoplayTimer = null;
      }
    }

    function resetAutoplay() {
      stopAutoplay();
      startAutoplay();
    }

    indicators.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var idx = parseInt(btn.getAttribute("data-slide-index"), 10);
        goToSlide(idx);
      });
    });

    heroSection.addEventListener("mouseenter", stopAutoplay);
    heroSection.addEventListener("mouseleave", startAutoplay);
    document.addEventListener("visibilitychange", function () {
      if (document.hidden) stopAutoplay();
      else startAutoplay();
    });

    renderSlide(currentSlide);
    startAutoplay();
  }

  /* --- Navbar Search Expand (desktop) --- */
  var navSearch = document.getElementById("nav-search");
  var navSearchToggle = document.getElementById("nav-search-toggle");
  var navSearchInput = document.getElementById("nav-search-input");
  if (navSearch && navSearchToggle && navSearchInput) {
    navSearchToggle.addEventListener("click", function (event) {
      event.stopPropagation();
      var isOpen = navSearch.classList.toggle("open");
      if (isOpen) {
        window.setTimeout(function () {
          navSearchInput.focus();
        }, 150);
      } else {
        navSearchInput.value = "";
      }
    });
    document.addEventListener("click", function (event) {
      if (!navSearch.contains(event.target)) {
        navSearch.classList.remove("open");
      }
    });
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") navSearch.classList.remove("open");
    });
  }

  /* --- Kolaborasi & Integrasi: carousel logo mitra --- */
  var mitraTrack = document.getElementById("mitra-track");
  var mitraDots = document.querySelectorAll("#mitra-dots .mitra-dot");

  if (mitraTrack && mitraDots.length) {
    var mitraSlides = mitraTrack.querySelectorAll(".mitra-slide");
    var mitraTotal = mitraSlides.length;
    var mitraCurrent = 0;
    var mitraTimer = null;
    var MITRA_DELAY = 4000;

    var mitraLogos = mitraTrack.querySelectorAll(".mitra-logo img");

    mitraLogos.forEach(function (logo) {
      logo.setAttribute("role", "button");
      logo.setAttribute("tabindex", "0");

      var activateLogo = function () {
        logo.classList.add("active");
      };

      logo.addEventListener("click", activateLogo);

      logo.addEventListener("keydown", function (event) {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          activateLogo();
        }
      });
    });

    function renderMitraSlide(index) {
      mitraTrack.style.transform = "translateX(-" + index * 100 + "%)";

      mitraDots.forEach(function (dot) {
        var idx = parseInt(dot.getAttribute("data-slide-index"), 10);
        dot.classList.toggle("is-active", idx === index);
      });
    }

    function goToMitraSlide(index) {
      mitraCurrent = (index + mitraTotal) % mitraTotal;
      renderMitraSlide(mitraCurrent);
      resetMitraAutoplay();
    }

    function startMitraAutoplay() {
      stopMitraAutoplay();

      mitraTimer = window.setInterval(function () {
        mitraCurrent = (mitraCurrent + 1) % mitraTotal;
        renderMitraSlide(mitraCurrent);
      }, MITRA_DELAY);
    }

    function stopMitraAutoplay() {
      if (mitraTimer) {
        window.clearInterval(mitraTimer);
        mitraTimer = null;
      }
    }

    function resetMitraAutoplay() {
      stopMitraAutoplay();
      startMitraAutoplay();
    }

    mitraDots.forEach(function (dot) {
      dot.addEventListener("click", function () {
        var idx = parseInt(dot.getAttribute("data-slide-index"), 10);
        goToMitraSlide(idx);
      });
    });

    var mitraCarousel = document.getElementById("mitra-carousel");

    if (mitraCarousel) {
      mitraCarousel.addEventListener("mouseenter", stopMitraAutoplay);
      mitraCarousel.addEventListener("mouseleave", startMitraAutoplay);
    }

    renderMitraSlide(mitraCurrent);
    startMitraAutoplay();
  }

  /* --- Floating Social Media Bar --- */
  var socialFab = document.getElementById("social-fab");
  var socialFabToggle = document.getElementById("social-fab-toggle");
  if (socialFab && socialFabToggle) {
    socialFabToggle.addEventListener("click", function () {
      socialFab.classList.toggle("closed");
    });
  }

  /* --- Floating accessibility button --- */
  var a11yFab = document.getElementById("a11y-fab");
  var a11yToggleBtn = document.getElementById("a11y-fab-toggle");
  var a11yCloseBtn = document.getElementById("a11y-fab-close");
  if (a11yFab && a11yToggleBtn) {
    a11yToggleBtn.addEventListener("click", function () {
      a11yFab.classList.toggle("open");
    });
  }
  if (a11yFab && a11yCloseBtn) {
    a11yCloseBtn.addEventListener("click", function () {
      a11yFab.classList.remove("open");
    });
  }
  document.addEventListener("click", function (event) {
    if (!a11yFab) return;
    if (!a11yFab.contains(event.target)) a11yFab.classList.remove("open");
  });
  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && a11yFab) a11yFab.classList.remove("open");
  });

  /* --- Back to top button --- */
  var backToTopBtn = document.getElementById("back-to-top");
  if (backToTopBtn) {
    var toggleBackToTopVisibility = function () {
      if (window.scrollY > 400) backToTopBtn.classList.add("visible");
      else backToTopBtn.classList.remove("visible");
    };
    backToTopBtn.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
    window.addEventListener("scroll", toggleBackToTopVisibility, {
      passive: true,
    });
    toggleBackToTopVisibility();
  }

  /* --- Sambutan Kepala Dinas: toggle Baca Selengkapnya / Tampilkan Sedikit --- */
  var sambutanBody = document.getElementById("sambutan-body");
  var sambutanToggleBtn = document.getElementById("sambutan-toggle-btn");
  var sambutanToggleLabel = document.getElementById("sambutan-toggle-label");
  var sambutanToggleIcon = document.getElementById("sambutan-toggle-icon");

  if (sambutanBody && sambutanToggleBtn) {
    sambutanToggleBtn.addEventListener("click", function () {
      var isExpanded = sambutanBody.classList.contains("sambutan-expanded");
      if (isExpanded) {
        sambutanBody.classList.remove("sambutan-expanded");
        sambutanBody.classList.add("sambutan-collapsed");
        sambutanToggleLabel.textContent = "Baca Selengkapnya";
        sambutanToggleIcon.textContent = "expand_more";
        sambutanBody.scrollIntoView({ behavior: "smooth", block: "nearest" });
      } else {
        sambutanBody.classList.remove("sambutan-collapsed");
        sambutanBody.classList.add("sambutan-expanded");
        sambutanToggleLabel.textContent = "Tampilkan Sedikit";
        sambutanToggleIcon.textContent = "expand_less";
      }
    });
  }

  /* =========================================================
     PENAMBAHAN UNTUK TAMPILAN MOBILE (aman dibuka di HP)
     ========================================================= */

  /* --- A. Hamburger menu: buka/tutup panel navigasi mobile --- */
  var mobileMenuToggle = document.getElementById("mobile-menu-toggle");
  var mobileNavPanel = document.getElementById("mobile-nav-panel");
  var mobileNavClose = document.getElementById("mobile-nav-close");

  function openMobileNav() {
    if (!mobileNavPanel) return;
    mobileNavPanel.classList.add("open");
    document.body.classList.add("mobile-nav-locked");
    if (mobileMenuToggle)
      mobileMenuToggle.setAttribute("aria-expanded", "true");
  }
  function closeMobileNav() {
    if (!mobileNavPanel) return;
    mobileNavPanel.classList.remove("open");
    document.body.classList.remove("mobile-nav-locked");
    if (mobileMenuToggle)
      mobileMenuToggle.setAttribute("aria-expanded", "false");
  }

  if (mobileMenuToggle && mobileNavPanel) {
    mobileMenuToggle.addEventListener("click", openMobileNav);
  }
  if (mobileNavClose) {
    mobileNavClose.addEventListener("click", closeMobileNav);
  }
  if (mobileNavPanel) {
    mobileNavPanel.addEventListener("click", function (e) {
      if (e.target === mobileNavPanel) closeMobileNav();
    });
  }
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeMobileNav();
  });
  window.addEventListener("resize", function () {
    if (window.innerWidth >= 1280) closeMobileNav();
  });

  /* Tutup panel mobile setiap kali link biasa (bukan accordion) diklik */
  if (mobileNavPanel) {
    var mobilePlainLinks = mobileNavPanel.querySelectorAll(
      ".mobile-nav-link, .mobile-nav-submenu a, .mobile-nav-subgroup a",
    );
    mobilePlainLinks.forEach(function (link) {
      link.addEventListener("click", function () {
        closeMobileNav();
      });
    });
  }

  /* --- B. Accordion: grup menu (Pelayanan / Informasi) --- */
  if (mobileNavPanel) {
    var groupToggles = mobileNavPanel.querySelectorAll(
      ".mobile-nav-group-toggle",
    );
    groupToggles.forEach(function (toggle) {
      toggle.addEventListener("click", function () {
        var group = toggle.closest(".mobile-nav-group");
        if (!group) return;
        var isOpen = group.classList.toggle("open");
        toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
      });
    });

    /* Sub-accordion: Cek Bansos di dalam Informasi */
    var subgroupToggles = mobileNavPanel.querySelectorAll(
      ".mobile-nav-subgroup-toggle",
    );
    subgroupToggles.forEach(function (toggle) {
      toggle.addEventListener("click", function (e) {
        e.stopPropagation();
        var wrap = toggle.closest(".mobile-nav-subgroup-wrap");
        var subgroup = wrap ? wrap.querySelector(".mobile-nav-subgroup") : null;
        if (!subgroup) return;
        var isOpen = subgroup.classList.toggle("open");
        toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
      });
    });
  }

  /* --- C. Sinkronkan pencarian mobile dengan pencarian desktop --- */
  var mobileNavSearchInput = document.getElementById("mobile-nav-search-input");
  if (mobileNavSearchInput && navSearchInput) {
    mobileNavSearchInput.addEventListener("input", function () {
      navSearchInput.value = mobileNavSearchInput.value;
    });
  }

  /* =========================================================
   AKSES CEPAT LAYANAN
   6 ICON DI DALAM CAROUSEL
   ========================================================= */

  var fabRadial = document.getElementById("fab-radial");

  if (fabRadial) {
    var isTouchDevice =
      "ontouchstart" in window ||
      (navigator.maxTouchPoints && navigator.maxTouchPoints > 0) ||
      (window.matchMedia && window.matchMedia("(hover: none)").matches) ||
      (window.matchMedia && window.matchMedia("(pointer: coarse)").matches);

    /* =========================================
     KHUSUS TOUCH DEVICE / HP
     ========================================= */

    if (isTouchDevice) {
      var fabItems = fabRadial.querySelectorAll(".fab-radial-item");

      fabItems.forEach(function (item) {
        item.addEventListener("click", function (e) {
          /* Kalau label belum terbuka */
          if (!item.classList.contains("show-label")) {
            /* Jangan langsung pindah link */
            e.preventDefault();

            /* Tutup label icon lain */
            fabRadial
              .querySelectorAll(".fab-radial-item.show-label")
              .forEach(function (el) {
                if (el !== item) {
                  el.classList.remove("show-label");
                }
              });

            /* Buka label icon yang dipilih */
            item.classList.add("show-label");
          }
        });
      });

      /* =========================================
       TAP DI LUAR ICON
       TUTUP SEMUA LABEL
       ========================================= */

      document.addEventListener("click", function (e) {
        if (!fabRadial.contains(e.target)) {
          fabRadial
            .querySelectorAll(".fab-radial-item.show-label")
            .forEach(function (item) {
              item.classList.remove("show-label");
            });
        }
      });
    }
  }

  /* ============================================================
     TRACKING PERMOHONAN BPJS PBI APBD
     - Alert error & panel hasil DIPAKSA hidden saat pertama load
     - Alert & hasil hanya muncul setelah user klik "Lacak Berkas"
     - Data status di sini masih dummy untuk kebutuhan tampilan.
     ============================================================ */
  (function () {
    var codes = ["7Q2 KX", "M9F 3T", "B5N QW", "2XZ K7", "R3Q 8M"];
    var captchaEl = document.getElementById("tracking-captcha-text");
    var captchaInput = document.getElementById("tracking-captcha-input");
    var trackingInput = document.getElementById("tracking-input");
    var alertBox = document.getElementById("tracking-alert");
    var alertText = document.getElementById("tracking-alert-text");
    var resultsPanel = document.getElementById("tracking-results");
    var tracking3col = document.getElementById("tracking-3col");
    var form = document.getElementById("tracking-form");
    var submitBtn = document.getElementById("tracking-submit-btn");
    var submitLabel = document.getElementById("tracking-submit-label");
    var submitIcon = document.getElementById("tracking-submit-icon");
    if (!form || !captchaEl) return;

    /* --- PERBAIKAN UTAMA ---
       Paksa alert & panel hasil tersembunyi begitu halaman dimuat,
       apapun kondisi state sebelumnya (cache, dsb). Baru tampil
       kalau user submit form. */
    function resetTrackingUI() {
      alertBox.style.display = "none";
      resultsPanel.style.display = "none";
      if (tracking3col) tracking3col.classList.remove("has-result");
    }
    resetTrackingUI();

    function randomCode() {
      return codes[Math.floor(Math.random() * codes.length)];
    }

    var refreshBtn = document.getElementById("tracking-captcha-refresh");
    if (refreshBtn) {
      refreshBtn.addEventListener("click", function () {
        captchaEl.textContent = randomCode();
        captchaInput.value = "";
      });
    }

    function showAlert(message) {
      alertText.textContent = message;
      alertBox.style.display = "flex";
      resultsPanel.style.display = "none";
      if (tracking3col) tracking3col.classList.remove("has-result");
    }

    function hideAlert() {
      alertBox.style.display = "none";
    }

    /* Status proses yang mungkin muncul, dipilih berdasarkan
       input pemohon supaya hasilnya konsisten untuk input yang
       sama (bukan acak setiap kali dicari). */
    var statusOptions = [
      {
        key: "diterima",
        label: "Diterima",
        badge: "tr-badge-diterima",
        keterangan: "Berkas telah diterima dan menunggu verifikasi petugas.",
      },
      {
        key: "diverifikasi",
        label: "Diverifikasi",
        badge: "tr-badge-diverifikasi",
        keterangan: "Berkas sedang dalam proses verifikasi data kependudukan.",
      },
      {
        key: "ditolak",
        label: "Ditolak",
        badge: "tr-badge-ditolak",
        keterangan:
          "Berkas ditolak karena data tidak sesuai. Silakan hubungi kantor Dinsos.",
      },
      {
        key: "selesai",
        label: "Selesai",
        badge: "tr-badge-selesai",
        keterangan:
          "Pendaftaran selesai diproses. Kartu BPJS PBI APBD dapat digunakan.",
      },
    ];

    function seedNumber(str) {
      var hash = 0;
      for (var i = 0; i < str.length; i++) {
        hash = (hash * 31 + str.charCodeAt(i)) >>> 0;
      }
      return hash;
    }

    function formatTanggalDummy(seed) {
      var tanggal = (seed % 27) + 1;
      var bulanList = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
      ];
      var bulan = bulanList[seed % 12];
      return tanggal + " " + bulan + " 2026";
    }

    function showResults(noRegister) {
      var seed = seedNumber(noRegister);
      var status = statusOptions[seed % statusOptions.length];

      document.getElementById("tracking-no-register").textContent = noRegister;
      document.getElementById("tracking-nama").textContent = "BUDI SANTOSO";
      document.getElementById("tracking-tanggal").textContent =
        formatTanggalDummy(seed);
      document.getElementById("tracking-keterangan").textContent =
        status.keterangan;

      var statusEl = document.getElementById("tracking-status");
      statusEl.textContent = status.label;
      statusEl.className = "tr-badge " + status.badge;

      resultsPanel.style.display = "block";
      if (tracking3col) tracking3col.classList.add("has-result");
      resultsPanel.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }

    var resetBtn = document.getElementById("tracking-reset-btn");
    if (resetBtn) {
      resetBtn.addEventListener("click", function () {
        trackingInput.value = "";
        captchaInput.value = "";
        captchaEl.textContent = randomCode();
        resetTrackingUI();
      });
    }

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      hideAlert();
      resultsPanel.style.display = "none";
      if (tracking3col) tracking3col.classList.remove("has-result");

      // Tidak lagi wajib isi No Register/NIK atau captcha.
      var noRegister = trackingInput.value.trim() || "-";

      submitLabel.textContent = "Mencari...";
      submitIcon.textContent = "progress_activity";
      submitBtn.disabled = true;

      setTimeout(function () {
        submitLabel.textContent = "Lacak Berkas";
        submitIcon.textContent = "search";
        submitBtn.disabled = false;
        showResults(noRegister);
      }, 700);
    });
  })();

  /* ============================================================
     Peta Penerima Bantuan Sosial — inisialisasi Leaflet + filter
     Kecamatan/Kelurahan/Tahun. Data wilayah (lat/lng pusat) masih
     dummy untuk kebutuhan tampilan. Batas wilayah (garis putus-putus
     mengikuti bentuk asli kelurahan/kecamatan) diambil langsung dari
     Nominatim (OpenStreetMap) secara real-time; kalau tidak ketemu,
     otomatis fallback ke marker titik seperti sebelumnya.
     ============================================================ */
  (function () {
    var mapEl = document.getElementById("peta-leaflet");
    if (!mapEl || typeof L === "undefined") return;

    /* --- Data dummy wilayah Kota Bogor --- */
    var wilayahData = {
      "Bogor Selatan": {
        lat: -6.6383,
        lng: 106.7972,
        kelurahan: [
          "Batutulis",
          "Bondongan",
          "Cikaret",
          "Cipaku",
          "Empang",
          "Genteng",
          "Harjasari",
          "Mulyaharja",
          "Pamoyanan",
          "Ranggamekar",
          "Rancamaya",
          "Lawanggintung",
          "Cibuluh",
        ],
      },
      "Bogor Timur": {
        lat: -6.6009,
        lng: 106.8148,
        kelurahan: [
          "Baranangsiang",
          "Katulampa",
          "Sindangrasa",
          "Sindangsari",
          "Sukasari",
          "Tajur",
        ],
      },
      "Bogor Utara": {
        lat: -6.5744,
        lng: 106.797,
        kelurahan: [
          "Bantarjati",
          "Cibuluh",
          "Cimahpar",
          "Ciparigi",
          "Kedunghalang",
          "Tanah Baru",
          "Tegal Gundil",
        ],
      },
      "Bogor Tengah": {
        lat: -6.5971,
        lng: 106.7967,
        kelurahan: [
          "Babakan",
          "Babakan Pasar",
          "Cibogor",
          "Ciwaringin",
          "Gudang",
          "Kebon Kelapa",
          "Panaragan",
          "Paledang",
          "Pabaton",
          "Sempur",
          "Tegallega",
        ],
      },
      "Bogor Barat": {
        lat: -6.5834,
        lng: 106.7638,
        kelurahan: [
          "Balumbang Jaya",
          "Bubulak",
          "Cilendek Barat",
          "Cilendek Timur",
          "Curug",
          "Curug Mekar",
          "Gunung Batu",
          "Loji",
          "Margajaya",
          "Menteng",
          "Pasir Jaya",
          "Pasir Kuda",
          "Pasir Mulya",
          "Semplak",
          "Sindang Barang",
        ],
      },
      "Tanah Sareal": {
        lat: -6.5583,
        lng: 106.8058,
        kelurahan: [
          "Cibadak",
          "Kebon Pedes",
          "Kayumanis",
          "Kedung Jaya",
          "Kedung Waringin",
          "Mekarwangi",
          "Sukadamai",
          "Sukaresmi",
          "Tanah Sareal",
          "Cibadak",
        ],
      },
    };

    var tahunList = [2026, 2025, 2024, 2023, 2022];

    var kecamatanSelect = document.getElementById("peta-kecamatan");
    var kelurahanSelect = document.getElementById("peta-kelurahan");
    var tahunSelect = document.getElementById("peta-tahun");
    var btnTampilkan = document.getElementById("peta-tampilkan");
    var btnReset = document.getElementById("peta-reset");

    /* --- Isi dropdown Kecamatan --- */
    Object.keys(wilayahData).forEach(function (nama) {
      var opt = document.createElement("option");
      opt.value = nama;
      opt.textContent = nama;
      kecamatanSelect.appendChild(opt);
    });

    /* --- Isi dropdown Tahun --- */
    tahunList.forEach(function (thn) {
      var opt = document.createElement("option");
      opt.value = thn;
      opt.textContent = thn;
      tahunSelect.appendChild(opt);
    });
    tahunSelect.value = tahunList[0];

    /* --- Isi/ubah dropdown Kelurahan sesuai Kecamatan terpilih --- */
    kecamatanSelect.addEventListener("change", function () {
      var nama = kecamatanSelect.value;
      kelurahanSelect.innerHTML = '<option value="">-Pilih-</option>';
      if (nama && wilayahData[nama]) {
        wilayahData[nama].kelurahan.forEach(function (kel) {
          var opt = document.createElement("option");
          opt.value = kel;
          opt.textContent = kel;
          kelurahanSelect.appendChild(opt);
        });
        kelurahanSelect.disabled = false;
      } else {
        kelurahanSelect.disabled = true;
      }
    });

    /* --- Inisialisasi peta Leaflet, pusat Kota Bogor --- */
    var BOGOR_CENTER = [-6.5971, 106.7967];
    var map = L.map(mapEl, {
      zoomControl: false, // matikan default (topleft), pasang manual di topright
      attributionControl: true,
    }).setView(BOGOR_CENTER, 12);

    // Tombol zoom (+/-) dipindah ke kanan atas supaya tidak
    // ketutupan badge "Pusat Data Dinsos Kota Bogor" di kiri atas.
    L.control.zoom({ position: "topright" }).addTo(map);

    var currentTileLayer = null;
    function setBasemap() {
      if (currentTileLayer) map.removeLayer(currentTileLayer);
      currentTileLayer = L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        { maxZoom: 19, attribution: "&copy; OpenStreetMap contributors" },
      ).addTo(map);
    }
    setBasemap();

    /* Marker pusat data Dinsos */
    var pusatMarker = L.circleMarker(BOGOR_CENTER, {
      radius: 8,
      color: "#136299",
      fillColor: "#136299",
      fillOpacity: 0.9,
    }).addTo(map);
    pusatMarker.bindTooltip("Pusat Data Dinsos Kota Bogor", {
      permanent: false,
    });

    var wilayahMarker = null;
    var wilayahBoundary = null; // layer polygon batas wilayah (dari Nominatim)

    function seedFromString(str) {
      var hash = 0;
      for (var i = 0; i < str.length; i++) {
        hash = (hash * 31 + str.charCodeAt(i)) >>> 0;
      }
      return hash;
    }

    function updateInfoPanel(kecamatan, kelurahan, tahun) {
      var wilayahLabel = "Semua Wilayah Kota Bogor";
      if (kecamatan && kelurahan)
        wilayahLabel = "Kel. " + kelurahan + ", Kec. " + kecamatan;
      else if (kecamatan) wilayahLabel = "Kec. " + kecamatan;

      document.getElementById("peta-info-wilayah").textContent = wilayahLabel;
      document.getElementById("peta-info-tahun").textContent = "Tahun " + tahun;
      document.getElementById("peta-info-kecamatan-txt").textContent =
        kecamatan || "–";
      document.getElementById("peta-info-kelurahan-txt").textContent =
        kelurahan || "–";

      var seed = seedFromString((kecamatan || "") + (kelurahan || "") + tahun);
      var laki = 8000 + (seed % 15000);
      var perempuan = 8000 + ((seed * 7) % 15000);
      var pkh = 200 + (seed % 800);
      var pbi = 300 + ((seed * 3) % 900);
      var bpnt = 250 + ((seed * 5) % 850);

      function fmt(n) {
        return n.toLocaleString("id-ID");
      }

      document.getElementById("peta-info-laki").textContent = fmt(laki);
      document.getElementById("peta-info-perempuan").textContent =
        fmt(perempuan);
      document.getElementById("peta-info-penduduk").textContent = fmt(
        laki + perempuan,
      );
      document.getElementById("peta-info-pkh").textContent = fmt(pkh);
      document.getElementById("peta-info-pbi").textContent = fmt(pbi);
      document.getElementById("peta-info-bpnt").textContent = fmt(bpnt);
    }

    /* --- Badge loading kecil di pojok peta selagi fetch boundary --- */
    function showPetaLoading() {
      hidePetaLoading();
      var mapContainer = document.getElementById("peta-map");
      if (!mapContainer) return;
      var badge = document.createElement("div");
      badge.className = "peta-loading-badge";
      badge.id = "peta-loading-badge";
      badge.innerHTML =
        '<span class="spin"></span><span>Memuat batas wilayah…</span>';
      mapContainer.appendChild(badge);
    }
    function hidePetaLoading() {
      var el = document.getElementById("peta-loading-badge");
      if (el) el.remove();
    }

    /* --- Fallback: kalau Nominatim tidak menemukan polygon,
       tampilkan titik seperti versi lama supaya peta tidak kosong. --- */
    function tampilkanMarkerFallback(kecamatan, kelurahan) {
      var data = wilayahData[kecamatan];
      if (!data) return;
      wilayahMarker = L.circleMarker([data.lat, data.lng], {
        radius: 10,
        color: "#fb8c00",
        fillColor: "#fb8c00",
        fillOpacity: 0.85,
      }).addTo(map);
      wilayahMarker.bindTooltip(
        kelurahan ? kelurahan + ", " + kecamatan : kecamatan,
        { permanent: true, direction: "top", className: "peta-label-marker" },
      );
      map.setView([data.lat, data.lng], 14);
    }

    function tampilkanWilayah() {
      var kecamatan = kecamatanSelect.value;
      var kelurahan = kelurahanSelect.value;
      var tahun = tahunSelect.value || tahunList[0];

      if (wilayahMarker) {
        map.removeLayer(wilayahMarker);
        wilayahMarker = null;
      }
      if (wilayahBoundary) {
        map.removeLayer(wilayahBoundary);
        wilayahBoundary = null;
      }

      if (kecamatan && wilayahData[kecamatan]) {
        var namaWilayah = kelurahan ? kelurahan + ", " + kecamatan : kecamatan;
        var query = namaWilayah + ", Kota Bogor, Jawa Barat, Indonesia";

        showPetaLoading();

        fetch(
          "https://nominatim.openstreetmap.org/search?format=geojson&polygon_geojson=1&limit=1&q=" +
            encodeURIComponent(query),
        )
          .then(function (res) {
            return res.json();
          })
          .then(function (data) {
            hidePetaLoading();
            if (data.features && data.features.length) {
              wilayahBoundary = L.geoJSON(data.features[0], {
                style: {
                  color: "#e53935",
                  weight: 2,
                  dashArray: "6, 6",
                  fillColor: "#fb8c00",
                  fillOpacity: 0.12,
                  className: "peta-boundary-path",
                },
              }).addTo(map);
              wilayahBoundary.bindTooltip(namaWilayah, {
                permanent: true,
                direction: "center",
                className: "peta-label-marker",
              });
              map.fitBounds(wilayahBoundary.getBounds(), { padding: [24, 24] });
            } else {
              tampilkanMarkerFallback(kecamatan, kelurahan);
            }
          })
          .catch(function () {
            hidePetaLoading();
            tampilkanMarkerFallback(kecamatan, kelurahan);
          });
      } else {
        map.setView(BOGOR_CENTER, 12);
      }

      updateInfoPanel(kecamatan, kelurahan, tahun);
    }

    function resetWilayah() {
      kecamatanSelect.value = "";
      kelurahanSelect.innerHTML = '<option value="">-Pilih-</option>';
      kelurahanSelect.disabled = true;
      tahunSelect.value = tahunList[0];

      if (wilayahMarker) {
        map.removeLayer(wilayahMarker);
        wilayahMarker = null;
      }
      if (wilayahBoundary) {
        map.removeLayer(wilayahBoundary);
        wilayahBoundary = null;
      }
      hidePetaLoading();

      map.setView(BOGOR_CENTER, 12);
      updateInfoPanel("", "", tahunList[0]);
    }

    if (btnTampilkan) btnTampilkan.addEventListener("click", tampilkanWilayah);
    if (btnReset) btnReset.addEventListener("click", resetWilayah);

    /* Nilai awal panel Informasi saat halaman pertama dimuat */
    updateInfoPanel("", "", tahunList[0]);

    /* Leaflet butuh invalidateSize setelah container selesai
       dirender (misalnya karena section baru terlihat/resize). */
    window.setTimeout(function () {
      map.invalidateSize();
    }, 300);
    window.addEventListener("resize", function () {
      map.invalidateSize();
    });
  })();

  /* --- Dropdown bahasa (desktop, hover-based) --- */
  document.querySelectorAll(".lang-option").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var lang = btn.getAttribute("data-lang");
      var flag = btn.getAttribute("data-flag");
      var labelEl = document.getElementById("lang-current-label");
      var flagEl = document.getElementById("lang-current-flag");
      if (labelEl) labelEl.textContent = lang.toUpperCase();
      if (flagEl) flagEl.src = flag;
      // Sinkron ke versi mobile juga
      var mobileLabel = document.getElementById("lang-mobile-label");
      var mobileFlag = document.getElementById("lang-mobile-flag");
      if (mobileLabel) mobileLabel.textContent = lang.toUpperCase();
      if (mobileFlag) mobileFlag.src = flag;
      // TODO: panggil fungsi ganti bahasa aktual di sini
    });
  });

  /* --- Dropdown bahasa (mobile, klik untuk buka/tutup) --- */
  var langMobileBtn = document.getElementById("lang-mobile-btn");
  var langMobileMenu = document.getElementById("lang-mobile-menu");
  if (langMobileBtn && langMobileMenu) {
    langMobileBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      langMobileMenu.classList.toggle("hidden");
    });
    document.addEventListener("click", function (e) {
      if (!langMobileMenu.contains(e.target) && e.target !== langMobileBtn) {
        langMobileMenu.classList.add("hidden");
      }
    });
  }
  document.querySelectorAll(".lang-option-mobile").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var lang = btn.getAttribute("data-lang");
      var flag = btn.getAttribute("data-flag");
      var labelEl = document.getElementById("lang-mobile-label");
      var flagEl = document.getElementById("lang-mobile-flag");
      if (labelEl) labelEl.textContent = lang.toUpperCase();
      if (flagEl) flagEl.src = flag;
      var currentLabel = document.getElementById("lang-current-label");
      var currentFlag = document.getElementById("lang-current-flag");
      if (currentLabel) currentLabel.textContent = lang.toUpperCase();
      if (currentFlag) currentFlag.src = flag;
      langMobileMenu.classList.add("hidden");
      // TODO: panggil fungsi ganti bahasa aktual di sini
    });
  });
});
