<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KesanPenerima extends Model
{
    protected $table = 'kesan_penerimas';
    protected $fillable = [
        'kesan_kategori_id', 'nama', 'no_kk', 'kecamatan', 'kelurahan', 'prioritas', 'jumlah_jiwa',
        'foto', 'profil', 'kebutuhan', 'estimasi_biaya', 'status', 'aktif',
    ];
    protected $casts = ['aktif' => 'boolean', 'estimasi_biaya' => 'integer'];

    public const KECAMATAN = ['Bogor Selatan', 'Bogor Timur', 'Bogor Utara', 'Bogor Tengah', 'Bogor Barat', 'Tanah Sareal'];

    public const PRIORITAS = [
        'tinggi' => ['label' => 'Prioritas Tinggi', 'dot' => 'bg-red-500', 'text' => 'text-red-600'],
        'sedang' => ['label' => 'Prioritas Sedang', 'dot' => 'bg-amber-500', 'text' => 'text-amber-600'],
        'rendah' => ['label' => 'Prioritas Rendah', 'dot' => 'bg-green-500', 'text' => 'text-green-600'],
    ];

    public const STATUS = [
        'tersedia' => ['label' => 'Tersedia', 'chip' => 'bg-green-100 text-green-800'],
        'dipilih'  => ['label' => 'Sedang diproses', 'chip' => 'bg-amber-100 text-amber-800'],
        'selesai'  => ['label' => 'Sudah dibantu', 'chip' => 'bg-slate-200 text-slate-700'],
    ];

    public const URUT_PRIORITAS = "case prioritas when 'tinggi' then 1 when 'sedang' then 2 else 3 end";

    public function kategori()
    {
        return $this->belongsTo(KesanKategori::class, 'kesan_kategori_id');
    }

    public function pilihans()
    {
        return $this->hasMany(KesanPilihan::class, 'kesan_penerima_id');
    }

    public function scopeTersedia($q)
    {
        return $q->where('aktif', true)
            ->where('status', 'tersedia')
            ->whereHas('kategori', fn ($k) => $k->where('aktif', true));
    }

    public function prioritasCfg(): array
    {
        return self::PRIORITAS[$this->prioritas] ?? self::PRIORITAS['sedang'];
    }

    public function statusCfg(): array
    {
        return self::STATUS[$this->status] ?? self::STATUS['tersedia'];
    }

    public function fotoUrl(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    public function rupiah(): string
    {
        return 'Rp ' . number_format((int) $this->estimasi_biaya, 0, ',', '.');
    }

    public function wilayah(): string
    {
        return trim(($this->kelurahan ? $this->kelurahan . ', ' : '') . 'Kec. ' . $this->kecamatan);
    }
}
