<?php

namespace App\Support;

use App\Models\GcKriteriaParameter;
use App\Models\GcPbiJawaban;
use App\Models\PbiApbd;
use Illuminate\Support\Str;


class PbiGcForm
{
    /**
     * Parameter GC yang tampil di form masyarakat, urut mengikuti master.
     * Tanpa $pbi = daftar penuh (dipakai pemanggil lama yang belum tahu pengajuannya).
     */
    public static function parameters(?PbiApbd $pbi = null)
    {
        if ($pbi && $pbi->form_lama) {
            return GcKriteriaParameter::daftarMasyarakat();
        }

        return GcKriteriaParameter::aktif()->with('opsis')->get();
    }

    /** Pertanyaan pemicu di Ketenagakerjaan ("Apakah responden bekerja ...?"). */
    public static function gate($params): ?GcKriteriaParameter
    {
        return $params->first(fn ($p) => $p->kelompok === 'ketenagakerjaan'
            && Str::startsWith(Str::lower(trim($p->nama)), 'apakah responden bekerja'));
    }

    /** Field Ketenagakerjaan lain yang hanya berlaku bila responden bekerja. */
    public static function dependenIds($params): array
    {
        $gate = self::gate($params);

        return $gate
            ? $params->where('kelompok', 'ketenagakerjaan')->where('id', '!=', $gate->id)->pluck('id')->all()
            : [];
    }

    public static function lintangBujur(GcKriteriaParameter $p): bool
    {
        return Str::contains(Str::lower($p->nama), ['lintang', 'bujur']);
    }

    /** Parameter foto "Foto kartu keluarga": memakai Scan KK yang sudah diunggah di pengajuan PBI. */
    public static function isFotoKk(GcKriteriaParameter $p): bool
    {
        return $p->tipe_input === 'foto'
            && Str::contains(Str::lower((string) $p->nama), 'kartu keluarga');
    }

    /**
     * Tidak diwajibkan dari warga:
     *  - koordinat / lintang / bujur (diambil otomatis dari lokasi rumah), atau
     *  - pertanyaan yang "Wajib diisi"-nya dimatikan di Master Kriteria Ground Check.
     */
    public static function opsional(GcKriteriaParameter $p): bool
    {
        return $p->tipe_input === 'koordinat'
            || self::lintangBujur($p)
            || ! $p->wajib;
    }

    /** id parameter yang wajib diisi, diberi peta id => jawaban yang sudah ada. */
    public static function wajibIds($params, array $jawaban): array
    {
        $gate = self::gate($params);
        $bekerja = $gate && (($jawaban[$gate->id] ?? null) === 'Ya');
        $dependen = self::dependenIds($params);

        return $params
            ->filter(fn ($p) => ! self::opsional($p) && (! in_array($p->id, $dependen, true) || $bekerja))
            ->pluck('id')->all();
    }

    /** id yang selalu wajib (bukan opsional, bukan turunan "bekerja") — untuk hitungan di daftar. */
    public static function idTetap(?PbiApbd $pbi = null): array
    {
        $params = self::parameters($pbi);
        $dependen = self::dependenIds($params);

        return $params
            ->filter(fn ($p) => ! self::opsional($p) && ! in_array($p->id, $dependen, true))
            ->pluck('id')->all();
    }

    /**
     * Isi jawaban yang masih kosong dari jawaban PBI (pbi_apbd_jawabans) yang padanannya sama.
     * Hanya parameter 'pilihan' bertautan PBI, dan hanya bila labelnya ada di opsi parameter GC.
     * Mengembalikan peta id parameter GC => jawaban.
     */
    public static function isiDariJawabanPbi(PbiApbd $pbi, $params, array $nilai): array
    {
        $jawabanPbi = $pbi->jawabans()
            ->whereNotNull('parameter_id')
            ->pluck('jawaban_label', 'parameter_id')
            ->all();

        if (! $jawabanPbi) {
            return $nilai;
        }

        foreach ($params as $p) {
            if (! $p->pbi_parameter_id || $p->tipe_input !== 'pilihan' || filled($nilai[$p->id] ?? null)) {
                continue;
            }

            $label = $jawabanPbi[$p->pbi_parameter_id] ?? null;

            if ($label !== null && $p->opsis->contains('label', $label)) {
                $nilai[$p->id] = $label;
            }
        }

        return $nilai;
    }

    /**
     * Nilai tampilan form masyarakat: lengkapi isian awal dengan
     *  - jawaban PBI yang padanannya sama (akun lama), dan
     *  - Scan KK pengajuan untuk "Foto kartu keluarga" (supaya tidak unggah dua kali).
     */
    public static function lengkapiNilai(PbiApbd $pbi, $params, array $nilai): array
    {
        $nilai = self::isiDariJawabanPbi($pbi, $params, $nilai);

        if (filled($pbi->scan_kk)) {
            foreach ($params as $p) {
                if (self::isFotoKk($p) && blank($nilai[$p->id] ?? null)) {
                    $nilai[$p->id] = $pbi->scan_kk;
                }
            }
        }

        return $nilai;
    }

    /** Jawaban yang dihitung "terisi": jawaban GC + (akun lama) jawaban PBI + Scan KK. */
    private static function jawabanEfektif(PbiApbd $pbi, $params, array $jawaban): array
    {
        if ($pbi->form_lama) {
            $jawaban = self::isiDariJawabanPbi($pbi, $params, $jawaban);
        }

        if (filled($pbi->scan_kk)) {
            foreach ($params as $p) {
                if (self::isFotoKk($p) && blank($jawaban[$p->id] ?? null)) {
                    $jawaban[$p->id] = $pbi->scan_kk;
                }
            }
        }

        return $jawaban;
    }

    /** ['total' => jumlah isian wajib, 'terjawab' => yang sudah terisi] untuk satu pengajuan. */
    public static function kelengkapan(?PbiApbd $pbi): array
    {
        $params = self::parameters($pbi);

        $jawaban = $pbi
            ? GcPbiJawaban::where('pbi_apbd_id', $pbi->id)->whereNotNull('jawaban')->pluck('jawaban', 'parameter_id')->all()
            : [];

        if ($pbi) {
            $jawaban = self::jawabanEfektif($pbi, $params, $jawaban);
        }

        $wajib = self::wajibIds($params, $jawaban);

        return [
            'total' => count($wajib),
            'terjawab' => count(array_filter($wajib, fn ($id) => isset($jawaban[$id]) && $jawaban[$id] !== '')),
        ];
    }

    /** Persentase kelengkapan 0-100. Angka 100 hanya muncul bila semua isian wajib terjawab (tidak dibulatkan ke atas). */
    public static function persen(array $kel): int
    {
        if ($kel['total'] <= 0) {
            return 0;
        }

        if ($kel['terjawab'] >= $kel['total']) {
            return 100;
        }

        return min(99, (int) floor($kel['terjawab'] / $kel['total'] * 100));
    }
}