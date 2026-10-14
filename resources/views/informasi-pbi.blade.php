@extends('layouts.app')

@section('title', 'Informasi & Tutorial Pendaftaran BPJS PBI - SOLID v4 Dinas Sosial Kota Bogor')

@push('head')
    <style>
        [id] {
            scroll-margin-top: 7.5rem;
        }

        /* --- Hero --- */
        .tp-hero {
            /* Menggunakan base deep navy blue dipadu dengan gradien modern */
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
            backdrop-filter: blur(4px); /* Efek glass tipis */
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

        /* --- Breadcrumb Enhancement --- */
        .tp-breadcrumb-wrapper {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            position: relative;
            z-index: 20;
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
            color: #121358;
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
            background: #121358;
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

        /* --- Informasi (PBI / Non-PBI) --- */
        .tp-info-block {
            border-left: 3px solid #121358;
            padding-left: 1rem;
        }
        .tp-info-list {
            list-style: upper-alpha;
            padding-left: 1.4rem;
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
            color: #121358;
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
            background: #121358;
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
            background: #121358;
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

        /* --- Form pendaftaran (Langkah 1) --- */
        .tp-form-card {
            border-radius: 1rem;
            border: 1px solid #d9e3f1;
            background: linear-gradient(180deg, #f7f9ff 0%, #ffffff 60%);
            padding: 1.5rem;
            box-shadow: 0 10px 26px rgba(18, 19, 88, 0.08);
        }
        @media (min-width: 768px) {
            .tp-form-card {
                padding: 2rem 2.25rem;
            }
        }
        .tp-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #121d26;
            margin-bottom: 0.5rem;
        }
        .tp-field-wrap {
            position: relative;
        }
        .tp-field-wrap .tp-field-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #72777f;
            pointer-events: none;
            display: flex;
        }
        .tp-field input {
            width: 100%;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            border-radius: 9999px;
            padding: 0.7rem 1rem 0.7rem 2.8rem;
            font-size: 14px;
            color: #121d26;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .tp-field input::placeholder {
            color: #9aa2ab;
            font-size: 13px;
        }
        .tp-field input:focus {
            outline: none;
            border-color: #121358;
            box-shadow: 0 0 0 4px rgba(18, 19, 88, 0.12);
        }
        .tp-field-error {
            margin-top: 0.35rem;
            padding: 0 0.3rem;
            font-size: 12px;
            font-weight: 600;
            color: #ba1a1a;
        }
        .tp-alert {
            display: flex;
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
        .tp-hint {
            display: flex;
            gap: 0.6rem;
            align-items: flex-start;
            border-radius: 0.85rem;
            border: 1px solid #a8d5f7;
            background: #eaf4fd;
            padding: 0.7rem 0.9rem;
            color: #0c527f;
            font-size: 13px;
            line-height: 1.4;
        }
        .tp-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 10rem;
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
        .tp-submit:hover {
            box-shadow: 0 10px 22px rgba(18, 19, 88, 0.4);
        }
        .tp-submit:active {
            transform: scale(0.98);
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
    <section class="tp-hero w-full text-white pt-32 pb-20 md:pt-40 md:pb-24">
        <div class="relative z-10 max-w-[1400px] mx-auto px-6 md:px-12 text-center flex flex-col items-center justify-center">
            <h1 class="text-3xl md:text-5xl lg:text-6xl font-bold tracking-tight max-w-4xl drop-shadow-md">
                Informasi &amp; Tutorial Pendaftaran BPJS PBI
            </h1>
            <p class="mt-5 text-sm md:text-base text-gray-300 max-w-2xl font-light leading-relaxed">
                Panduan lengkap langkah demi langkah untuk pendaftaran dan aktivasi akun kepesertaan BPJS Kesehatan PBI APBD Kota Bogor.
            </p>
        </div>
    </section>

    {{-- ============ BREADCRUMB ============ --}}
    <div class="tp-breadcrumb-wrapper w-full">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12 py-4 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('home') }}" class="hover:text-[#121358] transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">home</span>
                Home
            </a>
            <span class="material-symbols-outlined text-[16px] text-gray-400">chevron_right</span>
            <span class="text-[#121358] font-bold">Informasi &amp; Tutorial Pendaftaran BPJS PBI</span>
        </div>
    </div>

    {{-- ============ KONTEN UTAMA ============ --}}
    <section class="w-full py-12 md:py-16">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12">
            <div class="tp-page-card grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- Sidebar Daftar Isi (sticky, desktop) --}}
                <aside class="tp-toc lg:col-span-3 sticky top-28 space-y-4">
                    <div class="bg-[#f8fafc] rounded-xl border border-gray-200 p-4 space-y-1">
                        <a class="tp-toc-link" data-toc href="#informasi"><span class="tp-toc-dot"></span>Informasi BPJS Kesehatan</a>
                        <a class="tp-toc-link" data-toc href="#dokumen"><span class="tp-toc-dot"></span>Dokumen yang Disiapkan</a>
                        <a class="tp-toc-link" data-toc href="#buka-akun"><span class="tp-toc-dot"></span>Membuka Formulir Akun</a>
                        <a class="tp-toc-link" data-toc href="#lengkapi-data"><span class="tp-toc-dot"></span>Melengkapi Data &amp; Aktivasi</a>
                        <a class="tp-toc-link" data-toc href="#form-pendaftaran"><span class="tp-toc-dot"></span>Form Pendaftaran</a>
                    </div>
                    <a href="#form-pendaftaran"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-[#121358] hover:bg-[#1a233a] text-white font-semibold text-sm transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">how_to_reg</span>
                        <span>Daftar Akun Sekarang</span>
                    </a>
                </aside>

                {{-- Isi halaman --}}
                <div class="lg:col-span-9 space-y-12">

                    {{-- ================= INFORMASI JAMINAN / ASURANSI KESEHATAN ================= --}}
                    <div id="informasi" class="space-y-4">
                        <h2 class="text-2xl text-[#121358] font-bold leading-snug">
                            Informasi Jaminan / Asuransi Kesehatan
                        </h2>

                        <p class="text-gray-600 leading-relaxed text-justify">
                            BPJS Kesehatan yang berfokus dalam memberikan jaminan kesehatan bagi seluruh warga negara
                            Indonesia (WNI) dan juga warga negara asing yang sudah tinggal minimal 6 bulan. Awalnya
                            sebagai orang mengenal Askes, Jamkesmas, Jamkesda dan Jaminan Pemeliharaan Kesehatan, namun
                            semua program tersebut sudah tidak ada dan sudah diubah menjadi BPJS Kesehatan. Untuk
                            kepesertaan BPJS Kesehatan dibagi menjadi dua kelompok, yaitu
                            <strong>Peserta Penerima Bantuan Iuran (PBI)</strong> dan
                            <strong>Peserta Bukan Penerima Bantuan Iuran (Non-PBI)</strong>.
                        </p>

                        <div class="tp-info-block space-y-2">
                            <h3 class="text-lg text-[#121358] font-bold">
                                1. Peserta Penerima Bantuan Iuran (PBI)
                            </h3>
                            <p class="text-gray-600 leading-relaxed text-justify">
                                Peserta BPJS PBI meliputi orang yang tergolong fakir miskin dan orang tidak mampu menurut
                                data dari Dinas Sosial. Untuk biaya iuran bulanan tidak dibebani ke peserta BPJS PBI
                                melainkan ditanggung sepenuhnya oleh pemerintah.
                            </p>
                        </div>

                        <div class="tp-info-block space-y-2">
                            <h3 class="text-lg text-[#121358] font-bold">
                                2. Peserta Bukan Penerima Bantuan Iuran (Non-PBI)
                            </h3>
                            <p class="text-gray-600 leading-relaxed text-justify">
                                Sedangkan untuk peserta BPJS Non-PBI merupakan peserta yang tidak tergolong fakir miskin
                                dan orang tidak mampu. Jika BPJS-PBI biaya per bulan ditanggung oleh pemerintah, maka
                                jenis kepesertaan BPJS Kesehatan ini berkewajiban untuk membayar iuran bulanan sendiri.
                                Ini dikarenakan peserta dianggap mampu membayar iuran dan tidak termasuk fakir miskin
                                atau orang tidak mampu. Peserta BPJS Non-PBI dibagi lagi menjadi 3 yaitu:
                            </p>
                            <ol class="tp-info-list text-gray-600 leading-relaxed space-y-1">
                                <li>Pekerja Penerima Upah dan anggota keluarganya.</li>
                                <li>Pekerja Bukan Penerima Upah dan anggota keluarganya, terdiri atas pekerja di luar
                                    hubungan kerja dan pekerja mandiri.</li>
                                <li>Bukan Pekerja dan anggota keluarganya.</li>
                            </ol>
                        </div>
                    </div>

                    {{-- ================= TUTORIAL ================= --}}
                    <div class="space-y-2 max-w-3xl">
                        <h2 class="text-2xl text-[#121358] font-bold leading-snug">
                            PANDUAN PENDAFTARAN AKUN PELAYANAN SOSIAL JAMINAN KESEHATAN PENERIMA BANTUAN IURAN DINAS
                            SOSIAL KOTA BOGOR
                        </h2>
                    </div>

                    {{-- A. Dokumen --}}
                    <div id="dokumen" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="tp-step-num" style="width: 2.25rem; height: 2.25rem; font-size: 13px">A</span>
                            <h3 class="text-xl text-[#121358] font-bold">
                                Dokumen yang Perlu Disiapkan
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="tp-doc-card">
                                <span class="tp-doc-icon"><span class="material-symbols-outlined text-[22px]">badge</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-sm">Scan KTP</p>
                                    <p class="text-gray-500 text-xs">Khusus warga ber-KTP Kota Bogor.</p>
                                </div>
                            </div>
                            <div class="tp-doc-card">
                                <span class="tp-doc-icon"><span class="material-symbols-outlined text-[22px]">groups</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-sm">Scan Kartu Keluarga</p>
                                    <p class="text-gray-500 text-xs">Kartu Keluarga yang masih berlaku.</p>
                                </div>
                            </div>
                            <div class="tp-doc-card">
                                <span class="tp-doc-icon"><span class="material-symbols-outlined text-[22px]">mail</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-sm">Alamat E-Mail</p>
                                    <p class="text-gray-500 text-xs">Email pribadi yang aktif digunakan.</p>
                                </div>
                            </div>
                            <div class="tp-doc-card">
                                <span class="tp-doc-icon"><span class="material-symbols-outlined text-[22px]">call</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-sm">Nomor Telepon</p>
                                    <p class="text-gray-500 text-xs">Nomor aktif dan dapat dihubungi.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- B. Membuka formulir akun --}}
                    <div id="buka-akun" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="tp-step-num" style="width: 2.25rem; height: 2.25rem; font-size: 13px">B</span>
                            <h3 class="text-xl text-[#121358] font-bold">
                                Bagaimana Cara Mendaftar / Memperoleh Akun?
                            </h3>
                        </div>

                        <div class="bg-[#f8fafc] rounded-xl border border-gray-200 p-6">
                            <div class="tp-steps">
                                <div class="tp-step">
                                    <span class="tp-step-num">1</span>
                                    <div class="tp-step-body">
                                        <p>
                                            Silakan mengisi form pendaftaran pada bagian paling bawah halaman ini:
                                            <a href="#form-pendaftaran" class="tp-chip">
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
                                            Klik tulisan <span class="tp-chip tp-chip-blue">Register</span> pada halaman yang terbuka.
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
                            <ol class="list-decimal list-inside space-y-1 text-sm">
                                <li>Jika NIK calon peserta <strong>terdaftar</strong> di Disdukcapil, calon peserta bisa melanjutkan pendaftaran dan mengisi form berikutnya.</li>
                                <li>Jika NIK calon peserta <strong>tidak ditemukan</strong>, calon peserta harus mengurus NIK / Data Kependudukan ke Disdukcapil terlebih dahulu.</li>
                            </ol>
                        </div>

                        <div class="tp-note">
                            <p class="tp-note-title">
                                <span class="material-symbols-outlined text-[20px]">checklist</span>
                                Catatan #2
                            </p>
                            <ol class="list-decimal list-inside space-y-1 text-sm">
                                <li>KTP menjadi parameter Nomor Induk Kependudukan berdomisili di Kota Bogor.</li>
                                <li>Nomor HP diisi dengan nomor yang saat mendaftar masih aktif dan dapat dihubungi.</li>
                                <li>E-mail diisi dengan email pribadi yang saat ini digunakan untuk menerima informasi aktivasi akun.</li>
                            </ol>
                        </div>
                    </div>

                    {{-- C. Melengkapi data & aktivasi --}}
                    <div id="lengkapi-data" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="tp-step-num" style="width: 2.25rem; height: 2.25rem; font-size: 13px">C</span>
                            <h3 class="text-xl text-[#121358] font-bold">
                                Melengkapi Data &amp; Aktivasi Akun
                            </h3>
                        </div>

                        <div class="bg-[#f8fafc] rounded-xl border border-gray-200 p-6">
                            <div class="tp-steps">
                                <div class="tp-step">
                                    <span class="tp-step-num">1</span>
                                    <div class="tp-step-body"><p>Lengkapi data calon peserta.</p></div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">2</span>
                                    <div class="tp-step-body"><p>Isi password akun.</p></div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">3</span>
                                    <div class="tp-step-body"><p>Ulangi password pertama untuk konfirmasi.</p></div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">4</span>
                                    <div class="tp-step-body"><p>Isi nomor telepon aktif calon peserta.</p></div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">5</span>
                                    <div class="tp-step-body"><p>Isi e-mail aktif calon peserta untuk keperluan aktivasi akun.</p></div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">6</span>
                                    <div class="tp-step-body"><p>Periksa kembali data Anda sebagai calon peserta BPJS PBI APBD Kota Bogor.</p></div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">7</span>
                                    <div class="tp-step-body">
                                        <p>Jika sudah yakin dengan data yang diisi, klik / tekan tombol <span class="tp-chip tp-chip-blue">Submit</span>.</p>
                                    </div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">8</span>
                                    <div class="tp-step-body"><p>Periksa dan buka e-mail masuk dari Pelayanan Sosial.</p></div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">9</span>
                                    <div class="tp-step-body"><p>Klik / tekan link aktivasi akun yang dikirim oleh sistem.</p></div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">10</span>
                                    <div class="tp-step-body"><p>Jika berhasil, aktivasi akun peserta selesai dibuat.</p></div>
                                </div>
                                <div class="tp-step">
                                    <span class="tp-step-num">11</span>
                                    <div class="tp-step-body">
                                        <p>Anda akan diarahkan ke halaman <span class="tp-chip tp-chip-blue">Login</span> untuk mulai menggunakan akun.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= FORM PENDAFTARAN (LANGKAH 1) ================= --}}
                    <div id="form-pendaftaran" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="tp-step-num" style="width: 2.25rem; height: 2.25rem">
                                <span class="material-symbols-outlined text-[18px]">how_to_reg</span>
                            </span>
                            <h3 class="text-xl text-[#121358] font-bold">
                                Form Pendaftaran Akun
                            </h3>
                        </div>

                        <div class="tp-form-card space-y-4">
                            <div class="space-y-1">
                                <h4 class="text-lg text-gray-800 font-bold">
                                    Silakan Masukkan Data Diri Anda
                                </h4>
                                <p class="text-sm text-gray-500">
                                    Cek Nomor KK Anda untuk memulai pendaftaran BPJS PBI.
                                </p>
                            </div>

                            <div class="tp-hint">
                                <span class="material-symbols-outlined text-[18px] shrink-0">verified_user</span>
                                <span>Sebelum mendaftar, pastikan Anda belum terdaftar sebagai peserta JKN PBI / SPMB.</span>
                            </div>

                            @if (isset($captchaA) && isset($captchaB))
                                <form id="cek-kk-form" method="POST" action="{{ route('registrasi.step1.store') }}" novalidate class="space-y-4 max-w-xl">
                                    @csrf

                                    @if ($errors->any())
                                        <div class="tp-alert">
                                            <span class="material-symbols-outlined text-[18px] shrink-0">error</span>
                                            <span>{{ $errors->first() }}</span>
                                        </div>
                                    @endif

                                    <div class="tp-field">
                                        <label for="no-kk">No KK (Kartu Keluarga)</label>
                                        <div class="tp-field-wrap">
                                            <span class="tp-field-icon material-symbols-outlined text-[18px]">credit_card</span>
                                            <input id="no-kk" name="no_kk" type="text" inputmode="numeric" placeholder="Masukkan No KK (Kartu Keluarga) Anda" autocomplete="off" value="{{ old('no_kk') }}" maxlength="16" />
                                        </div>
                                        @error('no_kk')
                                            <p class="tp-field-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="tp-field">
                                        <label for="captcha">Berapa <span>{{ $captchaA }}</span> + <span>{{ $captchaB }}</span> ?</label>
                                        <div class="tp-field-wrap">
                                            <span class="tp-field-icon material-symbols-outlined text-[18px]">quiz</span>
                                            <input id="captcha" name="captcha" type="text" inputmode="numeric" placeholder="Masukkan jawaban" autocomplete="off" value="{{ old('captcha') }}" />
                                        </div>
                                        @error('captcha')
                                            <p class="tp-field-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <button type="submit" class="tp-submit">
                                        Next
                                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('registrasi.step1') }}" class="tp-submit">
                                    Mulai Pendaftaran
                                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                </a>
                            @endif

                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-4 border-t border-gray-200">
                                <span class="text-sm text-gray-500">Sudah mempunyai akun?</span>
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-1 w-fit px-4 py-2 rounded-full border-[1.5px] border-[#121358] text-[#121358] text-sm font-bold hover:bg-[#f8fafc] transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">login</span>
                                    Login
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection

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

    {{-- Setelah submit gagal (validasi), halaman dimuat ulang: langsung gulir ke form --}}
    @if ($errors->any())
        <script>
            (function () {
                var el = document.getElementById("form-pendaftaran");
                if (el) el.scrollIntoView({ behavior: "auto", block: "start" });
            })();
        </script>
    @endif
@endpush
