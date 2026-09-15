<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuKksSurat extends Model
{
    protected $table = 'kartu_kks_surat';

    protected $fillable = [
        'permohonan_id',
        'surat_pengantar_kelurahan',
        'surat_pengantar_kelurahan_at',
        'surat_keterangan_dinsos',
        'surat_keterangan_dinsos_at',
    ];

    protected $casts = [
        'surat_pengantar_kelurahan_at' => 'datetime',
        'surat_keterangan_dinsos_at' => 'datetime',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(KartuKksPermohonan::class, 'permohonan_id');
    }

    public function url(string $field): ?string
    {
        return $this->{$field} ? asset('storage/' . $this->{$field}) : null;
    }
}
