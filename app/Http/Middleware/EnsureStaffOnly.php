<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blokir user dengan role "masyarakat" dari seluruh area staf/admin.
 * Dipasang di atas group route admin, terpisah dari CheckRole
 * supaya tidak perlu daftar ulang semua slug role staf (kelurahan,
 * kabid, kadis, operator_dinsos, dst) yang bisa bertambah kapan saja
 * lewat menu Kelola Akses.
 */
class EnsureStaffOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $roleSlug = $user?->role?->slug;

        if ($roleSlug === 'masyarakat') {
            abort(403, 'Halaman ini khusus untuk petugas, bukan untuk akun masyarakat.');
        }

        return $next($request);
    }
}