<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Perbaikan form Ground Check:
 *  1. Tab E Keluarga: "Jenis kloset" & "Tempat pembuangan akhir tinja" dinonaktifkan;
 *     opsi "Kepemilikan fasilitas BAB" dilengkapi menjadi 6 pilihan beserta nilainya.
 *  2. Tab F2: "Jumlah ternak kuda yang dimiliki" diaktifkan (dibuat bila belum ada,
 *     TETAPI hanya pada database yang master-nya sudah berisi. Pada database kosong
 *     (migrate:fresh) parameter ini dibuat oleh GcKriteriaSeeder; kalau migration ini
 *     ikut membuatnya, GcKriteriaSeeder akan menganggap master sudah ada dan terlewati).
 *  4. Tab G: foto tabung gas, kamar mandi, dan individu dinonaktifkan.
 *
 * Parameter DINONAKTIFKAN (bukan dihapus) supaya jawaban lama tetap aman.
 */
return new class extends Migration
{
    /** label => nilai bawaan (dipakai bila opsi lama yang sepadan tidak ditemukan) */
    private const OPSI_BAB = [
        'Ada, digunakan hanya Anggota Keluarga sendiri' => 1.0,
        'Ada, digunakan bersama Anggota Keluarga dari keluarga tertentu' => 0.4,
        'Ada, di MCK komunal' => 0.3,
        'Ada, di MCK umum/siapapun menggunakan' => 0.2,
        'Ada, Anggota Keluarga tidak menggunakan' => 0.1,
        'Tidak ada fasilitas' => 0.1,
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $now = now();

            // ---- 1a. Nonaktifkan Jenis kloset & Tempat pembuangan akhir tinja ----
            DB::table('gc_kriteria_parameters')
                ->where('kelompok', 'keluarga')
                ->whereIn(DB::raw('LOWER(TRIM(nama))'), ['jenis kloset', 'tempat pembuangan akhir tinja'])
                ->update(['aktif' => false, 'updated_at' => $now]);

            // ---- 1b. Lengkapi opsi Kepemilikan fasilitas BAB ----
            $bab = DB::table('gc_kriteria_parameters')
                ->where('kelompok', 'keluarga')
                ->whereRaw('LOWER(TRIM(nama)) = ?', ['kepemilikan fasilitas bab'])
                ->first();

            if ($bab) {
                $lama = DB::table('gc_kriteria_opsis')->where('parameter_id', $bab->id)->get();
                $keep = [];
                $i = 0;

                foreach (self::OPSI_BAB as $label => $nilaiBawaan) {
                    $i++;
                    $ada = $lama->firstWhere('label', $label);

                    if ($ada) {
                        DB::table('gc_kriteria_opsis')->where('id', $ada->id)
                            ->update(['urutan' => $i, 'updated_at' => $now]);
                        $keep[] = $ada->id;
                        continue;
                    }

                    $keep[] = DB::table('gc_kriteria_opsis')->insertGetId([
                        'parameter_id' => $bab->id,
                        'label' => $label,
                        // Prioritas: nilai dari opsi lama yang sepadan, lalu nilai bawaan di atas.
                        'nilai' => $this->nilaiSepadan($lama, $label) ?? $nilaiBawaan,
                        'urutan' => $i,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                DB::table('gc_kriteria_opsis')
                    ->where('parameter_id', $bab->id)
                    ->whereNotIn('id', $keep)
                    ->delete();
            }

            // ---- 2. Aktifkan / buat "Jumlah ternak kuda yang dimiliki" ----
            $kuda = DB::table('gc_kriteria_parameters')
                ->where('kelompok', 'aset_tidak_bergerak')
                ->whereRaw('LOWER(TRIM(nama)) = ?', ['jumlah ternak kuda yang dimiliki'])
                ->first();

            if ($kuda) {
                DB::table('gc_kriteria_parameters')->where('id', $kuda->id)->update([
                    'aktif' => true,
                    'tipe_input' => 'angka',
                    'keterangan' => null,
                    'updated_at' => $now,
                ]);
            } elseif (DB::table('gc_kriteria_parameters')->exists()) {
                // Hanya dibuat bila master sudah berisi (database lama). Database kosong
                // dibiarkan kosong supaya GcKriteriaSeeder bisa mengisinya secara lengkap.
                $kerbau = DB::table('gc_kriteria_parameters')
                    ->where('kelompok', 'aset_tidak_bergerak')
                    ->whereRaw('LOWER(TRIM(nama)) = ?', ['jumlah ternak kerbau yang dimiliki'])
                    ->first();

                $urutan = $kerbau ? ((int) $kerbau->urutan) + 1 : ((int) DB::table('gc_kriteria_parameters')->max('urutan')) + 1;

                // Geser parameter sesudah kerbau supaya kuda tepat di bawahnya.
                if ($kerbau) {
                    DB::table('gc_kriteria_parameters')->where('urutan', '>=', $urutan)->increment('urutan');
                }

                DB::table('gc_kriteria_parameters')->insert([
                    'kelompok' => 'aset_tidak_bergerak',
                    'urutan' => $urutan,
                    'nama' => 'Jumlah ternak kuda yang dimiliki',
                    'bobot' => 0,
                    'tipe_input' => 'angka',
                    'aktif' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // ---- 4. Dokumentasi: sisakan tampak depan, tampak dalam, foto kartu keluarga ----
            DB::table('gc_kriteria_parameters')
                ->where('kelompok', 'dokumentasi')
                ->whereIn(DB::raw('LOWER(TRIM(nama))'), ['foto tabung gas', 'foto kamar mandi', 'foto individu'])
                ->update(['aktif' => false, 'updated_at' => $now]);
        });
    }

    public function down(): void
    {
        DB::table('gc_kriteria_parameters')
            ->whereIn(DB::raw('LOWER(TRIM(nama))'), [
                'jenis kloset', 'tempat pembuangan akhir tinja',
                'foto tabung gas', 'foto kamar mandi', 'foto individu',
            ])
            ->update(['aktif' => true, 'updated_at' => now()]);

        DB::table('gc_kriteria_parameters')
            ->whereRaw('LOWER(TRIM(nama)) = ?', ['jumlah ternak kuda yang dimiliki'])
            ->update(['aktif' => false, 'updated_at' => now()]);

        // Opsi BAB lama tidak dipulihkan.
    }

    /** Cari nilai (skor) dari opsi lama yang maknanya sepadan dengan label baru. */
    private function nilaiSepadan($lama, string $label)
    {
        $kunci = match (true) {
            str_contains($label, 'hanya Anggota Keluarga sendiri') => 'sendiri',
            str_contains($label, 'bersama Anggota Keluarga') => 'bersama',
            $label === 'Tidak ada fasilitas' => 'tidak ada',
            default => null,
        };

        if (! $kunci) {
            return null;
        }

        $cocok = $lama->first(fn ($o) => str_contains(mb_strtolower($o->label), $kunci));

        return $cocok->nilai ?? null;
    }
};
