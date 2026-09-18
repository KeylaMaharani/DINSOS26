<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Dipakai di route seperti: ->middleware('module:pbi-apbn')
     * $module harus sama dengan salah satu key di App\Models\Role::MODULES.
     *
     * Super Admin selalu lolos (lihat Role::isSuperAdmin()).
     * Role lain HARUS punya $module di dalam kolom `permissions`-nya.
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $role = $request->user()?->role;

        if (! $role || ! $role->hasModule($module)) {
            abort(403, 'Anda tidak memiliki akses ke modul ini. Hubungi Super Admin untuk meminta akses.');
        }

        return $next($request);
    }
}
