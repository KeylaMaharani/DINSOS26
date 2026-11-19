<?php

namespace App\Support;

use App\Models\Dokumen;
use App\Models\KartuKksPermohonan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\Process\Process;

class KartuKksSurat
{
    /** Kode template di menu Dokumen. */
    public const KODE_TEMPLATE = 'surat-keterangan-kks';

    public static function buatDanSimpan(KartuKksPermohonan $p, User $kadis, ?string $passphrase = null): string
    {
        $p->loadMissing('detail', 'surat');

        $nomor = '400.9.11.3/' . str_pad((string) $p->id, 6, '0', STR_PAD_LEFT) . ' - PFM';

        // QR berisi URL publik (signed, tanpa kedaluwarsa) yang membuka PDF surat keterangan.
        $qr = self::buatQr(
            URL::signedRoute('kks.keterangan.verifikasi', ['permohonan' => $p])
        );

        $template = Dokumen::where('kode', self::KODE_TEMPLATE)->first();

        if ($template && Storage::disk('local')->exists($template->file_path)) {
            $pdf = self::dariTemplate($template, $p, $kadis, $nomor, $qr);
        } else {
            // Fallback: view HTML lama (dipakai kalau template belum diunggah).
            $pdf = Pdf::loadView('pdf.kartu-kks-surat', [
                'p' => $p,
                'd' => $p->detail,
                'kadis' => $kadis,
                'nomor' => $nomor,
                'qr' => $qr,
                'logo' => self::logoPath(),
            ])->setPaper('a4')->output();
        }

        $pdf = TteSigner::sign($pdf, $kadis, $passphrase);

        $path = 'kartu-kks/surat/' . $p->no_permohonan . '.pdf';
        Storage::disk('public')->put($path, $pdf);

        $p->surat()->updateOrCreate([], [
            'surat_keterangan_dinsos' => $path,
            'surat_keterangan_dinsos_at' => now(),
        ]);

        return $path;
    }

    /**
     * Isi template .docx -> konversi ke PDF -> kembalikan isi PDF (string).
     */
    private static function dariTemplate(Dokumen $template, KartuKksPermohonan $p, User $kadis, string $nomor, string $qrDataUri): string
    {
        $d = $p->detail;
        $disk = Storage::disk('local');

        $tmpRel = 'dokumen-preview/kks-' . Str::uuid();
        $disk->makeDirectory($tmpRel);
        $tmpDir = $disk->path($tmpRel);

        try {
            Settings::setOutputEscapingEnabled(true);

            $tpl = new TemplateProcessor($disk->path($template->file_path));

            $masalah = strtolower((string) $d->masalah_kartu);
            $redaksi = $masalah === 'hilang'
                ? 'membuatkan kartu KKS yang baru'
                : 'memproses penggantian/perbaikan kartu KKS yang baru';

            $tpl->setValues([
                'no_sk' => $nomor,
                'tgl_sk' => now()->locale('id')->translatedFormat('d F Y'),
                'ditujukan_kpada' => 'Kepala Bank Penyalur KKS',
                'nama_pemohon' => $d->nama,
                'alamat' => $d->alamat . ($p->kelurahan ? ', Kelurahan ' . strtoupper($p->kelurahan) : ''),
                'pekerjaan' => $d->pekerjaan,
                'nik' => $d->nik,
                'no_kk' => $d->no_kk,
                'no_kks' => $d->no_kartu_kks,
                'no_rek' => $d->no_rekening,
                'no_sktm' => '-',
                'masalah_kartu' => $d->masalah_kartu,
                'redaksi' => $redaksi,
                'nama_lengkap_kadin' => $kadis->name,
                'pangkat_kadin' => $kadis->pangkat ?? '',
                'nip_kadin' => $kadis->nip ?? '',
            ]);

            // Gambar: di docx tulis ${qrcode} dan ${logo} (menggantikan teks "Qrcode" / "Logo").
            $png = base64_decode(preg_replace('#^data:image/[\w.+-]+;base64,#', '', $qrDataUri), true);
            if ($png === false || substr($png, 0, 4) !== "\x89PNG") {
                throw new \RuntimeException('buatQr() tidak menghasilkan PNG yang valid.');
            }

            $qrFile = $tmpDir . DIRECTORY_SEPARATOR . 'qr.png';
            file_put_contents($qrFile, $png);

            $variabel = $tpl->getVariables();

            $namaQr = collect(['qr_code', 'qrcode'])->first(fn ($n) => in_array($n, $variabel, true));

            if ($namaQr) {
                $tpl->setImageValue($namaQr, ['path' => $qrFile, 'width' => 100, 'height' => 100, 'ratio' => true]);
            } else {
                Log::warning('Surat keterangan KKS: placeholder QR (${qr_code}/${qrcode}) tidak terbaca di template.', ['terbaca' => $variabel]);
            }

            if (in_array('logo', $variabel, true)) {
                $tpl->setImageValue('logo', ['path' => self::logoPath(), 'width' => 60, 'height' => 60, 'ratio' => true]);
            }

            $docx = $tmpDir . DIRECTORY_SEPARATOR . 'surat.docx';
            $tpl->saveAs($docx);

            $log = self::docxKePdf($docx, $tmpDir);

            $hasil = $tmpDir . DIRECTORY_SEPARATOR . 'surat.pdf';
            if (! is_file($hasil)) {
                $isi = implode(', ', @scandir($tmpDir) ?: []);
                throw new \RuntimeException('Konversi surat KKS ke PDF gagal: ' . $log . ' | isi_outdir=[' . $isi . ']');
            }

            return (string) file_get_contents($hasil);
        } finally {
            $disk->deleteDirectory($tmpRel);
        }
    }

        public static function docxKePdf(string $docx, string $outDir): string
    {
        $norm = fn (string $p) => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p);

        $soffice = $norm((string) config('services.libreoffice.path'));
        $docx = $norm($docx);
        $outDir = $norm($outDir);

        if (DIRECTORY_SEPARATOR === '\\') {
            $bin = preg_replace('/soffice\.exe$/i', 'soffice.bin', $soffice);
            if (! is_file($bin)) {
                $bin = $soffice;
            }

            $proc = new Process([$bin, '--headless', '--convert-to', 'pdf', '--outdir', $outDir, $docx]);
            $proc->setTimeout(180);
            $proc->run();

            return 'bin=' . $bin . ' exit=' . var_export($proc->getExitCode(), true) . ' err=' . trim($proc->getErrorOutput() . ' ' . $proc->getOutput());
        }

        $profilDir = storage_path('app/lo_profile');
        if (! is_dir($profilDir)) {
            @mkdir($profilDir, 0775, true);
        }

        $proc = new Process([
            $soffice,
            '-env:UserInstallation=file://' . $profilDir,
            '--headless', '--norestore', '--nologo', '--nolockcheck',
            '--convert-to', 'pdf', '--outdir', $outDir, $docx,
        ]);
        $proc->setTimeout(180);
        $proc->run();

        return 'exit=' . var_export($proc->getExitCode(), true) . ' err=' . trim($proc->getErrorOutput());
    }

    /**
     * Logo Kota Bogor: public/assets/img/logo/bogor.(png|jpg|jpeg).
     * Fallback ke logo.png jika file bogor.* tidak ditemukan.
     */
    private static function logoPath(): string
    {
        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            $file = public_path('assets/img/logo/bogor.' . $ext);
            if (is_file($file)) {
                return $file;
            }
        }

        return public_path('assets/img/logo/logo.png');
    }

    /**
     * Render QR menjadi data-URI PNG. Kompatibel dengan
     * chillerlan/php-qrcode v4, v5, dan v6.
     */
    public static function buatQr(string $data): string
    {
        if (defined('chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG')) {
            // v5
            $options = new QROptions([
                'outputType' => constant('chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG'),
                'outputBase64' => true,
                'scale' => 4,
            ]);
        } elseif (defined('chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG')) {
            // v4
            $options = new QROptions([
                'outputType' => constant('chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG'),
                'imageBase64' => true,
                'scale' => 4,
            ]);
        } else {
            // v6
            $options = new QROptions([
                'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                'outputBase64' => true,
                'scale' => 4,
            ]);
        }

        return (new QRCode($options))->render($data);
    }
}