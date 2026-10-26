<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GcPbiJawaban extends Model
{
    protected $table = 'pbi_apbd_gc_jawabans';
    protected $guarded = [];

    protected $casts = [
        'bobot' => 'float',
        'skor' => 'float',
    ];

    public function pbiApbd(): BelongsTo
    {
        return $this->belongsTo(PbiApbd::class, 'pbi_apbd_id');
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(GcKriteriaParameter::class, 'parameter_id');
    }

    /** Nama sama seperti kolom di tabel jawaban lama, supaya view kelurahan tidak perlu diubah banyak. */
    public function getParameterNamaAttribute(): string
    {
        return (string) $this->nama_parameter;
    }

    /** Teks jawaban siap tampil. */
    public function getJawabanLabelAttribute(): string
    {
        if ($this->jawaban === null || $this->jawaban === '') {
            return '-';
        }

        if ($this->tipe_input === 'foto') {
            return 'Foto terunggah';
        }

        if ($this->tipe_input === 'pilihan_ganda') {
            $arr = json_decode($this->jawaban, true);

            return is_array($arr) ? implode(', ', $arr) : (string) $this->jawaban;
        }

        return (string) $this->jawaban;
    }
}
