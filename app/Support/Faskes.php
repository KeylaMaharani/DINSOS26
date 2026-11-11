<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Daftar Puskesmas (config/faskes.php) untuk wilayah pemohon.
 *
 * Penyebab dropdown faskes kosong sebelumnya: registrasi menyimpan kecamatan HURUF BESAR
 * ("BOGOR TENGAH", mengikuti config/wilayah.php) sedangkan config/faskes.php memakai
 * "Bogor Tengah", dan lookup memakai kecocokan persis. Di sini dicocokkan tanpa peduli
 * besar-kecil huruf, dan kecamatan dicari dari kelurahan lebih dulu (lebih akurat).
 *
 * Kecamatan tidak dikenali -> semua Puskesmas ditampilkan (dropdown tidak pernah kosong).
 */
class Faskes
{
    public static function untuk(?string $kecamatan, ?string $kelurahan = null): array
    {
        $puskesmas = config('faskes.puskesmas', []);
        $semua = collect($puskesmas)->flatten()->unique()->values()->all();

        // 1. Kecamatan dari kelurahan (config/wilayah.php)
        $kec = null;
        $kel = self::norm($kelurahan);

        if ($kel !== '') {
            foreach (config('wilayah.kecamatan', []) as $namaKec => $daftarKel) {
                if (collect($daftarKel)->contains(fn ($k) => self::norm($k) === $kel)) {
                    $kec = $namaKec;
                    break;
                }
            }
        }

        // 2. Kalau tidak ketemu, pakai kolom kecamatan pengajuan
        $kec = self::norm($kec ?: $kecamatan);

        if ($kec !== '') {
            foreach ($puskesmas as $namaKec => $daftar) {
                if (self::norm($namaKec) === $kec) {
                    return array_values($daftar);
                }
            }
        }

        return $semua;
    }

    private static function norm($teks): string
    {
        return Str::lower(trim(preg_replace('/\s+/', ' ', (string) $teks)));
    }
}