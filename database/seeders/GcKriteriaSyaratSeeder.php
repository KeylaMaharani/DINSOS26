<?php

namespace Database\Seeders;

use App\Models\GcKriteriaParameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Jalankan: php artisan db:seed --class=GcKriteriaSyaratSeeder */
class GcKriteriaSyaratSeeder extends Seeder
{
    /** true = syarat yang sudah terpasang ditimpa lagi (hasil edit Super Admin hilang). */
    private const TIMPA = false;

    /** true = anak ditandai wajib. Nyalakan SETELAH MasyarakatController ikut mengabaikan field tersembunyi. */
    private const ATUR_WAJIB = false;

    /** true = urutan global disusun ulang agar anak tepat di bawah induknya. */
    private const SUSUN_ULANG = true;

    public function run(): void
    {
        DB::transaction(function () {
            $this->tambahPendapatanPekerjaan();

            $bangunan = 'Jenis bangunan tempat tinggal';
            $milik = 'Status kepemilikan bangunan tempat tinggal yang ditempati';
            $lampu = 'Sumber penerangan utama';
            $usaha = 'Apakah ada pendapatan keluarga dari usaha (offline/online)?';
            $kerja = 'Apakah ada pendapatan keluarga dari pekerjaan?';

            // [nama anak, nama induk, jawaban pemicu, wajib bila tampil]
            $syarat = [
                ['Selain keluarga Anda, apakah ada keluarga lain yang tinggal di rumah ini?', $bangunan, ['Rumah tunggal', 'Rumah deret'], true],
                ['Nama gedung/tower tempat tinggal', $bangunan, ['Apartemen', 'Rumah susun'], true],
                ['Nama/nomor lantai tempat tinggal', $bangunan, ['Apartemen', 'Rumah susun'], true],
                ['Jenis bangunan lainnya (sebutkan)', $bangunan, ['Lainnya'], true],

                ['Bukti kepemilikan tanah bangunan tempat tinggal', $milik, ['Milik sendiri'], true],
                ['Perkiraan harga sewa/nilai bangunan tempat tinggal per bulan (Rp)', $milik, ['Milik sendiri', 'Kontrak/sewa', 'Bebas sewa', 'Dinas'], true],
                ['Luas lantai bangunan tempat tinggal (m²)', $milik, ['Kontrak/sewa', 'Bebas sewa', 'Dinas', 'Lainnya'], true],
                ['Status kepemilikan lainnya (sebutkan)', $milik, ['Lainnya'], true],

                ['Jumlah meteran listrik yang terpasang', $lampu, ['Listrik PLN dengan meteran'], true],
                ['Daya listrik terpasang', $lampu, ['Listrik PLN dengan meteran'], true],
                // Form: "minimal salah satu" (ID pelanggan atau No meteran) -> tidak diwajibkan per field
                ['ID Pelanggan PLN / No Meteran Listrik', $lampu, ['Listrik PLN dengan meteran'], false],
                ['Rata-rata pengeluaran listrik sebulan (Rp)', $lampu, ['Listrik PLN tanpa meteran', 'Listrik Non-PLN'], true],

                ['Total pendapatan keluarga dari usaha tiap bulan (Rp)', $usaha, ['Ya'], true],
                ['Total pendapatan keluarga dari pekerjaan tiap bulan (Rp)', $kerja, ['Ya'], true],
            ];

            $posisi = [];

            foreach ($syarat as $i => [$namaAnak, $namaInduk, $labels, $wajib]) {
                $anak = $this->cari($namaAnak);
                $induk = $this->cari($namaInduk);

                if (! $anak || ! $induk) {
                    $this->command?->warn('Tidak ditemukan: ' . (! $anak ? $namaAnak : $namaInduk));
                    continue;
                }

                $posisi[$anak->id] = $i;

                $induk->loadMissing('opsis');
                $ada = $induk->opsis->map(fn ($o) => mb_strtolower(trim($o->label)))->all();
                foreach ($labels as $l) {
                    if (! in_array(mb_strtolower(trim($l)), $ada, true)) {
                        $this->command?->warn("   Opsi \"{$l}\" tidak ada di induk \"{$induk->nama}\", tambahkan lewat menu master.");
                    }
                }

                if ($anak->induk_id && ! self::TIMPA) {
                    $this->command?->line("Syarat sudah ada, dilewati: {$anak->nama}");
                    continue;
                }

                $anak->update(['induk_id' => $induk->id, 'induk_nilai' => $labels]
                    + (self::ATUR_WAJIB ? ['wajib' => $wajib] : []));

                $this->command?->info("{$anak->nama} <- {$induk->nama}: " . implode(', ', $labels));
            }

            if (self::SUSUN_ULANG) {
                $this->susunUlang($posisi);
            }
        });
    }

    private function tambahPendapatanPekerjaan(): void
    {
        $urutan = (int) GcKriteriaParameter::max('urutan');

        $baru = [
            ['Apakah ada pendapatan keluarga dari pekerjaan?', 'pilihan', ['Ya', 'Tidak']],
            ['Total pendapatan keluarga dari pekerjaan tiap bulan (Rp)', 'angka', []],
        ];

        foreach ($baru as [$nama, $tipe, $opsi]) {
            if ($this->cari([$nama])) {
                continue;
            }

            $p = GcKriteriaParameter::create([
                'kelompok' => 'keluarga',
                'urutan' => ++$urutan,
                'nama' => $nama,
                'bobot' => 0,
                'tipe_input' => $tipe,
                'sumber' => null,
                'target' => GcKriteriaParameter::targetBawaan('keluarga', $nama),
                'pbi_parameter_id' => null,
                'keterangan' => 'Dari form Pendataan Keluarga terbaru; bobot belum ditentukan.',
                'aktif' => true,
                'wajib' => true,
            ]);

            foreach ($opsi as $j => $label) {
                $p->opsis()->create(['label' => $label, 'nilai' => null, 'urutan' => $j + 1]);
            }
            $this->command?->info("Ditambahkan: {$nama}");
        }
    }

    /** Anak dipindah tepat di bawah induknya (selama satu kelompok), urutan antaranak mengikuti daftar di atas. */
    private function susunUlang(array $posisi): void
    {
        $semua = GcKriteriaParameter::orderBy('urutan')->orderBy('id')->get();
        $byId = $semua->keyBy('id');

        $punyaInduk = fn ($p) => $p->induk_id
            && $byId->has($p->induk_id)
            && $byId[$p->induk_id]->kelompok === $p->kelompok;

        $anak = $semua->filter($punyaInduk)
            ->sortBy(fn ($p) => $posisi[$p->id] ?? 999)
            ->groupBy('induk_id');

        $urut = [];
        $tulis = function ($p) use (&$tulis, &$urut, $anak) {
            $urut[] = $p->id;
            foreach ($anak->get($p->id, collect()) as $c) {
                $tulis($c);
            }
        };

        foreach ($semua as $p) {
            if (! $punyaInduk($p)) {
                $tulis($p);
            }
        }

        foreach ($urut as $i => $id) {
            GcKriteriaParameter::whereKey($id)->update(['urutan' => $i + 1]);
        }
    }

    private function cari(array|string $kunci): ?GcKriteriaParameter
    {
        $nama = mb_strtolower(trim(is_array($kunci) ? $kunci[0] : $kunci));

        return GcKriteriaParameter::whereRaw('LOWER(TRIM(nama)) = ?', [$nama])->first();
    }
}