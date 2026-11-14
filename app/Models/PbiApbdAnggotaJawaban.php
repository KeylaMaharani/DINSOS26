<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Taruh di app/Models/PbiApbdAnggotaJawaban.php */
class PbiApbdAnggotaJawaban extends Model
{
    protected $table = 'pbi_apbd_anggota_jawabans';
    protected $guarded = [];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(PbiApbdAnggotaKeluarga::class, 'anggota_id');
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(GcKriteriaParameter::class, 'parameter_id');
    }
}