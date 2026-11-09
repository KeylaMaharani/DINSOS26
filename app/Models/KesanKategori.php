<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KesanKategori extends Model
{
    protected $table = 'kesan_kategoris';
    protected $fillable = ['kode', 'nama', 'singkatan', 'ikon', 'warna', 'urutan', 'aktif'];
    protected $casts = ['aktif' => 'boolean'];

    /** Warna kategori (class Tailwind ditulis utuh supaya terbaca JIT / safelist). */
    public const WARNA = [
        'orange' => ['label' => 'Oranye', 'solid' => 'bg-orange-500', 'soft' => 'bg-orange-100', 'text' => 'text-orange-600'],
        'rose'   => ['label' => 'Merah muda', 'solid' => 'bg-rose-500', 'soft' => 'bg-rose-100', 'text' => 'text-rose-600'],
        'green'  => ['label' => 'Hijau', 'solid' => 'bg-green-600', 'soft' => 'bg-green-100', 'text' => 'text-green-700'],
        'blue'   => ['label' => 'Biru', 'solid' => 'bg-blue-600', 'soft' => 'bg-blue-100', 'text' => 'text-blue-700'],
        'purple' => ['label' => 'Ungu', 'solid' => 'bg-purple-600', 'soft' => 'bg-purple-100', 'text' => 'text-purple-700'],
        'teal'   => ['label' => 'Tosca', 'solid' => 'bg-teal-600', 'soft' => 'bg-teal-100', 'text' => 'text-teal-700'],
        'amber'  => ['label' => 'Kuning', 'solid' => 'bg-amber-500', 'soft' => 'bg-amber-100', 'text' => 'text-amber-700'],
    ];

    public function penerimas()
    {
        return $this->hasMany(KesanPenerima::class, 'kesan_kategori_id');
    }

    public function warnaCfg(): array
    {
        return self::WARNA[$this->warna] ?? self::WARNA['blue'];
    }

    public function label(): string
    {
        return $this->singkatan ?: $this->nama;
    }

    public function scopeAktif($q)
    {
        return $q->where('aktif', true)->orderBy('urutan')->orderBy('nama');
    }
}
