<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Notifikasi (lonceng) untuk petugas Ngopi Pagi, lewat App\Support\Notif::kirim().
 *
 *   kePetugas($judul, $pesan, $url, $data, $targetUserId = null, $kecualiUserId = null)
 *     - $targetUserId diisi  -> hanya ke user itu (konselor/petugas pemegang kasus)
 *     - $targetUserId kosong -> ke semua petugas ber-modul dayasos-ngopi-pagi (+ Super Admin)
 *     - $kecualiUserId       -> jangan kirim ke user ini (pelaku aksi)
 *
 * Kegagalan notifikasi tidak pernah menggagalkan chat; hanya dicatat di storage/logs.
 */
class NgopiPagiNotifier
{
    public const MODUL = 'dayasos-ngopi-pagi';
    public const TIPE = 'ngopi_pagi';

    public static function kePetugas(string $judul, string $pesan, ?string $url = null, array $data = [], ?int $targetUserId = null, ?int $kecualiUserId = null): void
    {
        try {
            foreach (self::penerima($targetUserId, $kecualiUserId) as $userId) {
                Notif::kirim($userId, self::TIPE, $judul, $pesan, $url, $data);
            }
        } catch (\Throwable $e) {
            Log::warning('NgopiPagiNotifier gagal: ' . $e->getMessage());
        }
    }

    /** @return int[] */
    private static function penerima(?int $target, ?int $kecuali): array
    {
        if ($target) {
            return $target === $kecuali ? [] : [$target];
        }

        return User::with('role')->get()
            ->filter(fn ($u) => $u->role
                && $u->id !== $kecuali
                && ($u->role->isSuperAdmin() || $u->role->hasModule(self::MODUL)))
            ->pluck('id')->all();
    }
}
