<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Membuat role baru untuk alur SIGAP Bencana. Role yang SUDAH ADA
 * (kelurahan, kadis, dst) TIDAK diubah sama sekali -- centang modul
 * 'sigap-bencana' / 'sigap-proses' untuk mereka lewat Kelola Akses.
 *
 * Jalankan: php artisan db:seed --class=SigapBencanaRoleSeeder
 */
class SigapBencanaRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['slug' => 'tagana', 'name' => 'Tagana', 'description' => 'Taruna Siaga Bencana: laporan & assessment lapangan', 'permissions' => ['sigap-bencana']],
            ['slug' => 'perlinsos', 'name' => 'Bidang Perlinsos', 'description' => 'Verifikasi laporan & permohonan barang bantuan', 'permissions' => ['sigap-bencana']],
            ['slug' => 'pengurus_barang', 'name' => 'Pengurus Barang', 'description' => 'Cek stok & proses pengeluaran barang', 'permissions' => ['sigap-bencana']],
            ['slug' => 'kadis', 'name' => 'Kepala Dinas', 'description' => 'Persetujuan / TTD pengeluaran barang', 'permissions' => ['sigap-bencana']],
            ['slug' => 'kelurahan', 'name' => 'Kelurahan', 'description' => 'Petugas kelurahan / OPD wilayah', 'permissions' => ['sigap-proses']],
        ];

        foreach ($roles as $r) {
            // firstOrCreate: kalau role sudah ada, dibiarkan apa adanya.
            Role::firstOrCreate(['slug' => $r['slug']], $r);
        }
    }
}
