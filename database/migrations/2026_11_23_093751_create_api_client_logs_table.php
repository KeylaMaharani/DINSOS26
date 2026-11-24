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
        Schema::create('api_client_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('api_client_id')->nullable()->index();
            $t->string('klien')->nullable();
            $t->string('event', 30)->index();      // akses | auth_gagal | ip_ditolak | scope_ditolak
            $t->string('ip', 45)->nullable()->index();
            $t->string('method', 10);
            $t->string('path');
            $t->json('query_keys')->nullable();
            $t->unsignedSmallInteger('status')->index();
            $t->char('req_id', 36)->nullable();
            $t->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_client_logs');
    }
};
