<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NgopiPagiSpesialisasi extends Model
{
    protected $table = 'ngopi_pagi_spesialisasis';

    protected $guarded = ['id'];

    protected $casts = ['aktif' => 'boolean'];

    /** Kelas Tailwind ditulis utuh supaya terbaca Tailwind CDN. */
    public const WARNA = [
        'sky' => ['bg' => 'bg-sky-100', 'fg' => 'text-sky-700'],
        'rose' => ['bg' => 'bg-rose-100', 'fg' => 'text-rose-700'],
        'amber' => ['bg' => 'bg-amber-100', 'fg' => 'text-amber-700'],
        'violet' => ['bg' => 'bg-violet-100', 'fg' => 'text-violet-700'],
        'emerald' => ['bg' => 'bg-emerald-100', 'fg' => 'text-emerald-700'],
        'red' => ['bg' => 'bg-red-100', 'fg' => 'text-red-700'],
        'teal' => ['bg' => 'bg-teal-100', 'fg' => 'text-teal-700'],
        'orange' => ['bg' => 'bg-orange-100', 'fg' => 'text-orange-700'],
    ];

    public const IKON = [
        'info', 'family_restroom', 'child_care', 'psychology', 'elderly', 'accessible', 'shield',
        'volunteer_activism', 'diversity_1', 'health_and_safety', 'school', 'home', 'favorite',
        'medical_services', 'support_agent', 'groups', 'gavel', 'work',
    ];

    public function konselors(): BelongsToMany
    {
        return $this->belongsToMany(NgopiPagiKonselor::class, 'ngopi_pagi_konselor_spesialisasi', 'spesialisasi_id', 'konselor_id');
    }

    public function konsultasis(): HasMany
    {
        return $this->hasMany(NgopiPagiKonsultasi::class, 'spesialisasi_id');
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('ngopi_pagi_spesialisasis.aktif', true);
    }

    public function scopeUrut(Builder $q): Builder
    {
        return $q->orderBy('ngopi_pagi_spesialisasis.urutan')->orderBy('ngopi_pagi_spesialisasis.nama');
    }

    public function warna(): array
    {
        return self::WARNA[$this->warna] ?? self::WARNA['sky'];
    }
}
