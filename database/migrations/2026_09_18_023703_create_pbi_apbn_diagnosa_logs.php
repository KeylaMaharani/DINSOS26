<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbi_apbn_diagnosa_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pbi_apbn_id')->constrained('pbi_apbns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('username')->nullable();
            $table->string('role_name')->nullable();

            $table->text('diagnosa');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbi_apbn_diagnosa_logs');
    }
};
