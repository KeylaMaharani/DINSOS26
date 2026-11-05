<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NgopiPagiMonitoring extends Model
{
    protected $table = 'ngopi_pagi_monitorings';

    protected $guarded = ['id'];

    public function konsultasi(): BelongsTo
    {
        return $this->belongsTo(NgopiPagiKonsultasi::class, 'konsultasi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
