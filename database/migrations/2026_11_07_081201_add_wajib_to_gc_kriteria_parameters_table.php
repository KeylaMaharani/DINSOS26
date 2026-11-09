<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gc_kriteria_parameters', function (Blueprint $table) {
            $table->boolean('wajib')->default(true);
        });

        // Pertahankan perilaku lama: foto & koordinat/lintang/bujur tetap tidak wajib
        DB::table('gc_kriteria_parameters')
            ->where(function ($q) {
                $q->whereIn('tipe_input', ['foto', 'koordinat'])
                  ->orWhere('nama', 'like', '%lintang%')
                  ->orWhere('nama', 'like', '%bujur%');
            })
            ->update(['wajib' => false]);
    }

    public function down(): void
    {
        Schema::table('gc_kriteria_parameters', function (Blueprint $table) {
            $table->dropColumn('wajib');
        });
    }
};
