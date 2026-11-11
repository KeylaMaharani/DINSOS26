<?php

namespace Database\Seeders;

use App\Models\GcKriteriaOpsi;
use App\Models\GcKriteriaParameter;
use App\Models\PbiKriteriaParameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class GcKriteriaSeeder extends Seeder
{
    /** Nama (huruf kecil) parameter yang pilihannya berasal dari master terpisah -> nilai kolom 'sumber'. */
    private const SUMBER_MASTER = [
        'apakah memiliki keluhan kesehatan kronis/menahun?' => 'penyakit_kronis',
    ];

    public function run(): void
    {
        // Bersihkan opsi yatim (sisa dari parameter yang sudah dihapus/di-truncate).
        $yatim = GcKriteriaOpsi::whereNotIn('parameter_id', GcKriteriaParameter::select('id'))->delete();
        if ($yatim > 0) {
            $this->command?->warn("{$yatim} opsi yatim dibersihkan.");
        }

        if (GcKriteriaParameter::exists()) {
            $this->command?->warn('Master kriteria Ground Check sudah ada, seeder dilewati.');
            return;
        }

        // Opsi PBI disalin dari master PBI, jadi master PBI harus sudah terisi.
        if (!PbiKriteriaParameter::exists()) {
            $this->call(PbiKriteriaSeeder::class);
        }

        $yaTidak = ['Ya', 'Tidak'];

        // [kelompok, nama, bobot, tipe_input, nama parameter PBI (atau null), opsi sementara (atau []), aktif?, wajib?]
        $data = [
            // ================= A. KEPENDUDUKAN =================
            ['kependudukan', 'Nama', 0.5, 'teks', null, []],
            ['kependudukan', 'No KK', 1, 'teks', null, []],
            ['kependudukan', 'Alamat sesuai KK', 1, 'teks', null, []],
            ['kependudukan', 'Apakah No KK sama?', 1, 'pilihan', null, $yaTidak],
            ['kependudukan', 'Apakah nama sama?', 1, 'pilihan', null, $yaTidak],
            ['kependudukan', 'Apakah wilayah sama?', 1, 'pilihan', null, $yaTidak],
            ['kependudukan', 'Nama gadis ibu kandung', 0.5, 'teks', null, []],
            ['kependudukan', 'Alamat domisili', 1, 'teks', null, []],
            ['kependudukan', 'Jenis kelamin', 0.5, 'pilihan', null, ['Laki-laki', 'Perempuan']],
            ['kependudukan', 'Tanggal lahir', 0.5, 'tanggal', null, []],
            ['kependudukan', 'Status perkawinan', 0.5, 'pilihan', null,
                ['Belum kawin', 'Kawin/Nikah', 'Cerai hidup', 'Cerai mati']],
            ['kependudukan', 'Status hubungan dengan kepala keluarga', 1, 'pilihan', null,
                ['Kepala keluarga', 'Suami/Istri', 'Anak', 'Menantu', 'Cucu', 'Orang tua/Mertua', 'Famili lain', 'Lainnya']],
            ['kependudukan', 'No. Telp', 0.5, 'teks', null, []],
            ['kependudukan', 'Email', 0.5, 'teks', null, []],

            // ================= B. PENDIDIKAN =================
            ['pendidikan', 'Partisipasi sekolah', 3, 'pilihan', null,
                ['Belum/tidak pernah sekolah', 'Masih bersekolah', 'Tidak bersekolah lagi']],
            ['pendidikan', 'Jenjang dan jenis pendidikan tertinggi yang pernah/sedang diduduki', 3, 'pilihan', null,
                ['Tidak/belum sekolah', 'SD/sederajat', 'SMP/sederajat', 'SMA/sederajat', 'Diploma (D1-D3)', 'D4/S1', 'S2', 'S3']],
            ['pendidikan', 'Kelas tertinggi yang pernah/sedang diduduki', 2, 'pilihan', null,
                ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6', 'Tamat']],
            ['pendidikan', 'Ijazah/STTB tertinggi yang dimiliki', 2, 'pilihan', null,
                ['Tidak punya ijazah', 'SD/sederajat', 'SMP/sederajat', 'SMA/sederajat', 'Diploma', 'S1', 'S2/S3']],

            // ================= C. KETENAGAKERJAAN =================
            ['ketenagakerjaan', 'Apakah responden bekerja/membantu bekerja selama seminggu yang lalu?', 5, 'pilihan', null, $yaTidak],
            ['ketenagakerjaan', 'Keterampilan khusus/sertifikat keahlian yang dimiliki', 3, 'pilihan_ganda', null,
                ['Belum ada', 'Memiliki sertifikat keahlian', 'Memiliki keterampilan tanpa sertifikat']],
            ['ketenagakerjaan', 'Apakah responden memiliki usaha sendiri/bersama?', 3, 'pilihan', null,
                ['Tidak', 'Ya, sendiri', 'Ya, bersama']],
            ['ketenagakerjaan', 'Pendapatan sebulan terakhir', 4, 'pilihan', 'Penghasilan rata-rata per bulan', []],

            // ================= D. KESEHATAN =================
            ['kesehatan', 'Status disabilitas', 4, 'pilihan_ganda', null,
                ['Tidak ada', 'Tuna netra', 'Tuna rungu', 'Tuna wicara', 'Tuna daksa', 'Tuna grahita', 'Disabilitas mental', 'Disabilitas ganda']],
            // Pilihan berasal dari Master Penyakit Kronis (lihat SUMBER_MASTER), bukan dari gc_kriteria_opsis.
            ['kesehatan', 'Apakah memiliki keluhan kesehatan kronis/menahun?', 3, 'pilihan', null, []],

            // ================= E. KELUARGA =================
            ['keluarga', 'Jumlah keluarga yang tinggal dalam satu rumah', 2, 'angka', 'Jumlah tanggungan keluarga', []],
            ['keluarga', 'Status kepemilikan bangunan tempat tinggal yang ditempati', 4, 'pilihan', 'Status penguasaan bangunan tempat tinggal yang ditempati', []],
            ['keluarga', 'Jenis lantai terluas', 2, 'pilihan', 'Jenis lantai', []],
            ['keluarga', 'Jenis dinding terluas', 2, 'pilihan', 'Jenis dinding', []],
            ['keluarga', 'Jenis atap terluas', 2, 'pilihan', 'Jenis atap', []],
            ['keluarga', 'Sumber air minum utama', 3, 'pilihan', 'Sumber air minum', []],
            ['keluarga', 'Sumber penerangan utama', 2, 'pilihan', 'Sumber penerangan utama', []],
            ['keluarga', 'Bahan bakar/energi utama untuk memasak', 2, 'pilihan', 'Bahan bakar/energi utama untuk memasak', []],
            // 6 opsi sesuai form SOLID; nilai/skor diisi Super Admin lewat menu master.
            ['keluarga', 'Kepemilikan fasilitas BAB', 2, 'pilihan', null, [
                'Ada, digunakan hanya Anggota Keluarga sendiri',
                'Ada, digunakan bersama Anggota Keluarga dari keluarga tertentu',
                'Ada, di MCK komunal',
                'Ada, di MCK umum/siapapun menggunakan',
                'Ada, Anggota Keluarga tidak menggunakan',
                'Tidak ada fasilitas',
            ]],

            // ================= F1. ASET BERGERAK (semua isian angka) =================
            ['aset_bergerak', 'Jumlah tabung gas 5,5 kg atau lebih', 1, 'angka', null, []],
            ['aset_bergerak', 'Jumlah lemari es/kulkas', 1, 'angka', null, []],
            ['aset_bergerak', 'Jumlah air conditioner (AC)', 1, 'angka', null, []],
            ['aset_bergerak', 'Pemanas air (water heater) untuk mandi', 1, 'angka', null, []],
            ['aset_bergerak', 'Telepon rumah (PSTN)', 0.5, 'angka', null, []],
            ['aset_bergerak', 'Televisi layar datar (min. 30 inci)', 1, 'angka', null, []],
            ['aset_bergerak', 'Jumlah emas/perhiasan (gram)', 1, 'angka', null, []],
            ['aset_bergerak', 'Jumlah komputer/laptop/tablet', 1, 'angka', null, []],
            ['aset_bergerak', 'Jumlah sepeda motor', 2, 'angka', 'Kepemilikan kendaraan roda 2', []],
            ['aset_bergerak', 'Jumlah sepeda', 0.5, 'angka', null, []],
            ['aset_bergerak', 'Jumlah mobil', 2, 'angka', null, []],
            ['aset_bergerak', 'Jumlah perahu', 0.5, 'angka', null, []],
            ['aset_bergerak', 'Jumlah kapal/perahu motor', 0.5, 'angka', null, []],
            ['aset_bergerak', 'Jumlah smartphone', 1, 'angka', null, []],

            // ================= F2. ASET TIDAK BERGERAK =================
            ['aset_tidak_bergerak', 'Luas lahan sawah/kebun yang diusahakan milik sendiri', 2, 'pilihan', null,
                ['Tidak memiliki', 'Kurang dari 0,5 ha', '0,5 ha s/d 1 ha', 'Lebih dari 1 ha']],
            ['aset_tidak_bergerak', 'Memiliki lahan (selain yang ditempati)', 1.5, 'pilihan', null, $yaTidak],
            ['aset_tidak_bergerak', 'Memiliki rumah/bangunan di tempat lain', 1.5, 'pilihan', null, $yaTidak],
            ['aset_tidak_bergerak', 'Jumlah ternak sapi yang dimiliki', 0.5, 'angka', null, []],
            ['aset_tidak_bergerak', 'Jumlah ternak kerbau yang dimiliki', 0.5, 'angka', null, []],
            // Ada di form SOLID (Buku Saku hal. 8) tetapi tidak ada di dokumen 39 variabel -> tampil di form, bobot 0 (belum ditentukan).
            ['aset_tidak_bergerak', 'Jumlah ternak kuda yang dimiliki', 0, 'angka', null, []],
            ['aset_tidak_bergerak', 'Jumlah ternak babi yang dimiliki', 0.5, 'angka', null, []],
            ['aset_tidak_bergerak', 'Jumlah ternak kambing/domba yang dimiliki', 0.5, 'angka', null, []],

            // ================= G. DOKUMENTASI =================
            ['dokumentasi', 'Koordinat lokasi', 1, 'koordinat', null, []],
            ['dokumentasi', 'Koordinat lintang', 1, 'angka', null, []],
            ['dokumentasi', 'Koordinat bujur', 1, 'angka', null, []],
            ['dokumentasi', 'Foto tampak depan', 2, 'foto', null, []],
            ['dokumentasi', 'Foto tampak dalam', 1, 'foto', null, []],
            // Memakai Scan KK dari pengajuan PBI -> tidak wajib diunggah ulang (kolom ke-8 wajib = false)
            ['dokumentasi', 'Foto kartu keluarga', 2, 'foto', null, [], true, false],
        ];

        DB::transaction(function () use ($data) {
            foreach ($data as $i => $row) {
                [$kelompok, $nama, $bobot, $tipe, $pbiNama, $opsiSementara] = $row;
                $aktif = $row[6] ?? true;
                $wajib = $row[7] ?? true;
                $sumber = self::SUMBER_MASTER[mb_strtolower(trim($nama))] ?? null;

                $pbi = null;
                if ($pbiNama) {
                    $pbi = PbiKriteriaParameter::where('nama', $pbiNama)->first();
                    if (!$pbi) {
                        $this->command?->warn("Parameter PBI '{$pbiNama}' tidak ditemukan; '{$nama}' dibuat tanpa opsi, isi manual lewat menu master.");
                    }
                }

                $param = GcKriteriaParameter::create([
                    'kelompok' => $kelompok,
                    'urutan' => $i + 1,
                    'nama' => $nama,
                    'bobot' => $bobot,
                    'tipe_input' => $tipe,
                    'sumber' => $sumber,
                    'pbi_parameter_id' => $pbi?->id,
                    'keterangan' => $this->keterangan($tipe, $pbi, $opsiSementara, $aktif, $sumber),
                    'aktif' => $aktif,
                    'wajib' => $wajib,
                ]);

                // Opsi hanya untuk tipe pilihan / pilihan_ganda yang TIDAK bersumber master.
                if (!$param->butuhOpsi()) {
                    continue;
                }

                if ($pbi) {
                    // Salin opsi + indeks dari PBI.
                    foreach ($pbi->opsis()->orderBy('urutan')->get() as $j => $opsi) {
                        $param->opsis()->updateOrCreate(
                            ['label' => $opsi->label],
                            ['nilai' => $opsi->indeks_terintegrasi, 'urutan' => $j + 1]
                        );
                    }
                } else {
                    // Opsi sementara, nilai belum ditentukan.
                    foreach ($opsiSementara as $j => $label) {
                        $param->opsis()->updateOrCreate(
                            ['label' => $label],
                            ['nilai' => null, 'urutan' => $j + 1]
                        );
                    }
                }
            }
        });

        $this->command?->info(count($data) . ' parameter kriteria Ground Check berhasil di-seed.');
    }

    private function keterangan(string $tipe, ?PbiKriteriaParameter $pbi, array $opsiSementara, bool $aktif, ?string $sumber = null): ?string
    {
        if (!$aktif) {
            return 'Ada di form SOLID tetapi tidak tercantum di dokumen 39 variabel; bobot belum ditentukan.';
        }
        if ($sumber === 'penyakit_kronis') {
            return 'Pilihan dikelola di Master Penyakit Kronis/Menahun.';
        }
        if ($pbi && $tipe === 'angka') {
            return "Padanan PBI: {$pbi->nama}. Isian angka, opsi PBI tidak disalin; aturan konversi angka ke indeks belum ditentukan.";
        }
        if ($pbi) {
            return "Opsi disalin dari PBI: {$pbi->nama}.";
        }
        if (in_array($tipe, ['pilihan', 'pilihan_ganda'], true) && $opsiSementara) {
            return 'Opsi sementara, nilai belum ditentukan.';
        }
        return null;
    }
}