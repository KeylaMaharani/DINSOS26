@extends('layouts.app')

@section('title', 'Pendaftaran LKS - SOLID v4 Dinas Sosial Kota Bogor')

@push('head')
    <style>
        [id] {
            scroll-margin-top: 7.5rem;
        }

        /* --- Hero (sama dengan halaman lain) --- */
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
        .tp-hero::before { width: 32rem; height: 32rem; right: -12rem; top: -14rem; }
        .tp-hero::after { width: 18rem; height: 18rem; left: -6rem; bottom: -8rem; }

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
            .tp-page-card { padding: 2rem; }
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
        @media (max-width: 1023px) {
            .tp-toc { display: none; }
        }

        /* --- Ringkasan angka --- */
        .lk-stat {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 1rem 1.1rem;
            border-radius: 0.85rem;
            background: #f8fafc;
            border: 1px solid #e4e9f0;
        }
        .lk-icon {
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

        /* --- Penanda bagian (A, B, C) --- */
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
            flex-shrink: 0;
        }

        /* --- Diagram alur --- */
        .lk-flow {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.25rem;
        }
        .lk-node {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.9rem 1.1rem;
            border-radius: 0.85rem;
            background: #ffffff;
            border: 1.5px solid #e4e9f0;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }
        .lk-node:hover {
            border-color: #98cbff;
            transform: translateY(-2px);
        }
        .lk-node-num {
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            background: #121358;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .lk-node-title {
            font-weight: 700;
            font-size: 14px;
            color: #121358;
        }
        .lk-node-sub {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 0.1rem;
        }
        .lk-arrow {
            display: flex;
            justify-content: center;
            color: #94a3b8;
            line-height: 1;
        }
        .lk-node.is-start {
            background: #121358;
            border-color: #121358;
        }
        .lk-node.is-start .lk-node-title { color: #fff; }
        .lk-node.is-start .lk-node-sub { color: #cbd5e1; }
        .lk-node.is-start .lk-node-num { background: rgba(255, 255, 255, 0.16); }

        .lk-decision {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        @media (min-width: 768px) {
            .lk-decision { grid-template-columns: 1fr 1fr; }
        }
        .lk-outcome {
            border-radius: 0.85rem;
            padding: 1.1rem 1.25rem;
            border: 2px solid;
        }
        .lk-outcome.ok { border-color: #86c98f; background: #f1faf2; }
        .lk-outcome.no { border-color: #e59a9a; background: #fdf2f2; }
        .lk-outcome-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 800;
            font-size: 15px;
        }
        .lk-outcome.ok .lk-outcome-title { color: #146c2e; }
        .lk-outcome.no .lk-outcome-title { color: #93000a; }
        .lk-outcome ul {
            margin-top: 0.6rem;
            padding-left: 1.1rem;
            list-style: disc;
            font-size: 13.5px;
            color: #42474e;
            line-height: 1.55;
        }

        /* --- Stepper --- */
        .tp-step {
            position: relative;
            display: grid;
            grid-template-columns: 2.5rem 1fr;
            gap: 1rem;
            padding-bottom: 1.5rem;
        }
        .tp-step:last-child { padding-bottom: 0; }
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
        .tp-step:last-child::before { display: none; }
        .tp-step-body { padding-top: 0.3rem; }
        .tp-step-body p { color: #42474e; }

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
            text-decoration: none;
        }
        .tp-chip.tp-chip-blue { background: #121358; color: #ffffff; }

        /* --- Kartu bagian formulir --- */
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
        .lk-part {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: #64748b;
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
        .tp-note ol,
        .tp-note ul { color: #6b5417; }

        /* --- CTA --- */
        .lk-cta {
            border-radius: 1rem;
            border: 1px solid #d9e3f1;
            background: linear-gradient(180deg, #f7f9ff 0%, #ffffff 60%);
            padding: 1.5rem;
            box-shadow: 0 10px 26px rgba(18, 19, 88, 0.08);
        }
        @media (min-width: 768px) {
            .lk-cta { padding: 2rem 2.25rem; }
        }
        .lk-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.6rem;
            border: none;
            border-radius: 9999px;
            background: linear-gradient(135deg, #121358 0%, #1a233a 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            box-shadow: 0 8px 18px rgba(18, 19, 88, 0.3);
            transition: transform 0.15s ease, box-shadow 0.2s ease;
        }
        .lk-btn:hover { box-shadow: 0 10px 22px rgba(18, 19, 88, 0.4); }
        .lk-btn:active { transform: scale(0.98); }
        .lk-btn-outline {
            background: #ffffff;
            color: #121358;
            border: 1.5px solid #121358;
            box-shadow: none;
        }
        .lk-btn-outline:hover { background: #f8fafc; box-shadow: none; }
    </style>
@endpush

@section('content')
    @php
        /*
         | Ganti URL ini dengan alamat situs pendaftaran LKS resmi Kemensos
         | (atau kirim dari controller sebagai $lksUrl).
         */
        $lksUrl = $lksUrl ?? '#';
        $lksExternal = $lksUrl !== '#';

        $bagianFormulir = [
            ['I', 'Data Umum LKS', 'Informasi dasar lembaga.', 'apartment'],
            ['II', 'Identitas / Jati Diri LKS', 'Profil dan jati diri lembaga.', 'badge'],
            ['III', 'Legalitas LKS', 'Akta, izin, dan dokumen legalitas.', 'gavel'],
            ['IV', 'Program dan Kegiatan LKS', 'Program pelayanan yang dijalankan.', 'event_note'],
            ['V', 'Sumber Daya LKS', 'SDM, sarana, dan pembiayaan.', 'inventory_2'],
            ['VI', 'Jejaring Kerja LKS', 'Kemitraan dan jaringan kerja.', 'hub'],
            ['VII', 'Kelengkapan Dokumen Pendukung', 'Berkas penunjang pendaftaran.', 'folder_open'],
        ];

        $alurOnline = [
            ['Buka laman milik Kementerian Sosial', 'Pendaftar membuka situs resmi sebagai titik awal.'],
            ['Unduh pedoman dan formulir pendaftaran LKS', 'Pelajari pedoman dan formulir terlebih dahulu.'],
            ['Penuhi kelengkapan pendaftaran LKS', 'Siapkan dokumen pendukung sesuai persyaratan.'],
            ['Isi formulir pendaftaran melalui daring / online', 'Isi formulir yang terdiri atas 7 bagian.'],
            ['Diperoleh nomor urut pendaftaran secara online', 'Sistem memberikan nomor urut setelah formulir terkirim.'],
            ['Unggah dokumen', 'Surat permohonan, berkas formulir pendaftaran, serta dokumen kelengkapannya.'],
            ['Kemensos / instansi / Dinas Sosial sesuai kewenangan', 'Petugas menerima berkas untuk diproses.'],
            ['Validasi data dan berkas pendaftaran LKS secara daring / online', 'Pemeriksaan kebenaran dan kelengkapan data.'],
        ];

        $langkah = [
            'Pendaftar memperoleh panduan dan formulir pendaftaran LKS dari laman milik Kementerian Sosial untuk dipelajari terlebih dahulu.',
            'Persiapkan dokumen pendukung seperti yang diminta dalam persyaratan pendaftaran LKS.',
            'Buka situs pendaftaran LKS, kemudian pendaftar diminta mengisi data identitas terlebih dahulu.',
            'Apabila pengisian data identitas sudah lengkap, pendaftar akan menerima kata sandi.',
            'Isi formulir pendaftaran LKS yang terdiri atas 7 (tujuh) bagian (lihat bagian C).',
            'Jawab pernyataan pada setiap bagian dengan benar. Jika ada pertanyaan yang belum diisi, bagian berikutnya tidak dapat dibuka.',
            'Setelah semua bagian terisi dengan baik dan benar, kirimkan formulir pendaftaran LKS secara dalam jaringan / online.',
            'Pendaftar menerima notifikasi bahwa permohonan sudah berhasil dilakukan.',
            'Petugas Dinas Sosial yang menyelenggarakan pelayanan perizinan provinsi/kabupaten/kota setempat memvalidasi dokumen LKS sesuai kewenangannya.',
            'Dinas Sosial memproses pendaftaran pada e-PSKS untuk mendapatkan nomor induk LKS dari Kemensos.',
            'Kementerian Sosial menerbitkan nomor induk LKS.',
            'Dinas Sosial menerbitkan tanda pendaftaran LKS sesuai kewenangan.',
            'Pendaftar dapat mengunduh tanda pendaftaran LKS.',
        ];
    @endphp

    {{-- ============ HERO ============ --}}
    <section class="tp-hero w-full text-white pt-32 pb-20 md:pt-40 md:pb-24">
        <div class="relative z-10 max-w-[1400px] mx-auto px-6 md:px-12 text-center flex flex-col items-center justify-center">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold tracking-wide">
                <span class="material-symbols-outlined text-[16px]">apartment</span>
                Lembaga Kesejahteraan Sosial
            </span>
            <h1 class="mt-5 text-3xl md:text-5xl lg:text-6xl font-bold tracking-tight max-w-4xl drop-shadow-md">
                Pendaftaran LKS
            </h1>
            <p class="mt-5 text-sm md:text-base text-gray-300 max-w-2xl font-light leading-relaxed">
                Panduan alur dan prosedur pendaftaran Lembaga Kesejahteraan Sosial (LKS) secara dalam jaringan / online,
                dari pengisian formulir hingga terbitnya tanda pendaftaran dan nomor induk LKS.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row items-center gap-3">
                <a href="#panduan" class="lk-btn">
                    <span class="material-symbols-outlined text-[18px]">menu_book</span>
                    Baca Panduan
                </a>
                <a href="#daftar" class="lk-btn lk-btn-outline">
                    <span class="material-symbols-outlined text-[18px]">how_to_reg</span>
                    Mulai Pendaftaran
                </a>
            </div>
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
            <span class="text-[#121358] font-bold">LKS</span>
        </div>
    </div>

    {{-- ============ KONTEN UTAMA ============ --}}
    <section class="w-full py-12 md:py-16">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12">
            <div class="tp-page-card grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- Sidebar Daftar Isi --}}
                <aside class="tp-toc lg:col-span-3 sticky top-28 space-y-4">
                    <div class="bg-[#f8fafc] rounded-xl border border-gray-200 p-4 space-y-1">
                        <a class="tp-toc-link" data-toc href="#panduan"><span class="tp-toc-dot"></span>Tentang Pendaftaran LKS</a>
                        <a class="tp-toc-link" data-toc href="#alur"><span class="tp-toc-dot"></span>Alur Pendaftaran Online</a>
                        <a class="tp-toc-link" data-toc href="#mekanisme"><span class="tp-toc-dot"></span>Mekanisme Pendaftaran</a>
                        <a class="tp-toc-link" data-toc href="#formulir"><span class="tp-toc-dot"></span>Bagian Formulir</a>
                        <a class="tp-toc-link" data-toc href="#petunjuk"><span class="tp-toc-dot"></span>Petunjuk Pengisian</a>
                        <a class="tp-toc-link" data-toc href="#daftar"><span class="tp-toc-dot"></span>Mulai Pendaftaran</a>
                    </div>
                    <a href="#daftar"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-[#121358] hover:bg-[#1a233a] text-white font-semibold text-sm transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">how_to_reg</span>
                        <span>Daftar LKS Sekarang</span>
                    </a>
                </aside>

                {{-- Isi halaman --}}
                <div class="lg:col-span-9 space-y-12">

                    {{-- ================= TENTANG ================= --}}
                    <div id="panduan" class="space-y-4">
                        <h2 class="text-2xl text-[#121358] font-bold leading-snug">
                            Panduan Pendaftaran LKS
                        </h2>
                        <p class="text-gray-600 leading-relaxed text-justify">
                            Lembaga Kesejahteraan Sosial (LKS) yang menyelenggarakan pelayanan kesejahteraan sosial wajib
                            terdaftar agar tercatat secara resmi dan memperoleh <strong>nomor induk LKS</strong> dari
                            Kementerian Sosial. Pendaftaran dilakukan secara dalam jaringan / online dan divalidasi oleh
                            Dinas Sosial sesuai kewenangannya.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="lk-stat">
                                <span class="lk-icon"><span class="material-symbols-outlined text-[22px]">cloud_done</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-lg leading-none">Online</p>
                                    <p class="text-gray-500 text-xs mt-1">Pendaftaran daring</p>
                                </div>
                            </div>
                            <div class="lk-stat">
                                <span class="lk-icon"><span class="material-symbols-outlined text-[22px]">format_list_numbered</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-lg leading-none">{{ count($langkah) }}</p>
                                    <p class="text-gray-500 text-xs mt-1">Langkah proses</p>
                                </div>
                            </div>
                            <div class="lk-stat">
                                <span class="lk-icon"><span class="material-symbols-outlined text-[22px]">description</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-lg leading-none">{{ count($bagianFormulir) }}</p>
                                    <p class="text-gray-500 text-xs mt-1">Bagian formulir</p>
                                </div>
                            </div>
                            <div class="lk-stat">
                                <span class="lk-icon"><span class="material-symbols-outlined text-[22px]">workspace_premium</span></span>
                                <div>
                                    <p class="text-gray-800 font-bold text-lg leading-none">Nomor Induk</p>
                                    <p class="text-gray-500 text-xs mt-1">Diterbitkan Kemensos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= A. ALUR ================= --}}
                    <div id="alur" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="tp-step-num" style="width: 2.25rem; height: 2.25rem; font-size: 13px">A</span>
                            <h3 class="text-xl text-[#121358] font-bold">Alur Pendaftaran dalam Jaringan / Online</h3>
                        </div>

                        <div class="bg-[#f8fafc] rounded-xl border border-gray-200 p-5 md:p-6 space-y-2">
                            <div class="lk-flow">
                                @foreach ($alurOnline as $i => $n)
                                    <div class="lk-node {{ $loop->first ? 'is-start' : '' }}">
                                        <span class="lk-node-num">{{ $i + 1 }}</span>
                                        <div>
                                            <p class="lk-node-title">{{ $n[0] }}</p>
                                            <p class="lk-node-sub">{{ $n[1] }}</p>
                                        </div>
                                    </div>
                                    <div class="lk-arrow"><span class="material-symbols-outlined text-[22px]">arrow_downward</span></div>
                                @endforeach
                            </div>

                            {{-- Hasil validasi --}}
                            <p class="text-sm font-bold text-gray-800 pt-1">Hasil validasi</p>
                            <div class="lk-decision">
                                <div class="lk-outcome ok">
                                    <p class="lk-outcome-title">
                                        <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                        Permohonan Disetujui
                                    </p>
                                    <ul>
                                        <li>Diterbitkan tanda pendaftaran serta nomor induk LKS.</li>
                                        <li>Pendaftar mengunduh tanda pendaftaran LKS.</li>
                                    </ul>
                                </div>
                                <div class="lk-outcome no">
                                    <p class="lk-outcome-title">
                                        <span class="material-symbols-outlined text-[20px]">cancel</span>
                                        Permohonan Ditolak
                                    </p>
                                    <ul>
                                        <li>Permohonan dikembalikan kepada LKS pemohon.</li>
                                        <li>Perbaiki data atau berkas yang tidak sesuai, lalu ajukan kembali.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= B. MEKANISME ================= --}}
                    <div id="mekanisme" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="tp-step-num" style="width: 2.25rem; height: 2.25rem; font-size: 13px">B</span>
                            <h3 class="text-xl text-[#121358] font-bold">Mekanisme Pendaftaran</h3>
                        </div>

                        <div class="bg-[#f8fafc] rounded-xl border border-gray-200 p-6">
                            @foreach ($langkah as $teks)
                                <div class="tp-step">
                                    <span class="tp-step-num">{{ $loop->iteration }}</span>
                                    <div class="tp-step-body"><p>{{ $teks }}</p></div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ================= C. BAGIAN FORMULIR ================= --}}
                    <div id="formulir" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="tp-step-num" style="width: 2.25rem; height: 2.25rem; font-size: 13px">C</span>
                            <h3 class="text-xl text-[#121358] font-bold">Formulir Pendaftaran Terdiri atas 7 Bagian</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($bagianFormulir as $b)
                                <div class="tp-doc-card">
                                    <span class="lk-icon"><span class="material-symbols-outlined text-[22px]">{{ $b[3] }}</span></span>
                                    <div>
                                        <p class="lk-part">BAGIAN {{ $b[0] }}</p>
                                        <p class="text-gray-800 font-bold text-sm">{{ $b[1] }}</p>
                                        <p class="text-gray-500 text-xs">{{ $b[2] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="tp-note">
                            <p class="tp-note-title">
                                <span class="material-symbols-outlined text-[20px]">info</span>
                                Catatan Pengisian Bagian
                            </p>
                            <ol class="list-decimal list-inside space-y-1 text-sm">
                                <li>Semua pertanyaan harus dijawab / diisi dengan benar.</li>
                                <li>Apabila dalam satu bagian ada pertanyaan yang belum diisi, pendaftar tidak dapat membuka bagian berikutnya.</li>
                            </ol>
                        </div>
                    </div>

                    {{-- ================= D. PETUNJUK PENGISIAN ================= --}}
                    <div id="petunjuk" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="tp-step-num" style="width: 2.25rem; height: 2.25rem; font-size: 13px">D</span>
                            <h3 class="text-xl text-[#121358] font-bold">Petunjuk Pengisian Formulir Pendaftaran LKS</h3>
                        </div>

                        <div class="bg-[#f8fafc] rounded-xl border border-gray-200 p-6">
                            @foreach ([
                                'Pengisian formulir registrasi merupakan asupan berupa data yang menggambarkan profil dan kondisi LKS, serta menjadi bahan pertimbangan dalam penentuan status dan jangkauan layanan LKS.',
                                'Untuk menjawab pertanyaan dan membuat pernyataan, isilah pada kolom yang disediakan.',
                                'Jika pertanyaan / pernyataan bersifat pilihan, pilih jawaban yang sesuai dengan membubuhkan tanda silang [X] pada kolom yang disediakan.',
                                'Jawaban dapat lebih dari satu, sesuai dengan kondisi LKS.',
                                'Apabila kolom jawaban yang disediakan kurang, jawaban dapat ditulis pada lembar kertas lain dan disertakan sebagai lampiran.',
                                'Periksa ulang seluruh isian untuk memastikan semua pertanyaan telah dijawab lengkap dan data yang disampaikan sudah benar.',
                            ] as $p)
                                <div class="tp-step">
                                    <span class="tp-step-num">{{ $loop->iteration }}</span>
                                    <div class="tp-step-body"><p>{{ $p }}</p></div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ================= CTA ================= --}}
                    <div id="daftar" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="tp-step-num" style="width: 2.25rem; height: 2.25rem">
                                <span class="material-symbols-outlined text-[18px]">how_to_reg</span>
                            </span>
                            <h3 class="text-xl text-[#121358] font-bold">Mulai Pendaftaran LKS</h3>
                        </div>

                        <div class="lk-cta space-y-4">
                            <div class="space-y-1">
                                <h4 class="text-lg text-gray-800 font-bold">Siap mendaftarkan LKS Anda?</h4>
                                <p class="text-sm text-gray-500">
                                    Pelajari pedoman dan siapkan dokumen pendukung, lalu lanjutkan ke situs pendaftaran LKS.
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <a href="{{ $lksUrl }}" class="lk-btn"
                                    @if ($lksExternal) target="_blank" rel="noopener noreferrer" @endif>
                                    Buka Situs Pendaftaran LKS
                                    <span class="material-symbols-outlined text-[18px]">{{ $lksExternal ? 'open_in_new' : 'arrow_forward' }}</span>
                                </a>
                                <a href="{{ route('kontak') }}" class="lk-btn lk-btn-outline">
                                    <span class="material-symbols-outlined text-[18px]">support_agent</span>
                                    Hubungi Dinas Sosial
                                </a>
                            </div>

                            <div class="flex items-center gap-2 pt-4 border-t border-gray-200 text-sm text-gray-500">
                                <span class="material-symbols-outlined text-[18px]">help</span>
                                Ada kendala saat mendaftar? Petugas Dinas Sosial Kota Bogor siap membantu.
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
@endpush