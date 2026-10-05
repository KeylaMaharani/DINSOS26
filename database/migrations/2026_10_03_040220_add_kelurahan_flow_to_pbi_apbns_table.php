<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbi_apbns', function (Blueprint $table) {
            $table->decimal('skor_kumulatif', 5, 2)->nullable();
            $table->unsignedTinyInteger('kelas_kemiskinan')->nullable();
            $table->timestamp('data_diisi_at')->nullable();
            $table->boolean('dikembalikan_ke_masyarakat')->default(false);
            $table->text('catatan_kelurahan')->nullable();
            $table->timestamp('divalidasi_kelurahan_at')->nullable();
            $table->foreignId('divalidasi_kelurahan_oleh')->nullable()->constrained('users')->nullOnDelete();
        });

        // Default lama 'operator_dinsos' keliru; tahap awal alur = 'kelurahan'
        Schema::table('pbi_apbns', function (Blueprint $table) {
            $table->string('status')->default('kelurahan')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pbi_apbns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('divalidasi_kelurahan_oleh');
            $table->dropColumn([
                'skor_kumulatif', 'kelas_kemiskinan', 'data_diisi_at',
                'dikembalikan_ke_masyarakat', 'catatan_kelurahan', 'divalidasi_kelurahan_at',
            ]);
        });

        Schema::table('pbi_apbns', function (Blueprint $table) {
            $table->string('status')->default('operator_dinsos')->change();
        });
    }
};
