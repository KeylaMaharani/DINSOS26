<?php

namespace App\Providers;

use App\Models\DtsenPermohonan;
use App\Models\PbiApbd;
use App\Observers\PermohonanMasyarakatObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Notifikasi otomatis ke masyarakat saat status permohonan berubah
        PbiApbd::observe(PermohonanMasyarakatObserver::class);
        DtsenPermohonan::observe(PermohonanMasyarakatObserver::class);
    }
}
