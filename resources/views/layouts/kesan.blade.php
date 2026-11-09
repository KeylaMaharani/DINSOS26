<!doctype html>
<html lang="id">

<head>
    <title>@yield('title', 'KESAN - Kolaborasi Entaskan Kemiskinan')</title>
    @include('kesan._head')
    @stack('head')
</head>

<body class="bg-brand-soft text-on-surface antialiased min-h-screen" x-data="{ menu: false }">
    @php
        $kkUser = auth()->user();
        $kkKeranjang = \App\Models\KesanPilihan::where('user_id', $kkUser->id)->where('status', 'keranjang')->count();
        $kkNav = [
            ['label' => 'Beranda', 'icon' => 'home', 'route' => 'kesan.app.beranda', 'match' => ['kesan.app.beranda']],
            ['label' => 'Penerima', 'icon' => 'groups', 'route' => 'kesan.app.penerima', 'match' => ['kesan.app.penerima', 'kesan.app.detail']],
            ['label' => 'Pilih Bantuan', 'icon' => 'favorite', 'route' => 'kesan.app.pilihan', 'match' => ['kesan.app.pilihan'], 'badge' => $kkKeranjang],
            ['label' => 'Riwayat', 'icon' => 'history', 'route' => 'kesan.app.riwayat', 'match' => ['kesan.app.riwayat', 'kesan.app.tracking']],
        ];
    @endphp

    {{-- ================= HEADER ================= --}}
    <header class="kk-hdr">
        <div class="max-w-6xl mx-auto flex items-center gap-3">
            @hasSection('back')
                <a href="@yield('back')" aria-label="Kembali"
                    class="w-12 h-12 shrink-0 rounded-2xl bg-white flex items-center justify-center shadow-sm">
                    <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#003b62" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6" /></svg>
                </a>
            @else
                <a href="{{ route('kesan.app.beranda') }}"
                    class="w-12 h-12 shrink-0 rounded-2xl bg-white flex items-center justify-center shadow-sm">
                    <img src="{{ asset('assets/img/logo/logo.png') }}" alt="SOLID" class="w-8 h-8 object-contain">
                </a>
            @endif

            <div class="min-w-0 flex-1 leading-tight">
                <p class="text-lg font-extrabold text-white truncate">KESAN</p>
                <p class="mt-0.5 text-xs font-bold uppercase tracking-wider text-secondary-container truncate">
                    @yield('page_title', 'Beranda')</p>
            </div>

            {{-- Menu desktop (di HP diganti menu bawah) --}}
            <nav class="hidden lg:flex items-center gap-1 mr-2">
                @foreach ($kkNav as $n)
                    @php $on = request()->routeIs(...$n['match']); @endphp
                    <a href="{{ route($n['route']) }}"
                        class="relative inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-colors {{ $on ? 'bg-white text-primary' : 'text-white/85 hover:bg-white/15' }}">
                        <span class="material-symbols-outlined text-[20px] {{ $on ? 'msym-fill' : '' }}">{{ $n['icon'] }}</span>
                        {{ $n['label'] }}
                        @if (($n['badge'] ?? 0) > 0)
                            <span class="min-w-[18px] h-[18px] px-1 rounded-full bg-accent text-white text-[10px] font-bold flex items-center justify-center">{{ $n['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            @include('partials.lonceng', ['tone' => 'text-white'])

            <button type="button" @click="menu = true" aria-label="Menu akun"
                class="w-12 h-12 shrink-0 rounded-full border-2 border-secondary-container bg-primary text-white text-xl font-extrabold flex items-center justify-center">
                {{ strtoupper(substr($kkUser->name ?? 'F', 0, 1)) }}
            </button>
        </div>
    </header>

    {{-- ================= MENU AKUN (sheet di HP, dropdown di desktop) ================= --}}
    <div x-show="menu" x-cloak x-transition.opacity @click="menu = false" class="fixed inset-0 z-40 bg-black/40"></div>
    <div x-show="menu" x-cloak x-transition
        class="fixed z-50 inset-x-0 bottom-0 rounded-t-3xl bg-white p-4 shadow-2xl pb-[calc(env(safe-area-inset-bottom,0px)+16px)]
               lg:inset-x-auto lg:bottom-auto lg:top-20 lg:right-6 lg:w-72 lg:rounded-2xl lg:pb-4">
        <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-outline-variant lg:hidden"></div>
        <div class="px-2 pb-3 border-b border-outline-variant/40">
            <p class="font-extrabold text-primary truncate">{{ $kkUser->name }}</p>
            <p class="text-xs text-on-surface-variant truncate">{{ $kkUser->username }} · Filantropi</p>
        </div>
        <a href="{{ route('kesan.app.riwayat') }}" class="flex items-center gap-3 px-2 py-3 text-sm font-semibold">
            <span class="material-symbols-outlined text-[20px] text-brand">history</span>Pantau penyaluran</a>
        <a href="{{ route('kesan') }}" class="flex items-center gap-3 px-2 py-3 text-sm font-semibold">
            <span class="material-symbols-outlined text-[20px] text-brand">info</span>Tentang program KESAN</a>
        <form method="POST" action="{{ route('kesan.keluar') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-2 py-3 text-sm font-semibold text-error">
                <span class="material-symbols-outlined text-[20px]">logout</span>Keluar</button>
        </form>
    </div>

    {{-- ================= KONTEN ================= --}}
    <main class="max-w-6xl mx-auto px-4 pt-4 pb-28 lg:pt-6 lg:pb-12">
        @if (session('impersonator_id'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm flex flex-wrap items-center justify-between gap-3">
                <span class="break-words">Anda sedang masuk sebagai <b>{{ $kkUser->name }}</b>.</span>
                <form method="POST" action="{{ route('impersonate.stop') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-primary text-on-primary text-xs font-medium">Kembali ke Super Admin</button>
                </form>
            </div>
        @endif
        @foreach (['success' => 'bg-success-container text-on-success-container', 'error' => 'bg-error-container text-on-error-container', 'peringatan' => 'bg-warning-container text-on-warning-container'] as $k => $cls)
            @if (session($k))
                <div class="mb-4 px-4 py-3 rounded-xl text-sm break-words {{ $cls }}">{{ session($k) }}</div>
            @endif
        @endforeach

        @yield('content')
    </main>

    {{-- ================= MENU BAWAH (HP) ================= --}}
    <nav class="kk-nav lg:hidden" aria-label="Menu utama">
        <div class="grid grid-cols-4">
            @foreach ($kkNav as $n)
                @php $on = request()->routeIs(...$n['match']); @endphp
                <a href="{{ route($n['route']) }}"
                    class="relative flex flex-col items-center gap-0.5 pt-2 pb-1.5 text-[11px] font-semibold {{ $on ? 'text-brand' : 'text-on-surface-variant' }}">
                    <span class="material-symbols-outlined text-[26px] {{ $on ? 'msym-fill' : '' }}">{{ $n['icon'] }}</span>
                    {{ $n['label'] }}
                    @if (($n['badge'] ?? 0) > 0)
                        <span class="absolute top-1 left-1/2 ml-2 min-w-[18px] h-[18px] px-1 rounded-full bg-accent text-white text-[10px] font-bold flex items-center justify-center">{{ $n['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>

    @stack('scripts')
</body>

</html>
