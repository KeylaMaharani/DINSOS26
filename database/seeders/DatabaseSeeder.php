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
        // 1. Jalankan Role Seeder terlebih dahulu
        $this->call([
            RoleSeeder::class,
        ]);

        // 2. Buat akun Admin, dikaitkan ke role 'superadmin'
        $superadmin = Role::where('slug', 'superadmin')->firstOrFail();

        User::factory()->create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
            'role_id' => $superadmin->id, // <- WAJIB DITAMBAHKAN
        ]);

        // $this->call([
        //     KartuKksSeeder::class,
        // ]);
    }
}