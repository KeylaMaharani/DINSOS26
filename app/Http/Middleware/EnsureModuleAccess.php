<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Dipakai di route seperti:
     *   ->middleware('module:pbi-apbd')                       // satu modul
     *   ->middleware('module:sigap-bencana,sigap-proses')     // salah satu dari beberapa modul
     *
     * Semua nama harus sama dengan key di App\Models\Role::MODULES.
     * Super Admin selalu lolos. Role lain cukup punya SALAH SATU modul yang disebut.
     * (Pemakaian satu modul yang lama tetap bekerja persis seperti sebelumnya.)
     */
    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        $role = $request->user()?->role;

        $boleh = $role && collect($modules)->contains(fn ($m) => $role->hasModule($m));

        if (! $boleh) {
            abort(403, 'Anda tidak memiliki akses ke modul ini. Hubungi Super Admin untuk meminta akses.');
        }

        return $next($request);
    }
}
