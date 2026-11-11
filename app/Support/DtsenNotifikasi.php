<?php

namespace App\Support;

use App\Models\DtsenPermohonan;
use App\Models\User;

/**
 * Notifikasi lonceng untuk alur DTSEN. Pembuatan barisnya lewat App\Support\Notif::kirim();
 * kelas ini hanya menentukan penerima (role / warga) dan isi pesan DTSEN.
 * Kegagalan kirim notifikasi tidak boleh menggagalkan proses pengajuan.
 */
class DtsenNotifikasi
{
    /** Petugas pada role tujuan: pengajuan masuk ke antrean mereka. */
    public static function keRole(DtsenPermohonan $p, ?string $roleSlug, string $judul, ?string $pesan = null): void
    {
        if (! $roleSlug) {
            return;
        }

        try {
            $url = route('dtsen.show', $p);
            $users = User::whereHas('role', fn ($q) => $q->where('slug', $roleSlug))->pluck('id');

            foreach ($users as $userId) {
                self::buat($userId, $p, $judul, $pesan, $url);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Warga pemilik pengajuan (dilewati bila pengajuan tidak terhubung ke akun). */
    public static function keWarga(DtsenPermohonan $p, string $judul, ?string $pesan = null): void
    {
        if (! $p->user_id) {
            return;
        }

        try {
            self::buat($p->user_id, $p, $judul, $pesan, route('masyarakat.dtsen.lengkapi', $p));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function buat($userId, DtsenPermohonan $p, string $judul, ?string $pesan, string $url): void
    {
        Notif::kirim(
            (int) $userId,
            'dtsen',
            $judul,
            $pesan,
            $url,
            ['dtsen_permohonan_id' => $p->getKey(), 'no_permohonan' => $p->no_permohonan]
        );
    }
}
