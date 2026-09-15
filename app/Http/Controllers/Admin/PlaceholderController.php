<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Controller sementara untuk menu-menu yang belum dibangun fiturnya
 * (Asesmen SPMB, Kedaruratan Medis, Kartu KKS, DTSEN, Dokumen).
 *
 * Begitu fitur salah satu modul ini mau digarap, tinggal dibuatkan
 * controller sendiri (contoh: PbiApbnController) lalu route-nya
 * dialihkan dari placeholder ke controller barunya.
 */
class PlaceholderController extends Controller
{
    protected array $modules = [
        'asesmen-spmb' => 'Asesmen SPMB',
        'kedaruratan-medis' => 'Kedaruratan Medis',
        'kartu-kks' => 'Kartu KKS',
        'dtsen' => 'DTSEN',
    ];

    public function tab(string $module, string $tab)
    {
        $title = $this->modules[$module] ?? ucfirst(str_replace('-', ' ', $module));
        $tabLabel = match ($tab) {
            'ajuan' => 'Ajuan',
            'arsip' => 'Arsip',
            'monitoring' => 'Monitoring',
            'log' => 'Log',
            default => ucfirst($tab),
        };

        return view('admin.placeholder.index', [
            'moduleTitle' => $title,
            'tabLabel' => $tabLabel,
        ]);
    }

    public function dokumen()
    {
        return view('admin.placeholder.index', [
            'moduleTitle' => 'Dokumen',
            'tabLabel' => 'Template Dokumen',
        ]);
    }
}
