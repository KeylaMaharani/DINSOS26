<?php

namespace Database\Seeders;

use App\Models\GcKriteriaParameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tambahan field Ground Check untuk DATABASE YANG SUDAH TERISI
 * (GcKriteriaSeeder dilewati bila master sudah ada).
 *
 * Aman dijalankan berulang: field yang sudah ada (kelompok + nama sama) DILEWATI,
 * jadi hasil edit Super Admin di menu master tidak tertimpa.
 *
 * CATATAN PEMISAHAN PBI vs GROUND CHECK:
 *  Diagnosa, Faskes, Surat Keterangan Rawat Inap, dan Foto KTP SENGAJA TIDAK ada di Ground Check.
 *  Ground Check hanya memeriksa data; keempat field itu milik pengajuan PBI APBD
 *  (kolom pbi_apbds + menu Dokumen + halaman Ajuan).
 *  Foto kartu keluarga di Ground Check memakai Scan KK yang sudah diunggah di PBI.
 *
 * Jalankan: php artisan db:seed --class=GcKriteriaTambahanSeeder
 */
class GcKriteriaTambahanSeeder extends Seeder
{
    private const KELOMPOK_VALID = [
        'kependudukan', 'pendidikan', 'ketenagakerjaan', 'kesehatan',
        'keluarga', 'aset_bergerak', 'aset_tidak_bergerak', 'dokumentasi',
    ];

    private const TIPE_VALID = [
        'pilihan', 'pilihan_ganda', 'teks', 'angka', 'tanggal', 'koordinat', 'foto',
    ];

    public function run(): void
    {
        // Format: [kelompok, nama, bobot, tipe_input, wajib, opsi (opsional)]
        $tambahan = [
            ['dokumentasi', 'Foto kartu keluarga', 2, 'foto', false],
        ];

        DB::transaction(function () use ($tambahan) {
            $urutan = (int) GcKriteriaParameter::max('urutan');

            foreach ($tambahan as $row) {
                [$kelompok, $nama, $bobot, $tipe, $wajib] = $row;
                $opsi = $row[5] ?? [];

                if (! in_array($kelompok, self::KELOMPOK_VALID, true)) {
                    $this->command?->error("Kelompok '{$kelompok}' tidak valid, '{$nama}' dilewati.");
                    continue;
                }

                if (! in_array($tipe, self::TIPE_VALID, true)) {
                    $this->command?->error("Tipe '{$tipe}' tidak valid, '{$nama}' dilewati.");
                    continue;
                }

                if (GcKriteriaParameter::where('kelompok', $kelompok)->where('nama', $nama)->exists()) {
                    $this->command?->line("Sudah ada, dilewati: {$nama}");
                    continue;
                }

                $param = GcKriteriaParameter::create([
                    'kelompok' => $kelompok,
                    'urutan' => ++$urutan,
                    'nama' => $nama,
                    'bobot' => $bobot,
                    'tipe_input' => $tipe,
                    'sumber' => null,
                    'pbi_parameter_id' => null,
                    'keterangan' => $opsi && in_array($tipe, ['pilihan', 'pilihan_ganda'], true)
                        ? 'Opsi sementara, nilai belum ditentukan.'
                        : null,
                    'aktif' => true,
                    'wajib' => $wajib,
                ]);

                if ($param->butuhOpsi()) {
                    foreach ($opsi as $j => $label) {
                        $param->opsis()->updateOrCreate(
                            ['label' => $label],
                            ['nilai' => null, 'urutan' => $j + 1]
                        );
                    }
                }

                $this->command?->info("Ditambahkan: {$nama}");
            }
        });
    }
}