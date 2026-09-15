<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dtsen_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')
                ->constrained('dtsen_permohonan')->cascadeOnDelete();

            // Sesuai daftar field "Data Detail" pada dokumen kebutuhan
            $table->string('nik', 20)->nullable();
            $table->string('nama')->nullable();
            $table->string('no_kk', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alasan_keputusan_cetak')->nullable();

            $table->timestamps();

            $table->unique('permohonan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_detail');
    }
};
