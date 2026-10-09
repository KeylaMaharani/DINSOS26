<?php

/**
 * Struktur menu Rehabsos. Dipakai bersama oleh routes, sidebar, dan controller,
 * jadi menambah submenu cukup di satu tempat ini.
 *   module = key di Role::MODULES yang membuka grup tersebut
 */
return [
    'groups' => [
        'ppks' => [
            'label' => 'PPKS',
            'icon' => 'diversity_3',
            'module' => 'rehabsos-ppks',
            'items' => [
                'ajuan-masuk'   => 'Ajuan Masuk',
                'data-evakuasi' => 'Data Evakuasi',
                'validasi'      => 'Validasi',
                'asesmen'       => 'Asesmen',
                'laporan'       => 'Laporan',
            ],
        ],
        'rumah-singgah' => [
            'label' => 'Rumah Singgah',
            'icon' => 'night_shelter',
            'module' => 'rehabsos-rumah-singgah',
            'items' => [
                'penerimaan-klien'   => 'Penerimaan Klien',
                'layanan-management' => 'Layanan Management',
                'rujukan'            => 'Rujukan',
                'laporan'            => 'Laporan',
            ],
        ],
        'permohonan-bantuan' => [
            'label' => 'Data Permohonan Bantuan',
            'icon' => 'request_quote',
            'module' => 'rehabsos-bantuan',
            'items' => [
                'pengajuan-bantuan'        => 'Pengajuan Bantuan',
                'verifikasi-jenis-bantuan' => 'Verifikasi Jenis Bantuan',
                'laporan'                  => 'Laporan',
            ],
        ],
    ],
];
