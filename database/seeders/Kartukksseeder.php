<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\KartuKksPermohonan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KartuKksSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Pastikan semua role di alur sudah ada (aman dijalankan berkali-kali)
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin'],
            ['name' => 'Kelurahan', 'slug' => 'kelurahan'],
            ['name' => 'Operator Dinsos', 'slug' => 'operator_dinsos'],
            ['name' => 'PFM', 'slug' => 'pfm'],
            ['name' => 'Dayasos', 'slug' => 'dayasos'],
            ['name' => 'Perlinsos', 'slug' => 'perlinsos'],
            ['name' => 'Rehabsos', 'slug' => 'rehabsos'],
            ['name' => 'Kabin (Kepala Bidang)', 'slug' => 'kabin'],
            ['name' => 'Kadis (Kepala Dinas)', 'slug' => 'kadis'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['slug' => $r['slug']], $r);
        }

        // 2) Contoh 1 user dummy per role, supaya bisa login & lihat sisi masing-masing
        //    Password default: password
        foreach ($roles as $r) {
            User::firstOrCreate(
                ['email' => $r['slug'] . '@dinsoskotabogor.test'],
                [
                    'name' => $r['name'],
                    'username' => $r['slug'],
                    'password' => Hash::make('password'),
                    'role_id' => Role::where('slug', $r['slug'])->first()->id,
                ]
            );
        }

        // 3) Dummy permohonan tersebar di berbagai tahap alur, supaya tiap tab
        //    (Ajuan/Arsip/Monitoring) sudah ada isinya waktu pertama kali dicoba.
        //
        //    PENTING: current_role_id harus sesuai siapa yang SEDANG memegang
        //    berkas pada status tsb (lihat transitions() di KartuKksController):
        //      diajukan              -> kelurahan
        //      verifikasi_kelurahan  -> kelurahan
        //      ttd_lurah             -> operator_dinsos
        //      validasi_dinsos       -> operator_dinsos
        //      diproses_kabin        -> kabin
        //      disetujui_kadis       -> kadis
        //      selesai / ditolak     -> null
        $kelurahanRole = Role::where('slug', 'kelurahan')->first();
        $dinsosRole = Role::where('slug', 'operator_dinsos')->first();
        $kabinRole = Role::where('slug', 'kabin')->first();
        $kadisRole = Role::where('slug', 'kadis')->first();

        $dummy = [
            [
                'nik' => '3271010101900001',
                'nama' => 'Sri Wahyuni',
                'alamat' => 'Jl. Merdeka No. 12, Kel. Bogor Tengah',
                'lat' => -6.5950, 'lng' => 106.7890,
                'masalah' => 'Kartu Hilang',
                'status' => KartuKksPermohonan::STATUS_DIAJUKAN,
                'current_role' => $kelurahanRole?->id,
            ],
            [
                'nik' => '3271011101900011',
                'nama' => 'Yayan Sopian',
                'alamat' => 'Jl. Otista No. 5, Kel. Sempur',
                'lat' => -6.5940, 'lng' => 106.7955,
                'masalah' => 'Data Tidak Sesuai KTP',
                'status' => KartuKksPermohonan::STATUS_VERIFIKASI_KELURAHAN,
                'current_role' => $kelurahanRole?->id,
            ],
            [
                'nik' => '3271020202880002',
                'nama' => 'Ahmad Fauzi',
                'alamat' => 'Jl. Pajajaran No. 45, Kel. Baranangsiang',
                'lat' => -6.6021, 'lng' => 106.8071,
                'masalah' => 'Kartu Rusak',
                'status' => KartuKksPermohonan::STATUS_TTD_LURAH,
                'current_role' => $dinsosRole?->id,
            ],
            [
                'nik' => '3271030303950003',
                'nama' => 'Dewi Lestari',
                'alamat' => 'Jl. Sudirman No. 8, Kel. Cibogor',
                'lat' => -6.5975, 'lng' => 106.7950,
                'masalah' => 'Kartu Hilang',
                'status' => KartuKksPermohonan::STATUS_VALIDASI_DINSOS,
                'current_role' => $dinsosRole?->id,
            ],
            [
                'nik' => '3271040404870004',
                'nama' => 'Budi Santoso',
                'alamat' => 'Jl. Ahmad Yani No. 20, Kel. Tanah Sareal',
                'lat' => -6.5721, 'lng' => 106.7959,
                'masalah' => 'Kartu Rusak',
                'status' => KartuKksPermohonan::STATUS_DIPROSES_KABIN,
                'current_role' => $kabinRole?->id,
            ],
            [
                'nik' => '3271041404870014',
                'nama' => 'Fitri Ramadhani',
                'alamat' => 'Jl. Salak No. 9, Kel. Babakan',
                'lat' => -6.6005, 'lng' => 106.8002,
                'masalah' => 'Kartu Hilang',
                'status' => KartuKksPermohonan::STATUS_DISETUJUI_KADIS,
                'current_role' => $kadisRole?->id,
            ],
            [
                'nik' => '3271050505920005',
                'nama' => 'Ratna Sari',
                'alamat' => 'Jl. Pahlawan No. 3, Kel. Empang',
                'lat' => -6.6100, 'lng' => 106.7930,
                'masalah' => 'Kartu Hilang',
                'status' => KartuKksPermohonan::STATUS_SELESAI,
                'current_role' => null,
            ],
            [
                'nik' => '3271060606930006',
                'nama' => 'Hendra Gunawan',
                'alamat' => 'Jl. Raya Tajur No. 15, Kel. Baranangsiang',
                'lat' => -6.6180, 'lng' => 106.8100,
                'masalah' => 'Data Tidak Sesuai KTP',
                'status' => KartuKksPermohonan::STATUS_DITOLAK,
                'current_role' => null,
            ],
        ];

        foreach ($dummy as $d) {
            $permohonan = KartuKksPermohonan::firstOrCreate(
                ['nik' => $d['nik']],
                [
                    'nama_pemohon' => $d['nama'],
                    'alamat' => $d['alamat'],
                    'latitude' => $d['lat'],
                    'longitude' => $d['lng'],
                    'masalah_kartu' => $d['masalah'],
                    'nomor_kehilangan_polisi' => $d['masalah'] === 'Kartu Hilang' ? 'SKH/' . rand(100, 999) . '/IX/2026' : null,
                    'status' => $d['status'],
                    'current_role_id' => $d['current_role'],
                    'kelurahan' => explode(', Kel. ', $d['alamat'])[1] ?? null,
                    'tanggal_insert' => now()->subDays(rand(1, 20)),
                ]
            );

            if ($permohonan->wasRecentlyCreated) {
                $permohonan->detail()->create([
                    'nik' => $d['nik'],
                    'nama' => $d['nama'],
                    'jenis_kelamin' => rand(0, 1) ? 'L' : 'P',
                    'tempat_lahir' => 'Bogor',
                    'tanggal_lahir' => now()->subYears(rand(25, 55))->subDays(rand(1, 300)),
                    'agama' => 'Islam',
                    'status_perkawinan' => 'Kawin',
                    'no_pkh' => 'PKH-' . rand(100000, 999999),
                    'no_kk' => '32710' . rand(1000000000, 2000000000),
                    'no_kartu_kks' => 'KKS-' . rand(100000, 999999),
                    'no_rekening' => rand(1000000000, 9999999999),
                    'pekerjaan' => 'Tidak Bekerja',
                    'alamat' => $d['alamat'],
                    'masalah_kartu' => $d['masalah'],
                    'nomor_kehilangan_polisi' => $permohonan->nomor_kehilangan_polisi,
                ]);

                // Path dummy -- ganti dengan file asli setelah upload beneran berjalan
                $permohonan->lampiran()->create([
                    'scan_ktp' => 'dummy/ktp-placeholder.jpg',
                    'scan_kartu_keluarga' => 'dummy/kk-placeholder.jpg',
                    'surat_kehilangan_polisi' => $d['masalah'] === 'Kartu Hilang' ? 'dummy/skh-placeholder.pdf' : null,
                    'scan_kartu_kks' => 'dummy/kks-placeholder.jpg',
                    'screenshot_dtsen' => 'dummy/dtsen-placeholder.jpg',
                ]);

                $permohonan->surat()->create([
                    'surat_pengantar_kelurahan' => in_array($d['status'], [
                        KartuKksPermohonan::STATUS_TTD_LURAH,
                        KartuKksPermohonan::STATUS_VALIDASI_DINSOS,
                        KartuKksPermohonan::STATUS_DIPROSES_KABIN,
                        KartuKksPermohonan::STATUS_DISETUJUI_KADIS,
                        KartuKksPermohonan::STATUS_SELESAI,
                    ]) ? 'dummy/surat-pengantar-placeholder.pdf' : null,
                    'surat_keterangan_dinsos' => $d['status'] === KartuKksPermohonan::STATUS_SELESAI
                        ? 'dummy/surat-keterangan-placeholder.pdf' : null,
                ]);

                // Bangun histori log sesuai posisi status saat ini.
                // rolename & username mengikuti tahap alur yang sebenarnya
                // (bukan role asli akun demo yang menjalankan seeder), supaya
                // konsisten dengan roleLabel() di KartuKksController.
                $steps = [
                    KartuKksPermohonan::STATUS_DIAJUKAN => [
                        'task' => 'Permohonan Masuk',
                        'rolename' => 'Kelurahan',
                        'username' => 'kelurahan',
                    ],
                    KartuKksPermohonan::STATUS_VERIFIKASI_KELURAHAN => [
                        'task' => 'Verifikasi Kelurahan',
                        'rolename' => 'Kelurahan',
                        'username' => 'kelurahan',
                    ],
                    KartuKksPermohonan::STATUS_TTD_LURAH => [
                        'task' => 'TTD Lurah',
                        'rolename' => 'Kelurahan',
                        'username' => 'kelurahan',
                    ],
                    KartuKksPermohonan::STATUS_VALIDASI_DINSOS => [
                        'task' => 'Validasi Dinsos',
                        'rolename' => 'Operator Dinsos',
                        'username' => 'operator_dinsos',
                    ],
                    KartuKksPermohonan::STATUS_DIPROSES_KABIN => [
                        'task' => 'Diteruskan ke Kabin',
                        'rolename' => 'Operator Dinsos',
                        'username' => 'operator_dinsos',
                    ],
                    KartuKksPermohonan::STATUS_DISETUJUI_KADIS => [
                        'task' => 'Disetujui Kabin, diteruskan ke Kadis',
                        'rolename' => 'Kabin',
                        'username' => 'kabin',
                    ],
                    KartuKksPermohonan::STATUS_SELESAI => [
                        'task' => 'Disetujui Kadis',
                        'rolename' => 'Kepala Dinas',
                        'username' => 'kadis',
                    ],
                ];

                if ($d['status'] === KartuKksPermohonan::STATUS_DITOLAK) {
                    // Untuk yang ditolak: log 1 tahap awal saja, lalu log "Ditolak"
                    $permohonan->logs()->create([
                        'tanggal_proses' => now()->subDays(rand(5, 20)),
                        'username' => 'kelurahan',
                        'taskname' => 'Permohonan Masuk',
                        'rolename' => 'Kelurahan',
                        'catatan' => 'Data awal masuk melalui pendaftaran online.',
                    ]);
                    $permohonan->logs()->create([
                        'tanggal_proses' => now()->subDays(rand(1, 4)),
                        'username' => 'kelurahan',
                        'taskname' => 'Ditolak',
                        'rolename' => 'Kelurahan',
                        'catatan' => 'NIK tidak sesuai dengan data KTP.',
                    ]);
                } else {
                    $order = array_keys($steps);
                    $currentIndex = array_search($d['status'], $order);
                    $totalSteps = $currentIndex + 1;
                    $i = 0;
                    foreach ($steps as $status => $step) {
                        if ($i > $currentIndex) {
                            break;
                        }
                        // Log terlama = paling banyak hari lalu, log terbaru = paling sedikit hari lalu,
                        // supaya urutan tanggal selalu naik (kronologis), bukan acak.
                        $daysAgo = ($totalSteps - $i) * rand(2, 5);
                        $permohonan->logs()->create([
                            'tanggal_proses' => now()->subDays($daysAgo),
                            'username' => $step['username'],
                            'taskname' => $step['task'],
                            'rolename' => $step['rolename'],
                            'catatan' => $i === 0 ? 'Data awal masuk melalui pendaftaran online.' : null,
                        ]);
                        $i++;
                    }
                }
            }
        }
    }
}
