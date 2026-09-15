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

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

    @stack('scripts')
</body>

</html>
