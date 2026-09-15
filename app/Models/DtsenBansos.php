<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtsenBansos extends Model
{
    protected $table = 'dtsen_bansos';

    protected $fillable = [
        'permohonan_id',
        'peringkat_kesejahteraan_keluarga',
        'bnpt',
        'pkh',
        'pbi',
        'yatim_piatu',
        'tanggal_cetak',
        'berlaku_sampai',
        'dicetak_oleh',
        'upload_lainnya',
    ];

    protected $casts = [
        'bnpt' => 'boolean',
        'pkh' => 'boolean',
        'pbi' => 'boolean',
        'yatim_piatu' => 'boolean',
        'tanggal_cetak' => 'date',
        'berlaku_sampai' => 'date',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(DtsenPermohonan::class, 'permohonan_id');
    }

    /**
     * True jika kartu/keterangan sudah lewat masa berlaku.
     * Dipakai di Arsip/Monitoring untuk badge "Kedaluwarsa".
     */
    public function isExpired(): bool
    {
        return $this->berlaku_sampai && $this->berlaku_sampai->isPast();
    }

    public function url(string $field): ?string
    {
        return $this->{$field} ? asset('storage/' . $this->{$field}) : null;
    }
}
