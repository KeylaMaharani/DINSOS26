<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dtsen_bansos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')
                ->constrained('dtsen_permohonan')->cascadeOnDelete();

            // "Desil" -> tampil di kolom Monitoring & Arsip
            $table->string('peringkat_kesejahteraan_keluarga')->nullable();

            // Status kepesertaan program bansos terkait DTSEN
            // NOTE: cek ke tim bisnis apakah "BNPT" di dokumen sumber
            // memang dimaksud BPNT (Bantuan Pangan Non Tunai) — nama kolom
            // sengaja mengikuti ejaan dokumen asli dulu, tinggal di-rename
            // via migration baru kalau sudah dikonfirmasi.
            $table->boolean('bnpt')->nullable();
            $table->boolean('pkh')->nullable();
            $table->boolean('pbi')->nullable();
            $table->boolean('yatim_piatu')->nullable();

            // Info cetak kartu/keterangan
            $table->date('tanggal_cetak')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->string('dicetak_oleh')->nullable();

            // Upload dokumen tambahan (path relatif disk 'public')
            $table->string('upload_lainnya')->nullable();

            $table->timestamps();

            $table->unique('permohonan_id');
            $table->index('berlaku_sampai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_bansos');
    }
};
