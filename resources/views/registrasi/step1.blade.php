@extends('layouts.auth')

@section('title', 'Daftar - SOLID v4 Dinas Sosial Kota Bogor')

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

          .auth-page {
            min-height: 100vh;
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

          .auth-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 58rem;
            min-height: 34rem;
            max-height: calc(100vh - 3.5rem);
            background: #ffffff;
            border-radius: 1.1rem;
            box-shadow: 0 24px 60px rgba(0, 20, 40, 0.35);
            overflow: hidden;
            display: flex;
          }

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

          .auth-info-stack {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            width: 100%;
            margin-top: auto;
          }

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

          .auth-hint {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
            border-radius: 0.85rem;
            border: 1px solid #a8d5f7;
            background: #eaf4fd;
            padding: 0.55rem 0.9rem;
            color: #0c527f;
            font-size: 11.5px;
            line-height: 1.4;
          }
          .auth-hint a {
            color: #003b62;
            font-weight: 700;
            text-decoration: underline;
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

          .auth-login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            width: fit-content;
            min-width: 8rem;
            margin: 0 auto;
            padding: 0.45rem 1.2rem;
            border-radius: 9999px;
            border: 1.5px solid #136299;
            color: #003b62;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            transition:
              background 0.15s ease,
              border-color 0.15s ease,
              transform 0.15s ease;
          }
          .auth-login-btn:hover {
            background: #eaf4fd;
            border-color: #003b62;
          }
          .auth-login-btn:active {
            transform: scale(0.98);
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

          .auth-back-form {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            color: #42474e;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 0.1rem;
            transition: color 0.15s ease;
          }
          .auth-back-form:hover {
            color: #003b62;
          }
          .auth-back-form .material-symbols-outlined {
            font-size: 16px;
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
            align-items: center;
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

          .auth-hint-text {
            margin-top: 0.4rem;
            font-size: 11px;
            font-weight: 600;
            color: #ba1a1a;
            line-height: 1.5;
            padding: 0 0.3rem;
          }

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
              min-height: 0;
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
        </style>

@endpush

@section('body_class', 'bg-surface font-body-md text-on-surface antialiased')

@section('content')

    <div class="auth-page">
      <div class="auth-outer-wrap">
        <div class="auth-card">
          <!-- Panel kiri -->
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

            <div class="auth-badge-wrap">
              <div class="auth-badge">
                <img alt="Logo SOLID" src="{{ asset('assets/img/logo/logo.png') }}" />
              </div>
            </div>

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

          <!-- Panel kanan: form Step 1 (terhubung ke server) -->
          <form
            class="auth-card-body"
            id="cek-kk-form"
            method="POST"
            action="{{ route('registrasi.step1.store') }}"
            novalidate
          >
            @csrf

            <div class="auth-warning">
              <span class="material-symbols-outlined text-[17px] shrink-0"
                >error</span
              >
              <span>
                Pendaftaran hanya diperuntukan bagi masyarakat yang berasal dari
                keluarga tidak mampu.
              </span>
            </div>

            <div class="auth-form-heading">
              <h2>Silahkan Masukan Data Diri Anda</h2>
              <p>Cek Nomor KK Anda untuk memulai pendaftaran BPJS PBI.</p>
            </div>

            @if ($errors->any())
              <div class="auth-warning">
                <span class="material-symbols-outlined text-[17px] shrink-0"
                  >error</span
                >
                <span>{{ $errors->first() }}</span>
              </div>
            @endif

            <div class="auth-field">
              <label for="no-kk">No KK (Kartu Keluarga)</label>
              <div class="auth-field-icon-wrap">
                <span
                  class="auth-input-icon material-symbols-outlined text-[17px]"
                  >credit_card</span
                >
                <input
                  id="no-kk"
                  name="no_kk"
                  type="text"
                  inputmode="numeric"
                  placeholder="Masukan No KK (Kartu Keluarga) Anda"
                  autocomplete="off"
                  value="{{ old('no_kk') }}"
                  maxlength="16"
                />
              </div>
              @error('no_kk')
                <p class="auth-hint-text">{{ $message }}</p>
              @enderror
            </div>

            <div class="auth-field">
              <label for="captcha"
                >Berapa <span>{{ $captchaA }}</span> +
                <span>{{ $captchaB }}</span> ?</label
              >
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
                  autocomplete="off"
                  value="{{ old('captcha') }}"
                />
              </div>
              @error('captcha')
                <p class="auth-hint-text">{{ $message }}</p>
              @enderror
            </div>

            <button type="submit" class="auth-submit">
              Next
              <span class="material-symbols-outlined text-[18px]"
                >arrow_forward</span
              >
            </button>

            <div class="auth-hint">
              <span class="material-symbols-outlined text-[17px] shrink-0"
                >help</span
              >
              <span>
                Bingung cara mendaftar? Pelajari
                <a href="{{ route('tutorial-pendaftaran') }}">Informasi Pendaftaran</a>
              </span>
            </div>

            <div class="auth-divider">Sudah mempunyai akun?</div>

            <a href="{{ route('login') }}" class="auth-login-btn">
              <span class="material-symbols-outlined text-[16px]">login</span>
              Login
            </a>
          </form>
        </div>

        <div class="auth-bottom-row">
          <p class="auth-footnote">
            © 2026 Pemerintah Kota Bogor. All right reserved
          </p>
        </div>
      </div>
    </div>

@endsection
