<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gc_lapangan_usahas')) {
            Schema::create('gc_lapangan_usahas', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->string('nama')->unique();
                $table->string('ikon', 16)->nullable();
                $table->text('penjelasan')->nullable();
                $table->text('contoh_berusaha_sendiri')->nullable();
                $table->text('contoh_buruh')->nullable();
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('gc_pekerjaans')) {
            Schema::create('gc_pekerjaans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lapangan_usaha_id')->constrained('gc_lapangan_usahas')->cascadeOnDelete();
                $table->string('nama');
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->boolean('aktif')->default(true);
                $table->timestamps();
                $table->unique(['lapangan_usaha_id', 'nama']);
            });
        }

        if (! Schema::hasTable('gc_keterampilans')) {
            Schema::create('gc_keterampilans', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->string('nama')->unique();
                $table->boolean('eksklusif')->default(false); // true = "Belum ada": tidak boleh digabung pilihan lain
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('gc_keterampilans', 'eksklusif')) {
            Schema::table('gc_keterampilans', function (Blueprint $table) {
                $table->boolean('eksklusif')->default(false)->after('nama');
            });
        }

        if (! Schema::hasColumn('gc_kriteria_parameters', 'sumber')) {
            Schema::table('gc_kriteria_parameters', function (Blueprint $table) {
                $table->string('sumber', 20)->nullable()->after('tipe_input');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('gc_kriteria_parameters', 'sumber')) {
            Schema::table('gc_kriteria_parameters', fn (Blueprint $t) => $t->dropColumn('sumber'));
        }
        Schema::dropIfExists('gc_keterampilans');
        Schema::dropIfExists('gc_pekerjaans');
        Schema::dropIfExists('gc_lapangan_usahas');
    }
};
