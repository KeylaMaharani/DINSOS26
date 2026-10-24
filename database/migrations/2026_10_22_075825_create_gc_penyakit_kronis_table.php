<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tab D Kesehatan:
 *  - Buat master Penyakit Kronis/Menahun (gc_penyakit_kronis) + isi awal sesuai dropdown SOLID.
 *  - "Apakah memiliki keluhan kesehatan kronis/menahun?" berubah dari Ya/Tidak menjadi dropdown master.
 *  - "Apakah penderita stunting?" dinonaktifkan (jawaban lama tetap aman).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gc_penyakit_kronis')) {
            Schema::create('gc_penyakit_kronis', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->string('nama')->unique();
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        $daftar = [
            'Tidak Ada',
            'Hipertensi (Tekanan Darah Tinggi)',
            'Rematik',
            'Asma',
            'Masalah jantung',
            'Diabetes (Kencing Manis)',
            'Tuberkulosis (TBC)',
            'Stroke',
            'Kanker atau Tumor Ganas',
            'Gagal Ginjal',
            'Haemophilia',
            'HIV/AIDS',
            'Kolesterol',
            'Sirosis Hati',
            'Thalasemia',
            'Leukimia',
            'Alzheimer',
            'Lainnya',
        ];

        foreach ($daftar as $i => $nama) {
            $ada = DB::table('gc_penyakit_kronis')->where('nama', $nama)->exists();
            if ($ada) {
                DB::table('gc_penyakit_kronis')->where('nama', $nama)->update(['urutan' => $i + 1, 'updated_at' => now()]);
            } else {
                DB::table('gc_penyakit_kronis')->insert([
                    'nama' => $nama,
                    'urutan' => $i + 1,
                    'aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Parameter keluhan kronis -> bersumber dari master (bukan Ya/Tidak lagi)
        $kronis = DB::table('gc_kriteria_parameters')
            ->where('kelompok', 'kesehatan')
            ->where('nama', 'like', '%keluhan kesehatan kronis%')
            ->first();

        if ($kronis) {
            DB::table('gc_kriteria_parameters')->where('id', $kronis->id)->update([
                'tipe_input' => 'pilihan',
                'sumber' => 'penyakit_kronis',
                'keterangan' => 'Pilihan dikelola di Master Penyakit Kronis/Menahun.',
                'updated_at' => now(),
            ]);

            // Opsi lama (Tidak ada/Ada) tidak dipakai lagi
            DB::table('gc_kriteria_opsis')->where('parameter_id', $kronis->id)->delete();

            // Konversi jawaban lama: "Tidak ada"/"Tidak" -> "Tidak Ada"; "Ada"/"Ya" dikosongkan (harus dipilih ulang)
            DB::table('gc_jawabans')->where('parameter_id', $kronis->id)
                ->whereIn('jawaban', ['Tidak', 'Tidak ada', 'Tidak Ada'])
                ->update(['jawaban' => 'Tidak Ada', 'skor' => null]);
            DB::table('gc_jawabans')->where('parameter_id', $kronis->id)
                ->whereIn('jawaban', ['Ya', 'Ada'])
                ->update(['jawaban' => null, 'skor' => null]);
        }

        // Pertanyaan stunting dihilangkan (dinonaktifkan; jawaban lama tetap tersimpan)
        DB::table('gc_kriteria_parameters')
            ->where('kelompok', 'kesehatan')
            ->where('nama', 'like', '%stunting%')
            ->update(['aktif' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('gc_kriteria_parameters')->where('sumber', 'penyakit_kronis')->update(['sumber' => null]);

        DB::table('gc_kriteria_parameters')
            ->where('kelompok', 'kesehatan')
            ->where('nama', 'like', '%stunting%')
            ->update(['aktif' => true, 'updated_at' => now()]);

        Schema::dropIfExists('gc_penyakit_kronis');
    }
};
