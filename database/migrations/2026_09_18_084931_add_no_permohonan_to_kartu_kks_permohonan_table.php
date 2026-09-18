<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kartu_kks_permohonan', function (Blueprint $table) {
            $table->string('no_permohonan')->nullable()->after('id');
        });

        // Backfill data lama yang sudah ada supaya kolom tidak kosong,
        // nomor dibuat berurutan per tahun berdasarkan created_at.
        $rows = DB::table('kartu_kks_permohonan')->orderBy('id')->get();
        $counterPerYear = [];

        foreach ($rows as $row) {
            $year = Carbon::parse($row->created_at ?? now())->year;
            $counterPerYear[$year] = ($counterPerYear[$year] ?? 0) + 1;

            $noPermohonan = 'KKS-' . $year . '-' . str_pad($counterPerYear[$year], 4, '0', STR_PAD_LEFT);

            DB::table('kartu_kks_permohonan')
                ->where('id', $row->id)
                ->update(['no_permohonan' => $noPermohonan]);
        }

        Schema::table('kartu_kks_permohonan', function (Blueprint $table) {
            $table->unique('no_permohonan');
        });
    }

    public function down(): void
    {
        Schema::table('kartu_kks_permohonan', function (Blueprint $table) {
            $table->dropUnique(['no_permohonan']);
            $table->dropColumn('no_permohonan');
        });
    }
};
