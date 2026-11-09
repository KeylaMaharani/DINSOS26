<?php

namespace App\Support;

use App\Models\DtsenPermohonan;
use App\Models\MasyarakatDokumen;
use App\Models\PbiApbd;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menghubungkan Dokumen milik masyarakat dengan pengajuan PBI APBD & DTSEN.
 * Berkas SELALU disalin ke pengajuan (bukan ditautkan), jadi menghapus / mengganti dokumen
 * di halaman Dokumen tidak mengubah berkas yang sudah melekat di pengajuan yang berjalan.
 */
class DokumenMasyarakat
{
    /** Jenis dokumen => kolom tujuan di pengajuan. */
    public const TARGET = [
        'ktp' => ['pbi' => 'scan_ktp', 'dtsen' => 'scan_ktp'],
        'kk' => ['pbi' => 'scan_kk'],
        'surat_rawat_inap' => ['pbi' => 'surat_rawat_inap'],
        'screenshot_desil' => ['pbi' => 'screenshot_pembaharuan_desil'],
        'screenshot_dtsen' => ['dtsen' => 'screenshot_dtsen'],
    ];

    public static function terbaru(int $userId, string $jenis): ?MasyarakatDokumen
    {
        return MasyarakatDokumen::where('user_id', $userId)->where('jenis', $jenis)
            ->orderByDesc('created_at')->orderByDesc('id')->first();
    }

    public static function ada(User $user, string $jenis): bool
    {
        return MasyarakatDokumen::where('user_id', $user->id)->where('jenis', $jenis)->exists();
    }

    /** Salin file dokumen ke folder pengajuan dengan nama "acak6__nama-asli.ext" (dibaca <x-lampiran-preview>). */
    private static function salin(MasyarakatDokumen $dok, string $folder): string
    {
        $ext = $dok->ekstensi();
        $nama = Str::limit(Str::slug(pathinfo((string) $dok->nama_asli, PATHINFO_FILENAME)), 60, '');
        $nama = $nama !== '' ? $nama : 'berkas';
        $tujuan = $folder . '/' . Str::random(6) . '__' . $nama . ($ext ? ".{$ext}" : '');

        Storage::disk('public')->copy($dok->path, $tujuan);

        return $tujuan;
    }

    /** Isi kolom lampiran PBI yang masih KOSONG dengan dokumen terbaru milik pemohon. */
    public static function isiBilaKosongPbi(PbiApbd $pbi, ?string $hanyaJenis = null): void
    {
        if (! $pbi->user_id) {
            return;
        }

        $update = [];
        foreach (self::TARGET as $jenis => $t) {
            if (empty($t['pbi']) || ($hanyaJenis && $hanyaJenis !== $jenis) || filled($pbi->{$t['pbi']})) {
                continue;
            }
            if ($dok = self::terbaru($pbi->user_id, $jenis)) {
                $update[$t['pbi']] = self::salin($dok, 'pbi-apbd/lampiran');
            }
        }

        if ($update) {
            $pbi->update($update);
        }
    }

    /**
     * Berkas DTSEN yang masih kosong, diisi dari dokumen terbaru. Mengembalikan [kolom => path baru];
     * penyimpanan ke database dilakukan pemanggil (di dalam transaksinya).
     */
    public static function salinUntukDtsen(User $user, $lampiranLama = null): array
    {
        $baru = [];
        foreach (self::TARGET as $jenis => $t) {
            if (empty($t['dtsen']) || filled($lampiranLama->{$t['dtsen']} ?? null)) {
                continue;
            }
            if ($dok = self::terbaru($user->id, $jenis)) {
                $baru[$t['dtsen']] = self::salin($dok, 'dtsen/lampiran');
            }
        }

        return $baru;
    }

    /** Isi lampiran DTSEN yang kosong pada permohonan tertentu (dipakai setelah unggah di halaman Dokumen). */
    public static function isiBilaKosongDtsen(DtsenPermohonan $p, ?string $hanyaJenis = null): void
    {
        if (! $p->user_id) {
            return;
        }

        $baru = [];
        foreach (self::TARGET as $jenis => $t) {
            if (empty($t['dtsen']) || ($hanyaJenis && $hanyaJenis !== $jenis) || filled($p->lampiran->{$t['dtsen']} ?? null)) {
                continue;
            }
            if ($dok = self::terbaru($p->user_id, $jenis)) {
                $baru[$t['dtsen']] = self::salin($dok, 'dtsen/lampiran');
            }
        }

        if ($baru) {
            $p->lampiran()->updateOrCreate([], $baru);
        }
    }

    /**
     * "Pakai di pengajuan": pasang versi dokumen ini ke pengajuan PBI APBD & DTSEN terbaru milik user
     * (hanya yang masih bisa diubah), MENGGANTI berkas yang sebelumnya melekat.
     * Mengembalikan daftar nama layanan yang diperbarui.
     */
    public static function pakai(MasyarakatDokumen $dok): array
    {
        $t = self::TARGET[$dok->jenis] ?? [];
        $hasil = [];
        $disk = Storage::disk('public');

        if (! empty($t['pbi'])) {
            $pbi = PbiApbd::where('user_id', $dok->user_id)->latest()->first();
            if ($pbi && $pbi->masyarakatBisaEdit()) {
                $lama = $pbi->{$t['pbi']};
                $pbi->update([$t['pbi'] => self::salin($dok, 'pbi-apbd/lampiran')]);
                if ($lama && $disk->exists($lama)) {
                    $disk->delete($lama);
                }
                // Hasil cek Kelurahan untuk berkas ini tidak berlaku lagi
                $cek = $pbi->verifikasi_berkas ?? [];
                if (isset($cek[$t['pbi']])) {
                    unset($cek[$t['pbi']]);
                    $pbi->update(['verifikasi_berkas' => $cek]);
                }
                $hasil[] = 'PBI APBD';
            }
        }

        if (! empty($t['dtsen'])) {
            $p = DtsenPermohonan::where('user_id', $dok->user_id)->latest()->first();
            if ($p && $p->status !== DtsenPermohonan::STATUS_SELESAI) {
                $p->loadMissing('lampiran');
                $lama = $p->lampiran->{$t['dtsen']} ?? null;
                $p->lampiran()->updateOrCreate([], [$t['dtsen'] => self::salin($dok, 'dtsen/lampiran')]);
                if ($lama && $disk->exists($lama)) {
                    $disk->delete($lama);
                }
                $hasil[] = 'DTSEN';
            }
        }

        return $hasil;
    }
}