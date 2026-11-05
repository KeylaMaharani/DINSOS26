<?php

namespace App\Support;

use App\Models\GcSasaran;
use App\Models\Notifikasi;
use App\Models\Role;
use App\Models\User;

/**
 * Memasukkan satu orang (pemohon DTSEN / Kartu KKS) ke Daftar Sasaran Ground Check.
 * - No KK sudah ada di Ground Check -> ikut keluarga itu (wilayah/alamat/desil mengikuti bila kosong).
 * - No KK baru -> otomatis jadi keluarga baru.
 * NIK yang sudah ada tidak ditimpa. Notifikasi hanya dikirim bila sasaran benar-benar baru.
 */
class GroundCheckSinkron
{
    /** @param array{nik:mixed,nama:mixed,no_kk:mixed,tanggal_lahir?:mixed,alamat?:mixed,kelurahan?:mixed,kecamatan?:mixed} $d */
    public static function individu(array $d): bool
    {
        $nik = preg_replace('/\D/', '', (string) ($d['nik'] ?? ''));
        $kk = preg_replace('/\D/', '', (string) ($d['no_kk'] ?? ''));

        if (strlen($nik) !== 16 || strlen($kk) !== 16) {
            return false;
        }

        $induk = GcSasaran::where('no_kk', $kk)->first();

        $s = GcSasaran::firstOrCreate(['nik' => $nik], [
            'nama' => mb_strtoupper(trim((string) ($d['nama'] ?? ''))),
            'no_kk' => $kk,
            'tanggal_lahir' => $d['tanggal_lahir'] ?? null,
            'kecamatan' => $induk->kecamatan ?? ($d['kecamatan'] ?? null),
            'kelurahan' => $induk->kelurahan ?? ($d['kelurahan'] ?? null),
            'alamat' => $induk->alamat ?? ($d['alamat'] ?? null),
            'rt' => $induk->rt ?? null,
            'rw' => $induk->rw ?? null,
            'desil' => $induk->desil ?? null,
        ]);

        if (! $s->wasRecentlyCreated) {
            return false;
        }

        $roleIds = Role::all()
            ->filter(fn ($r) => ! $r->isSuperAdmin() && $r->hasModule('ground-check'))
            ->pluck('id');

        User::whereIn('role_id', $roleIds)->get()->each(
            fn ($u) => Notifikasi::create([
                'user_id' => $u->id,
                'tipe' => 'ground_check_baru',
                'judul' => 'Sasaran Ground Check baru',
                'pesan' => $s->nama . ' (KK ' . $kk . ') masuk daftar sasaran.',
                'url' => route('ground-check.keluarga', $kk),
            ])
        );

        return true;
    }
}