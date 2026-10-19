<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Daftar sasaran (individu per KK) yang harus di-ground check.
        Schema::create('gc_sasarans', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->string('no_kk', 16)->index();
            $table->date('tanggal_lahir')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable()->index();
            $table->text('alamat')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->unsignedTinyInteger('desil')->nullable();
            $table->timestamps();
        });

        // Hasil verifikasi per sasaran per tahun.
        Schema::create('gc_verifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sasaran_id')->constrained('gc_sasarans')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun')->default(2026);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // ditemukan | pindah_tidak_ditemukan | meninggal
            $table->string('status_temuan', 30);
            // draft | submit
            $table->string('status_entry', 10)->default('draft');
            $table->boolean('pernyataan')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['sasaran_id', 'tahun']);
        });

        // Jawaban per parameter. nama_parameter & bobot disimpan sebagai snapshot
        // supaya jawaban lama aman walau master diubah/dihapus.
        Schema::create('gc_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verifikasi_id')->constrained('gc_verifikasis')->cascadeOnDelete();
            $table->foreignId('parameter_id')->nullable()->constrained('gc_kriteria_parameters')->nullOnDelete();
            $table->string('nama_parameter');
            $table->decimal('bobot', 5, 2)->default(0);
            // pilihan: label | pilihan_ganda: JSON array label | foto: path | lainnya: nilai isian
            $table->text('jawaban')->nullable();
            // nilai opsi terpilih saat disimpan (null jika belum ditentukan / bukan pilihan)
            $table->decimal('skor', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['verifikasi_id', 'parameter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gc_jawabans');
        Schema::dropIfExists('gc_verifikasis');
        Schema::dropIfExists('gc_sasarans');
    }
};
