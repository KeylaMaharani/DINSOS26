<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dokumen milik masyarakat. Maksimal 2 versi terbaru per jenis (lihat MasyarakatDokumenController).
 */
class MasyarakatDokumen extends Model
{
    protected $table = 'masyarakat_dokumens';

    protected $guarded = [];

    /** Jumlah versi yang disimpan per jenis dokumen. */
    public const MAKS_VERSI = 2;

    /** Ukuran maksimal unggahan (KB) & format yang diterima. */
    public const MAKS_KB = 4096;
    public const MIMES = 'jpg,jpeg,png,pdf';

    /**
     * Jenis dokumen: kunci => [label, keterangan, ikon].
     * Pemakaian di pengajuan (kolom PBI / DTSEN) diatur di App\Support\DokumenMasyarakat::TARGET.
     */
    public const JENIS = [
        'ktp' => ['Scan KTP', 'Dipakai di pengajuan PBI APBD dan DTSEN.', 'badge'],
        'kk' => ['Scan Kartu Keluarga (KK)', 'Dipakai di pengajuan PBI APBD.', 'family_restroom'],
        'surat_rawat_inap' => ['Surat Keterangan Rawat Inap', 'Jika ada. Dipakai di pengajuan PBI APBD.', 'local_hospital'],
        'screenshot_desil' => ['Screenshot Pembaharuan Desil', 'Opsional. Dipakai di pengajuan PBI APBD.', 'screenshot_monitor'],
        'screenshot_dtsen' => ['Screenshot DTSEN', 'Dipakai di pengajuan DTSEN.', 'groups'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }

    public function ekstensi(): string
    {
        return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    public function isGambar(): bool
    {
        return in_array($this->ekstensi(), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    public function isPdf(): bool
    {
        return $this->ekstensi() === 'pdf';
    }
}