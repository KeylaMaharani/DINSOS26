<?php

namespace App\Support;

use App\Models\KartuKksPermohonan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class KartuKksSurat
{
    public static function buatDanSimpan(KartuKksPermohonan $p, User $kadis, ?string $passphrase = null): string
    {
        $p->loadMissing('detail', 'surat');

        $nomor = '400.9.11.3/' . str_pad((string) $p->id, 6, '0', STR_PAD_LEFT) . ' - PFM';

        $qr = self::buatQr(
            'Surat Keterangan KKS ' . $p->no_permohonan . ' | ' . $nomor . ' | ' . ($kadis->name ?? '')
        );

        $pdf = Pdf::loadView('pdf.kartu-kks-surat', [
            'p' => $p,
            'd' => $p->detail,
            'kadis' => $kadis,
            'nomor' => $nomor,
            'qr' => $qr,
            'logo' => self::logoPath(),
        ])->setPaper('a4')->output();

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