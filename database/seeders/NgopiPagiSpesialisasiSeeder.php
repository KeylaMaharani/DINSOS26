<?php

namespace Database\Seeders;

use App\Models\NgopiPagiSpesialisasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NgopiPagiSpesialisasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Informasi Bantuan Sosial', 'info', 'sky', 'A', 'Program, bantuan, dan layanan Dinas Sosial'],
            ['Konseling Keluarga', 'family_restroom', 'rose', 'B', 'Konflik dan perselisihan dalam keluarga'],
            ['Pengasuhan Anak', 'child_care', 'amber', 'B', 'Pola asuh dan permasalahan anak'],
            ['Psikososial', 'psychology', 'violet', 'B', 'Tekanan batin, stres, dan masalah sosial'],
            ['Lansia & Disabilitas', 'elderly', 'emerald', 'B', 'Pendampingan lansia dan penyandang disabilitas'],
            ['Kekerasan & Prioritas', 'shield', 'red', 'C', 'Kekerasan, penelantaran, dan kondisi darurat'],
        ];

        foreach ($data as $i => [$nama, $ikon, $warna, $kat, $desk]) {
            NgopiPagiSpesialisasi::updateOrCreate(
                ['slug' => Str::slug($nama)],
                ['nama' => $nama, 'ikon' => $ikon, 'warna' => $warna, 'kategori_awal' => $kat, 'deskripsi' => $desk, 'urutan' => $i + 1, 'aktif' => true]
            );
        }
    }
}
