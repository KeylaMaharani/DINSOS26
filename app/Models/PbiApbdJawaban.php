<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PbiApbdJawaban extends Model
{
    protected $table = 'pbi_apbd_jawabans';
    protected $guarded = [];

    protected $casts = [
        'indeks' => 'decimal:2',
        'bobot' => 'decimal:2',
        'indeks_terintegrasi' => 'decimal:2',
        'skor' => 'decimal:6',
    ];

    public function pbiApbd()
    {
        return $this->belongsTo(PbiApbd::class);
    }

    public function parameter()
    {
        return $this->belongsTo(PbiKriteriaParameter::class, 'parameter_id');
    }

    public function opsi()
    {
        return $this->belongsTo(PbiKriteriaOpsi::class, 'opsi_id');
    }
}
