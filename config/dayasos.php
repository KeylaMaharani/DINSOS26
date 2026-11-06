<?php

/**
 * Menu DAYASOS (dipakai sidebar layouts/admin.blade.php dan routes/web.php).
 *   module = key di Role::MODULES. Anak menu otomatis mengikuti module induknya.
 *   custom = true -> rute TIDAK dibuat oleh loop placeholder di web.php (sudah ada controller sendiri).
 *
 * Ngopi Pagi: submenu mengikuti alur layanan LK3 (lihat NgopiPagiKonsultasi::TAHAP),
 * ditambah Data Konselor dan Spesialisasi.
 * Rute-nya ada di routes/web.php (blok "NGOPI PAGI (admin)").
 */
$leaf = fn (string $label, string $path) => [
    'label' => $label,
    'route' => 'dayasos.' . str_replace('/', '.', $path),
    'url'   => 'dayasos/' . $path,
];

// Submenu Ngopi Pagi. 'badge' = key di $notif sidebar (jumlah kasus yang sedang di tahap itu).
$np = fn (string $label, string $tahap, ?string $badge = null) => [
    'label'  => $label,
    'route'  => 'ngopi-pagi.admin.tahap.' . $tahap,
    'custom' => true,
    'badge'  => $badge,
];

return [
    [
        'label'  => 'Beranda',
        'route'  => 'dayasos.dashboard',
        'url'    => 'dayasos/dashboard',
        'module' => ['dayasos-ngopi-pagi', 'dayasos-kesan', 'dayasos-lks'],
    ],
    [
        'label'    => 'Ngopi Pagi',
        'module'   => 'dayasos-ngopi-pagi',
        'children' => [
            $np('Chat Masuk', 'chat_masuk', 'ngopi_chat_masuk'),
            $np('Screening', 'screening', 'ngopi_screening'),
            $np('Konsultasi LK3', 'konsultasi', 'ngopi_konsultasi'),
            $np('Tindak Lanjut', 'tindak_lanjut', 'ngopi_tindak_lanjut'),
            $np('Monitoring', 'monitoring', 'ngopi_monitoring'),
            $np('Konfirmasi & Penutupan', 'konfirmasi', 'ngopi_konfirmasi'),
            [
                'label'  => 'Rekapitulasi',
                'route'  => 'ngopi-pagi.admin.rekap',
                'match'  => ['ngopi-pagi.admin.kasus', 'ngopi-pagi.admin.rekap.*'],
                'custom' => true,
            ],
            [
                'label'  => 'Data Konselor',
                'route'  => 'ngopi-pagi.admin.konselor.index',
                'match'  => ['ngopi-pagi.admin.konselor.*'],
                'custom' => true,
            ],
            [
                'label'  => 'Spesialisasi',
                'route'  => 'ngopi-pagi.admin.spesialisasi.index',
                'match'  => ['ngopi-pagi.admin.spesialisasi.*'],
                'custom' => true,
            ],
        ],
    ],
    [
        'label'    => 'Kesan',
        'module'   => 'dayasos-kesan',
        'children' => [
            [
                'label'    => 'Data Warga',
                'children' => [
                    $leaf('Daftar Warga', 'kesan/data-warga/daftar-warga'),
                    $leaf('Riwayat Bantuan Warga', 'kesan/data-warga/riwayat-bantuan-warga'),
                    $leaf('Validasi', 'kesan/data-warga/validasi'),
                ],
            ],
            [
                'label'    => 'Pengelolaan Bantuan',
                'children' => [
                    $leaf('Jenis Bantuan', 'kesan/pengelolaan-bantuan/jenis-bantuan'),
                    $leaf('Pengajuan Penerima', 'kesan/pengelolaan-bantuan/pengajuan-penerima'),
                ],
            ],
            [
                'label'    => 'Filantropi',
                'children' => [
                    $leaf('Salurkan Bantuan', 'kesan/filantropi/salurkan-bantuan'),
                    $leaf('Tracking Penyaluran', 'kesan/filantropi/tracking-penyaluran'),
                    $leaf('Laporan Penyaluran Bantuan', 'kesan/filantropi/laporan-penyaluran-bantuan'),
                ],
            ],
        ],
    ],
    [
        'label'    => 'LKS',
        'module'   => 'dayasos-lks',
        'children' => [
            [
                'label'    => 'Pendaftaran',
                'children' => [
                    $leaf('Ajuan', 'lks/pendaftaran/ajuan'),
                    $leaf('Verifikasi Berkas', 'lks/pendaftaran/verifikasi-berkas'),
                    $leaf('Penerbitan Tanda Terdaftar', 'lks/pendaftaran/penerbitan'),
                    $leaf('Arsip', 'lks/pendaftaran/arsip'),
                ],
            ],
            [
                'label'    => 'Data LKS',
                'children' => [
                    $leaf('Daftar LKS', 'lks/data-lks/daftar-lks'),
                    $leaf('Perpanjangan Legalitas', 'lks/data-lks/perpanjangan'),
                ],
            ],
            [
                'label'    => 'Pelaporan',
                'children' => [
                    $leaf('Laporan Kegiatan LKS', 'lks/pelaporan/laporan-kegiatan'),
                    $leaf('Pemantauan', 'lks/pelaporan/monitoring'),
                ],
            ],
            $leaf('Panduan & Format Surat', 'lks/panduan'),
        ],
    ],
];
