<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master kriteria GROUND CHECK (dipisah dari master kriteria PBI).
 * Sumber: dokumen "39 Variabel Ground Check pada Aplikasi SOLID" + Buku Saku Ground Check 2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gc_kriteria_parameters', function (Blueprint $table) {
            $table->id();
            // kependudukan | pendidikan | ketenagakerjaan | kesehatan | keluarga
            // | aset_bergerak | aset_tidak_bergerak | dokumentasi
            $table->string('kelompok', 30)->index();
            $table->unsignedInteger('urutan')->default(0);
            $table->string('nama');
            $table->decimal('bobot', 5, 2)->default(0);
            // pilihan | pilihan_ganda | teks | angka | tanggal | koordinat | foto
            $table->string('tipe_input', 20)->default('pilihan');
            // Relasi (referensi saja) ke parameter PBI yang jadi sumber opsinya.
            // Opsi DISALIN saat seed, jadi mengubah master GC tidak mengubah master PBI.
            $table->foreignId('pbi_parameter_id')->nullable()
                ->constrained('pbi_kriteria_parameters')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('gc_kriteria_opsis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_id')->constrained('gc_kriteria_parameters')->cascadeOnDelete();
            $table->string('label');
            // Null = belum ditentukan (opsi sementara). Untuk opsi hasil salinan PBI
            // diisi dari indeks_terintegrasi PBI.
            $table->decimal('nilai', 5, 2)->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gc_kriteria_opsis');
        Schema::dropIfExists('gc_kriteria_parameters');
    }
};
