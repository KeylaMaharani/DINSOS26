<?php

namespace Database\Seeders;

use App\Models\GcKriteriaParameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Menyusun ulang tab "Keterangan Sosial Ekonomi" (kelompok `keluarga`) mengikuti dokumen (Screenshot 5-33):
 *   A. Jenis Bangunan & Jumlah Penghuni            (urutan 100-199)
 *   B. Kepemilikan, Bukti Tanah, Nilai & Luas       (urutan 200-299)
 *   C. Kondisi Fisik & Sanitasi / Air / Listrik     (urutan 300-399)
 *   D. Pengeluaran & Pendapatan Keluarga            (urutan 400-499)
 *   E. Keterangan tambahan (khusus PBI, tetap tampil) (urutan 500-899)
 *   Sisa parameter lama                             (urutan 900+, TIDAK dihapus)
 *
 * PERUBAHAN dari versi sebelumnya:
 *  1. Parameter yang sudah dibuat GcKriteriaSyaratSeeder (nama lama) DICOCOKKAN lewat 'cari', jadi tidak dobel.
 *  2. Parameter yang cocok tetapi berada di kelompok lain DIPINDAH ke kelompok `keluarga`.
 *  3. induk_nilai disimpan persis sesuai label opsi di database (huruf besar/kecil mengikuti DB),
 *     sehingga cocok dengan skrip gc-syarat & validasi controller.
 *  4. Label opsi lama bisa diganti nama di tempat ('rename_opsi'), mis. Ya/Tidak -> Ada/Tidak Ada,
 *     dan jawaban warga yang sudah tersimpan ikut diubah.
 *  5. Pertanyaan khusus PBI (status lahan, kondisi atap, dst.) masuk blok E, bukan tercampur di D.
 *
 * Jalankan:  php artisan db:seed --class=GcSosialEkonomiSeeder
 * Pertama kali biarkan DRY_RUN = true, baca laporannya, lalu ubah ke false dan jalankan lagi.
 * JANGAN jalankan GcKriteriaSyaratSeeder sesudahnya dengan SUSUN_ULANG = true (urutan blok akan tertimpa).
 */
class GcSosialEkonomiSeeder extends Seeder
{
    /** true = simulasi saja: semua perubahan di-rollback, laporan tetap tampil. */
    private const DRY_RUN = false;

    /** true = pertanyaan sisa (tidak ada di dokumen, mis. Jenis kloset) dinonaktifkan agar tidak tampil di form warga. */
    private const NONAKTIFKAN_SISA = true;

    /** true = pilihan jawaban lama yang tidak ada di dokumen DIHAPUS dari dropdown (jawaban warga lama tidak diubah). */
    private const HAPUS_OPSI_LAMA = true;

    private const KELOMPOK = 'keluarga';

    private function norm($t): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $t)));
    }

    public function run(): void
    {
        $lap = [];

        DB::beginTransaction();
        try {
            $semua = GcKriteriaParameter::orderBy('urutan')->orderBy('id')->get();
            $disentuh = [];
            $id = [];
            $hitung = [];

            // ---- Tahap 1: buat / pakai parameter, urutan, opsi ----
            foreach ($this->spec() as $s) {
                $kandidat = array_map([$this, 'norm'], array_filter(array_merge([$s['nama'] ?? null], $s['cari'] ?? [])));
                $p = $this->temukan($semua, $kandidat, $disentuh);
                $baru = false;

                if (! $p) {
                    if (! empty($s['adopsi_saja'])) {
                        $lap[] = '[LEWAT] tidak ditemukan di master: ' . implode(' / ', $s['cari']);
                        continue;
                    }

                    $p = new GcKriteriaParameter([
                        'kelompok' => self::KELOMPOK,
                        'nama' => $s['nama'],
                        'tipe_input' => $s['tipe'],
                        'bobot' => 0,
                        'target' => 'gc',
                        'aktif' => true,
                        'wajib' => $s['wajib'] ?? false,
                    ]);
                    $baru = true;
                } else {
                    if ($p->kelompok !== self::KELOMPOK) {
                        $lap[] = "[PINDAH] {$p->nama}: kelompok {$p->kelompok} -> " . self::KELOMPOK;
                        $p->kelompok = self::KELOMPOK;
                    }
                    if (! empty($s['ganti_nama']) && ! empty($s['nama'])) {
                        $p->nama = $s['nama'];
                    }
                    if (! empty($s['target_bila_ada'])) {
                        $p->target = $s['target_bila_ada'];
                    }
                    if (! empty($s['pasti_aktif'])) {
                        $p->aktif = true;
                    }
                    if (! empty($s['nonaktifkan']) && $p->aktif) {
                        $p->aktif = false;
                        $lap[] = "[NONAKTIF] {$p->nama}: dihilangkan dari form warga (data lama tidak dihapus)";
                    }
                }

                $hitung[$s['blok']] = ($hitung[$s['blok']] ?? 0) + 5;
                $p->urutan = $s['blok'] + $hitung[$s['blok']];
                $p->save();

                $disentuh[] = $p->id;
                $id[$s['key']] = $p->id;
                $lap[] = ($baru ? '[BARU]  ' : '[PAKAI] ') . $p->nama . '  (urutan ' . $p->urutan . ')';

                if (! empty($s['opsi'])) {
                    $this->sinkronOpsi($p->id, $s['opsi'], $s['rename_opsi'] ?? [], $lap);
                }

                if (! empty($s['hapus_opsi'])) {
                    $this->hapusOpsi($p->id, $s['hapus_opsi'], $lap);
                }
            }

            // ---- Tahap 2: syarat tampil (induk), memakai label opsi PERSIS seperti di database ----
            foreach ($this->spec() as $s) {
                if (empty($s['induk']) || ! isset($id[$s['key']])) {
                    continue;
                }

                [$kunciInduk, $nilai] = $s['induk'];

                if (! isset($id[$kunciInduk])) {
                    $lap[] = "[WARN] induk '{$kunciInduk}' tidak ada, syarat untuk '{$s['key']}' dilewati";
                    continue;
                }

                $petaLabel = DB::table('gc_kriteria_opsis')->where('parameter_id', $id[$kunciInduk])
                    ->pluck('label')->mapWithKeys(fn ($l) => [$this->norm($l) => $l])->all();

                $aktual = [];
                foreach ($nilai as $n) {
                    if (isset($petaLabel[$this->norm($n)])) {
                        $aktual[] = $petaLabel[$this->norm($n)];
                    } else {
                        $lap[] = "[WARN] '{$s['key']}' tampil bila induk = '{$n}', tetapi opsi itu tidak ada di induk. Cek penulisan label.";
                    }
                }

                if (! $aktual) {
                    continue;
                }

                $m = GcKriteriaParameter::find($id[$s['key']]);
                $m->induk_id = $id[$kunciInduk];
                $m->induk_nilai = $aktual;
                $m->save();
            }

            // ---- Tahap 3: parameter kelompok keluarga yang tidak disebut spec -> ke akhir (tidak dihapus) ----
            $sisa = GcKriteriaParameter::where('kelompok', self::KELOMPOK)
                ->whereNotIn('id', $disentuh)->orderBy('urutan')->orderBy('id')->get();
            foreach ($sisa as $i => $x) {
                $x->urutan = 900 + $i * 10;
                if ($x->aktif && self::NONAKTIFKAN_SISA) {
                    $x->aktif = false;
                }
                $x->save();
                $lap[] = "[SISA]  {$x->nama}  (target: {$x->target}, aktif: " . ($x->aktif ? 'ya' : 'tidak') . ')';
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        self::DRY_RUN ? DB::rollBack() : DB::commit();

        foreach ($lap as $l) {
            $this->command?->line($l);
        }

        $this->command?->warn(self::DRY_RUN
            ? 'DRY RUN: tidak ada perubahan yang disimpan. Ubah DRY_RUN menjadi false untuk menyimpan.'
            : 'Selesai dan tersimpan.');
    }

    /** Cari parameter: utamakan kelompok keluarga, lalu kelompok lain. Satu parameter hanya dipakai sekali. */
    private function temukan($semua, array $kandidat, array $disentuh): ?GcKriteriaParameter
    {
        $cocok = fn ($x) => ! in_array($x->id, $disentuh, true) && in_array($this->norm($x->nama), $kandidat, true);

        return $semua->first(fn ($x) => $x->kelompok === self::KELOMPOK && $cocok($x))
            ?? $semua->first($cocok);
    }

    /**
     * Urutkan opsi sesuai dokumen. Label lama dicocokkan tanpa beda huruf besar/kecil dan TIDAK diganti
     * (supaya padanan skor PBI tidak rusak). Label yang belum ada ditambahkan; label lama yang tidak ada
     * di dokumen dibiarkan (dipindah ke akhir). $rename = [label lama => label baru] diganti di tempat.
     */
    /** Hapus opsi tertentu dari sebuah pertanyaan (mis. "Milik orang lain"). Jawaban lama yang memakainya hanya dilaporkan. */
    private function hapusOpsi(int $pid, array $labels, array &$lap): void
    {
        foreach ($labels as $l) {
            $baris = DB::table('gc_kriteria_opsis')->where('parameter_id', $pid)->get()
                ->filter(fn ($o) => $this->norm($o->label) === $this->norm($l));

            foreach ($baris as $o) {
                $dipakai = DB::table('pbi_apbd_gc_jawabans')->where('parameter_id', $pid)->where('jawaban', $o->label)->count();
                DB::table('gc_kriteria_opsis')->where('id', $o->id)->delete();
                $lap[] = "           - opsi dihapus: {$o->label}" . ($dipakai ? " (PERHATIAN: {$dipakai} jawaban lama masih memakainya)" : '');
            }
        }
    }

    private function sinkronOpsi(int $pid, array $labels, array $rename, array &$lap): void
    {
        foreach ($rename as $lamaLabel => $baruLabel) {
            $row = DB::table('gc_kriteria_opsis')->where('parameter_id', $pid)
                ->whereRaw('LOWER(TRIM(label)) = ?', [$this->norm($lamaLabel)])->first();
            $sudah = DB::table('gc_kriteria_opsis')->where('parameter_id', $pid)
                ->whereRaw('LOWER(TRIM(label)) = ?', [$this->norm($baruLabel)])->exists();

            if ($row && ! $sudah) {
                DB::table('gc_kriteria_opsis')->where('id', $row->id)
                    ->update(['label' => $baruLabel, 'updated_at' => now()]);
                $n = DB::table('pbi_apbd_gc_jawabans')->where('parameter_id', $pid)
                    ->where('jawaban', $row->label)->update(['jawaban' => $baruLabel]);
                $lap[] = "           ~ opsi diganti nama: {$row->label} -> {$baruLabel} ({$n} jawaban warga ikut diubah)";
            }
        }

        $lama = DB::table('gc_kriteria_opsis')->where('parameter_id', $pid)->get();
        $dipakai = [];

        foreach ($labels as $i => $l) {
            $row = $lama->first(fn ($o) => $this->norm($o->label) === $this->norm($l));

            if ($row) {
                DB::table('gc_kriteria_opsis')->where('id', $row->id)
                    ->update(['urutan' => ($i + 1) * 10, 'updated_at' => now()]);
                $dipakai[] = $row->id;
            } else {
                DB::table('gc_kriteria_opsis')->insert([
                    'parameter_id' => $pid,
                    'label' => $l,
                    'nilai' => null,
                    'urutan' => ($i + 1) * 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $lap[] = "           + opsi baru: {$l}";
            }
        }

        foreach ($lama as $o) {
            if (in_array($o->id, $dipakai, true)) {
                continue;
            }

            if (self::HAPUS_OPSI_LAMA) {
                $n = DB::table('pbi_apbd_gc_jawabans')->where('parameter_id', $pid)->where('jawaban', $o->label)->count();
                DB::table('gc_kriteria_opsis')->where('id', $o->id)->delete();
                $lap[] = "           - opsi lama dihapus (tidak ada di dokumen): {$o->label}" . ($n ? " (PERHATIAN: {$n} jawaban lama memakainya)" : '');
            } else {
                DB::table('gc_kriteria_opsis')->where('id', $o->id)->update(['urutan' => 1000 + $o->id]);
                $lap[] = "           ! opsi lama tidak ada di dokumen (dibiarkan): {$o->label}";
            }
        }
    }

    /**
     * Nama BARU memakai "[Rp]" / "[m²]" karena PbiGcForm::isUang / isLuas mengenali field dari tanda ini; nama LAMA di "cari" memakai kurung biasa
     * (PbiGcForm::isUang / isLuas mengenali field dari nama).
     */
    private function spec(): array
    {
        $meteran = ['penerangan', ['Listrik PLN dengan meteran']];
        $adaListrik = ['penerangan', ['Listrik PLN dengan meteran', 'Listrik PLN tanpa meteran', 'Listrik Non-PLN']];
        $adaTidak = ['Ya' => 'Ada', 'Tidak' => 'Tidak Ada'];

        return [
            // =========================================================
            // A. Jenis Bangunan Tempat Tinggal & Jumlah Penghuni
            // =========================================================
            ['key' => 'jenis_bangunan', 'blok' => 100, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Apa jenis bangunan tempat tinggal yang Anda tempati?',
                'cari' => ['Jenis bangunan tempat tinggal'], 'ganti_nama' => true,
                'opsi' => ['Rumah Tunggal', 'Apartemen', 'Rumah Susun', 'Rumah Deret', 'Lainnya']],

            ['key' => 'keluarga_lain', 'blok' => 100, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Selain keluarga Anda, apakah ada keluarga lain yang tinggal di rumah/tempat tinggal ini?',
                'cari' => ['Selain keluarga Anda, apakah ada keluarga lain yang tinggal di rumah ini?'], 'ganti_nama' => true,
                'opsi' => ['Ya', 'Tidak'],
                'induk' => ['jenis_bangunan', ['Rumah Tunggal', 'Rumah Deret']]],

            ['key' => 'nama_gedung', 'blok' => 100, 'tipe' => 'teks', 'wajib' => true,
                'nama' => 'Apa nama gedung/tower tempat tinggal Anda?',
                'cari' => ['Nama gedung/tower tempat tinggal'], 'ganti_nama' => true,
                'induk' => ['jenis_bangunan', ['Apartemen', 'Rumah Susun']]],

            ['key' => 'nomor_lantai', 'blok' => 100, 'tipe' => 'teks', 'wajib' => true,
                'nama' => 'Nama/nomor lantai tempat tinggal Anda',
                'cari' => ['Nama/nomor lantai tempat tinggal'], 'ganti_nama' => true,
                'induk' => ['jenis_bangunan', ['Apartemen', 'Rumah Susun']]],

            ['key' => 'jenis_lainnya', 'blok' => 100, 'tipe' => 'teks', 'wajib' => true,
                'nama' => 'Jenis bangunan lainnya (sebutkan)',
                'induk' => ['jenis_bangunan', ['Lainnya']]],

            ['key' => 'jumlah_orang', 'blok' => 100, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Berapa orang yang tinggal dalam 1 rumah/tempat tinggal Anda?',
                'cari' => ['Jumlah keluarga yang tinggal dalam satu rumah'], 'ganti_nama' => true],

            // =========================================================
            // B. Status Kepemilikan, Bukti Tanah, Nilai Sewa & Luas Lantai
            // =========================================================
            ['key' => 'status_milik', 'blok' => 200, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Status kepemilikan bangunan tempat tinggal yang ditempati',
                'opsi' => ['Milik Sendiri', 'Kontrak/Sewa', 'Bebas Sewa', 'Dinas', 'Lainnya'],
                'hapus_opsi' => ['Milik orang lain']],

            ['key' => 'bukti_tanah', 'blok' => 200, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Apa bukti kepemilikan tanah bangunan tempat tinggal yang Anda tempati?',
                'cari' => ['Bukti kepemilikan tanah bangunan tempat tinggal'], 'ganti_nama' => true,
                'opsi' => ['SHM', 'Sertifikat selain SHM (SHGB, SHSRS)', 'Surat bukti lainnya (Girik, Letter C, dll)', 'Tidak Punya'],
                'induk' => ['status_milik', ['Milik Sendiri']]],

            ['key' => 'status_lainnya', 'blok' => 200, 'tipe' => 'teks', 'wajib' => true,
                'nama' => 'Status kepemilikan lainnya (sebutkan)',
                'induk' => ['status_milik', ['Lainnya']]],

            ['key' => 'nilai_bangunan', 'blok' => 200, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Berapa perkiraan harga sewa bangunan tempat tinggal Anda selama sebulan? [Rp]',
                'cari' => ['Berapa perkiraan harga sewa/kontrak bangunan tempat tinggal Anda selama sebulan? [Rp]',
                    'Perkiraan harga sewa/nilai bangunan tempat tinggal per bulan (Rp)'], 'ganti_nama' => true,
                'induk' => ['status_milik', ['Milik Sendiri', 'Kontrak/Sewa', 'Bebas Sewa', 'Dinas']]],

            ['key' => 'luas_lantai', 'blok' => 200, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Berapa luas lantai bangunan tempat tinggal Anda? (Luas bangunan yang ditempati dan digunakan untuk keperluan sehari-hari sebatas atap rumah) [m²]',
                'cari' => ['Luas lantai bangunan tempat tinggal (m²)'], 'ganti_nama' => true,
                'induk' => ['status_milik', ['Kontrak/Sewa', 'Bebas Sewa', 'Dinas', 'Lainnya']]],

            // =========================================================
            // C. Kondisi Fisik Bangunan & Fasilitas Sanitasi / Air / Listrik
            // =========================================================
            ['key' => 'lantai', 'blok' => 300, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Apa jenis lantai terluas di tempat tinggal Anda?',
                'cari' => ['Jenis lantai terluas'], 'ganti_nama' => true,
                'opsi' => ['Marmer/granit', 'Keramik', 'Parket/vinil/karpet', 'Ubin/tegel/teraso', 'Kayu/papan',
                    'Semen/bata merah', 'Bambu', 'Tanah', 'Lainnya']],

            // Tidak ada di dokumen, tetapi berpadanan PBI (skor): hanya diurutkan.
            ['key' => 'dinding_jenis', 'blok' => 300, 'adopsi_saja' => true, 'nonaktifkan' => true, 'cari' => ['Jenis dinding terluas']],

            ['key' => 'dinding_kondisi', 'blok' => 300, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Bagaimana kondisi dinding di tempat tinggal Anda?',
                'cari' => ['Kondisi dinding'], 'ganti_nama' => true, 'target_bila_ada' => 'pbi_gc',
                'opsi' => ['Baik', 'Rusak Ringan', 'Rusak Sedang', 'Rusak Berat'],
                'hapus_opsi' => ['Bagus/kualitas tinggi', 'Jelek/kualitas rendah']],

            ['key' => 'atap', 'blok' => 300, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Apa jenis atap terluas di tempat tinggal Anda?',
                'cari' => ['Jenis atap terluas'], 'ganti_nama' => true,
                'opsi' => ['Beton', 'Genteng', 'Seng', 'Asbes', 'Kayu/sirap', 'Bambu', 'Jerami/ijuk/daun-daunan/rumbia', 'Lainnya']],

            // Label BAB sengaja TIDAK disentuh: dipakai PETA_BAB di MasyarakatController untuk skor PBI.
            ['key' => 'bab', 'blok' => 300, 'adopsi_saja' => true, 'pasti_aktif' => true,
                'nama' => 'Di tempat tinggal Anda, apakah memiliki fasilitas tempat buang air besar (BAB) dan siapa saja yang menggunakan?',
                'cari' => ['Kepemilikan fasilitas BAB'], 'ganti_nama' => true],

            ['key' => 'air', 'blok' => 300, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Apa sumber air minum utama di tempat tinggal Anda?',
                'cari' => ['Sumber air minum utama'], 'ganti_nama' => true,
                'opsi' => ['Air kemasan bermerek', 'Air isi ulang', 'Leding', 'Sumur bor/pompa', 'Sumur terlindung',
                    'Sumur tak terlindung', 'Mata air terlindung', 'Mata air tak terlindung',
                    'Air permukaan (sungai/danau/waduk/kolam/irigasi)', 'Air hujan', 'Lainnya']],

            ['key' => 'bahan_bakar', 'blok' => 300, 'adopsi_saja' => true, 'nonaktifkan' => true, 'cari' => ['Bahan bakar/energi utama untuk memasak']],

            ['key' => 'penerangan', 'blok' => 300, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Apa sumber penerangan utama di tempat tinggal Anda?',
                'cari' => ['Sumber penerangan utama'], 'ganti_nama' => true,
                'opsi' => ['Listrik PLN dengan meteran', 'Listrik PLN tanpa meteran', 'Listrik Non-PLN', 'Bukan listrik']],

            ['key' => 'jml_meteran', 'blok' => 300, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Berapa jumlah meteran listrik yang terpasang di rumah Anda?',
                'cari' => ['Jumlah meteran listrik yang terpasang'], 'ganti_nama' => true,
                'induk' => $meteran],

            ['key' => 'daya', 'blok' => 300, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Berapa daya pada meteran listrik ke-1 yang terpasang?',
                'cari' => ['Daya listrik terpasang'], 'ganti_nama' => true, 'target_bila_ada' => 'pbi_gc', 'pasti_aktif' => true,
                'opsi' => ['450 watt', '900 watt', '1.300 watt', '2.200 watt', '> 2.200 watt'],
                'induk' => $meteran],

            // "Minimal salah satu" diisi -> divalidasi di controller (lihat patch), bukan wajib per field.
            ['key' => 'id_pelanggan', 'blok' => 300, 'tipe' => 'teks', 'wajib' => false,
                'nama' => 'ID Pelanggan PLN',
                'cari' => ['ID Pelanggan PLN / No Meteran Listrik'], 'ganti_nama' => true,
                'induk' => $meteran],

            ['key' => 'no_meteran', 'blok' => 300, 'tipe' => 'teks', 'wajib' => false,
                'nama' => 'No Meteran Listrik',
                'induk' => $meteran],

            // =========================================================
            // D. Rincian Pengeluaran & Pendapatan Keluarga
            // =========================================================
            ['key' => 'listrik_bulan', 'blok' => 400, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Berapa rata-rata pengeluaran listrik selama sebulan? (Dapat menggunakan pengeluaran sebulan terakhir) [Rp]',
                'cari' => ['Rata-rata pengeluaran listrik sebulan (Rp)'], 'ganti_nama' => true,
                'induk' => ['penerangan', ['Listrik PLN tanpa meteran', 'Listrik Non-PLN']]],

            ['key' => 'pulsa', 'blok' => 400, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Berapa rata-rata pengeluaran pulsa untuk seluruh anggota keluarga selama sebulan? (Dapat menggunakan pengeluaran sebulan terakhir) [Rp]',
                'cari' => ['Rata-rata pengeluaran pulsa seluruh anggota keluarga sebulan (Rp)'], 'ganti_nama' => true],

            ['key' => 'internet', 'blok' => 400, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Berapa rata-rata pengeluaran internet untuk seluruh anggota keluarga selama sebulan? (Dapat menggunakan pengeluaran sebulan terakhir) [Rp]',
                'cari' => ['Rata-rata pengeluaran internet seluruh anggota keluarga sebulan (Rp)'], 'ganti_nama' => true],

            ['key' => 'makanan_minggu', 'blok' => 400, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Berapa rata-rata pengeluaran makanan keluarga selama seminggu? (Dapat menggunakan pengeluaran seminggu terakhir) [Rp]',
                'cari' => ['Rata-rata pengeluaran makanan keluarga seminggu (Rp)'], 'ganti_nama' => true],

            ['key' => 'non_makanan_bulan', 'blok' => 400, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Berapa rata-rata pengeluaran bukan makanan rutin keluarga selama sebulan? (Dapat menggunakan pengeluaran sebulan terakhir) [Rp]',
                'cari' => ['Rata-rata pengeluaran bukan makanan rutin keluarga sebulan (Rp)'], 'ganti_nama' => true],

            ['key' => 'non_makanan_tahun', 'blok' => 400, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Berapa rata-rata pengeluaran bukan makanan rutin keluarga selama setahun? [Rp]',
                'cari' => ['Rata-rata pengeluaran bukan makanan rutin keluarga setahun (Rp)'], 'ganti_nama' => true],

            ['key' => 'pendapatan_kerja', 'blok' => 400, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Total pendapatan seluruh anggota keluarga dari pekerjaan, baik berupa uang maupun barang/jasa (Gaji, tunjangan, uang makan, honor, lembur, dll)',
                'cari' => ['Apakah ada pendapatan keluarga dari pekerjaan?'], 'ganti_nama' => true,
                'opsi' => ['Ada', 'Tidak Ada'], 'rename_opsi' => $adaTidak],

            ['key' => 'pendapatan_kerja_nominal', 'blok' => 400, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Total pendapatan bekerja sebulan [Rp]',
                'cari' => ['Total pendapatan keluarga dari pekerjaan tiap bulan (Rp)'], 'ganti_nama' => true,
                'induk' => ['pendapatan_kerja', ['Ada']]],

            ['key' => 'pendapatan_usaha', 'blok' => 400, 'tipe' => 'pilihan', 'wajib' => true,
                'nama' => 'Total pendapatan seluruh anggota keluarga dari usaha, baik offline maupun online (Offline seperti warung, kosan, rentenir, dll. Online seperti affiliate, online shop, endorse, youtuber, dll)',
                'cari' => ['Apakah ada pendapatan keluarga dari usaha (offline/online)?'], 'ganti_nama' => true,
                'opsi' => ['Ada', 'Tidak Ada'], 'rename_opsi' => $adaTidak],

            ['key' => 'pendapatan_usaha_nominal', 'blok' => 400, 'tipe' => 'angka', 'wajib' => true,
                'nama' => 'Total pendapatan usaha tiap bulan [Rp]',
                'cari' => ['Total pendapatan keluarga dari usaha tiap bulan (Rp)'], 'ganti_nama' => true,
                'induk' => ['pendapatan_usaha', ['Ada']]],

            // =========================================================
            // E. Keterangan tambahan: khusus PBI APBD (dipakai skor kelas kemiskinan), tidak ada di dokumen.
            //    Tetap tampil, hanya dipindah ke blok sendiri supaya tidak tercampur di A-D.
            // =========================================================
            ['key' => 'status_lahan', 'blok' => 500, 'adopsi_saja' => true, 'nonaktifkan' => true, 'cari' => ['Status lahan tempat tinggal yang ditempati']],
            ['key' => 'kondisi_atap', 'blok' => 500, 'adopsi_saja' => true, 'nonaktifkan' => true, 'cari' => ['Kondisi atap']],
            ['key' => 'cara_air', 'blok' => 500, 'adopsi_saja' => true, 'nonaktifkan' => true, 'cari' => ['Cara memperoleh air minum']],
            ['key' => 'tanggungan', 'blok' => 500, 'adopsi_saja' => true, 'nonaktifkan' => true, 'cari' => ['Jumlah tanggungan keluarga']],
            ['key' => 'roda2', 'blok' => 500, 'adopsi_saja' => true, 'nonaktifkan' => true, 'cari' => ['Kepemilikan kendaraan roda 2']],
        ];
    }
}
