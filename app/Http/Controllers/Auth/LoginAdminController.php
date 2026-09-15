<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginAdminController
{
    /**
     * Tampilkan halaman form login.
     */
    public function create(Request $request)
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        $request->session()->put('captcha_a', $a);
        $request->session()->put('captcha_b', $b);

        return view('auth.login-admin', [
            'captchaA' => $a,
            'captchaB' => $b,
        ]);
    }

    /**
     * Proses percobaan login.
     */
    public function store(Request $request)
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
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        // Login gagal
        throw ValidationException::withMessages([
            'username' => 'Username atau kata sandi salah.',
        ]);
    }

    /**
     * Proses logout user.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
