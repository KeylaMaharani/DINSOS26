<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Fitur "Masuk sebagai" untuk Super Admin.
 *
 * Super Admin yang sedang login bisa membuka tampilan akun staf mana pun
 * TANPA perlu tahu password-nya (dan tidak terpengaruh kalau staf sudah
 * mengganti password). ID Super Admin disimpan di session ('impersonator_id')
 * supaya bisa kembali lewat tombol di banner.
 */
class ImpersonateController extends Controller
{
    /**
     * Urutan modul -> route tujuan setelah masuk, supaya akun yang tidak
     * punya modul 'beranda' (mis. kelurahan) tidak mendarat di halaman 403.
     */
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

    public function start(Request $request, User $akun): RedirectResponse
    {
        $admin = $request->user();

        abort_unless($admin->role?->isSuperAdmin(), 403);
        abort_if($request->session()->has('impersonator_id'), 403, 'Kembali ke Super Admin dulu sebelum berpindah akun.');
        abort_if($akun->id === $admin->id, 422, 'Tidak bisa masuk sebagai diri sendiri.');

        $request->session()->put('impersonator_id', $admin->id);

        Auth::login($akun);
        $request->session()->regenerate();

        return redirect()->to($this->landingUrl($akun));
    }

    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->pull('impersonator_id');
        abort_unless($adminId, 403);

        $admin = User::with('role')->find($adminId);

        // Pastikan yang dikembalikan memang masih Super Admin.
        abort_unless($admin && $admin->role?->isSuperAdmin(), 403);

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('akun.index')->with('success', 'Kembali ke akun Super Admin.');
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
