<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GcPekerjaan extends Model
{
    protected $table = 'gc_pekerjaans';

    protected $fillable = ['lapangan_usaha_id', 'nama', 'urutan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function lapanganUsaha(): BelongsTo
    {
        return $this->belongsTo(GcLapanganUsaha::class, 'lapangan_usaha_id');
    }
}
