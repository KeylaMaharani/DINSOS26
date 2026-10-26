<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Role harus paling awal (dipakai akun admin, akun uji & permission modul)
        $this->call([
            RoleSeeder::class,
            SigapBencanaRoleSeeder::class,
        ]);

        // 2. Akun Admin, dikaitkan ke role 'superadmin'
        //    Dicek dulu supaya tidak dobel kalau seeder dijalankan ulang
        $superadmin = Role::where('slug', 'superadmin')->firstOrFail();

        if (! User::where('username', 'admin')->exists()) {
            User::factory()->create([
                'name'     => 'Administrator',
                'username' => 'admin',
                'email'    => 'admin@gmail.com',
                'password' => bcrypt('admin123'),
                'role_id'  => $superadmin->id,
            ]);
        }

        // 3. Master kriteria. Urutan penting:
        //    - PbiKriteriaSeeder dulu, karena parameter Ground Check punya padanan PBI (pbi_parameter_id)
        //    - GcKriteriaSeeder HARUS sebelum GcKetenagakerjaanSeeder: seeder ini hanya berjalan
        //      kalau tabel gc_kriteria_parameters masih KOSONG, jadi tidak boleh ada seeder
        //      lain yang mengisi tabel itu lebih dulu.
        $this->call([
            PbiKriteriaSeeder::class,
            GcKriteriaSeeder::class,
            GcKetenagakerjaanSeeder::class,
        ]);

        // 4. Data layanan (bergantung pada role, user & kriteria di atas)
        $this->call([
            PbiApbdSeeder::class,
            KartuKksSeeder::class,
            DtsenSeeder::class,
        ]);

        // 5. SIGAP Bencana: master barang dulu, baru akun uji
        $this->call([
            SigapBarangSeeder::class,
            SigapAkunUjiSeeder::class,
        ]);

        // 6. Data demo Ground Check (paling akhir karena butuh master GC & data PBI)
        $this->call([
            GcSasaranDemoSeeder::class,
        ]);
    }
}
