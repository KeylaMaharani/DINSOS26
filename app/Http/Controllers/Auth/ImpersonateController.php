<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ImpersonateRequest;
use App\Models\Notifikasi;
use App\Models\User;
use App\Support\Notif;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * "Masuk sebagai" dengan izin pemilik akun:
 *  1. Super Admin minta izin  -> notif muncul di lonceng pemilik akun
 *  2. Pemilik akun Setujui/Tolak -> jika setuju, OTP tampil di lonceng pemilik akun
 *  3. Super Admin memasukkan OTP -> baru sesi "masuk sebagai" dimulai
 */
class ImpersonateController extends Controller
{
    private const LANDING = [
        'beranda' => 'dashboard',
        'pbi-kelurahan' => 'pbi-kelurahan.index',
        'layanan-kartu-kks' => 'layanan.kartu-kks.riwayat',
        'layanan-dtsen' => 'layanan.dtsen.riwayat',
        'sigap-proses' => 'sigap.proses.index',
        'ground-check' => 'ground-check.index',
        'pbi-apbd' => 'pbi-apbd.ajuan.index',
        'kartu-kks' => 'kartu-kks.ajuan',
        'dtsen' => 'dtsen.ajuan',
        'sigap-bencana' => 'sigap.index',
        'dokumen' => 'dokumen.index',
    ];

    // ================= SISI SUPER ADMIN =================

    /** POST: Super Admin meminta izin masuk ke akun $akun. */
    public function minta(Request $request, User $akun): RedirectResponse
    {
        $admin = $request->user();

        abort_unless($admin->role?->isSuperAdmin(), 403);
        abort_if($request->session()->has('impersonator_id'), 403, 'Kembali ke Super Admin dulu sebelum berpindah akun.');
        abort_if($akun->id === $admin->id, 422, 'Tidak bisa masuk sebagai diri sendiri.');
        abort_if($akun->loadMissing('role')->role?->isSuperAdmin(), 422, 'Tidak bisa masuk sebagai Super Admin lain.');

        // Permintaan lama yang masih terbuka untuk akun ini dibatalkan.
        ImpersonateRequest::where('admin_id', $admin->id)
            ->where('target_user_id', $akun->id)
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'expired', 'otp' => null]);

        $izin = ImpersonateRequest::create([
            'admin_id' => $admin->id,
            'target_user_id' => $akun->id,
            'status' => 'pending',
            'expired_at' => now()->addMinutes(ImpersonateRequest::MENIT_MENUNGGU_IZIN),
            'ip' => $request->ip(),
        ]);

        Notif::kirim(
            $akun->id,
            'izin_masuk',
            'Permintaan izin masuk ke akun Anda',
            "Super Admin ({$admin->name}) meminta izin untuk masuk ke akun Anda. Setujui hanya jika Anda mengetahui permintaan ini.",
            null,
            ['izin_id' => $izin->id]
        );

        return redirect()->route('akun.izin.show', $izin);
    }

    public function show(Request $request, ImpersonateRequest $izin)
    {
        $this->pastikanMilikAdmin($request, $izin);
        $izin->load('target.role');

        return view('kelola-akses.izin-masuk', compact('izin'));
    }

    /** JSON, dipolling halaman izin. */
    public function status(Request $request, ImpersonateRequest $izin): JsonResponse
    {
        $this->pastikanMilikAdmin($request, $izin);

        return response()->json(['status' => $izin->statusEfektif()]);
    }

    public function verifikasi(Request $request, ImpersonateRequest $izin): RedirectResponse
    {
        $this->pastikanMilikAdmin($request, $izin);
        abort_if($request->session()->has('impersonator_id'), 403);

        $request->validate(
            ['otp' => ['required', 'digits:6']],
            ['otp.required' => 'Kode OTP wajib diisi.', 'otp.digits' => 'Kode OTP harus 6 digit angka.']
        );

        if ($izin->statusEfektif() !== 'approved') {
            return back()->with('error', 'Izin belum disetujui, ditolak, atau sudah kedaluwarsa.');
        }

        if ($izin->percobaan >= ImpersonateRequest::MAKS_PERCOBAAN) {
            $izin->update(['status' => 'expired', 'otp' => null]);

            return back()->with('error', 'Terlalu banyak percobaan salah. Ajukan permintaan izin baru.');
        }

        if (! hash_equals((string) $izin->otp, (string) $request->input('otp'))) {
            $izin->increment('percobaan');

            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        // Sekali pakai (atomik): hanya satu request yang bisa mengubah approved -> used.
        $terpakai = ImpersonateRequest::whereKey($izin->id)->where('status', 'approved')->update([
            'status' => 'used',
            'otp' => null,
            'digunakan_at' => now(),
            'ip' => $request->ip(),
        ]);

        if (! $terpakai) {
            return back()->with('error', 'Izin sudah tidak berlaku.');
        }

        $admin = $request->user();
        $akun = $izin->target;

        $request->session()->put('impersonator_id', $admin->id);
        Auth::login($akun);
        $request->session()->regenerate();

        Notif::kirim($akun->id, 'info', 'Super Admin masuk ke akun Anda',
            "{$admin->name} masuk ke akun Anda atas izin Anda pada " . now()->format('d/m/Y H:i') . '.');

        return redirect()->to($this->landingUrl($akun));
    }

    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->pull('impersonator_id');
        abort_unless($adminId, 403);

        $admin = User::with('role')->find($adminId);
        abort_unless($admin && $admin->role?->isSuperAdmin(), 403);

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('akun.index')->with('success', 'Kembali ke akun Super Admin.');
    }

    // ================= SISI PEMILIK AKUN =================

    public function setujui(Request $request, ImpersonateRequest $izin)
    {
        $this->pastikanMilikTarget($request, $izin);

        if ($izin->statusEfektif() !== 'pending') {
            return $this->balas($request, false, 'Permintaan ini sudah tidak berlaku.');
        }

        $izin->update([
            'status' => 'approved',
            'otp' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'disetujui_at' => now(),
            'expired_at' => now()->addMinutes(ImpersonateRequest::MENIT_OTP),
        ]);

        $this->tandaiDibaca($izin);
        Notif::kirim($izin->admin_id, 'info', 'Izin masuk disetujui',
            "{$request->user()->name} menyetujui. Minta kode OTP dari pemilik akun.");

        return $this->balas($request, true);
    }

    public function tolak(Request $request, ImpersonateRequest $izin)
    {
        $this->pastikanMilikTarget($request, $izin);

        if ($izin->statusEfektif() !== 'pending') {
            return $this->balas($request, false, 'Permintaan ini sudah tidak berlaku.');
        }

        $izin->update(['status' => 'rejected', 'otp' => null]);

        $this->tandaiDibaca($izin);
        Notif::kirim($izin->admin_id, 'info', 'Izin masuk ditolak',
            "{$request->user()->name} menolak permintaan masuk ke akunnya.");

        return $this->balas($request, true);
    }

    // ================= HELPER =================

    private function pastikanMilikAdmin(Request $request, ImpersonateRequest $izin): void
    {
        abort_unless($request->user()->role?->isSuperAdmin() && $izin->admin_id === $request->user()->id, 403);
    }

    private function pastikanMilikTarget(Request $request, ImpersonateRequest $izin): void
    {
        abort_unless($izin->target_user_id === $request->user()->id, 403);
        abort_if($request->session()->has('impersonator_id'), 403, 'Tidak bisa menyetujui saat sedang dalam mode "Masuk sebagai".');
    }

    private function tandaiDibaca(ImpersonateRequest $izin): void
    {
        Notifikasi::where('user_id', $izin->target_user_id)
            ->where('tipe', 'izin_masuk')
            ->where('data->izin_id', $izin->id)
            ->update(['dibaca_at' => now()]);
    }

    private function balas(Request $request, bool $ok, ?string $pesan = null)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $pesan], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'error', $pesan ?? 'Berhasil.');
    }

    private function landingUrl(User $akun): string
    {
        $akun->loadMissing('role');

        if ($akun->role?->slug === 'masyarakat') {
            return route('masyarakat.dashboard');
        }

        foreach (self::LANDING as $module => $routeName) {
            if ($akun->role?->hasModule($module) && Route::has($routeName)) {
                return route($routeName);
            }
        }

        return route('dashboard');
    }
}
