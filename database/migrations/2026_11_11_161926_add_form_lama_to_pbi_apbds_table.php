<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * pbi_apbds.form_lama
 *   false (bawaan) = pendaftar BARU  -> form Lengkapi Data memakai semua parameter Ground Check (39 variabel)
 *   true           = akun LAMA        -> memakai parameter yang bertaut ke 18 parameter PBI
 *
 * Backfill: semua pengajuan yang SUDAH ADA saat migration ini dijalankan dianggap akun lama,
 * kecuali yang sudah pernah menjawab form 39 variabel (punya jawaban di pbi_apbd_gc_jawabans).
 * Pendaftar baru setelahnya otomatis false karena nilai bawaan kolom.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbi_apbds', function (Blueprint $table) {
            if (! Schema::hasColumn('pbi_apbds', 'form_lama')) {
                $table->boolean('form_lama')->default(false)->after('status');
            }
        });

        DB::table('pbi_apbds')
            ->whereNotIn('id', function ($q) {
                $q->select('pbi_apbd_id')
                    ->from('pbi_apbd_gc_jawabans')
                    ->whereNotNull('pbi_apbd_id')
                    ->whereNotNull('jawaban')
                    ->where('jawaban', '!=', '');
            })
            ->update(['form_lama' => true]);
    }

    public function down(): void
    {
        Schema::table('pbi_apbds', function (Blueprint $table) {
            if (Schema::hasColumn('pbi_apbds', 'form_lama')) {
                $table->dropColumn('form_lama');
            }
        });
    }
};