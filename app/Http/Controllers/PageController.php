<?php

namespace App\Http\Controllers;

use App\Models\PbiApbd;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

            // Setelah login selalu masuk ke dashboard masyarakat.
            // (Kalau data belum lengkap, dashboard menampilkan tombol "Lengkapi Data".)
            return redirect()->route('masyarakat.dashboard');
        }

        RateLimiter::hit($throttleKey);

        throw ValidationException::withMessages([
            'username' => 'No KK / NIK atau kata sandi salah.',
        ]);
    }

    /**
     * Cari kandidat user dari input login:
     *  1. NIK   -> users.username (diisi NIK saat registrasi)
     *  2. No KK -> pbi_apbds.no_kk -> pbi_apbds.user_id
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
            $userIds = PbiApbd::where('no_kk', $identifier)
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
        // View sudah diganti nama: tutorial-pendaftaran -> informasi-pbi
        // (route name & URL tetap 'tutorial-pendaftaran').
        //
        // Jika form pendaftaran di halaman ini ingin langsung tampil
        // (tanpa tombol "Mulai Pendaftaran"), aktifkan snippet captcha ini
        // SETELAH memastikan key session-nya sama dengan yang dipakai
        // RegistrasiController@step1Store:
        //
        // $a = random_int(1, 9);
        // $b = random_int(1, 9);
        // session()->put('captcha_a', $a);
        // session()->put('captcha_b', $b);
        // return view('informasi-pbi', ['captchaA' => $a, 'captchaB' => $b]);

        return view('informasi-pbi');
    }

    /**
     * Halaman PPID (Pejabat Pengelola Informasi dan Dokumentasi).
     * Saat ini view memakai data dummy. Nanti kirim variabel
     * 'dokumenPpid' dari database, mis.:
     *   return view('ppid', ['dokumenPpid' => $dokumen]);
     */
    public function ppid()
    {
        return view('ppid');
    }

    /**
     * Halaman Regulasi (tabel peraturan + unduh berkas).
     * Saat ini data ada di view. Untuk data dari database kirim
     * 'daftarRegulasi' => [['nama'=>..., 'jenis'=>..., 'nomor'=>...,
     *                       'tentang'=>..., 'file'=>url|null], ...]
     */
    public function regulasi()
    {
        return view('regulasi');
    }

    /**
     * Halaman PPKS & PSKS (data per kecamatan / kelurahan).
     * Saat ini view memakai data contoh. Untuk data asli kirim:
     *   'dataPpks' => ['Bogor Barat' => ['Bubulak' => 120, ...], ...]
     *   'dataPsks' => (struktur sama)
     * Query ?tahun=2025 dibaca oleh view (tahun yang tidak dikenal
     * otomatis jatuh ke 2025).
     */
    public function ppksPsks(Request $request)
    {
        return view('ppks-psks', [
            'tahun' => (int) $request->query('tahun', 2025),
        ]);
    }

    /**
     * Halaman Kontak / Hubungi Kami (form + info kontak).
     */
    public function kontak(Request $request)
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        // Key session khusus kontak, supaya tidak bentrok dengan captcha login.
        $request->session()->put('kontak_captcha_a', $a);
        $request->session()->put('kontak_captcha_b', $b);

        return view('kontak', [
            'captchaA' => $a,
            'captchaB' => $b,
        ]);
    }

    /**
     * Proses form Hubungi Kami.
     * Saat ini pesan dicatat ke log (storage/logs/laravel.log).
     * TODO: simpan ke database (model Pesan) atau kirim email ke dinsos.
     */
    public function kontakStore(Request $request)
    {
        // Honeypot: bot biasanya mengisi field ini. Pura-pura sukses.
        if ($request->filled('website')) {
            return redirect()->route('kontak')->with('success', 'Terima kasih, pesan Anda telah terkirim.');
        }

        $expected = (int) $request->session()->get('kontak_captcha_a', 0)
            + (int) $request->session()->get('kontak_captcha_b', 0);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'pesan' => ['required', 'string', 'max:2000'],
            'captcha' => ['required', 'numeric'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'no_hp.required' => 'No. HP wajib diisi.',
            'no_hp.regex' => 'Format No. HP tidak valid.',
            'pesan.required' => 'Isi pesan wajib diisi.',
            'captcha.required' => 'Jawaban verifikasi wajib diisi.',
            'captcha.numeric' => 'Jawaban verifikasi harus berupa angka.',
        ]);

        if ((int) $validated['captcha'] !== $expected) {
            throw ValidationException::withMessages([
                'captcha' => 'Jawaban verifikasi salah, silakan coba lagi.',
            ]);
        }

        $request->session()->forget(['kontak_captcha_a', 'kontak_captcha_b']);

        \Illuminate\Support\Facades\Log::info('Pesan Hubungi Kami', [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'pesan' => $validated['pesan'],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('kontak')
            ->with('success', 'Terima kasih, pesan Anda telah terkirim. Kami akan segera menanggapi.');
    }
}
