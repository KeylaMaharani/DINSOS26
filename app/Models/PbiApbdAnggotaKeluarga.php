<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PbiApbdAnggotaKeluarga extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function pbiApbd()
    {
        return $this->belongsTo(PbiApbd::class);
    }
}
