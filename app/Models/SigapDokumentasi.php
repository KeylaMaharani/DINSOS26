<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SigapDokumentasi extends Model
{
    protected $table = 'sigap_dokumentasi';

    protected $fillable = ['laporan_id', 'path', 'keterangan', 'uploaded_by'];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(SigapLaporan::class, 'laporan_id');
    }
}
