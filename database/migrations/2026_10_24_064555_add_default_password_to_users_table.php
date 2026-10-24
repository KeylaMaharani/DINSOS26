<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Disimpan terenkripsi (cast 'encrypted' di model User) -- hanya untuk
            // ditampilkan ke Super Admin di Kelola Akses. Bukan dipakai untuk login.
            $table->text('default_password')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('default_password');
        });
    }
};
