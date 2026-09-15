<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kartu_kks_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')
                ->constrained('kartu_kks_permohonan')->cascadeOnDelete();

            $table->string('nik', 20);
            $table->string('nama');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->string('status_perkawinan')->nullable();
            $table->string('no_pkh')->nullable();
            $table->string('no_kk', 20)->nullable();
            $table->string('no_kartu_kks', 30)->nullable();
            $table->string('no_rekening', 30)->nullable();
            $table->string('pekerjaan')->nullable();
            $table->text('alamat')->nullable();
            $table->string('masalah_kartu')->nullable();
            $table->string('nomor_kehilangan_polisi')->nullable();

            $table->timestamps();

            $table->unique('permohonan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_kks_detail');
    }
};
