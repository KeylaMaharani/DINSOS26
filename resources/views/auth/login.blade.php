@extends('layouts.auth')

@section('title', 'Masuk Akun - SOLID v4 Dinas Sosial Kota Bogor')

@push('head')
        <meta charset="utf-8" />
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="https://fonts.googleapis.com" rel="preconnect" />
        <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/logo.png') }}" />
        <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
        <link
          href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap"
          rel="stylesheet"
        />
        <link
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
          rel="stylesheet"
        />
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
                  "surface-variant": "#d9e3f1",
                  "on-primary": "#ffffff",
                  "surface-container-highest": "#d9e3f1",
                  "surface-container-high": "#dfe9f7",
                  "surface-container": "#e4effd",
                  "on-secondary": "#ffffff",
                  "primary-container": "#24527a",
                  error: "#ba1a1a",
                  "error-container": "#ffdad6",
                  "on-error-container": "#93000a",
                  "on-error": "#ffffff",
                  "surface-dim": "#d1dbe8",
                  "surface-container-low": "#edf4ff",
                  "surface-container-lowest": "#ffffff",
                  "secondary-fixed": "#cfe5ff",
                  "secondary-container": "#82c1fd",
                  primary: "#003b62",
                  "on-surface": "#121d26",
                  outline: "#72777f",
                  "on-surface-variant": "#42474e",
                  secondary: "#136299",
                  "secondary-fixed-dim": "#98cbff",
                  "on-secondary-container": "#004e7e",
                  "surface-bright": "#f7f9ff",
                  "on-background": "#121d26",
                  background: "#f7f9ff",
                  "success-container": "#d0f8d0",
                  "on-success-container": "#0a5e0a",
                  success: "#2e7d32",
                },
                borderRadius: {
                  DEFAULT: "0.25rem",
                  lg: "0.5rem",
                  xl: "0.75rem",
                  full: "9999px",
                },
                fontFamily: {
                  "body-lg": ["Plus Jakarta Sans"],
                  "body-sm": ["Plus Jakarta Sans"],
                  "headline-lg-mobile": ["Plus Jakarta Sans"],
                  "label-sm": ["Plus Jakarta Sans"],
                  "headline-sm": ["Plus Jakarta Sans"],
                  "label-md": ["Plus Jakarta Sans"],
                  "label-lg": ["Plus Jakarta Sans"],
                  "headline-md": ["Plus Jakarta Sans"],
                  "body-md": ["Plus Jakarta Sans"],
                  "headline-lg": ["Plus Jakarta Sans"],
                },
              },
            },
          };
        </script>
        <style>
          html,
          body {
            height: 100%;
            margin: 0;
            padding: 0;
          }
          * {
            box-sizing: border-box;
          }

          /* ===== Background halaman ===== */
          .auth-page {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1rem;
            position: relative;
            overflow: hidden;
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
          }
          .auth-page::before,
          .auth-page::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            pointer-events: none;
          }
          .auth-page::before {
            width: 28rem;
            height: 28rem;
            right: -8rem;
            top: -10rem;
          }
          .auth-page::after {
            width: 16rem;
            height: 16rem;
            left: -4rem;
            bottom: -5rem;
            border-color: rgba(255, 255, 255, 0.08);
          }

          /* ===== Card split: panel kiri (branding/gambar) + panel kanan (form) ===== */
          .auth-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 58rem;
            max-height: calc(100vh - 3.5rem);
            background: #ffffff;
            border-radius: 1.1rem;
            box-shadow: 0 24px 60px rgba(0, 20, 40, 0.35);
            overflow: hidden;
            display: flex;
            min-height: 0;
          }

          /* ---- Panel kiri: brand & ilustrasi ---- */
          .auth-side {
            flex: 1 1 40%;
            background: linear-gradient(
              160deg,
              #003b62 0%,
              #0c527f 55%,
              #136299 100%
            );
            padding: 1.75rem 1.75rem;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
          }
          .auth-side::before {
            content: "";
            position: absolute;
            width: 20rem;
            height: 20rem;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            right: -6rem;
            bottom: -7rem;
          }
          .auth-side::after {
            content: "";
            position: absolute;
            width: 10rem;
            height: 10rem;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            left: -3rem;
            top: -3rem;
          }

          .auth-brand-row {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            position: relative;
            z-index: 2;
          }
          .auth-brand-row img {
            width: 1.8rem;
            height: 1.8rem;
            object-fit: contain;
          }
          .auth-brand-row span {
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.02em;
            white-space: nowrap;
          }

          /* Badge/gambar: diam di tengah ruang kosong, tidak ikut terdorong turun */
          .auth-badge-wrap {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1 1 auto;
            margin: 0.5rem 0;
          }
          .auth-badge {
            width: 8rem;
            height: 8rem;
            border-radius: 9999px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.22);
          }
          .auth-badge img {
            width: 5.8rem;
            height: 5.8rem;
            object-fit: contain;
          }

          /* Stack info box: didorong ke bawah panel, gambar di atas tetap diam */
          .auth-info-stack {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            width: 100%;
            margin-top: auto;
          }

          /* ---- Info box di panel kiri (transparan di atas background gelap) ---- */
          .auth-info-box {
            width: 100%;
            display: flex;
            gap: 0.65rem;
            align-items: center;
            text-align: left;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 0.85rem;
            padding: 0.7rem 0.85rem;
            backdrop-filter: blur(6px);
          }
          .auth-info-box .material-symbols-outlined {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.14);
            border-radius: 9999px;
            padding: 0.3rem;
            font-size: 18px !important;
            flex-shrink: 0;
          }
          .auth-info-box .auth-info-title {
            color: #ffffff;
            font-size: 12.5px;
            font-weight: 700;
            margin: 0 0 0.1rem;
          }
          .auth-info-box .auth-info-text {
            color: rgba(255, 255, 255, 0.75);
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
          }
          .auth-info-box > div {
            min-width: 0;
            flex: 1 1 auto;
          }
          .auth-info-box .auth-info-text a {
            color: #98cbff;
            font-weight: 700;
            text-decoration: none;
          }
          .auth-info-box .auth-info-text a:hover {
            text-decoration: underline;
          }

          /* ---- Panel kanan: form ---- */
          .auth-card-body {
            flex: 1 1 60%;
            padding: 1.6rem 2rem 1.6rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            justify-content: center;
            overflow-y: auto;
          }
          .auth-form-heading h2 {
            color: #121d26;
            font-size: 18px;
            font-weight: 700;
          }
          .auth-form-heading p {
            margin-top: 0.2rem;
            color: #42474e;
            font-size: 12px;
          }
          .auth-form-heading {
            margin-bottom: 0.2rem;
          }

          /* ===== Field kekinian: rounded penuh, ikon kiri ===== */
          .auth-field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #121d26;
            margin-bottom: 0.3rem;
          }
          .auth-field-icon-wrap {
            position: relative;
          }
          .auth-field-icon-wrap .auth-input-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #72777f;
            pointer-events: none;
            display: flex;
          }
          .auth-field input,
          .auth-field select {
            width: 100%;
            border: 1.5px solid #e4e9f0;
            background: #f7f9ff;
            border-radius: 9999px;
            padding: 0.55rem 1rem 0.55rem 2.7rem;
            font-size: 13.5px;
            color: #121d26;
            transition:
              border-color 0.2s ease,
              box-shadow 0.2s ease,
              background 0.2s ease;
          }
          .auth-field select {
            padding-left: 2.75rem;
            appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2372777f' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>");
            background-repeat: no-repeat;
            background-position: right 1rem center;
          }
          .auth-field input::placeholder {
            color: #9aa2ab;
          }
          .auth-field input:focus,
          .auth-field select:focus {
            outline: none;
            border-color: #136299;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(19, 98, 153, 0.12);
          }
          .auth-field-icon-wrap input {
            padding-right: 2.6rem;
          }
          .auth-toggle-eye {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #72777f;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0.2rem;
            display: flex;
          }

          .auth-row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11.5px;
          }
          .auth-row-between label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: #42474e;
            font-weight: 500;
          }
          .auth-row-between a {
            color: #136299;
            font-weight: 700;
            text-decoration: none;
          }
          .auth-row-between a:hover {
            text-decoration: underline;
          }

          /* ===== Button "Masuk" ===== */
          .auth-submit {
            width: fit-content;
            min-width: 9.5rem;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.45rem 1.4rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #003b62 0%, #136299 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 13px;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(0, 59, 98, 0.3);
            transition:
              transform 0.15s ease,
              box-shadow 0.2s ease;
          }
          .auth-submit:hover {
            box-shadow: 0 10px 22px rgba(0, 59, 98, 0.4);
          }
          .auth-submit:active {
            transform: scale(0.98);
          }

          .auth-warning {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
            border-radius: 0.85rem;
            border: 1px solid #f6c9c9;
            background: #fdecec;
            padding: 0.55rem 0.9rem;
            color: #93000a;
            font-size: 11.5px;
            line-height: 1.4;
          }

          .auth-divider {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #72777f;
            font-size: 10.5px;
          }
          .auth-divider::before,
          .auth-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e4e9f0;
          }

          .auth-register-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0.5rem;
          }
          .auth-register-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0.5rem 0.5rem;
            border-radius: 0.75rem;
            /* border lebih gelap supaya tetap kelihatan jelas walau layar sempit/di-scale */
            border: 1.5px solid #c9d3e0;
            background: #ffffff;
            color: #003b62;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            transition:
              background 0.15s ease,
              border-color 0.15s ease;
          }
          .auth-register-btn:hover {
            background: #f7f9ff;
            border-color: #136299;
          }
          .auth-register-btn.is-highlighted {
            animation: register-glow 1.2s ease-in-out infinite;
          }
          @keyframes register-glow {
            0%,
            100% {
              box-shadow: 0 0 0 0 rgba(19, 98, 153, 0.55);
              background: #eaf3ff;
              border-color: #136299;
              color: #003b62;
            }
            50% {
              box-shadow: 0 0 0 8px rgba(19, 98, 153, 0);
              background: #136299;
              border-color: #003b62;
              color: #ffffff;
            }
          }

          .auth-back {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            color: #ffffff;
            opacity: 0.85;
            font-size: 11.5px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
          }
          .auth-back:hover {
            opacity: 1;
          }
          .auth-back .material-symbols-outlined {
            font-size: 15px;
          }

          .auth-bottom-row {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 58rem;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            margin-top: 0.6rem;
          }

          .auth-outer-wrap {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 64rem;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
          }

          .auth-footnote {
            color: rgba(255, 255, 255, 0.6);
            font-size: 10px;
            white-space: nowrap;
          }
          .auth-bottom-dot {
            color: rgba(255, 255, 255, 0.4);
            font-size: 10px;
          }

          /* ===== Responsif =====
             Breakpoint diturunkan dari 860px -> 700px supaya tampilan
             dua-panel (seperti versi desktop) tetap dipertahankan lebih lama
             dan tidak cepat "tumpuk" saat dilihat di jendela/preview sempit. */
          @media (max-width: 700px) {
            .auth-page {
              height: auto;
              min-height: 100vh;
              align-items: flex-start;
            }
            .auth-card {
              flex-direction: column;
              max-width: 26rem;
              max-height: none;
            }
            .auth-side {
              padding: 1.5rem 1.5rem 2rem;
            }
            .auth-badge-wrap {
              flex: 0 0 auto;
              margin: 1rem 0;
            }
            .auth-badge {
              width: 6.5rem;
              height: 6.5rem;
            }
            .auth-badge img {
              width: 4.6rem;
              height: 4.6rem;
            }
            .auth-info-stack {
              margin-top: 0.5rem;
            }
            .auth-info-box {
              padding: 0.6rem 0.75rem;
            }
            .auth-info-box .auth-info-text {
              font-size: 10.5px;
            }
            .auth-card-body {
              padding: 1.25rem 1.5rem 1.5rem;
              max-height: none;
              overflow-y: visible;
            }
            .auth-bottom-row {
              max-width: 26rem;
              flex-direction: column;
              align-items: center;
              text-align: center;
              gap: 0.35rem;
            }
            .auth-bottom-dot {
              display: none;
            }
            .auth-footnote {
              white-space: normal;
            }
            .auth-outer-wrap {
              align-items: center;
            }
            .auth-submit {
              width: 100%;
              margin: 0;
            }
          }
          @media (max-width: 380px) {
            .auth-register-grid {
              grid-template-columns: 1fr;
            }
          }
        </style>

@endpush

@section('body_class', 'bg-surface font-body-md text-on-surface antialiased')

@section('content')

    <div class="auth-page">
      <div class="auth-outer-wrap">
        <div class="auth-card">
          <!-- Panel kiri: gambar / branding -->
          <div class="auth-side">
            <div
              class="auth-brand-row"
              style="
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
              "
            >
              <a href="{{ route('home') }}" class="auth-back">
                <span class="material-symbols-outlined">arrow_back</span>
                Kembali ke Home
              </a>
              <div style="display: flex; align-items: center; gap: 0.6rem">
                <img
                  alt="Logo Dinas Sosial Kota Bogor"
                  src="{{ asset('assets/img/logo/bogor.png') }}"
                />
                <span>Dinas Sosial Kota Bogor</span>
              </div>
            </div>

            <!-- Badge/gambar: posisinya tetap, tidak ikut terdorong -->
            <div class="auth-badge-wrap">
              <div class="auth-badge">
                <img alt="Logo SOLID" src="{{ asset('assets/img/logo/logo.png') }}" />
              </div>
            </div>

            <!-- Info box: didorong ke bagian bawah panel kiri -->
            <div class="auth-info-stack">
              <div class="auth-info-box">
                <span class="material-symbols-outlined">support_agent</span>
                <div>
                  <p class="auth-info-title">
                    Layanan CHIKA (Chat Assistant JKN)
                  </p>
                  <p class="auth-info-text">
                    Tanya via WhatsApp
                    <a href="https://wa.me/628118750400">0811-8750-400</a>
                  </p>
                </div>
              </div>

              <div class="auth-info-box">
                <span class="material-symbols-outlined">verified_user</span>
                <div>
                  <p class="auth-info-title">Sebelum mendaftar</p>
                  <p class="auth-info-text">
                    Pastikan Anda belum terdaftar peserta JKN PBI / SPMB.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Panel kanan: form login -->
          <form class="auth-card-body" id="login-form">
            <div class="auth-form-heading">
              <h2>Silakan masuk dengan akun Anda</h2>
              <p>Gunakan NIK / Nomor KK dan kata sandi yang telah terdaftar.</p>
            </div>

            <div class="auth-field">
              <label for="jenis-akun">Jenis Akun</label>
              <div class="auth-field-icon-wrap">
                <span
                  class="auth-input-icon material-symbols-outlined text-[17px]"
                  >badge</span
                >
                <select id="jenis-akun" name="jenis_akun">
                  <option value="pbi">PBI / SPMB</option>
                  <option value="yayasan">YAYASAN</option>
                  <option value="csr">CSR</option>
                </select>
              </div>
            </div>

            <div class="auth-field">
              <label for="nik">NIK / No. KK</label>
              <div class="auth-field-icon-wrap">
                <span
                  class="auth-input-icon material-symbols-outlined text-[17px]"
                  >credit_card</span
                >
                <input
                  id="nik"
                  name="nik"
                  type="text"
                  inputmode="numeric"
                  placeholder="Masukan NIK / No. KK Anda"
                  autocomplete="off"
                />
              </div>
            </div>

            <div class="auth-field">
              <label for="password">Kata Sandi</label>
              <div class="auth-field-icon-wrap">
                <span
                  class="auth-input-icon material-symbols-outlined text-[17px]"
                  >lock</span
                >
                <input
                  id="password"
                  name="password"
                  type="password"
                  placeholder="Masukan kata sandi Anda"
                  autocomplete="current-password"
                />
                <button
                  class="auth-toggle-eye"
                  id="toggle-password"
                  type="button"
                  aria-label="Tampilkan kata sandi"
                >
                  <span class="material-symbols-outlined text-[17px]"
                    >visibility</span
                  >
                </button>
              </div>
            </div>

            <div class="auth-field">
              <label for="captcha">Berapa 4 + 3 ?</label>
              <div class="auth-field-icon-wrap">
                <span
                  class="auth-input-icon material-symbols-outlined text-[17px]"
                  >quiz</span
                >
                <input
                  id="captcha"
                  name="captcha"
                  type="text"
                  inputmode="numeric"
                  placeholder="Jawaban captcha"
                />
              </div>
            </div>

            <div class="auth-row-between">
              <label>
                <input type="checkbox" class="rounded" />
                Ingat saya
              </label>
              <a href="#">Lupa kata sandi?</a>
            </div>

            <button type="submit" class="auth-submit">
              <span class="material-symbols-outlined text-[16px]">login</span>
              Masuk
            </button>

            <div class="auth-warning">
              <span class="material-symbols-outlined text-[17px] shrink-0"
                >error</span
              >
              <span>
                Pendaftaran BPJS PBI hanya diperuntukan bagi masyarakat yang
                berasal dari keluarga tidak mampu.
              </span>
            </div>

            <div class="auth-divider">Belum mempunyai akun?</div>

            <div class="auth-register-grid">
              <a
                href="{{ route('registrasi.step1') }}"
                class="auth-register-btn"
                id="register-bpjs-btn"
                >Register BPJS PBI</a
              >
              <a href="#" class="auth-register-btn">Register SPMB</a>
              <a href="#" class="auth-register-btn">Register Yayasan</a>
            </div>
          </form>
        </div>

        <div class="auth-bottom-row">
          <p class="auth-footnote">
            © 2026 Pemerintah Kota Bogor. All right reserved
          </p>
        </div>
      </div>
    </div>

    <script>
      (function () {
        var toggleBtn = document.getElementById("toggle-password");
        var passwordInput = document.getElementById("password");
        if (toggleBtn && passwordInput) {
          toggleBtn.addEventListener("click", function () {
            var isHidden = passwordInput.type === "password";
            passwordInput.type = isHidden ? "text" : "password";
            toggleBtn.querySelector(".material-symbols-outlined").textContent =
              isHidden ? "visibility_off" : "visibility";
          });
        }

        var form = document.getElementById("login-form");
        var registerBpjsBtn = document.getElementById("register-bpjs-btn");
        if (form) {
          form.addEventListener("submit", function (e) {
            e.preventDefault();
            // Simulasi: login gagal -> tuntun user ke Register BPJS PBI
            if (registerBpjsBtn) {
              registerBpjsBtn.classList.add("is-highlighted");
              registerBpjsBtn.scrollIntoView({
                behavior: "smooth",
                block: "center",
              });
            }
          });
        }
      })();
    </script>

@endsection
