<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Dokumen milik masyarakat (diunggah sekali, dipakai di pengajuan PBI APBD & DTSEN).
 * Tiap jenis dokumen menyimpan maksimal 2 versi terbaru (tanggal unggah berbeda).
 *
 * Berkas yang sudah ada di pengajuan lama ikut dimasukkan (disalin ke folder dokumen milik user,
 * maksimal 2 terbaru per jenis), jadi masyarakat tidak perlu mengunggah ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masyarakat_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis', 40);          // ktp | kk | surat_rawat_inap | screenshot_desil | screenshot_dtsen
            $table->string('path');               // path di disk 'public'
            $table->string('nama_asli')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'jenis']);
        });

        $this->isiDariPengajuanLama();
    }

    public function down(): void
    {
        Schema::dropIfExists('masyarakat_dokumens');
    }

    private function isiDariPengajuanLama(): void
    {
        $sumber = []; // [user_id, jenis, path, tanggal]

        if (Schema::hasTable('pbi_apbds')) {
            $petaPbi = [
                'scan_ktp' => 'ktp',
                'scan_kk' => 'kk',
                'surat_rawat_inap' => 'surat_rawat_inap',
                'screenshot_pembaharuan_desil' => 'screenshot_desil',
            ];

            foreach (DB::table('pbi_apbds')->whereNotNull('user_id')->get() as $p) {
                foreach ($petaPbi as $kolom => $jenis) {
                    if (! empty($p->{$kolom})) {
                        $sumber[] = [$p->user_id, $jenis, $p->{$kolom}, $p->updated_at ?? $p->created_at];
                    }
                }
            }
        }

        if (Schema::hasTable('dtsen_lampiran') && Schema::hasTable('dtsen_permohonan')) {
            $rows = DB::table('dtsen_lampiran')
                ->join('dtsen_permohonan', 'dtsen_permohonan.id', '=', 'dtsen_lampiran.permohonan_id')
                ->whereNotNull('dtsen_permohonan.user_id')
                ->select('dtsen_permohonan.user_id', 'dtsen_lampiran.scan_ktp', 'dtsen_lampiran.screenshot_dtsen', 'dtsen_lampiran.updated_at', 'dtsen_lampiran.created_at')
                ->get();

            foreach ($rows as $r) {
                $tgl = $r->updated_at ?? $r->created_at;
                if (! empty($r->scan_ktp)) {
                    $sumber[] = [$r->user_id, 'ktp', $r->scan_ktp, $tgl];
                }
                if (! empty($r->screenshot_dtsen)) {
                    $sumber[] = [$r->user_id, 'screenshot_dtsen', $r->screenshot_dtsen, $tgl];
                }
            }
        }

        // Terbaru dulu; satu path hanya sekali; maksimal 2 per (user, jenis)
        usort($sumber, fn ($a, $b) => strcmp((string) $b[3], (string) $a[3]));

        $disk = Storage::disk('public');
        $jumlah = [];
        $sudah = [];

        foreach ($sumber as [$userId, $jenis, $path, $tanggal]) {
            $kunci = $userId . '|' . $jenis;
            if (($jumlah[$kunci] ?? 0) >= 2 || isset($sudah[$userId . '|' . $path]) || ! $disk->exists($path)) {
                continue;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $dasar = basename($path);
            $namaAsli = str_contains($dasar, '__') ? Str::after($dasar, '__') : $dasar;
            $tujuan = "masyarakat-dokumen/{$userId}/{$jenis}-" . Str::random(10) . ($ext ? ".{$ext}" : '');

            $disk->copy($path, $tujuan);

            DB::table('masyarakat_dokumens')->insert([
                'user_id' => $userId,
                'jenis' => $jenis,
                'path' => $tujuan,
                'nama_asli' => $namaAsli,
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            $jumlah[$kunci] = ($jumlah[$kunci] ?? 0) + 1;
            $sudah[$userId . '|' . $path] = true;
        }
    }
};