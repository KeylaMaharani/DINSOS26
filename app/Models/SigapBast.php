<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SigapBast extends Model
{
    protected $table = 'sigap_bast';

    protected $fillable = [
        'laporan_id', 'nomor_bast', 'tanggal_terima', 'penerima_id', 'nama_penerima',
        'jabatan_penerima', 'instansi_penerima', 'catatan', 'ttd_penerima_at',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
        'ttd_penerima_at' => 'datetime',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(SigapLaporan::class, 'laporan_id');
    }
}
