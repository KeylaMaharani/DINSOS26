<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kartu_kks_surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')
                ->constrained('kartu_kks_permohonan')->cascadeOnDelete();

            // Diisi/di-generate saat lurah ttd
            $table->string('surat_pengantar_kelurahan')->nullable();
            $table->timestamp('surat_pengantar_kelurahan_at')->nullable();

            // Diisi/di-generate saat dinsos menerbitkan surat keterangan
            $table->string('surat_keterangan_dinsos')->nullable();
            $table->timestamp('surat_keterangan_dinsos_at')->nullable();

            $table->timestamps();

            $table->unique('permohonan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_kks_surat');
    }
};
