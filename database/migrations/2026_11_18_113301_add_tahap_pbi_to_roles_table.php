<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // kelurahan | lurah | operator | kabid | kadis | dinkes | bpjs | admin (paksa tampilan admin) | NULL (tebak dari nama/slug)
            $table->string('tahap_pbi', 20)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('tahap_pbi');
        });
    }
};
