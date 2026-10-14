@extends('layouts.app')

@section('title', 'Hubungi Kami - SOLID v4 Dinas Sosial Kota Bogor')

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
        .kt-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #121d26;
            margin-bottom: 0.5rem;
        }
        .kt-field-wrap {
            position: relative;
        }
        .kt-field-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #72777f;
            pointer-events: none;
            display: flex;
        }
        .kt-field-wrap.is-area .kt-field-icon {
            top: 1.1rem;
            transform: none;
        }
        .kt-field input,
        .kt-field textarea {
            width: 100%;
            border: 1.5px solid #e4e9f0;
            background: #ffffff;
            border-radius: 9999px;
            padding: 0.7rem 1rem 0.7rem 2.8rem;
            font-size: 14px;
            color: #121d26;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .kt-field textarea {
            border-radius: 1.1rem;
            min-height: 9rem;
            resize: vertical;
            line-height: 1.5;
        }
        .kt-field input::placeholder,
        .kt-field textarea::placeholder {
            color: #9aa2ab;
            font-size: 13px;
        }
        .kt-field input:focus,
        .kt-field textarea:focus {
            outline: none;
            border-color: #121358;
            box-shadow: 0 0 0 4px rgba(18, 19, 88, 0.12);
        }
        .kt-error {
            margin-top: 0.35rem;
            padding: 0 0.3rem;
            font-size: 12px;
            font-weight: 600;
            color: #ba1a1a;
        }
        .kt-alert {
            display: flex;
            gap: 0.6rem;
            align-items: flex-start;
            border-radius: 0.85rem;
            padding: 0.7rem 0.9rem;
            font-size: 13px;
            line-height: 1.4;
        }
        .kt-alert-error {
            border: 1px solid #f6c9c9;
            background: #fdecec;
            color: #93000a;
        }
        .kt-alert-ok {
            border: 1px solid #b7e4c7;
            background: #ecfaf1;
            color: #14532d;
        }
        .kt-submit {
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
        .kt-submit:hover {
            box-shadow: 0 10px 22px rgba(18, 19, 88, 0.4);
        }
        .kt-submit:active {
            transform: scale(0.98);
        }

        /* --- Info kontak --- */
        .kt-info-card {
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
            padding: 1rem 1.1rem;
            border-radius: 0.85rem;
            background: #f8fafc;
            border: 1px solid #e4e9f0;
        }
        .kt-info-icon {
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
        .kt-social {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.95rem;
            border-radius: 9999px;
            border: 1.5px solid #121358;
            color: #121358;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .kt-social:hover {
            background: #121358;
            color: #ffffff;
        }
        .kt-map {
            width: 100%;
            height: 16rem;
            border: 0;
            border-radius: 0.85rem;
        }
    </style>
@endpush

@section('content')
    @php
        // Data kontak. Ubah di sini bila ada perubahan.
        $kontak = [
            'alamat' => 'Jl. Merdeka No.142, RT.03/RW.05, Ciwaringin, Kecamatan Bogor Tengah, Kota Bogor, Jawa Barat 16125',
            'email' => 'dinsoskotabogor@gmail.com',
            'telepon' => '(+62) 85333395667',
            'telepon_link' => '+6285333395667',
            'facebook' => '#',
            'instagram' => '#',
            'youtube' => '#',
        ];
    @endphp

    {{-- ============ HERO ============ --}}
    <section class="tp-hero w-full text-white pt-32 pb-20 md:pt-40 md:pb-24">
        <div class="relative z-10 max-w-[1400px] mx-auto px-6 md:px-12 text-center flex flex-col items-center justify-center">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold tracking-wide">
                <span class="material-symbols-outlined text-[16px]">support_agent</span>
                Layanan Pengaduan &amp; Informasi
            </span>
            <h1 class="mt-5 text-3xl md:text-5xl lg:text-6xl font-bold tracking-tight max-w-4xl drop-shadow-md">
                Hubungi Kami
            </h1>
            <p class="mt-5 text-sm md:text-base text-gray-300 max-w-2xl font-light leading-relaxed">
                Punya pertanyaan, saran, atau pengaduan? Sampaikan kepada Dinas Sosial Kota Bogor melalui
                formulir di bawah ini.
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
            <span class="text-[#121358] font-bold">Hubungi Kami</span>
        </div>
    </div>

    {{-- ============ KONTEN ============ --}}
    <section class="w-full py-12 md:py-16">
        <div class="max-w-[1400px] mx-auto px-6 md:px-12">
            <div class="tp-page-card space-y-8">

                <div class="space-y-2">
                    <h2 class="text-2xl text-[#121358] font-bold leading-snug">Sampaikan Pesan Anda</h2>
                    <p class="text-gray-600 leading-relaxed max-w-3xl">
                        Isi formulir di bawah ini atau hubungi kami langsung melalui kontak yang tersedia.
                    </p>
                </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

                {{-- ===== Form pesan ===== --}}
                <div id="form-kontak" class="lg:col-span-7">
                    <div class="space-y-5">
                        <div class="space-y-1">
                            <h3 class="text-lg text-gray-800 font-bold">Kirim Pesan</h3>
                            <p class="text-sm text-gray-500">Kolom bertanda * wajib diisi.</p>
                        </div>

                        @if (session('success'))
                            <div class="kt-alert kt-alert-ok" role="status">
                                <span class="material-symbols-outlined text-[18px] shrink-0">check_circle</span>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="kt-alert kt-alert-error" role="alert">
                                <span class="material-symbols-outlined text-[18px] shrink-0">error</span>
                                <span>Pesan belum terkirim. Mohon periksa kembali isian Anda.</span>
                            </div>
                        @endif

                        @if (isset($captchaA) && isset($captchaB))
                            <form method="POST" action="{{ route('kontak.store') }}" novalidate class="space-y-4">
                                @csrf

                                {{-- Honeypot anti-spam: biarkan kosong, disembunyikan dari pengguna --}}
                                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                                </div>

                                <div class="kt-field">
                                    <label for="kt-nama">Nama *</label>
                                    <div class="kt-field-wrap">
                                        <span class="kt-field-icon material-symbols-outlined text-[18px]">person</span>
                                        <input id="kt-nama" name="nama" type="text" maxlength="100"
                                            placeholder="Nama lengkap Anda" value="{{ old('nama') }}" autocomplete="name" />
                                    </div>
                                    @error('nama')<p class="kt-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="kt-field">
                                        <label for="kt-email">Email *</label>
                                        <div class="kt-field-wrap">
                                            <span class="kt-field-icon material-symbols-outlined text-[18px]">mail</span>
                                            <input id="kt-email" name="email" type="email" maxlength="150"
                                                placeholder="nama@email.com" value="{{ old('email') }}" autocomplete="email" />
                                        </div>
                                        @error('email')<p class="kt-error">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="kt-field">
                                        <label for="kt-hp">No. HP *</label>
                                        <div class="kt-field-wrap">
                                            <span class="kt-field-icon material-symbols-outlined text-[18px]">call</span>
                                            <input id="kt-hp" name="no_hp" type="tel" inputmode="tel" maxlength="20"
                                                placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}" autocomplete="tel" />
                                        </div>
                                        @error('no_hp')<p class="kt-error">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="kt-field">
                                    <label for="kt-pesan">Isi Pesan *</label>
                                    <div class="kt-field-wrap is-area">
                                        <span class="kt-field-icon material-symbols-outlined text-[18px]">chat</span>
                                        <textarea id="kt-pesan" name="pesan" maxlength="2000"
                                            placeholder="Tulis pesan Anda di sini...">{{ old('pesan') }}</textarea>
                                    </div>
                                    @error('pesan')<p class="kt-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="kt-field max-w-xs">
                                    <label for="kt-captcha">Berapa {{ $captchaA }} + {{ $captchaB }} ? *</label>
                                    <div class="kt-field-wrap">
                                        <span class="kt-field-icon material-symbols-outlined text-[18px]">quiz</span>
                                        <input id="kt-captcha" name="captcha" type="text" inputmode="numeric"
                                            placeholder="Masukkan jawaban" autocomplete="off" value="{{ old('captcha') }}" />
                                    </div>
                                    @error('captcha')<p class="kt-error">{{ $message }}</p>@enderror
                                </div>

                                <button type="submit" class="kt-submit">
                                    <span class="material-symbols-outlined text-[18px]">send</span>
                                    Kirim Pesan
                                </button>
                            </form>
                        @else
                            <div class="kt-alert kt-alert-error">
                                <span class="material-symbols-outlined text-[18px] shrink-0">error</span>
                                <span>Form belum dapat ditampilkan. Muat ulang halaman ini.</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ===== Info kontak ===== --}}
                <aside class="lg:col-span-5 space-y-5">
                    <div class="space-y-1">
                        <h3 class="text-lg text-gray-800 font-bold">Kontak Kami</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Anda juga dapat menghubungi atau mengunjungi kantor kami secara langsung.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="kt-info-card">
                            <span class="kt-info-icon"><span class="material-symbols-outlined text-[22px]">location_on</span></span>
                            <div>
                                <p class="text-gray-800 font-bold text-sm">Alamat</p>
                                <p class="text-gray-500 text-sm leading-relaxed">{{ $kontak['alamat'] }}</p>
                            </div>
                        </div>
                        <div class="kt-info-card">
                            <span class="kt-info-icon"><span class="material-symbols-outlined text-[22px]">mail</span></span>
                            <div>
                                <p class="text-gray-800 font-bold text-sm">Email</p>
                                <a href="mailto:{{ $kontak['email'] }}"
                                    class="text-gray-500 text-sm hover:text-[#121358] break-all">{{ $kontak['email'] }}</a>
                            </div>
                        </div>
                        <div class="kt-info-card">
                            <span class="kt-info-icon"><span class="material-symbols-outlined text-[22px]">call</span></span>
                            <div>
                                <p class="text-gray-800 font-bold text-sm">Telepon</p>
                                <a href="tel:{{ $kontak['telepon_link'] }}"
                                    class="text-gray-500 text-sm hover:text-[#121358]">{{ $kontak['telepon'] }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ $kontak['facebook'] }}" target="_blank" rel="noopener" class="kt-social">Facebook</a>
                        <a href="{{ $kontak['instagram'] }}" target="_blank" rel="noopener" class="kt-social">Instagram</a>
                        <a href="{{ $kontak['youtube'] }}" target="_blank" rel="noopener" class="kt-social">YouTube</a>
                    </div>

                    <iframe class="kt-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        title="Lokasi Dinas Sosial Kota Bogor"
                        src="https://www.google.com/maps?q={{ urlencode($kontak['alamat']) }}&output=embed"></iframe>
                </aside>
            </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Setelah submit gagal/berhasil, halaman dimuat ulang: langsung gulir ke form --}}
    @if ($errors->any() || session('success'))
        <script>
            (function () {
                var el = document.getElementById("form-kontak");
                if (el) el.scrollIntoView({ behavior: "auto", block: "start" });
            })();
        </script>
    @endif
@endpush
