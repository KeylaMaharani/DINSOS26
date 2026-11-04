<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'role_id',
        'password',
        'kecamatan',
        'kelurahan',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Pakai `username` sebagai slug di URL (route model binding).
     * Aman karena kolom `username` unique di database.
     */
    public function getRouteKeyName(): string
    {
        return 'username';
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Notifikasi milik user ini (lonceng).
     */
    public function notifikasis(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'user_id');
    }

    /**
     * Riwayat pengajuan PBI APBD milik user ini (sisi masyarakat).
     */
    public function pengajuanPbiApbd(): HasMany
    {
        return $this->hasMany(PbiApbd::class, 'user_id');
    }

    /**
     * Riwayat pengajuan Kartu KKS milik user ini (sisi masyarakat).
     */
    public function pengajuanKartuKks(): HasMany
    {
        return $this->hasMany(KartuKksPermohonan::class, 'user_id');
    }

    /**
     * Riwayat pengajuan DTSEN milik user ini (sisi masyarakat).
     */
    public function pengajuanDtsen(): HasMany
    {
        return $this->hasMany(DtsenPermohonan::class, 'user_id');
    }
}
