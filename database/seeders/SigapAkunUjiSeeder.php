<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun uji untuk mencoba seluruh alur SIGAP Bencana, satu akun per role.
 *
 * HANYA UNTUK PENGEMBANGAN: password-nya sama semua dan mudah ditebak,
 * jadi seeder ini menolak jalan kalau APP_ENV bukan "local".
 *
 * Jalankan: php artisan db:seed --class=SigapAkunUjiSeeder
 * Login lewat /admin-login. Password semua akun: sigap123
 */
class SigapAkunUjiSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->error('SigapAkunUjiSeeder hanya boleh dijalankan di environment local.');

            return;
        }

        // Pastikan role-nya ada dulu (tidak mengubah role yang sudah ada)
        $this->call(SigapBencanaRoleSeeder::class);

        $akun = [
            // slug role, nama, username, kecamatan, kelurahan
            ['tagana', 'Tagana Uji', 'tagana', null, null],
            ['perlinsos', 'Perlinsos Uji', 'perlinsos', null, null],
            ['pengurus_barang', 'Pengurus Barang Uji', 'pengurusbarang', null, null],
            ['kadis', 'Kepala Dinas Uji', 'kadis', null, null],
            ['kelurahan', 'Petugas Kelurahan Uji', 'kelurahan', 'Bogor Tengah', 'Ciwaringin'],
        ];

        foreach ($akun as [$slug, $nama, $username, $kecamatan, $kelurahan]) {
            $role = Role::where('slug', $slug)->first();

            if (! $role) {
                continue;
            }

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $nama,
                    'email' => $username . '@sigap.test',
                    'password' => 'sigap123', // di-hash otomatis oleh cast 'hashed' pada model User
                    'role_id' => $role->id,
                    'kecamatan' => $kecamatan,
                    'kelurahan' => $kelurahan,
                ]
            );
        }

        $this->command?->info('Akun uji dibuat. Password semua akun: sigap123');
    }
}
