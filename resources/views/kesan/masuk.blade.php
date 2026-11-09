<!doctype html>
<html lang="id">

<head>
    <title>Masuk - KESAN Kolaborasi Entaskan Kemiskinan</title>
    @include('kesan._head')
</head>

<body class="bg-white text-on-surface antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-2">

        {{-- Foto + tagline --}}
        <div class="relative h-60 sm:h-80 lg:h-auto lg:min-h-screen overflow-hidden"
            style="background: linear-gradient(180deg, rgba(0,40,69,.10), rgba(0,59,98,.30)), url('{{ asset('assets/img/kesan/kesan.jpeg') }}') center/cover no-repeat, #0a5ca8;">
            <a href="{{ route('kesan') }}" aria-label="Kembali ke halaman KESAN"
                class="absolute left-4 top-4 w-10 h-10 rounded-full bg-white/90 flex items-center justify-center shadow"
                style="margin-top: env(safe-area-inset-top, 0px)">
                <span class="material-symbols-outlined text-primary text-[22px]">arrow_back</span>
            </a>
            <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-white to-transparent lg:hidden"></div>
            <div class="hidden lg:block absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/30 to-transparent"></div>
            <div class="hidden lg:block absolute left-10 right-10 bottom-12 text-white">
                <p class="text-3xl font-extrabold leading-snug max-w-md">Kebaikan Anda mengubah mereka menjadi lebih baik.</p>
                <p class="mt-2 text-white/80 max-w-md">Platform kolaborasi antara pemerintah, filantropi, dan masyarakat untuk bantuan tepat sasaran, cepat, dan berkelanjutan.</p>
            </div>
        </div>

        {{-- Form masuk --}}
        <div class="relative -mt-10 lg:mt-0 px-6 pb-10 lg:flex lg:items-center lg:justify-center lg:py-12">
            <div class="w-full max-w-sm mx-auto">
                <div class="flex items-center justify-center gap-3">
                    <span class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-brand flex items-center justify-center shadow-md">
                        <span class="material-symbols-outlined msym-fill text-white text-[32px]">volunteer_activism</span>
                    </span>
                    <div class="leading-none">
                        <p class="text-4xl font-extrabold text-primary tracking-tight">KESAN</p>
                        <p class="mt-1 text-[11px] font-semibold text-secondary">Kolaborasi Entaskan Kemiskinan</p>
                    </div>
                </div>

                <h1 class="mt-7 text-center text-2xl font-extrabold text-primary">Selamat Datang</h1>
                <p class="mt-1 text-center text-sm text-on-surface-variant">Silakan masuk untuk mengakses platform kolaborasi bantuan sosial.</p>

                <form method="POST" action="{{ route('kesan.masuk.store') }}" class="mt-6 space-y-4" x-data="{ show: false }">
                    @csrf
                    <div>
                        <label for="username" class="block text-xs font-semibold text-on-surface-variant mb-1">Username atau email</label>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required
                            class="w-full rounded-xl border @error('username') border-error @else border-outline-variant @enderror bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                        @error('username')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="block text-xs font-semibold text-on-surface-variant mb-1">Kata sandi</label>
                        <div class="relative">
                            <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required
                                class="w-full rounded-xl border border-outline-variant bg-white px-4 py-3 pr-11 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                            <button type="button" @click="show = !show" tabindex="-1" class="absolute inset-y-0 right-0 px-3 text-on-surface-variant"
                                :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                        @error('password')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm text-on-surface-variant">
                        <input type="checkbox" name="remember" value="1" class="rounded border-outline-variant text-brand focus:ring-brand"> Ingat saya
                    </label>

                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand py-3.5 text-sm font-bold text-white shadow-sm hover:bg-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">account_balance</span>Masuk sebagai Filantropi
                    </button>
                </form>

                <a href="{{ route('admin.login') }}"
                    class="mt-3 w-full inline-flex items-center justify-center gap-2 rounded-xl border-2 border-brand py-3 text-sm font-bold text-brand hover:bg-brand-soft transition-colors">
                    <span class="material-symbols-outlined text-[20px]">account_balance</span>Masuk sebagai Admin/Petugas
                </a>

                <p class="mt-6 text-center text-xs text-on-surface-variant">Belum punya akun Filantropi? Hubungi Dinas Sosial Kota Bogor.</p>
                <p class="mt-4 text-center text-sm font-semibold text-primary">Bersama kita bisa mengentaskan kemiskinan</p>
            </div>
        </div>
    </div>
</body>

</html>
