<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GcJawaban extends Model
{
    protected $table = 'gc_jawabans';
    protected $guarded = [];

    protected $casts = ['bobot' => 'float', 'skor' => 'float'];

    public function verifikasi(): BelongsTo
    {
        return $this->belongsTo(GcVerifikasi::class, 'verifikasi_id');
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(GcKriteriaParameter::class, 'parameter_id');
    }
}
