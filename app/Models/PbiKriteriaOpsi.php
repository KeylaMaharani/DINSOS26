<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PbiKriteriaOpsi extends Model
{
    protected $table = 'pbi_kriteria_opsis';
    protected $guarded = [];

    protected $casts = [
        'indeks_terintegrasi' => 'decimal:2',
    ];

    public function parameter()
    {
        return $this->belongsTo(PbiKriteriaParameter::class, 'parameter_id');
    }
}
