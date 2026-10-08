<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SigapPermohonanBarang extends Model
{
    protected $table = 'sigap_permohonan_barang';

    protected $fillable = [
        'laporan_id', 'nomor_surat', 'tanggal', 'verifikator_id',
        'nama_verifikator', 'hasil_verifikasi', 'ttd_perlinsos_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'ttd_perlinsos_at' => 'datetime',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(SigapLaporan::class, 'laporan_id');
    }
}
