<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbi_apbn_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pbi_apbn_id')->constrained('pbi_apbns')->cascadeOnDelete();
            $table->foreignId('parameter_id')->nullable()->constrained('pbi_kriteria_parameters')->nullOnDelete();
            $table->foreignId('opsi_id')->nullable()->constrained('pbi_kriteria_opsis')->nullOnDelete();

            // SNAPSHOT: nilai master saat masyarakat menjawab. Kalau master diubah/dihapus
            // di kemudian hari, data pengajuan lama tetap utuh dan skornya tidak berubah.
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->string('parameter_nama');
            $table->string('jawaban_label');
            $table->decimal('indeks', 4, 2);
            $table->decimal('bobot', 4, 2);
            $table->decimal('indeks_terintegrasi', 4, 2);
            $table->decimal('skor', 10, 6); // indeks x bobot x indeks_terintegrasi

            $table->timestamps();
            $table->unique(['pbi_apbn_id', 'parameter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbi_apbn_jawabans');
    }
};
