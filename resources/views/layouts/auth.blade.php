<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'SOLID v4 Dinas Sosial Kota Bogor')</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/logo.png') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet"
    />

    <link href="{{ asset('css/style.css') }}" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>

    @stack('head')
</head>
<body class="@yield('body_class', 'bg-surface font-body-md text-on-surface antialiased')">

    <main class="w-full">
        @yield('content')
    </main>

    <script src="{{ asset('js/script.js') }}"></script>
    @stack('scripts')
</body>
</html>
