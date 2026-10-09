<?php

/**
 * Menu FRONTEND / WEBSITE (kerangka menu saja, halaman menyusul).
 * Ditampilkan langsung di sidebar admin (di bawah Beranda) dan didaftarkan
 * di routes/web.php. Tiap menu paling atas mengikuti module 'menu-frontend'
 * (key di Role::MODULES), anak menu otomatis mengikuti induknya.
 *
 * Catatan: 'Beranda Website' = pengaturan isi homepage website,
 *          beda dengan 'Beranda' paling atas (dashboard monitoring admin).
 */
$leaf = fn (string $label, string $path) => [
    'label' => $label,
    'route' => 'frontend.' . str_replace('/', '.', $path),
    'url'   => 'frontend/' . $path,
];

// Menu paling atas butuh ikon + module (anak menu tidak perlu)
$top = fn (array $node, string $icon) => $node + [
    'icon'   => $icon,
    'module' => 'menu-frontend',
];

return [
    $top($leaf('Beranda Website', 'beranda'), 'language'),
    $top([
        'label'    => 'Pelayanan',
        'children' => [
            $leaf('Pendaftaran BPJS PBI APBD', 'pelayanan/pendaftaran-bpjs-pbi-apbd'),
            $leaf('Tutorial Pendaftaran BPJS PBI', 'pelayanan/tutorial-pendaftaran-bpjs-pbi'),
            $leaf('Informasi BPJS Kesehatan', 'pelayanan/informasi-bpjs-kesehatan'),
            $leaf('Permohonan Kunjungan Dinas', 'pelayanan/permohonan-kunjungan-dinas'),
        ],
    ], 'support_agent'),
    $top([
        'label'    => 'Informasi',
        'children' => [
            $leaf('Cek Desil', 'informasi/cek-desil'),
            $leaf('DTSEN', 'informasi/dtsen'),
            $leaf('PKKS Dan PSKS', 'informasi/pkks-dan-psks'),
            $leaf('LKS', 'informasi/lks'),
            $leaf('Penonaktifkan Kartu BPJS', 'informasi/penonaktifkan-kartu-bpjs'),
            $leaf('E-Warong', 'informasi/e-warong'),
            $leaf('Data Suplier', 'informasi/data-suplier'),
            $leaf('SK PBI APBD', 'informasi/sk-pbi-apbd'),
        ],
    ], 'info'),
    $top($leaf('Berita', 'berita'), 'newspaper'),
    $top($leaf('Galeri', 'galeri'), 'photo_library'),
    $top($leaf('Hubungi Kami', 'hubungi-kami'), 'contact_phone'),
    $top($leaf('Bahan Paparan', 'bahan-paparan'), 'slideshow'),
    $top($leaf('Regulasi', 'regulasi'), 'gavel'),
    $top([
        'label'    => 'Cek Bansos',
        'children' => [
            $leaf('BLT BBM', 'cek-bansos/blt-bbm'),
            $leaf('PKH Dan BPNT', 'cek-bansos/pkh-dan-bpnt'),
        ],
    ], 'manage_search'),
];
