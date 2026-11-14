<?php

use App\Models\GcKriteriaParameter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Taruh di database/migrations/.
 *
 * Memisahkan pertanyaan berdasarkan tujuan jawaban:
 *   pbi_gc = dijawab sekali, dipakai PBI APBD dan Ground Check
 *   pbi    = hanya PBI APBD
 *   gc     = hanya Ground Check
 *
 * 1. Kolom `target` di gc_kriteria_parameters + snapshot-nya di pbi_apbd_gc_jawabans.
 * 2. Target semua parameter yang ada diisi dari nama (GcKriteriaParameter::targetBawaan).
 * 3. "Jumlah keluarga satu rumah" dan "Jumlah sepeda motor" (GC, angka) dilepas dari padanan PBI.
 * 4. Sembilan pertanyaan khusus PBI dibuat / diaktifkan kembali dengan opsi disalin dari master PBI.
 *    Dibuat hanya bila master GC sudah berisi; database kosong diisi oleh GcKriteriaSeeder.
 */
return new class extends Migration
{
    /** [nama di master GC = nama di master PBI] */
    private const KHUSUS_PBI = [
        'Status lahan tempat tinggal yang ditempati',
        'Kondisi dinding',
        'Kondisi atap',
        'Cara memperoleh air minum',
        'Daya listrik terpasang',
        'Jenis kloset',
        'Tempat pembuangan akhir tinja',
        'Jumlah tanggungan keluarga',
        'Kepemilikan kendaraan roda 2',
    ];

    public function up(): void
    {
        Schema::table('gc_kriteria_parameters', function (Blueprint $t) {
            if (! Schema::hasColumn('gc_kriteria_parameters', 'target')) {
                $t->string('target', 10)->default('gc')->after('kelompok')->index();
            }
        });

        Schema::table('pbi_apbd_gc_jawabans', function (Blueprint $t) {
            if (! Schema::hasColumn('pbi_apbd_gc_jawabans', 'target')) {
                $t->string('target', 10)->default('gc')->after('tipe_input')->index();
            }
        });

        DB::transaction(function () {
            $now = now();

            // 2. Target dari nama
            foreach (DB::table('gc_kriteria_parameters')->get(['id', 'kelompok', 'nama']) as $p) {
                DB::table('gc_kriteria_parameters')->where('id', $p->id)->update([
                    'target' => GcKriteriaParameter::targetBawaan((string) $p->kelompok, (string) $p->nama),
                ]);
            }

            // 3. Lepas padanan PBI dari pertanyaan angka GC (PBI memakai pilihan, bukan angka)
            DB::table('gc_kriteria_parameters')
                ->whereIn(DB::raw('LOWER(TRIM(nama))'), [
                    'jumlah keluarga yang tinggal dalam satu rumah',
                    'jumlah sepeda motor',
                ])
                ->update(['pbi_parameter_id' => null, 'target' => 'gc', 'updated_at' => $now]);

            // 4. Pertanyaan khusus PBI
            if (DB::table('gc_kriteria_parameters')->exists()) {
                $urutan = (int) DB::table('gc_kriteria_parameters')->max('urutan');

                foreach (self::KHUSUS_PBI as $nama) {
                    $pbi = DB::table('pbi_kriteria_parameters')->where('nama', $nama)->first();
                    if (! $pbi) {
                        continue; // master PBI tidak punya parameter ini
                    }

                    $ada = DB::table('gc_kriteria_parameters')
                        ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($nama)])
                        ->first();

                    if ($ada) {
                        DB::table('gc_kriteria_parameters')->where('id', $ada->id)->update([
                            'target' => 'pbi',
                            'aktif' => true,
                            'tipe_input' => 'pilihan',
                            'pbi_parameter_id' => $pbi->id,
                            'updated_at' => $now,
                        ]);
                        $id = $ada->id;
                    } else {
                        $id = DB::table('gc_kriteria_parameters')->insertGetId([
                            'kelompok' => 'keluarga',
                            'target' => 'pbi',
                            'urutan' => ++$urutan,
                            'nama' => $nama,
                            'bobot' => $pbi->bobot,
                            'tipe_input' => 'pilihan',
                            'pbi_parameter_id' => $pbi->id,
                            'keterangan' => "Khusus PBI APBD. Opsi disalin dari PBI: {$nama}.",
                            'aktif' => true,
                            'wajib' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    // Opsi disalin dari master PBI bila belum ada
                    if (! DB::table('gc_kriteria_opsis')->where('parameter_id', $id)->exists()) {
                        $opsis = DB::table('pbi_kriteria_opsis')->where('parameter_id', $pbi->id)->orderBy('urutan')->get();
                        foreach ($opsis as $j => $o) {
                            DB::table('gc_kriteria_opsis')->insert([
                                'parameter_id' => $id,
                                'label' => $o->label,
                                'nilai' => $o->indeks_terintegrasi,
                                'urutan' => $j + 1,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }
                }
            }

            // Snapshot target pada jawaban masyarakat yang sudah ada
            DB::table('pbi_apbd_gc_jawabans')
                ->join('gc_kriteria_parameters', 'gc_kriteria_parameters.id', '=', 'pbi_apbd_gc_jawabans.parameter_id')
                ->update(['pbi_apbd_gc_jawabans.target' => DB::raw('gc_kriteria_parameters.target')]);
        });
    }

    public function down(): void
    {
        Schema::table('pbi_apbd_gc_jawabans', function (Blueprint $t) {
            if (Schema::hasColumn('pbi_apbd_gc_jawabans', 'target')) {
                $t->dropColumn('target');
            }
        });

        Schema::table('gc_kriteria_parameters', function (Blueprint $t) {
            if (Schema::hasColumn('gc_kriteria_parameters', 'target')) {
                $t->dropColumn('target');
            }
        });
        // Pertanyaan khusus PBI yang dibuat tidak dihapus.
    }
};