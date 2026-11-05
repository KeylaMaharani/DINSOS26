<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NgopiPagiPesan extends Model
{
    protected $table = 'ngopi_pagi_pesans';

    protected $guarded = ['id'];

    protected $casts = ['dibaca_at' => 'datetime'];

    public function konsultasi(): BelongsTo
    {
        return $this->belongsTo(NgopiPagiKonsultasi::class, 'konsultasi_id');
    }

    // FIX: relasi ini sebelumnya tidak ada, padahal dipakai with('user') / load('pesans.user').
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Bentuk ringkas untuk JSON (dipakai polling chat di sisi publik & admin). */
    public function ringkas(?int $konselorId = null): array
    {
        $petugas = $this->pengirim === 'petugas';

        return [
            'nama' => $petugas ? ($this->user->name ?? null) : null,
            'peran' => $petugas ? (($konselorId && (int) $this->user_id === $konselorId) ? 'Konselor LK3' : 'Admin') : null,
            'id' => $this->id,
            'pengirim' => $this->pengirim,
            'isi' => $this->isi,
            'waktu' => $this->created_at->format($this->created_at->isToday() ? 'H:i' : 'd/m H:i'),
        ];
    }
}
