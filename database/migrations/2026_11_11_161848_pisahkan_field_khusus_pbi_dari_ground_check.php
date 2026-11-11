<?php

use App\Models\GcJawaban;
use App\Models\GcKriteriaOpsi;
use App\Models\GcKriteriaParameter;
use App\Models\PbiApbd;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Field yang HANYA milik PBI APBD tidak boleh ada di master Ground Check:
 *   Diagnosa, Faskes, Surat Keterangan Rawat Inap, Foto KTP.
 *
 * Langkah:
 *  1. Jawaban yang sudah terlanjur diisi masyarakat di GC DIPINDAHKAN ke kolom pengajuan PBI
 *     (hanya bila kolom PBI-nya masih kosong, jadi data PBI yang sudah ada tidak tertimpa).
 *  2. Parameter + opsi + jawaban GC untuk keempat field itu dihapus dari Ground Check.
 *
 * Yang tetap di PBI: scan_ktp, surat_rawat_inap (opsional), diagnosa (riwayat), nama_faskes.
 * Berkas fisik tidak dihapus (path-nya kini dipakai kolom PBI).
 */
return new class extends Migration
{
    /** nama parameter (huruf kecil) => kolom tujuan di pbi_apbds */
    private const PETA = [
        'diagnosa' => 'diagnosa',
        'faskes (fasilitas kesehatan)' => 'nama_faskes',
        'faskes' => 'nama_faskes',
        'surat keterangan rawat inap' => 'surat_rawat_inap',
        'foto ktp' => 'scan_ktp',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $params = GcKriteriaParameter::whereIn('kelompok', ['kesehatan', 'dokumentasi'])
                ->get()
                ->filter(fn ($p) => isset(self::PETA[Str::lower(trim($p->nama))]));

            if ($params->isEmpty()) {
                return;
            }

            // 1. Pindahkan jawaban masyarakat ke kolom PBI
            foreach ($params as $p) {
                $kolom = self::PETA[Str::lower(trim($p->nama))];

                $rows = DB::table('pbi_apbd_gc_jawabans')
                    ->where('parameter_id', $p->id)
                    ->whereNotNull('jawaban')
                    ->where('jawaban', '!=', '')
                    ->get();

                foreach ($rows as $r) {
                    $pbi = PbiApbd::find($r->pbi_apbd_id);

                    if (! $pbi || filled($pbi->{$kolom})) {
                        continue;
                    }

                    $pbi->{$kolom} = $r->jawaban;
                    $pbi->saveQuietly();

                    // Diagnosa tampil di halaman PBI lewat riwayat (diagnosaLogs)
                    if ($kolom === 'diagnosa' && $pbi->user_id) {
                        try {
                            $pbi->diagnosaLogs()->create([
                                'user_id' => $pbi->user_id,
                                'username' => $pbi->nama_kepala_keluarga,
                                'role_name' => 'Masyarakat',
                                'diagnosa' => $r->jawaban,
                            ]);
                        } catch (\Throwable $e) {
                            // kolom diagnosa di pbi_apbds sudah terisi; riwayat bersifat tambahan
                        }
                    }
                }
            }

            // 2. Hapus dari Ground Check
            $ids = $params->pluck('id')->all();

            DB::table('pbi_apbd_gc_jawabans')->whereIn('parameter_id', $ids)->delete();
            GcJawaban::whereIn('parameter_id', $ids)->delete();
            GcKriteriaOpsi::whereIn('parameter_id', $ids)->delete();
            GcKriteriaParameter::whereIn('id', $ids)->delete();
        });
    }

    public function down(): void
    {
        // Tidak dapat dipulihkan otomatis: tambahkan ulang parameter lewat menu Master Kriteria Ground Check bila perlu.
    }
};