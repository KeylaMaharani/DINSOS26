<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Halaman-halaman statis SOLID v4 Dinas Sosial Kota Bogor.
 */
class PageController extends Controller
{
    public function home()
    {
        return view('home');
    }

    /**
     * Tampilkan form login user biasa.
     */
    public function login(Request $request)
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        $request->session()->put('captcha_a', $a);
        $request->session()->put('captcha_b', $b);

        return view('auth.login', [
            'captchaA' => $a,
            'captchaB' => $b,
        ]);
    }

    /**
     * Proses login user biasa.
     */
    public function loginStore(Request $request)
    {
        $expected = (int) $request->session()->get('captcha_a', 0)
            + (int) $request->session()->get('captcha_b', 0);

        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'captcha' => ['required', 'numeric'],
        ]);

        if ((int) $validated['captcha'] !== $expected) {
            throw ValidationException::withMessages([
                'captcha' => 'Jawaban verifikasi salah, silakan coba lagi.',
            ]);
        }

        $request->session()->forget(['captcha_a', 'captcha_b']);

        $credentials = [
            'username' => $validated['username'],
            'password' => $validated['password'],
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // TODO: sesuaikan dengan cara kamu menandai admin
            if ($user->role === 'admin') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'username' => 'Akun admin harus login lewat halaman admin.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'username' => 'Username atau kata sandi salah.',
        ]);
    }

    public function registrasiStep1()
    {
        return view('registrasi.step1');
    }

    public function registrasiStep2()
    {
        return view('registrasi.step2');
    }

    public function cekDensil()
    {
        return view('cek-densil');
    }

    public function kejadianBencana()
    {
        return view('kejadian-bencana');
    }

    public function kesan()
    {
        return view('kesan');
    }

    public function tutorialPendaftaran()
    {
        return view('tutorial-pendaftaran');
    }
}
