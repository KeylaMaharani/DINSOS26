<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Daftar modul yang boleh diakses role ini, mis. ["beranda","pbi-apbn","kartu-kks"]
            // NULL / kosong berarti tidak ada modul yang bisa diakses (kecuali Super Admin,
            // yang selalu bypass pengecekan ini — lihat Role::isSuperAdmin()).
            $table->json('permissions')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
