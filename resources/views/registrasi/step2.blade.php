@extends('layouts.auth')

@section('title', 'Registrasi Data Diri - SOLID v4 Dinas Sosial Kota Bogor')

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
                  warning: "#f9a825",
                  "warning-container": "#fff3cd",
                  "on-warning-container": "#7a5c00",
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
            overflow: hidden; /* Mengunci halaman utama */
          }
          * {
            box-sizing: border-box;
          }

          .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
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
            max-width: 78rem;
            background: #ffffff;
            border-radius: 1.1rem;
            box-shadow: 0 24px 60px rgba(0, 20, 40, 0.35);
            overflow: hidden;
            display: flex;
            align-items: stretch;
          }

          .auth-side {
            flex: 0 0 26%;
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
            flex: 1 1 74%;
            min-width: 0;
            padding: 1.9rem 2.2rem 1.9rem;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
            max-height: calc(100vh - 3rem);
            overflow-y: auto;
            overflow-x: hidden;
          }
          .auth-form-heading h2 {
            color: #121d26;
            font-size: 19px;
            font-weight: 700;
          }
          .auth-form-heading p {
            margin-top: 0.2rem;
            color: #42474e;
            font-size: 12.5px;
          }
          .auth-form-heading {
            margin-bottom: 0.1rem;
          }

          .auth-field {
            margin-bottom: 0;
            min-width: 0; /* Mencegah Grid Blowout */
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
            min-width: 0;
            width: 100%;
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
          .auth-field select,
          .auth-field textarea {
            width: 100%;
            min-width: 0; /* Mengizinkan form mengecil di bawah default browser */
            max-width: 100%;
            border: 1.5px solid #e4e9f0;
            background: #f7f9ff;
            border-radius: 9999px;
            padding: 0.55rem 1rem 0.55rem 2.7rem;
            font-size: 13.5px;
            color: #121d26;
            box-sizing: border-box;
            transition:
              border-color 0.2s ease,
              box-shadow 0.2s ease,
              background 0.2s ease;
          }
          .auth-field textarea {
            border-radius: 1rem;
            resize: vertical;
            min-height: 4.2rem;
            padding-top: 0.65rem;
          }
          .auth-field select {
            appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2372777f' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>");
            background-repeat: no-repeat;
            background-position: right 1.1rem center;
          }

          /* STANDAR UKURAN PLACEHOLDER */
          .auth-field input::placeholder,
          .auth-field textarea::placeholder {
            color: #9aa2ab;
            font-size: 14px;
          }

          .auth-field input:focus,
          .auth-field select:focus,
          .auth-field textarea:focus {
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
          .auth-field .auth-hint-text {
            margin-top: 0.4rem;
            font-size: 11px;
            font-weight: 600;
            color: #ba1a1a;
            line-height: 1.5;
            padding: 0 0.3rem;
          }

          .reg-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem 1.75rem;
          }
          @media (min-width: 900px) {
            .reg-grid {
              grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            }
          }
          .reg-col {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            min-width: 0;
          }

          .reg-section-title {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 12.5px;
            font-weight: 700;
            color: #136299;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-top: 0.2rem;
          }
          .reg-section-title .material-symbols-outlined {
            font-size: 16px;
          }

          .reg-rtrw {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            gap: 0.6rem;
          }
          .reg-rtrw label {
            font-size: 12px;
            font-weight: 600;
            color: #121d26;
            margin: 0;
          }

          .reg-family-card {
            border: 1.5px solid #e4e9f0;
            border-radius: 1rem;
            padding: 0.9rem 1rem;
            background: #f7f9ff;
            min-width: 0;
          }
          .reg-add-family {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            margin-bottom: 0.7rem;
            flex-wrap: wrap;
          }
          .reg-add-family label {
            font-size: 12.5px;
            font-weight: 700;
            color: #121d26;
          }
          .reg-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: linear-gradient(135deg, #003b62 0%, #136299 100%);
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            border: none;
            border-radius: 9999px;
            padding: 0.45rem 0.95rem;
            cursor: pointer;
            transition:
              box-shadow 0.2s ease,
              transform 0.15s ease;
            white-space: nowrap;
            flex-shrink: 0;
          }
          .reg-add-btn:hover {
            box-shadow: 0 8px 18px rgba(0, 59, 98, 0.3);
          }
          .reg-add-btn:active {
            transform: scale(0.98);
          }

          .reg-family-table-wrap {
            width: 100%;
            min-width: 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 0.75rem;
          }
          .reg-family-table {
            width: 100%;
            min-width: 420px;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12.5px;
            background: #ffffff;
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid #e4e9f0;
          }
          .reg-family-table th,
          .reg-family-table td {
            padding: 0.55rem 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eef2f6;
            white-space: nowrap;
          }
          .reg-family-table tr:last-child td {
            border-bottom: none;
          }
          .reg-family-table th {
            background: #eef4fb;
            color: #42474e;
            font-weight: 700;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
          }
          .reg-family-empty {
            color: #9aa2ab;
            font-style: italic;
            white-space: normal !important;
          }
          /* Tombol Edit dan Hapus Keluarga */
          .reg-family-edit, .reg-family-remove {
            cursor: pointer;
            background: none;
            border: none;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
          }
          .reg-family-edit {
            color: #136299;
          }
          .reg-family-remove {
            color: #ba1a1a;
          }

          .reg-family-scroll-hint {
            display: none;
            align-items: center;
            gap: 0.3rem;
            font-size: 10.5px;
            color: #72777f;
            margin-top: 0.4rem;
          }
          .reg-family-scroll-hint .material-symbols-outlined {
            font-size: 14px;
          }

          .reg-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding-top: 0.4rem;
            flex-wrap: wrap;
          }
          .auth-submit {
            width: fit-content;
            min-width: 9.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.5rem 1.6rem;
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
          .reg-cancel-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            border: 1.5px solid #f3c76a;
            background: #fff8e6;
            color: #7a5c00;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition:
              background 0.15s ease,
              border-color 0.15s ease,
              transform 0.15s ease;
          }
          .reg-cancel-btn:hover {
            background: #fff3cd;
            border-color: #d6a10a;
          }
          .reg-cancel-btn:active {
            transform: scale(0.98);
          }

          .auth-warning {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
            border-radius: 0.85rem;
            border: 1px solid #f6c9c9;
            background: #fdecec;
            padding: 0.6rem 0.95rem;
            color: #93000a;
            font-size: 12px;
            line-height: 1.45;
          }

          .reg-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 40, 69, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 50;
            padding: 1rem;
            backdrop-filter: blur(2px);
          }
          .reg-modal-backdrop.is-open {
            display: flex;
          }
          .reg-modal {
            background: #ffffff;
            border-radius: 1rem;
            width: 100%;
            max-width: 26rem;
            padding: 1.5rem;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
          }
          .reg-modal h3 {
            font-size: 16px;
            font-weight: 700;
            color: #121d26;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
          }
          .reg-modal h3 .material-symbols-outlined {
            color: #136299;
          }
          .reg-modal .auth-field {
            margin-bottom: 1rem;
          }
          .reg-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.6rem;
            margin-top: 0.3rem;
            flex-wrap: wrap;
          }
          .reg-modal-btn-cancel {
            padding: 0.5rem 1.15rem;
            border-radius: 9999px;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            color: #42474e;
            font-weight: 700;
            font-size: 12.5px;
            cursor: pointer;
          }
          .reg-modal-btn-save {
            padding: 0.5rem 1.15rem;
            border-radius: 9999px;
            border: none;
            background: linear-gradient(135deg, #003b62 0%, #136299 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 12.5px;
            cursor: pointer;
          }
          .reg-modal-btn-save:hover {
            box-shadow: 0 8px 18px rgba(0, 59, 98, 0.3);
          }

          .auth-bottom-row {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 78rem;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            margin-top: 0.75rem;
          }
          .auth-outer-wrap {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 84rem;
            display: flex;
            flex-direction: column;
            align-items: center;
          }
          .auth-back {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            color: #ffffff;
            opacity: 0.85;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
          }
          .auth-back:hover {
            opacity: 1;
          }
          .auth-back .material-symbols-outlined {
            font-size: 16px;
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

          /* ==========================================================
             FORM WIZARD (Mode Wizard aktif)
             ========================================================== */
          html.wz-active,
          html.wz-active body {
            overflow: hidden;
          }
          .wz-active .auth-page {
            height: 100vh;
            height: 100dvh;
            min-height: 0;
            padding: 0.75rem 1rem;
            align-items: center;
          }
          .wz-active .auth-outer-wrap {
            max-height: 100%;
          }
          .wz-active .auth-bottom-row {
            margin-top: 0.6rem;
          }

          /* Tinggi kartu tetap di Desktop */
          .wz-active .auth-card {
            height: min(40rem, calc(100vh - 3.5rem));
            height: min(40rem, calc(100dvh - 3.5rem));
          }
          .wz-active .auth-card-body {
            flex: 1 1 0;
            min-width: 0;
            min-height: 0;
            max-height: none;
            overflow: hidden;
            padding: 1.4rem 2rem 1.2rem;
          }

          .wz-on {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            gap: 1.25rem;
          }
          .wz-on .auth-form-heading {
            margin: 0;
          }
          .wz-on .auth-form-heading h2 {
            font-size: 17px;
          }
          .wz-on .auth-warning {
            padding: 0.45rem 0.8rem;
            font-size: 11.5px;
          }

          .wz {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            flex: 1 1 auto;
            min-height: 0;
          }

          /* Stepper */
          .wz-stepper {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
          }
          .wz-stepper li {
            display: flex;
            align-items: center;
            flex: 1 1 0;
            min-width: 0;
          }
          .wz-stepper li:last-child {
            flex: 0 0 auto;
          }
          .wz-stepper li:not(:last-child)::after {
            content: "";
            flex: 1 1 auto;
            height: 2px;
            margin: 0 0.5rem;
            background: #e4e9f0;
            border-radius: 9999px;
          }
          .wz-stepper li.is-done::after {
            background: #2e7d32;
          }
          .wz-dot {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0;
            border: none;
            background: none;
            font: inherit;
            cursor: default;
            pointer-events: none;
          }
          .wz-num {
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11.5px;
            font-weight: 700;
            background: #e4effd;
            color: #42474e;
            flex-shrink: 0;
          }
          .wz-label {
            font-size: 12px;
            font-weight: 600;
            color: #72777f;
            white-space: nowrap;
          }
          .wz-stepper li.is-done .wz-num {
            background: #2e7d32;
            color: #ffffff;
          }
          .wz-stepper li.is-current .wz-num {
            background: #003b62;
            color: #ffffff;
            box-shadow: 0 0 0 3px rgba(19, 98, 153, 0.18);
          }
          .wz-stepper li.is-current .wz-label {
            color: #003b62;
            font-weight: 700;
          }
          .wz-stepper li.has-error .wz-num {
            background: #ba1a1a;
            color: #ffffff;
          }
          .wz-stepper li.has-error .wz-label {
            color: #ba1a1a;
          }

          /* Isi Langkah */
          .wz-panels {
            flex: 1 1 auto;
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            padding: 4px;
            margin: -4px;
          }
          .wz-step {
            display: none;
          }
          .wz-step.is-active {
            display: block;
          }

          .wz-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem 0.9rem;
            align-content: start;
          }
          .wz-fields > * {
            grid-column: 1 / -1;
          }
          .wz-fields > .wz-half {
            grid-column: auto;
            min-width: 0; /* Mencegah overflow grid form setengah ukuran */
          }
          .wz-fields .auth-field label {
            margin-bottom: 0.2rem;
          }
          .wz-fields .auth-field input,
          .wz-fields .auth-field select {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
          }
          .wz-fields textarea {
            height: 3.6rem;
            min-height: 3.6rem;
            resize: none;
          }
          .wz-fields .auth-field select {
            padding-right: 2.2rem;
          }
          .wz-fields .reg-rtrw {
            grid-template-columns: auto minmax(0, 1fr) auto minmax(0, 1fr);
          }

          /* Tabel anggota keluarga (Wizard Mode) */
          .wz-active .reg-family-card {
            margin-top: 1rem;
          }
          .wz-active .reg-family-table-wrap {
            max-height: 9rem;
            overflow-y: auto;
          }
          .wz-active .reg-family-table th {
            position: sticky;
            top: 0;
            z-index: 1;
          }
          .wz-active .reg-family-scroll-hint {
            display: none;
          }
          /* Sembunyikan section title agar Alamat dan Keluarga terlihat menyambung */
          .wz-active .reg-section-title {
            display: none;
          }

          /* Navigasi Bawah Wizard */
          .wz-nav {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding-top: 0.2rem;
          }
          .wz-spacer {
            flex: 1 1 auto;
          }
          .wz-prev {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.5rem 1.2rem;
            border-radius: 9999px;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            color: #42474e;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
          }
          .wz-prev:hover {
            border-color: #136299;
            color: #003b62;
          }
          .wz-nav .auth-submit {
            width: auto;
            min-width: 8rem;
            padding: 0.5rem 1.4rem;
          }
          .wz-nav .reg-cancel-btn {
            width: auto;
            padding: 0.5rem 1.2rem;
          }

          @media (min-width: 981px) {
            .wz-fields > * {
              grid-column: auto;
            }
            .wz-fields > .wz-wide {
              grid-column: 1 / -1;
            }
          }

          /* ==========================================================
             OPTIMASI MOBILE FRIENDLY (MAX-WIDTH: 700px)
             ========================================================== */
          @media (max-width: 700px) {
            .wz-active .auth-page {
              padding: 0;
              align-items: flex-start;
            }

            .wz-active .auth-card {
              height: 100dvh;
              max-height: 100dvh;
              max-width: 100%;
              border-radius: 0;
              display: flex;
              flex-direction: column;
            }
            .wz-active .auth-side {
              flex: 0 0 auto;
              padding: 1.25rem 1.5rem; /* disamakan padding dengan step 1 */
            }

            /* SESUAIKAN DENGAN STEP 1 HEADER DI MOBILE */
            .wz-active .auth-brand-row {
              flex-direction: row !important;
              align-items: center !important;
              justify-content: space-between;
              gap: 0.5rem !important;
            }

            .wz-active .auth-badge-wrap,
            .wz-active .auth-info-stack,
            .wz-active .auth-bottom-row {
              display: none;
            }

            .wz-active .auth-card-body {
              flex: 1 1 auto;
              padding: 1.5rem 1.1rem 1rem;
              display: flex;
              flex-direction: column;
            }
            .wz-active .wz-panels {
              flex: 1 1 auto;
              overflow-y: auto;
              -webkit-overflow-scrolling: touch;
            }

            .wz-active .wz-nav {
              padding-top: 0.75rem;
              padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
            .wz-active .wz-nav .auth-submit {
              flex: 1 1 auto;
            }

            .wz-label {
              display: none;
            }
            .wz-stepper li.is-current .wz-label {
              display: inline;
            }
            .wz-nav .reg-cancel-btn {
              display: none !important;
            }

            .wz-fields input,
            .wz-fields select,
            .wz-fields textarea {
              font-size: 16px;
            }
            .wz-half .auth-input-icon {
              display: none;
            }
            .wz-active .wz-half input,
            .wz-active .wz-half select {
              padding-left: 0.75rem;
              padding-right: 0.75rem; /* Override padding agar tidak melebihi kolom */
              width: 100%;
              min-width: 0; /* Penting untuk menghentikan overflow grid */
              box-sizing: border-box;
            }

            /* Penyesuaian khusus form date di mobile supaya format text panjang tidak keluar form */
            .wz-active .wz-half input[type="date"] {
              padding-left: 0.4rem;
              padding-right: 0.4rem;
              font-size: 14.5px;
            }

            /* PLACEHOLDER MOBILE MATCHING STEP 1 */
            .auth-field input::placeholder,
            .auth-field textarea::placeholder {
               font-size: 13.5px;
            }

            .wz-active .auth-brand-row > div > span {
              display: block; /* kembalikan ke block untuk samakan dengan step 1 */
            }
          }

          @media (max-height: 720px) {
            .wz-active .auth-form-heading p {
              display: none;
            }
            .wz-fields {
              gap: 0.55rem 0.9rem;
            }
          }
          @media (max-height: 640px) {
            .wz-active .auth-card-body {
              padding-top: 0.9rem;
              padding-bottom: 0.8rem;
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
            <!-- HEADER LOGO SAMA DENGAN STEP 1 -->
            <div
              class="auth-brand-row"
              style="
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
              "
            >
              <a href="{{ route('registrasi.step1') }}" class="auth-back">
                <span class="material-symbols-outlined">arrow_back</span>
                Kembali
              </a>
              <div style="display: flex; align-items: center; gap: 0.75rem">
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

          <!-- Panel kanan: form Step 2 (terhubung ke server) -->
          <div class="auth-card-body" id="auth-card-body">
            <form
              id="register-step2-form"
              method="POST"
              action="{{ route('registrasi.step2.store') }}"
              novalidate
            >
              @csrf

              <div class="auth-form-heading">
                <h2>Lengkapi Data Diri Anda</h2>
                <p>
                  Isi data sesuai KTP/KK untuk menyelesaikan pendaftaran BPJS
                  PBI.
                </p>
              </div>

              @if ($errors->any())
                <div class="auth-warning" style="margin-bottom: 0.5rem">
                  <span class="material-symbols-outlined text-[17px] shrink-0"
                    >error</span
                  >
                  <span>Periksa kembali kolom yang ditandai merah di bawah.</span>
                </div>
              @endif

              <div class="reg-grid">
                <!-- ==================== KOLOM KIRI ==================== -->
                <div class="reg-col">
                  <div class="reg-section-title">
                    <span class="material-symbols-outlined">badge</span>
                    Akun &amp; Identitas
                  </div>

                  <div class="auth-field">
                    <label for="no-kk">No KK (Kartu Keluarga)</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >credit_card</span
                      >
                      <input
                        id="no-kk"
                        type="text"
                        value="{{ $noKk }}"
                        readonly
                      />
                    </div>
                  </div>

                  <div class="auth-field">
                    <label for="status-hubungan"
                      >Status Hubungan Keluarga</label
                    >
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >diversity_1</span
                      >
                      <select id="status-hubungan" name="status_hubungan" required>
                        <option value="">-Pilih-</option>
                        <option value="kepala_keluarga" @selected(old('status_hubungan') == 'kepala_keluarga')>Kepala Keluarga</option>
                        <option value="suami" @selected(old('status_hubungan') == 'suami')>Suami</option>
                        <option value="istri" @selected(old('status_hubungan') == 'istri')>Istri</option>
                        <option value="anak" @selected(old('status_hubungan') == 'anak')>Anak</option>
                        <option value="lainnya" @selected(old('status_hubungan') == 'lainnya')>Lainnya</option>
                      </select>
                    </div>
                    @error('status_hubungan') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="nik">NIK</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >badge</span
                      >
                      <input
                        id="nik"
                        name="nik"
                        type="text"
                        inputmode="numeric"
                        maxlength="16"
                        placeholder="Masukan No KTP Anda"
                        value="{{ old('nik') }}"
                        required
                      />
                    </div>
                    @error('nik') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >lock</span
                      >
                      <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Masukan password Anda"
                        autocomplete="new-password"
                        required
                      />
                      <button
                        class="auth-toggle-eye"
                        type="button"
                        data-toggle-target="password"
                        aria-label="Tampilkan password"
                      >
                        <span class="material-symbols-outlined text-[17px]"
                          >visibility</span
                        >
                      </button>
                    </div>
                    @error('password') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="password_confirmation">Ulangi Password</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >lock_reset</span
                      >
                      <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="Ulangi password Anda"
                        autocomplete="new-password"
                        required
                      />
                      <button
                        class="auth-toggle-eye"
                        type="button"
                        data-toggle-target="password_confirmation"
                        aria-label="Tampilkan password"
                      >
                        <span class="material-symbols-outlined text-[17px]"
                          >visibility</span
                        >
                      </button>
                    </div>
                    <p class="auth-hint-text">
                      (* Password minimal 8 karakter, memuat 1 Huruf Kapital, 1
                      Huruf Kecil, dan 1 angka)
                    </p>
                  </div>

                  <div class="reg-section-title">
                    <span class="material-symbols-outlined">home</span>
                    Alamat
                  </div>

                  <div class="auth-field">
                    <label for="alamat">Alamat Pemohon</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        style="top: 1.1rem; transform: none"
                        >home</span
                      >
                      <textarea
                        id="alamat"
                        name="alamat"
                        placeholder="Masukan Alamat Anda"
                        required
                      >{{ old('alamat') }}</textarea>
                    </div>
                    @error('alamat') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="reg-rtrw">
                    <label for="rt">RT</label>
                    <input
                      id="rt"
                      name="rt"
                      type="text"
                      inputmode="numeric"
                      placeholder="01"
                      value="{{ old('rt') }}"
                      required
                      style="
                        border-radius: 9999px;
                        border: 1.5px solid #e4e9f0;
                        padding: 0.55rem 1rem;
                      "
                    />
                    <label for="rw">RW</label>
                    <input
                      id="rw"
                      name="rw"
                      type="text"
                      inputmode="numeric"
                      placeholder="01"
                      value="{{ old('rw') }}"
                      required
                      style="
                        border-radius: 9999px;
                        border: 1.5px solid #e4e9f0;
                        padding: 0.55rem 1rem;
                      "
                    />
                  </div>

                  <div class="auth-field">
                    <label for="provinsi">Provinsi</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >map</span
                      >
                      <select id="provinsi" name="provinsi" required>
                        <option value="Jawa Barat" selected>Jawa Barat</option>
                      </select>
                    </div>
                  </div>

                  <div class="auth-field">
                    <label for="kab-kota">Kab/Kota</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >location_city</span
                      >
                      <select id="kab-kota" name="kab_kota" required>
                        <option value="Kota Bogor" selected>Kota Bogor</option>
                      </select>
                    </div>
                  </div>

                  <div class="auth-field">
                    <label for="kecamatan">Kecamatan</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >signpost</span
                      >
                      <select id="kecamatan" name="kecamatan" required>
                        <option value="">-Pilih-</option>
                        <option value="Bogor Selatan" @selected(old('kecamatan') == 'Bogor Selatan')>Bogor Selatan</option>
                        <option value="Bogor Timur" @selected(old('kecamatan') == 'Bogor Timur')>Bogor Timur</option>
                        <option value="Bogor Utara" @selected(old('kecamatan') == 'Bogor Utara')>Bogor Utara</option>
                        <option value="Bogor Tengah" @selected(old('kecamatan') == 'Bogor Tengah')>Bogor Tengah</option>
                        <option value="Bogor Barat" @selected(old('kecamatan') == 'Bogor Barat')>Bogor Barat</option>
                        <option value="Tanah Sareal" @selected(old('kecamatan') == 'Tanah Sareal')>Tanah Sareal</option>
                      </select>
                    </div>
                    @error('kecamatan') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="desa-kelurahan">Desa/Kelurahan</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >holiday_village</span
                      >
                      <input
                        id="desa-kelurahan"
                        name="desa_kelurahan"
                        type="text"
                        placeholder="Masukan Desa/Kelurahan Anda"
                        value="{{ old('desa_kelurahan') }}"
                        required
                      />
                    </div>
                    @error('desa_kelurahan') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>
                </div>

                <!-- ==================== KOLOM KANAN ==================== -->
                <div class="reg-col">
                  <div class="reg-section-title">
                    <span class="material-symbols-outlined">person</span>
                    Data Pribadi
                  </div>

                  <div class="auth-field">
                    <label for="nama-lengkap">Nama Lengkap</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >person</span
                      >
                      <input
                        id="nama-lengkap"
                        name="nama_lengkap"
                        type="text"
                        placeholder="Masukan Nama Anda"
                        value="{{ old('nama_lengkap') }}"
                        required
                      />
                    </div>
                    @error('nama_lengkap') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="email">Email Pemohon</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >mail</span
                      >
                      <input
                        id="email"
                        name="email"
                        type="email"
                        placeholder="Masukan Email Anda"
                        value="{{ old('email') }}"
                        required
                      />
                    </div>
                    @error('email') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="telp">Telp Pemohon</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >call</span
                      >
                      <input
                        id="telp"
                        name="telp"
                        type="text"
                        inputmode="numeric"
                        placeholder="Masukan Telp Anda"
                        value="{{ old('telp') }}"
                        required
                      />
                    </div>
                    @error('telp') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="agama">Agama</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >mosque</span
                      >
                      <select id="agama" name="agama" required>
                        <option value="">-Pilih-</option>
                        <option value="islam" @selected(old('agama') == 'islam')>Islam</option>
                        <option value="kristen" @selected(old('agama') == 'kristen')>Kristen</option>
                        <option value="katolik" @selected(old('agama') == 'katolik')>Katolik</option>
                        <option value="hindu" @selected(old('agama') == 'hindu')>Hindu</option>
                        <option value="buddha" @selected(old('agama') == 'buddha')>Buddha</option>
                        <option value="konghucu" @selected(old('agama') == 'konghucu')>Khonghucu</option>
                      </select>
                    </div>
                    @error('agama') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="pekerjaan">Pekerjaan</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >work</span
                      >
                      <input
                        id="pekerjaan"
                        name="pekerjaan"
                        type="text"
                        placeholder="Masukan Pekerjaan Anda Sesuai KTP"
                        value="{{ old('pekerjaan') }}"
                        required
                      />
                    </div>
                    @error('pekerjaan') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="tempat-lahir">Tempat Lahir</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >location_city</span
                      >
                      <input
                        id="tempat-lahir"
                        name="tempat_lahir"
                        type="text"
                        placeholder="Masukan Tempat Lahir"
                        value="{{ old('tempat_lahir') }}"
                        required
                      />
                    </div>
                    @error('tempat_lahir') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="tgl-lahir">Tgl Lahir</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >calendar_month</span
                      >
                      <input id="tgl-lahir" name="tgl_lahir" type="date" value="{{ old('tgl_lahir') }}" required />
                    </div>
                    @error('tgl_lahir') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="auth-field">
                    <label for="jenis-kelamin">Jenis Kelamin</label>
                    <div class="auth-field-icon-wrap">
                      <span
                        class="auth-input-icon material-symbols-outlined text-[17px]"
                        >wc</span
                      >
                      <select id="jenis-kelamin" name="jenis_kelamin" required>
                        <option value="">-Pilih-</option>
                        <option value="L" @selected(old('jenis_kelamin') == 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin') == 'P')>Perempuan</option>
                      </select>
                    </div>
                    @error('jenis_kelamin') <p class="auth-hint-text">{{ $message }}</p> @enderror
                  </div>

                  <div class="reg-section-title">
                    <span class="material-symbols-outlined">groups</span>
                    Anggota Keluarga
                  </div>

                  <div class="reg-family-card">
                    <div class="reg-add-family">
                      <label>Anggota Keluarga yang didaftarkan (opsional)</label>
                      <button
                        type="button"
                        class="reg-add-btn"
                        id="btn-tambah-anggota"
                      >
                        <span class="material-symbols-outlined text-[15px]"
                          >add</span
                        >
                        Tambah Data
                      </button>
                    </div>

                    <div class="reg-family-table-wrap">
                      <table class="reg-family-table" id="family-table">
                        <thead>
                          <tr>
                            <th>Nama</th>
                            <th>NIK</th>
                            <th>Status</th>
                            <th>Aksi</th>
                          </tr>
                        </thead>
                        <tbody id="family-table-body">
                          <tr id="family-empty-row">
                            <td colspan="4" class="reg-family-empty">
                              Belum ada anggota keluarga ditambahkan
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div class="reg-family-scroll-hint">
                      <span class="material-symbols-outlined">swipe</span>
                      Geser tabel ke kanan untuk lihat semua kolom
                    </div>
                  </div>
                </div>
              </div>

              <div class="reg-actions">
                <button type="submit" class="auth-submit">
                  <span class="material-symbols-outlined text-[16px]"
                    >check_circle</span
                  >
                  Submit
                </button>
                <a href="{{ route('registrasi.step1') }}" class="reg-cancel-btn">
                  <span class="material-symbols-outlined text-[16px]"
                    >close</span
                  >
                  Batal
                </a>
              </div>
            </form>
          </div>
        </div>

        <div class="auth-bottom-row">
          <p class="auth-footnote">
            © 2026 Pemerintah Kota Bogor. All right reserved
          </p>
        </div>
      </div>
    </div>

    <!-- ============================================================
         MODAL — Tambah / Edit Anggota Keluarga
         ============================================================ -->
    <div class="reg-modal-backdrop" id="modal-tambah-anggota">
      <div class="reg-modal">
        <h3 id="modal-title">
          <span class="material-symbols-outlined">group_add</span>
          Tambah Anggota Keluarga
        </h3>
        <div class="auth-field">
          <label for="modal-nama">Nama</label>
          <div class="auth-field-icon-wrap">
            <span class="auth-input-icon material-symbols-outlined text-[17px]"
              >person</span
            >
            <input id="modal-nama" type="text" placeholder="Masukan Nama" />
          </div>
        </div>
        <div class="auth-field">
          <label for="modal-nik">NIK</label>
          <div class="auth-field-icon-wrap">
            <span class="auth-input-icon material-symbols-outlined text-[17px]"
              >badge</span
            >
            <input
              id="modal-nik"
              type="text"
              inputmode="numeric"
              maxlength="16"
              placeholder="Masukan NIK"
            />
          </div>
        </div>
        <div class="auth-field" style="margin-bottom: 0">
          <label for="modal-status">Status Hubungan Keluarga</label>
          <div class="auth-field-icon-wrap">
            <span class="auth-input-icon material-symbols-outlined text-[17px]"
              >diversity_1</span
            >
            <select id="modal-status">
              <option value="">-Pilih-</option>
              <option value="suami">Suami</option>
              <option value="istri">Istri</option>
              <option value="anak">Anak</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
        </div>
        <div class="reg-modal-actions">
          <button type="button" class="reg-modal-btn-cancel" id="modal-batal">
            Batal
          </button>
          <button type="button" class="reg-modal-btn-save" id="modal-simpan">
            Simpan
          </button>
        </div>
      </div>
    </div>

    <script>
      (function () {
        // Toggle show/hide password
        document.querySelectorAll(".auth-toggle-eye").forEach(function (btn) {
          btn.addEventListener("click", function () {
            var targetId = btn.getAttribute("data-toggle-target");
            var input = document.getElementById(targetId);
            if (!input) return;
            var isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";
            btn.querySelector(".material-symbols-outlined").textContent =
              isHidden ? "visibility_off" : "visibility";
          });
        });

        // Modal anggota keluarga (Tambah / Edit)
        var modal = document.getElementById("modal-tambah-anggota");
        var modalTitle = document.getElementById("modal-title");
        var btnOpen = document.getElementById("btn-tambah-anggota");
        var btnCancel = document.getElementById("modal-batal");
        var btnSave = document.getElementById("modal-simpan");
        var tableBody = document.getElementById("family-table-body");
        var emptyRow = document.getElementById("family-empty-row");
        var familyIndex = 0;
        var editingRowId = null; // Penanda jika sedang dalam mode edit

        function openModal(editData = null) {
          if (editData) {
            document.getElementById("modal-nama").value = editData.nama;
            document.getElementById("modal-nik").value = editData.nik;
            document.getElementById("modal-status").value = editData.status;
            modalTitle.innerHTML = '<span class="material-symbols-outlined">edit</span> Edit Anggota Keluarga';
            editingRowId = editData.id;
          } else {
            document.getElementById("modal-nama").value = "";
            document.getElementById("modal-nik").value = "";
            document.getElementById("modal-status").value = "";
            modalTitle.innerHTML = '<span class="material-symbols-outlined">group_add</span> Tambah Anggota Keluarga';
            editingRowId = null;
          }
          modal.classList.add("is-open");
        }

        function closeModal() {
          modal.classList.remove("is-open");
        }

        function addFamilyRow(nama, nik, statusValue, statusLabel) {
          if (emptyRow) {
            emptyRow.remove();
            emptyRow = null;
          }
          var idx = familyIndex++;
          var rowId = "family-row-" + idx;

          var row = document.createElement("tr");
          row.id = rowId;
          row.innerHTML =
            "<td class='td-nama'>" + nama + "</td>" +
            "<td class='td-nik'>" + nik + "</td>" +
            "<td class='td-status'>" + statusLabel + "</td>" +
            "<td>" +
               "<div style='display:flex; gap:0.5rem;'>" +
                 "<button type='button' class='reg-family-edit'>Edit</button>" +
                 "<button type='button' class='reg-family-remove'>Hapus</button>" +
               "</div>" +
            "</td>";

          // Hidden input agar ikut disubmit PHP
          var inputNama = document.createElement("input");
          inputNama.type = "hidden";
          inputNama.name = "anggota[" + idx + "][nama]";
          inputNama.value = nama;
          inputNama.className = "input-nama";

          var inputNik = document.createElement("input");
          inputNik.type = "hidden";
          inputNik.name = "anggota[" + idx + "][nik]";
          inputNik.value = nik;
          inputNik.className = "input-nik";

          var inputStatus = document.createElement("input");
          inputStatus.type = "hidden";
          inputStatus.name = "anggota[" + idx + "][status]";
          inputStatus.value = statusValue;
          inputStatus.className = "input-status";

          row.appendChild(inputNama);
          row.appendChild(inputNik);
          row.appendChild(inputStatus);

          // Event Delete
          row.querySelector(".reg-family-remove").addEventListener("click", function () {
              row.remove();
              if (!tableBody.querySelector("tr")) {
                var newEmptyRow = document.createElement("tr");
                newEmptyRow.id = "family-empty-row";
                newEmptyRow.innerHTML =
                  '<td colspan="4" class="reg-family-empty">Belum ada anggota keluarga ditambahkan</td>';
                tableBody.appendChild(newEmptyRow);
                emptyRow = newEmptyRow;
              }
            });

          // Event Edit
          row.querySelector(".reg-family-edit").addEventListener("click", function () {
              openModal({
                 id: rowId,
                 nama: row.querySelector(".input-nama").value,
                 nik: row.querySelector(".input-nik").value,
                 status: row.querySelector(".input-status").value
              });
          });

          tableBody.appendChild(row);
        }

        function updateFamilyRow(rowId, nama, nik, statusValue, statusLabel) {
            var row = document.getElementById(rowId);
            if(!row) return;

            row.querySelector(".td-nama").textContent = nama;
            row.querySelector(".td-nik").textContent = nik;
            row.querySelector(".td-status").textContent = statusLabel;

            row.querySelector(".input-nama").value = nama;
            row.querySelector(".input-nik").value = nik;
            row.querySelector(".input-status").value = statusValue;
        }

        if (btnOpen) btnOpen.addEventListener("click", function() { openModal(null); });
        if (btnCancel) btnCancel.addEventListener("click", closeModal);
        modal.addEventListener("click", function (e) {
          if (e.target === modal) closeModal();
        });

        if (btnSave) {
          btnSave.addEventListener("click", function () {
            var nama = document.getElementById("modal-nama").value.trim();
            var nik = document.getElementById("modal-nik").value.trim();
            var statusSelect = document.getElementById("modal-status");
            var statusValue = statusSelect.value;
            var statusLabel = statusSelect.options[statusSelect.selectedIndex]
              ? statusSelect.options[statusSelect.selectedIndex].text
              : "";

            if (!nama || !nik || !statusValue) {
              alert("Nama, NIK, dan Status wajib diisi.");
              return;
            }

            if(editingRowId !== null) {
              updateFamilyRow(editingRowId, nama, nik, statusValue, statusLabel);
            } else {
              addFamilyRow(nama, nik, statusValue, statusLabel);
            }

            closeModal();
          });
        }
      })();
    </script>

    {{-- Wizard 3 langkah --}}
    <script>
      (function () {
        var form = document.getElementById("register-step2-form");
        var grid = form && form.querySelector(".reg-grid");
        if (!form || !grid) return;

        var groups = [];
        Array.prototype.forEach.call(grid.querySelectorAll(".reg-col"), function (col) {
          var current = null;
          Array.prototype.slice.call(col.children).forEach(function (el) {
            if (el.classList.contains("reg-section-title")) {
              // Simpan elemen title ini ke dalam grup (agar ikut tersembunyi nantinya)
              current = { els: [el] };
              groups.push(current);
            } else if (current) {
              current.els.push(el);
            }
          });
        });

        // Kita memastikan ada 4 section asli: Akun, Alamat, Pribadi, Keluarga
        if (groups.length !== 4) return;

        // GABUNGKAN LANGKAH:
        // 0: Akun
        // 1: Alamat
        // 2: Data Pribadi
        // 3: Keluarga
        // Jadinya -> 1. Akun, 2. Data Pribadi, 3. Alamat & Keluarga
        var steps = [
            { els: [].concat(groups[0].els) }, // Akun
            { els: [].concat(groups[2].els) }, // Data Pribadi
            { els: [].concat(groups[1].els, groups[3].els) } // Alamat disambung Keluarga
        ];
        var labels = ["Akun", "Data Pribadi", "Alamat & Keluarga"];

        function reorder(group, ids) {
          var byId = {};
          group.els.forEach(function (el) {
            var input = el.querySelector("input, select, textarea");
            if (input && input.id) byId[input.id] = el;
          });
          var out = [];
          ids.forEach(function (id) {
            if (byId[id]) out.push(byId[id]);
          });
          group.els.forEach(function (el) {
            if (out.indexOf(el) === -1) out.push(el);
          });
          group.els = out;
        }

        // Urut ulang form step Data Pribadi (indeks 1 sekarang)
        reorder(steps[1], [
          "nama-lengkap", "email", "telp", "pekerjaan",
          "agama", "jenis-kelamin", "tempat-lahir", "tgl-lahir",
        ]);

        [
          "agama", "jenis-kelamin", "tempat-lahir", "tgl-lahir",
          "provinsi", "kab-kota", "kecamatan", "desa-kelurahan",
        ].forEach(function (id) {
          var input = document.getElementById(id);
          var box = input && input.closest(".auth-field");
          if (box) box.classList.add("wz-half");
        });
        var alamat = document.getElementById("alamat");
        var alamatBox = alamat && alamat.closest(".auth-field");
        if (alamatBox) alamatBox.classList.add("wz-wide");
        var familyCard = form.querySelector(".reg-family-card");
        if (familyCard) familyCard.classList.add("wz-wide");

        var wz = document.createElement("div");
        wz.className = "wz";
        var stepper = document.createElement("ol");
        stepper.className = "wz-stepper";
        var panels = document.createElement("div");
        panels.className = "wz-panels";

        var sections = [];
        var dots = [];
        var errs = [];

        steps.forEach(function (g, i) {
          var sec = document.createElement("section");
          sec.className = "wz-step";
          var fields = document.createElement("div");
          fields.className = "wz-fields";
          g.els.forEach(function (el) {
            fields.appendChild(el);
          });
          sec.appendChild(fields);
          panels.appendChild(sec);
          sections.push(sec);

          errs.push(
            Array.prototype.some.call(sec.querySelectorAll(".auth-hint-text"), function (p) {
              return p.textContent.trim().indexOf("(*") !== 0;
            })
          );

          var li = document.createElement("li");
          var btn = document.createElement("button");
          btn.type = "button";
          btn.className = "wz-dot";
          btn.innerHTML =
            '<span class="wz-num">' + (i + 1) + '</span><span class="wz-label">' + labels[i] + "</span>";

          // Event click pada stepper dimatikan karena pointer-events: none (via CSS)

          li.appendChild(btn);
          stepper.appendChild(li);
          dots.push(li);
        });

        wz.appendChild(stepper);
        wz.appendChild(panels);
        grid.parentNode.replaceChild(wz, grid);

        var actions = form.querySelector(".reg-actions");
        var submitBtn = actions && actions.querySelector(".auth-submit");
        var cancelBtn = actions && actions.querySelector(".reg-cancel-btn");

        var nav = document.createElement("div");
        nav.className = "wz-nav";

        var prev = document.createElement("button");
        prev.type = "button";
        prev.className = "wz-prev";
        prev.innerHTML =
          '<span class="material-symbols-outlined text-[16px]">arrow_back</span> Sebelumnya';

        var spacer = document.createElement("span");
        spacer.className = "wz-spacer";

        var next = document.createElement("button");
        next.type = "button";
        next.className = "auth-submit wz-next";
        next.innerHTML =
          'Lanjut <span class="material-symbols-outlined text-[16px]">arrow_forward</span>';

        nav.appendChild(prev);
        nav.appendChild(spacer);
        if (cancelBtn) nav.appendChild(cancelBtn);
        nav.appendChild(next);
        if (submitBtn) nav.appendChild(submitBtn);

        wz.parentNode.insertBefore(nav, wz.nextSibling);
        if (actions && actions.parentNode) actions.parentNode.removeChild(actions);

        var warnings = form.querySelectorAll(".auth-warning");
        var errWarn = warnings[0]; // Hanya ada 1 warning pop up ketika error
        var cur = 0;

        function show(i) {
          cur = Math.max(0, Math.min(sections.length - 1, i));
          var last = cur === sections.length - 1;

          sections.forEach(function (s, k) {
            s.classList.toggle("is-active", k === cur);
          });
          dots.forEach(function (d, k) {
            d.classList.toggle("is-current", k === cur);
            d.classList.toggle("is-done", k < cur && !errs[k]);
            d.classList.toggle("has-error", !!errs[k]);
            var b = d.firstChild;
            if (k === cur) b.setAttribute("aria-current", "step");
            else b.removeAttribute("aria-current");
          });

          prev.style.visibility = cur === 0 ? "hidden" : "visible";
          next.style.display = last ? "none" : "";
          if (submitBtn) submitBtn.style.display = last ? "" : "none";
          if (cancelBtn) cancelBtn.style.display = last ? "" : "none";

          if (errWarn) errWarn.style.display = errs[cur] ? "" : "none";
        }

        prev.addEventListener("click", function () {
          show(cur - 1);
        });

        // Validasi HTML5 saat menekan tombol Lanjut
        next.addEventListener("click", function () {
          var currentSection = sections[cur];
          var inputs = Array.from(currentSection.querySelectorAll("input[required], select[required], textarea[required]"));
          var isValid = true;

          for (var i = 0; i < inputs.length; i++) {
            if (!inputs[i].checkValidity()) {
              inputs[i].reportValidity(); // Memunculkan tooltip peringatan native browser
              isValid = false;
              break;
            }
          }

          if (isValid) {
            show(cur + 1);
          }
        });

        // Validasi dan Next pakai tombol Enter
        form.addEventListener("keydown", function (e) {
          if (e.key === "Enter" && (e.target.tagName === "INPUT" || e.target.tagName === "SELECT") && cur < sections.length - 1) {
            e.preventDefault();
            next.click(); // Trigger validasi yang sama dengan klik Lanjut
          }
        });

        document.documentElement.classList.add("wz-active");
        form.classList.add("wz-on");
        var firstErr = errs.indexOf(true);
        show(firstErr >= 0 ? firstErr : 0);
      })();
    </script>

@endsection
