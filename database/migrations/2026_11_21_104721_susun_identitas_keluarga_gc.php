<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Data pribadi anggota: TETAP di kelompok kependudukan */
    private const ANGGOTA = [
        'jenis kelamin', 'tanggal lahir', 'status perkawinan', 'status hubungan dengan kepala keluarga',
        'no. telp', 'no telp', 'email', 'nama gadis ibu kandung',
    ];

    /** [nama baru, tipe, alias nama lama yang dipakai ulang, wajib bila dibuat baru] */
    private const IDENTITAS = [
        ['Nama',                                      'teks',   ['nama', 'nama lengkap', 'nama kepala keluarga'], true],
        ['NIK Kepala Keluarga',                       'teks',   ['nik', 'nik kepala keluarga'], true],
        ['No KK',                                     'teks',   ['no kk', 'nomor kk', 'no. kk'], true],
        ['Jumlah anggota keluarga',                   'angka',  ['jumlah anggota keluarga', 'jumlah art'], true],
        ['Provinsi',                                  'teks',   ['provinsi'], true],
        ['Kabupaten/Kota',                            'teks',   ['kabupaten/kota', 'kab/kota'], true],
        ['Kecamatan',                                 'teks',   ['kecamatan'], true],
        ['Desa/Kelurahan',                            'teks',   ['desa/kelurahan', 'kelurahan'], true],
        ['Kode pos',                                  'teks',   ['kode pos'], true],
        ['Satuan lingkungan setempat (RT/RW/Dusun)',  'teks',   ['rt/rw', 'rt'], true],
        ['Alamat lengkap',                            'teks',   ['alamat lengkap', 'alamat domisili', 'alamat'], true],
        ['Nama jalan',                                'teks',   [], false],
        ['Nomor rumah',                               'teks',   [], false],
        ['Apakah alamat tempat tinggal saat ini sesuai dengan Kartu Keluarga?', 'pilihan',
            ['apakah wilayah sama?', 'apakah wilayah sama'], true],
    ];

    public function up(): void
    {
        // Database kosong (migrate:fresh): biarkan GcKriteriaSeeder yang mengisi
        if (! DB::table('gc_kriteria_parameters')->exists()) {
            return;
        }

        DB::transaction(function () {
            $now = now();

            // 1. Geser urutan lama supaya identitas bisa memakai 1..14
            DB::table('gc_kriteria_parameters')->increment('urutan', 100);

            // 2. Kependudukan yang bukan data pribadi anggota -> kelompok baru
            $pindah = DB::table('gc_kriteria_parameters')->where('kelompok', 'kependudukan')->get()
                ->filter(function ($p) {
                    $n = mb_strtolower(trim($p->nama));

                    return ! in_array($n, self::ANGGOTA, true) && ! str_contains($n, 'rekening');
                })->pluck('id')->all();

            DB::table('gc_kriteria_parameters')->whereIn('id', $pindah)
                ->update(['kelompok' => 'identitas_keluarga']);

            // 3. Upsert 14 field sesuai urutan formulir
            $dipakai = [];

            foreach (self::IDENTITAS as $i => [$nama, $tipe, $alias, $wajib]) {
                $cari = array_map('mb_strtolower', array_merge([$nama], $alias));

                $ada = DB::table('gc_kriteria_parameters')
                    ->where('kelompok', 'identitas_keluarga')
                    ->whereNotIn('id', $dipakai)
                    ->whereIn(DB::raw('LOWER(TRIM(nama))'), $cari)
                    ->orderBy('id')->first();

                $attrs = [
                    'kelompok' => 'identitas_keluarga',
                    'nama' => $nama,
                    'urutan' => $i + 1,
                    'target' => 'pbi_gc',
                    'aktif' => true,
                    'updated_at' => $now,
                ];

                if ($ada) {
                    DB::table('gc_kriteria_parameters')->where('id', $ada->id)->update($attrs);
                    $id = $ada->id;
                } else {
                    $id = DB::table('gc_kriteria_parameters')->insertGetId($attrs + [
                        'tipe_input' => $tipe,
                        'bobot' => 0,
                        'wajib' => $wajib,
                        'created_at' => $now,
                    ]);

                    if ($tipe === 'pilihan') {
                        foreach (['Ya', 'Tidak'] as $u => $label) {
                            DB::table('gc_kriteria_opsis')->insert([
                                'parameter_id' => $id, 'label' => $label, 'nilai' => null,
                                'urutan' => $u + 1, 'created_at' => $now, 'updated_at' => $now,
                            ]);
                        }
                    }
                }

                $dipakai[] = $id;
            }

            // 4. Koordinat (peta) ikut tab Identitas = nomor 15-16 di formulir
            $koordinat = DB::table('gc_kriteria_parameters')
                ->where('kelompok', 'dokumentasi')
                ->whereIn(DB::raw('LOWER(TRIM(nama))'), ['koordinat lokasi', 'koordinat lintang', 'koordinat bujur'])
                ->pluck('id')->all();

            DB::table('gc_kriteria_parameters')->whereIn('id', $koordinat)
                ->update(['kelompok' => 'identitas_keluarga', 'updated_at' => $now]);

            $dipakai = array_merge($dipakai, $koordinat);

            // 5. Pertanyaan lama ("Apakah No KK sama?", "nama sama?", "Alamat sesuai KK", dll): nonaktifkan, bukan hapus
            DB::table('gc_kriteria_parameters')->where('kelompok', 'identitas_keluarga')
                ->whereNotIn('id', $dipakai)->update(['aktif' => false, 'updated_at' => $now]);

            // 6. Snapshot jawaban warga ikut kelompok & nama baru
            foreach (array_slice($dipakai, 0, count(self::IDENTITAS)) as $i => $id) {
                DB::table('pbi_apbd_gc_jawabans')->where('parameter_id', $id)
                    ->update(['kelompok' => 'identitas_keluarga', 'nama_parameter' => self::IDENTITAS[$i][0]]);
            }
            DB::table('pbi_apbd_gc_jawabans')->whereIn('parameter_id', array_merge($pindah, $koordinat))
                ->update(['kelompok' => 'identitas_keluarga']);
        });
    }

    public function down(): void
    {
        // Tidak dapat dipulihkan otomatis.
    }
};
