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
     * (Key lama TIDAK diubah, jadi hak akses role yang sudah tersimpan tetap berlaku.)
     *
     * Modul yang ada di config/akses.php punya hak akses DETAIL (submenu + aksi);
     * modul lainnya tetap satu centang per modul.
     */
    public const MODULES = [
        'beranda' => 'Beranda',

        // --- Portal (menu website; dulu bernama "Front End") ---
        'menu-frontend' => 'Portal - Kelola Menu Website',

        // --- PFM (Monitoring) ---
        'pbi-apbd' => 'PFM - PBI APBD (Super Admin: Ajuan, Arsip, Monitoring)',
        'kartu-kks' => 'PFM - Kartu KKS',
        'dtsen' => 'PFM - DTSEN',

        // --- PFM: halaman approval PBI APBD per tahap ---
        'pbi-lurah' => 'PFM - Approval PBI APBD: Lurah',
        'pbi-operator' => 'PFM - Approval PBI APBD: Operator Dinsos',
        'pbi-kabid' => 'PFM - Approval PBI APBD: Kepala Bidang',
        'pbi-kadis' => 'PFM - Approval PBI APBD: Kepala Dinas',
        'pbi-dinkes' => 'PFM - Approval PBI APBD: Dinas Kesehatan',
        'pbi-bpjs' => 'PFM - Approval PBI APBD: BPJS Kesehatan',

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
        'rehabsos-ground-check' => 'Rehabsos - Ground Check 2026',

        // --- Ground Check ---
        'ground-check' => 'Ground Check - Entry Data Ground Check 2026',

        // --- Kelurahan ---
        'pbi-kelurahan' => 'Verifikasi Kelurahan - PBI APBD',
        'layanan-kartu-kks' => 'Layanan Kelurahan - Pengajuan Kartu KKS',
        'layanan-dtsen' => 'Layanan Kelurahan - Pengajuan DTSEN',

        // --- Lainnya ---
        'asesmen-spmb' => 'Asesmen SPMB',
        'kedaruratan-medis' => 'Kedaruratan Medis (belum ada di menu)',
        'dokumen' => 'Dokumen - Lihat & Unduh Template',
    ];

    /**
     * Katalog modul -> submenu -> aksi (config/akses.php).
     */
    public static function katalog(): array
    {
        return config('akses', []);
    }

    /**
     * Semua kunci yang sah disimpan di `permissions`: modul, modul.submenu, modul.submenu.aksi.
     */
    public static function semuaKunci(): array
    {
        $kunci = array_keys(self::MODULES);

        foreach (self::katalog() as $modul => $submenus) {
            foreach ($submenus as $sub => $cfg) {
                $kunci[] = "{$modul}.{$sub}";
                foreach (array_keys($cfg['aksi'] ?? []) as $aksi) {
                    $kunci[] = "{$modul}.{$sub}.{$aksi}";
                }
            }
        }

        return $kunci;
    }

    /**
     * Rapikan daftar hak akses sebelum disimpan:
     *  - buang kunci tak dikenal & duplikat
     *  - aksi hanya dipertahankan bila submenunya dicentang
     *  - kunci modul otomatis ditambahkan bila ada submenu yang dicentang,
     *    dan dibuang bila modul berkatalog tidak punya submenu sama sekali
     *    (modul tanpa katalog dibiarkan seperti pilihan pengguna).
     */
    public static function normalisasi(array $permissions): array
    {
        $ada = array_flip(array_intersect(array_unique(array_map('strval', $permissions)), self::semuaKunci()));

        foreach (self::katalog() as $modul => $submenus) {
            $adaSub = false;

            foreach ($submenus as $sub => $cfg) {
                $kunciSub = "{$modul}.{$sub}";

                if (! isset($ada[$kunciSub])) {
                    foreach (array_keys($cfg['aksi'] ?? []) as $aksi) {
                        unset($ada["{$kunciSub}.{$aksi}"]);
                    }
                    continue;
                }

                $adaSub = true;
            }

            if ($adaSub) {
                $ada[$modul] = true;
            } else {
                unset($ada[$modul]);
            }
        }

        return array_values(array_keys($ada));
    }

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
     * Slug yang sudah dinormalisasi (lowercase, strip -/spasi jadi _).
     */
    public function normalizedSlug(): string
    {
        return strtolower(str_replace(['-', ' '], '_', $this->slug ?? ''));
    }

    /**
     * Super Admin SELALU bypass semua pengecekan modul.
     */
    public function isSuperAdmin(): bool
    {
        return in_array($this->normalizedSlug(), ['superadmin', 'super_admin'], true);
    }

    /**
     * Cek satu kunci hak akses: modul ('pbi-apbd'), submenu ('pbi-apbd.ajuan'),
     * atau aksi ('pbi-apbd.ajuan.proses'). Super Admin selalu lolos.
     * Nama dipertahankan (hasModule) agar semua pemanggil lama tetap bekerja.
     */
    public function hasModule(string $key): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($key, $this->permissions ?? [], true);
    }

    /**
     * Cara baca yang lebih jelas: $role->bisa('pbi-apbd', 'ajuan', 'proses').
     */
    public function bisa(string $modul, ?string $submenu = null, ?string $aksi = null): bool
    {
        return $this->hasModule(implode('.', array_filter([$modul, $submenu, $aksi])));
    }
}
