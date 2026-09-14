@extends('layouts.app')

@section('title', 'Login - SOLID Dinas Sosial Kota Bogor')

@section('content')
    <div class="auth-page">
        <div class="auth-outer-wrap">
            <div class="auth-card">

                <!-- Body: form login (single card, no split header) -->
                <form class="auth-card-body" id="login-form" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div style="display:flex; justify-content:center; margin-bottom: 0.75rem;">
                        <img alt="Logo SOLID" src="{{ asset('assets/img/logo/logo.png') }}"
                            style="width:4.4rem; height:4.4rem; object-fit:contain;" />
                    </div>

                    <div class="auth-form-heading">
                        <h2 style="font-size:19px;">Selamat Datang</h2>
                        <p>Silakan masuk ke akun Anda</p>
                    </div>

                    @if (session('error'))
                        <div class="auth-warning">
                            <span class="material-symbols-outlined text-[16px] shrink-0">error</span>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="auth-warning">
                            <span class="material-symbols-outlined text-[16px] shrink-0">error</span>
                            <span>
                                @foreach ($errors->all() as $error)
                                    {{ $error }}@if (!$loop->last)
                                        <br />
                                    @endif
                                @endforeach
                            </span>
                        </div>
                    @endif

                    <div class="auth-field">
                        <label for="username">Username</label>
                        <div class="auth-field-icon-wrap">
                            <span class="auth-input-icon material-symbols-outlined text-[16px]">person</span>
                            <input id="username" name="username" type="text" value="{{ old('username') }}"
                                placeholder="Masukan username Anda" autocomplete="username" required />
                        </div>
                        @error('username')
                            <p class="text-error text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="password">Kata Sandi</label>
                        <div class="auth-field-icon-wrap">
                            <span class="auth-input-icon material-symbols-outlined text-[16px]">lock</span>
                            <input id="password" name="password" type="password" placeholder="Masukan kata sandi Anda"
                                autocomplete="current-password" required />
                            <button class="auth-toggle-eye" id="toggle-password" type="button"
                                aria-label="Tampilkan kata sandi">
                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-error text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="captcha">Verifikasi: Berapa {{ $captchaA ?? 4 }} + {{ $captchaB ?? 3 }} ?</label>
                        <div class="auth-field-icon-wrap">
                            <span class="auth-input-icon material-symbols-outlined text-[16px]">quiz</span>
                            <input id="captcha" name="captcha" type="text" inputmode="numeric"
                                placeholder="Hasil penjumlahan" required />
                        </div>
                        @error('captcha')
                            <p class="text-error text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="auth-row-between">
                        <label class="auth-remember-wrap"
                            style="display:flex; align-items:center; gap:0.4rem; font-size:12.5px; cursor:pointer;">
                            <input type="checkbox" id="remember" name="remember" value="1"
                                {{ old('remember') ? 'checked' : '' }} />
                            <span>Ingat saya</span>
                        </label>
                        <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
                    </div>

                    <button type="submit" class="auth-submit">
                        <span class="material-symbols-outlined text-[16px]">login</span>
                        Login
                    </button>
                </form>
            </div>

            <div class="auth-bottom-row">
                <p class="auth-footnote">
                    &copy; {{ date('Y') }} Pemerintah Kota Bogor. All right reserved
                </p>
            </div>
        </div>
    </div>
@endsection
