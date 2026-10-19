<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GcSasaran extends Model
{
    protected $table = 'gc_sasarans';
    protected $guarded = [];

    protected $casts = ['tanggal_lahir' => 'date'];

    public function verifikasis(): HasMany
    {
        return $this->hasMany(GcVerifikasi::class, 'sasaran_id');
    }
}
