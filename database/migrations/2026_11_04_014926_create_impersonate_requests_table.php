<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impersonate_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            // pending | approved | rejected | expired | used
            $table->string('status', 20)->default('pending')->index();
            $table->text('otp')->nullable();              // terenkripsi, dikosongkan setelah dipakai
            $table->unsignedTinyInteger('percobaan')->default(0);
            $table->timestamp('expired_at');
            $table->timestamp('disetujui_at')->nullable();
            $table->timestamp('digunakan_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impersonate_requests');
    }
};
