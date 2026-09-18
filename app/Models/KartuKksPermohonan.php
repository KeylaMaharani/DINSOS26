<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KartuKksPermohonan extends Model
{
    protected $table = 'kartu_kks_permohonan';

    protected $fillable = [
        'user_id',
        'no_permohonan',
        'nik',
        'nama_pemohon',
        'alamat',
        'latitude',
        'longitude',
        'masalah_kartu',
        'nomor_kehilangan_polisi',
        'status',
        'current_role_id',
        'kelurahan',
        'tanggal_insert',
        'created_by',
    ];

    protected $casts = [
        'tanggal_insert' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // Daftar status resmi alur, dipakai controller & view supaya konsisten
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_VERIFIKASI_KELURAHAN = 'verifikasi_kelurahan';
    public const STATUS_TTD_LURAH = 'ttd_lurah';
    public const STATUS_VALIDASI_DINSOS = 'validasi_dinsos';
    public const STATUS_DIPROSES_KABIN = 'diproses_kabin';
    public const STATUS_DISETUJUI_KADIS = 'disetujui_kadis';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';

    /**
     * Auto-generate no_permohonan (format: KKS-2026-0001) setiap kali
     * data baru dibuat, KECUALI sudah diisi manual sebelumnya.
     * Ini dipasang di sini (bukan di controller) supaya controller
     * yang sudah ada (mis. KartuKksAjuanController) TIDAK perlu diubah.
     */
    protected static function booted(): void
    {
        static::creating(function (self $permohonan) {
            if (empty($permohonan->no_permohonan)) {
                $year = now()->year;
                $count = self::whereYear('created_at', $year)->count() + 1;
                $permohonan->no_permohonan = 'KKS-' . $year . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Pakai `no_permohonan` sebagai slug di URL (route model binding),
     * bukan `id` numerik. Berlaku otomatis untuk semua route dengan
     * parameter {permohonan}, selama route() di Blade/redirect pass
     * objek model (bukan ->id) — sesuai pola yang sudah dipakai di modul PBI APBN.
     */
    public function getRouteKeyName(): string
    {
        return 'no_permohonan';
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_VERIFIKASI_KELURAHAN => 'Verifikasi Kelurahan',
            self::STATUS_TTD_LURAH => 'TTD Lurah',
            self::STATUS_VALIDASI_DINSOS => 'Validasi Dinsos',
            self::STATUS_DIPROSES_KABIN => 'Diproses Kabin',
            self::STATUS_DISETUJUI_KADIS => 'Disetujui Kadis',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    public function detail(): HasOne
    {
        return $this->hasOne(KartuKksDetail::class, 'permohonan_id');
    }

    public function lampiran(): HasOne
    {
        return $this->hasOne(KartuKksLampiran::class, 'permohonan_id');
    }

    public function surat(): HasOne
    {
        return $this->hasOne(KartuKksSurat::class, 'permohonan_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(KartuKksLog::class, 'permohonan_id')->orderBy('tanggal_proses');
    }

    public function currentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'current_role_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
