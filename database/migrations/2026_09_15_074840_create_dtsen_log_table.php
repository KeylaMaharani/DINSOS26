<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dtsen_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')
                ->constrained('dtsen_permohonan')->cascadeOnDelete();

            $table->timestamp('tanggal_proses')->useCurrent();
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // Disimpan sebagai snapshot text (bukan cuma relasi) supaya histori
            // tetap valid walau username / nama role berubah / dihapus di kemudian hari
            $table->string('username')->nullable();
            $table->string('taskname'); // contoh: "Verifikasi Kelurahan", "TTD Lurah", "Validasi Dinsos"
            $table->string('rolename')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index(['permohonan_id', 'tanggal_proses']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_log');
    }
};
