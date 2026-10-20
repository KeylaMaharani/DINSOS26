<?php

/**
 * Struktur menu Rehabsos. Dipakai bersama oleh routes, sidebar, dan controller,
 * jadi menambah submenu cukup di satu tempat ini.
 *   module = key di Role::MODULES yang membuka grup tersebut
 *
 * Catatan penggabungan fitur:
 *   - PPKS            : Ajuan Masuk + Validasi            -> 'Ajuan & Validasi'
 *   - Rumah Singgah   : Penerimaan Klien                  -> 'Klien' (daftar + masalah klien)
 *   - Permohonan      : Pengajuan + Verifikasi Jenis      -> 'Pengajuan & Verifikasi'
 */
return [
    'groups' => [
        'ppks' => [
            'label' => 'PPKS',
            'icon' => 'diversity_3',
            'module' => 'rehabsos-ppks',
            'items' => [
                'ajuan-validasi' => 'Ajuan & Validasi',
                'data-evakuasi'  => 'Data Evakuasi',
                'asesmen'        => 'Asesmen',
                'laporan'        => 'Laporan',
            ],
        ],
        'rumah-singgah' => [
            'label' => 'Rumah Singgah',
            'icon' => 'night_shelter',
            'module' => 'rehabsos-rumah-singgah',
            'items' => [
                'klien'             => 'Klien',
                'manajemen-layanan' => 'Manajemen Layanan',
                'rujukan'           => 'Rujukan',
                'laporan'           => 'Laporan',
            ],
        ],
        'permohonan-bantuan' => [
            'label' => 'Data Permohonan Bantuan',
            'icon' => 'request_quote',
            'module' => 'rehabsos-bantuan',
            'items' => [
                'pengajuan-verifikasi' => 'Pengajuan & Verifikasi',
                'laporan'              => 'Laporan',
            ],
        ],
    ],
];
