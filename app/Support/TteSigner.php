<?php

namespace App\Support;

use App\Models\User;
use RuntimeException;

class TteSigner
{
    public static function aktif(): bool
    {
        return config('tte.driver', 'none') !== 'none';
    }

    public static function sign(string $pdf, User $penandatangan, ?string $passphrase = null): string
    {
        return match (config('tte.driver', 'none')) {
            'ssn' => self::signSsn($pdf, $penandatangan, $passphrase),
            default => $pdf, // tanda tangan visual saja
        };
    }

    private static function signSsn(string $pdf, User $penandatangan, ?string $passphrase): string
    {
        if (blank($passphrase)) {
            throw new RuntimeException('Password TTE wajib diisi.');
        }

        // TODO: isi sesuai dokumentasi API SSN:
        //  - kirim $pdf + NIK penandatangan ($penandatangan->nik) + $passphrase
        //  - kembalikan isi PDF yang sudah ditandatangani (string biner)
        //  - jangan simpan / log $passphrase
        throw new RuntimeException('Integrasi API SSN belum diimplementasikan.');
    }
}