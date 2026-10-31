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
 * - Dihitung dari status saat ini, jadi otomatis berpindah mengikuti alur
 *   Kelurahan -> Operator Dinsos -> Kabid -> Kadis.
 * - Begitu user membuka halaman detail, permohonan itu tidak dihitung lagi untuk user tsb
 *   (dicatat di tabel pbi_apbd_dilihat lewat PbiApbd::tandaiDilihat()).
 * - Saat status berubah (next/back) catatan "dilihat" untuk tahap baru dihapus,
 *   jadi role di tahap berikutnya melihatnya sebagai notifikasi baru.
 * - Draft (warga belum menekan "Simpan Data", data_diisi_at kosong) tidak dihitung.
 *
 * Key hasil dipakai di layouts/admin.blade.php lewat 'badge' => '<key>'.
 */
class MenuBadge
{
    /** @return array{pbi_kelurahan:int, pbi_ajuan:int} */
    public static function untuk(?User $user): array
    {
        $kosong = ['pbi_kelurahan' => 0, 'pbi_ajuan' => 0];

        if (! $user) {
            return $kosong;
        }

        try {
            $user->loadMissing('role');
            $role = $user->role;

            if (! $role) {
                return $kosong;
            }

            return [
                'pbi_kelurahan' => $role->hasModule('pbi-kelurahan') ? self::pbiKelurahan($user) : 0,
                'pbi_ajuan' => $role->hasModule('pbi-apbd') ? self::pbiAjuan($role, $user) : 0,
            ];
        } catch (Throwable $e) {
            // Badge hanya pemanis: kalau gagal (mis. migration belum dijalankan),
            // jangan sampai seluruh halaman admin ikut error.
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
            // Yang sedang dikembalikan ke warga menunggu warga, bukan Kelurahan
            ->where(fn ($q) => $q->where('dikembalikan_ke_masyarakat', false)
                ->orWhereNull('dikembalikan_ke_masyarakat'));

        return self::belumDilihat($query, $user)->count();
    }

    /** Menunggu tindakan role Dinsos ini (Operator / Kabid / Kadis). Super Admin = semua yang berjalan. */
    private static function pbiAjuan($role, User $user): int
    {
        $query = PbiApbd::query()
            ->whereNotIn('status', ['disetujui', 'ditolak'])
            // Buang draft (status awal 'kelurahan' yang belum pernah disimpan warga)
            ->where(fn ($q) => $q->where('status', '!=', 'kelurahan')->orWhereNotNull('data_diisi_at'));

        if (! $role->isSuperAdmin()) {
            $slug = $role->normalizedSlug();

            $tahap = collect(PbiApbd::STAGES)
                ->reject(fn ($s, $key) => $key === 'kelurahan') // Kelurahan punya menu & badge sendiri
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
