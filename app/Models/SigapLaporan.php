<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SigapLaporan extends Model
{
    protected $table = 'sigap_laporan';

    protected $fillable = [
        'no_tiket', 'nomor_surat_laporan', 'user_id', 'nama_pelapor', 'peran_pelapor',
        'jenis_bencana', 'tanggal_kejadian', 'jumlah_korban', 'no_kk', 'kerusakan', 'kerugian',
        'bantuan_dibutuhkan', 'kecamatan', 'kelurahan', 'rt', 'rw', 'alamat',
        'latitude', 'longitude', 'lokasi_diperbarui_at',
        'status', 'current_role_id', 'ditutup_at',
    ];

    protected $casts = [
        'tanggal_kejadian' => 'datetime',
        'lokasi_diperbarui_at' => 'datetime',
        'ditutup_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public const STATUS_DILAPORKAN = 'dilaporkan';                 // menunggu assessment Tagana
    public const STATUS_VERIFIKASI_PERLINSOS = 'verifikasi_perlinsos';
    public const STATUS_PROSES_BARANG = 'proses_barang';           // Pengurus Barang
    public const STATUS_TTD_KADIS = 'ttd_kadis';
    public const STATUS_PENGIRIMAN = 'pengiriman';
    public const STATUS_BAST = 'bast';                             // menunggu BAST dari Kelurahan/OPD
    public const STATUS_PENYELESAIAN = 'penyelesaian';             // tutup tiket
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';

    public const JENIS_BENCANA = [
        'Banjir', 'Tanah Longsor', 'Pohon Tumbang', 'Angin Kencang / Puting Beliung',
        'Kebakaran', 'Gempa Bumi', 'Lainnya',
    ];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DILAPORKAN => 'Dilaporkan - Menunggu Assessment',
            self::STATUS_VERIFIKASI_PERLINSOS => 'Verifikasi Perlinsos',
            self::STATUS_PROSES_BARANG => 'Proses Pengurus Barang',
            self::STATUS_TTD_KADIS => 'Menunggu TTD Kadis',
            self::STATUS_PENGIRIMAN => 'Pengiriman Bantuan',
            self::STATUS_BAST => 'Menunggu BAST Kelurahan/OPD',
            self::STATUS_PENYELESAIAN => 'Penyelesaian Laporan',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    public static function statusFinal(): array
    {
        return [self::STATUS_SELESAI, self::STATUS_DITOLAK];
    }

    /** URL memakai no_tiket, sama pola dengan no_permohonan di Kartu KKS. */
    public function getRouteKeyName(): string
    {
        return 'no_tiket';
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function currentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'current_role_id');
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(SigapAssessment::class, 'laporan_id');
    }

    public function permohonanBarang(): HasOne
    {
        return $this->hasOne(SigapPermohonanBarang::class, 'laporan_id');
    }

    public function pengeluaran(): HasOne
    {
        return $this->hasOne(SigapPengeluaran::class, 'laporan_id');
    }

    public function bast(): HasOne
    {
        return $this->hasOne(SigapBast::class, 'laporan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SigapItem::class, 'laporan_id')->orderBy('id');
    }

    public function dokumentasi(): HasMany
    {
        return $this->hasMany(SigapDokumentasi::class, 'laporan_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SigapLog::class, 'laporan_id')->orderBy('tanggal_proses');
    }
}
