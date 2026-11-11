<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ke varchar dulu supaya data lama ('L'/'P') bisa dikonversi
        DB::statement("ALTER TABLE kartu_kks_detail MODIFY jenis_kelamin VARCHAR(20) NOT NULL");
        DB::statement("UPDATE kartu_kks_detail SET jenis_kelamin = 'Laki-laki' WHERE jenis_kelamin = 'L'");
        DB::statement("UPDATE kartu_kks_detail SET jenis_kelamin = 'Perempuan' WHERE jenis_kelamin = 'P'");
    }

    public function down(): void
    {
        DB::statement("UPDATE kartu_kks_detail SET jenis_kelamin = 'L' WHERE jenis_kelamin = 'Laki-laki'");
        DB::statement("UPDATE kartu_kks_detail SET jenis_kelamin = 'P' WHERE jenis_kelamin = 'Perempuan'");
        DB::statement("ALTER TABLE kartu_kks_detail MODIFY jenis_kelamin ENUM('L','P') NOT NULL");
    }
};