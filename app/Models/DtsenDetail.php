<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtsenDetail extends Model
{
    protected $table = 'dtsen_detail';

    protected $fillable = [
        'permohonan_id',
        'nik',
        'nama',
        'no_kk',
        'alamat',
        'tanggal_lahir',
        'alasan_keputusan_cetak',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(DtsenPermohonan::class, 'permohonan_id');
    }
}
