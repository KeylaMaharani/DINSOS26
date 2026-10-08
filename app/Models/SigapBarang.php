<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SigapBarang extends Model
{
    protected $table = 'sigap_barang';

    protected $fillable = ['kode', 'nama', 'satuan', 'stok', 'stok_minimum', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(SigapStokMutasi::class, 'barang_id')->latest();
    }

    public function stokMenipis(): bool
    {
        return $this->stok_minimum > 0 && $this->stok <= $this->stok_minimum;
    }
}
