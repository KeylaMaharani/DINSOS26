<?php

namespace App\Http\Controllers\Kesan;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login KHUSUS Filantropi (halaman "Masuk sebagai Filantropi").
 * Petugas / Admin tetap lewat /admin-login, masyarakat lewat /login.
 */
class AuthController extends Controller
{
    public function create(Request $request)
    {
        if (Auth::check() && (Auth::user()->role->slug ?? null) === 'filantropi') {
            return redirect()->route('kesan.app.beranda');
        }

        return view('kesan.masuk');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $identifier = trim($data['username']);
        $key = 'kesan-login|' . Str::lower($identifier) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $detik = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan. Coba lagi dalam {$detik} detik.",
            ]);
        }

        $user = User::with('role')
            ->where(fn ($q) => $q->where('username', $identifier)->orWhere('email', $identifier))
            ->first();

        // Cek kata sandi DULU, baru cek peran, supaya jenis akun tidak bisa ditebak tanpa kata sandi.
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages(['username' => 'Username / email atau kata sandi salah.']);
        }

        if (($user->role->slug ?? null) !== 'filantropi') {
            throw ValidationException::withMessages([
                'username' => 'Akun ini bukan akun Filantropi. Petugas silakan pilih "Masuk sebagai Admin/Petugas".',
            ]);
        }

        RateLimiter::clear($key);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('kesan.app.beranda');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('kesan');
    }
}
