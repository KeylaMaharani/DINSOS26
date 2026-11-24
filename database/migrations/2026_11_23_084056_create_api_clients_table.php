<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_clients', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->char('key_hash', 64)->unique();
            $t->json('scopes');                         // ["nik","kk","index","statistik","wilayah","detail"]
            $t->json('kecamatan')->nullable();          // null = semua wilayah
            $t->json('allowed_ips')->nullable();        // IP atau CIDR, null = bebas
            $t->unsignedSmallInteger('per_page_max')->default(25);
            $t->unsignedSmallInteger('rate_per_menit')->default(60);
            $t->unsignedInteger('kuota_harian')->default(1000);
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('revoked_at')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_clients');
    }
};
