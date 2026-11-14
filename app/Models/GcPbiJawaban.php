<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class GcPbiJawaban extends Model
{
    protected $table = 'pbi_apbd_gc_jawabans';
    protected $guarded = [];

    protected $casts = [
        'bobot' => 'float',
        'skor' => 'float',
    ];

    /** Parameter identitas: sudah tampil di card Data Pemohon, jadi tidak diulang di Isian Masyarakat. */
    public const NAMA_IDENTITAS = [
        'nama', 'no kk', 'alamat sesuai kk', 'no telp', 'no. telp', 'email',
    ];

    /** Sembunyikan parameter identitas (dicocokkan lewat nama, huruf besar/kecil & spasi pinggir diabaikan). */
    public function scopeBukanIdentitas($query)
    {
        return $query->whereNotIn(DB::raw('LOWER(TRIM(nama_parameter))'), self::NAMA_IDENTITAS);
    }

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