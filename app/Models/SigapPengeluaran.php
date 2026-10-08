<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SigapPengeluaran extends Model
{
    protected $table = 'sigap_pengeluaran';

    protected $fillable = [
        'laporan_id', 'nomor_surat', 'tanggal', 'pengurus_barang_id', 'nama_pengurus_barang',
        'ttd_pengurus_at', 'kadis_id', 'nama_kadis', 'ttd_kadis_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'ttd_pengurus_at' => 'datetime',
        'ttd_kadis_at' => 'datetime',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(SigapLaporan::class, 'laporan_id');
    }
}
