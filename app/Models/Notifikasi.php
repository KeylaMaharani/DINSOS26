<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasis';

    protected $fillable = ['user_id', 'tipe', 'judul', 'pesan', 'url', 'data', 'dibaca_at'];

    protected $casts = [
        'data' => 'array',
        'dibaca_at' => 'datetime',
    ];
}
