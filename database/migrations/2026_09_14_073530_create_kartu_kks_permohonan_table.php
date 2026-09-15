<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kartu_kks_permohonan', function (Blueprint $table) {
            $table->id();

            // Data inti yang selalu tampil di listing (Arsip & Monitoring)
            $table->string('nik', 20);
            $table->string('nama_pemohon');
            $table->text('alamat')->nullable();

            // Koordinat untuk peta di menu Ajuan
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // "Masalah Kartu" -> filter di search Arsip: hilang, rusak, tidak_aktif, dll
            $table->string('masalah_kartu')->nullable();
            $table->string('nomor_kehilangan_polisi')->nullable();

            // Status alur (dipakai oleh Ajuan, Arsip, dan Monitoring)
            // diajukan -> verifikasi_kelurahan -> ttd_lurah -> validasi_dinsos
            // -> diproses_kabin -> disetujui_kadis -> selesai
            // (atau) ditolak di titik manapun
            $table->string('status')->default('diajukan');

            // Role yang sedang memegang berkas ini sekarang (untuk notifikasi & filter "punya siapa")
            $table->foreignId('current_role_id')->nullable()
                ->constrained('roles')->nullOnDelete();

            // Kelurahan asal pemohon (opsional, isi manual dulu selama belum ada master wilayah)
            $table->string('kelurahan')->nullable();

            $table->date('tanggal_insert')->useCurrent();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['masalah_kartu']);
            $table->index(['tanggal_insert']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_kks_permohonan');
    }
};
