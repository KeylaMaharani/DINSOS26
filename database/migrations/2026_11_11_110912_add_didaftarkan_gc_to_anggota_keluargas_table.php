<?php

use App\Models\PbiApbdAnggotaKeluarga;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda anggota keluarga yang didaftarkan warga ke Ground Check.
 * Kepala keluarga selalu ikut (tidak bergantung kolom ini).
 */
return new class extends Migration
{
    private function tabel(): string
    {
        return (new PbiApbdAnggotaKeluarga)->getTable();
    }

    public function up(): void
    {
        $t = $this->tabel();

        Schema::table($t, function (Blueprint $table) {
            $table->boolean('didaftarkan_gc')->default(false);
            $table->timestamp('didaftarkan_gc_at')->nullable();
        });

        // Data lama: anggota yang sudah menjadi sasaran Ground Check dianggap sudah didaftarkan.
        DB::table($t)
            ->whereIn('nik', DB::table('gc_sasarans')->select('nik'))
            ->update(['didaftarkan_gc' => true, 'didaftarkan_gc_at' => now()]);
    }

    public function down(): void
    {
        Schema::table($this->tabel(), function (Blueprint $table) {
            $table->dropColumn(['didaftarkan_gc', 'didaftarkan_gc_at']);
        });
    }
};
