<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ganti('PBI APBN', 'PBI APBD');
    }

    public function down(): void
    {
        $this->ganti('PBI APBD', 'PBI APBN');
    }

    private function ganti(string $from, string $to): void
    {
        if (! Schema::hasTable('pbi_apbds') || ! Schema::hasColumn('pbi_apbds', 'nama_layanan')) {
            return;
        }

        // Ubah baris yang sudah ada
        DB::table('pbi_apbds')
            ->where('nama_layanan', $from)
            ->update(['nama_layanan' => $to]);

        // Ubah default kolom untuk data baru
        Schema::table('pbi_apbds', function ($table) use ($to) {
            $table->string('nama_layanan')->default($to)->change();
        });
    }
};
