<?php

namespace App\Support;

use App\Models\Notifikasi;

class Notif
{
    public static function kirim(
        int $userId,
        string $tipe,
        string $judul,
        ?string $pesan = null,
        ?string $url = null,
        array $data = []
    ): Notifikasi {
        return Notifikasi::create([
            'user_id' => $userId,
            'tipe' => $tipe,
            'judul' => $judul,
            'pesan' => $pesan,
            'url' => $url,
            'data' => $data ?: null,
        ]);
    }
}
