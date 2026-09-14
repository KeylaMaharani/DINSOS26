@extends('layouts.app')

@section('title',
    $token ?? null
    ? 'Reset Kata Sandi - SOLID Dinas Sosial Kota Bogor'
    : 'Lupa Kata Sandi - SOLID
    Dinas Sosial Kota Bogor')

@section('content')
    <div class="auth-page">
        <div class="auth-outer-wrap">
            <div class="auth-card">
                @if ($token ?? null)
                    {{-- ==== MODE RESET: sudah punya token dari email ==== --}}
                    <form class="auth-card-body" method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ old('email', $email) }}">

                        <div style="display:flex; justify-content:center; margin-bottom: 0.75rem;">
                            <img alt="Logo SOLID" src="{{ asset('assets/img/logo/logo.png') }}"
                                style="width:4.4rem; height:4.4rem; object-fit:contain;" />
                        </div>

                        <div class="auth-form-heading">
                            <h2 style="font-size:19px;">Atur Ulang Kata Sandi</h2>
                            <p>Masukkan kata sandi baru Anda</p>
                        </div>

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
                            <label for="password">Kata Sandi Baru</label>
                            <div class="auth-field-icon-wrap">
                                <span class="auth-input-icon material-symbols-outlined text-[16px]">lock</span>
                                <input id="password" name="password" type="password" placeholder="Minimal 8 karakter"
                                    autocomplete="new-password" required />
                            </div>
                            @error('password')
                                <p class="text-error text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                            <div class="auth-field-icon-wrap">
                                <span class="auth-input-icon material-symbols-outlined text-[16px]">lock</span>
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                    placeholder="Ulangi kata sandi baru" autocomplete="new-password" required />
                            </div>
                        </div>

                        <button type="submit" class="auth-submit">
                            <span class="material-symbols-outlined text-[16px]">lock_reset</span>
                            Simpan Kata Sandi
                        </button>
                    </form>
                @else
                    {{-- ==== MODE REQUEST: minta link reset ==== --}}
                    <form class="auth-card-body" method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div style="display:flex; justify-content:center; margin-bottom: 0.75rem;">
                            <img alt="Logo SOLID" src="{{ asset('assets/img/logo/logo.png') }}"
                                style="width:4.4rem; height:4.4rem; object-fit:contain;" />
                        </div>

                        <div class="auth-form-heading">
                            <h2 style="font-size:19px;">Lupa Kata Sandi</h2>
                            <p>Masukkan email akun Anda, kami akan kirim tautan reset</p>
                        </div>

                        @if (session('status'))
                            <div class="auth-warning">
                                <span class="material-symbols-outlined text-[16px] shrink-0">check_circle</span>
                                <span>{{ session('status') }}</span>
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
                            <label for="email">Email</label>
                            <div class="auth-field-icon-wrap">
                                <span class="auth-input-icon material-symbols-outlined text-[16px]">mail</span>
                                <input id="email" name="email" type="email" value="{{ old('email') }}"
                                    placeholder="Masukan email Anda" autocomplete="email" required />
                            </div>
                            @error('email')
                                <p class="text-error text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="auth-submit">
                            <span class="material-symbols-outlined text-[16px]">send</span>
                            Kirim Tautan Reset
                        </button>

                        <div class="auth-row-between" style="justify-content:center; margin-top:0.75rem;">
                            <a href="{{ route('login') }}">Kembali ke Login</a>
                        </div>
                    </form>
                @endif
            </div>

            <div class="auth-bottom-row">
                <p class="auth-footnote">
                    &copy; {{ date('Y') }} Pemerintah Kota Bogor. All right reserved
                </p>
            </div>
        </div>
    </div>
@endsection
