<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * REHABSOS -- PPKS (SOP TRC), Rumah Singgah, Data Permohonan Bantuan.
 * Kolom mengacu ke PDF "Komponen" (aplikasi 1, 2, 3).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- Master ----------
        Schema::create('rehabsos_jenis_ppks', function (Blueprint $t) {
            $t->id();
            $t->string('nama')->unique();
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        Schema::create('rehabsos_jenis_bantuan', function (Blueprint $t) {
            $t->id();
            $t->string('nama')->unique();
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        // ---------- 1. Penjangkauan & Penanganan PPKS (SOP TRC) ----------
        Schema::create('rehabsos_ppks', function (Blueprint $t) {
            $t->id();
            $t->string('no_registrasi', 30)->unique();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('sumber_laporan', 30)->nullable();   // whatsapp|surat|telepon|aplikasi|polisi|satpol_pp|rs|lembaga
            $t->string('nama_pelapor')->nullable();
            $t->date('tanggal_kegiatan');
            $t->unsignedTinyInteger('tim_petugas')->nullable();  // tim 1 / 2 / 3
            $t->string('nama');
            $t->foreignId('jenis_ppks_id')->nullable()->constrained('rehabsos_jenis_ppks')->nullOnDelete();
            $t->text('alamat')->nullable();
            $t->string('tempat_lahir', 100)->nullable();
            $t->date('tanggal_lahir')->nullable();
            $t->string('desil', 10)->nullable();
            $t->string('jenis_bantuan_pemerintah')->nullable();
            $t->string('lokasi_evakuasi')->nullable();
            $t->text('hasil_observasi')->nullable();
            $t->string('status', 30)->default('ajuan_masuk');
            $t->foreignId('current_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $t->timestamps();
            $t->index('status');
        });

        Schema::create('rehabsos_ppks_asesmen', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ppks_id')->unique()->constrained('rehabsos_ppks')->cascadeOnDelete();
            $t->text('biologis')->nullable();
            $t->text('psikologis')->nullable();
            $t->text('sosial')->nullable();
            $t->text('spiritual')->nullable();
            $t->text('hasil_asesmen')->nullable();
            $t->text('intervensi_tindak_lanjut')->nullable();
            $t->string('rekomendasi_rujukan')->nullable();
            $t->string('approval', 15)->nullable();          // disetujui | ditolak
            $t->foreignId('approval_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approval_at')->nullable();
            $t->string('tujuan_rujukan')->nullable();
            $t->timestamps();
        });

        // ---------- 2. Penanganan Klien Rumah Singgah ----------
        Schema::create('rehabsos_rs_klien', function (Blueprint $t) {
            $t->id();
            $t->string('no_registrasi', 30)->unique();
            $t->foreignId('ppks_id')->nullable()->constrained('rehabsos_ppks')->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->date('tanggal_kegiatan');
            $t->string('petugas')->nullable();
            $t->string('nama');
            $t->foreignId('jenis_ppks_id')->nullable()->constrained('rehabsos_jenis_ppks')->nullOnDelete();
            $t->text('alamat')->nullable();
            $t->string('tempat_lahir', 100)->nullable();
            $t->date('tanggal_lahir')->nullable();
            $t->unsignedTinyInteger('usia')->nullable();
            $t->string('pendidikan', 50)->nullable();
            $t->string('pekerjaan', 100)->nullable();
            $t->string('desil', 10)->nullable();
            $t->string('jenis_bantuan_pemerintah')->nullable();
            $t->string('lokasi_evakuasi')->nullable();
            $t->text('hasil_observasi')->nullable();
            $t->text('hasil_asesmen')->nullable();
            $t->text('intervensi_tindak_lanjut')->nullable();
            $t->date('tanggal_masuk')->nullable();
            $t->date('batas_layanan')->nullable();           // tanggal_masuk + 7 hari (SOP RS langkah 4)
            $t->string('tujuan_rujukan')->nullable();
            $t->date('tanggal_keluar')->nullable();
            $t->string('status', 30)->default('diterima');
            $t->foreignId('current_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $t->timestamps();
            $t->index(['status', 'batas_layanan']);
        });

        Schema::create('rehabsos_rs_layanan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('klien_id')->constrained('rehabsos_rs_klien')->cascadeOnDelete();
            $t->date('tanggal');
            $t->string('kamar', 50)->nullable();
            $t->unsignedTinyInteger('makan_kali')->default(0);   // target 3x sehari
            $t->boolean('sandang')->default(false);
            $t->boolean('homecare')->default(false);
            $t->text('layanan_medis')->nullable();
            $t->text('catatan')->nullable();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['klien_id', 'tanggal']);
        });

        // ---------- 3. Data Permohonan Bantuan ----------
        Schema::create('rehabsos_bantuan', function (Blueprint $t) {
            $t->id();
            $t->string('no_permohonan', 30)->unique();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('jenis_permohonan');
            $t->foreignId('jenis_bantuan_id')->nullable()->constrained('rehabsos_jenis_bantuan')->nullOnDelete();
            $t->string('nama_pemohon');
            $t->string('nik', 16)->nullable();
            $t->string('no_kk', 16)->nullable();
            $t->string('pekerjaan', 100)->nullable();
            $t->string('desil', 10)->nullable();
            $t->string('jenis_bantuan_pemerintah')->nullable();
            $t->foreignId('jenis_ppks_id')->nullable()->constrained('rehabsos_jenis_ppks')->nullOnDelete();
            $t->text('alamat')->nullable();
            $t->string('ttd_elektronik')->nullable();
            $t->date('tanggal_serah_terima')->nullable();
            $t->string('status', 30)->default('diajukan');
            $t->foreignId('current_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $t->timestamps();
            $t->index('status');
        });

        // ---------- Lampiran (polimorfik) & Log ----------
        Schema::create('rehabsos_lampiran', function (Blueprint $t) {
            $t->id();
            $t->morphs('lampiranable');
            $t->string('jenis', 30);                          // dokumentasi|ktp|kk|surat_wilayah|surat_polisi
            $t->string('path');
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('rehabsos_log', function (Blueprint $t) {
            $t->id();
            $t->morphs('logable');
            $t->timestamp('tanggal_proses')->useCurrent();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('username')->nullable();
            $t->string('taskname');
            $t->string('rolename')->nullable();
            $t->text('catatan')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'rehabsos_log', 'rehabsos_lampiran', 'rehabsos_bantuan', 'rehabsos_rs_layanan',
            'rehabsos_rs_klien', 'rehabsos_ppks_asesmen', 'rehabsos_ppks',
            'rehabsos_jenis_bantuan', 'rehabsos_jenis_ppks',
        ] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
