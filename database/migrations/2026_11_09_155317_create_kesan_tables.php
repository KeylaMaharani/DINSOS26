<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kategori bantuan: RTLH, Anak Yatim/Piatu, Dhuafa, Biaya Pendidikan, Bantuan Pangan, dst.
        Schema::create('kesan_kategoris', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 60)->unique();
            $t->string('nama', 80);
            $t->string('singkatan', 40)->nullable();
            $t->string('ikon', 40)->default('volunteer_activism');
            $t->string('warna', 20)->default('blue');
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        // Calon penerima bantuan (data yang dilihat Filantropi)
        Schema::create('kesan_penerimas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kesan_kategori_id')->constrained('kesan_kategoris')->restrictOnDelete();
            $t->string('nama', 120);
            $t->string('no_kk', 16)->nullable()->index();   // hanya untuk petugas, TIDAK tampil ke Filantropi
            $t->string('kecamatan', 60);
            $t->string('kelurahan', 60)->nullable();
            $t->string('prioritas', 10)->default('sedang'); // tinggi | sedang | rendah
            $t->unsignedTinyInteger('jumlah_jiwa')->nullable();
            $t->string('foto')->nullable();
            $t->text('profil')->nullable();
            $t->text('kebutuhan')->nullable();
            $t->unsignedBigInteger('estimasi_biaya')->default(0);
            $t->string('status', 12)->default('tersedia');  // tersedia | dipilih | selesai
            $t->boolean('aktif')->default(true);
            $t->timestamps();
            $t->index(['aktif', 'status']);
        });

        // Pilihan Filantropi -> keranjang -> dipilih -> penyaluran -> selesai
        Schema::create('kesan_pilihans', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 24)->nullable()->unique();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('kesan_penerima_id')->constrained('kesan_penerimas')->cascadeOnDelete();
            $t->string('status', 12)->default('keranjang');
            $t->timestamp('dipilih_at')->nullable();
            $t->timestamp('verifikasi_at')->nullable();
            $t->timestamp('selesai_at')->nullable();
            $t->foreignId('verifikator_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('catatan')->nullable();
            $t->text('laporan')->nullable();
            $t->string('bukti')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kesan_pilihans');
        Schema::dropIfExists('kesan_penerimas');
        Schema::dropIfExists('kesan_kategoris');
    }
};
