<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    {{-- viewport-fit=cover: supaya env(safe-area-inset-*) bekerja di iPhone (notch / home indicator) --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>@yield('title', 'Akun Saya - SOLID Dinas Sosial Kota Bogor')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/logo.png') }}" />
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#003b62",
                        secondary: "#136299",
                        surface: "#f7f9ff",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#edf4ff",
                        "surface-container": "#e4effd",
                        "on-surface": "#121d26",
                        "on-surface-variant": "#42474e",
                        "outline-variant": "#c2c7cf",
                        "on-primary": "#ffffff",
                        error: "#ba1a1a",
                        "error-container": "#ffdad6",
                        "on-error-container": "#93000a",
                        success: "#2e7d32",
                        "success-container": "#d0f8d0",
                        "on-success-container": "#0a5e0a",
                        // Dipakai di halaman "Pengajuan Saya" tapi sebelumnya belum terdaftar
                        warning: "#b26a00",
                        "warning-container": "#fff1cc",
                        "on-warning-container": "#5c3b00",
                    },
                    fontFamily: {
                        sans: ["Plus Jakarta Sans", "ui-sans-serif", "system-ui", "sans-serif"]
                    },
                }
            },
        };
    </script>
    {{-- Alpine.js: dibutuhkan untuk dropdown "Lihat / Unduh" pada komponen <x-lampiran-preview> --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>
        /* Cegah teks otomatis membesar saat layar diputar di iOS */
        html { -webkit-text-size-adjust: 100%; }
        /* Jaga agar konten panjang (nama, alamat, URL) tidak membuat halaman bergeser ke samping */
        body { overflow-x: hidden; }
    </style>
    @stack('head')
</head>

<body class="bg-surface font-sans text-on-surface antialiased min-h-screen">

    <header class="sticky top-0 z-40 bg-primary text-white shadow">
        <div class="h-14 sm:h-16 flex items-center justify-between gap-3 px-4 sm:px-6">
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                <img src="{{ asset('assets/img/logo/logo.png') }}" class="w-8 h-8 object-contain shrink-0"
                    alt="Logo SOLID" />
                <div class="leading-tight min-w-0">
                    <p class="text-sm font-semibold text-white">SOLID</p>
                    <p class="text-[11px] text-white/80 truncate">Dinas Sosial Kota Bogor</p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <span class="hidden md:block max-w-[14rem] truncate text-sm text-white">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" aria-label="Logout"
                        class="flex items-center gap-1.5 px-2.5 sm:px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-sm text-white transition-colors">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-5 sm:py-8 lg:py-10">
        @if (session('success'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-success-container text-on-success-container text-sm break-words">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm break-words">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
