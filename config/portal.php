<?php

/**
 * Menu PORTAL (dulu bernama "Front End" / "Website"). Kerangka menu saja,
 * halaman menyusul.
 *
 * Di sidebar admin semua menu ini dibungkus satu folder bernama "Portal"
 * (lihat resources/views/layouts/admin.blade.php) dan didaftarkan di
 * routes/web.php. Tiap menu paling atas mengikuti module 'menu-frontend'
 * (key di Role::MODULES, labelnya "Portal - Kelola Menu Website"), anak menu
 * otomatis mengikuti induknya. Key module 'menu-frontend' sengaja TIDAK diganti
 * supaya data hak akses yang sudah tersimpan tetap berlaku. Nama route-nya
 * 'portal.*' dan URL-nya '/portal/...'.
 *
 * Struktur ini HARUS sama dengan navbar website depan
 * (resources/views/layouts/app.blade.php -> variabel $pelayananBaru):
 *
 *   Beranda
 *   Pelayanan
 *     ├─ PBI               : Informasi BPJS PBI, SK PBI APBD, Penonaktifan Kartu BPJS
 *     ├─ Bantuan Sosial    : Cek Desil, BLT BBM, PKH dan BPNT, E-Warong
 *     └─ Data dan Kunjungan: DTSEN, PPKS & PSKS, LKS, Data Suplier, Permohonan Kunjungan Dinas
 *   Berita
 *   Regulasi
 *   PPID
 *   Kontak
 *
 * Catatan: 'Beranda Portal' = pengaturan isi homepage website,
 *          beda dengan 'Beranda' paling atas (dashboard monitoring admin).
 *
 * Kedalaman menu bebas (sidebar & routes dibaca rekursif), jadi grup
 * seperti PBI / Bantuan Sosial cukup ditulis sebagai node ber-'children'.
 */
$leaf = fn (string $label, string $path) => [
    'label' => $label,
    'route' => 'portal.' . str_replace('/', '.', $path),
    'url'   => 'portal/' . $path,
];

// Menu paling atas butuh ikon + module (anak menu tidak perlu)
$top = fn (array $node, string $icon) => $node + [
    'icon'   => $icon,
    'module' => 'menu-frontend',
];

return [
    // Beranda
    $top($leaf('Beranda Portal', 'beranda'), 'language'),

    // Pelayanan (dropdown bertingkat: grup -> item)
    $top([
        'label'    => 'Pelayanan',
        'children' => [
            [
                'label'    => 'PBI',
                'children' => [
                    $leaf('Informasi BPJS PBI', 'pelayanan/pbi/informasi-bpjs-pbi'),
                    $leaf('SK PBI APBD', 'pelayanan/pbi/sk-pbi-apbd'),
                    $leaf('Penonaktifan Kartu BPJS', 'pelayanan/pbi/penonaktifan-kartu-bpjs'),
                ],
            ],
            [
                'label'    => 'Bantuan Sosial',
                'children' => [
                    $leaf('Cek Desil', 'pelayanan/bantuan-sosial/cek-desil'),
                    $leaf('BLT BBM', 'pelayanan/bantuan-sosial/blt-bbm'),
                    $leaf('PKH dan BPNT', 'pelayanan/bantuan-sosial/pkh-dan-bpnt'),
                    $leaf('E-Warong', 'pelayanan/bantuan-sosial/e-warong'),
                ],
            ],
            [
                'label'    => 'Data dan Kunjungan',
                'children' => [
                    $leaf('DTSEN', 'pelayanan/data-dan-kunjungan/dtsen'),
                    $leaf('PPKS & PSKS', 'pelayanan/data-dan-kunjungan/ppks-psks'),
                    $leaf('LKS', 'pelayanan/data-dan-kunjungan/lks'),
                    $leaf('Data Suplier', 'pelayanan/data-dan-kunjungan/data-suplier'),
                    $leaf('Permohonan Kunjungan Dinas', 'pelayanan/data-dan-kunjungan/permohonan-kunjungan-dinas'),
                ],
            ],
        ],
    ], 'support_agent'),

    // Menu tunggal lainnya
    $top($leaf('Berita', 'berita'), 'newspaper'),
    $top($leaf('Regulasi', 'regulasi'), 'gavel'),
    $top($leaf('PPID', 'ppid'), 'policy'),
    $top($leaf('Kontak', 'kontak'), 'contact_phone'),
];