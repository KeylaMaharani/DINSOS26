<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data penandatangan surat (Kepala Dinas, Lurah, dst.) disimpan di akun user,
     * diisi lewat menu Kelola User & Role -- bukan hardcode di config/.env.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 30)->nullable()->after('email');
            $table->string('pangkat', 100)->nullable()->after('nip');
            $table->string('jabatan', 100)->nullable()->after('pangkat');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'pangkat', 'jabatan']);
        });
    }
};