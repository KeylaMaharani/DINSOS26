<?php

namespace App\Support;

use App\Models\PbiApbd;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Angka badge merah di sidebar admin = jumlah permohonan yang SEDANG MENUNGGU
 * tindakan role yang login DAN belum pernah dibuka detailnya oleh user tsb pada tahap ini.
 *
 * Key hasil dipakai di layouts/admin.blade.php lewat 'badge' => '<key>':
 *   pbi_kelurahan, pbi_ajuan, pbi_operator, pbi_kabid, pbi_kadis, pbi_dinkes, pbi_bpjs
 */
class MenuBadge
{
    public static function untuk(?User $user): array
    {
        $kosong = ['pbi_kelurahan' => 0, 'pbi_ajuan' => 0];
        foreach (array_keys(PbiApbd::TAHAP) as $t) {
            $kosong['pbi_' . $t] = 0;
        }

        if (! $user) {
            return $kosong;
        }

        try {
            $user->loadMissing('role');
            $role = $user->role;

            if (! $role) {
                return $kosong;
            }

            $hasil = [
                'pbi_kelurahan' => $role->hasModule('pbi-kelurahan') ? self::pbiKelurahan($user) : 0,
                'pbi_ajuan' => $role->hasModule('pbi-apbd') ? self::pbiAjuan($role, $user) : 0,
            ];

            foreach (PbiApbd::TAHAP as $t => $cfg) {
                $hasil['pbi_' . $t] = $role->hasModule($cfg['module'])
                    ? self::pbiTahap($user, $cfg['status'])
                    : 0;
            }

            return $hasil;
        } catch (Throwable $e) {
            // Badge hanya pemanis: jangan sampai seluruh halaman admin ikut error.
            report($e);

            return $kosong;
        }
    }

    /** Hanya permohonan yang belum dibuka user ini pada tahap (status) yang sekarang. */
    private static function belumDilihat($query, User $user)
    {
        return $query->whereNotExists(function ($q) use ($user) {
            $q->select(DB::raw(1))
                ->from('pbi_apbd_dilihat')
                ->whereColumn('pbi_apbd_dilihat.pbi_apbd_id', 'pbi_apbds.id')
                ->whereColumn('pbi_apbd_dilihat.status', 'pbi_apbds.status')
                ->where('pbi_apbd_dilihat.user_id', $user->id);
        });
    }

    /** Menunggu verifikasi Kelurahan (hanya wilayah petugas yang login). */
    private static function pbiKelurahan(User $user): int
    {
        $query = PbiApbd::query()
            ->untukKelurahan($user)
            ->where('status', 'kelurahan')
            ->whereNotNull('data_diisi_at')
            ->where(fn ($q) => $q->where('dikembalikan_ke_masyarakat', false)
                ->orWhereNull('dikembalikan_ke_masyarakat'));

        return self::belumDilihat($query, $user)->count();
    }

    /** Menunggu tindakan di satu tahap approval (operator/kabid/kadis/dinkes/bpjs). */
    private static function pbiTahap(User $user, string $status): int
    {
        $query = PbiApbd::query()->where('status', $status);

        return self::belumDilihat($query, $user)->count();
    }

    /**
     * Menu "Ajuan" lama (lintas tahap). Super Admin = semua yang berjalan.
     * Role lain dihitung sesuai tahap yang role-nya cocok.
     */
    private static function pbiAjuan($role, User $user): int
    {
        $query = PbiApbd::query()
            ->whereNotIn('status', ['disetujui', 'ditolak'])
            ->where(fn ($q) => $q->where('status', '!=', 'kelurahan')->orWhereNotNull('data_diisi_at'));

        if (! $role->isSuperAdmin()) {
            $slug = $role->normalizedSlug();

            $tahap = collect(PbiApbd::STAGES)
                ->reject(fn ($s, $key) => $key === 'kelurahan')
                ->filter(fn ($s) => strtolower(str_replace(['-', ' '], '_', $s['role'])) === $slug)
                ->keys()
                ->all();

            if (! $tahap) {
                return 0;
            }

            $query->whereIn('status', $tahap);
        }

        return self::belumDilihat($query, $user)->count();
    }
}
