@extends('layouts.app')

@section('title', 'Cek Desil & Status Bansos - SOLID v4 Dinas Sosial Kota Bogor')

{{-- CSS khusus halaman ini. WAJIB pakai push('head'), karena layout memakai stack('head').
     Header, menu mobile, tombol melayang (sosmed/WA/aksesibilitas) dan script.js
     sudah disediakan layout, jadi TIDAK ditulis ulang di sini. --}}
@push('head')
    <style>
        [id] {
            scroll-margin-top: 7.5rem;
        }

        /* --- Hero (gaya sama dengan halaman lain) --- */
        .tp-hero {
            background:
                radial-gradient(circle at 15% 50%, rgba(255, 255, 255, 0.04), transparent 30%),
                radial-gradient(circle at 85% 30%, rgba(255, 255, 255, 0.06), transparent 30%),
                linear-gradient(135deg, #121358 0%, #1a233a 50%, #0f172a 100%);
            position: relative;
            overflow: hidden;
        }
        .tp-hero::before,
        .tp-hero::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(4px);
        }
        .tp-hero::before {
            width: 32rem;
            height: 32rem;
            right: -12rem;
            top: -14rem;
        }
        .tp-hero::after {
            width: 18rem;
            height: 18rem;
            left: -6rem;
            bottom: -8rem;
        }
        .tp-breadcrumb-wrapper {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            position: relative;
            z-index: 20;
        }
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

        /* --- Form --- */
        .cd-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #121d26;
            margin-bottom: 0.5rem;
        }
        .cd-field-wrap {
            position: relative;
        }
        .cd-field-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #72777f;
            pointer-events: none;
            display: flex;
        }
        .cd-field input {
            width: 100%;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            border-radius: 9999px;
            padding: 0.7rem 1rem 0.7rem 2.8rem;
            font-size: 14px;
            color: #121d26;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .cd-field input::placeholder {
            color: #9aa2ab;
            font-size: 13px;
        }
        .cd-field input:focus {
            outline: none;
            border-color: #121358;
            box-shadow: 0 0 0 4px rgba(18, 19, 88, 0.12);
        }

        /* --- Captcha "Huruf Kode" --- */
        .cd-captcha {
            font-family: "Georgia", serif;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 0.2em;
            color: #121358;
            background:
                repeating-linear-gradient(115deg, rgba(18, 19, 88, 0.07) 0 2px, transparent 2px 10px),
                #f1f5fb;
            border: 1.5px dashed #98cbff;
            border-radius: 9999px;
            padding: 0.6rem 1rem;
            user-select: none;
            text-align: center;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
        }
        .cd-captcha::before,
        .cd-captcha::after {
            content: "";
            position: absolute;
            height: 1px;
            left: 8%;
            right: 8%;
            background: rgba(18, 19, 88, 0.3);
        }
        .cd-captcha::before {
            top: 38%;
            transform: rotate(-3deg);
        }
        .cd-captcha::after {
            top: 62%;
            transform: rotate(2deg);
        }
        .cd-refresh {
            width: 2.6rem;
            height: 2.6rem;
            flex-shrink: 0;
            border-radius: 9999px;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            color: #121358;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .cd-refresh:hover {
            background: #f8fafc;
            border-color: #121358;
        }

        /* --- Tombol --- */
        .cd-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 9rem;
            padding: 0.75rem 1.6rem;
            border: none;
            border-radius: 9999px;
            background: linear-gradient(135deg, #121358 0%, #1a233a 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(18, 19, 88, 0.3);
            transition: transform 0.15s ease, box-shadow 0.2s ease;
        }
        .cd-submit:hover {
            box-shadow: 0 10px 22px rgba(18, 19, 88, 0.4);
        }
        .cd-submit:active {
            transform: scale(0.98);
        }
        .cd-submit:disabled {
            opacity: 0.7;
            cursor: wait;
        }
        .cd-ghost {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.7rem 1.3rem;
            border-radius: 9999px;
            border: 1.5px solid #121358;
            background: #ffffff;
            color: #121358;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .cd-ghost:hover {
            background: #f8fafc;
        }

        /* --- Alert --- */
        #cd-alert {
            display: none;
            gap: 0.6rem;
            align-items: flex-start;
            border-radius: 0.85rem;
            border: 1px solid #f6c9c9;
            background: #fdecec;
            padding: 0.7rem 0.9rem;
            color: #93000a;
            font-size: 13px;
            line-height: 1.4;
        }

        /* --- Panel hasil --- */
        .cd-panel {
            border-radius: 0.85rem;
            border: 1px solid #e4e9f0;
            background: #f8fafc;
            padding: 1.25rem;
            height: 100%;
        }
        #cd-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-align: center;
            min-height: 14rem;
            color: #64748b;
            font-size: 13.5px;
            border: 1.5px dashed #d1d5db;
            background: #f8fafc;
        }
        #cd-results {
            display: none;
        }
        #cd-3col.has-result #cd-placeholder {
            display: none;
        }
        #cd-3col.has-result #cd-results {
            display: block;
        }
        .cd-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.8rem;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 700;
            background: #121358;
            color: #ffffff;
            white-space: nowrap;
        }
        .cd-sample {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.15rem 0.6rem;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            background: #fbbf24;
            color: #4a3200;
        }
        .cd-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.55rem 0;
            border-bottom: 1px solid #e9eef5;
            font-size: 13.5px;
        }
        .cd-row:last-of-type {
            border-bottom: 0;
        }
        .cd-row dt {
            color: #64748b;
            flex-shrink: 0;
        }
        .cd-row dd {
            color: #121d26;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        /* --- Info desil --- */
        .cd-info {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            border-radius: 0.85rem;
            border: 1px solid #a8d5f7;
            background: #eaf4fd;
            padding: 0.9rem 1.1rem;
            color: #0c527f;
            font-size: 13.5px;
            line-height: 1.5;
        }
    </style>
@endpush

@section('content')
    {{-- ============ HERO ============ --}}
    <section class="tp-hero w-full text-white pt-32 pb-20 md:pt-40 md:pb-24">
        <div class="relative z-10 max-w-[1400px] mx-auto px-6 md:px-12 text-center flex flex-col items-center justify-center">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold tracking-wide">
                <span class="material-symbols-outlined text-[16px]">manage_search</span>
                Cek Status Bantuan Sosial
            </span>
            <h1 class="mt-5 text-3xl md:text-5xl lg:text-6xl font-bold tracking-tight max-w-4xl drop-shadow-md">
                Cek Desil
            </h1>
            <p class="mt-5 text-sm md:text-base text-gray-300 max-w-2xl font-light leading-relaxed">
                Pencarian data penerima manfaat bantuan sosial. Masukkan NIK Anda untuk melihat data kependudukan
                dan tingkat desil kesejahteraan.
            </p>
        </div>
    </section>

    {{-- ============ BREADCRUMB ============ --}}
    <div class="tp-breadcrumb-wrapper w-full">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12 py-4 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('home') }}" class="hover:text-[#121358] transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">home</span>
                Beranda
            </a>
            <span class="material-symbols-outlined text-[16px] text-gray-400">chevron_right</span>
            <span class="text-[#121358] font-bold">Cek Desil</span>
        </div>
    </div>

    {{-- ============ KONTEN: Maskot | Form | Hasil ============ --}}
    <section class="w-full py-12 md:py-16">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12">
            <div class="tp-page-card space-y-8">

                <div class="space-y-2">
                    <h2 class="text-2xl text-[#121358] font-bold leading-snug">Pencarian Berdasarkan NIK</h2>
                    <p class="text-gray-600 leading-relaxed max-w-3xl">
                        Isi NIK dan huruf kode di bawah ini, lalu tekan <strong>Cari Data</strong>.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch" id="cd-3col">

                    {{-- KOLOM 1: Maskot --}}
                    <div class="lg:col-span-3 flex items-center justify-center">
                        <img alt="Maskot Dinsos Kota Bogor"
                            class="w-40 md:w-full max-w-[220px] h-auto object-contain"
                            src="{{ asset('assets/img/rubo-mencari/rubo-mencari.png') }}" />
                    </div>

                    {{-- KOLOM 2: Form --}}
                    <div class="lg:col-span-5 space-y-4">
                        <div id="cd-alert" role="alert">
                            <span class="material-symbols-outlined text-[18px] shrink-0">error</span>
                            <span id="cd-alert-text">Hasil pencarian tidak ditemukan. Silakan periksa kembali NIK Anda.</span>
                        </div>

                        <form id="cd-form" class="space-y-4" novalidate>
                            <div class="cd-field">
                                <label for="cd-nik">NIK (Nomor Induk Kependudukan)</label>
                                <div class="cd-field-wrap">
                                    <span class="cd-field-icon material-symbols-outlined text-[18px]">badge</span>
                                    <input id="cd-nik" type="text" inputmode="numeric" maxlength="16"
                                        placeholder="Masukkan 16 digit NIK" autocomplete="off" />
                                </div>
                            </div>

                            <div class="cd-field">
                                <label for="cd-captcha-input">Huruf Kode</label>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="cd-captcha flex-1" id="cd-captcha-text" aria-live="polite">7Q2 KX</div>
                                    <button type="button" class="cd-refresh" id="cd-captcha-refresh"
                                        title="Muat ulang kode" aria-label="Muat ulang kode">
                                        <span class="material-symbols-outlined text-[20px]">refresh</span>
                                    </button>
                                </div>
                                <div class="cd-field-wrap">
                                    <span class="cd-field-icon material-symbols-outlined text-[18px]">pin</span>
                                    <input id="cd-captcha-input" type="text" placeholder="Ketik huruf kode di atas"
                                        autocomplete="off" />
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 pt-1">
                                <button type="submit" class="cd-submit" id="cd-search-btn">
                                    <span class="material-symbols-outlined text-[18px]" id="cd-search-icon">search</span>
                                    <span id="cd-search-label">Cari Data</span>
                                </button>
                                <button type="button" class="cd-ghost" id="cd-reset-btn">
                                    <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- KOLOM 3: Hasil --}}
                    <div class="lg:col-span-4" id="cd-results-wrapper">
                        <div class="cd-panel" id="cd-placeholder">
                            <span class="material-symbols-outlined text-[36px] text-gray-400">person_search</span>
                            <p>Hasil pencarian akan tampil di sini.</p>
                        </div>

                        <div class="cd-panel" id="cd-results" aria-live="polite">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-lg text-[#121358] font-bold">Hasil Cek Desil</h3>
                                <span class="cd-badge">Desil <span id="cd-desil">-</span></span>
                            </div>
                            <p class="mt-1 mb-2">
                                <span class="cd-sample">
                                    <span class="material-symbols-outlined text-[13px]">science</span>
                                    Data contoh
                                </span>
                            </p>

                            <dl>
                                <div class="cd-row"><dt>NIK</dt><dd id="cd-nik-cell">-</dd></div>
                                <div class="cd-row"><dt>Nama Penerima</dt><dd id="cd-nama">-</dd></div>
                                <div class="cd-row"><dt>Provinsi</dt><dd id="cd-provinsi">-</dd></div>
                                <div class="cd-row"><dt>Kabupaten/Kota</dt><dd id="cd-kabupaten">-</dd></div>
                                <div class="cd-row"><dt>Kecamatan</dt><dd id="cd-kecamatan">-</dd></div>
                                <div class="cd-row"><dt>Kelurahan</dt><dd id="cd-kelurahan">-</dd></div>
                            </dl>

                            <div class="pt-3 mt-1 border-t border-[#e9eef5]">
                                <p class="text-[12px] text-gray-500 mb-1">Alamat</p>
                                <p class="text-[13.5px] text-gray-800 font-semibold" id="cd-alamat">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cd-info">
                    <span class="material-symbols-outlined text-[20px] shrink-0">info</span>
                    <span>
                        <strong>Apa itu desil?</strong> Desil adalah pengelompokan rumah tangga ke dalam 10 tingkat
                        kesejahteraan. Desil 1 berarti 10% kelompok dengan kesejahteraan paling rendah, dan desil 10
                        berarti 10% kelompok dengan kesejahteraan paling tinggi.
                    </span>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        /* Logika halaman Cek Desil: captcha, tampilan hasil.
           Header, menu mobile, dan tombol melayang ditangani layout. */
        (function () {
            var codes = ["7Q2 KX", "M9F 3T", "B5N QW", "2XZ K7", "R3Q 8M"];
            var captchaEl = document.getElementById("cd-captcha-text");
            var captchaInput = document.getElementById("cd-captcha-input");
            var nikInput = document.getElementById("cd-nik");
            var alertBox = document.getElementById("cd-alert");
            var alertText = document.getElementById("cd-alert-text");
            var col3 = document.getElementById("cd-3col");
            var form = document.getElementById("cd-form");
            var searchBtn = document.getElementById("cd-search-btn");
            var searchLabel = document.getElementById("cd-search-label");
            var searchIcon = document.getElementById("cd-search-icon");
            var resetBtn = document.getElementById("cd-reset-btn");
            var refreshBtn = document.getElementById("cd-captcha-refresh");

            function randomCode() {
                return codes[Math.floor(Math.random() * codes.length)];
            }

            function resetUI() {
                alertBox.style.display = "none";
                col3.classList.remove("has-result");
            }

            function showAlert(message) {
                alertText.textContent = message;
                alertBox.style.display = "flex";
                col3.classList.remove("has-result");
            }

            /* Data dummy tunggal, dipakai untuk NIK apa pun.
               Ganti dengan hasil pencarian server (fetch ke backend) bila
               data asli sudah tersedia. Label "Data contoh" di panel hasil
               ikut dihapus saat itu. */
            var dummyData = {
                nama: "BUDI SANTOSO",
                provinsi: "JAWA BARAT",
                kabupaten: "KOTA BOGOR",
                kecamatan: "-",
                kelurahan: "-",
                alamat: "KP.BABAKAN ANYAR",
                desil: "1"
            };

            function showResults(data) {
                document.getElementById("cd-nik-cell").textContent = data.nik;
                document.getElementById("cd-nama").textContent = data.nama;
                document.getElementById("cd-provinsi").textContent = data.provinsi;
                document.getElementById("cd-kabupaten").textContent = data.kabupaten;
                document.getElementById("cd-kecamatan").textContent = data.kecamatan;
                document.getElementById("cd-kelurahan").textContent = data.kelurahan;
                document.getElementById("cd-alamat").textContent = data.alamat;
                document.getElementById("cd-desil").textContent = data.desil;

                col3.classList.add("has-result");
                document.getElementById("cd-results").scrollIntoView({ behavior: "smooth", block: "nearest" });
            }

            refreshBtn.addEventListener("click", function () {
                captchaEl.textContent = randomCode();
                captchaInput.value = "";
            });

            form.addEventListener("submit", function (event) {
                event.preventDefault();
                resetUI();

                var nik = nikInput.value.trim() || "-";

                searchLabel.textContent = "Mencari...";
                searchIcon.textContent = "progress_activity";
                searchBtn.disabled = true;

                setTimeout(function () {
                    searchLabel.textContent = "Cari Data";
                    searchIcon.textContent = "search";
                    searchBtn.disabled = false;

                    showResults({
                        nik: nik,
                        nama: dummyData.nama,
                        provinsi: dummyData.provinsi,
                        kabupaten: dummyData.kabupaten,
                        kecamatan: dummyData.kecamatan,
                        kelurahan: dummyData.kelurahan,
                        alamat: dummyData.alamat,
                        desil: dummyData.desil
                    });
                }, 700);
            });

            resetBtn.addEventListener("click", function () {
                nikInput.value = "";
                captchaInput.value = "";
                captchaEl.textContent = randomCode();
                resetUI();
            });

            resetUI();
        })();
    </script>
@endpush
