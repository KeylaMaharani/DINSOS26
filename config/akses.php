<?php

/**
 * KATALOG HAK AKSES DETAIL:  modul -> submenu -> aksi
 *
 * Kunci yang tersimpan di roles.permissions:
 *   modul                (otomatis ditambahkan bila ada submenu yang dicentang)
 *   modul.submenu        = BISA LIHAT (boleh membuka halaman)
 *   modul.submenu.aksi   = tambah | ubah | hapus | proses | unduh (export Excel) | pesan
 *
 * Aksi baru bisa dicentang setelah submenunya dicentang (lihat Role::normalisasi()).
 * Key lama tidak diubah, jadi hak akses yang sudah tersimpan tetap berlaku.
 *
 * PBI APBD: semua role (Kelurahan, Lurah, Operator, Kabid, Kadis, Dinkes, BPJS) memakai
 * satu menu "Ajuan" (pbi-apbd.ajuan). Tampilan kerjanya ditentukan dari slug role.
 */
$aksi = [
    'tambah' => 'Bisa tambah data',
    'ubah'   => 'Bisa edit',
    'hapus'  => 'Bisa hapus',
    'proses' => 'Bisa memproses (teruskan / kembalikan / tolak)',
    'unduh'  => 'Bisa export Excel / unduh',
];
$pilih = fn (string ...$k) => array_intersect_key($aksi, array_flip($k));

// Rehabsos dibaca otomatis dari config/rehabsos.php (config() belum siap saat file ini dimuat)
$rehabsos = [];
$rehabCfg = require base_path('config/rehabsos.php');
foreach ($rehabCfg['groups'] as $g) {
    foreach ($g['items'] as $ik => $label) {
        $rehabsos[$g['module']][$ik] = [
            'label' => $label,
            'aksi'  => $ik === 'laporan' ? $pilih('unduh') : $pilih('tambah', 'ubah', 'proses', 'unduh'),
        ];
    }
}
$rehabsos['rehabsos-ground-check']['daftar'] = [
    'label' => 'Ground Check 2026',
    'aksi'  => $pilih('tambah', 'ubah', 'unduh'),
];

$katalog = [
    // ---------- PFM ----------
    'pbi-apbd' => [
        'ajuan' => ['label' => 'Ajuan', 'aksi' => [
            'tambah' => 'Bisa tambah diagnosa (Dinkes)',
            'ubah'   => 'Bisa edit data tambahan / isi Desil Nasional (Operator)',
            'proses' => $aksi['proses'],
            'unduh'  => $aksi['unduh'],
        ]],
        'arsip'      => ['label' => 'Arsip', 'aksi' => $pilih('unduh')],
        'monitoring' => ['label' => 'Monitoring', 'aksi' => $pilih('unduh')],
        'log'        => ['label' => 'Log', 'aksi' => $pilih('unduh')],
    ],
    'kartu-kks' => [
        'ajuan'      => ['label' => 'Ajuan', 'aksi' => $pilih('tambah', 'ubah', 'proses', 'unduh')],
        'arsip'      => ['label' => 'Arsip', 'aksi' => $pilih('unduh')],
        'monitoring' => ['label' => 'Monitoring', 'aksi' => $pilih('unduh')],
        'log'        => ['label' => 'Log', 'aksi' => $pilih('unduh')],
    ],
    'dtsen' => [
        'ajuan'      => ['label' => 'Ajuan', 'aksi' => $pilih('ubah', 'proses', 'unduh')],
        'arsip'      => ['label' => 'Arsip', 'aksi' => $pilih('unduh')],
        'monitoring' => ['label' => 'Monitoring', 'aksi' => $pilih('unduh')],
    ],
    'ground-check' => [
        'daftar' => ['label' => 'Daftar Sasaran', 'aksi' => [
            'tambah' => 'Bisa tambah sasaran', 'ubah' => 'Bisa isi form & edit status',
            'unduh' => $aksi['unduh'],
        ]],
    ],

    // ---------- Layanan Kelurahan ----------
    'layanan-kartu-kks' => [
        'ajukan'  => ['label' => 'Ajukan', 'aksi' => ['tambah' => 'Bisa kirim pengajuan']],
        'riwayat' => ['label' => 'Riwayat', 'aksi' => $pilih('unduh')],
    ],
    'layanan-dtsen' => [
        'ajukan'  => ['label' => 'Ajukan', 'aksi' => ['tambah' => 'Bisa kirim pengajuan']],
        'riwayat' => ['label' => 'Riwayat', 'aksi' => $pilih('unduh')],
    ],

    // ---------- Dayasos: Ngopi Pagi ----------
    'dayasos-ngopi-pagi' => [
        'chat_masuk'    => ['label' => 'Chat Masuk', 'aksi' => $pilih('unduh')],
        'screening'     => ['label' => 'Screening', 'aksi' => $pilih('unduh')],
        'konsultasi'    => ['label' => 'Konsultasi LK3', 'aksi' => $pilih('unduh')],
        'tindak_lanjut' => ['label' => 'Tindak Lanjut', 'aksi' => $pilih('unduh')],
        'monitoring'    => ['label' => 'Monitoring', 'aksi' => $pilih('unduh')],
        'konfirmasi'    => ['label' => 'Konfirmasi & Penutupan', 'aksi' => $pilih('unduh')],
        'kasus'         => ['label' => 'Detail Kasus', 'aksi' => ['proses' => 'Bisa proses kasus', 'pesan' => 'Bisa kirim pesan chat']],
        'rekap'         => ['label' => 'Rekapitulasi', 'aksi' => $pilih('unduh')],
        'konselor'      => ['label' => 'Data Konselor', 'aksi' => $pilih('tambah', 'ubah', 'hapus', 'unduh')],
        'spesialisasi'  => ['label' => 'Spesialisasi', 'aksi' => $pilih('tambah', 'ubah', 'hapus', 'unduh')],
    ],

    // ---------- Dayasos: Kesan ----------
    'dayasos-kesan' => [
        'data-warga'          => ['label' => 'Data Warga', 'aksi' => $pilih('tambah', 'ubah', 'proses', 'unduh')],
        'pengelolaan-bantuan' => ['label' => 'Pengelolaan Bantuan', 'aksi' => $pilih('tambah', 'ubah', 'hapus', 'proses', 'unduh')],
        'filantropi'          => ['label' => 'Filantropi', 'aksi' => $pilih('tambah', 'ubah', 'proses', 'unduh')],
    ],

    // ---------- Dayasos: LKS ----------
    'dayasos-lks' => [
        'pendaftaran' => ['label' => 'Pendaftaran', 'aksi' => $pilih('tambah', 'ubah', 'proses', 'unduh')],
        'data-lks'    => ['label' => 'Data LKS', 'aksi' => $pilih('tambah', 'ubah', 'hapus', 'unduh')],
        'pelaporan'   => ['label' => 'Pelaporan', 'aksi' => $pilih('tambah', 'ubah', 'unduh')],
        'panduan'     => ['label' => 'Panduan & Format Surat', 'aksi' => $pilih('unduh')],
    ],

    // ---------- Perlinsos: Sigap Bencana (Dinsos) ----------
    'sigap-bencana' => [
        'laporan' => ['label' => 'Laporan Bantuan Bencana', 'aksi' => $pilih('tambah', 'ubah', 'proses', 'unduh')],
        'stok'    => ['label' => 'Stock Barang', 'aksi' => [
            'tambah' => 'Bisa tambah barang & stok masuk', 'ubah' => $aksi['ubah'], 'unduh' => $aksi['unduh'],
        ]],
        'rekap'   => ['label' => 'Laporan (Rekap)', 'aksi' => $pilih('unduh')],
    ],

    // ---------- Perlinsos: Kelurahan / OPD wilayah ----------
    'sigap-proses' => [
        'proses' => ['label' => 'Proses Bantuan Kebencanaan', 'aksi' => [
            'tambah' => 'Bisa buat laporan', 'ubah' => 'Bisa edit lokasi', 'proses' => $aksi['proses'],
            'unduh' => $aksi['unduh'],
        ]],
    ],

    // ---------- KESAN (Portal / Filantropi) ----------
    'kesan' => [
        'pilihan'    => ['label' => 'Verifikasi Pilihan', 'aksi' => [
            'proses' => 'Bisa verifikasi / tolak / selesaikan penyaluran', 'unduh' => $aksi['unduh'],
        ]],
        'penerima'   => ['label' => 'Calon Penerima', 'aksi' => $pilih('tambah', 'ubah', 'hapus', 'unduh')],
        'kategori'   => ['label' => 'Kategori Bantuan', 'aksi' => $pilih('tambah', 'ubah', 'hapus')],
        'filantropi' => ['label' => 'Data Filantropi', 'aksi' => $pilih('tambah', 'ubah', 'hapus', 'unduh')],
    ],

    // ---------- Dokumen ----------
    'dokumen' => [
        'daftar' => ['label' => 'Daftar Template', 'aksi' => $pilih('unduh')],
    ],
];

// Rehabsos (PPKS, Rumah Singgah, Permohonan Bantuan, Ground Check) ditaruh paling akhir
return array_merge($katalog, $rehabsos);