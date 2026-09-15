<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dtsen_lampiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')
                ->constrained('dtsen_permohonan')->cascadeOnDelete();

            // Semua kolom ini menyimpan PATH file relatif terhadap disk 'public'
            $table->string('screenshot_dtsen')->nullable();
            $table->string('scan_ktp')->nullable();

            $table->timestamps();

            $table->unique('permohonan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_lampiran');
    }
};
