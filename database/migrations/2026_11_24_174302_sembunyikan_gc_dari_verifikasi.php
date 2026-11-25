<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('gc_kriteria_parameters')->where('target', 'gc')->update(['tampil_verifikasi' => false]);
    }

    public function down(): void {}
};