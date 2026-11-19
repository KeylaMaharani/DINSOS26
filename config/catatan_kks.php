<?php

/**
 * Template catatan proses Kartu KKS, dipakai di halaman Detail Ajuan KKS.
 * Kunci level 1 = status permohonan (tahap yang sedang memegang berkas).
 * Kunci level 2 = aksi: lanjut = meneruskan/menyetujui, simpan = simpan catatan,
 *                 kembalikan = kembalikan ke tahap sebelumnya, tolak = tolak.
 * Isi catatan masih bisa diedit petugas. Ubah kata-kata cukup di file ini.
 */
return [
    // Lurah (TTD digital + surat pengantar)
    'ttd_lurah' => [
        'lanjut' => [
            'Data pemohon sudah diperiksa dan sesuai, surat pengantar ditandatangani dan diteruskan ke Operator Dinsos.',
        ],
        'simpan' => [
            'Berkas sedang diperiksa.',
        ],
        'tolak' => [
            'Pemohon bukan warga kelurahan ini.',
            'Data/berkas tidak sesuai dan tidak dapat diperbaiki.',
        ],
    ],

    // Operator Dinsos (ajuan baru)
    'diajukan' => [
        'lanjut' => [
            'Data dan berkas sudah diperiksa dan lengkap, diteruskan ke Kepala Bidang.',
            'Screenshot DTSEN sudah diunggah dan data sesuai, diteruskan ke Kepala Bidang.',
        ],
        'simpan' => [
            'Berkas masih menunggu kelengkapan dari pemohon/kelurahan.',
        ],
        'tolak' => [
            'Data/berkas tidak sesuai atau tidak lengkap dan tidak dapat diperbaiki.',
            'Pemohon tidak memenuhi persyaratan penggantian Kartu KKS.',
        ],
    ],

    // Kepala Bidang
    'diproses_kabin' => [
        'lanjut' => [
            'Data sudah diperiksa dan sesuai, disetujui dan diteruskan ke Kepala Dinas.',
        ],
        'simpan' => [
            'Berkas sedang diperiksa.',
        ],
        'kembalikan' => [
            'Berkas belum lengkap, mohon dilengkapi Operator.',
            'Data tidak sesuai, mohon diverifikasi ulang.',
        ],
        'tolak' => [
            'Tidak memenuhi persyaratan penggantian Kartu KKS.',
            'Tidak lolos verifikasi data.',
        ],
    ],

    // Kepala Dinas (TTD digital)
    'disetujui_kadis' => [
        'lanjut' => [
            'Disetujui dan ditandatangani secara digital.',
        ],
        'simpan' => [
            'Berkas sedang dipelajari.',
        ],
        'kembalikan' => [
            'Mohon diperiksa ulang oleh Kepala Bidang.',
            'Data tidak sesuai, mohon diverifikasi ulang.',
        ],
        'tolak' => [
            'Tidak memenuhi persyaratan penggantian Kartu KKS.',
        ],
    ],

    // Operator Dinsos (pemberitahuan ke pemohon)
    'validasi_dinsos' => [
        'lanjut' => [
            'Hasil pengajuan telah diberitahukan kepada pemohon.',
        ],
        'simpan' => [
            'Pemohon belum dapat dihubungi, akan dicoba kembali.',
        ],
        'tolak' => [
            'Pemohon tidak dapat dihubungi dan tidak mengambil hasil.',
        ],
    ],
];