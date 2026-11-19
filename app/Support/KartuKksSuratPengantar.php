<?php

namespace App\Support;

use App\Models\Dokumen;
use App\Models\KartuKksPermohonan;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

class KartuKksSuratPengantar
{
    /** Kode template di menu Dokumen. */
    public const KODE_TEMPLATE = 'surat-pengantar-kks';

    public static function buatDanSimpan(KartuKksPermohonan $p, User $lurah, ?string $passphrase = null): string
    {
        $p->loadMissing('detail', 'surat');

        $template = Dokumen::where('kode', self::KODE_TEMPLATE)->first();

        if (! $template || ! Storage::disk('local')->exists($template->file_path)) {
            throw new \RuntimeException('Template surat pengantar KKS (kode "' . self::KODE_TEMPLATE . '") belum diunggah di menu Dokumen.');
        }

        $pdf = self::dariTemplate($template, $p, $lurah);
        $pdf = TteSigner::sign($pdf, $lurah, $passphrase);

        $path = 'kartu-kks/surat/pengantar-' . $p->no_permohonan . '.pdf';
        Storage::disk('public')->put($path, $pdf);

        $p->surat()->updateOrCreate([], [
            'surat_pengantar_kelurahan' => $path,
            'surat_pengantar_kelurahan_at' => now(),
        ]);

        return $path;
    }

    private static function dariTemplate(Dokumen $template, KartuKksPermohonan $p, User $lurah): string
    {
        $d = $p->detail;
        $disk = Storage::disk('local');

        $nomor = str_pad((string) $p->id, 6, '0', STR_PAD_LEFT)
            . '/SOLID/' . self::romawi((int) now()->format('n')) . '/' . now()->year;

        // QR berisi URL publik (signed, tanpa kedaluwarsa) yang membuka PDF surat pengantar.
        $urlVerifikasi = URL::signedRoute('kks.pengantar.verifikasi', ['permohonan' => $p]);
        $qr = KartuKksSurat::buatQr($urlVerifikasi);

        // Wilayah mengikuti akun yang menandatangani (sama seperti surat pengantar PBI);
        // cadangan: kelurahan pada permohonan (mis. bila yang memproses Super Admin).
        $kel = trim((string) ($lurah->kelurahan ?: $p->kelurahan));
        $kec = trim((string) $lurah->kecamatan);
        $alamatKel = (string) (config('pbi_surat.alamat_kelurahan.' . Str::upper($kel))
            ?: ($lurah->alamat_kelurahan ?? ''));

        $tmpRel = 'dokumen-preview/kks-pengantar-' . Str::uuid();
        $disk->makeDirectory($tmpRel);
        $tmpDir = $disk->path($tmpRel);

        try {
            Settings::setOutputEscapingEnabled(true);

            $tpl = new TemplateProcessor($disk->path($template->file_path));

            // Diagnostik: placeholder apa saja yang benar-benar terbaca dari template.
            $variabel = $tpl->getVariables();
            foreach (['qr_code', 'ttd_nama_lurah'] as $wajib) {
                if (! in_array($wajib, $variabel, true)) {
                    Log::warning("Surat pengantar KKS: placeholder \${{$wajib}} tidak terbaca di template.", [
                        'terbaca' => $variabel,
                    ]);
                }
            }

            $tpl->setValues([
                'kec_up' => Str::upper($kec),
                'kec' => $kec,
                'kel_up' => Str::upper($kel),
                'kel' => $kel,
                'kel_ttd' => 'LURAH ' . Str::upper($kel),
                'alamat_kelurahan' => $alamatKel,
                'tgl_sk' => PbiSuratIsian::tanggalId(now()),
                'no_sk' => $nomor,
                'ditujukan_kpada' => 'Kepala Dinas Sosial',
                'nama_pemohon' => $d->nama,
                'alamat_pemohon' => $d->alamat,
                'pekerjaan' => $d->pekerjaan,
                'nik' => $d->nik,
                'no_kk' => $d->no_kk,
                'no_kks' => $d->no_kartu_kks,
                'no_rek' => $d->no_rekening,
                'masalah_kartu' => self::redaksiMasalah($d->masalah_kartu),
                'ttd_nama_lurah' => (string) $lurah->name,
                'jabatan_lurah' => (string) ($lurah->pangkat ?: ($lurah->jabatan ?: 'Lurah')),
            ]);

            // Decode QR -> PNG. Gagal keras kalau bukan PNG supaya ketahuan.
            $png = base64_decode(preg_replace('#^data:image/[\w.+-]+;base64,#', '', $qr), true);
            if ($png === false || substr($png, 0, 4) !== "\x89PNG") {
                throw new \RuntimeException('KartuKksSurat::buatQr() tidak menghasilkan PNG yang valid (kemungkinan SVG).');
            }

            $qrFile = $tmpDir . DIRECTORY_SEPARATOR . 'qr.png';
            file_put_contents($qrFile, $png);

            if (in_array('qr_code', $variabel, true)) {
                $tpl->setImageValue('qr_code', [
                    'path' => $qrFile,
                    'width' => 60,
                    'height' => 60,
                    'ratio' => true,
                ]);
            }

            $docx = $tmpDir . DIRECTORY_SEPARATOR . 'surat.docx';
            $tpl->saveAs($docx);

            $log = KartuKksSurat::docxKePdf($docx, $tmpDir);

            $hasil = $tmpDir . DIRECTORY_SEPARATOR . 'surat.pdf';
            if (! is_file($hasil)) {
                $isi = implode(', ', @scandir($tmpDir) ?: []);
                throw new \RuntimeException('Konversi surat pengantar KKS ke PDF gagal: ' . $log . ' | isi_outdir=[' . $isi . ']');
            }

            return (string) file_get_contents($hasil);
        } finally {
            $disk->deleteDirectory($tmpRel);
        }
    }

    /**
     * Kalimat untuk ${masalah_kartu}: "...Kartu Keluarga Sejahteranya ${masalah_kartu}."
     * Jika hilang, otomatis ditambah keterangan surat kehilangan kepolisian.
     */
    private static function redaksiMasalah(?string $masalah): string
    {
        $m = strtolower(trim((string) $masalah));

        return match ($m) {
            'hilang' => 'hilang (Surat kehilangan dari kepolisian terlampir)',
            'masa berlaku habis' => 'masa berlakunya sudah habis',
            'nama tidak sesuai' => 'namanya tidak sesuai',
            'keterangan lainnya' => 'bermasalah',
            default => $m,
        };
    }

    private static function romawi(int $bulan): string
    {
        return ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$bulan] ?? '';
    }
}