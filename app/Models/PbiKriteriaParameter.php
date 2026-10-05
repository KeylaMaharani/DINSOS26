<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PbiKriteriaParameter extends Model
{
    protected $table = 'pbi_kriteria_parameters';
    protected $guarded = [];

    protected $casts = [
        'bobot' => 'decimal:2',
        'indeks' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function opsis(): HasMany
    {
        return $this->hasMany(PbiKriteriaOpsi::class, 'parameter_id')->orderBy('urutan')->orderBy('id');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true)->orderBy('urutan')->orderBy('id');
    }
}
