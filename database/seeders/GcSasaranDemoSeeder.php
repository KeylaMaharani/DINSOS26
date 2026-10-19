<?php

namespace Database\Seeders;

use App\Models\GcSasaran;
use Illuminate\Database\Seeder;

/**
 * DATA FIKTIF untuk uji alur Ground Check. Jangan dipakai di produksi.
 * Data asli diimpor dari daftar sasaran Dinsos ke tabel gc_sasarans.
 * Jalankan: php artisan db:seed --class=GcSasaranDemoSeeder
 */
class GcSasaranDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (GcSasaran::exists()) {
            $this->command?->warn('gc_sasarans sudah berisi data, seeder demo dilewati.');
            return;
        }

        $kk = [
            ['3271000000000001', 'Babakan Lebak', 'Balumbang Jaya', 'Bogor Barat', '1', '6', 2, [
                ['3271000000010001', 'Contoh Satu', '1985-02-10'], ['3271000000010002', 'Contoh Dua', '1988-07-21'],
                ['3271000000010003', 'Contoh Tiga', '2010-03-17'], ['3271000000010004', 'Contoh Empat', '2021-11-05'],
            ]],
            ['3271000000000002', 'Cilubang', 'Balumbang Jaya', 'Bogor Barat', '4', '4', 5, [
                ['3271000000020001', 'Contoh Lima', '1979-09-30'], ['3271000000020002', 'Contoh Enam', '1982-12-12'],
                ['3271000000020003', 'Contoh Tujuh', '2008-01-25'],
            ]],
            ['3271000000000003', 'Jl. Batu Hulung', 'Balumbang Jaya', 'Bogor Barat', '1', '1', 3, [
                ['3271000000030001', 'Contoh Delapan', '1970-05-02'], ['3271000000030002', 'Contoh Sembilan', '1974-08-19'],
            ]],
        ];

        foreach ($kk as [$noKk, $alamat, $kel, $kec, $rt, $rw, $desil, $anggota]) {
            foreach ($anggota as [$nik, $nama, $lahir]) {
                GcSasaran::create([
                    'nik' => $nik, 'nama' => $nama, 'no_kk' => $noKk, 'tanggal_lahir' => $lahir,
                    'kecamatan' => $kec, 'kelurahan' => $kel, 'alamat' => $alamat,
                    'rt' => $rt, 'rw' => $rw, 'desil' => $desil,
                ]);
            }
        }

        $this->command?->info('Data sasaran demo (fiktif) berhasil dibuat.');
    }
}
