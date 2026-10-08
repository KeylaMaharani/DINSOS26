<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Role harus paling awal (dipakai akun admin & permission modul)
        $this->call([
            RoleSeeder::class,
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

        // 3. Seeder modul. Urutan penting:
        //    kriteria dulu, karena jawaban ajuan PBI APBD bergantung pada parameter kriteria
        $this->call([
            PbiKriteriaSeeder::class,
            PbiApbdSeeder::class,
            KartuKksSeeder::class,
            DtsenSeeder::class,
        ]);
    }
}
