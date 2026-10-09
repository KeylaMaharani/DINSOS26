<?php

/**
 * Menu DAYASOS (kerangka menu saja, tampilan menyusul).
 * Dipakai oleh sidebar (layouts/admin.blade.php) dan routes/web.php.
 *   module = key di Role::MODULES. Anak menu otomatis mengikuti module induknya.
 */
$leaf = fn (string $label, string $path) => [
    'label' => $label,
    'route' => 'dayasos.' . str_replace('/', '.', $path),
    'url'   => 'dayasos/' . $path,
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
            $leaf('Live Chat', 'ngopi-pagi/live-chat'),
            $leaf('Konselor', 'ngopi-pagi/konselor'),
            $leaf('Jadwal', 'ngopi-pagi/jadwal'),
            $leaf('Penjadwalan', 'ngopi-pagi/penjadwalan'),
            $leaf('Masalah', 'ngopi-pagi/masalah'),
            $leaf('Client', 'ngopi-pagi/client'),
            $leaf('Laporan', 'ngopi-pagi/laporan'),
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
                    $leaf('Monitoring', 'lks/pelaporan/monitoring'),
                ],
            ],
            $leaf('Panduan & Format Surat', 'lks/panduan'),
        ],
    ],
];