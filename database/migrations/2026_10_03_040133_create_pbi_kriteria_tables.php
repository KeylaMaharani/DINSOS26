<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master parameter (18 pertanyaan) -- dikelola Super Admin
        Schema::create('pbi_kriteria_parameters', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->string('nama');
            $table->decimal('bobot', 4, 2);
            $table->decimal('indeks', 4, 2)->default(1.00);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Opsi jawaban (isi dropdown) + indeks terintegrasi
        Schema::create('pbi_kriteria_opsis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_id')->constrained('pbi_kriteria_parameters')->cascadeOnDelete();
            $table->string('label');
            $table->decimal('indeks_terintegrasi', 4, 2);
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbi_kriteria_opsis');
        Schema::dropIfExists('pbi_kriteria_parameters');
    }
};
