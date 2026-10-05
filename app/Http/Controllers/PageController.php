<?php

namespace App\Http\Controllers;

use App\Models\PbiApbn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\PbiKriteriaParameter;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


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
     * Field "username" boleh berisi NIK (No KTP) ATAU No KK.
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

        $identifier = trim($validated['username']);

        // Batasi percobaan login agar No KK / NIK tidak mudah ditebak.
        $throttleKey = Str::lower($identifier) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $remember = $request->boolean('remember');

        foreach ($this->resolveLoginCandidates($identifier) as $user) {
            if (! Hash::check($validated['password'], $user->password)) {
                continue;
            }

            $roleSlug = $user->role->slug ?? null;

            // Halaman /login ini KHUSUS masyarakat. Role apapun selain
            // masyarakat (admin, kelurahan, kabid, kadis, operator_dinsos, dll)
            // wajib login lewat /admin-login.
            if ($roleSlug !== 'masyarakat') {
                throw ValidationException::withMessages([
                    'username' => 'Akun ini bukan akun masyarakat. Silakan login lewat halaman khusus petugas.',
                ]);
            }

            RateLimiter::clear($throttleKey);

            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Kalau data belum lengkap, langsung arahkan ke form lengkapi data
            $pbi = $user->pengajuanPbiApbn()->latest()->first();
            if ($pbi && $pbi->masyarakatBisaEdit()) {
                $totalParameter = PbiKriteriaParameter::aktif()->count();
                $belumLengkap = ! $pbi->latitude
                    || ! $pbi->longitude
                    || $pbi->jawabans()->count() < $totalParameter;

                if ($belumLengkap) {
                    return redirect()->route('masyarakat.pbi-apbn.lengkapi', $pbi);
                }
            }

            return redirect()->intended(route('masyarakat.dashboard'));
        }

        RateLimiter::hit($throttleKey);

        throw ValidationException::withMessages([
            'username' => 'No KK / NIK atau kata sandi salah.',
        ]);
    }

    /**
     * Cari kandidat user dari input login:
     *  1. NIK   -> users.username (diisi NIK saat registrasi)
     *  2. No KK -> pbi_apbns.no_kk -> pbi_apbns.user_id
     *
     * Satu KK bisa punya lebih dari satu pendaftaran, jadi hasilnya
     * Collection dan password dicek ke tiap kandidat.
     */
    private function resolveLoginCandidates(string $identifier): Collection
    {
        $candidates = User::with('role')
            ->where('username', $identifier)
            ->get();

        if (preg_match('/^\d{16}$/', $identifier)) {
            $userIds = PbiApbn::where('no_kk', $identifier)
                ->whereNotNull('user_id')
                ->pluck('user_id');

            if ($userIds->isNotEmpty()) {
                $candidates = $candidates
                    ->merge(User::with('role')->whereIn('id', $userIds)->get())
                    ->unique('id')
                    ->values();
            }
        }

        return $candidates;
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
