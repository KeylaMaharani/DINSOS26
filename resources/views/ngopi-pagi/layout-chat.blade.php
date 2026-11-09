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
    <div class="{{ trim($__env->yieldContent('wrap')) ?: 'max-w-xl' }} mx-auto min-h-screen bg-surface sm:shadow-xl">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>