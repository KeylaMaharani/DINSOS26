<?php

namespace App\Support;

use App\Models\GcKriteriaParameter;
use App\Models\GcPbiJawaban;
use App\Models\PbiApbd;
use Illuminate\Support\Str;

/**
 * Aturan form "Lengkapi Data Pengajuan" milik masyarakat yang bersumber dari
 * Master Kriteria Ground Check. Dipakai bersama oleh halaman masyarakat,
 * validasi kelurahan, dan penghapusan parameter di master.
 */
class PbiGcForm
{
    /** Semua parameter GC yang aktif (beserta opsi), urut mengikuti master. */
    public static function parameters()
    {
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

    /** Tidak diwajibkan dari warga: foto & koordinat (koordinat diambil otomatis dari lokasi rumah). */
    public static function opsional(GcKriteriaParameter $p): bool
    {
        return in_array($p->tipe_input, ['foto', 'koordinat'], true) || self::lintangBujur($p);
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
    public static function idTetap(): array
    {
        $params = self::parameters();
        $dependen = self::dependenIds($params);

        return $params
            ->filter(fn ($p) => ! self::opsional($p) && ! in_array($p->id, $dependen, true))
            ->pluck('id')->all();
    }

    /** ['total' => jumlah isian wajib, 'terjawab' => yang sudah terisi] untuk satu pengajuan. */
    public static function kelengkapan(?PbiApbd $pbi): array
    {
        $params = self::parameters();

        $jawaban = $pbi
            ? GcPbiJawaban::where('pbi_apbd_id', $pbi->id)->whereNotNull('jawaban')->pluck('jawaban', 'parameter_id')->all()
            : [];

        $wajib = self::wajibIds($params, $jawaban);

        return [
            'total' => count($wajib),
            'terjawab' => count(array_filter($wajib, fn ($id) => isset($jawaban[$id]) && $jawaban[$id] !== '')),
        ];
    }
}
