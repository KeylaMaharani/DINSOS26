<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PbiApbdLog extends Model
{
    protected $guarded = [];

    public function pbiApbd()
    {
        return $this->belongsTo(PbiApbd::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
