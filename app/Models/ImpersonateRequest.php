<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpersonateRequest extends Model
{
    public const MAKS_PERCOBAAN = 5;
    public const MENIT_MENUNGGU_IZIN = 10;
    public const MENIT_OTP = 5;

    protected $fillable = [
        'admin_id', 'target_user_id', 'status', 'otp', 'percobaan',
        'expired_at', 'disetujui_at', 'digunakan_at', 'ip',
    ];

    protected function casts(): array
    {
        return [
            'otp' => 'encrypted',
            'expired_at' => 'datetime',
            'disetujui_at' => 'datetime',
            'digunakan_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /** Status yang sudah memperhitungkan kedaluwarsa. */
    public function statusEfektif(): string
    {
        if (in_array($this->status, ['pending', 'approved'], true) && $this->expired_at->isPast()) {
            return 'expired';
        }

        return $this->status;
    }
}
