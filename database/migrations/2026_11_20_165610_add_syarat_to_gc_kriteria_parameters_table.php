<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gc_kriteria_parameters', function (Blueprint $t) {
            $t->unsignedBigInteger('induk_id')->nullable()->after('pbi_parameter_id');
            $t->json('induk_nilai')->nullable()->after('induk_id');
        });
    }

    public function down(): void
    {
        Schema::table('gc_kriteria_parameters', function (Blueprint $t) {
            $t->dropColumn(['induk_id', 'induk_nilai']);
        });
    }
};