<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->unique()->after('username');
            $table->string('no_telp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->string('no_rekening', 30)->nullable();
            $table->timestamp('gc_profil_at')->nullable(); 
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropColumn(['nik', 'no_telp', 'alamat', 'pekerjaan', 'no_rekening', 'gc_profil_at']);
        });
    }
};