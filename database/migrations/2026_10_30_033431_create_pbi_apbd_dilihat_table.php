<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mencatat siapa yang sudah membuka detail sebuah permohonan PBI APBD pada tahap tertentu.
 * Dipakai untuk badge/notifikasi: sudah dibuka = notifikasi hilang untuk user itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbi_apbd_dilihat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pbi_apbd_id')->constrained('pbi_apbds')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 50); // tahap saat detail dibuka (kelurahan, operator_dinsos, kabid, kadis, ...)
            $table->timestamp('dilihat_at')->nullable();

            $table->unique(['pbi_apbd_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbi_apbd_dilihat');
    }
};
