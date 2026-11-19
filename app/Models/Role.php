<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'permissions', 'tahap_pbi'])]
class Role extends Model
{
    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Daftar modul yang bisa dicentang di form "Kelola Hak Akses".
     * (Key TIDAK diubah, jadi hak akses role yang sudah tersimpan tetap berlaku.)
     *
     * Modul yang ada di config/akses.php punya hak akses DETAIL (submenu + aksi);
     * modul lainnya tetap satu centang per modul.
     *
     * PBI APBD: semua role (Kelurahan, Lurah, Operator, Kabid, Kadis, Dinkes, BPJS) memakai
     * satu menu "Ajuan" (pbi-apbd.ajuan). Modul approval per tahap sudah dihapus.
     */
    public const MODULES = [
        'beranda' => 'Beranda',

        // --- Portal (menu website; dulu bernama "Front End") ---
        'menu-frontend' => 'Portal - Kelola Menu Website',

        // --- KESAN (Kolaborasi Entaskan Kemiskinan / Filantropi) ---
        'kesan' => 'KESAN - Verifikasi Pilihan, Calon Penerima, Kategori, Data Filantropi',

        // --- PFM (Monitoring) ---
        'pbi-apbd' => 'PFM - PBI APBD (Ajuan, Arsip, Monitoring, Log)',
        'kartu-kks' => 'PFM - Kartu KKS (Ajuan, Arsip, Monitoring, Log)',
        'dtsen' => 'PFM - DTSEN (Ajuan, Arsip, Monitoring)',

        // --- Dayasos ---
        'dayasos-ngopi-pagi' => 'Dayasos - Ngopi Pagi (Admin: semua kasus, rekap, konselor, spesialisasi)',
        // Konselor: HANYA halaman kerja kasus miliknya. Jangan dicentang bersamaan dengan modul admin di atas.
        'dayasos-ngopi-konselor' => 'Dayasos - Ngopi Pagi (Konselor: hanya kasus miliknya)',
        'dayasos-kesan' => 'Dayasos - Kesan',
        'dayasos-lks' => 'Dayasos - LKS',

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
        // Menu Ground Check MANDIRI untuk petugas (tidak masuk bidang apa pun): Home (isi data petugas,
        // unduh dokumen) + Ground Check 2026. Satu centang = semua akses Ground Check petugas.
        'gc-petugas' => 'Ground Check Petugas - Menu mandiri (Home + Ground Check 2026)',

        // --- Kelurahan ---
        'layanan-kartu-kks' => 'Layanan Kelurahan - Pengajuan Kartu KKS',
        'layanan-dtsen' => 'Layanan Kelurahan - Pengajuan DTSEN',

        // --- Lainnya ---
        'asesmen-spmb' => 'Asesmen SPMB',
        'kedaruratan-medis' => 'Kedaruratan Medis (belum ada di menu)',
        'dokumen' => 'Dokumen - Lihat & Unduh Template',
        'data-desil' => 'Entri Data - Data Desil Kependudukan',
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
            $kunci[] = $modul;

            foreach ($submenus as $sub => $cfg) {
                $kunci[] = "{$modul}.{$sub}";
                foreach (array_keys($cfg['aksi'] ?? []) as $aksi) {
                    $kunci[] = "{$modul}.{$sub}.{$aksi}";
                }
            }
        }

        return array_values(array_unique($kunci));
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

    /** Kata kunci (nama/slug role) untuk menebak tahap PBI APBD bila kolom tahap_pbi kosong. */
    private const ALIAS_TAHAP_PBI = [
        'kelurahan' => ['kelurahan', 'petugas kelurahan'],
        'lurah'     => ['lurah'],
        'dinkes'    => ['dinkes', 'dinas kesehatan'],
        'bpjs'      => ['bpjs'],
        'operator'  => ['operator', 'dayasos'],
        'kabid'     => ['kabid', 'kabin', 'kepala bidang'],
        'kadis'     => ['kadis', 'kepala dinas'],
    ];

    /**
     * Tahap kerja PBI APBD untuk role ini:
     * 'kelurahan' | 'lurah' | 'operator' | 'kabid' | 'kadis' | 'dinkes' | 'bpjs' | null.
     * Null = tampilan admin lintas tahap (Super Admin atau role non-tahap).
     * Urutan: kolom roles.tahap_pbi (bila diisi) -> tebakan dari nama/slug role.
     */
    public function tahapPbi(): ?string
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        $valid = array_merge(['kelurahan'], array_keys(\App\Models\PbiApbd::TAHAP));

        $diset = $this->getAttribute('tahap_pbi');
        if (filled($diset)) {
            return in_array($diset, $valid, true) ? $diset : null; // 'admin' / nilai lain = tampilan admin
        }

        $teks = ' ' . trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower(($this->slug ?? '') . ' ' . ($this->name ?? '')))) . ' ';
        foreach (self::ALIAS_TAHAP_PBI as $tahap => $kataKunci) {
            foreach ($kataKunci as $kata) {
                if (str_contains($teks, " {$kata} ")) {
                    return $tahap;
                }
            }
        }

        return null;
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
