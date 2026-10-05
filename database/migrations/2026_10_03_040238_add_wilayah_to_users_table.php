<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Wilayah tugas akun petugas kelurahan (dicocokkan ke pbi_apbns.kecamatan & desa_kelurahan)
            $table->string('kecamatan')->nullable()->after('role_id');
            $table->string('kelurahan')->nullable()->after('kecamatan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['kecamatan', 'kelurahan']);
        });
    }
};
