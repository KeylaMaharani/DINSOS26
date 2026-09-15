<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtsenLog extends Model
{
    protected $table = 'dtsen_log';

    protected $fillable = [
        'permohonan_id',
        'tanggal_proses',
        'user_id',
        'username',
        'taskname',
        'rolename',
        'catatan',
    ];

    protected $casts = [
        'tanggal_proses' => 'datetime',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(DtsenPermohonan::class, 'permohonan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
