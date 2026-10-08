<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SigapAssessment extends Model
{
    protected $table = 'sigap_assessment';

    protected $fillable = [
        'laporan_id', 'petugas_id', 'nama_petugas', 'nomor_nota_dinas',
        'tanggal_assessment', 'hasil_assessment', 'tembusan', 'ttd_tagana_at',
    ];

    protected $casts = [
        'tanggal_assessment' => 'date',
        'ttd_tagana_at' => 'datetime',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(SigapLaporan::class, 'laporan_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
