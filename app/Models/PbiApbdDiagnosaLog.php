<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PbiApbdDiagnosaLog extends Model
{
    protected $table = 'pbi_apbd_diagnosa_logs';

    protected $guarded = [];

    public function pbiApbd(): BelongsTo
    {
        return $this->belongsTo(PbiApbd::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
