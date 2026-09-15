<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kartu_kks_lampiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')
                ->constrained('kartu_kks_permohonan')->cascadeOnDelete();

            // Semua kolom ini menyimpan PATH file relatif terhadap disk 'public'
            $table->string('scan_ktp')->nullable();
            $table->string('scan_kartu_keluarga')->nullable();
            $table->string('surat_kehilangan_polisi')->nullable();
            $table->string('scan_kartu_kks')->nullable();
            $table->string('screenshot_dtsen')->nullable();

            $table->timestamps();

            $table->unique('permohonan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_kks_lampiran');
    }
};
