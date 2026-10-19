<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GcKriteriaOpsi extends Model
{
    protected $table = 'gc_kriteria_opsis';

    protected $fillable = ['parameter_id', 'label', 'nilai', 'urutan'];

    protected $casts = ['nilai' => 'float'];

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(GcKriteriaParameter::class, 'parameter_id');
    }
}
