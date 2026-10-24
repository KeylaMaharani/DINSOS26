<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GcLapanganUsaha extends Model
{
    protected $table = 'gc_lapangan_usahas';

    protected $fillable = ['nama', 'ikon', 'penjelasan', 'contoh_berusaha_sendiri', 'contoh_buruh', 'urutan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function pekerjaans(): HasMany
    {
        return $this->hasMany(GcPekerjaan::class, 'lapangan_usaha_id')->orderBy('urutan')->orderBy('id');
    }

    public function pekerjaansAktif(): HasMany
    {
        return $this->pekerjaans()->where('aktif', true);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true)->orderBy('urutan')->orderBy('id');
    }
}
