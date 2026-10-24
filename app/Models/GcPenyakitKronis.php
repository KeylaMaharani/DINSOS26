<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GcPenyakitKronis extends Model
{
    protected $table = 'gc_penyakit_kronis';

    protected $fillable = ['nama', 'urutan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true)->orderBy('urutan')->orderBy('id');
    }
}
