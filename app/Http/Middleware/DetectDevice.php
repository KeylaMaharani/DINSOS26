<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deteksi otomatis: HP -> tampilan mobile, laptop/tablet -> tampilan desktop.
 * Di HP, folder resources/views/mobile dicari lebih dulu.
 */
class DetectDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $isMobile = $this->isMobile($request);

        $request->attributes->set('is_mobile', $isMobile);
        View::share('isMobile', $isMobile);

        if ($isMobile) {
            app('view.finder')->prependLocation(resource_path('views/mobile'));
        }

        return $next($request);
    }

    private function isMobile(Request $request): bool
    {
        $ua = $request->userAgent() ?? '';

        // Tablet dianggap desktop
        if (preg_match('/iPad|Tablet|PlayBook|Silk/i', $ua)) {
            return false;
        }

        return (bool) preg_match('/Android.+Mobile|iPhone|iPod|Windows Phone|IEMobile|Opera Mini|BlackBerry|webOS/i', $ua);
    }
}
