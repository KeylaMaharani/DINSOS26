<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PbiApbnAnggotaKeluarga extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function pbiApbn()
    {
        return $this->belongsTo(PbiApbn::class);
    }
}
