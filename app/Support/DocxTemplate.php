<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Pengisi placeholder ${nama} pada file Word (.docx) TANPA library tambahan (hanya ext-zip).
 *
 * Word sering memecah satu placeholder menjadi beberapa "run" (mis. "${", "kel", "_up", "}"),
 * jadi teks seluruh <w:t> digabung dulu, placeholder dicari di teks gabungan, lalu nilainya
 * ditaruh di run pertama dan sisa pecahannya dikosongkan. Format (bold/ukuran) run pertama dipertahankan.
 *
 * Diproses: word/document.xml, header*.xml, footer*.xml.
 * Placeholder yang namanya TIDAK ada di $nilai dibiarkan apa adanya (supaya salah ketik kelihatan).
 */
class DocxTemplate
{
    /**
     * @param  array<string, mixed>  $nilai  nama placeholder (tanpa ${}) => nilai
     * @return array{terisi: int, tidak_dikenal: string[]}
     */
    public static function isi(string $pathAbsolut, array $nilai): array
    {
        $peta = [];
        foreach ($nilai as $kunci => $v) {
            $peta[strtolower((string) $kunci)] = self::bersihkan($v);
        }

        $zip = new ZipArchive();
        if ($zip->open($pathAbsolut) !== true) {
            throw new RuntimeException('File Word tidak dapat dibuka (bukan .docx yang valid).');
        }

        $terisi = 0;
        $asing = [];

        try {
            $bagian = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $nama = $zip->getNameIndex($i);
                if (preg_match('#^word/(document|header\d*|footer\d*)\.xml$#', (string) $nama)) {
                    $bagian[] = $nama;
                }
            }

            foreach ($bagian as $nama) {
                $xml = $zip->getFromName($nama);
                if ($xml === false) {
                    continue;
                }
                $baru = self::prosesXml($xml, $peta, $terisi, $asing);
                if ($baru !== $xml) {
                    $zip->addFromString($nama, $baru);
                }
            }
        } finally {
            $zip->close();
        }

        return ['terisi' => $terisi, 'tidak_dikenal' => array_values(array_keys($asing))];
    }

    private static function prosesXml(string $xml, array $peta, int &$terisi, array &$asing): string
    {
        // Semua node teks <w:t ...>...</w:t> (tidak cocok dengan <w:tab/>, <w:tbl>, <w:tc>, dst.)
        if (! preg_match_all('/<w:t(\s[^>]*)?>(.*?)<\/w:t>/s', $xml, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return $xml;
        }

        $node = [];
        $semua = '';
        foreach ($m as $x) {
            $teks = $x[2][0];
            $node[] = [
                'mulai' => $x[0][1],
                'panjang' => strlen($x[0][0]),
                'attr' => isset($x[1]) && $x[1][1] >= 0 ? $x[1][0] : '',
                'teks' => $teks,
                'awal' => strlen($semua),
                'akhir' => strlen($semua) + strlen($teks),
            ];
            $semua .= $teks;
        }

        if (! preg_match_all('/\$\{\s*([A-Za-z0-9_.\-]+)\s*\}/', $semua, $mm, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return $xml;
        }

        $span = [];
        foreach ($mm as $x) {
            $kunci = strtolower($x[1][0]);
            if (! array_key_exists($kunci, $peta)) {
                $asing[$x[1][0]] = true;
                continue;
            }
            $span[] = [
                'dari' => $x[0][1],
                'sampai' => $x[0][1] + strlen($x[0][0]),
                'nilai' => htmlspecialchars($peta[$kunci], ENT_XML1 | ENT_QUOTES, 'UTF-8'),
            ];
            $terisi++;
        }

        if (! $span) {
            return $xml;
        }

        // Susun teks baru tiap node
        foreach ($node as $i => $n) {
            $kini = $n['awal'];
            $hasil = '';
            $berubah = false;

            foreach ($span as $s) {
                if ($s['sampai'] <= $n['awal'] || $s['dari'] >= $n['akhir']) {
                    continue; // tidak menyentuh node ini
                }
                $berubah = true;
                $a = max($s['dari'], $n['awal']);
                $b = min($s['sampai'], $n['akhir']);
                $hasil .= substr($semua, $kini, $a - $kini);
                if ($s['dari'] >= $n['awal']) {
                    $hasil .= $s['nilai']; // nilai ditaruh di node tempat placeholder dimulai
                }
                $kini = $b;
            }

            if ($berubah) {
                $hasil .= substr($semua, $kini, $n['akhir'] - $kini);
                $node[$i]['baru'] = $hasil;
            }
        }

        // Tulis balik dari belakang supaya offset node sebelumnya tetap valid
        for ($i = count($node) - 1; $i >= 0; $i--) {
            if (! isset($node[$i]['baru'])) {
                continue;
            }
            $attr = $node[$i]['attr'];
            if (! str_contains($attr, 'xml:space')) {
                $attr .= ' xml:space="preserve"';
            }
            $xml = substr_replace(
                $xml,
                '<w:t' . $attr . '>' . $node[$i]['baru'] . '</w:t>',
                $node[$i]['mulai'],
                $node[$i]['panjang']
            );
        }

        return $xml;
    }

    private static function bersihkan(mixed $v): string
    {
        $v = is_scalar($v) ? (string) $v : '';

        return trim((string) preg_replace('/\s+/u', ' ', $v));
    }
}