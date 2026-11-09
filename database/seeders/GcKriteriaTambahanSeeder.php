<?php

namespace Database\Seeders;

use App\Models\GcKriteriaParameter;
use Illuminate\Database\Seeder;

/**
 * Tambahan field Ground Check untuk DATABASE YANG SUDAH TERISI
 * (GcKriteriaSeeder dilewati bila master sudah ada).
 *
 * Aman dijalankan berulang: field yang sudah ada tidak dibuat dua kali,
 * hanya jenis isian & status wajib-nya disesuaikan.
 *
 * Jalankan: php artisan db:seed --class=GcKriteriaTambahanSeeder
 */
class GcKriteriaTambahanSeeder extends Seeder
{
    public function run(): void
    {
        // [kelompok, nama, bobot, tipe_input, wajib]
        $tambahan = [
            ['kesehatan',   'Diagnosa',                      0, 'teks', true],
            ['kesehatan',   'Faskes (fasilitas kesehatan)',  0, 'teks', true],
            ['kesehatan',   'Surat Keterangan Rawat Inap',   0, 'foto', false], // opsional
            ['dokumentasi', 'Foto kartu keluarga',           2, 'foto', true],
            ['dokumentasi', 'Foto KTP',                      2, 'foto', true],
        ];

        $urutan = (int) GcKriteriaParameter::max('urutan');

        foreach ($tambahan as [$kelompok, $nama, $bobot, $tipe, $wajib]) {
            $param = GcKriteriaParameter::where('kelompok', $kelompok)->where('nama', $nama)->first();

            if ($param) {
                // Sudah ada: hanya samakan jenis isian & status wajib, bobot/urutan hasil edit admin dibiarkan.
                $param->update(['tipe_input' => $tipe, 'wajib' => $wajib, 'aktif' => true]);
                $this->command?->line("Sudah ada, diperbarui: {$nama}");
                continue;
            }

            GcKriteriaParameter::create([
                'kelompok' => $kelompok,
                'urutan' => ++$urutan,
                'nama' => $nama,
                'bobot' => $bobot,
                'tipe_input' => $tipe,
                'sumber' => null,
                'pbi_parameter_id' => null,
                'keterangan' => null,
                'aktif' => true,
                'wajib' => $wajib,
            ]);
            $this->command?->info("Ditambahkan: {$nama}");
        }
    }
}