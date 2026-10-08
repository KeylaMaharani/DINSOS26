<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SigapStokMutasi extends Model
{
    protected $table = 'sigap_stok_mutasi';

    protected $fillable = ['barang_id', 'jenis', 'jumlah', 'saldo_setelah', 'laporan_id', 'keterangan', 'user_id'];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(SigapBarang::class, 'barang_id');
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(SigapLaporan::class, 'laporan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
