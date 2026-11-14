<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PbiApbdAnggotaKeluarga extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function pbiApbd()
    {
        return $this->belongsTo(PbiApbd::class);
    }

    /** Jawaban masyarakat untuk anggota ini (tab A-D, diisi lewat halaman "Isi data" anggota). */
    public function anggotaJawabans()
    {
        return $this->hasMany(PbiApbdAnggotaJawaban::class, 'anggota_id');
    }
}