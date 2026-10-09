<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Halaman sementara untuk menu yang baru berupa kerangka (Dayasos, Dashboard & Laporan Perlinsos).
 * Judul & label dikirim lewat ->defaults() di routes/web.php.
 */
class MenuPlaceholderController extends Controller
{
    public function show(string $judul, string $label)
    {
        return view('admin.placeholder.index', [
            'moduleTitle' => $judul,
            'tabLabel' => $label,
        ]);
    }
}
