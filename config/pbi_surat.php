<?php

/**
 * Data tetap untuk pengisian placeholder surat PBI APBD (lihat App\Support\PbiSuratIsian).
 *
 * Data penandatangan (Kepala Dinas / Lurah: nama lengkap, pangkat, NIP, jabatan) TIDAK di sini:
 * diisi di akun user lewat menu Kelola User & Role.
 */
return [
    // Alamat kantor kelurahan untuk ${alamat_kelurahan}. Kunci = NAMA KELURAHAN HURUF KAPITAL.
    // Contoh: 'PABATON' => 'Jl. Contoh No. 1, Bogor Tengah',
    'alamat_kelurahan' => [
    ],
];