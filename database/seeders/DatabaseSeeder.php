<?php

namespace Database\Seeders;

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
        // 1. Jalankan Role Seeder terlebih dahulu (opsional jika Anda butuh relasi role)
        $this->call([
            RoleSeeder::class,
        ]);

        // 2. Buat akun Admin
        User::factory()->create([
            'name' => 'Administrator', // <- WAJIB DITAMBAHKAN
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
        ]);

        $this->call([
            KartuKksSeeder::class,
        ]);
    }
}
