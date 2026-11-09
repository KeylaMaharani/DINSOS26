<?php

namespace Database\Seeders;

use App\Models\KesanKategori;
use App\Models\Role;
use Illuminate\Database\Seeder;

class KesanSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            ['rtlh', 'Rumah Tidak Layak Huni (RTLH)', 'RTLH', 'home', 'orange', 1],
            ['anak-yatim-piatu', 'Anak Yatim/Piatu', 'Anak Yatim/Piatu', 'child_care', 'rose', 2],
            ['dhuafa', 'Dhuafa', 'Dhuafa', 'handshake', 'green', 3],
            ['biaya-pendidikan', 'Biaya Pendidikan', 'Biaya Pendidikan', 'school', 'blue', 4],
            ['bantuan-pangan', 'Bantuan Pangan', 'Bantuan Pangan', 'shopping_basket', 'purple', 5],
        ];
        foreach ($kategori as [$kode, $nama, $singkat, $ikon, $warna, $urut]) {
            KesanKategori::updateOrCreate(['kode' => $kode], [
                'nama' => $nama, 'singkatan' => $singkat, 'ikon' => $ikon, 'warna' => $warna, 'urutan' => $urut, 'aktif' => true,
            ]);
        }

        // Role untuk akun Filantropi (hak akses halaman Filantropi dicek lewat middleware role:filantropi,
        // jadi tidak perlu centang modul apa pun di Kelola Akses).
        Role::firstOrCreate(['slug' => 'filantropi'], ['name' => 'Filantropi']);
    }
}
