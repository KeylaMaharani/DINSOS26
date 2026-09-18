<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PbiApbnDiagnosaLog extends Model
{
    protected $table = 'pbi_apbn_diagnosa_logs';

    protected $guarded = [];

    public function pbiApbn(): BelongsTo
    {
        return $this->belongsTo(PbiApbn::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
