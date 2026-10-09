<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'permissions'])]
class Role extends Model
{
    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Daftar modul yang bisa dicentang di form "Kelola Hak Akses".
     * key   = dipakai di middleware module:xxx & pengecekan hasModule().
     * value = label checkbox. Urutan di sini = urutan tampil, dan diberi
     *         awalan nama bidang supaya otomatis terkelompok.
     *
     * Tambahkan/hapus baris di sini kalau ada modul baru: otomatis muncul di
     * form Tambah/Edit Hak Akses tanpa perlu ubah view.
     * (Key lama TIDAK diubah, jadi hak akses role yang sudah tersimpan tetap berlaku.)
     */
    public const MODULES = [
        'beranda' => 'Beranda',

        // --- PFM (Monitoring) ---
        'pbi-apbd' => 'PFM - PBI APBD',
        'kartu-kks' => 'PFM - Kartu KKS',
        'dtsen' => 'PFM - DTSEN',

        // --- Dayasos ---
        'dayasos-ngopi-pagi' => 'Dayasos - Ngopi Pagi',
        'dayasos-kesan' => 'Dayasos - Kesan',

        // --- Perlinsos ---
        'sigap-bencana' => 'Perlinsos - SIGAP Bencana (Dinsos)',
        'sigap-proses' => 'Perlinsos - Proses Bantuan (Kelurahan/OPD Wilayah)',

        // --- Rehabsos ---
        'rehabsos-ppks' => 'Rehabsos - PPKS',
        'rehabsos-rumah-singgah' => 'Rehabsos - Rumah Singgah',
        'rehabsos-bantuan' => 'Rehabsos - Data Permohonan Bantuan',

        // --- Kelurahan ---
        'pbi-kelurahan' => 'Verifikasi Kelurahan - PBI APBD',
        'layanan-kartu-kks' => 'Layanan Kelurahan - Pengajuan Kartu KKS',
        'layanan-dtsen' => 'Layanan Kelurahan - Pengajuan DTSEN',

        // --- Lainnya ---
        'asesmen-spmb' => 'Asesmen SPMB',
        'kedaruratan-medis' => 'Kedaruratan Medis (belum ada di menu)',
        'dokumen' => 'Dokumen (belum ada di menu)',
    ];

    /**
     * Pakai `slug` sebagai slug di URL (route model binding), bukan `id`.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Slug yang sudah dinormalisasi (lowercase, strip -/spasi jadi _),
     * supaya "Super Admin", "super-admin", "SUPERADMIN" dianggap sama.
     */
    public function normalizedSlug(): string
    {
        return strtolower(str_replace(['-', ' '], '_', $this->slug ?? ''));
    }

    /**
     * Super Admin SELALU bypass semua pengecekan modul & bisa akses
     * "Kelola Akses" (menu ini TIDAK termasuk di MODULES -- sengaja
     * tidak bisa dicentang, khusus Super Admin saja).
     */
    public function isSuperAdmin(): bool
    {
        return in_array($this->normalizedSlug(), ['superadmin', 'super_admin'], true);
    }

    /**
     * Cek apakah role ini boleh mengakses modul tertentu (key di MODULES).
     * Super Admin selalu true untuk modul apapun.
     */
    public function hasModule(string $key): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($key, $this->permissions ?? [], true);
    }
} 
