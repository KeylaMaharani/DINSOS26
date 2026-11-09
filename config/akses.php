<?php

/**
 * Katalog hak akses DETAIL: modul -> submenu -> aksi.
 * Hanya modul yang halamannya sudah ada. Modul yang belum ada di sini (Portal, Kesan, LKS,
 * Rehabsos, Asesmen SPMB, Kedaruratan Medis, Proses Bantuan, Beranda) tetap satu centang per modul.
 *
 * Kunci yang disimpan di roles.permissions (semuanya string biasa):
 *   modul               mis. pbi-apbd                  (otomatis, bila ada submenu yang dicentang)
 *   modul.submenu       mis. pbi-apbd.ajuan            = boleh MEMBUKA halaman (lihat)
 *   modul.submenu.aksi  mis. pbi-apbd.ajuan.proses     = boleh melakukan aksi itu
 * Aksi hanya sah bila submenu-nya dicentang (dinormalkan di Role::normalisasi()).
 *
 * Menambah submenu/aksi baru: cukup tambah di sini, lalu pasang di route:
 *   ->middleware('module:modul.submenu.aksi')
 * Catatan: role lama TIDAK otomatis dapat submenu/aksi baru; centang di Kelola Akses.
 */
$aksi = [
    'tambah' => 'Tambah',
    'ubah'   => 'Ubah',
    'hapus'  => 'Hapus',
    'proses' => 'Proses (teruskan / kembalikan / tolak)',
    'unduh'  => 'Unduh / ekspor',
];
$pilih = fn (string ...$k) => array_intersect_key($aksi, array_flip($k));

return [
    // ---------- PFM ----------
    'pbi-apbd' => [
        'ajuan' => ['label' => 'Ajuan', 'aksi' => [
            'ubah' => 'Ubah data tambahan', 'tambah' => 'Tambah diagnosa', 'proses' => $aksi['proses'],
        ]],
        'arsip'      => ['label' => 'Arsip'],
        'monitoring' => ['label' => 'Monitoring'],
        'log'        => ['label' => 'Log'],
    ],
    'kartu-kks' => [
        'ajuan'      => ['label' => 'Ajuan', 'aksi' => $pilih('ubah', 'proses')],
        'arsip'      => ['label' => 'Arsip'],
        'monitoring' => ['label' => 'Monitoring'],
        'log'        => ['label' => 'Log'],
    ],
    'dtsen' => [
        'ajuan'      => ['label' => 'Ajuan', 'aksi' => $pilih('ubah', 'proses')],
        'arsip'      => ['label' => 'Arsip'],
        'monitoring' => ['label' => 'Monitoring'],
    ],
    'ground-check' => [
        'daftar' => ['label' => 'Daftar Sasaran', 'aksi' => [
            'tambah' => 'Tambah sasaran', 'ubah' => 'Isi form & ubah status',
        ]],
    ],

    // ---------- Approval PBI APBD (satu halaman per tahap) ----------
    'pbi-lurah' => [
        'daftar' => ['label' => 'Daftar & Detail', 'aksi' => ['proses' => 'Tanda tangan & teruskan / kembalikan']],
    ],
    'pbi-operator' => [
        'daftar' => ['label' => 'Daftar & Detail', 'aksi' => ['ubah' => 'Isi Desil Nasional', 'proses' => 'Verifikasi & teruskan']],
    ],
    'pbi-kabid' => [
        'daftar' => ['label' => 'Daftar & Detail', 'aksi' => ['proses' => $aksi['proses']]],
    ],
    'pbi-kadis' => [
        'daftar' => ['label' => 'Daftar & Detail', 'aksi' => ['proses' => $aksi['proses']]],
    ],
    'pbi-dinkes' => [
        'daftar' => ['label' => 'Daftar & Detail', 'aksi' => ['ubah' => 'Ubah diagnosa', 'proses' => $aksi['proses']]],
    ],
    'pbi-bpjs' => [
        'daftar' => ['label' => 'Daftar & Detail', 'aksi' => ['proses' => 'Aktivasi / kembalikan / tolak']],
    ],
    'pbi-kelurahan' => [
        'daftar' => ['label' => 'Daftar & Detail', 'aksi' => ['proses' => 'Simpan, verifikasi, kembalikan, tolak']],
    ],

    // ---------- Layanan Kelurahan ----------
    'layanan-kartu-kks' => [
        'ajukan'  => ['label' => 'Ajukan', 'aksi' => ['tambah' => 'Kirim pengajuan']],
        'riwayat' => ['label' => 'Riwayat'],
    ],
    'layanan-dtsen' => [
        'ajukan'  => ['label' => 'Ajukan', 'aksi' => ['tambah' => 'Kirim pengajuan']],
        'riwayat' => ['label' => 'Riwayat'],
    ],

    // ---------- Dayasos: Ngopi Pagi ----------
    'dayasos-ngopi-pagi' => [
        'chat_masuk'    => ['label' => 'Chat Masuk'],
        'screening'     => ['label' => 'Screening'],
        'konsultasi'    => ['label' => 'Konsultasi LK3'],
        'tindak_lanjut' => ['label' => 'Tindak Lanjut'],
        'monitoring'    => ['label' => 'Monitoring'],
        'konfirmasi'    => ['label' => 'Konfirmasi & Penutupan'],
        'kasus'         => ['label' => 'Detail Kasus', 'aksi' => ['proses' => 'Proses kasus', 'pesan' => 'Kirim pesan chat']],
        'rekap'         => ['label' => 'Rekapitulasi', 'aksi' => $pilih('unduh')],
        'konselor'      => ['label' => 'Data Konselor', 'aksi' => $pilih('tambah', 'ubah', 'hapus')],
        'spesialisasi'  => ['label' => 'Spesialisasi', 'aksi' => $pilih('tambah', 'ubah', 'hapus')],
    ],

    // ---------- Perlinsos ----------
    // (Detail laporan & proses laporan dipakai bersama Kelurahan/OPD, jadi tetap di level modul.)
    'sigap-bencana' => [
        'laporan' => ['label' => 'Laporan Bantuan Bencana'],
        'stok'    => ['label' => 'Stock Barang', 'aksi' => ['tambah' => 'Tambah barang & stok masuk']],
    ],

    // ---------- Dokumen ----------
    'dokumen' => [
        'daftar' => ['label' => 'Daftar Template', 'aksi' => $pilih('unduh')],
    ],
];
