<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtsenSurat extends Model
{
    protected $table = 'dtsen_surat';

    protected $fillable = [
        'permohonan_id',
        'surat_pengantar_dtsen_kelurahan_digital',
        'surat_pengantar_dtsen_kelurahan_digital_at',
        'surat_keterangan_dtsen_digital',
        'surat_keterangan_dtsen_digital_at',
    ];

    protected $casts = [
        'surat_pengantar_dtsen_kelurahan_digital_at' => 'datetime',
        'surat_keterangan_dtsen_digital_at' => 'datetime',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(DtsenPermohonan::class, 'permohonan_id');
    }

    public function url(string $field): ?string
    {
        return $this->{$field} ? asset('storage/' . $this->{$field}) : null;
    }
}
