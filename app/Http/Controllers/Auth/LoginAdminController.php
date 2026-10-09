<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
            $user = Auth::user();
            $roleSlug = $user->role->slug ?? null;

            // Halaman /admin-login ini KHUSUS petugas (kelurahan, kabid,
            // kadis, operator_dinsos, superadmin, dll). Akun masyarakat
            // wajib login lewat halaman utama (/login).
            if ($roleSlug === 'masyarakat') {
                Auth::logout();
                $request->session()->invalidate();

                throw ValidationException::withMessages([
                    'username' => 'Akun ini adalah akun masyarakat. Silakan login lewat halaman utama pendaftaran.',
                ]);
            }

            $request->session()->regenerate();

            // Role yang tidak punya modul Beranda (mis. Kelurahan SIGAP) tidak
            // boleh dikirim ke /dashboard (akan 403). Buang juga tujuan
            // "intended" yang tersimpan, karena bisa saja isinya /dashboard.
            if (! ($user->role?->hasModule('beranda'))) {
                $request->session()->forget('url.intended');

                return redirect()->to($this->halamanAwal($user->role));
            }

            return redirect()->intended('/dashboard');
        }

        // Login gagal
        throw ValidationException::withMessages([
            'username' => 'Username atau kata sandi salah.',
        ]);
    }

    /**
     * Halaman pertama setelah login untuk role yang TIDAK punya modul Beranda:
     * modul pertama yang dimiliki role tersebut, sesuai urutan di bawah.
     * Urutan = prioritas; ubah/tambah baris di sini kalau ada modul baru.
     */
    private function halamanAwal($role): string
    {
        $urutan = [
            'sigap-proses' => 'sigap.proses.index',
            'sigap-bencana' => 'sigap.index',
            'pbi-kelurahan' => 'pbi-kelurahan.index',
            'layanan-kartu-kks' => 'layanan.kartu-kks.ajukan',
            'layanan-dtsen' => 'layanan.dtsen.ajukan',
            'pbi-apbd' => 'pbi-apbd.ajuan.index',
            'kartu-kks' => 'kartu-kks.ajuan',
            'dtsen' => 'dtsen.ajuan',
            'asesmen-spmb' => 'asesmen_spmb.ajuan',
            'kedaruratan-medis' => 'kedaruratan_medis.ajuan',
            'dokumen' => 'dokumen.index',
        ];

        foreach ($urutan as $modul => $namaRute) {
            if ($role && $role->hasModule($modul) && Route::has($namaRute)) {
                return route($namaRute);
            }
        }

        // Role belum diberi modul apa pun: tampilkan pesan 403 yang jelas
        // dari middleware (bukan error aneh).
        return route('dashboard');
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
