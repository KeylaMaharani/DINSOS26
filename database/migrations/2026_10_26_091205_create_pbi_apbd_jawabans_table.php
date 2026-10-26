<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbi_apbd_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pbi_apbd_id')->constrained('pbi_apbds')->cascadeOnDelete();
            $table->foreignId('parameter_id')->nullable()->constrained('pbi_kriteria_parameters')->nullOnDelete();
            $table->foreignId('opsi_id')->nullable()->constrained('pbi_kriteria_opsis')->nullOnDelete();

            $table->unsignedInteger('urutan')->default(0);
            $table->string('parameter_nama');
            $table->string('jawaban_label');

            $table->decimal('indeks', 8, 2)->default(0);
            $table->decimal('bobot', 8, 2)->default(0);
            $table->decimal('indeks_terintegrasi', 8, 2)->default(0);
            $table->decimal('skor', 12, 6)->default(0);

            $table->timestamps();
            $table->unique(['pbi_apbd_id', 'parameter_id'], 'pbi_jawaban_skor_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbi_apbd_jawabans');
    }
};
