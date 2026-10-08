<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Penomoran berurutan per jenis surat per tahun.
 *
 * Aman dari nomor kembar: nomor diambil lewat baris counter yang dikunci
 * (lockForUpdate) di dalam transaksi, jadi dua orang yang menyimpan
 * bersamaan tidak akan mendapat nomor yang sama.
 *
 * FORMAT NOMOR MASIH SEMENTARA -- sesuaikan di FORMAT di bawah setelah
 * contoh surat resmi Dinsos diterima.
 */
class NomorSuratService
{
    public const TIKET = 'tiket';
    public const LAPORAN = 'laporan';
    public const NOTA_DINAS = 'nota_dinas';
    public const PERMOHONAN = 'permohonan';
    public const PENGELUARAN = 'pengeluaran';
    public const BAST = 'bast';

    /** kode singkatan di tengah nomor surat */
    private const KODE = [
        self::LAPORAN => 'LKB',
        self::NOTA_DINAS => 'ND',
        self::PERMOHONAN => 'PB',
        self::PENGELUARAN => 'SPB',
        self::BAST => 'BAST',
    ];

    private const ROMAWI = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    /**
     * Ambil nomor urut berikutnya. WAJIB dipanggil di dalam DB::transaction()
     * yang sama dengan penyimpanan datanya, supaya nomor yang sudah dipakai
     * ikut dibatalkan kalau penyimpanan gagal (tidak ada nomor loncat).
     */
    public function urut(string $jenis, ?int $tahun = null): int
    {
        $tahun ??= now()->year;

        DB::table('sigap_nomor_urut')->insertOrIgnore([
            'jenis' => $jenis,
            'tahun' => $tahun,
            'terakhir' => 0,
        ]);

        $row = DB::table('sigap_nomor_urut')
            ->where('jenis', $jenis)
            ->where('tahun', $tahun)
            ->lockForUpdate()
            ->first();

        $next = $row->terakhir + 1;

        DB::table('sigap_nomor_urut')->where('id', $row->id)->update(['terakhir' => $next]);

        return $next;
    }

    /** Nomor tiket, contoh: SGP-2026-0001 */
    public function tiket(?Carbon $tanggal = null): string
    {
        $tanggal ??= now();

        return 'SGP-' . $tanggal->year . '-' . str_pad((string) $this->urut(self::TIKET, $tanggal->year), 4, '0', STR_PAD_LEFT);
    }

    /** Nomor surat, contoh: 001/LKB/X/2026 */
    public function surat(string $jenis, ?Carbon $tanggal = null): string
    {
        $tanggal ??= now();
        $no = $this->urut($jenis, $tanggal->year);

        return sprintf(
            '%03d/%s/%s/%d',
            $no,
            self::KODE[$jenis] ?? strtoupper($jenis),
            self::ROMAWI[$tanggal->month],
            $tanggal->year
        );
    }
}
