<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuKksDetail extends Model
{
    protected $table = 'kartu_kks_detail';

    protected $fillable = [
        'permohonan_id',
        'nik',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'status_perkawinan',
        'no_pkh',
        'no_kk',
        'no_kartu_kks',
        'no_rekening',
        'pekerjaan',
        'alamat',
        'masalah_kartu',
        'nomor_kehilangan_polisi',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(KartuKksPermohonan::class, 'permohonan_id');
    }
}
