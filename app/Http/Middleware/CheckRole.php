<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cek apakah user yang login punya salah satu role (slug) yang diizinkan.
 *
 * Pemakaian di route:
 *   Route::middleware('role:kelurahan,super_admin')->group(...);
 *
 * Dibuat toleran terhadap role yang belum punya kolom "slug" terisi
 * (misalnya role dibuat lewat form Kelola Akses yang cuma minta Nama +
 * Deskripsi): kalau slug kosong, di-generate on-the-fly dari nama role.
 * Role yang namanya mengandung kata "admin" (Admin, Super Admin, dst)
 * selalu diloloskan tanpa perlu dicantumkan di daftar role route.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Anda harus login.');
        }

        $role = $user->role;

        if (! $role) {
            abort(403, 'Akun Anda belum memiliki Hak Akses (role). Hubungi Super Admin.');
        }

        // Slug resmi kalau ada, kalau tidak buat dari nama role sebagai fallback
        // (memakai Str::slug() default / strip "-", sama seperti yang dipakai
        // UserController@generateUniqueSlug di project Anda).
        $userRoleSlug = $role->slug ?: Str::slug($role->name);

        // Admin selalu boleh masuk ke menu manapun, apapun cara penulisan namanya.
        $isAdmin = str_contains(strtolower($role->name ?? ''), 'admin')
            || in_array($userRoleSlug, ['admin', 'super_admin', 'superadmin'], true);

        if ($isAdmin) {
            return $next($request);
        }

        if (! in_array($userRoleSlug, $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk menu ini.');
        }

        return $next($request);
    }
}
