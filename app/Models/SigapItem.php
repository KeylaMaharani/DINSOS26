<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SigapItem extends Model
{
    protected $table = 'sigap_item';

    protected $fillable = [
        'laporan_id', 'barang_id', 'nama_barang', 'satuan',
        'jumlah_dibutuhkan', 'jumlah_disetujui', 'jumlah_keluar',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(SigapLaporan::class, 'laporan_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(SigapBarang::class, 'barang_id');
    }
}
