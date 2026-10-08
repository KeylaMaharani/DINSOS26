<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SigapLog extends Model
{
    protected $table = 'sigap_log';

    protected $fillable = ['laporan_id', 'tanggal_proses', 'user_id', 'username', 'taskname', 'rolename', 'catatan'];

    protected $casts = ['tanggal_proses' => 'datetime'];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(SigapLaporan::class, 'laporan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
