<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuKksLampiran extends Model
{
    protected $table = 'kartu_kks_lampiran';

    protected $fillable = [
        'permohonan_id',
        'scan_ktp',
        'scan_kartu_keluarga',
        'surat_kehilangan_polisi',
        'scan_kartu_kks',
        'screenshot_dtsen',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(KartuKksPermohonan::class, 'permohonan_id');
    }

    // Helper untuk view: kembalikan URL publik jika file ada
    public function url(string $field): ?string
    {
        return $this->{$field} ? asset('storage/' . $this->{$field}) : null;
    }
}
