<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#003b62">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>@yield('title', 'Akun Saya - SOLID Dinas Sosial Kota Bogor')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/logo.png') }}" />

    {{-- ===== Aset yang sama persis dengan layout desktop ===== --}}
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
                        brand: "#0a5ca8",
                        "brand-soft": "#e8f4fd",
                        "secondary-container": "#82c1fd",
                        "on-secondary-container": "#004e7e",
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>
        html { -webkit-text-size-adjust: 100%; }
        body { overflow-x: hidden; }
        [x-cloak] { display: none !important; }

        /* Header gaya MyAlumni: gradasi, menempel di atas, aman untuk notch */
        .m-hdr {
            position: sticky; top: 0; z-index: 30;
            background: linear-gradient(135deg, #003b62 0%, #0a5ca8 100%);
            padding: calc(env(safe-area-inset-top, 0px) + 14px) 16px 16px;
            box-shadow: 0 4px 14px rgba(0, 59, 98, 0.25);
        }
        /* Jika .m-content di style.css memberi jarak atas untuk top bar lama, samakan dengan header baru */
        .m-content { padding-top: 16px !important; }
    </style>
    @stack('head')
</head>

<body class="m-body bg-brand-soft font-sans text-on-surface antialiased">

    {{-- ================= HEADER (gaya MyAlumni) ================= --}}
    <header class="m-hdr">
        <div class="flex items-center gap-3">
            {{-- Kotak logo putih; jadi tombol kembali bila halaman punya @section('back') --}}
            @hasSection('back')
                <a href="@yield('back')" aria-label="Kembali"
                    class="w-12 h-12 shrink-0 rounded-2xl bg-white flex items-center justify-center shadow-sm">
                    <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#003b62" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6" /></svg>
                </a>
            @else
                <div class="w-12 h-12 shrink-0 rounded-2xl bg-white flex items-center justify-center shadow-sm">
                    <img src="{{ asset('assets/img/logo/logo.png') }}" alt="SOLID" class="w-8 h-8 object-contain">
                </div>
            @endif

            {{-- Judul aplikasi + nama halaman (huruf kapital kecil di bawahnya) --}}
            <div class="min-w-0 flex-1 leading-tight">
                <p class="text-lg font-extrabold text-white truncate">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                <p class="mt-0.5 text-xs font-bold uppercase tracking-wider text-secondary-container truncate">
                    @yield('page_title', 'Beranda')
                </p>
            </div>

            {{-- Lonceng notifikasi: tampil hanya jika route-nya sudah ada --}}
            @if (\Illuminate\Support\Facades\Route::has('masyarakat.notifikasi'))
                <a href="{{ route('masyarakat.notifikasi') }}" aria-label="Notifikasi"
                    class="relative w-10 h-10 shrink-0 flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-[28px]">notifications</span>
                    @php $notifBaru = \App\Models\Notifikasi::where('user_id', auth()->id())->whereNull('dibaca_at')->count(); @endphp
                    @if ($notifBaru > 0)
                        <span class="absolute top-0.5 right-0 min-w-[18px] h-[18px] px-1 rounded-full bg-secondary-container text-primary text-[10px] font-extrabold flex items-center justify-center ring-2 ring-primary">{{ $notifBaru > 9 ? '9+' : $notifBaru }}</span>
                    @endif
                </a>
            @endif

            {{-- Avatar bulat berlingkar, membuka menu akun --}}
            <button type="button" id="m-menu-btn" aria-label="Menu akun"
                class="w-12 h-12 shrink-0 rounded-full border-2 border-secondary-container bg-primary text-white text-xl font-extrabold flex items-center justify-center">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </button>
        </div>
    </header>

    {{-- ================= MENU AKUN (sheet dari bawah) ================= --}}
    <div class="m-sheet-backdrop" id="m-sheet-backdrop"></div>
    <div class="m-sheet" id="m-sheet">
        <div class="m-sheet-handle"></div>
        <div class="m-sheet-user">
            <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
            <span>{{ auth()->user()->username ?? '' }}</span>
        </div>
        <a href="{{ route('masyarakat.profil') }}" class="m-sheet-item">Profil saya</a>
        <a href="{{ route('masyarakat.password') }}" class="m-sheet-item">Akun &amp; kata sandi</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="m-sheet-item m-danger">Logout</button>
        </form>
    </div>

    {{-- ================= KONTEN ================= --}}
    <main class="m-content">
        {{-- Banner "Masuk sebagai" (Super Admin sedang melihat akun ini) --}}
        @if (session('impersonator_id'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm flex flex-wrap items-center justify-between gap-3">
                <span class="break-words">Anda sedang masuk sebagai <b>{{ auth()->user()->name }}</b>.</span>
                <form method="POST" action="{{ route('impersonate.stop') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-primary text-on-primary text-xs font-medium">
                        Kembali ke Super Admin
                    </button>
                </form>
            </div>
        @endif

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

    {{-- ================= BOTTOM NAV ================= --}}
    @include('masyarakat._bottom-nav')

    <script src="{{ asset('js/script.js') }}" defer></script>
    <script>
        // Simpan tinggi menu bawah supaya bar "Simpan" yang fixed tidak menimpanya
        (function () {
            function ukur() {
                var n = document.querySelector('.m-bottomnav');
                document.documentElement.style.setProperty('--m-nav-h', (n ? n.offsetHeight : 0) + 'px');
            }
            window.addEventListener('load', ukur);
            window.addEventListener('resize', ukur);
            ukur();
        })();
    </script>
    <script>
        (function () {
            var btn = document.getElementById('m-menu-btn');
            var sheet = document.getElementById('m-sheet');
            var back = document.getElementById('m-sheet-backdrop');
            function toggle(open) {
                sheet.classList.toggle('open', open);
                back.classList.toggle('open', open);
            }
            btn.addEventListener('click', function () { toggle(true); });
            back.addEventListener('click', function () { toggle(false); });

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function () {});
            }
        })();
    </script>
    @stack('scripts')
</body>

</html>
