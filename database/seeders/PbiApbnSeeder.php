<?php

namespace Database\Seeders;

use App\Models\PbiApbn;
use App\Models\PbiApbnAnggotaKeluarga;
use App\Models\PbiApbnLog;
use Illuminate\Database\Seeder;

class PbiApbnSeeder extends Seeder
{
    public function run(): void
    {
        $contoh = [
            [
                'nama_kepala_keluarga' => 'Asep Suryana',
                'nik_kepala_keluarga' => '3271010101800001',
                'alamat' => 'Jl. Kebon Kembang No. 12',
                'kecamatan' => 'Bogor Tengah',
                'desa_kelurahan' => 'Paledang',
                'latitude' => -6.5950,
                'longitude' => 106.7900,
                'status' => 'operator_dinsos',
                'desil_nasional' => 'Desil 1',
            ],
            [
                'nama_kepala_keluarga' => 'Euis Nuraeni',
                'nik_kepala_keluarga' => '3271020202850002',
                'alamat' => 'Jl. Pahlawan No. 45',
                'kecamatan' => 'Bogor Selatan',
                'desa_kelurahan' => 'Batutulis',
                'latitude' => -6.6230,
                'longitude' => 106.8000,
                'status' => 'kabid',
                'desil_nasional' => 'Desil 2',
            ],
            [
                'nama_kepala_keluarga' => 'Dedi Hidayat',
                'nik_kepala_keluarga' => '3271030303750003',
                'alamat' => 'Jl. Raya Tajur No. 8',
                'kecamatan' => 'Bogor Timur',
                'desa_kelurahan' => 'Tajur',
                'latitude' => -6.6180,
                'longitude' => 106.8150,
                'status' => 'kadis',
                'desil_nasional' => 'Desil 1',
            ],
            [
                'nama_kepala_keluarga' => 'Siti Aminah',
                'nik_kepala_keluarga' => '3271040404900004',
                'alamat' => 'Jl. Sholeh Iskandar No. 3',
                'kecamatan' => 'Tanah Sareal',
                'desa_kelurahan' => 'Kedung Waringin',
                'latitude' => -6.5680,
                'longitude' => 106.7750,
                'status' => 'disetujui',
                'desil_nasional' => 'Desil 3',
            ],
            [
                'nama_kepala_keluarga' => 'Wawan Setiawan',
                'nik_kepala_keluarga' => '3271050505880005',
                'alamat' => 'Jl. Dreded No. 21',
                'kecamatan' => 'Bogor Barat',
                'desa_kelurahan' => 'Menteng',
                'latitude' => -6.5900,
                'longitude' => 106.7650,
                'status' => 'ditolak',
                'desil_nasional' => 'Desil 4',
                'catatan_internal' => 'Data ganda dengan pengajuan lain.',
            ],
        ];

        foreach ($contoh as $i => $row) {
            $noRegistrasi = 'PBI-APBN-2026-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            $pbi = PbiApbn::updateOrCreate(
                ['no_registrasi' => $noRegistrasi],
                array_merge([
                    'no_kk' => '327101' . str_pad($i + 1, 10, '0', STR_PAD_LEFT),
                    'provinsi' => 'Jawa Barat',
                    'kabupaten_kota' => 'Kota Bogor',
                    'rt' => '001',
                    'rw' => '002',
                    'no_telp' => '0812345678' . $i,
                    'sudah_diverifikasi_kelurahan' => true,
                    'status_penguasaan_bangunan' => 'Milik Sendiri',
                    'status_lahan' => 'Milik Sendiri',
                    'penghasilan_rata_rata' => 'Rp 1.000.000 - Rp 2.000.000',
                    'punya_kendaraan_roda_2' => false,
                    'jumlah_tanggungan_keluarga' => 3,
                    'jenis_lantai' => 'Keramik',
                    'jenis_dinding' => 'Tembok',
                    'kondisi_dinding' => 'Baik',
                    'jenis_atap' => 'Genteng',
                    'kondisi_atap' => 'Baik',
                    'sumber_air_minum' => 'PDAM',
                    'cara_memperoleh_air_minum' => 'Membeli',
                    'sumber_penerangan_utama' => 'Listrik PLN',
                    'daya_terpasang' => '900 VA',
                    'bahan_bakar_energi_memasak' => 'Gas/LPG',
                    'penggunaan_fasilitas_bab' => 'Sendiri',
                    'jenis_kloset' => 'Leher Angsa',
                    'tempat_pembuangan_akhir_tinja' => 'Tangki Septik',
                    'kesimpulan_rekomendasi' => 'Layak diberikan bantuan PBI APBN.',
                    'nama_faskes' => 'Puskesmas Terdekat',
                    'diagnosa' => 'Ibu hamil /gapias gravid 29-30 m664,presboking +4T+anensia',
                    
                    // PAKSA KOSONGKAN LAMPIRAN AGAR MUNCUL ICON SILANG
                    'screenshot_dtsen' => null,
                    'surat_rawat_inap' => null,
                    'scan_ktp' => null,
                    'scan_kk' => null,
                    'foto_rumah' => null,
                    'foto_kamar_mandi' => null,
                    'foto_selfie_ktp' => null,
                    'screenshot_pembaharuan_desil' => null,
                ], $row)
            );

            PbiApbnAnggotaKeluarga::updateOrCreate(
                ['pbi_apbn_id' => $pbi->id, 'nik' => $row['nik_kepala_keluarga']],
                [
                    'nama' => $row['nama_kepala_keluarga'],
                    'tempat_lahir' => 'Bogor',
                    'tanggal_lahir' => '1985-01-01',
                    'pekerjaan' => 'Wiraswasta',
                    'agama' => 'Islam',
                    'jenis_kelamin' => 'L',
                    'hubungan_keluarga' => 'Kepala Keluarga',
                    'keanggotaan_bpjs' => 'Belum Terdaftar',
                ]
            );

            $kesimpulan = $row['catatan_internal'] ?? 'Layak diberikan bantuan PBI APBN.';

            $riwayat = [
                [
                    'username' => 'Sistem Kelurahan',
                    'role_name' => 'kelurahan',
                    'task_name' => 'Verifikasi kelurahan selesai, diteruskan ke Dinsos',
                    'catatan' => "Verifikasi kelengkapan berkas dan data rumah tangga telah selesai dilakukan oleh petugas Kelurahan. Kesimpulan: {$kesimpulan}",
                ],
            ];

            $sudahLewatOperator = in_array($row['status'], ['kabid', 'kadis', 'disetujui', 'ditolak']);
            $sudahLewatKabid = in_array($row['status'], ['kadis', 'disetujui']);
            $sudahLewatKadis = $row['status'] === 'disetujui';

            if ($row['status'] === 'ditolak') {
                $riwayat[] = [
                    'username' => 'Operator Dayasos',
                    'role_name' => PbiApbn::OPERATOR_ROLE ?? 'operator',
                    'task_name' => 'Menolak permohonan',
                    'catatan' => $kesimpulan,
                ];
            } elseif ($sudahLewatOperator) {
                $riwayat[] = [
                    'username' => 'Operator Dayasos',
                    'role_name' => PbiApbn::OPERATOR_ROLE ?? 'operator',
                    'task_name' => 'Meneruskan ke Kepala Bidang',
                    'catatan' => 'Berkas lengkap, memenuhi syarat administrasi. Diteruskan untuk persetujuan Kepala Bidang.',
                ];

                if ($sudahLewatKabid) {
                    $riwayat[] = [
                        'username' => 'Kepala Bidang',
                        'role_name' => 'kabin',
                        'task_name' => 'Meneruskan ke Kepala Dinas',
                        'catatan' => 'Disetujui di tingkat Kepala Bidang, diteruskan untuk persetujuan final Kepala Dinas.',
                    ];

                    if ($sudahLewatKadis) {
                        $riwayat[] = [
                            'username' => 'Kepala Dinas',
                            'role_name' => 'kadis',
                            'task_name' => 'Menyetujui permohonan',
                            'catatan' => 'Permohonan disetujui dan berhak menerima bantuan PBI APBN.',
                        ];
                    }
                }
            }

            foreach ($riwayat as $log) {
                PbiApbnLog::updateOrCreate(
                    ['pbi_apbn_id' => $pbi->id, 'task_name' => $log['task_name']],
                    [
                        'user_id' => null,
                        'username' => $log['username'],
                        'role_name' => $log['role_name'],
                        'catatan' => $log['catatan'],
                    ]
                );
            }
        }
    }
}