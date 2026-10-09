<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * REHABSOS -- Dashboard khusus modul + kerangka submenu (fitur menyusul per tahap).
 */
class RehabsosController extends Controller
{
    public function dashboard()
    {
        $role = Auth::user()->role;
        $groups = collect(config('rehabsos.groups'))
            ->filter(fn ($g) => $role && $role->hasModule($g['module']));

        $stat = [];

        if ($groups->has('ppks')) {
            $stat['ppks'] = [
                ['label' => 'Ajuan Masuk (belum divalidasi)', 'value' => DB::table('rehabsos_ppks')->where('status', 'ajuan_masuk')->count(), 'icon' => 'inbox'],
                ['label' => 'Menunggu Approval Rujukan', 'value' => DB::table('rehabsos_ppks')->where('status', 'menunggu_approval')->count(), 'icon' => 'rule'],
                ['label' => 'Dalam Penanganan', 'value' => DB::table('rehabsos_ppks')->whereIn('status', ['terverifikasi', 'didisposisi', 'diasesmen', 'disetujui', 'dirujuk'])->count(), 'icon' => 'sync'],
                ['label' => 'Sudah Dilaporkan ke Kadis', 'value' => DB::table('rehabsos_ppks')->where('status', 'dilaporkan')->count(), 'icon' => 'task_alt'],
            ];
        }

        if ($groups->has('rumah-singgah')) {
            $stat['rumah-singgah'] = [
                ['label' => 'Klien Dalam Layanan', 'value' => DB::table('rehabsos_rs_klien')->where('status', 'dalam_layanan')->count(), 'icon' => 'bed'],
                ['label' => 'Mendekati Batas 7 Hari', 'value' => DB::table('rehabsos_rs_klien')->where('status', 'dalam_layanan')->where('batas_layanan', '<=', now()->addDays(2)->toDateString())->count(), 'icon' => 'timer'],
                ['label' => 'Dirujuk Bulan Ini', 'value' => DB::table('rehabsos_rs_klien')->where('status', 'dirujuk')->whereMonth('updated_at', now()->month)->count(), 'icon' => 'swap_horiz'],
                ['label' => 'Laporan Menunggu Pengesahan', 'value' => DB::table('rehabsos_rs_klien')->whereIn('status', ['laporan_disusun', 'diperiksa'])->count(), 'icon' => 'description'],
            ];
        }

        if ($groups->has('permohonan-bantuan')) {
            $stat['permohonan-bantuan'] = [
                ['label' => 'Diajukan', 'value' => DB::table('rehabsos_bantuan')->where('status', 'diajukan')->count(), 'icon' => 'send'],
                ['label' => 'Diverifikasi', 'value' => DB::table('rehabsos_bantuan')->where('status', 'diverifikasi')->count(), 'icon' => 'fact_check'],
                ['label' => 'Disetujui', 'value' => DB::table('rehabsos_bantuan')->where('status', 'disetujui')->count(), 'icon' => 'thumb_up'],
                ['label' => 'Sudah Diserahkan', 'value' => DB::table('rehabsos_bantuan')->where('status', 'diserahkan')->count(), 'icon' => 'volunteer_activism'],
            ];
        }

        return view('admin.rehabsos.dashboard', compact('groups', 'stat'));
    }

    /** Kerangka submenu: sementara memakai view placeholder yang sudah ada. */
    public function tab(string $group, string $item)
    {
        $g = config("rehabsos.groups.{$group}");
        abort_unless($g && isset($g['items'][$item]), 404);

        return view('admin.placeholder.index', [
            'moduleTitle' => 'Rehabsos — ' . $g['label'],
            'tabLabel' => $g['items'][$item],
        ]);
    }
}
