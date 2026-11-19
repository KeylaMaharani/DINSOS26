<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Berlaku HANYA untuk role yang punya modul 'gc-petugas' (bukan Super Admin):
 * kalau data petugas belum lengkap, dilempar ke Home Ground Check.
 */
class GcPetugasProfil
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $role = $user?->role;

        if (! $user || ! $role || $role->isSuperAdmin() || ! $role->hasModule('gc-petugas')) {
            return $next($request);
        }

        if (blank($user->gc_profil_at)) {
            return redirect()->route('gc.home')
                ->with('error', 'Lengkapi data petugas terlebih dahulu sebelum melakukan Ground Check.');
        }

        return $next($request);
    }
}   