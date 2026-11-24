<?php

namespace Database\Seeders;

use App\Models\GcKriteriaOpsi;
use App\Models\GcKriteriaParameter;
use App\Models\GcPenyakitKronis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Keterangan Anggota Keluarga disamakan dengan rekap formulir (bagian 4). Aman dijalankan berulang.
 * Jalankan: php artisan db:seed --class=GcKriteriaAnggotaSeeder
 */
class GcKriteriaAnggotaSeeder extends Seeder
{
    /** true = opsi lama yang tidak ada di tabel dihapus (jawaban lama yang labelnya hilang akan gagal validasi saat disimpan ulang). */
    private const HAPUS_OPSI_LAMA = true;

    private const PROFESI = [
        'Tidak Bekerja', 'Agen Tenaga Kerja', 'Ahli Sejarah dan Cagar budaya', 'Akuntan', 'Analisis Keuangan',
        'Anggota DPD', 'Anggota DPR RI/MPR RI', 'Anggota DPRD Provinsi/ Anggota DPRD Kabupaten/Kota',
        'Apoteker', 'Arsiparis', 'Arsitek', 'Asisten Apoteker', 'Atase', 'Atlet/Olahragawan', 'Awak Kapal',
        'Bhikkhu', 'Biarawan', 'Biarawati', 'Bidan', 'Broker/Pialang Saham', 'Bupati', 'Buruh Angkut Barang',
        'Buruh Bangunan', 'Buruh Industri', 'Buruh Perikanan', 'Buruh Pertambangan', 'Buruh Pertanian/Kehutanan',
        'Buruh Peternakan',
        'Lainnya',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $yaTidak = ['Ya', 'Tidak'];

            // 1. Pertanyaan baru
            $this->pastikan('kependudukan', 'Dimana keberadaan anggota keluarga sekarang?', [
                'Tinggal bersama keluarga', 'Meninggal', 'Pindah ke daerah lain di Indonesia',
                'Pindah ke luar negeri', 'Sudah pisah kartu keluarga', 'Lainnya',
            ], true);

            foreach (['Fisik', 'Mental', 'Intelektual', 'Sensorik Netra', 'Sensorik Rungu', 'Sensorik Wicara'] as $j) {
                $this->pastikan('kesehatan', "Apakah menyandang Disabilitas {$j}?", $yaTidak, true);
            }

            // 2. Ubah teks pertanyaan + samakan pilihan: [kata kunci lama/baru, nama baru, opsi]
            $sinkron = [
                [['apa status partisipasi sekolah', 'partisipasi sekolah'], 'Apa status partisipasi sekolah?',
                    ['Tidak/belum pernah sekolah', 'Masih sekolah', 'Tidak bersekolah lagi']],
                [['apa ijazah / sttb', 'ijazah/sttb tertinggi'], 'Apa ijazah / STTB tertinggi yang dimiliki?',
                    ['Tidak Punya Ijazah SD', 'SD/Sederajat', 'SMP/Sederajat', 'SMA/Sederajat', 'Diploma (D1/D2/D3)',
                     'Diploma IV/S1', 'S2', 'S3', 'Lainnya']], // Diploma IV/S1, S2, S3 = asumsi (di dokumen tertutup "Lainnya")
                [['apa status kedudukan dalam pekerjaan utama', 'status kedudukan dalam pekerjaan utama'],
                    'Apa status kedudukan dalam pekerjaan utama?',
                    ['Berusaha sendiri', 'Berusaha dibantu buruh', 'Buruh/karyawan/pegawai swasta',
                     'ASN/TNI/Polri/BUMN/BUMD/pejabat negara/kades', 'Pekerja bebas', 'Pekerja keluarga/tidak dibayar']],
                [['kepemilikan rekening aktif'], null,
                    ['Ya untuk usaha', 'Ya untuk pribadi', 'Ya untuk usaha dan pribadi', 'Tidak ada']],
                [['status perkawinan'], null, ['Belum Kawin', 'Kawin/Nikah', 'Cerai Hidup', 'Cerai Mati']],
                [['status hubungan dengan kepala keluarga'], null,
                    ['Kepala keluarga', 'Istri/suami', 'Anak', 'Menantu', 'Cucu', 'Lainnya']],
            ];
            foreach ($sinkron as [$kunci, $namaBaru, $opsi]) {
                $p = $this->cari($kunci);
                if (! $p) { $this->command?->warn('Tidak ditemukan: ' . $kunci[0]); continue; }
                $p->update(['aktif' => true] + ($namaBaru ? ['nama' => $namaBaru] : []));
                $this->syncOpsi($p, $opsi);
                $this->command?->info('Disamakan: ' . $p->nama);
            }

            // 3. No. Telp: tidak wajib (tabel: kosongkan bila tidak punya)
            GcKriteriaParameter::whereRaw("LOWER(TRIM(nama)) IN ('no. telp','no telp')")->update(['wajib' => false]);

            // 4. Tidak ada di tabel -> nonaktif di form masyarakat (data lama aman)
            $mati = GcKriteriaParameter::where('aktif', true)->where(function ($q) {
                foreach ([
                    'tanggal lahir', 'email', 'nama gadis ibu kandung', 'status disabilitas',
                    'jenjang dan jenis pendidikan%', 'kelas tertinggi%', 'keterampilan khusus%',
                    'apakah responden memiliki usaha%', 'pendapatan sebulan terakhir', 'pekerjaan%',
                ] as $pola) {
                    $q->orWhereRaw('LOWER(TRIM(nama)) LIKE ?', [$pola]);
                }
            })->whereIn('kelompok', ['kependudukan', 'pendidikan', 'ketenagakerjaan', 'kesehatan'])
              ->where('nama', 'not like', 'Pekerjaan sebulan%');
            $this->command?->info($mati->count() . ' pertanyaan lama dinonaktifkan.');
            $mati->update(['aktif' => false]);

            // 4b. Profesi pekerjaan utama menggantikan "Lapangan usaha"; "Apakah responden bekerja...?" dihapus dari form
            $gate = GcKriteriaParameter::whereRaw("LOWER(TRIM(nama)) LIKE ?", ['apakah responden bekerja%'])->first();
            if ($gate) {
                GcKriteriaParameter::where('induk_id', $gate->id)->update(['induk_id' => null, 'induk_nilai' => null]);
                $gate->update(['aktif' => false, 'wajib' => false]);
            }

            $prof = $this->cari(['lapangan usaha dari pekerjaan utama', 'profesi pekerjaan utama']);
            if ($prof) {
                $prof->update([
                    'nama' => 'Apa profesi pekerjaan utama?', 'tipe_input' => 'pilihan', 'sumber' => null,
                    'aktif' => true, 'wajib' => true, 'induk_id' => null, 'induk_nilai' => null,
                ]);
                $this->syncOpsi($prof, self::PROFESI);
                $this->command?->info('Profesi pekerjaan utama: ' . count(self::PROFESI) . ' pilihan.');
            } else {
                $this->command?->warn('Parameter lapangan usaha tidak ditemukan.');
            }

            // 5. Master penyakit kronis = daftar di tabel
            $penyakit = ['Tidak Ada', 'Hipertensi (tekanan darah tinggi)', 'Rematik', 'Asma', 'Masalah jantung',
                'Diabetes (kencing manis)', 'Tuberculosis (TBC)', 'Stroke', 'Kanker atau tumor ganas', 'Gagal ginjal',
                'Haemophilia', 'HIV/AIDS', 'Kolesterol', 'Sirosis Hati', 'Thalasemia', 'Leukimia', 'Alzheimer', 'Lainnya'];
            $keep = [];
            foreach ($penyakit as $i => $nama) {
                $keep[] = GcPenyakitKronis::updateOrCreate(['nama' => $nama], ['urutan' => $i + 1, 'aktif' => true])->id;
            }
            GcPenyakitKronis::whereNotIn('id', $keep)->delete();
        });
    }

    private function pastikan(string $kel, string $nama, array $opsi, bool $wajib): GcKriteriaParameter
    {
        $p = GcKriteriaParameter::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($nama)])->first();
        if (! $p) {
            $p = GcKriteriaParameter::create([
                'kelompok' => $kel,
                'urutan' => ((int) GcKriteriaParameter::max('urutan')) + 1,
                'nama' => $nama, 'bobot' => 0, 'tipe_input' => 'pilihan', 'sumber' => null,
                'target' => GcKriteriaParameter::targetBawaan($kel, $nama),
                'pbi_parameter_id' => null,
                'keterangan' => 'Dari rekap formulir Keterangan Anggota Keluarga; bobot & nilai opsi belum ditentukan.',
                'aktif' => true, 'wajib' => $wajib,
            ]);
            $this->command?->info("Ditambahkan: {$nama}");
        }
        $this->syncOpsi($p, $opsi);

        return $p;
    }

    private function syncOpsi(GcKriteriaParameter $p, array $labels): void
    {
        $ada = GcKriteriaOpsi::where('parameter_id', $p->id)->get()->keyBy(fn ($o) => mb_strtolower(trim($o->label)));
        $keep = [];
        $urut = 0;

        foreach ($labels as $label) {
            $o = $ada->get(mb_strtolower(trim($label)));
            if ($o) {
                $o->update(['label' => $label, 'urutan' => ++$urut]);
            } else {
                $o = $p->opsis()->create(['label' => $label, 'nilai' => null, 'urutan' => ++$urut]);
            }
            $keep[] = $o->id;
        }

        foreach ($ada as $o) {
            if (in_array($o->id, $keep, true)) { continue; }
            self::HAPUS_OPSI_LAMA ? $o->delete() : $o->update(['urutan' => ++$urut]);
        }
    }

    private function cari(array $kunci): ?GcKriteriaParameter
    {
        foreach ($kunci as $k) {
            $p = GcKriteriaParameter::whereRaw('LOWER(nama) LIKE ?', ['%' . mb_strtolower($k) . '%'])->first();
            if ($p) { return $p; }
        }

        return null;
    }
}