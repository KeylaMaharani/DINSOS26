<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\IpUtils;

class ApiClient extends Model
{
    protected $guarded = [];
    protected $hidden = ['key_hash'];
    protected $casts = [
        'scopes' => 'array', 'kecamatan' => 'array', 'allowed_ips' => 'array',
        'expires_at' => 'datetime', 'revoked_at' => 'datetime', 'last_used_at' => 'datetime',
    ];

    public function aktif(): bool
    {
        return $this->revoked_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function bisa(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? [], true);
    }

    public function ipBoleh(?string $ip): bool
    {
        return empty($this->allowed_ips) || ($ip && IpUtils::checkIp($ip, $this->allowed_ips));
    }

    public function wilayahBoleh(?string $kecamatan): bool
    {
        return empty($this->kecamatan) || in_array($kecamatan, $this->kecamatan, true);
    }
}