<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbi_apbds', function (Blueprint $table) {
            if (! Schema::hasColumn('pbi_apbds', 'surat_keterangan')) {
                $table->string('surat_keterangan')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pbi_apbds', function (Blueprint $table) {
            if (Schema::hasColumn('pbi_apbds', 'surat_keterangan')) {
                $table->dropColumn('surat_keterangan');
            }
        });
    }
};
