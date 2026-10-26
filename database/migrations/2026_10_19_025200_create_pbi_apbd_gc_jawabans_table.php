<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jawaban masyarakat untuk SEMUA parameter Master Kriteria Ground Check (form "Lengkapi Data Pengajuan").
 * Berisi snapshot nama/bobot supaya arsip tetap utuh bila parameter di master berubah.
 * Jawaban pilihan ganda disimpan sebagai JSON, foto sebagai path file.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbi_apbd_gc_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pbi_apbd_id')->constrained('pbi_apbds')->cascadeOnDelete();
            $table->foreignId('parameter_id')->nullable()->constrained('gc_kriteria_parameters')->nullOnDelete();

            // Snapshot
            $table->string('kelompok', 30)->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->string('nama_parameter');
            $table->string('tipe_input', 20)->default('teks');
            $table->decimal('bobot', 5, 2)->default(0);

            $table->text('jawaban')->nullable();
            // bobot x nilai opsi (hanya untuk tipe pilihan). Belum dipakai menghitung kelas kemiskinan.
            $table->decimal('skor', 10, 6)->nullable();

            $table->timestamps();
            $table->unique(['pbi_apbd_id', 'parameter_id'], 'pbi_gc_jawaban_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbi_apbd_gc_jawabans');
    }
};
