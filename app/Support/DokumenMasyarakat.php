<?php

namespace App\Support;

use App\Models\DtsenPermohonan;
use App\Models\GcPbiJawaban;
use App\Models\MasyarakatDokumen;
use App\Models\PbiApbd;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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
    public static function salinUntukDtsen(User $user, $lampiranLama = null, array $paksa = []): array
    {
        $baru = [];
        foreach (self::TARGET as $jenis => $t) {
            // $paksa = kolom yang harus diganti walau sudah terisi (warga baru saja mengunggah berkas pengganti)
            if (empty($t['dtsen']) || (filled($lampiranLama->{$t['dtsen']} ?? null) && ! in_array($t['dtsen'], $paksa, true))) {
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
     * Simpan berkas yang diunggah warga lewat formulir pengajuan sebagai dokumen di menu Dokumen.
     * Tiap jenis hanya menyimpan MAKS_VERSI versi terbaru; yang lebih lama dihapus.
     */
    public static function tambahDariUnggahan(User $user, string $jenis, UploadedFile $file): MasyarakatDokumen
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        $path = $file->storeAs(
            "masyarakat-dokumen/{$user->id}",
            $jenis . '-' . now()->format('YmdHis') . '-' . Str::random(5) . '.' . $ext,
            'public'
        );

        $dok = $user->dokumens()->create([
            'jenis' => $jenis,
            'path' => $path,
            'nama_asli' => Str::limit($file->getClientOriginalName(), 200, ''),
        ]);

        $kelebihan = $user->dokumens()->where('jenis', $jenis)
            ->orderByDesc('created_at')->orderByDesc('id')
            ->skip(MasyarakatDokumen::MAKS_VERSI)->take(100)->get();

        foreach ($kelebihan as $lama) {
            Storage::disk('public')->delete($lama->path);
            $lama->delete();
        }

        return $dok;
    }

    /**
     * Impor berkas yang diunggah saat pengajuan PBI APBD (KTP, KK, dst) ke menu Dokumen milik pemohon.
     * Berkas disalin (bukan dipindah), dan hanya jenis yang belum punya dokumen sama sekali yang diimpor,
     * jadi dokumen yang sudah diunggah atau dihapus sendiri oleh warga tidak ditimpa.
     * Mengembalikan jumlah dokumen yang berhasil diimpor.
     */
    public static function imporDariPbi(PbiApbd $pbi): int
    {
        if (! $pbi->user_id || ! ($user = User::find($pbi->user_id))) {
            return 0;
        }

        $disk = Storage::disk('public');
        $jumlah = 0;

        // Foto yang dijawab warga di form Lengkapi Data (pertanyaan bertipe foto), dikenali dari nama pertanyaannya
        $fotoGc = GcPbiJawaban::where('pbi_apbd_id', $pbi->id)
            ->where('tipe_input', 'foto')
            ->whereNotNull('jawaban')
            ->get();

        foreach (self::TARGET as $jenis => $t) {
            if (empty($t['pbi'])) {
                continue;
            }

            // Sumber 1: kolom lampiran di pengajuan; sumber 2: jawaban foto di form Lengkapi Data
            $sumber = $pbi->{$t['pbi']};

            if (blank($sumber)) {
                $sumber = $fotoGc->first(fn ($j) => self::cocokJenis($jenis, (string) $j->nama_parameter))->jawaban ?? null;
            }

            if (blank($sumber) || ! $disk->exists($sumber)) {
                continue;
            }

            if (MasyarakatDokumen::where('user_id', $user->id)->where('jenis', $jenis)->exists()) {
                continue;
            }

            $ext = strtolower(pathinfo($sumber, PATHINFO_EXTENSION));
            $dasar = basename($sumber);
            // Berkas di pengajuan bernama "acak6__nama-asli.ext": ambil nama aslinya kembali
            $namaAsli = str_contains($dasar, '__') ? Str::after($dasar, '__') : $dasar;

            $tujuan = "masyarakat-dokumen/{$user->id}/{$jenis}-" . now()->format('YmdHis') . '-' . Str::random(5) . ($ext ? ".{$ext}" : '');

            $disk->copy($sumber, $tujuan);

            $user->dokumens()->create([
                'jenis' => $jenis,
                'path' => $tujuan,
                'nama_asli' => Str::limit($namaAsli, 200, ''),
            ]);

            $jumlah++;
        }

        return $jumlah;
    }

    /** Apakah nama pertanyaan foto di form Lengkapi Data merujuk ke jenis dokumen ini. */
    private static function cocokJenis(string $jenis, string $nama): bool
    {
        $n = Str::lower($nama);

        return match ($jenis) {
            'ktp' => str_contains($n, 'ktp') && ! str_contains($n, 'selfie'),
            'kk' => (bool) preg_match('/\bkk\b|kartu keluarga/', $n),
            'surat_rawat_inap' => str_contains($n, 'rawat inap'),
            'screenshot_desil' => str_contains($n, 'desil'),
            default => false,
        };
    }

    /**
     * Pasang versi dokumen ini ke pengajuan PBI APBD & DTSEN terbaru milik user
     * (hanya yang masih bisa diubah warga), MENGGANTI berkas yang sebelumnya melekat.
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
            // Hanya saat masih di tahap awal / ditolak / dikembalikan; saat di meja Kabid / Kadis berkas tidak diganti
            if ($p && $p->masyarakatBisaEdit()) {
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
