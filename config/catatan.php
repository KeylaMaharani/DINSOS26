<?php

/**
 * Template catatan petugas (Kelurahan sampai BPJS).
 * Dipakai komponen <x-catatan-template>. Petugas memilih kalimat, isinya masuk ke kolom catatan
 * dan MASIH BISA diedit. Ubah kata-kata cukup di file ini.
 *
 * Kunci aksi: next = meneruskan / verifikasi / aktivasi, back = mengembalikan, reject = menolak.
 * (Kelurahan: next = Verifikasi, back = Kembalikan ke Masyarakat, reject = Tolak.)
 */
return [
    'kelurahan' => [
        'next' => [
            'Data dan berkas sudah diverifikasi di lapangan dan sesuai.',
            'Verifikasi lapangan selesai, data sesuai kondisi sebenarnya.',
        ],
        'back' => [
            'Mohon lengkapi parameter yang belum terisi.',
            'Scan KTP/KK tidak jelas atau tidak sesuai, mohon unggah ulang.',
            'Data tidak sesuai dengan kondisi di lapangan, mohon diperbaiki.',
            'Data anggota keluarga belum lengkap.',
        ],
        'reject' => [
            'Pemohon tidak termasuk kriteria penerima PBI APBD.',
            'Pemohon tidak berdomisili di wilayah kelurahan ini.',
            'Data tidak sesuai dan tidak dapat diperbaiki.',
        ],
    ],

    'lurah' => [
        'next' => [
            'Berkas sudah diperiksa dan ditandatangani, diteruskan ke Operator Dinsos.',
        ],
        'back' => [
            'Data/berkas perlu diperbaiki oleh Kelurahan.',
            'Surat pengantar perlu diperbaiki.',
            'Data tidak sesuai dengan kondisi wilayah.',
        ],
    ],

    'operator' => [
        'next' => [
            'Data sudah diverifikasi dan lengkap, diteruskan ke Kepala Bidang.',
            'Desil Nasional sudah diisi dan data sesuai, diteruskan ke Kepala Bidang.',
        ],
    ],

    'kabid' => [
        'next' => [
            'Data sesuai, disetujui dan diteruskan ke Kepala Dinas.',
        ],
        'back' => [
            'Berkas belum lengkap, mohon dilengkapi Kelurahan.',
            'Data tidak sesuai, mohon diverifikasi ulang.',
        ],
        'reject' => [
            'Tidak memenuhi kriteria PBI APBD.',
            'Tidak lolos verifikasi data.',
        ],
    ],

    'kadis' => [
        'next' => [
            'Disetujui dan diteruskan ke Dinas Kesehatan.',
        ],
        'back' => [
            'Berkas belum lengkap, mohon dilengkapi Kelurahan.',
            'Data tidak sesuai, mohon diverifikasi ulang.',
        ],
        'reject' => [
            'Tidak memenuhi kriteria PBI APBD.',
            'Tidak lolos verifikasi data.',
        ],
    ],

    'dinkes' => [
        'next' => [
            'Data kesehatan dan diagnosa sudah diverifikasi, diteruskan ke BPJS Kesehatan.',
        ],
        'back' => [
            'Diagnosa atau data kesehatan perlu dilengkapi/diperbaiki.',
            'Data tidak sesuai, mohon diverifikasi ulang.',
        ],
        'reject' => [
            'Tidak memenuhi syarat kesehatan untuk PBI APBD.',
            'Data kesehatan tidak sesuai dan tidak dapat diperbaiki.',
        ],
    ],

    'bpjs' => [
        'next' => [
            'Data sesuai, kepesertaan PBI APBD diaktivasi.',
        ],
        'back' => [
            'Data kepesertaan tidak sesuai, mohon diperiksa kembali oleh Dinkes.',
        ],
        'reject' => [
            'Peserta sudah terdaftar pada segmen kepesertaan lain.',
            'Data tidak sesuai dengan data kependudukan.',
        ],
    ],
];