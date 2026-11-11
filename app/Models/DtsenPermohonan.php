<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DtsenPermohonan extends Model
{
    protected $table = 'dtsen_permohonan';

    protected $fillable = [
        'user_id',
        'no_permohonan',
        'nik',
        'nama_pemohon',
        'alamat',
        'alasan_cetak',
        'status',
        'current_role_id',
        'kelurahan',
        'tanggal_insert',
        'created_by',
    ];

    protected $casts = [
        'tanggal_insert' => 'date',
    ];

    // Alur DTSEN: Validasi Dinsos (Operator) -> Diproses Kabin -> Disetujui Kadis -> Selesai
    // (atau Ditolak di tahap manapun). Tahap Kelurahan & TTD Lurah sudah dihapus.
    public const STATUS_VALIDASI_DINSOS = 'validasi_dinsos';
    public const STATUS_DIPROSES_KABIN = 'diproses_kabin';
    public const STATUS_DISETUJUI_KADIS = 'disetujui_kadis';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';

    /** Tahap awal setiap permohonan baru (atau yang diajukan ulang setelah ditolak). */
    public const STATUS_AWAL = self::STATUS_VALIDASI_DINSOS;

    protected static function booted(): void
    {
        static::creating(function (self $permohonan) {
            if (empty($permohonan->no_permohonan)) {
                $year = now()->year;
                $count = self::whereYear('created_at', $year)->count() + 1;
                $permohonan->no_permohonan = 'DTSEN-' . $year . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'no_permohonan';
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_VALIDASI_DINSOS => 'Validasi Dinsos',
            self::STATUS_DIPROSES_KABIN => 'Diproses Kabin',
            self::STATUS_DISETUJUI_KADIS => 'Disetujui Kadis',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    /**
     * Masyarakat boleh mengubah data & lampiran hanya selama pengajuan masih di tahap awal (Validasi Dinsos),
     * sedang ditolak, atau dikembalikan petugas. Saat di meja Kabid / Kadis atau sudah selesai, terkunci.
     * Dipakai form warga (MasyarakatController) dan pemasangan lampiran otomatis (DokumenMasyarakat).
     */
    public function masyarakatBisaEdit(): bool
    {
        return in_array($this->status, [self::STATUS_VALIDASI_DINSOS, self::STATUS_DITOLAK], true)
            || (bool) ($this->dikembalikan_ke_masyarakat ?? false);
    }

    public function detail(): HasOne
    {
        return $this->hasOne(DtsenDetail::class, 'permohonan_id');
    }

    public function bansos(): HasOne
    {
        return $this->hasOne(DtsenBansos::class, 'permohonan_id');
    }

    public function lampiran(): HasOne
    {
        return $this->hasOne(DtsenLampiran::class, 'permohonan_id');
    }

    public function surat(): HasOne
    {
        return $this->hasOne(DtsenSurat::class, 'permohonan_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DtsenLog::class, 'permohonan_id')->orderBy('tanggal_proses');
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
