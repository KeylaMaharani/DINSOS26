<?php

namespace Database\Seeders;

use App\Models\GcKriteriaParameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Penyesuaian master Ground Check dengan form Pendataan Keluarga (DTSEN) terbaru.
 * Aman dijalankan berulang: parameter yang sudah ada dilewati, opsi lama tidak dihapus
 * (kecuali HAPUS_OPSI_LAMA = true). Bobot parameter baru = 0, nilai opsi baru = null
 * (diisi Super Admin lewat menu master).
 *
 * Jalankan: php artisan db:seed --class=GcKriteriaFormDtsenSeeder
 */
class GcKriteriaFormDtsenSeeder extends Seeder
{
    /** true = opsi lama yang tidak ada di form baru dihapus; false = dipindah ke urutan paling bawah. */
    private const HAPUS_OPSI_LAMA = false;

    public function run(): void
    {
        $yaTidak = ['Ya', 'Tidak'];

        // [kelompok, nama, tipe_input, wajib, opsi, kata kunci tambahan (awalan "=" = nama persis), keterangan]
        $baru = [
            // ===== Identitas =====
            ['kependudukan', 'Provinsi', 'teks', true],
            ['kependudukan', 'Kabupaten/Kota', 'teks', true, [], ['=kota', '=kabupaten']],
            ['kependudukan', 'Kecamatan', 'teks', true],
            ['kependudukan', 'Desa/Kelurahan', 'teks', true, [], ['=kelurahan']],
            ['kependudukan', 'Kode pos', 'teks', true],
            ['kependudukan', 'RT', 'teks', true],
            ['kependudukan', 'RW', 'teks', true],
            ['kependudukan', 'Nama jalan', 'teks', true],
            ['kependudukan', 'Nomor rumah', 'teks', true],
            ['kependudukan', 'Kepemilikan rekening aktif/dompet digital', 'pilihan', true,
                ['Ya untuk usaha', 'Ya untuk pribadi', 'Ya untuk usaha dan pribadi', 'Tidak ada'], ['rekening'],
                'Ditaruh di Kependudukan (bukan Ketenagakerjaan) agar tidak ikut dikosongkan saat responden "tidak bekerja".'],

            // ===== Bangunan tempat tinggal =====
            ['keluarga', 'Jenis bangunan tempat tinggal', 'pilihan', true,
                ['Rumah tunggal', 'Apartemen', 'Rumah susun', 'Rumah deret', 'Lainnya'], ['jenis bangunan']],
            ['keluarga', 'Jenis bangunan lainnya (sebutkan)', 'teks', false, [], ['bangunan lainnya'],
                'Hanya relevan jika jenis bangunan = Lainnya.'],
            ['keluarga', 'Selain keluarga Anda, apakah ada keluarga lain yang tinggal di rumah ini?', 'pilihan', false,
                $yaTidak, ['keluarga lain'], 'Hanya untuk Rumah tunggal / Rumah deret.'],
            ['keluarga', 'Nama gedung/tower tempat tinggal', 'teks', false, [], ['gedung', 'tower'],
                'Hanya untuk Apartemen / Rumah susun.'],
            ['keluarga', 'Nama/nomor lantai tempat tinggal', 'teks', false, [], ['nomor lantai'],
                'Hanya untuk Apartemen / Rumah susun.'],
            ['keluarga', 'Jumlah orang yang tinggal dalam satu rumah', 'angka', true, [],
                ['jumlah orang', 'tinggal dalam satu rumah']],

            // ===== Kepemilikan =====
            ['keluarga', 'Bukti kepemilikan tanah bangunan tempat tinggal', 'pilihan', false,
                ['SHM', 'Sertifikat selain SHM (SHGB, SHSRS)', 'Surat bukti lainnya (Girik, Letter C, dll)', 'Tidak punya'],
                ['bukti kepemilikan tanah'], 'Hanya untuk status kepemilikan Milik sendiri.'],
            ['keluarga', 'Perkiraan harga sewa/nilai bangunan tempat tinggal per bulan (Rp)', 'angka', true, [],
                ['harga sewa', 'nilai bangunan']],
            ['keluarga', 'Luas lantai bangunan tempat tinggal (m²)', 'angka', false, [], ['luas lantai'],
                'Untuk semua status kecuali Milik sendiri.'],
            ['keluarga', 'Status kepemilikan lainnya (sebutkan)', 'teks', false, [], ['kepemilikan lainnya'],
                'Hanya relevan jika status kepemilikan = Lainnya.'],

            // ===== Listrik & pengeluaran =====
            ['keluarga', 'Jumlah meteran listrik yang terpasang', 'angka', false, [], ['jumlah meteran'],
                'Hanya untuk Listrik PLN dengan meteran.'],
            ['keluarga', 'ID Pelanggan PLN / No Meteran Listrik', 'teks', false, [], ['id pelanggan', 'no meteran', 'nomor meteran'],
                'Hanya untuk Listrik PLN dengan meteran; minimal salah satu terisi.'],
            ['keluarga', 'Rata-rata pengeluaran listrik sebulan (Rp)', 'angka', false, [], ['pengeluaran listrik'],
                'Untuk PLN tanpa meteran dan Non-PLN.'],
            ['keluarga', 'Rata-rata pengeluaran pulsa seluruh anggota keluarga sebulan (Rp)', 'angka', true, [], ['pengeluaran pulsa']],
            ['keluarga', 'Rata-rata pengeluaran internet seluruh anggota keluarga sebulan (Rp)', 'angka', true, [], ['pengeluaran internet']],
            ['keluarga', 'Rata-rata pengeluaran makanan keluarga seminggu (Rp)', 'angka', true],
            ['keluarga', 'Rata-rata pengeluaran bukan makanan rutin keluarga sebulan (Rp)', 'angka', true],
            ['keluarga', 'Rata-rata pengeluaran bukan makanan rutin keluarga setahun (Rp)', 'angka', true],

            // ===== Pendapatan =====
            ['keluarga', 'Apakah ada pendapatan keluarga dari usaha (offline/online)?', 'pilihan', true, $yaTidak,
                ['pendapatan dari usaha', 'pendapatan usaha']],
            ['keluarga', 'Total pendapatan keluarga dari usaha tiap bulan (Rp)', 'angka', false, [],
                ['total pendapatan usaha'], 'Diisi jika ada pendapatan dari usaha.'],

            // ===== Anggota =====
            // ['ketenagakerjaan', 'Status kedudukan dalam pekerjaan utama', 'pilihan', true,
            //     ['Berusaha sendiri', 'Berusaha dibantu buruh', 'Buruh/karyawan/pegawai swasta',
            //      'ASN/TNI/Polri/BUMN/BUMD/pejabat negara/kades', 'Pekerja bebas', 'Pekerja keluarga/tidak dibayar'],
            //     ['kedudukan']],

            // ===== Aset =====
            ['aset_bergerak', 'Jumlah tabung gas 3 kg', 'angka', true, [], ['tabung gas 3']],
        ];

        // [kata kunci pencarian, opsi sesuai form, hanya_tambah]
        $sinkron = [
            [['=status kepemilikan bangunan tempat tinggal yang ditempati', 'status kepemilikan bangunan'],
                ['Milik sendiri', 'Kontrak/sewa', 'Bebas sewa', 'Dinas', 'Lainnya'], false],
            [['=jenis lantai terluas', 'jenis lantai'],
                ['Marmer/granit', 'Keramik', 'Parket/vinil/karpet', 'Ubin/tegel/teraso', 'Kayu/papan',
                 'Semen/bata merah', 'Bambu', 'Tanah', 'Lainnya'], false],
            [['=kondisi dinding'], ['Baik', 'Rusak ringan', 'Rusak sedang', 'Rusak berat'], false],
            [['=jenis atap terluas', 'jenis atap'],
                ['Beton', 'Genteng', 'Seng', 'Asbes', 'Kayu/sirap', 'Bambu',
                 'Jerami/ijuk/daun-daunan/rumbia', 'Lainnya'], false],
            [['=sumber air minum utama', 'sumber air minum'],
                ['Air kemasan bermerek', 'Air isi ulang', 'Leding', 'Sumur bor/pompa', 'Sumur terlindung',
                 'Sumur tak terlindung', 'Mata air terlindung', 'Mata air tak terlindung',
                 'Air permukaan (sungai/danau/waduk/kolam/irigasi)', 'Air hujan', 'Lainnya'], false],
            [['=sumber penerangan utama', 'sumber penerangan'],
                ['Listrik PLN dengan meteran', 'Listrik PLN tanpa meteran', 'Listrik Non-PLN', 'Bukan listrik'], false],
            [['=daya listrik terpasang', 'daya listrik'],
                ['450 watt', '900 watt', '1.300 watt', '2.200 watt', '> 2.200 watt'], false],
            [['=status disabilitas', 'disabilitas'], ['Disabilitas intelektual'], true],
        ];

        DB::transaction(function () use ($baru, $sinkron) {
            $this->tambahParameter($baru);
            $this->sinkronOpsi($sinkron);
        });
    }

    private function tambahParameter(array $baru): void
    {
        $urutan = (int) GcKriteriaParameter::max('urutan');

        foreach ($baru as $row) {
            [$kelompok, $nama, $tipe, $wajib] = $row;
            $opsi = $row[4] ?? [];
            $kunci = array_merge(['=' . $nama], $row[5] ?? []);
            $catatan = $row[6] ?? null;

            if ($ada = $this->cari($kunci)) {
                $this->command?->line("Sudah ada, dilewati: {$nama}" . ($ada->nama !== $nama ? " (cocok dengan \"{$ada->nama}\")" : ''));
                continue;
            }

            $param = GcKriteriaParameter::create([
                'kelompok' => $kelompok,
                'urutan' => ++$urutan,
                'nama' => $nama,
                'bobot' => 0,
                'tipe_input' => $tipe,
                'sumber' => null,
                'target' => GcKriteriaParameter::targetBawaan($kelompok, $nama),
                'pbi_parameter_id' => null,
                'keterangan' => trim('Dari form Pendataan Keluarga terbaru; bobot' . ($opsi ? ' dan nilai opsi' : '')
                    . ' belum ditentukan. ' . $catatan),
                'aktif' => true,
                'wajib' => $wajib,
            ]);

            if ($param->butuhOpsi()) {
                foreach ($opsi as $j => $label) {
                    $param->opsis()->create(['label' => $label, 'nilai' => null, 'urutan' => $j + 1]);
                }
            }

            $this->command?->info("Ditambahkan: {$nama}");
        }
    }

    private function sinkronOpsi(array $sinkron): void
    {
        foreach ($sinkron as [$kunci, $opsiForm, $hanyaTambah]) {
            $param = $this->cari($kunci);
            if (! $param) {
                $this->command?->warn('Parameter tidak ditemukan untuk: ' . implode(' | ', $kunci));
                continue;
            }

            $param->load('opsis');
            $urutan = $hanyaTambah ? (int) $param->opsis->max('urutan') : 0;
            $ditambah = [];

            foreach ($opsiForm as $label) {
                $row = $param->opsis->first(fn ($o) => $this->norm($o->label) === $this->norm($label));
                if ($row) {
                    if (! $hanyaTambah) {
                        $row->update(['urutan' => ++$urutan]);
                    }
                } else {
                    $param->opsis()->create(['label' => $label, 'nilai' => null, 'urutan' => ++$urutan]);
                    $ditambah[] = $label;
                }
            }

            $usang = [];
            if (! $hanyaTambah) {
                $formNorm = array_map(fn ($l) => $this->norm($l), $opsiForm);
                foreach ($param->opsis as $o) {
                    if (in_array($this->norm($o->label), $formNorm, true)) {
                        continue;
                    }
                    $usang[] = $o->label;
                    self::HAPUS_OPSI_LAMA ? $o->delete() : $o->update(['urutan' => ++$urutan]);
                }
            }

            $this->command?->info("{$param->nama}: +" . count($ditambah) . ' opsi baru'
                . ($ditambah ? ' (' . implode(', ', $ditambah) . ')' : ''));
            if ($usang) {
                $this->command?->warn('   Opsi lama tidak ada di form'
                    . (self::HAPUS_OPSI_LAMA ? ' (DIHAPUS)' : ' (dipindah ke bawah)') . ': ' . implode(', ', $usang));
            }
            if ($param->pbi_parameter_id && $ditambah) {
                $this->command?->warn('   Terhubung ke skor PBI: nilai opsi baru perlu diisi di menu master.');
            }
        }
    }

    /** Kata kunci berawalan "=" = nama persis; selain itu = mengandung. */
    private function cari(array $kunci): ?GcKriteriaParameter
    {
        foreach ($kunci as $k) {
            $k = mb_strtolower(trim($k));
            $p = str_starts_with($k, '=')
                ? GcKriteriaParameter::whereRaw('LOWER(TRIM(nama)) = ?', [substr($k, 1)])->first()
                : GcKriteriaParameter::whereRaw('LOWER(nama) LIKE ?', ['%' . $k . '%'])->first();
            if ($p) {
                return $p;
            }
        }

        return null;
    }

    private function norm(string $s): string
    {
        return mb_strtolower(trim($s));
    }
}