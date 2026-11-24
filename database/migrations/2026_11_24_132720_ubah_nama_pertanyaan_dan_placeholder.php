<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** [kelompok, nama lama (huruf kecil), nama baru] */
    private const NAMA = [
        // Identitas keluarga
        ['identitas_keluarga', 'nama', 'Siapa nama Kepala Keluarga di Kartu Keluarga (KK) Anda?'],
        ['identitas_keluarga', 'nik kepala keluarga', 'Berapa Nomor Induk Kependudukan (NIK) Kepala Keluarga Anda?'],
        ['identitas_keluarga', 'no kk', 'Berapa Nomor Kartu Keluarga (KK) Anda?'],
        ['identitas_keluarga', 'jumlah anggota keluarga', 'Berapa jumlah anggota keluarga Anda?'],
        ['identitas_keluarga', 'provinsi', 'Provinsi tempat tinggal keluarga saat ini'],
        ['identitas_keluarga', 'kabupaten/kota', 'Kabupaten/Kota tempat tinggal keluarga saat ini'],
        ['identitas_keluarga', 'kecamatan', 'Kecamatan tempat tinggal keluarga saat ini'],
        ['identitas_keluarga', 'desa/kelurahan', 'Desa/Kelurahan tempat tinggal keluarga saat ini'],
        ['identitas_keluarga', 'kode pos', 'Kode Pos tempat tinggal saat ini'],
        ['identitas_keluarga', 'satuan lingkungan setempat (rt/rw/dusun)', 'Satuan Lingkungan Setempat (RT/RW/Dusun dll)'],
        ['identitas_keluarga', 'alamat lengkap', 'Alamat Lengkap Rumah/Tempat Tinggal Anda?'],
        ['identitas_keluarga', 'nama jalan', 'Nama Jalan Rumah/Tempat Tinggal Anda?'],
        ['identitas_keluarga', 'nomor rumah', 'Nomor Rumah/Tempat Tinggal Anda?'],
        // Keterangan sosial ekonomi
        ['keluarga', 'berapa perkiraan harga sewa/kontrak bangunan tempat tinggal anda selama sebulan? [rp]', 'Berapa perkiraan harga sewa bangunan tempat tinggal Anda selama sebulan? [Rp]'],
        ['keluarga', 'jenis lantai terluas', 'Apa jenis lantai terluas di tempat tinggal Anda?'],
        ['keluarga', 'kondisi dinding', 'Bagaimana kondisi dinding di tempat tinggal Anda?'],
        ['keluarga', 'jenis atap terluas', 'Apa jenis atap terluas di tempat tinggal Anda?'],
        ['keluarga', 'kepemilikan fasilitas bab', 'Di tempat tinggal Anda, apakah memiliki fasilitas tempat buang air besar (BAB) dan siapa saja yang menggunakan?'],
        ['keluarga', 'sumber air minum utama', 'Apa sumber air minum utama di tempat tinggal Anda?'],
        ['keluarga', 'sumber penerangan utama', 'Apa sumber penerangan utama di tempat tinggal Anda?'],
    ];

    /** Pilihan jawaban Kepemilikan fasilitas BAB: [label lama, label baru] */
    private const OPSI_BAB = [
        ['Ada, digunakan hanya Anggota Keluarga sendiri', 'Ada, digunakan oleh anggota keluarga dalam satu rumah'],
        ['Ada, digunakan bersama Anggota Keluarga dari keluarga tertentu', 'Ada, digunakan bersama oleh anggota keluarga dari beberapa rumah'],
    ];

    /** Teks contoh di kolom isian (hanya untuk kolom yang tidak terkunci). [nama baru huruf kecil, placeholder] */
    private const PLACEHOLDER = [
        ['berapa jumlah anggota keluarga anda?', 'Jumlah Anggota'],
        ['kode pos tempat tinggal saat ini', 'Nomor Kode Pos'],
        ['nomor rumah/tempat tinggal anda?', 'Nomor Rumah'],
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('gc_kriteria_parameters', 'placeholder')) {
            Schema::table('gc_kriteria_parameters', function (Blueprint $table) {
                $table->string('placeholder', 150)->nullable();
            });
        }

        DB::transaction(function () {
            // Tabel yang menyimpan salinan nama / jawaban (dilewati bila belum ada)
            $tabelJawaban = collect(['pbi_apbd_gc_jawabans', 'gc_jawabans'])
                ->filter(fn ($t) => Schema::hasTable($t))->values();

            foreach (self::NAMA as [$kelompok, $lama, $baru]) {
                $ids = DB::table('gc_kriteria_parameters')
                    ->where('kelompok', $kelompok)
                    ->whereRaw('LOWER(TRIM(nama)) = ?', [$lama])
                    ->pluck('id')->all();

                if (! $ids) {
                    continue;
                }

                DB::table('gc_kriteria_parameters')->whereIn('id', $ids)
                    ->update(['nama' => $baru, 'updated_at' => now()]);

                foreach ($tabelJawaban as $t) {
                    if (Schema::hasColumn($t, 'nama_parameter')) {
                        DB::table($t)->whereIn('parameter_id', $ids)->update(['nama_parameter' => $baru]);
                    }
                }

                // Pilihan jawaban BAB ikut diganti, termasuk jawaban warga/petugas yang sudah tersimpan
                if ($lama === 'kepemilikan fasilitas bab') {
                    foreach (self::OPSI_BAB as [$labelLama, $labelBaru]) {
                        DB::table('gc_kriteria_opsis')->whereIn('parameter_id', $ids)
                            ->where('label', $labelLama)
                            ->update(['label' => $labelBaru, 'updated_at' => now()]);

                        foreach ($tabelJawaban as $t) {
                            DB::table($t)->whereIn('parameter_id', $ids)
                                ->where('jawaban', $labelLama)->update(['jawaban' => $labelBaru]);
                        }
                    }
                }
            }

            foreach (self::PLACEHOLDER as [$nama, $teks]) {
                DB::table('gc_kriteria_parameters')
                    ->whereRaw('LOWER(TRIM(nama)) = ?', [$nama])
                    ->update(['placeholder' => $teks]);
            }
        });
    }

    public function down(): void
    {
        // Tidak dapat dipulihkan otomatis.
    }
};
