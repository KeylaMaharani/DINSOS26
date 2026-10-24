<header class="flex items-center gap-3 px-5 py-4 border-b border-outline-variant/40">
    <span class="w-10 h-10 shrink-0 rounded-xl bg-brand-soft text-brand flex items-center justify-center">
        <span class="material-symbols-outlined text-[22px]">lock</span>
    </span>
    <div class="min-w-0">
        <h2 class="text-base font-extrabold text-brand leading-tight">Akun & Kata Sandi</h2>
        <p class="text-xs text-on-surface-variant">Ubah nama akun dan kata sandi yang dibuat saat pendaftaran.</p>
    </div>
</header>

<form method="POST" action="{{ route('masyarakat.password.update') }}">
    @csrf
    @method('PUT')

    <div class="px-5 py-6 space-y-4">

        {{-- Username/NIK (terkunci) --}}
        <div class="{{ $rowCls }}">
            <label class="{{ $labelCls }}">Username (NIK)</label>
            <input type="text" value="{{ $user->username }}" class="{{ $readCls }}" readonly>
        </div>

        {{-- Nama akun --}}
        <div class="{{ $rowCls }}">
            <label for="name" class="{{ $labelCls }}">Nama Akun <span class="text-error">*</span></label>
            <div>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                    maxlength="150" class="{{ $inputCls }}" required>
                @error('name') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="border-t border-outline-variant/40 pt-4">
            <p class="sm:pl-[calc(10rem+1.25rem)] text-xs text-on-surface-variant">
                Kosongkan kolom kata sandi jika tidak ingin menggantinya.
            </p>
        </div>

        {{-- Kata sandi lama --}}
        <div class="{{ $rowCls }}">
            <label for="password_lama" class="{{ $labelCls }}">Kata Sandi Lama</label>
            <div>
                <input id="password_lama" type="password" name="password_lama" autocomplete="current-password"
                    class="{{ $inputCls }}">
                @error('password_lama') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Kata sandi baru --}}
        <div class="{{ $rowCls }}">
            <label for="password" class="{{ $labelCls }}">Kata Sandi Baru</label>
            <div>
                <input id="password" type="password" name="password" autocomplete="new-password"
                    class="{{ $inputCls }}">
                @error('password') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Konfirmasi --}}
        <div class="{{ $rowCls }}">
            <label for="password_confirmation" class="{{ $labelCls }}">Ulangi Kata Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                autocomplete="new-password" class="{{ $inputCls }}">
        </div>

        {{-- Tombol --}}
        <div class="{{ $rowCls }}">
            <span class="hidden sm:block"></span>
            <div>
                <button type="submit"
                    class="h-11 px-8 rounded-lg bg-brand text-sm font-semibold text-white hover:bg-primary transition-colors">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</form>