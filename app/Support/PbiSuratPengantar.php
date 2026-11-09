<?php

namespace App\Support;

use App\Models\PbiApbd;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Membuat Surat Pengantar Kelurahan otomatis dari template .docx.
 * Template: storage/app/templates/SuratPengantarPBI.docx
 *  - Placeholder teks ${...} diisi oleh PbiSuratIsian.
 *  - Placeholder gambar ${ttd_kelurahan} (opsional) diisi TTD Lurah bila $ttdPath diberikan.
 */
class PbiSuratPengantar
{
    public static function templatePath(): string
    {
        return storage_path('app/templates/SuratPengantarPBI.docx');
    }

    /**
     * Buat ulang surat dari template asli dan simpan ke kolom scan_surat_pengantar.
     * Mengembalikan pesan info/peringatan (string kosong bila semua lancar).
     */
    public static function generate(PbiApbd $m, User $user, ?string $ttdPath = null): string
    {
        $src = self::templatePath();
        if (! is_file($src)) {
            return 'Surat pengantar belum dibuat: template tidak ditemukan di storage/app/templates/SuratPengantarPBI.docx.';
        }

        $disk = Storage::disk('public');
        $lama = $m->scan_surat_pengantar;
        $path = 'pbi-apbd/lampiran/' . Str::random(6) . '__surat-pengantar-' . Str::slug($m->no_registrasi) . '.docx';
        $disk->put($path, file_get_contents($src));

        $peringatan = '';
        if ($ttdPath && $disk->exists($ttdPath)) {
            try {
                $tp = new \PhpOffice\PhpWord\TemplateProcessor($disk->path($path));
                if (in_array('ttd_kelurahan', $tp->getVariables(), true)) {
                    $tp->setImageValue('ttd_kelurahan', [
                        'path' => $disk->path($ttdPath),
                        'width' => 140,
                        'height' => 70,
                        'ratio' => true,
                    ]);
                    $tp->saveAs($disk->path($path));
                } else {
                    $peringatan = 'TTD tidak disisipkan: template belum punya placeholder ${ttd_kelurahan}.';
                }
            } catch (\Throwable $e) {
                report($e);
                $peringatan = 'TTD gagal disisipkan ke surat (cek storage/logs/laravel.log).';
            }
        }

        $m->update(['scan_surat_pengantar' => $path]);

        if ($lama && $lama !== $path) {
            $disk->delete($lama);
            $disk->delete('pbi-apbd/template/' . basename($lama)); // sisa salinan template versi lama
        }

        return trim(PbiSuratIsian::isiBerkas($m->fresh(), $path, $user, null) . ' ' . $peringatan);
    }
}
