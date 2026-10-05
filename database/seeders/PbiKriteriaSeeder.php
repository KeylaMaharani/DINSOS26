<?php

namespace Database\Seeders;

use App\Models\PbiKriteriaParameter;
use Illuminate\Database\Seeder;

/**
 * Sumber: Lampiran SK Wali Kota Bogor No. 460/Kep.196-Dinsos/2021, bagian A (hal. 4-5).
 * Format: [nama parameter, bobot, [[label opsi, indeks terintegrasi], ...]]
 * Seeder hanya berjalan jika master masih kosong, jadi tidak menimpa hasil edit Super Admin.
 */
class PbiKriteriaSeeder extends Seeder
{
    public function run(): void
    {
        if (PbiKriteriaParameter::exists()) {
            $this->command?->warn('Master kriteria sudah ada, seeder dilewati.');
            return;
        }

        $data = [
            ['Status penguasaan bangunan tempat tinggal yang ditempati', 0.50, [
                ['Milik sendiri', 1.00], ['Kontrak/sewa', 0.50], ['Milik orang lain', 0.20], ['Dinas', 0.10],
            ]],
            ['Status lahan tempat tinggal yang ditempati', 0.50, [
                ['Milik sendiri', 1.00], ['Milik orang lain', 0.40], ['Tanah negara', 0.10],
            ]],
            ['Penghasilan rata-rata per bulan', 0.50, [
                ['Lebih dari Rp5.000.000,00', 1.00], ['Kurang dari Rp4.000.000,00', 0.80],
                ['Kurang dari Rp3.000.000,00', 0.30], ['Kurang dari Rp2.000.000,00', 0.20],
                ['Kurang dari Rp1.000.000,00', 0.10],
            ]],
            ['Kepemilikan kendaraan roda 2', 0.40, [
                ['Lebih dari 2', 1.00], ['Di atas 150 cc', 0.90], ['150 cc', 0.80],
                ['125 cc', 0.40], ['110 cc', 0.30], ['Tidak memiliki', 0.10],
            ]],
            ['Jumlah tanggungan keluarga', 0.40, [
                ['Tidak ada', 1.00], ['1 jiwa', 0.80], ['2 jiwa', 0.50], ['3 jiwa', 0.40],
                ['4 jiwa', 0.30], ['5 jiwa', 0.20], ['Lebih dari 5 jiwa', 0.10],
            ]],
            ['Jenis lantai', 0.40, [
                ['Marmer/granit', 1.00], ['Keramik', 0.80], ['Parket/vinil/permadani', 0.70],
                ['Ubin/tegel/teraso', 0.60], ['Kayu/papan kualitas tinggi', 0.50],
                ['Semen/bata merah', 0.40], ['Bambu', 0.30], ['Kayu/papan kualitas rendah', 0.20], ['Tanah', 0.10],
            ]],
            ['Jenis dinding', 0.30, [
                ['Tembok', 1.00], ['Plesteran anyaman bambu/kawat', 0.80], ['Kayu', 0.50],
                ['Anyaman bambu', 0.40], ['Batang kayu', 0.20], ['Bambu', 0.10],
            ]],
            ['Kondisi dinding', 0.30, [
                ['Bagus/kualitas tinggi', 0.50], ['Jelek/kualitas rendah', 0.10],
            ]],
            ['Jenis atap', 0.30, [
                ['Beton/genteng beton', 1.00], ['Genteng keramik', 0.90], ['Genteng metal/baja ringan', 0.60],
                ['Genteng tanah liat', 0.50], ['Asbes', 0.40], ['Seng', 0.30], ['Sirap', 0.20],
                ['Bambu, jerami/ijuk/daun-daunan/rumbia', 0.10],
            ]],
            ['Kondisi atap', 0.25, [
                ['Bagus/kualitas tinggi', 0.50], ['Jelek/kualitas rendah', 0.10],
            ]],
            ['Sumber air minum', 0.25, [
                ['Air kemasan bermerek', 1.00], ['Air isi ulang', 0.90], ['Ledeng meteran', 0.80],
                ['Ledeng eceran', 0.70], ['Sumur bor/pompa', 0.60], ['Sumur terlindung', 0.50],
                ['Sumur tak terlindung', 0.40], ['Mata air terlindung', 0.30],
                ['Mata air tak terlindung', 0.20], ['Air sungai/danau/waduk/hujan', 0.10],
            ]],
            ['Cara memperoleh air minum', 0.25, [
                ['Membeli eceran', 1.00], ['Tidak membeli', 0.10],
            ]],
            ['Sumber penerangan utama', 0.15, [
                ['Listrik PLN/token', 0.50], ['Listrik PLN non token', 0.30], ['Bukan listrik', 0.10],
            ]],
            ['Daya listrik terpasang', 0.10, [
                ['900 watt s/d 2.200 watt', 1.00], ['Kurang dari 2.200 watt', 0.80],
                ['Kurang dari 900 watt', 0.50], ['450 watt', 0.40], ['Tanpa meteran', 0.10],
            ]],
            ['Bahan bakar/energi utama untuk memasak', 0.10, [
                ['Listrik', 1.00], ['Gas 12 kg', 0.90], ['Minyak tanah', 0.80], ['Gas kota/biogas', 0.70],
                ['Gas 3 kg', 0.50], ['Briket', 0.40], ['Arang', 0.30], ['Kayu bakar', 0.20],
                ['Tidak memasak di rumah', 0.10],
            ]],
            ['Penggunaan fasilitas tempat buang air besar', 0.10, [
                ['Sendiri', 1.00], ['Bersama', 0.40], ['Tidak ada', 0.10],
            ]],
            ['Jenis kloset', 0.10, [
                ['Leher angsa/duduk', 1.00], ['Plengsengan/jongkok', 0.70],
                ['Cemplung/cubluk/bata', 0.40], ['Tidak pakai', 0.10],
            ]],
            ['Tempat pembuangan akhir tinja', 0.10, [
                ['Septictank komunal', 1.00], ['Septictank', 0.60], ['Lubang tanah', 0.50],
                ['Kolam/sawah/sungai/danau', 0.30], ['Tanah/kebun', 0.10],
            ]],
        ];

        foreach ($data as $i => [$nama, $bobot, $opsis]) {
            $param = PbiKriteriaParameter::create([
                'urutan' => $i + 1,
                'nama' => $nama,
                'bobot' => $bobot,
                'indeks' => 1.00,
                'aktif' => true,
            ]);

            foreach ($opsis as $j => [$label, $terintegrasi]) {
                $param->opsis()->create([
                    'label' => $label,
                    'indeks_terintegrasi' => $terintegrasi,
                    'urutan' => $j + 1,
                ]);
            }
        }

        $this->command?->info('18 parameter kriteria berhasil di-seed.');
    }
}
