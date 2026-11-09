<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbi_apbds', function (Blueprint $table) {
            // Hasil cek Kelurahan per berkas masyarakat (scan_ktp, scan_kk, surat_rawat_inap): sesuai / tidak_sesuai
            $table->json('verifikasi_berkas')->nullable();

            // Tahap Lurah
            $table->text('keterangan_lurah')->nullable();
            $table->timestamp('divalidasi_lurah_at')->nullable();
            $table->foreignId('divalidasi_lurah_oleh')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pbi_apbds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('divalidasi_lurah_oleh');
            $table->dropColumn(['verifikasi_berkas', 'keterangan_lurah', 'divalidasi_lurah_at']);
        });
    }
};
