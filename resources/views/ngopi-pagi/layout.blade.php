<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ngopi Pagi - Konsultasi LK3 Dinas Sosial Kota Bogor')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#003b62', brand: '#0a5ca8', 'brand-soft': '#e8f4fd',
                        'on-surface': '#121d26', 'on-surface-variant': '#42474e',
                        'outline-variant': '#c2c7cf', surface: '#f7f9ff', error: '#ba1a1a',
                        crema: '#f3dfc1', cup: '#8a5a2b',
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                },
            },
        };
    </script>
    <style>
        @keyframes uap { 0% { opacity: 0; transform: translateY(6px) scaleY(.8); } 40% { opacity: .9; } 100% { opacity: 0; transform: translateY(-10px) scaleY(1.15); } }
        .uap { animation: uap 2.2s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) { .uap { animation: none; } }
        details > summary { list-style: none; } details > summary::-webkit-details-marker { display: none; }
    </style>
    @stack('head')
</head>
<body class="bg-surface font-sans text-on-surface antialiased">
@if (trim($__env->yieldContent('portal')))
    {{-- MODE PORTAL (desktop penuh, gaya Halodoc): dipakai halaman beranda, spesialisasi, mulai --}}
    <div class="min-h-screen flex flex-col bg-surface">
        <nav class="sticky top-0 z-30 bg-white border-b border-outline-variant/40">
            <div class="max-w-7xl mx-auto h-16 px-4 sm:px-6 lg:px-8 flex items-center gap-4 lg:gap-8 pt-[env(safe-area-inset-top,0px)]">
                <a href="{{ route('ngopi-pagi.index') }}" class="flex items-center gap-2.5 shrink-0">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px] text-crema" style="font-variation-settings:'FILL' 1">coffee</span>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-lg font-extrabold italic tracking-tight text-primary">Ngopi Pagi</span>
                        <span class="hidden sm:block text-[10px] text-on-surface-variant -mt-0.5">LK3 Dinas Sosial Kota Bogor</span>
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-6 text-sm font-semibold text-on-surface">
                    <a href="{{ route('home') }}" class="hover:text-brand">Beranda</a>
                    <a href="{{ route('ngopi-pagi.index') }}" class="hover:text-brand">Konselor</a>
                    <a href="{{ route('ngopi-pagi.index') }}#cara-kerja" class="hover:text-brand">Cara kerja</a>
                    <a href="{{ route('ngopi-pagi.index') }}#info-layanan" class="hover:text-brand">Jam layanan</a>
                </div>

                <div class="ml-auto flex items-center gap-3">
                    @isset($buka)
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold {{ $buka ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' }}">
                            <span class="w-2 h-2 rounded-full {{ $buka ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            <span class="hidden sm:inline">{{ $buka ? 'Petugas sedang melayani' : 'Di luar jam layanan' }}</span>
                            <span class="sm:hidden">{{ $buka ? 'Buka' : 'Tutup' }}</span>
                        </span>
                    @endisset
                    <a href="{{ route('home') }}" class="md:hidden w-9 h-9 rounded-full hover:bg-surface-container flex items-center justify-center" aria-label="Beranda">
                        <span class="material-symbols-outlined text-[22px]">home</span>
                    </a>
                </div>
            </div>
        </nav>

        <div class="flex-1">
            @yield('content')
        </div>

        <footer class="bg-white border-t border-outline-variant/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-1 text-[12px] text-on-surface-variant">
                <span>Ngopi Pagi &middot; Layanan konsultasi LK3 Dinas Sosial Kota Bogor</span>
                <span>Senin &ndash; Jumat, 08.00 &ndash; 15.00 WIB</span>
            </div>
        </footer>
    </div>
@else
    {{-- MODE KARTU PONSEL (chat dan halaman lain): lebar diatur lewat @section('wrap') --}}
    <div class="{{ trim($__env->yieldContent('wrap')) ?: 'max-w-xl' }} mx-auto min-h-screen bg-surface sm:shadow-xl">
        @yield('content')
    </div>
@endif
    @stack('scripts')
</body>
</html>
