<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Taruh di database/migrations/.
 *
 * didaftarkan_pbi = anggota ini termasuk "Anggota Keluarga Yang Didaftarkan" pada pengajuan PBI APBD.
 * Berlaku untuk SEMUA anggota termasuk kepala keluarga (kepala boleh tidak ikut mengajukan).
 * Ground Check tidak berubah: kepala keluarga selalu ikut, anggota lain ikut bila didaftarkan_gc = true.
 *
 * Backfill agar tampilan data lama tidak berubah: kepala keluarga + anggota yang sudah dicentang (didaftarkan_gc).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbi_apbd_anggota_keluargas', function (Blueprint $table) {
            if (! Schema::hasColumn('pbi_apbd_anggota_keluargas', 'didaftarkan_pbi')) {
                $table->boolean('didaftarkan_pbi')->default(false);
            }
        });

        DB::statement("
            UPDATE pbi_apbd_anggota_keluargas a
            JOIN pbi_apbds p ON p.id = a.pbi_apbd_id
            SET a.didaftarkan_pbi = 1
            WHERE a.didaftarkan_gc = 1
               OR a.nik = p.nik_kepala_keluarga
               OR LOWER(REPLACE(a.hubungan_keluarga, '_', ' ')) LIKE '%kepala%'
        ");
    }

    public function down(): void
    {
        Schema::table('pbi_apbd_anggota_keluargas', function (Blueprint $table) {
            if (Schema::hasColumn('pbi_apbd_anggota_keluargas', 'didaftarkan_pbi')) {
                $table->dropColumn('didaftarkan_pbi');
            }
        });
    }
};