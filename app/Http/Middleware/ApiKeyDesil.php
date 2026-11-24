<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use App\Models\ApiClientLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ApiKeyDesil
{
    public function handle(Request $request, Closure $next, string $scope = 'nik')
    {
        $request->headers->set('Accept', 'application/json');
        $request->attributes->set('req_id', (string) Str::uuid());
        $ip = $request->ip();

        if (app()->isProduction() && ! $request->isSecure()) {
            return $this->tolak(403, 'HTTPS wajib.');
        }

        // 1) Hentikan brute-force key per IP
        $gagal = 'desil-gagal:' . $ip;
        if (RateLimiter::tooManyAttempts($gagal, 10)) {
            return $this->tolak(429, 'Terlalu banyak percobaan.', RateLimiter::availableIn($gagal));
        }

        // 2) Autentikasi
        $key = $request->header('X-API-Key') ?: $request->bearerToken();
        $client = $key ? ApiClient::where('key_hash', hash('sha256', $key))->first() : null;

        if (! $client || ! $client->aktif()) {
            RateLimiter::hit($gagal, 900);
            $this->audit('auth_gagal', $request, null, 401);
            return $this->tolak(401, 'API key tidak valid atau tidak dikirim.');
        }

        // 3) Otorisasi: IP allowlist + scope endpoint
        if (! $client->ipBoleh($ip)) {
            $this->audit('ip_ditolak', $request, $client, 403);
            return $this->tolak(403, 'Akses dari IP ini tidak diizinkan.');
        }
        if (! $client->bisa($scope)) {
            $this->audit('scope_ditolak', $request, $client, 403, ['scope' => $scope]);
            return $this->tolak(403, 'Klien tidak berhak mengakses endpoint ini.');
        }

        // 4) Throttle per klien (bukan per IP) + kuota harian
        $menit = "desil-menit:{$client->id}";
        if (RateLimiter::tooManyAttempts($menit, $client->rate_per_menit)) {
            return $this->tolak(429, 'Batas request per menit terlampaui.', RateLimiter::availableIn($menit));
        }
        $harian = "desil-harian:{$client->id}:" . now()->format('Ymd');
        if (RateLimiter::tooManyAttempts($harian, $client->kuota_harian)) {
            return $this->tolak(429, 'Kuota harian habis.', RateLimiter::availableIn($harian));
        }
        RateLimiter::hit($menit, 60);
        RateLimiter::hit($harian, 86400);

        if (! $client->last_used_at || $client->last_used_at->lt(now()->subMinute())) {
            $client->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        $request->attributes->set('api_client', $client);

        return $next($request);
    }

    /** Dijalankan setelah respons terkirim, jadi status akhir (termasuk 404/422) ikut tercatat. */
    public function terminate(Request $request, $response): void
    {
        if ($client = $request->attributes->get('api_client')) {
            $this->audit('akses', $request, $client, $response->getStatusCode());
        }
    }

    private function audit(string $event, Request $r, ?ApiClient $c, int $status, array $extra = []): void
    {
        // NIK/No. KK di path disamarkan, log tidak boleh jadi sumber kebocoran data
        $path = preg_replace_callback('/\d{16}/', fn ($m) => substr($m[0], 0, 6) . '**********', $r->path());

        Log::channel('desil_audit')->info($event, [
            'req_id' => $r->attributes->get('req_id'),
            'klien' => $c?->nama,
            'ip' => $r->ip(),
            'method' => $r->method(),
            'path' => $path,
            'query_keys' => array_keys($r->query()),
            'status' => $status,
        ] + $extra);

        try {
            ApiClientLog::create([
                'api_client_id' => $c?->id,
                'klien' => $c?->nama,
                'event' => $event,
                'ip' => $r->ip(),
                'method' => $r->method(),
                'path' => $path,
                'query_keys' => array_keys($r->query()),
                'status' => $status,
                'req_id' => $r->attributes->get('req_id'),
            ]);
        } catch (\Throwable $e) {
            report($e); // gagal mencatat tidak boleh membuat API ikut error
        }
    }

    private function tolak(int $code, string $pesan, ?int $retry = null)
    {
        $res = response()->json(['success' => false, 'message' => $pesan], $code);
        return $retry ? $res->header('Retry-After', $retry) : $res;
    }
}