<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PbiApbnLog extends Model
{
    protected $guarded = [];

    public function pbiApbn()
    {
        return $this->belongsTo(PbiApbn::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
