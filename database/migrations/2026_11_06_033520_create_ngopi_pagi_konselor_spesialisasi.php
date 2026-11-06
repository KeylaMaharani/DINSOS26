<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ngopi_pagi_spesialisasis', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 80);
            $t->string('slug', 100)->unique();
            $t->string('deskripsi', 200)->nullable();
            $t->string('ikon', 40)->default('psychology');   // nama Material Symbol
            $t->string('warna', 20)->default('sky');         // lihat NgopiPagiSpesialisasi::WARNA
            $t->char('kategori_awal', 1)->nullable();        // pra-klasifikasi A/B/C (tetap dikonfirmasi saat Screening)
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        // Profil publik konselor. Akun login & hak akses tetap di tabel users.
        Schema::create('ngopi_pagi_konselors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $t->string('nama_tampilan', 100);                // termasuk gelar, mis. "Rina Marlina, M.Psi."
            $t->string('headline', 100);                     // mis. "Psikolog Keluarga"
            $t->text('bio')->nullable();
            $t->unsignedTinyInteger('pengalaman_tahun')->nullable();
            $t->string('foto')->nullable();                  // path di disk public
            $t->string('status', 12)->default('tersedia');   // tersedia | sibuk | offline
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        Schema::create('ngopi_pagi_konselor_spesialisasi', function (Blueprint $t) {
            $t->foreignId('konselor_id')->constrained('ngopi_pagi_konselors')->cascadeOnDelete();
            $t->foreignId('spesialisasi_id')->constrained('ngopi_pagi_spesialisasis')->cascadeOnDelete();
            $t->primary(['konselor_id', 'spesialisasi_id']);
        });

        Schema::table('ngopi_pagi_konsultasis', function (Blueprint $t) {
            $t->foreignId('spesialisasi_id')->nullable()->after('user_id')
                ->constrained('ngopi_pagi_spesialisasis')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ngopi_pagi_konsultasis', fn (Blueprint $t) => $t->dropConstrainedForeignId('spesialisasi_id'));
        Schema::dropIfExists('ngopi_pagi_konselor_spesialisasi');
        Schema::dropIfExists('ngopi_pagi_konselors');
        Schema::dropIfExists('ngopi_pagi_spesialisasis');
    }
};
