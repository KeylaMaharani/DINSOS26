<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbi_apbds', function (Blueprint $table) {
            // Penanda "warga sudah menekan Simpan Data" (= sudah resmi jadi pengajuan).
            // Dipakai untuk membedakan draft vs pengajuan di tracking, Ajuan, dan badge sidebar.
            if (! Schema::hasColumn('pbi_apbds', 'data_diisi_at')) {
                $table->timestamp('data_diisi_at')->nullable();
            }

            if (! Schema::hasColumn('pbi_apbds', 'dikembalikan_ke_masyarakat')) {
                $table->boolean('dikembalikan_ke_masyarakat')->default(false);
            }

            // Tanda tangan khusus Kelurahan (file gambar), diunggah petugas Kelurahan
            if (! Schema::hasColumn('pbi_apbds', 'ttd_kelurahan')) {
                $table->string('ttd_kelurahan')->nullable();
            }
        });

        // Data lama yang sudah lewat dari Kelurahan jelas bukan draft.
        // (Baris berstatus 'kelurahan' sengaja tidak disentuh: tidak bisa dibedakan dari draft.)
        DB::table('pbi_apbds')
            ->whereNull('data_diisi_at')
            ->where('status', '!=', 'kelurahan')
            ->update(['data_diisi_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        // Hanya kolom TTD yang dibuang; data_diisi_at & dikembalikan_ke_masyarakat
        // dipakai fitur lain sehingga tidak ikut dihapus.
        Schema::table('pbi_apbds', function (Blueprint $table) {
            if (Schema::hasColumn('pbi_apbds', 'ttd_kelurahan')) {
                $table->dropColumn('ttd_kelurahan');
            }
        });
    }
};
