<?php

namespace Database\Seeders;

use App\Models\DtsenBansos;
use App\Models\DtsenDetail;
use App\Models\DtsenLampiran;
use App\Models\DtsenLog;
use App\Models\DtsenPermohonan;
use App\Models\DtsenSurat;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DtsenSeeder extends Seeder
{
    /**
     * Urutan tahapan alur DTSEN, identik dengan alur Kartu KKS:
     * Kelurahan -> Operator Dinsos -> Kabin -> Kadis -> Selesai.
     * Dipakai untuk membangun riwayat log yang konsisten dengan status akhir tiap permohonan.
     */
    private array $stages = [
        DtsenPermohonan::STATUS_DIAJUKAN => [
            'task' => 'Permohonan Masuk',
            'role' => 'Kelurahan',
            'username' => 'kelurahan',
        ],
        DtsenPermohonan::STATUS_VERIFIKASI_KELURAHAN => [
            'task' => 'Verifikasi Kelurahan',
            'role' => 'Kelurahan',
            'username' => 'kelurahan',
        ],
        DtsenPermohonan::STATUS_TTD_LURAH => [
            'task' => 'TTD Lurah',
            'role' => 'Kelurahan',
            'username' => 'kelurahan',
        ],
        DtsenPermohonan::STATUS_VALIDASI_DINSOS => [
            'task' => 'Validasi Dinsos',
            'role' => 'Operator Dinsos',
            'username' => 'operator_dinsos',
        ],
        DtsenPermohonan::STATUS_DIPROSES_KABIN => [
            'task' => 'Diproses Kabin',
            'role' => 'Kabin',
            'username' => 'kabin',
        ],
        DtsenPermohonan::STATUS_DISETUJUI_KADIS => [
            'task' => 'Disetujui Kadis',
            'role' => 'Kadis',
            'username' => 'kadis',
        ],
        DtsenPermohonan::STATUS_SELESAI => [
            'task' => 'Selesai',
            'role' => 'Kadis',
            'username' => 'kadis',
        ],
    ];

    private array $kelurahanList = [
        'Sukasari', 'Baranangsiang', 'Sempur', 'Cibogor', 'Panaragan',
        'Gudang', 'Paledang', 'Kebon Kelapa', 'Ciwaringin', 'Pabaton',
        'Tegallega', 'Babakan Pasar', 'Bantarjati', 'Tanah Baru', 'Katulampa',
    ];

    private array $alasanCetakList = [
        'Kartu DTSEN rusak',
        'Kartu DTSEN hilang',
        'Perubahan data anggota keluarga',
        'Pencetakan ulang karena data tidak sesuai',
        'Kartu DTSEN sudah habis masa berlaku',
        'Pengajuan baru untuk keluarga penerima manfaat',
    ];

    private array $alasanTolakList = [
        'Data NIK tidak sesuai dengan Dukcapil',
        'Berkas lampiran tidak lengkap',
        'Alamat pada KK tidak sesuai domisili saat ini',
        'Sudah tidak memenuhi kriteria penerima manfaat',
    ];

    private array $peringkatList = ['Desil 1', 'Desil 2', 'Desil 3', 'Desil 4', 'Desil 5'];

    /**
     * Nama tampilan (Title Case) untuk tiap slug role, dipakai saat firstOrCreate
     * supaya konsisten dengan data yang dibuat RoleSeeder/KartuKksSeeder.
     */
    private function roleDisplayName(string $slug): string
    {
        return match ($slug) {
            'kelurahan' => 'Kelurahan',
            'operator_dinsos' => 'Operator Dinsos',
            'kabin' => 'Kabin (Kepala Bidang)',
            'kadis' => 'Kadis (Kepala Dinas)',
            default => ucfirst(str_replace('_', ' ', $slug)),
        };
    }

    public function run(): void
    {
        // Role dipakai untuk current_role_id (foreign key), dibuat kalau belum ada.
        // PENTING: dicari & dibuat berdasarkan `slug` (bukan `name`), dan `slug` selalu
        // diisi eksplisit -- kolom ini NOT NULL tanpa default di database.
        $roles = collect(['kelurahan', 'operator_dinsos', 'kabin', 'kadis'])
            ->mapWithKeys(fn (string $slug) => [
                $slug => Role::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $this->roleDisplayName($slug), 'slug' => $slug]
                )->id,
            ]);

        $creatorId = User::query()->inRandomOrder()->value('id');

        $stageOrder = array_keys($this->stages);

        // Status "aktif" = semua tahap kecuali Selesai (Ditolak sengaja tidak
        // dibuat di seeder ini). Dipakai untuk mengisi slot 7 data Ajuan.
        $activeStatuses = array_values(array_diff($stageOrder, [DtsenPermohonan::STATUS_SELESAI]));

        // Rencana status utk 10 data: 7 tersebar di tahap aktif (-> muncul di Ajuan),
        // 3 berstatus Selesai (-> muncul di Arsip, karena Arsip hanya menampilkan Selesai).
        $statusPlan = array_merge(
            array_fill(0, 7, null), // null = diisi status aktif acak di bawah
            array_fill(0, 3, DtsenPermohonan::STATUS_SELESAI)
        );
        shuffle($statusPlan);

        foreach ($statusPlan as $i => $plannedStatus) {
            $status = $plannedStatus ?? $activeStatuses[array_rand($activeStatuses)];
            $nik = fake()->numerify('################');
            $nama = fake('id_ID')->name();
            $alamat = fake('id_ID')->address();
            $kelurahan = $this->kelurahanList[array_rand($this->kelurahanList)];
            $tanggalInsert = Carbon::now()->subDays(random_int(2, 120));

            $isFinal = $status === DtsenPermohonan::STATUS_SELESAI;

            $currentRoleName = match (true) {
                $isFinal => null,
                default => match ($status) {
                    DtsenPermohonan::STATUS_DIAJUKAN,
                    DtsenPermohonan::STATUS_VERIFIKASI_KELURAHAN,
                    DtsenPermohonan::STATUS_TTD_LURAH => 'kelurahan',
                    DtsenPermohonan::STATUS_VALIDASI_DINSOS => 'operator_dinsos',
                    DtsenPermohonan::STATUS_DIPROSES_KABIN => 'kabin',
                    DtsenPermohonan::STATUS_DISETUJUI_KADIS => 'kadis',
                    default => null,
                },
            };

            $permohonan = DtsenPermohonan::create([
                'nik' => $nik,
                'nama_pemohon' => $nama,
                'alamat' => $alamat,
                'alasan_cetak' => $this->alasanCetakList[array_rand($this->alasanCetakList)],
                'status' => $status,
                'current_role_id' => $currentRoleName ? $roles[$currentRoleName] : null,
                'kelurahan' => $kelurahan,
                'tanggal_insert' => $tanggalInsert,
                'created_by' => $creatorId,
            ]);

            DtsenDetail::create([
                'permohonan_id' => $permohonan->id,
                'nik' => $nik,
                'nama' => $nama,
                'no_kk' => fake()->numerify('################'),
                'alamat' => $alamat,
                'tanggal_lahir' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
                'alasan_keputusan_cetak' => null,
            ]);

            // Bansos & surat baru terisi kalau sudah lewat tahap Validasi Dinsos / Selesai.
            $sudahDivalidasiDinsos = in_array($status, [
                DtsenPermohonan::STATUS_DIPROSES_KABIN,
                DtsenPermohonan::STATUS_DISETUJUI_KADIS,
                DtsenPermohonan::STATUS_SELESAI,
            ], true);

            $tanggalCetak = $sudahDivalidasiDinsos ? $tanggalInsert->copy()->addDays(random_int(3, 10)) : null;
            $berlakuSampai = $tanggalCetak ? $tanggalCetak->copy()->addYear() : null;

            DtsenBansos::create([
                'permohonan_id' => $permohonan->id,
                'peringkat_kesejahteraan_keluarga' => $this->peringkatList[array_rand($this->peringkatList)],
                'bnpt' => fake()->boolean(40),
                'pkh' => fake()->boolean(40),
                'pbi' => fake()->boolean(60),
                'yatim_piatu' => fake()->boolean(15),
                'tanggal_cetak' => $tanggalCetak,
                // sebagian sengaja dibuat sudah lewat masa berlaku, untuk menguji badge "Kedaluwarsa"
                'berlaku_sampai' => $berlakuSampai && fake()->boolean(25)
                    ? Carbon::now()->subMonths(random_int(1, 6))
                    : $berlakuSampai,
                'dicetak_oleh' => $sudahDivalidasiDinsos ? 'Operator Dinsos' : null,
                'upload_lainnya' => null,
            ]);

            // Tidak ada file asli yang diunggah — kolom lampiran/surat sengaja dikosongkan (null)
            // supaya tampilan "belum ada" di UI tetap benar, tidak menunjuk ke file yang tidak ada.
            DtsenLampiran::create([
                'permohonan_id' => $permohonan->id,
                'screenshot_dtsen' => null,
                'scan_ktp' => null,
            ]);

            DtsenSurat::create([
                'permohonan_id' => $permohonan->id,
                'surat_pengantar_dtsen_kelurahan_digital' => null,
                'surat_pengantar_dtsen_kelurahan_digital_at' => null,
                'surat_keterangan_dtsen_digital' => null,
                'surat_keterangan_dtsen_digital_at' => null,
            ]);

            // ===== Bangun riwayat log sesuai status saat ini =====
            $logTime = $tanggalInsert->copy();
            $reachedIndex = array_search($status, $stageOrder, true);

            foreach (array_slice($stageOrder, 0, $reachedIndex + 1) as $stageStatus) {
                $stage = $this->stages[$stageStatus];
                $logTime = $logTime->copy()->addDays(random_int(1, 4))->setTime(random_int(8, 16), random_int(0, 59));

                DtsenLog::create([
                    'permohonan_id' => $permohonan->id,
                    'tanggal_proses' => $logTime,
                    'user_id' => $creatorId,
                    'username' => $stage['username'],
                    'taskname' => $stage['task'],
                    'rolename' => $stage['role'],
                    'catatan' => $stageStatus === array_key_first($this->stages)
                        ? 'Data awal masuk melalui pendaftaran online.'
                        : null,
                ]);
            }
        }

        $this->command?->info('10 data dummy DTSEN berhasil dibuat (7 di Ajuan, 3 di Arsip).');
    }
}
