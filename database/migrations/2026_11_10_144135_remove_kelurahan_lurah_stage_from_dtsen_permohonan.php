<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Permohonan yang masih tertahan di tahap Kelurahan / Lurah dipindah ke Validasi Dinsos
        // dan dipegang Operator Dinsos. Riwayat log lama tidak diubah.
        $operatorId = DB::table('roles')->where('slug', 'operator_dinsos')->value('id');

        DB::table('dtsen_permohonan')
            ->whereIn('status', ['diajukan', 'verifikasi_kelurahan', 'ttd_lurah'])
            ->update([
                'status' => 'validasi_dinsos',
                'current_role_id' => $operatorId,
            ]);

        Schema::table('dtsen_permohonan', function (Blueprint $table) {
            $table->string('status')->default('validasi_dinsos')->change();
        });
    }

    public function down(): void
    {
        Schema::table('dtsen_permohonan', function (Blueprint $table) {
            $table->string('status')->default('diajukan')->change();
        });
    }
};
