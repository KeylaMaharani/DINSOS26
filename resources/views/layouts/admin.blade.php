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
    </style>
</head>

<body class="bg-surface font-body-md text-on-surface antialiased">
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

                {{-- Tambahkan menu lain di sini, contoh: --}}
                {{--
                <a href="#"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/80 hover:bg-white/10 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">group</span>
                    Data Penerima
                </a>
                --}}

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

                <div class="relative" x-data="{ userMenuOpen: false }">
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

                        {{-- <a href="#"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">lock</span>
                            Ubah Kata Sandi
                        </a>
                        <a href="#"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">person</span>
                            Edit Profil
                        </a> --}}

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
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>

</html>
