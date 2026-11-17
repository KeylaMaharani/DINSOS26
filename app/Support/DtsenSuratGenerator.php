<?php

namespace App\Support;

use App\Models\Dokumen;
use App\Models\DtsenPermohonan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Symfony\Component\Process\Process;

/**
 * Surat Keterangan DTSEN dari template menu Dokumen (kode: surat-keterangan-dtsen, .docx).
 * Alur sama dengan KartuKksSurat: isi docx -> PDF (LibreOffice) -> TTE -> simpan di disk public.
 */
class DtsenSuratGenerator
{
    public const KODE_TEMPLATE = 'surat-keterangan-dtsen';

    public static function templateTersedia(): bool
    {
        $dok = Dokumen::where('kode', self::KODE_TEMPLATE)->first();

        return $dok && Storage::disk('local')->exists($dok->file_path);
    }

    public static function buatDanSimpan(DtsenPermohonan $p, User $kadis, ?string $passphrase = null): string
    {
        $template = Dokumen::where('kode', self::KODE_TEMPLATE)->first();

        if (! $template || ! Storage::disk('local')->exists($template->file_path)) {
            throw new \RuntimeException('Template "Surat Keterangan DTSEN" belum diunggah di menu Dokumen.');
        }

        $p->loadMissing('detail', 'bansos', 'surat');

        $nomor = '400.9.11.3/' . str_pad((string) $p->id, 6, '0', STR_PAD_LEFT) . ' - PFM';

        $qr = self::buatQr(
            'Surat Keterangan DTSEN ' . $p->no_permohonan . ' | ' . $nomor . ' | ' . ($kadis->name ?? '')
        );

        $pdf = self::dariTemplate($template, $p, $kadis, $nomor, $qr);
        $pdf = TteSigner::sign($pdf, $kadis, $passphrase);

        $path = 'dtsen/surat/' . $p->no_permohonan . '.pdf';
        Storage::disk('public')->put($path, $pdf);

        $p->surat()->updateOrCreate([], [
            'surat_keterangan_dtsen_digital' => $path,
            'surat_keterangan_dtsen_digital_at' => now(),
        ]);

        return $path;
    }

    private static function dariTemplate(Dokumen $template, DtsenPermohonan $p, User $kadis, string $nomor, string $qrDataUri): string
    {
        $d = $p->detail;
        $b = $p->bansos;
        $disk = Storage::disk('local');

        $tmpRel = 'dokumen-preview/dtsen-' . Str::uuid();
        $disk->makeDirectory($tmpRel);
        $tmpDir = $disk->path($tmpRel);

        $tgl = fn ($v) => $v ? Carbon::parse($v)->locale('id')->translatedFormat('d F Y') : '-';
        $ya = fn ($v) => $v ? 'Ya' : 'Tidak';
        $satuBaris = fn ($v) => trim(preg_replace('/\s+/', ' ', (string) $v));

        try {
            Settings::setOutputEscapingEnabled(true);

            $tpl = new TemplateProcessor($disk->path($template->file_path));

            $nama = $d->nama ?? $p->nama_pemohon;

            $tpl->setValues([
                'no_sk' => $nomor,
                'tgl_sk' => $tgl(now()),
                'tanggal_surat' => $tgl(now()),
                'no_permohonan' => $p->no_permohonan,
                'nama' => $nama,
                'nama_pemohon' => $nama,
                'nik' => $d->nik ?? $p->nik,
                'no_kk' => $d->no_kk ?? '-',
                'tanggal_lahir' => $tgl($d->tanggal_lahir ?? null),
                'alamat' => $satuBaris($d->alamat ?? $p->alamat) . ($p->kelurahan ? ', Kelurahan ' . strtoupper($p->kelurahan) : ''),
                'kelurahan' => $p->kelurahan ?? '-',
                'alasan_cetak' => $p->alasan_cetak ?? '-',
                'desil' => $b->peringkat_kesejahteraan_keluarga ?? '-',
                'bnpt' => $ya($b->bnpt ?? false),
                'pkh' => $ya($b->pkh ?? false),
                'pbi' => $ya($b->pbi ?? false),
                'yatim_piatu' => $ya($b->yatim_piatu ?? false),
                'tanggal_cetak' => $tgl($b->tanggal_cetak ?? null),
                'berlaku_sampai' => $tgl($b->berlaku_sampai ?? null),
                'dicetak_oleh' => $b->dicetak_oleh ?? '-',
                // nama penandatangan: dua gaya penamaan (sama dengan template KKS)
                'nama_kadis' => $kadis->name,
                'pangkat_kadis' => $kadis->pangkat ?? '',
                'nip_kadis' => $kadis->nip ?? '',
                'nama_lengkap_kadin' => $kadis->name,
                'pangkat_kadin' => $kadis->pangkat ?? '',
                'nip_kadin' => $kadis->nip ?? '',
            ]);

            $qrFile = $tmpDir . DIRECTORY_SEPARATOR . 'qr.png';
            file_put_contents($qrFile, base64_decode(preg_replace('#^data:image/\w+;base64,#', '', $qrDataUri)));

            try {
                $tpl->setImageValue('qrcode', ['path' => $qrFile, 'width' => 80, 'height' => 80, 'ratio' => true]);
                $tpl->setImageValue('logo', ['path' => self::logoPath(), 'width' => 60, 'height' => 60, 'ratio' => true]);
            } catch (\Throwable $e) {
                // Placeholder gambar tidak ada di template -> abaikan.
            }

            $docx = $tmpDir . DIRECTORY_SEPARATOR . 'surat.docx';
            $tpl->saveAs($docx);

            $log = self::docxKePdf($docx, $tmpDir);

            $hasil = $tmpDir . DIRECTORY_SEPARATOR . 'surat.pdf';
            if (! is_file($hasil)) {
                $isi = implode(', ', @scandir($tmpDir) ?: []);
                throw new \RuntimeException('Konversi surat DTSEN ke PDF gagal: ' . $log . ' | isi_outdir=[' . $isi . ']');
            }

            return (string) file_get_contents($hasil);
        } finally {
            $disk->deleteDirectory($tmpRel);
        }
    }

    private static function docxKePdf(string $docx, string $outDir): string
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

        // Profil LibreOffice dipisah dari KKS supaya dua konversi bersamaan tidak saling mengunci
        $profilDir = storage_path('app/lo_profile_dtsen');
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

    private static function buatQr(string $data): string
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