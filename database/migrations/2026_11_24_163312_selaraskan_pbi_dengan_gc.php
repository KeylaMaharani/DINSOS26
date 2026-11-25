<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1. gc_kriteria_parameters.tampil_verifikasi : saklar "tampil di kartu verifikasi Kelurahan".
 * 2. gc_kriteria_opsis.pbi_opsi_id            : opsi jawaban GC -> opsi PBI (dasar penilaian skor).
 * 3. pbi_kriteria_parameters.aktif            : sekarang berarti "ikut hitung skor".
 *    Diisi true hanya bila parameter PBI punya pertanyaan GC yang AKTIF.
 *
 * Opsi yang labelnya sama otomatis dipetakan. Yang berbeda diisi dari daftar PETA di bawah.
 * Opsi yang tidak ada di PETA dan tidak sama labelnya dibiarkan NULL (tidak dihitung) -> atur di
 * Master Kriteria PBI > tombol "Pemetaan".
 */
return new class extends Migration
{
    /** [nama parameter PBI (huruf kecil)] => [label opsi GC (huruf kecil) => label opsi PBI] */
    private const PETA = [
        'status penguasaan bangunan tempat tinggal yang ditempati' => [
            'bebas sewa' => 'Milik orang lain',
        ],
        'penghasilan rata-rata per bulan' => [
            'tidak sama sekali' => 'Kurang dari Rp1.000.000,00',
            '<1 juta per bulan' => 'Kurang dari Rp1.000.000,00',
            'umk' => 'Lebih dari Rp5.000.000,00',
            '> umk - 10 juta per bulan' => 'Lebih dari Rp5.000.000,00',
            '> 10 juta perbulan' => 'Lebih dari Rp5.000.000,00',
        ],
        'jenis lantai' => [
            'parket/vinil/karpet' => 'Parket/vinil/permadani',
            'kayu/papan' => 'Kayu/papan kualitas rendah',
        ],
        'kondisi dinding' => [
            'baik' => 'Bagus/kualitas tinggi',
            'rusak ringan' => 'Bagus/kualitas tinggi',
            'rusak sedang' => 'Jelek/kualitas rendah',
            'rusak berat' => 'Jelek/kualitas rendah',
        ],
        'jenis atap' => [
            'beton' => 'Beton/genteng beton',
            'genteng' => 'Genteng tanah liat',
            'kayu/sirap' => 'Sirap',
            'bambu' => 'Bambu, jerami/ijuk/daun-daunan/rumbia',
            'jerami/ijuk/daun-daunan/rumbia' => 'Bambu, jerami/ijuk/daun-daunan/rumbia',
        ],
        'sumber air minum' => [
            'leding' => 'Ledeng meteran',
            'air permukaan (sungai/danau/waduk/kolam/irigasi)' => 'Air sungai/danau/waduk/hujan',
            'air hujan' => 'Air sungai/danau/waduk/hujan',
        ],
        'sumber penerangan utama' => [
            'listrik pln dengan meteran' => 'Listrik PLN/token',
            'listrik pln tanpa meteran' => 'Listrik PLN non token',
        ],
        'daya listrik terpasang' => [
            '900 watt' => '900 watt s/d 2.200 watt',
            '1.300 watt' => '900 watt s/d 2.200 watt',
            '2.200 watt' => '900 watt s/d 2.200 watt',
            '> 2.200 watt' => '900 watt s/d 2.200 watt',
        ],
        'penggunaan fasilitas tempat buang air besar' => [
            'ada, digunakan oleh anggota keluarga dalam satu rumah' => 'Sendiri',
            'ada, digunakan bersama oleh anggota keluarga dari beberapa rumah' => 'Bersama',
            'ada, di mck komunal' => 'Bersama',
            'ada, di mck umum/siapapun menggunakan' => 'Bersama',
            'ada, anggota keluarga tidak menggunakan' => 'Tidak ada',
            'tidak ada fasilitas' => 'Tidak ada',
        ],
    ];

    public function up(): void
    {
        Schema::table('gc_kriteria_parameters', function (Blueprint $t) {
            if (! Schema::hasColumn('gc_kriteria_parameters', 'tampil_verifikasi')) {
                $t->boolean('tampil_verifikasi')->default(true)->after('aktif');
            }
        });

        Schema::table('gc_kriteria_opsis', function (Blueprint $t) {
            if (! Schema::hasColumn('gc_kriteria_opsis', 'pbi_opsi_id')) {
                $t->foreignId('pbi_opsi_id')->nullable()->after('parameter_id')
                    ->constrained('pbi_kriteria_opsis')->nullOnDelete();
            }
        });

        DB::transaction(function () {
            // Identitas sudah ada di kartu Data Pemohon: tidak ikut kartu verifikasi
            DB::table('gc_kriteria_parameters')
                ->where('kelompok', 'identitas_keluarga')
                ->update(['tampil_verifikasi' => false]);

            $pbiParams = DB::table('pbi_kriteria_parameters')->get()->keyBy('id');
            $pbiOpsis = DB::table('pbi_kriteria_opsis')->get()->groupBy('parameter_id');

            $norm = fn ($t) => mb_strtolower(trim((string) $t));

            foreach (DB::table('gc_kriteria_parameters')->whereNotNull('pbi_parameter_id')->get() as $g) {
                $pp = $pbiParams->get($g->pbi_parameter_id);
                if (! $pp) {
                    continue;
                }

                $khusus = self::PETA[$norm($pp->nama)] ?? [];
                $opsiPbi = $pbiOpsis->get($pp->id, collect());

                foreach (DB::table('gc_kriteria_opsis')->where('parameter_id', $g->id)->get() as $o) {
                    $tujuan = $khusus[$norm($o->label)] ?? $o->label;
                    $cocok = $opsiPbi->first(fn ($x) => $norm($x->label) === $norm($tujuan));

                    if ($cocok) {
                        DB::table('gc_kriteria_opsis')->where('id', $o->id)->update(['pbi_opsi_id' => $cocok->id]);
                    }
                }
            }

            // Ikut skor hanya bila ada pertanyaan GC aktif yang bertaut
            foreach ($pbiParams as $pp) {
                $ada = DB::table('gc_kriteria_parameters')
                    ->where('pbi_parameter_id', $pp->id)->where('aktif', true)->exists();

                DB::table('pbi_kriteria_parameters')->where('id', $pp->id)->update(['aktif' => $ada]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('gc_kriteria_opsis', function (Blueprint $t) {
            if (Schema::hasColumn('gc_kriteria_opsis', 'pbi_opsi_id')) {
                $t->dropConstrainedForeignId('pbi_opsi_id');
            }
        });

        Schema::table('gc_kriteria_parameters', function (Blueprint $t) {
            if (Schema::hasColumn('gc_kriteria_parameters', 'tampil_verifikasi')) {
                $t->dropColumn('tampil_verifikasi');
            }
        });
    }
};
