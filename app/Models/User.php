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
     * Pakai `username` sebagai slug di URL (route model binding) untuk
     * halaman "Kelola User & Role" di admin, bukan `id` numerik.
     *
     * PENTING: ini AMAN dipakai HANYA jika kolom `username` unique di
     * database. Kalau migration users Anda belum punya unique constraint
     * di kolom ini, tambahkan dulu (lihat catatan di chat) sebelum pasang
     * method ini — supaya tidak ada 2 user beda ke-resolve ke username sama.
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
     * Riwayat pengajuan PBI APBN milik user ini (sisi masyarakat).
     */
    public function pengajuanPbiApbn(): HasMany
    {
        return $this->hasMany(PbiApbn::class, 'user_id');
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
