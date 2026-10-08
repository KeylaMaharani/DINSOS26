<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SIGAP BENCANA (Standarisasi Implementasi Gerak Administrasi Penanganan Bencana)
 * Modul Perlinsos: laporan kejadian -> assessment -> verifikasi -> pengeluaran barang -> BAST -> selesai.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- Penomoran otomatis berurutan (per jenis surat per tahun) ----------
        Schema::create('sigap_nomor_urut', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 30);
            $table->unsignedSmallInteger('tahun');
            $table->unsignedInteger('terakhir')->default(0);
            $table->unique(['jenis', 'tahun']);
        });

        // ---------- Master barang & stok ----------
        Schema::create('sigap_barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->nullable()->unique();
            $table->string('nama');
            $table->string('satuan', 30);
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // ---------- Tiket laporan ----------
        Schema::create('sigap_laporan', function (Blueprint $table) {
            $table->id();
            $table->string('no_tiket', 30)->unique();          // SGP-2026-0001 (dipakai di URL)
            $table->string('nomor_surat_laporan', 60)->nullable()->unique();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // pelapor
            $table->string('nama_pelapor')->nullable();
            $table->string('peran_pelapor', 30)->nullable();   // tagana | kelurahan | superadmin

            $table->string('jenis_bencana', 100);
            $table->dateTime('tanggal_kejadian');
            $table->unsignedInteger('jumlah_korban')->default(0);
            $table->string('no_kk')->nullable();
            $table->text('kerusakan')->nullable();
            $table->text('kerugian')->nullable();
            $table->text('bantuan_dibutuhkan');

            $table->string('kecamatan', 100);
            $table->string('kelurahan', 100);
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->text('alamat');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('lokasi_diperbarui_at')->nullable(); // diisi saat Tagana tiba di lokasi

            $table->string('status', 30)->default('dilaporkan');
            $table->foreignId('current_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->timestamp('ditutup_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('kelurahan');
            $table->index('tanggal_kejadian');
        });

        // ---------- Tahap 2: Assessment Tagana -> Nota Dinas ----------
        Schema::create('sigap_assessment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->unique()->constrained('sigap_laporan')->cascadeOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_petugas');                    // snapshot, boleh lebih dari satu nama
            $table->string('nomor_nota_dinas', 60)->nullable()->unique();
            $table->date('tanggal_assessment');
            $table->text('hasil_assessment');
            $table->string('tembusan')->default('Kepala Bidang Perlinsos');
            $table->timestamp('ttd_tagana_at')->nullable();
            $table->timestamps();
        });

        // ---------- Barang yang dibutuhkan / disetujui / keluar ----------
        Schema::create('sigap_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('sigap_laporan')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('sigap_barang')->restrictOnDelete();
            $table->string('nama_barang');                     // snapshot
            $table->string('satuan', 30);                      // snapshot
            $table->unsignedInteger('jumlah_dibutuhkan');      // diisi Tagana (assessment)
            $table->unsignedInteger('jumlah_disetujui')->nullable(); // diisi Perlinsos (verifikasi)
            $table->unsignedInteger('jumlah_keluar')->nullable();    // terisi saat Kadis menyetujui
            $table->timestamps();

            $table->unique(['laporan_id', 'barang_id']);
        });

        // ---------- Tahap 3: Verifikasi Perlinsos + Form Permohonan Barang ----------
        Schema::create('sigap_permohonan_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->unique()->constrained('sigap_laporan')->cascadeOnDelete();
            $table->string('nomor_surat', 60)->nullable()->unique();
            $table->date('tanggal');
            $table->foreignId('verifikator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_verifikator');
            $table->text('hasil_verifikasi');
            $table->timestamp('ttd_perlinsos_at')->nullable();
            $table->timestamps();
        });

        // ---------- Tahap 4-5: Surat Pengeluaran Barang (TTD Pengurus Barang + Kadis) ----------
        Schema::create('sigap_pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->unique()->constrained('sigap_laporan')->cascadeOnDelete();
            $table->string('nomor_surat', 60)->nullable()->unique();
            $table->date('tanggal');
            $table->foreignId('pengurus_barang_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_pengurus_barang');
            $table->timestamp('ttd_pengurus_at')->nullable();
            $table->foreignId('kadis_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_kadis')->nullable();
            $table->timestamp('ttd_kadis_at')->nullable();
            $table->timestamps();
        });

        // ---------- Mutasi stok (riwayat masuk/keluar) ----------
        Schema::create('sigap_stok_mutasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('sigap_barang')->cascadeOnDelete();
            $table->string('jenis', 10);                       // masuk | keluar
            $table->unsignedInteger('jumlah');
            $table->unsignedInteger('saldo_setelah');
            $table->foreignId('laporan_id')->nullable()->constrained('sigap_laporan')->nullOnDelete();
            $table->string('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['barang_id', 'created_at']);
        });

        // ---------- Tahap 6: BAST + dokumentasi (diunggah OPD wilayah) ----------
        Schema::create('sigap_bast', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->unique()->constrained('sigap_laporan')->cascadeOnDelete();
            $table->string('nomor_bast', 60)->nullable()->unique();
            $table->date('tanggal_terima');
            $table->foreignId('penerima_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_penerima');
            $table->string('jabatan_penerima');
            $table->string('instansi_penerima')->nullable();   // kelurahan / OPD wilayah
            $table->text('catatan')->nullable();
            $table->timestamp('ttd_penerima_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sigap_dokumentasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('sigap_laporan')->cascadeOnDelete();
            $table->string('path');
            $table->string('keterangan')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ---------- Log proses ----------
        Schema::create('sigap_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('sigap_laporan')->cascadeOnDelete();
            $table->timestamp('tanggal_proses')->useCurrent();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('username')->nullable();
            $table->string('taskname');
            $table->string('rolename')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['laporan_id', 'tanggal_proses']);
        });
    }

    public function down(): void
    {
        foreach ([
            'sigap_log', 'sigap_dokumentasi', 'sigap_bast', 'sigap_stok_mutasi', 'sigap_pengeluaran',
            'sigap_permohonan_barang', 'sigap_item', 'sigap_assessment', 'sigap_laporan',
            'sigap_barang', 'sigap_nomor_urut',
        ] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
