<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dtsen_surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')
                ->constrained('dtsen_permohonan')->cascadeOnDelete();

            // Diisi/di-generate saat lurah ttd
            $table->string('surat_pengantar_dtsen_kelurahan_digital')->nullable();
            $table->timestamp('surat_pengantar_dtsen_kelurahan_digital_at')->nullable();

            // Diisi/di-generate saat dinsos menerbitkan surat keterangan
            $table->string('surat_keterangan_dtsen_digital')->nullable();
            $table->timestamp('surat_keterangan_dtsen_digital_at')->nullable();

            $table->timestamps();

            $table->unique('permohonan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_surat');
    }
};
