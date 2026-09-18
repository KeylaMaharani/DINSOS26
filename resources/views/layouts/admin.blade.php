<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>@yield('title', 'SOLID v4 Dinas Sosial Kota Bogor')</title>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/logo.png') }}" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script>
        (function() {
            const originalWarn = console.warn;
            console.warn = function(...args) {
                if (args[0] && String(args[0]).includes('cdn.tailwindcss.com')) return;
                originalWarn.apply(console, args);
            };
        })();
    </script>
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
                        "warning-container": "#fff3cd",
                        "on-warning-container": "#7a5b00",
                        warning: "#b98900",
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
    @yield('head')
    @stack('styles')
    <style>
        /* Sembunyikan elemen yang belum sempat diinisialisasi Alpine.js,
           supaya tidak "kedip" muncul sebelum disembunyikan (misal overlay sidebar). */
        [x-cloak] {
            display: none !important;
        }

        /* Sembunyikan scrollbar tapi tetap bisa discroll */
        * {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        *::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }

        /* ====== Flipbook (buku panduan bantuan) ====== */
        #help-flipbook .page {
            background-color: #ffffff;
            overflow: hidden;
            box-shadow: inset 0 0 15px rgba(0,0,0,0.03); /* Tambahan shadow tipis di dalam buku */
            border-right: 1px solid rgba(0,0,0,0.05); /* Batas lipatan tengah */
        }

        /* page-flip me-render tiap halaman sebagai <img> ketika dimuat lewat
           loadFromImages()/updateFromImages() -- jaga rasio asli, tidak digepengkan */
        #help-flipbook img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Buku terasa lebih natural: sudut sedikit membulat & rasio halaman dijaga */
        #help-flipbook {
            border-radius: 6px;
        }

        .stf__parent {
            margin: 0 auto;
        }

        #help-flipbook-search-highlight {
            transition: opacity 0.3s ease;
        }
    </style>
</head>

<body class="bg-surface font-body-md text-on-surface antialiased">
    @php
        // Peta menu => info tampilan, dipakai untuk render sidebar & cek "aktif"
        $sidebarMenus = [
            [
                'key' => 'asesmen-spmb',
                'label' => 'Asesmen SPMB',
                'icon' => 'fact_check',
                'route_prefix' => 'asesmen_spmb.',
            ],
            [
                'key' => 'pbi-apbn',
                'label' => 'PBI APBN',
                'icon' => 'health_and_safety',
                'route_prefix' => 'pbi-apbn.',
            ],
            [
                'key' => 'kedaruratan-medis',
                'label' => 'Kedaruratan Medis',
                'icon' => 'emergency',
                'route_prefix' => 'kedaruratan_medis.',
            ],
            [
                'key' => 'kartu-kks',
                'label' => 'Kartu KKS',
                'icon' => 'credit_card',
                'route_prefix' => 'kartu-kks.',
            ],
            [
                'key' => 'dtsen',
                'label' => 'DTSEN',
                'icon' => 'database',
                'route_prefix' => 'dtsen.',
            ],
        ];

        // URL file PDF panduan penggunaan yang akan ditampilkan sebagai flipbook.
        // Taruh file PDF-nya di public/assets/help/panduan-penggunaan.pdf,
        // atau ganti path di bawah ini sesuai lokasi file kamu.
        $helpPdfUrl = asset('assets/help/panduan-penggunaan.pdf');
    @endphp

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

        <!-- Overlay (mobile) -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-30 lg:hidden">
        </div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 w-64 h-screen overflow-y-auto bg-primary text-white flex flex-col shrink-0 transition-transform duration-200 ease-in-out lg:translate-x-0">

            <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10 shrink-0">
                <img alt="Logo SOLID" src="{{ asset('assets/img/logo/logo.png') }}" class="w-8 h-8 object-contain" />
                <div class="leading-tight">
                    <p class="text-sm font-semibold">SOLID</p>
                    <p class="text-[11px] text-white/70">Dinas Sosial Kota Bogor</p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                        {{ request()->routeIs('dashboard') ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    Beranda
                </a>

                {{-- ========== Menu layanan (Asesmen SPMB, PBI APBN, Kedaruratan Medis, Kartu KKS, DTSEN) ========== --}}
                @foreach ($sidebarMenus as $menu)
                    @php $isMenuActive = request()->routeIs($menu['route_prefix'] . '*'); @endphp
                    <div x-data="{ open: {{ $isMenuActive ? 'true' : 'false' }} }">
                        <button @click="open = !open" type="button"
                            class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                {{ $isMenuActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            <span class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px]">{{ $menu['icon'] }}</span>
                                {{ $menu['label'] }}
                            </span>
                            <span class="material-symbols-outlined text-[18px] transition-transform"
                                :class="open ? 'rotate-180' : ''">expand_more</span>
                        </button>

                        <div x-show="open" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="mt-1 ml-4 pl-4 border-l border-white/15 space-y-0.5">
                            @php
                                $subRoutes = [
                                    'ajuan' => 'Ajuan',
                                    'arsip' => 'Arsip',
                                    'monitoring' => 'Monitoring',
                                    // 'log' => 'Log',
                                ];
                            @endphp
                            @foreach ($subRoutes as $subKey => $subLabel)
                                @php
                                    $subRouteName = $menu['route_prefix'] . $subKey . ($menu['key'] === 'pbi-apbn' ? '.index' : '');
                                @endphp
                                <a href="{{ route($subRouteName) }}"
                                    class="block px-3 py-2 rounded-lg text-[13px] transition-colors
                                        {{ request()->routeIs($subRouteName) ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                                    {{ $subLabel }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- ========== 7. Dokumen ========== --}}
                <a href="{{ route('dokumen.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                        {{ request()->routeIs('dokumen.*') ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="material-symbols-outlined text-[20px]">description</span>
                    Dokumen
                </a>

                <div class="pt-2 mt-2 border-t border-white/10"></div>

                <a href="{{ route('akun.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
        {{ request()->routeIs('akun.*') ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                    Kelola Akses
                </a>
            </nav>

        </aside>

        <!-- Spacer agar konten tidak ketutup sidebar fixed di layar besar -->
        <div class="hidden lg:block w-64 shrink-0"></div>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

            <!-- Topbar -->
            <header
                class="h-16 shrink-0 bg-surface-container-lowest border-b border-outline-variant/40 flex items-center justify-between px-4 lg:px-6">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden text-on-surface-variant">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h1 class="text-base font-semibold text-on-surface">@yield('page_title', 'Beranda')</h1>
                </div>

                {{-- ===== Dropdown user + Edit Profil (pop-up, tidak lewat halaman Kelola Akses) ===== --}}
                <div x-data="{ userMenuOpen: false, profileModalOpen: {{ $errors->profil->any() ? 'true' : 'false' }} }">
                    <div class="relative">
                        <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false"
                            class="flex items-center gap-3 px-2 py-1.5 rounded-lg hover:bg-surface-container transition-colors">
                            <div
                                class="w-9 h-9 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container font-semibold text-sm shrink-0">
                                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="hidden sm:block leading-tight text-left">
                                <p class="text-sm font-medium text-on-surface">{{ auth()->user()->name ?? 'Admin' }}</p>
                                <p class="text-[11px] text-on-surface-variant">{{ auth()->user()->username ?? '' }}</p>
                            </div>
                            <span
                                class="material-symbols-outlined text-[18px] text-on-surface-variant hidden sm:block">expand_more</span>
                        </button>

                        <div x-show="userMenuOpen" x-cloak x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            class="absolute right-0 mt-2 w-52 bg-surface-container-lowest border border-outline-variant/40 rounded-xl shadow-lg overflow-hidden z-50">

                            <button type="button" @click="profileModalOpen = true; userMenuOpen = false"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors">
                                <span class="material-symbols-outlined text-[18px]">person</span>
                                Edit Profil
                            </button>

                            <div class="border-t border-outline-variant/40"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-error hover:bg-error-container/40 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">logout</span>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Modal Edit Profil (akun milik pengguna yang sedang login) --}}
                    <div x-show="profileModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                        <div @click.outside="profileModalOpen = false" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            class="bg-surface-container-lowest w-full max-w-md rounded-xl shadow-lg overflow-hidden">

                            <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                                <h3 class="text-base font-semibold text-on-surface">Edit Profil</h3>
                                <button @click="profileModalOpen = false" class="text-on-surface-variant">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('profil.update') }}" class="p-5 space-y-4">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label class="block text-sm font-medium mb-1">Username</label>
                                    <input name="username" type="text"
                                        value="{{ old('username', auth()->user()->username ?? '') }}"
                                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" required />
                                    @error('username', 'profil')
                                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Email</label>
                                    <input name="email" type="email"
                                        value="{{ old('email', auth()->user()->email ?? '') }}"
                                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" required />
                                    @error('email', 'profil')
                                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Kata Sandi Baru</label>
                                    <p class="text-xs text-red-600 font-medium mb-1">Kosongkan jika tidak ingin mengubah
                                        kata sandi.</p>
                                    <div class="relative" x-data="{ show: false }">
                                        <input name="password" :type="show ? 'text' : 'password'"
                                            class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 pr-10 text-sm" />
                                        <button type="button" @click="show = !show" tabindex="-1"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-on-surface-variant"
                                            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                            <span class="material-symbols-outlined text-[16px]"
                                                x-text="show ? 'visibility_off' : 'visibility'"></span>
                                        </button>
                                    </div>
                                    @error('password', 'profil')
                                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Konfirmasi Kata Sandi Baru</label>
                                    <div class="relative" x-data="{ show: false }">
                                        <input name="password_confirmation" :type="show ? 'text' : 'password'"
                                            class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 pr-10 text-sm" />
                                        <button type="button" @click="show = !show" tabindex="-1"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-on-surface-variant"
                                            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                            <span class="material-symbols-outlined text-[16px]"
                                                x-text="show ? 'visibility_off' : 'visibility'"></span>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="profileModalOpen = false"
                                        class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</button>
                                    <button type="submit"
                                        class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 rounded-lg bg-success-container text-on-success-container text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ====== Tombol bantuan mengambang (floating) + modal Flipbook PDF ====== --}}
    <div x-data="{ helpOpen: false }">

        <button type="button" @click="helpOpen = true"
            title="Bantuan / Panduan Penggunaan"
            class="fixed bottom-6 right-6 z-40 w-14 h-14 rounded-full bg-primary text-on-primary shadow-lg
                   hover:bg-secondary transition-all flex items-center justify-center">
            <span class="material-symbols-outlined text-[26px]">help</span>
        </button>

        <div x-show="helpOpen" x-cloak
            x-init="$watch('helpOpen', value => { if (value) window.dispatchEvent(new CustomEvent('help-flipbook-open')); })"
            @keydown.escape.window="helpOpen = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-[9990] bg-[#101418] overflow-hidden">

            {{-- Header — bar tipis tembus pandang, melayang di atas buku
                 (absolute, tidak mereservasi ruang lewat padding) supaya
                 area buku di bawahnya bisa penuh setinggi layar. --}}
            <div class="absolute top-0 left-0 right-0 flex items-center justify-between px-4 py-2 gap-3 bg-white/85 backdrop-blur z-30 shadow-sm">
                <h3 class="text-sm font-bold text-on-surface flex items-center gap-2 min-w-0">
                    <span class="material-symbols-outlined text-[20px] text-primary shrink-0">menu_book</span>
                    <span class="truncate">Panduan Penggunaan</span>
                </h3>
                <div class="flex items-center gap-1 shrink-0">
                    <a href="{{ $helpPdfUrl }}" target="_blank" rel="noopener noreferrer"
                        title="Buka di Tab Baru"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-primary hover:bg-primary/10 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                    </a>
                    <a href="{{ $helpPdfUrl }}" download="Panduan-Penggunaan-SOLID.pdf"
                        title="Unduh PDF"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-primary hover:bg-primary/10 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">cloud_download</span>
                    </a>
                    <div class="w-px h-5 bg-outline-variant/40 mx-1"></div>
                    <button @click="helpOpen = false" title="Tutup (Esc)"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-error hover:bg-error-container/50 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
            </div>

            {{-- Container Flipbook — full-bleed, memakai hampir seluruh layar.
                 Header & kontrol di bawah melayang TEMBUS PANDANG di atasnya
                 (bukan mendorong/mengecilkan area buku), jadi bukunya bisa
                 setinggi & sebesar mungkin. --}}
            <div class="absolute inset-0 w-full h-full flex items-center justify-center p-2">

                    <div id="help-flipbook-loading" class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-on-surface-variant z-10 bg-[#eef1f6]">
                        <span class="material-symbols-outlined text-[32px] animate-spin text-primary">progress_activity</span>
                        <span class="text-sm font-medium">Memuat Panduan...</span>
                    </div>

                    <div id="help-flipbook-wrap" class="w-full h-full flex items-center justify-center px-2">
                        <div id="help-flipbook" class="hidden shadow-2xl"></div>
                    </div>

                    {{-- Kontrol Navigasi + Pencarian (Absolute di bagian bawah) --}}
                    <div id="help-flipbook-controls"
                        class="hidden absolute bottom-2 left-1/2 -translate-x-1/2 z-20 flex-col sm:flex-row items-center gap-2 sm:gap-3 bg-white/90 backdrop-blur px-3 py-1.5 rounded-2xl sm:rounded-full shadow-lg border border-outline-variant/30 max-w-[92%] w-max">


                        {{-- Pencarian --}}
                        <div class="flex items-center gap-1.5">
                            <input type="text" id="help-flipbook-search-input" placeholder="Cari kata..."
                                class="w-28 sm:w-40 text-xs px-2.5 py-1.5 rounded-full border border-outline-variant/50 focus:outline-none focus:ring-1 focus:ring-primary" />
                            <button type="button" id="help-flipbook-search-btn"
                                class="w-7 h-7 shrink-0 rounded-full bg-primary text-white flex items-center justify-center hover:bg-secondary transition-colors">
                                <span class="material-symbols-outlined text-[16px]">search</span>
                            </button>
                            <span id="help-flipbook-search-status" class="text-[11px] text-on-surface-variant whitespace-nowrap"></span>
                        </div>

                        <div class="hidden sm:block w-px h-5 bg-outline-variant/40"></div>

                        {{-- Navigasi halaman --}}
                        <div class="flex items-center gap-3">
                            <button type="button" id="help-flipbook-prev"
                                class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                            </button>
                            <span class="flex items-center gap-1 text-xs font-bold text-on-surface-variant tabular-nums">
                                <input type="number" id="help-flipbook-page-input" min="1" value="1"
                                    class="w-10 text-center rounded-md border border-outline-variant/50 px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-primary" />
                                <span>/ <span id="help-flipbook-page-total">0</span></span>
                            </span>
                            <button type="button" id="help-flipbook-next"
                                class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                            </button>
                        </div>

                        <span id="help-flipbook-load-progress" class="text-[11px] text-success font-medium whitespace-nowrap"></span>
                    </div>

                    <p id="help-flipbook-error" class="hidden absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-sm text-error text-center bg-error-container px-6 py-4 rounded-xl shadow-lg border border-error/20 max-w-sm z-30">
                        Gagal memuat file panduan. Pastikan file PDF tersedia dan perangkat memiliki cukup memori.
                    </p>
                </div>
            </div>
        </div>
    {{-- ====== Akhir tombol bantuan & modal flipbook ====== --}}

    {{-- Kotak highlight hasil pencarian pada flipbook. Diletakkan di level
         body supaya `position: fixed`-nya selalu mengacu ke viewport, aman
         dari elemen ancestor mana pun yang mungkin memakai transform. --}}
    <div id="help-flipbook-search-highlight"
        class="hidden fixed pointer-events-none border-2 border-yellow-400 bg-yellow-300/40 rounded shadow-[0_0_10px_rgba(250,204,21,0.75)] z-[9995]"></div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- pdf.js: merender tiap halaman PDF ke <canvas> lalu dikonversi ke gambar --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    {{-- page-flip: efek membalik halaman seperti buku, dari gambar yang sudah dirender --}}
    <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>

    {{-- ====== Modal konfirmasi bertema (menggantikan confirm() bawaan browser) ====== --}}
    <div id="app-confirm-modal"
         class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-[1px] px-4">
        <div data-confirm-panel
             class="opacity-0 scale-95 transition-all duration-150 ease-out bg-surface-container-lowest border border-outline-variant/40 rounded-xl shadow-xl w-full max-w-sm p-5">
            <div class="flex items-start gap-3">
                <span data-confirm-icon class="material-symbols-outlined text-[22px] text-primary">help</span>
                <div class="flex-1 min-w-0">
                    <h3 data-confirm-title class="text-sm font-semibold text-on-surface">Konfirmasi</h3>
                    <p data-confirm-message class="text-sm text-on-surface-variant mt-1"></p>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" data-confirm-cancel
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-outline-variant/60 text-on-surface hover:bg-surface-container text-sm font-medium transition-colors">
                    Batal
                </button>
                <button type="button" data-confirm-ok
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-medium transition-colors">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <script>
    (function () {
        let modalEl, titleEl, messageEl, confirmBtn, cancelBtn, iconEl, panelEl;
        let resolvePromise = null;

        function ensureModal() {
            if (modalEl) return true;
            modalEl = document.getElementById('app-confirm-modal');
            if (!modalEl) return false;

            panelEl = modalEl.querySelector('[data-confirm-panel]');
            titleEl = modalEl.querySelector('[data-confirm-title]');
            messageEl = modalEl.querySelector('[data-confirm-message]');
            confirmBtn = modalEl.querySelector('[data-confirm-ok]');
            cancelBtn = modalEl.querySelector('[data-confirm-cancel]');
            iconEl = modalEl.querySelector('[data-confirm-icon]');

            confirmBtn.addEventListener('click', () => close(true));
            cancelBtn.addEventListener('click', () => close(false));
            modalEl.addEventListener('click', (e) => {
                if (e.target === modalEl) close(false);
            });
            document.addEventListener('keydown', (e) => {
                if (!modalEl.classList.contains('hidden') && e.key === 'Escape') close(false);
            });

            return true;
        }

        function open(message, options) {
            options = options || {};
            if (!ensureModal()) {
                return Promise.resolve(window.confirm(message));
            }

            const title = options.title || 'Konfirmasi';
            const variant = options.variant || 'primary';
            const confirmText = options.confirmText || 'Ya, Lanjutkan';
            const cancelText = options.cancelText || 'Batal';

            titleEl.textContent = title;
            messageEl.textContent = message;
            confirmBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;

            confirmBtn.className = variant === 'error'
                ? 'inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-error text-on-error hover:opacity-90 text-sm font-medium transition-colors'
                : 'inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-medium transition-colors';

            iconEl.textContent = variant === 'error' ? 'warning' : 'help';
            iconEl.className = 'material-symbols-outlined text-[22px] ' + (variant === 'error' ? 'text-error' : 'text-primary');

            modalEl.classList.remove('hidden');
            requestAnimationFrame(() => {
                panelEl.classList.remove('opacity-0', 'scale-95');
            });

            return new Promise((resolve) => {
                resolvePromise = resolve;
            });
        }

        function close(result) {
            if (!modalEl) return;
            panelEl.classList.add('opacity-0', 'scale-95');
            setTimeout(() => modalEl.classList.add('hidden'), 150);
            if (resolvePromise) {
                resolvePromise(result);
                resolvePromise = null;
            }
        }

        window.confirmModal = open;

        document.addEventListener('click', async function (e) {
            const trigger = e.target.closest('[data-confirm]');
            if (!trigger || trigger.dataset.confirmBound === 'pending') return;

            e.preventDefault();

            const ok = await window.confirmModal(trigger.getAttribute('data-confirm'), {
                variant: trigger.getAttribute('data-confirm-variant') || 'primary',
                title: trigger.getAttribute('data-confirm-title') || undefined,
            });
            if (!ok) return;

            trigger.dataset.confirmBound = 'pending';

            const form = trigger.closest('form');
            if (form) {
                if (trigger.tagName === 'BUTTON' && trigger.name) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = trigger.name;
                    hidden.value = trigger.value;
                    form.appendChild(hidden);
                }
                form.submit();
            } else if (trigger.tagName === 'A' && trigger.href) {
                window.location.href = trigger.href;
            }

            delete trigger.dataset.confirmBound;
        });
    })();
    </script>
    {{-- ====== Akhir blok modal konfirmasi ====== --}}

    {{-- ====== Flipbook PDF untuk tombol bantuan (pdf.js + page-flip) ======
         Dirapikan seperti contoh viewer manual-book: rendering per-halaman
         bertahap (tidak memblokir modal), navigasi lewat input nomor halaman,
         indikator progres pemuatan, dan pencarian kata dengan highlight. --}}
    <script>
    (function () {
        const PDF_URL = @json($helpPdfUrl);

        let loaded = false;
        let loading = false;
        let pageFlipInstance = null;
        let pdfDocRef = null;
        let totalPages = 0;
        let loadedCount = 0;
        let renderScale = 2; // nilai awal, dihitung ulang secara dinamis di initFlipbook()

        let pageImages = [];
        let pageTexts = [];
        let pageItemsData = [];

        let currentMatches = [];
        let currentMatchPos = -1;
        let lastQuery = null;

        function createPlaceholder(w, h) {
            const c = document.createElement('canvas');
            c.width = w;
            c.height = h;
            const ctx = c.getContext('2d');
            ctx.fillStyle = '#eef1f6';
            ctx.fillRect(0, 0, w, h);
            ctx.fillStyle = '#9aa5b1';
            ctx.font = '16px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('Memuat halaman...', w / 2, h / 2);
            return c.toDataURL('image/jpeg', 0.7);
        }

        function renderSinglePage(pageNum) {
            return pdfDocRef.getPage(pageNum).then(function (page) {
                const vp = page.getViewport({ scale: renderScale });
                const canvas = document.createElement('canvas');
                canvas.width = vp.width;
                canvas.height = vp.height;
                const ctx = canvas.getContext('2d');

                const renderTask = page.render({ canvasContext: ctx, viewport: vp }).promise;
                const textTask = page.getTextContent().then(function (tc) {
                    pageTexts[pageNum - 1] = tc.items.map(function (it) { return it.str; }).join(' ').toLowerCase();
                    pageItemsData[pageNum - 1] = { items: tc.items, viewport: vp };
                });

                return Promise.all([renderTask, textTask]).then(function () {
                    return canvas.toDataURL('image/jpeg', 0.85);
                });
            });
        }

        function updatePageIndicator() {
            const input = document.getElementById('help-flipbook-page-input');
            if (!pageFlipInstance || !input) return;
            input.value = pageFlipInstance.getCurrentPageIndex() + 1;
        }

        function updateLoadProgress() {
            const el = document.getElementById('help-flipbook-load-progress');
            if (!el) return;
            el.textContent = loadedCount >= totalPages ? '' : ('Memuat: ' + loadedCount + '/' + totalPages);
        }

        function hideSearchHighlight() {
            const hl = document.getElementById('help-flipbook-search-highlight');
            if (!hl) return;
            clearTimeout(hl._fadeTimer);
            hl.classList.add('hidden');
        }

        function showSearchHighlight(pageIndex, rect, retriesLeft) {
            if (retriesLeft === undefined) retriesLeft = 8;
            const flipbookEl = document.getElementById('help-flipbook');
            const targetSrc = pageImages[pageIndex];
            const imgs = flipbookEl.querySelectorAll('img');
            let targetImg = null, bestArea = 0;

            imgs.forEach(function (img) {
                if (img.src === targetSrc) {
                    const r = img.getBoundingClientRect();
                    const area = r.width * r.height;
                    if (area > bestArea) {
                        bestArea = area;
                        targetImg = img;
                    }
                }
            });

            if (!targetImg || bestArea === 0) {
                if (retriesLeft <= 0) return;
                setTimeout(function () { showSearchHighlight(pageIndex, rect, retriesLeft - 1); }, 200);
                return;
            }

            const ir = targetImg.getBoundingClientRect();
            const scaleX = ir.width / rect.canvasWidth;
            const scaleY = ir.height / rect.canvasHeight;
            const hl = document.getElementById('help-flipbook-search-highlight');
            clearTimeout(hl._fadeTimer);
            hl.style.left = (ir.left + rect.left * scaleX) + 'px';
            hl.style.top = (ir.top + rect.top * scaleY) + 'px';
            hl.style.width = (rect.width * scaleX) + 'px';
            hl.style.height = (rect.height * scaleY) + 'px';
            hl.classList.remove('hidden');
            hl.style.opacity = '1';
        }

        function findAllMatches(query) {
            const matches = [];
            for (let p = 0; p < pageItemsData.length; p++) {
                const data = pageItemsData[p];
                if (!data) continue;
                for (let i = 0; i < data.items.length; i++) {
                    const it = data.items[i];
                    if (it.str && it.str.toLowerCase().indexOf(query) !== -1) {
                        const tx = pdfjsLib.Util.transform(data.viewport.transform, it.transform);
                        const fontHeight = Math.hypot(tx[2], tx[3]) || 12;
                        const fontWidth = Math.hypot(tx[0], tx[1]) || 1;
                        const width = (it.width || it.str.length * 5) * fontWidth;
                        matches.push({
                            pageIndex: p,
                            rect: {
                                left: tx[4],
                                top: tx[5] - fontHeight,
                                width: width,
                                height: fontHeight * 1.3,
                                canvasWidth: data.viewport.width,
                                canvasHeight: data.viewport.height
                            }
                        });
                    }
                }
            }
            return matches;
        }

        function goToMatch(idx) {
            if (!currentMatches.length) return;
            if (idx < 0) idx = currentMatches.length - 1;
            if (idx >= currentMatches.length) idx = 0;
            currentMatchPos = idx;
            const match = currentMatches[idx];
            const statusEl = document.getElementById('help-flipbook-search-status');
            if (statusEl) {
                statusEl.textContent = (idx + 1) + '/' + currentMatches.length + ' (hal. ' + (match.pageIndex + 1) + ')';
            }
            hideSearchHighlight();
            pageFlipInstance.flip(match.pageIndex);
            setTimeout(function () { showSearchHighlight(match.pageIndex, match.rect); }, 450);
        }

        function doSearch() {
            const input = document.getElementById('help-flipbook-search-input');
            const statusEl = document.getElementById('help-flipbook-search-status');
            if (!input || !pageFlipInstance) return;
            const query = input.value.trim().toLowerCase();

            if (query !== lastQuery) {
                hideSearchHighlight();
                lastQuery = query;

                if (!query) {
                    if (statusEl) statusEl.textContent = '';
                    currentMatches = [];
                    currentMatchPos = -1;
                    return;
                }

                currentMatches = findAllMatches(query);
                currentMatchPos = -1;

                if (!currentMatches.length) {
                    if (statusEl) {
                        statusEl.textContent = loadedCount < totalPages
                            ? 'Tidak ditemukan (masih memuat...)'
                            : 'Tidak ditemukan';
                    }
                    return;
                }

                goToMatch(0);
                return;
            }

            if (!currentMatches.length) {
                if (statusEl) statusEl.textContent = 'Tidak ditemukan';
                return;
            }
            goToMatch(currentMatchPos + 1);
        }

        function loadRemainingPagesInBackground() {
            const CONCURRENCY = 3;
            let nextPage = 2;
            let activeCount = 0;
            let updateScheduled = false;

            updateLoadProgress();

            function scheduleFlipbookUpdate() {
                if (updateScheduled) return;
                updateScheduled = true;
                setTimeout(function () {
                    updateScheduled = false;
                    if (pageFlipInstance && typeof pageFlipInstance.updateFromImages === 'function') {
                        pageFlipInstance.updateFromImages(pageImages);
                    }
                }, 250);
            }

            function startNext() {
                while (activeCount < CONCURRENCY && nextPage <= totalPages) {
                    (function (pn) {
                        activeCount++;
                        renderSinglePage(pn)
                            .then(function (url) { pageImages[pn - 1] = url; })
                            .catch(function (e) { console.error('Gagal merender halaman ' + pn, e); })
                            .then(function () {
                                loadedCount++;
                                activeCount--;
                                updateLoadProgress();
                                scheduleFlipbookUpdate();
                                startNext();
                            });
                    })(nextPage);
                    nextPage++;
                }
            }

            startNext();
        }

        async function initFlipbook() {
            if (loaded || loading) return;
            loading = true;

            const loadingEl = document.getElementById('help-flipbook-loading');
            const flipbookEl = document.getElementById('help-flipbook');
            const controlsEl = document.getElementById('help-flipbook-controls');
            const errorEl = document.getElementById('help-flipbook-error');
            const wrapEl = document.getElementById('help-flipbook-wrap');

            try {
                if (typeof pdfjsLib === 'undefined') throw new Error('pdf.js gagal dimuat');
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                pdfDocRef = await pdfjsLib.getDocument(PDF_URL).promise;
                totalPages = pdfDocRef.numPages;
                if (!totalPages) throw new Error('PDF tidak memiliki halaman');

                // Dapatkan rasio dasar PDF dari halaman 1
                const firstPage = await pdfDocRef.getPage(1);
                const viewportRaw = firstPage.getViewport({ scale: 1 });
                const baseWidth = viewportRaw.width;
                const baseHeight = viewportRaw.height;

                // Hitung ukuran TAMPIL buku dulu (berdasarkan ruang modal yang
                // tersedia), sebelum merender halaman apa pun. Ini penting:
                // skala render PDF di bawah diturunkan dari ukuran tampil ini,
                // supaya resolusi gambar selalu cukup tinggi untuk ukuran
                // sebesar apa pun buku ini ditampilkan (termasuk saat full-bleed
                // di layar besar) — kalau urutannya dibalik, gambar hasil render
                // beresolusi tetap akan di-upscale oleh browser untuk memenuhi
                // ukuran tampil yang lebih besar, dan itulah yang bikin teksnya
                // buram.
                function computeSpreadSize() {
                    const rect = wrapEl.getBoundingClientRect();
                    const availW = Math.max(rect.width - 8, 280);
                    const availH = Math.max(rect.height - 8, 360);

                    const spreadRatio = (baseWidth * 2) / baseHeight;

                    let spreadW = availW;
                    let spreadH = spreadW / spreadRatio;

                    if (spreadH > availH) {
                        spreadH = availH;
                        spreadW = spreadH * spreadRatio;
                    }

                    return {
                        pageW: Math.max(Math.round(spreadW / 2), 140),
                        pageH: Math.max(Math.round(spreadH), 200),
                    };
                }

                const { pageW, pageH } = computeSpreadSize();

                // Turunkan skala render PDF dari ukuran tampil x devicePixelRatio
                // (supaya tajam di layar retina/HiDPI juga), dibatasi 1x–3x agar
                // memori & waktu render tetap wajar untuk PDF yang panjang.
                const dpr = window.devicePixelRatio || 1;
                renderScale = Math.min(Math.max((pageW * dpr) / baseWidth, 1.2), 3);

                const placeholderVp = firstPage.getViewport({ scale: renderScale });
                pageImages = new Array(totalPages).fill(createPlaceholder(placeholderVp.width, placeholderVp.height));

                // Render halaman pertama dulu (dengan skala yang sudah pas)
                // supaya buku bisa langsung tampil, sisanya dimuat bertahap di
                // latar belakang (lihat di bawah).
                pageImages[0] = await renderSinglePage(1);
                loadedCount = 1;

                pageFlipInstance = new St.PageFlip(flipbookEl, {
                    width: pageW,
                    height: pageH,
                    size: 'stretch',
                    minWidth: Math.round(pageW * 0.5),
                    maxWidth: pageW,
                    minHeight: Math.round(pageH * 0.5),
                    maxHeight: pageH,
                    showCover: true,
                    maxShadowOpacity: 0.25,
                    showPageCorners: true,
                    mobileScrollSupport: true,
                });

                pageFlipInstance.loadFromImages(pageImages);
                pageFlipInstance.on('flip', function () {
                    updatePageIndicator();
                    hideSearchHighlight();
                });

                document.getElementById('help-flipbook-prev').addEventListener('click', function () {
                    hideSearchHighlight();
                    pageFlipInstance.flipPrev();
                });
                document.getElementById('help-flipbook-next').addEventListener('click', function () {
                    hideSearchHighlight();
                    pageFlipInstance.flipNext();
                });

                // Navigasi lewat input nomor halaman
                const pageInputEl = document.getElementById('help-flipbook-page-input');
                const pageTotalEl = document.getElementById('help-flipbook-page-total');
                pageInputEl.max = totalPages;
                pageTotalEl.textContent = totalPages;

                function goToPage() {
                    let t = parseInt(pageInputEl.value, 10);
                    if (isNaN(t)) return;
                    t = Math.max(1, Math.min(t, totalPages));
                    pageInputEl.value = t;
                    hideSearchHighlight();
                    pageFlipInstance.flip(t - 1);
                }
                pageInputEl.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') { e.preventDefault(); goToPage(); }
                });
                pageInputEl.addEventListener('blur', goToPage);

                // Pencarian kata
                document.getElementById('help-flipbook-search-btn').addEventListener('click', doSearch);
                const searchInputEl = document.getElementById('help-flipbook-search-input');
                searchInputEl.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
                });
                searchInputEl.addEventListener('input', function () { lastQuery = null; });

                // Navigasi keyboard (dinonaktifkan saat sedang mengetik di input)
                document.addEventListener('keydown', function (e) {
                    if (!pageFlipInstance) return;
                    if (!flipbookEl || flipbookEl.classList.contains('hidden')) return;
                    const active = document.activeElement;
                    if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA')) return;
                    if (e.key === 'ArrowLeft') pageFlipInstance.flipPrev();
                    if (e.key === 'ArrowRight') pageFlipInstance.flipNext();
                });

                // Hitung ulang & terapkan ukuran saat jendela/modal berganti ukuran,
                // supaya buku tetap proporsional di layar apa pun.
                let resizeTimeout = null;
                window.addEventListener('resize', function () {
                    if (!pageFlipInstance) return;
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(function () {
                        if (typeof pageFlipInstance.updateSize === 'function') {
                            pageFlipInstance.updateSize();
                        }
                        hideSearchHighlight();
                    }, 150);
                });

                loadingEl.classList.add('hidden');
                flipbookEl.classList.remove('hidden');
                controlsEl.classList.remove('hidden');
                controlsEl.classList.add('flex'); // munculkan kontrol melayang

                updatePageIndicator();
                loaded = true;

                // Muat sisa halaman secara bertahap di latar belakang, tidak
                // memblokir tampilan buku yang sudah bisa dibuka dari halaman 1.
                loadRemainingPagesInBackground();
            } catch (err) {
                console.error('Gagal memuat flipbook panduan:', err);
                loadingEl.classList.add('hidden');
                errorEl.classList.remove('hidden');
            } finally {
                loading = false;
            }
        }

        window.addEventListener('help-flipbook-open', initFlipbook);
    })();
    </script>
    {{-- ====== Akhir blok flipbook PDF ====== --}}

    @stack('scripts')
</body>

</html>
