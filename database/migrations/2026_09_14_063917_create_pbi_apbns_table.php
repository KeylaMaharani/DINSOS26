<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbi_apbns', function (Blueprint $table) {
            $table->id();

            // ==== 1. Data Permohonan (diisi masyarakat) ====
            $table->string('nama_layanan')->default('PBI APBN');
            $table->string('no_registrasi')->unique();
            $table->string('no_kk');
            $table->string('nama_kepala_keluarga');
            $table->string('nik_kepala_keluarga');
            $table->text('alamat');
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('desa_kelurahan')->nullable();
            $table->string('no_telp')->nullable();
            $table->string('email')->nullable();

            // ==== 2. Koordinat ====
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('desil_nasional')->nullable();
            $table->string('screenshot_pembaharuan_desil')->nullable();
            $table->text('kelengkapan_kekurangan_berkas')->nullable();
            $table->text('catatan_berkas')->nullable();
            $table->string('screenshot_dtsen')->nullable();
            $table->text('diagnosa')->nullable();

            // ==== 3. Lampiran Persyaratan ====
            $table->string('scan_ktp')->nullable();
            $table->string('scan_kk')->nullable();
            $table->string('foto_rumah')->nullable();
            $table->string('foto_kamar_mandi')->nullable();
            $table->string('foto_selfie_ktp')->nullable();
            $table->string('surat_rawat_inap')->nullable();

            // ==== 4. Verifikasi & Validasi Petugas Kelurahan (READ ONLY di sisi Dinsos) ====
            $table->string('status_penguasaan_bangunan')->nullable();
            $table->string('status_lahan')->nullable();
            $table->string('penghasilan_rata_rata')->nullable();
            $table->boolean('punya_kendaraan_roda_2')->nullable();
            $table->unsignedTinyInteger('jumlah_tanggungan_keluarga')->nullable();
            $table->string('jenis_lantai')->nullable();
            $table->string('jenis_dinding')->nullable();
            $table->string('kondisi_dinding')->nullable();
            $table->string('jenis_atap')->nullable();
            $table->string('kondisi_atap')->nullable();
            $table->string('sumber_air_minum')->nullable();
            $table->string('cara_memperoleh_air_minum')->nullable();
            $table->string('sumber_penerangan_utama')->nullable();
            $table->string('daya_terpasang')->nullable();
            $table->string('bahan_bakar_energi_memasak')->nullable();
            $table->string('penggunaan_fasilitas_bab')->nullable();
            $table->string('jenis_kloset')->nullable();
            $table->string('tempat_pembuangan_akhir_tinja')->nullable();

            // 5. Kesimpulan hasil verifikasi kelurahan
            $table->text('kesimpulan_rekomendasi')->nullable();
            $table->string('scan_surat_pengantar')->nullable();
            $table->string('surat_pengantar_digital')->nullable();
            $table->boolean('sudah_diverifikasi_kelurahan')->default(false);

            // 6. Fasilitas kesehatan
            $table->string('nama_faskes')->nullable();

            // ==== Alur internal Dinsos ====
            // operator_dinsos -> kabid -> kadis -> disetujui | ditolak
            $table->string('status')->default('operator_dinsos');
            $table->text('catatan_internal')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbi_apbns');
    }
};
