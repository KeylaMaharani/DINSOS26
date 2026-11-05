<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu kasus konsultasi. Data asesmen (langkah 5) dan tindak lanjut (langkah 6-7)
        // bersifat 1:1 dengan kasus, jadi disimpan sebagai kolom di sini (bukan tabel terpisah).
        Schema::create('ngopi_pagi_konsultasis', function (Blueprint $t) {
            $t->id();
            $t->string('no_konsultasi', 30)->unique();
            $t->string('token', 64)->unique();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Langkah 1-3: data awal
            $t->string('nama', 100);
            $t->string('kontak', 20);
            $t->string('kecamatan', 60);
            $t->string('kelurahan', 100);
            $t->text('kebutuhan');
            $t->boolean('butuh_segera')->default(false);
            $t->boolean('identitas_terverifikasi')->default(false);
            $t->text('verifikasi_catatan')->nullable();

            // Langkah 4: screening
            $t->char('kategori', 1)->nullable();
            $t->string('jenis_masalah', 40)->nullable();
            $t->text('klasifikasi_catatan')->nullable();

            // Langkah 5: asesmen petugas LK3
            $t->text('kondisi')->nullable();
            $t->text('kronologi')->nullable();
            $t->text('kebutuhan_asesmen')->nullable();
            $t->string('tingkat_urgensi', 20)->nullable();
            $t->text('hasil_asesmen')->nullable();

            // Langkah 6-7: tindak lanjut
            $t->string('jenis_tindak_lanjut', 20)->nullable();
            $t->text('rencana_intervensi')->nullable();
            $t->string('tujuan_rujukan', 150)->nullable();
            $t->string('output_layanan', 150)->nullable();

            // Alur
            $t->string('tahap', 30)->default('chat_masuk')->index();
            $t->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('konselor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('terakhir_pesan_at')->nullable();
            $t->timestamp('dikonfirmasi_at')->nullable();
            $t->timestamp('closed_at')->nullable();
            $t->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('catatan_penutupan')->nullable();
            $t->timestamps();
        });

        Schema::create('ngopi_pagi_pesans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('konsultasi_id')->constrained('ngopi_pagi_konsultasis')->cascadeOnDelete();
            $t->string('pengirim', 12); // masyarakat | petugas | sistem
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('isi');
            $t->timestamp('dibaca_at')->nullable();
            $t->timestamps();
            $t->index(['konsultasi_id', 'id']);
        });

        Schema::create('ngopi_pagi_monitorings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('konsultasi_id')->constrained('ngopi_pagi_konsultasis')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status_perkembangan', 30);
            $t->text('catatan');
            $t->timestamps();
        });

        Schema::create('ngopi_pagi_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('konsultasi_id')->constrained('ngopi_pagi_konsultasis')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('aksi', 40);
            $t->string('dari_tahap', 30)->nullable();
            $t->string('ke_tahap', 30)->nullable();
            $t->text('catatan')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ngopi_pagi_logs');
        Schema::dropIfExists('ngopi_pagi_monitorings');
        Schema::dropIfExists('ngopi_pagi_pesans');
        Schema::dropIfExists('ngopi_pagi_konsultasis');
    }
};
