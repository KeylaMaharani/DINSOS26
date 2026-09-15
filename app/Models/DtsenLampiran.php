<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtsenLampiran extends Model
{
    protected $table = 'dtsen_lampiran';

    protected $fillable = [
        'permohonan_id',
        'screenshot_dtsen',
        'scan_ktp',
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
