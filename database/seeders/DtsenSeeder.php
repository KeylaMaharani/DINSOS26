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
use Illuminate\Support\Facades\Schema;

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
        // 1. KOSONGKAN TABEL KHUSUS DTSEN SAJA
        // Ini memastikan tidak ada error duplikat data jika seeder dijalankan berulang kali,
        // tanpa menghapus data di tabel lain (seperti users atau roles).
        Schema::disableForeignKeyConstraints();
        DtsenPermohonan::truncate();
        DtsenDetail::truncate();
        DtsenBansos::truncate();
        DtsenLampiran::truncate();
        DtsenSurat::truncate();
        DtsenLog::truncate();
        Schema::enableForeignKeyConstraints();

        // 2. PASTIKAN ROLE TERSEDIA
        $roles = collect(['kelurahan', 'operator_dinsos', 'kabin', 'kadis'])
            ->mapWithKeys(fn (string $slug) => [
                $slug => Role::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $this->roleDisplayName($slug), 'slug' => $slug]
                )->id,
            ]);

        $creatorId = User::query()->inRandomOrder()->value('id');
        $stageOrder = array_keys($this->stages);

        $activeStatuses = array_values(array_diff($stageOrder, [DtsenPermohonan::STATUS_SELESAI]));

        // 3. RENCANA STATUS (5 Ajuan aktif, 3 Selesai, 2 Ditolak)
        $statusPlan = array_merge(
            array_fill(0, 5, null), // null = diisi status aktif acak
            array_fill(0, 3, DtsenPermohonan::STATUS_SELESAI),
            array_fill(0, 2, DtsenPermohonan::STATUS_DITOLAK)
        );
        shuffle($statusPlan);

        foreach ($statusPlan as $i => $plannedStatus) {
            $status = $plannedStatus ?? $activeStatuses[array_rand($activeStatuses)];
            $nik = fake()->numerify('################');
            $nama = fake('id_ID')->name();
            $alamat = fake('id_ID')->address();
            $kelurahan = $this->kelurahanList[array_rand($this->kelurahanList)];
            $tanggalInsert = Carbon::now()->subDays(random_int(2, 120));

            // Cek apakah status sudah final (Selesai atau Ditolak)
            $isFinal = in_array($status, [DtsenPermohonan::STATUS_SELESAI, DtsenPermohonan::STATUS_DITOLAK]);

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
                'berlaku_sampai' => $berlakuSampai && fake()->boolean(25)
                    ? Carbon::now()->subMonths(random_int(1, 6))
                    : $berlakuSampai,
                'dicetak_oleh' => $sudahDivalidasiDinsos ? 'Operator Dinsos' : null,
                'upload_lainnya' => null,
            ]);

            DtsenLampiran::create([
                'permohonan_id' => $permohonan->id,
                'screenshot_dtsen' => null,
                'scan_ktp' => null,
            ]);

            DtsenSurat::create([
                'permohonan_id' => $permohonan->id,
                'surat_pengantar_dtsen_kelurahan_digital' => null,
                'surat_keterangan_dtsen_digital' => null,
            ]);

            // 4. BANGUN RIWAYAT LOG
            $logTime = $tanggalInsert->copy();

            // Jika status ditolak, kita simulasikan penolakan terjadi di tahap 1, 2, atau 3 (secara acak)
            if ($status === DtsenPermohonan::STATUS_DITOLAK) {
                $reachedIndex = random_int(1, 3);
                $stagesToLog = array_slice($stageOrder, 0, $reachedIndex);
            } else {
                $reachedIndex = array_search($status, $stageOrder, true);
                $stagesToLog = array_slice($stageOrder, 0, $reachedIndex + 1);
            }

            foreach ($stagesToLog as $stageStatus) {
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

            // Tambahkan log khusus penolakan jika status akhirnya Ditolak
            if ($status === DtsenPermohonan::STATUS_DITOLAK) {
                $logTime = $logTime->copy()->addHours(random_int(1, 5));
                $alasanTolak = $this->alasanTolakList[array_rand($this->alasanTolakList)];

                // Ambil data aktor terakhir yang memproses sebelum ditolak
                $lastStage = $this->stages[$stageOrder[$reachedIndex - 1]];

                DtsenLog::create([
                    'permohonan_id' => $permohonan->id,
                    'tanggal_proses' => $logTime,
                    'user_id' => $creatorId,
                    'username' => $lastStage['username'],
                    'taskname' => 'Permohonan Ditolak',
                    'rolename' => $lastStage['role'],
                    'catatan' => 'Ditolak dengan alasan: ' . $alasanTolak,
                ]);
            }
        }

        $this->command?->info('10 data dummy DTSEN berhasil dibuat (5 Ajuan aktif, 3 Selesai, 2 Ditolak).');
    }
}
