<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Taruh di database/migrations/.
 * Jawaban masyarakat PER ANGGOTA KELUARGA (tab A-D: kependudukan, pendidikan, ketenagakerjaan, kesehatan)
 * untuk anggota yang dicentang di halaman Daftar Keluarga. Jawaban kepala keluarga tetap di pbi_apbd_gc_jawabans.
 * Dipakai sebagai nilai awal form petugas Ground Check; petugas yang memverifikasi/mengoreksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbi_apbd_anggota_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pbi_apbd_id')->constrained('pbi_apbds')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('pbi_apbd_anggota_keluargas')->cascadeOnDelete();
            $table->foreignId('parameter_id')->nullable()->constrained('gc_kriteria_parameters')->nullOnDelete();

            // Snapshot
            $table->string('kelompok', 30)->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->string('nama_parameter');
            $table->string('tipe_input', 20)->default('teks');

            $table->text('jawaban')->nullable();
            $table->timestamps();

            $table->unique(['anggota_id', 'parameter_id'], 'pbi_anggota_jawaban_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbi_apbd_anggota_jawabans');
    }
};