<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbi_apbn_anggota_keluargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pbi_apbn_id')->constrained('pbi_apbns')->cascadeOnDelete();
            $table->string('nik');
            $table->string('nama');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('agama')->nullable();
            $table->string('jenis_kelamin')->nullable(); // L / P
            $table->string('hubungan_keluarga')->nullable();
            $table->string('keanggotaan_bpjs')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbi_apbn_anggota_keluargas');
    }
};
