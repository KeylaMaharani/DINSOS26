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
     * key = dipakai di middleware & pengecekan hasModule().
     * value = label yang ditampilkan di checkbox.
     *
     * Tambahkan/hapus baris di sini kalau ada modul baru di kemudian hari —
     * otomatis muncul di form Tambah/Edit Hak Akses tanpa perlu ubah view.
     */
    public const MODULES = [
        'beranda' => 'Beranda',
        'asesmen-spmb' => 'Asesmen SPMB',
        'pbi-apbn' => 'PBI APBN',
        'kedaruratan-medis' => 'Kedaruratan Medis',
        'kartu-kks' => 'Kartu KKS',
        'dtsen' => 'DTSEN',
        'dokumen' => 'Dokumen',
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
     * "Kelola Akses" (menu ini TIDAK termasuk di MODULES — sengaja
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
