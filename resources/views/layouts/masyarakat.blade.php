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
    {{-- Alpine.js: dibutuhkan sidebar + dropdown "Lihat / Unduh" pada komponen <x-lampiran-preview> --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>
        /* Cegah teks otomatis membesar saat layar diputar di iOS */
        html { -webkit-text-size-adjust: 100%; }
        /* Jaga agar konten panjang (nama, alamat, URL) tidak membuat halaman bergeser ke samping */
        body { overflow-x: hidden; }
        /* Sembunyikan elemen yang belum sempat diinisialisasi Alpine.js */
        [x-cloak] { display: none !important; }

        /* Scrollbar tipis untuk sidebar (latar biru) */
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: rgba(255, 255, 255, 0.45) transparent; }
        .sidebar-scroll::-webkit-scrollbar { display: block; width: 6px; height: 6px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.12); border-radius: 9999px; margin: 6px 0; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.55); border-radius: 9999px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.8); }

        /* Versi untuk panel melayang (latar putih) saat sidebar diciutkan */
        .sidebar-scroll-light { scrollbar-width: thin; scrollbar-color: #c2c7cf transparent; }
        .sidebar-scroll-light::-webkit-scrollbar { display: block; width: 6px; height: 6px; }
        .sidebar-scroll-light::-webkit-scrollbar-track { background: #edf4ff; border-radius: 9999px; margin: 6px 0; }
        .sidebar-scroll-light::-webkit-scrollbar-thumb { background: #c2c7cf; border-radius: 9999px; }
    </style>
    @stack('head')
</head>

<body class="bg-brand-soft font-sans text-on-surface antialiased">
    @php
        /* ====================================================================
         |  MENU SIDEBAR MASYARAKAT (bertingkat)
         |  Node: label, icon (menu paling atas), route -> LINK,
         |        match (pola route tambahan agar tetap aktif),
         |        children -> FOLDER, separator -> label pemisah grup.
         |
         |  Menu SELALU tampil. Kalau route-nya belum dibuat, linknya sementara '#'
         |  dan otomatis jadi link asli begitu route dengan nama itu didaftarkan.
         |
         |  Menu "Profile" = 1 link tunggal ke halaman profil. Di dalam halamannya
         |  ada 2 tab: "Data Diri" (masyarakat.profil) dan "Akun & Kata Sandi"
         |  (masyarakat.password). Keduanya membuat menu Profile tetap aktif.
         * ==================================================================== */
        // Menu "PBI APBD" langsung membuka form PBI milik user ini (form tetap sama
        // sebelum maupun sesudah mendaftar). Kalau user belum punya pengajuan PBI,
        // arahkan ke form pendaftaran awal.
        $pbiSaya = auth()->check() && method_exists(auth()->user(), 'pengajuanPbiApbd')
            ? auth()->user()->pengajuanPbiApbd()->latest()->first()
            : null;

        $pbiUrl = $pbiSaya
            ? route('masyarakat.pbi-apbd.lengkapi', $pbiSaya)
            : (\Illuminate\Support\Facades\Route::has('registrasi.step1') ? route('registrasi.step1') : '#');

        // Menu "DTSEN" menuju route 'masyarakat.dtsen' yang menentukan tujuannya sendiri:
        // form DTSEN milik user kalau sudah punya pengajuan, kalau belum kembali ke Beranda
        // dengan pesan. Jadi menu ini selalu bisa diklik (tidak lagi '#').

        $sidebar = [
            ['separator' => 'Menu Utama'],

            ['label' => 'Beranda', 'icon' => 'home', 'route' => 'masyarakat.dashboard', 'match' => ['masyarakat.dashboard']],
            [
                'label' => 'Layanan',
                'icon' => 'settings',
                'children' => [
                    ['label' => 'PBI APBD', 'route' => 'masyarakat.pbi-apbd.lengkapi', 'url' => $pbiUrl, 'match' => ['masyarakat.pbi-apbd.*'], 'only_match' => true],
                    ['label' => 'DTSEN', 'route' => 'masyarakat.dtsen', 'match' => ['masyarakat.dtsen.*']],
                ],
            ],
            ['label' => 'Permohonan Saya', 'icon' => 'description', 'route' => 'masyarakat.permohonan', 'match' => ['masyarakat.permohonan.*']],
            ['label' => 'Tracking Permohonan', 'icon' => 'search', 'route' => 'masyarakat.tracking', 'match' => ['masyarakat.tracking.*']],
            ['label' => 'Anggota Keluarga', 'icon' => 'group', 'route' => 'masyarakat.keluarga', 'match' => ['masyarakat.keluarga.*']],
            // Profile: menu tunggal, 2 tab (Data Diri & Akun/Kata Sandi) ada di dalam halamannya
            ['label' => 'Profile', 'icon' => 'person', 'route' => 'masyarakat.profil', 'match' => ['masyarakat.profil.*', 'masyarakat.password', 'masyarakat.password.*']],
        ];

        // Link menu: route asli kalau sudah terdaftar, kalau belum '#'
        // ('url' = tujuan khusus yang sudah dihitung di atas, mis. form PBI milik user)
        $href = fn(array $n) => $n['url'] ?? (\Illuminate\Support\Facades\Route::has($n['route']) ? route($n['route']) : '#');

        // Node tampil jika: (link -> punya route) / (folder -> ada anak yang tampil)
        $visible = function (array $n) use (&$visible) {
            if (isset($n['children'])) {
                return collect($n['children'])->contains(fn($c) => $visible($c));
            }

            return isset($n['route']);
        };

        // Node aktif jika halaman sekarang ada di dalamnya (folder: salah satu turunannya).
        // 'only_match' = hanya aktif pada pola 'match', bukan pada route utamanya.
        $active = function (array $n) use (&$active) {
            if (isset($n['children'])) {
                return collect($n['children'])->contains(fn($c) => $active($c));
            }

            $patterns = !empty($n['only_match'])
                ? ($n['match'] ?? [])
                : array_merge([$n['route']], $n['match'] ?? []);

            return $patterns && request()->routeIs(...$patterns);
        };

        // Separator hanya ikut jika ada menu yang tampil sesudahnya
        $menuItems = [];
        $pendingSeparator = null;
        foreach ($sidebar as $node) {
            if (isset($node['separator'])) {
                $pendingSeparator = $node;
                continue;
            }
            if (!$visible($node)) {
                continue;
            }
            if ($pendingSeparator) {
                $menuItems[] = $pendingSeparator;
                $pendingSeparator = null;
            }
            $menuItems[] = $node;
        }

        // Render anak menu (rekursif). $floating = versi panel melayang (sidebar ciut)
        $renderChildren = function (array $nodes, bool $floating) use (&$renderChildren, $visible, $active, $href) {
            $on = $floating ? 'bg-brand/10 text-brand font-semibold' : 'bg-white/20 text-white font-semibold';
            $off = $floating
                ? 'text-on-surface-variant hover:bg-surface-container'
                : 'text-white/70 hover:bg-white/10 hover:text-white';
            $head = $floating ? 'text-on-surface hover:bg-surface-container' : 'text-white/90 hover:bg-white/10';
            $line = $floating ? 'border-outline-variant/40' : 'border-white/20';

            $html = '';
            foreach ($nodes as $n) {
                if (!$visible($n)) {
                    continue;
                }

                if (isset($n['children'])) {
                    $open = $active($n) ? 'true' : 'false';
                    $html .=
                        '<div x-data="{ g: ' . $open . ' }">' .
                        '<button type="button" x-on:click="g = !g" class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-[13px] font-semibold transition-colors ' . $head . '">' .
                        '<span class="text-left">' . e($n['label']) . '</span>' .
                        '<span class="material-symbols-outlined text-[16px] transition-transform" :class="g ? \'rotate-180\' : \'\'">expand_more</span>' .
                        '</button>' .
                        '<div x-show="g" x-cloak class="ml-3 pl-3 border-l ' . $line . ' space-y-0.5">' .
                        $renderChildren($n['children'], $floating) .
                        '</div></div>';
                } else {
                    $html .=
                        '<a href="' . e($href($n)) . '" class="block px-3 py-1.5 rounded-lg text-[13px] transition-colors ' .
                        ($active($n) ? $on : $off) . '">' . e($n['label']) . '</a>';
                }
            }

            return $html;
        };
    @endphp

    {{-- Status sidebar:
         - collapsedPref : pilihan pengguna (localStorage), true = hanya ikon
         - desktop       : true di layar >= lg. Di layar kecil sidebar selalu tampil penuh
         - collapsed     : gabungan keduanya --}}
    {{-- h-screen h-dvh: h-dvh (tinggi layar dinamis) menimpa h-screen di browser yang mendukung,
         supaya di iPhone konten tidak terpotong toolbar Safari. Browser lama tetap pakai h-screen. --}}
    <div x-cloak class="flex h-screen h-dvh overflow-hidden" x-data="{
        sidebarOpen: false,
        collapsedPref: (function() { try { return localStorage.getItem('userSidebarCollapsed') === '1'; } catch (e) { return false; } })(),
        desktop: window.innerWidth >= 1024,
        get collapsed() { return this.collapsedPref && this.desktop; },
        toggleSidebar() {
            this.collapsedPref = !this.collapsedPref;
            try { localStorage.setItem('userSidebarCollapsed', this.collapsedPref ? '1' : '0'); } catch (e) {}
        }
    }"
        @resize.window="desktop = window.innerWidth >= 1024">

        <!-- Overlay (mobile) -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-30 lg:hidden">
        </div>

        <!-- Sidebar (melayang, bisa diciutkan) -->
        <aside :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-[130%]', collapsed ? 'w-20' : 'w-64']"
            class="fixed left-4 top-4 bottom-4 z-40 rounded-[28px] bg-brand text-white flex flex-col
                   shadow-[0_10px_30px_rgba(10,92,168,0.35)] ring-1 ring-white/20
                   transition-[width,transform] duration-200 ease-in-out lg:translate-x-0">

            {{-- Logo + tombol ciutkan (di DALAM card) --}}
            <div class="h-20 shrink-0 flex items-center" :class="collapsed ? 'justify-center' : 'pl-5 pr-3 gap-3'">
                <div class="w-11 h-11 shrink-0 rounded-xl bg-white flex items-center justify-center shadow-sm">
                    <img alt="Logo SOLID" src="{{ asset('assets/img/logo/logo.png') }}"
                        class="w-8 h-8 object-contain" />
                </div>
                <div x-show="!collapsed" class="leading-tight min-w-0 flex-1">
                    <p class="text-sm font-bold">SOLID</p>
                    <p class="text-[11px] text-white/70 truncate">Dinas Sosial Kota Bogor</p>
                </div>

                {{-- Sidebar lebar: tombol ciutkan di kanan logo (hanya layar besar) --}}
                <button type="button" x-show="!collapsed" @click="toggleSidebar()" title="Ciutkan sidebar"
                    class="hidden lg:flex w-8 h-8 shrink-0 rounded-lg items-center justify-center text-white/80 hover:bg-white/15 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                </button>
            </div>

            {{-- Sidebar ciut: tombol lebarkan tepat di bawah logo --}}
            <button type="button" x-show="collapsed" x-cloak @click="toggleSidebar()" title="Lebarkan sidebar"
                class="flex w-12 h-8 mx-auto mb-1 shrink-0 rounded-lg items-center justify-center text-white/80 hover:bg-white/15 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </button>

            {{-- Pembungkus nav + indikator scroll (up/down = masih ada menu tersembunyi) --}}
            <div class="relative flex-1 min-h-0" x-data="{
                up: false,
                down: false,
                check() {
                    const n = this.$refs.nav;
                    if (!n) return;
                    this.up = n.scrollTop > 4;
                    this.down = n.scrollTop + n.clientHeight < n.scrollHeight - 4;
                }
            }"
                x-init="$nextTick(() => check());
                new ResizeObserver(() => check()).observe($refs.nav);
                new MutationObserver(() => $nextTick(() => check())).observe($refs.nav, { childList: true, subtree: true, attributes: true });
                $watch('collapsed', () => setTimeout(() => check(), 250));"
                @resize.window="check()">

                <nav x-ref="nav" @scroll.passive="check()"
                    class="sidebar-scroll h-full px-3 pt-2 pb-12 space-y-1.5 overflow-y-auto overflow-x-hidden">
                    @foreach ($menuItems as $top)
                        @if (isset($top['separator']))
                            {{-- Label grup menu --}}
                            <div class="{{ $loop->first ? '' : 'pt-2' }}">
                                @unless ($loop->first)
                                    <div class="mx-3 border-t border-white/15"></div>
                                @endunless
                                <p x-show="!collapsed"
                                    class="px-3 pt-2 pb-0.5 text-[10px] font-bold uppercase tracking-[0.14em] text-white/50">
                                    {{ $top['separator'] }}
                                </p>
                            </div>
                            @continue
                        @endif

                        @php $isAct = $active($top); @endphp

                        @if (!isset($top['children']))
                            {{-- Menu tunggal (link langsung) --}}
                            <a href="{{ $href($top) }}" :title="collapsed ? '{{ $top['label'] }}' : null"
                                @click="sidebarOpen = false"
                                :class="collapsed ? 'w-12 mx-auto justify-center' : 'w-full px-3 gap-3'"
                                class="h-12 rounded-xl flex items-center transition-colors
                                    {{ $isAct ? 'bg-white/25 text-white shadow-inner' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                                <span class="material-symbols-outlined text-[24px] shrink-0">{{ $top['icon'] }}</span>
                                <span x-show="!collapsed" class="text-sm font-medium truncate">{{ $top['label'] }}</span>
                            </a>
                        @else
                            {{-- Folder (Layanan) --}}
                            <div class="relative" x-data="{ open: false, top: 0, maxH: 400 }"
                                x-init="open = {{ $isAct ? 'true' : 'false' }} && !collapsed;
                                $watch('collapsed', function(v) { open = v ? false : {{ $isAct ? 'true' : 'false' }}; })"
                                @click.outside="if (collapsed) open = false">

                                <button
                                    @click="
                                        if (collapsed) {
                                            const r = $el.getBoundingClientRect();
                                            const a = $el.closest('aside').getBoundingClientRect();
                                            top = r.top - a.top;
                                            maxH = window.innerHeight - r.top - 16;
                                        }
                                        open = !open
                                    "
                                    type="button" :title="collapsed ? '{{ $top['label'] }}' : null"
                                    :class="collapsed ? 'w-12 mx-auto justify-center' : 'w-full px-3 gap-3'"
                                    class="h-12 rounded-xl flex items-center transition-colors
                                        {{ $isAct ? 'bg-white/25 text-white shadow-inner' : 'text-white/80 hover:bg-white/15 hover:text-white' }}">
                                    <span class="material-symbols-outlined text-[24px] shrink-0">{{ $top['icon'] }}</span>
                                    <span x-show="!collapsed"
                                        class="flex-1 text-left text-sm font-medium truncate">{{ $top['label'] }}</span>
                                    <span x-show="!collapsed"
                                        class="material-symbols-outlined text-[18px] transition-transform"
                                        :class="open ? 'rotate-180' : ''">expand_more</span>
                                </button>

                                {{-- Accordion (sidebar lebar) --}}
                                <div x-show="open && !collapsed" x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="mt-1 ml-6 pl-4 border-l border-white/20 space-y-0.5">
                                    {!! $renderChildren($top['children'], false) !!}
                                </div>

                                {{-- Panel melayang (sidebar ciut) --}}
                                <div x-show="open && collapsed" x-cloak :style="`top:${top}px; max-height:${maxH}px`"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-x-1"
                                    x-transition:enter-end="opacity-100 translate-x-0"
                                    class="sidebar-scroll-light fixed left-full ml-5 w-64 overflow-y-auto bg-white text-on-surface rounded-2xl shadow-xl border border-outline-variant/30 p-2 z-50">
                                    <p class="px-3 py-1.5 text-xs font-bold text-brand">{{ $top['label'] }}</p>
                                    {!! $renderChildren($top['children'], true) !!}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </nav>

                {{-- Indikator: masih ada menu di ATAS --}}
                <div x-show="up" x-cloak x-transition.opacity
                    class="pointer-events-none absolute top-0 inset-x-0 h-8 bg-gradient-to-b from-brand to-transparent z-10">
                </div>

                {{-- Indikator: masih ada menu di BAWAH (bisa diklik) --}}
                <div x-show="down" x-cloak x-transition.opacity
                    class="pointer-events-none absolute bottom-0 inset-x-0 h-14 bg-gradient-to-t from-brand via-brand/90 to-transparent z-10 flex items-end justify-center pb-1.5">
                    <button type="button" @click="$refs.nav.scrollBy({ top: 220, behavior: 'smooth' })"
                        title="Scroll untuk melihat menu lainnya"
                        class="pointer-events-auto w-7 h-7 rounded-full bg-white/25 hover:bg-white/40 text-white flex items-center justify-center animate-bounce transition-colors">
                        <span class="material-symbols-outlined text-[20px]">keyboard_arrow_down</span>
                    </button>
                </div>
            </div>

            {{-- Logout (bagian bawah sidebar) --}}
            <form method="POST" action="{{ route('logout') }}" class="px-3 py-4 shrink-0">
                @csrf
                <button type="submit" :title="collapsed ? 'Logout' : null"
                    :class="collapsed ? 'w-12 mx-auto justify-center' : 'w-full px-3 gap-3'"
                    class="h-12 rounded-xl flex items-center text-white/80 hover:bg-white/15 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[24px] shrink-0">logout</span>
                    <span x-show="!collapsed" class="text-sm font-medium">Logout</span>
                </button>
            </form>
        </aside>

        <!-- Spacer agar konten tidak ketutup sidebar fixed di layar besar (lebarnya ikut sidebar) -->
        <div class="hidden lg:block shrink-0 transition-[width] duration-200"
            :class="collapsed ? 'w-28' : 'w-[18rem]'"></div>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0 h-screen h-dvh overflow-hidden">

            <!-- Topbar -->
            <div class="shrink-0 flex items-center gap-4 pl-4 pr-6 pt-4 pb-2 lg:pl-6 lg:pr-8">
                <button @click="sidebarOpen = true"
                    class="lg:hidden w-11 h-11 shrink-0 rounded-xl bg-brand text-white flex items-center justify-center">
                    <span class="material-symbols-outlined">menu</span>
                </button>

                <h1 class="hidden md:block shrink-0 text-xl font-extrabold text-brand tracking-tight">
                    @yield('page_title', 'Beranda')
                </h1>

                {{-- Bar pengguna: bar biru di layar >= sm, hanya avatar bulat di mobile --}}
                <div
                    class="ml-auto min-w-0 max-w-full h-14 flex items-center gap-3 sm:rounded-xl sm:bg-brand sm:shadow-[0_6px_18px_rgba(10,92,168,0.30)] sm:ring-1 sm:ring-white/20 sm:px-5">

                    <div class="shrink-0">
                        <div class="relative">
                            <div class="flex items-center gap-3 pl-1">
                                <div class="hidden sm:block leading-tight text-right">
                                    <p class="text-xs font-semibold text-white max-w-[12rem] truncate">
                                        {{ auth()->user()->name ?? 'Pengguna' }}</p>
                                    <p class="text-[10px] uppercase tracking-wide text-white/70">
                                        {{ auth()->user()->username ?? '' }}</p>
                                </div>
                                <div
                                    class="w-9 h-9 rounded-full bg-secondary-container ring-2 ring-brand/30 sm:ring-white/70 flex items-center justify-center text-on-secondary-container font-semibold text-sm shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto pl-4 pr-6 pb-24 pt-2 lg:pl-6 lg:pr-8">
                <div class="@yield('container', 'max-w-3xl') mx-auto">
                    {{-- Banner "Masuk sebagai": tampil hanya saat Super Admin sedang melihat akun ini (dari master) --}}
                    @if (session('impersonator_id'))
                        <div
                            class="mb-4 px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm flex flex-wrap items-center justify-between gap-3">
                            <span class="break-words">Anda sedang masuk sebagai <b>{{ auth()->user()->name }}</b>.</span>
                            <form method="POST" action="{{ route('impersonate.stop') }}">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1.5 rounded-lg bg-primary text-on-primary text-xs font-medium hover:bg-secondary transition-colors">
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
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>