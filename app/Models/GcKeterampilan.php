<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GcKeterampilan extends Model
{
    protected $table = 'gc_keterampilans';

    protected $fillable = ['nama', 'urutan', 'eksklusif', 'aktif'];

    protected $casts = ['aktif' => 'boolean', 'eksklusif' => 'boolean'];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true)->orderBy('urutan')->orderBy('id');
    }
}   
