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
 * Semua role PBI APBD memakai SATU menu "Ajuan", jadi hanya key 'pbi_ajuan' yang dipakai sidebar.
 * Angkanya mengikuti tahap milik role (Role::tahapPbi()).
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

            if (! $role || ! $role->hasModule('pbi-apbd.ajuan')) {
                return $kosong;
            }

            $hasil = $kosong;
            $hasil['pbi_ajuan'] = self::pbiAjuan($role, $user);

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

    /** Menunggu tindakan di satu tahap approval (lurah/operator/kabid/kadis/dinkes/bpjs). */
    private static function pbiTahap(User $user, string $status, bool $batasiWilayah = false): int
    {
        $query = PbiApbd::query()->where('status', $status);

        // Lurah hanya melihat ajuan di kelurahannya (sama seperti PbiApprovalController::index)
        if ($batasiWilayah) {
            $query->untukKelurahan($user);
        }

        return self::belumDilihat($query, $user)->count();
    }

    /**
     * Badge menu "Ajuan". Super Admin = semua yang masih berjalan.
     * Role lain = antrean tahap milik role-nya.
     */
    private static function pbiAjuan($role, User $user): int
    {
        if ($role->isSuperAdmin()) {
            $query = PbiApbd::query()
                ->whereNotIn('status', ['disetujui', 'ditolak'])
                ->where(fn ($q) => $q->where('status', '!=', 'kelurahan')->orWhereNotNull('data_diisi_at'));

            return self::belumDilihat($query, $user)->count();
        }

        $tahap = $role->tahapPbi();

        if ($tahap === 'kelurahan') {
            return self::pbiKelurahan($user);
        }

        if ($tahap !== null) {
            return self::pbiTahap($user, PbiApbd::TAHAP[$tahap]['status'], $tahap === 'lurah');
        }

        return 0;
    }
}
