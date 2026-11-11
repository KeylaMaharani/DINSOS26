<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data koreksi dari warga bila menjawab "Tidak" pada pertanyaan
 * "Apakah No KK sama?", "Apakah nama sama?", "Apakah wilayah sama?".
 * Disimpan TERPISAH: tidak mengubah pbi_apbds maupun data akun yang login.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbi_apbd_gc_koreksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pbi_apbd_id')->unique()->constrained('pbi_apbds')->cascadeOnDelete();
            $table->string('no_kk_baru', 16)->nullable();
            $table->string('nama_baru')->nullable();
            $table->string('kabupaten_baru', 100)->nullable();
            $table->string('kecamatan_baru', 100)->nullable();
            $table->string('kelurahan_baru', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbi_apbd_gc_koreksis');
    }
};
