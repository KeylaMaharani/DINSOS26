<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PbiApbdGcKoreksi extends Model
{
    protected $table = 'pbi_apbd_gc_koreksis';
    protected $guarded = [];

    public function pbiApbd()
    {
        return $this->belongsTo(PbiApbd::class, 'pbi_apbd_id');
    }
}
