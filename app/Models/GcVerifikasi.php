<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GcVerifikasi extends Model
{
    protected $table = 'gc_verifikasis';
    protected $guarded = [];

    protected $casts = [
        'pernyataan' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public const STATUS_TEMUAN = [
        'ditemukan' => 'Ditemukan',
        'pindah_tidak_ditemukan' => 'Pindah/Tidak Ditemukan',
        'meninggal' => 'Meninggal',
    ];

    public function sasaran(): BelongsTo
    {
        return $this->belongsTo(GcSasaran::class, 'sasaran_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jawabans(): HasMany
    {
        return $this->hasMany(GcJawaban::class, 'verifikasi_id');
    }
}
