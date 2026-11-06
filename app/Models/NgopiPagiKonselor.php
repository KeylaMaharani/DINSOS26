<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class NgopiPagiKonselor extends Model
{
    protected $table = 'ngopi_pagi_konselors';

    protected $guarded = ['id'];

    protected $casts = ['aktif' => 'boolean'];

    public const STATUS = [
        'tersedia' => ['label' => 'Tersedia', 'chip' => 'bg-emerald-100 text-emerald-800', 'dot' => 'bg-emerald-500'],
        'sibuk' => ['label' => 'Sedang melayani', 'chip' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-500'],
        'offline' => ['label' => 'Offline', 'chip' => 'bg-slate-100 text-slate-600', 'dot' => 'bg-slate-400'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function spesialisasis(): BelongsToMany
    {
        return $this->belongsToMany(NgopiPagiSpesialisasi::class, 'ngopi_pagi_konselor_spesialisasi', 'konselor_id', 'spesialisasi_id')
            ->orderBy('ngopi_pagi_spesialisasis.urutan');
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('ngopi_pagi_konselors.aktif', true);
    }

    /** Tersedia dulu, lalu sibuk, lalu offline; sisanya menurut urutan & nama. */
    public function scopeUrut(Builder $q): Builder
    {
        return $q->orderByRaw("CASE ngopi_pagi_konselors.status WHEN 'tersedia' THEN 0 WHEN 'sibuk' THEN 1 ELSE 2 END")
            ->orderBy('ngopi_pagi_konselors.urutan')
            ->orderBy('ngopi_pagi_konselors.nama_tampilan');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? Storage::disk('public')->url($this->foto) : null;
    }

    public function statusCfg(): array
    {
        return self::STATUS[$this->status] ?? self::STATUS['offline'];
    }

    public function inisial(): string
    {
        return strtoupper(mb_substr(preg_replace('/^(dr|drs|dra|prof)\.?\s+/i', '', $this->nama_tampilan), 0, 1));
    }
}
