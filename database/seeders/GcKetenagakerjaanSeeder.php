<?php

namespace Database\Seeders;

use App\Models\GcKeterampilan;
use App\Models\GcKriteriaOpsi;
use App\Models\GcKriteriaParameter;
use App\Models\GcLapanganUsaha;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Master ketenagakerjaan Ground Check — disamakan PERSIS dengan sistem SOLID lama.
 *
 *  - 26 Lapangan Usaha (+ contoh pekerjaan, contoh "berusaha sendiri" & "buruh/karyawan")
 *    sesuai "Rangkuman Klasifikasi Pekerjaan" (#GC_DTSEN).
 *  - Keterampilan Khusus sesuai foto dropdown (Belum Ada, Bertani, ... Teknik Sipil dan Arsitektur).
 *  - Tab C Ketenagakerjaan dirapikan menjadi 6 field persis seperti foto contoh;
 *    field lain di tab C dinonaktifkan (bukan dihapus, jadi data lama aman).
 *
 * Aman dijalankan ulang (idempotent). CATATAN: seeder ini MENIMPA isi master lapangan usaha
 * & keterampilan dengan data rujukan di bawah dan menghapus entri yang tidak ada di daftar.
 *
 * Jalankan:  php artisan db:seed --class=GcKetenagakerjaanSeeder
 */
class GcKetenagakerjaanSeeder extends Seeder
{
    /** Isi dropdown "Status dalam pekerjaan utama" — urutan & tulisan persis foto. */
    public const STATUS_PEKERJAAN = [
        'Berusaha dibantu buruh tetap/dibayar',
        'Berusaha dibantu buruh tidak tetap/tidak dibayar',
        'Berusaha sendiri',
        'Buruh/karyawan/pegawai swasta',
        'Pekerja bebas non-pertanian',
        'Pekerja bebas pertanian',
        'Pekerja keluarga/tidak dibayar',
        'PNS/TNI/Polri/BUMN/BUMD/anggota legislatif',
    ];

    /** Isi dropdown "Pendapatan sebulan terakhir" — urutan & tulisan persis foto (termasuk ejaannya). */
    public const PENDAPATAN = [
        '<1 juta per bulan',
        '> 10 juta perbulan',
        '> UMK - 10 juta per bulan',
        '>1 juta per bulan',
        'Tidak Sama Sekali',
        'UMK',
    ];

    public function run(): void
    {
        if (! GcKriteriaParameter::exists()) {
            $this->call(GcKriteriaSeeder::class);
        }

        DB::transaction(function () {
            $this->seedLapangan();
            $this->seedKeterampilan();
            $this->rapikanTabKetenagakerjaan();
        });

        $this->command?->info('Master ketenagakerjaan Ground Check selesai disinkronkan.');
    }

    /* ------------------------------------------------------------------ */
    /*  26 LAPANGAN USAHA                                                  */
    /* ------------------------------------------------------------------ */

    private function seedLapangan(): void
    {
        $idAktif = [];

        foreach ($this->dataLapangan() as $i => [$nama, $ikon, $pekerjaan, $sendiri, $buruh]) {
            $pekerjaan = array_map(fn ($x) => $this->kapital($x), $pekerjaan);

            $lap = GcLapanganUsaha::updateOrCreate(['nama' => $nama], [
                'ikon' => $ikon,
                'penjelasan' => implode(', ', $pekerjaan),
                'contoh_berusaha_sendiri' => $sendiri,
                'contoh_buruh' => $buruh,
                'urutan' => $i + 1,
                'aktif' => true,
            ]);
            $idAktif[] = $lap->id;

            $keep = [];
            foreach ($pekerjaan as $j => $namaPk) {
                $keep[] = $lap->pekerjaans()->updateOrCreate(['nama' => $namaPk], ['urutan' => $j + 1, 'aktif' => true])->id;
            }
            $lap->pekerjaans()->whereNotIn('id', $keep)->delete();
        }

        // Buang lapangan usaha lama yang bukan bagian dari 26 bidang (jawaban lama aman: tersimpan sebagai teks).
        GcLapanganUsaha::whereNotIn('id', $idAktif)->delete();
    }

    /** [nama, ikon, [contoh pekerjaan], contoh berusaha sendiri, contoh buruh/karyawan/pegawai] */
    private function dataLapangan(): array
    {
        return [
            ['Pertanian tanaman padi & palawija', '🌾', ['Petani padi', 'Petani jagung', 'Petani kedelai', 'Buruh tani'],
                'Petani yang mengelola sawah sendiri', 'Buruh tani di sawah milik orang lain'],
            ['Hortikultura', '🥦', ['Petani sayur', 'Petani buah', 'Petani cabai', 'Petani bawang merah'],
                'Petani cabai dengan lahan sendiri', 'Buruh tani di kebun hortikultura'],
            ['Perkebunan', '🌴', ['Petani kelapa sawit', 'Petani teh', 'Petani kopi', 'Petani kakao'],
                'Petani kopi dengan kebun sendiri', 'Pekerja di perkebunan teh'],
            ['Perikanan', '🐟', ['Nelayan', 'Petani tambak ikan', 'Pembudidaya lele'],
                'Nelayan kecil dengan perahu sendiri', 'Anak buah kapal (ABK) nelayan'],
            ['Peternakan', '🐔', ['Peternak ayam', 'Peternak bebek', 'Peternak sapi perah'],
                'Peternak ayam kampung dengan kandang sendiri', 'Pekerja di peternakan ayam besar'],
            ['Kehutanan & pertanian lainnya', '🌲', ['Penyadap karet', 'Penebang kayu', 'Pekerja konservasi hutan'],
                'Penyadap karet mandiri', 'Buruh di perusahaan kehutanan'],
            ['Pertambangan/penggalian', '⛏️', ['Penambang pasir', 'Penggali batu kali', 'Penambang emas rakyat'],
                'Penambang emas tradisional', 'Pekerja di tambang batu bara'],
            ['Industri pengolahan', '🏭', ['Pembuat tempe', 'Pembuat tahu', 'Pembuat kerupuk', 'Pengrajin kayu', 'Pengrajin batik',
                'Pembuatan genteng', 'Pembuatan bata merah', 'Produksi roti rumahan', 'Industri kecil sabun cuci', 'Pembuatan minyak kelapa'],
                'Pemilik usaha tempe rumahan', 'Buruh pabrik makanan'],
            ['Pengadaan listrik, gas, uap/air panas, dan udara dingin', '⚡', ['Teknisi listrik', 'Pemasang panel surya'],
                'Teknisi listrik freelance', 'Pegawai PLN'],
            ['Pengelolaan air, limbah, dan daur ulang', '🚮', ['Pemulung', 'Pengepul barang bekas', 'Pengelola bank sampah'],
                'Pengepul barang bekas', 'Buruh di pabrik daur ulang'],
            ['Konstruksi', '🏗️', ['Tukang bangunan', 'Tukang gali sumur', 'Tukang pasang keramik'],
                'Tukang bangunan mandiri', 'Pekerja di perusahaan kontraktor'],
            ['Perdagangan besar dan eceran, reparasi dan perawatan mobil dan sepeda motor', '🛍️', ['Pedagang sayur', 'Pemilik warung',
                'Pedagang pakaian di pasar', 'Pedagang elektronik bekas', 'Bengkel motor', 'Bengkel mobil', 'Montir', 'Tukang tambal ban'],
                'Pemilik bengkel motor kecil', 'Mekanik di bengkel mobil resmi'],
            ['Pengangkutan dan pergudangan', '🚚', ['Sopir truk', 'Ojek online', 'Kurir paket'],
                'Ojek pangkalan dengan motor sendiri', 'Kurir di perusahaan ekspedisi'],
            ['Penyediaan akomodasi & makan minum', '🍛', ['Pemilik warung makan', 'Juru masak warteg', 'Penjual gorengan'],
                'Pemilik warung nasi', 'Karyawan restoran'],
            ['Informasi & komunikasi', '📱', ['Penjual pulsa', 'Tukang servis HP'],
                'Pemilik konter pulsa', 'Pegawai toko HP'],
            ['Keuangan & asuransi', '🏦', ['Kolektor pinjaman harian', 'Agen asuransi'],
                'Kolektor mandiri', 'Pegawai koperasi'],
            ['Real estate', '🏠', ['Pengelola kos-kosan', 'Penjaga rumah kontrakan'],
                'Pemilik kos-kosan', 'Juru kunci rumah kontrakan'],
            ['Aktivitas profesional, ilmiah, dan teknis', '📊', ['Konsultan pajak', 'Desainer grafis freelance'], null, null],
            ['Aktivitas penyewaan & agen perjalanan', '🎫', ['Agen travel', 'Persewaan mobil'], null, null],
            ['Administrasi pemerintahan', '🏛️', ['Ketua RT dengan insentif', 'Pegawai kelurahan'], null, null],
            ['Pendidikan', '📖', ['Guru les privat', 'Tutor bimbel'], null, null],
            ['Aktivitas kesehatan & sosial', '💉', ['Perawat homecare', 'Tukang pijat'], null, null],
            ['Kesenian, hiburan, dan rekreasi', '🎭', ['Pemain musik jalanan', 'Pelukis'], null, null],
            ['Aktivitas jasa Lainnya', '✂️', ['Tukang cukur/pangkas rambut', 'Tukang jahit', 'Tukang las', 'Jasa makeup artist (MUA)',
                'Jasa spa dan pijat refleksi', 'Tukang lulur dan perawatan tubuh tradisional',
                'Guru les musik privat (misal: gitar, piano, biola)', 'Guru tari tradisional atau modern',
                'Personal trainer (pelatih kebugaran)', 'Jasa penyewaan baju adat/pengantin', 'Fotografer dan videografer untuk acara',
                'Penulis biografi/jasa penulisan artikel', 'Jasa mediator atau konsultan hukum non-formal',
                'Tukang servis elektronik (HP, laptop, TV)', 'Jasa perbaikan barang rumah tangga (lemari, meja, kursi)',
                'Jasa pembuatan kasur busa/jok motor', 'Jasa sablon kaos atau bordir pakaian'],
                'Tukang cukur panggilan keliling, MUA freelance yang menerima job wedding/event, Guru les privat yang mengajar dari rumah ke rumah, Jasa servis elektronik dengan toko kecil atau panggilan',
                'Tukang cukur di barbershop modern, Terapis spa yang bekerja di salon kecantikan, Personal trainer di pusat kebugaran (gym), Fotografer profesional di studio foto'],
            ['Aktivitas keluarga sebagai pemberi kerja', '👶', ['Babysitter (pengasuh anak di rumah)',
                'Perawat lansia/jompo (caregiver di rumah tangga)', 'Sopir pribadi (pengemudi keluarga)', 'Asisten rumah tangga (ART)',
                'Tukang cuci dan setrika baju (pembantu harian)', 'Juru masak keluarga (koki pribadi)',
                'Perawat hewan peliharaan (pet sitter)', 'Tukang kebun (merawat taman dan tanaman keluarga)',
                'Tukang bangunan rumahan (renovasi kecil-kecilan di rumah tangga)'],
                'ART freelance yang bekerja untuk beberapa keluarga, Tukang cuci dan setrika baju panggilan, Pet sitter yang menerima jasa titip hewan',
                'ART yang bekerja tetap dalam satu rumah tangga, Sopir pribadi dengan gaji bulanan, Babysitter yang direkrut melalui agen tenaga kerja'],
            ['Aktivitas badan internasional', '🌍', ['Staf LSM internasional', 'Relawan PBB'], null, null],
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  KETERAMPILAN KHUSUS (isi dropdown sesuai foto)                     */
    /* ------------------------------------------------------------------ */

    private function seedKeterampilan(): void
    {
        $daftar = [
            'Belum Ada', 'Bertani', 'Data Science dan Analisis Data', 'Desain Grafis', 'Desain Interior', 'Energi dan Lingkungan',
            'Fotografi dan Videografi', 'Jaringan dan Administrasi Sistem', 'Keamanan cyber', 'Kebugaran dan Pelatihan Personal',
            'Keperawatan dan Medis', 'Kesehatan Mental dan Konseling', 'Keuangan dan Akuntansi', 'Komunikasi Pemasaran', 'Kuliner',
            'Manajemen Proyek', 'Mengajar', 'Mengasuh anak', 'Mengemudi kendaraan besar/berat', 'Mengemudi roda 2', 'Mengemudi roda 4',
            'Menjahit', 'Nutrisi dan Dietetik', 'Pelatihan dan Pengembangan Profesional', 'Pemasaran',
            'Pemrograman dan Pengembangan Perangkat Lunak', 'Pendidikan Anak Usia Dini', 'Penerjemahan dan Interpretasi',
            'Pengajaran dan Pembelajaran', 'Pengembangan Kurikulum', 'Penulisan Kreatif dan Jurnalistik', 'Pertukangan',
            'Public Speaking dan Presentasi', 'Seni Rupa dan Ilustrasi', 'Sumber Daya Manusia', 'Teknik Elektro dan Elektronik',
            'Teknik Mesin (bengkel dll)', 'Teknik Sipil dan Arsitektur',
        ];

        $idAktif = [];
        foreach ($daftar as $i => $nama) {
            // "Belum Ada" bersifat eksklusif: tidak boleh digabung dengan pilihan lain.
            $idAktif[] = GcKeterampilan::updateOrCreate(['nama' => $nama], [
                'urutan' => $i + 1,
                'eksklusif' => $i === 0,
                'aktif' => true,
            ])->id;
        }

        GcKeterampilan::whereNotIn('id', $idAktif)->delete();
    }

    /* ------------------------------------------------------------------ */
    /*  TAB C — 6 FIELD PERSIS FOTO CONTOH                                 */
    /* ------------------------------------------------------------------ */

    private function rapikanTabKetenagakerjaan(): void
    {
        $kelompok = 'ketenagakerjaan';

        // Urutan sama dengan foto contoh. 'awalan' = pencocokan nama parameter yang sudah ada.
        $spec = [
            ['awalan' => 'apakah responden bekerja', 'nama' => 'Apakah responden bekerja/membantu bekerja selama seminggu yang lalu?',
                'tipe' => 'pilihan', 'opsi' => ['Ya', 'Tidak'], 'paksa' => true],
            ['awalan' => 'lapangan usaha', 'nama' => 'Lapangan Usaha dari pekerjaan utama',
                'tipe' => 'pilihan', 'sumber' => 'lapangan_usaha', 'keterangan' => 'Pilihan (26 bidang) dikelola di Master Lapangan Usaha.'],
            ['awalan' => 'status dalam pekerjaan', 'nama' => 'Status dalam pekerjaan utama',
                'tipe' => 'pilihan', 'opsi' => self::STATUS_PEKERJAAN, 'paksa' => true],
            ['awalan' => 'keterampilan khusus', 'nama' => 'Keterampilan khusus/sertifikat keahlian yang dimiliki',
                'tipe' => 'pilihan_ganda', 'sumber' => 'keterampilan', 'keterangan' => 'Pilihan dikelola di Master Keterampilan.'],
            ['awalan' => 'apakah responden memiliki usaha', 'nama' => 'Apakah responden memiliki usaha sendiri/bersama?',
                'tipe' => 'pilihan', 'opsi' => ['Ya', 'Tidak'], 'paksa' => true],
            ['awalan' => 'pendapatan sebulan', 'nama' => 'Pendapatan sebulan terakhir',
                'tipe' => 'pilihan', 'opsi' => self::PENDAPATAN, 'paksa' => true,
                'keterangan' => 'Pilihan disamakan dengan sistem SOLID lama. Nilai (skor) tiap pilihan diisi di Master Kriteria Ground Check.'],
        ];

        $semua = GcKriteriaParameter::where('kelompok', $kelompok)->orderBy('urutan')->orderBy('id')->get();
        $dipakai = [];
        $berurutan = [];

        foreach ($spec as $s) {
            $p = $semua->first(fn ($x) => ! in_array($x->id, $dipakai, true)
                && Str::startsWith(Str::lower(trim($x->nama)), $s['awalan']));

            if (! $p) {
                if (empty($s['sumber']) && ($s['opsi'] ?? null) === null) {
                    $this->command?->warn('Parameter "' . $s['nama'] . '" tidak ditemukan & tidak bisa dibuat otomatis (opsi belum diketahui). Tambahkan lewat Master Kriteria Ground Check.');
                    continue;
                }
                $p = GcKriteriaParameter::create([
                    'kelompok' => $kelompok,
                    'urutan' => ((int) GcKriteriaParameter::max('urutan')) + 1,
                    'nama' => $s['nama'],
                    'bobot' => 0,
                    'tipe_input' => $s['tipe'],
                    'sumber' => $s['sumber'] ?? null,
                    'aktif' => true,
                ]);
            }

            $p->nama = $s['nama'];
            $p->aktif = true;
            if (! empty($s['tipe'])) {
                $p->tipe_input = $s['tipe'];
            }
            $p->sumber = $s['sumber'] ?? null;
            if (! empty($s['keterangan'])) {
                $p->keterangan = $s['keterangan'];
            }
            $p->save();

            if ($p->sumber) {
                // Opsi berasal dari master, bukan dari gc_kriteria_opsis.
                GcKriteriaOpsi::where('parameter_id', $p->id)->delete();
            } elseif (is_array($s['opsi'] ?? null)) {
                $sudah = GcKriteriaOpsi::where('parameter_id', $p->id)->count();
                if (! empty($s['paksa']) || $sudah < 2) {
                    $this->syncOpsi($p, $s['opsi']);
                }
            }

            $dipakai[] = $p->id;
            $berurutan[] = $p;
        }

        // Field lain di tab C (mis. "Pekerjaan/jabatan utama") tidak ada di foto contoh -> nonaktifkan.
        $lain = GcKriteriaParameter::where('kelompok', $kelompok)->whereNotIn('id', $dipakai)->orderBy('urutan')->get();
        foreach ($lain as $p) {
            $p->update(['aktif' => false]);
        }

        // Pakai ulang nomor urut milik kelompok ini supaya urutan tab lain tidak terganggu.
        $slot = GcKriteriaParameter::where('kelompok', $kelompok)->pluck('urutan')->map(fn ($u) => (int) $u)->sort()->values()->all();
        foreach (array_merge($berurutan, $lain->all()) as $i => $p) {
            $p->update(['urutan' => $slot[$i] ?? ($slot[count($slot) - 1] + $i)]);
        }
    }

    /** Samakan daftar opsi sebuah parameter dengan $labels; nilai (skor) opsi yang labelnya sama dipertahankan. */
    private function syncOpsi(GcKriteriaParameter $p, array $labels): void
    {
        $ada = GcKriteriaOpsi::where('parameter_id', $p->id)->get()->keyBy('label');
        $keep = [];

        foreach (array_values($labels) as $i => $label) {
            $o = $ada->get($label);
            if ($o) {
                $o->update(['urutan' => $i + 1]);
            } else {
                $o = $p->opsis()->create(['label' => $label, 'urutan' => $i + 1]);
            }
            $keep[] = $o->id;
        }

        GcKriteriaOpsi::where('parameter_id', $p->id)->whereNotIn('id', $keep)->delete();
    }

    private function kapital(string $s): string
    {
        return mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1);
    }
}
