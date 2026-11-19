<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SigapBarang;
use App\Models\SigapLaporan;
use App\Models\SigapStokMutasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Beranda bidang Perlinsos: ringkasan Sigap Bencana (laporan, alur tahap, stok barang). */
class PerlinsosDashboardController extends Controller
{
    private const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;
        $can = fn ($m) => $role && collect((array) $m)->contains(fn ($k) => $role->hasModule($k));

        $slug = $role ? strtolower(str_replace(['-', ' '], '_', $role->slug ?: Str::slug($role->name, '_'))) : null;
        $isSuper = in_array($slug, ['super_admin', 'superadmin'], true);

        $labels = SigapLaporan::statusLabels();
        $final = SigapLaporan::statusFinal();
        $tahapAktif = array_values(array_diff(array_keys($labels), $final));

        // 6 bulan terakhir (untuk grafik)
        $bulan = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $awal = $bulan->first();
        $perBulan = fn ($tanggal) => $bulan->map(
            fn ($b) => $tanggal->filter(fn ($t) => Carbon::parse($t)->format('Y-m') === $b->format('Y-m'))->count()
        )->all();

        $grafik = ['labels' => $bulan->map(fn ($b) => self::BULAN[$b->month - 1])->all(), 'datasets' => []];

        // ---------- LAPORAN BENCANA ----------
        $laporan = null;
        $terbaru = collect();
        if ($can(['sigap-bencana', 'sigap-bencana.laporan'])) {
            $perStatus = SigapLaporan::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
            $total = (int) $perStatus->sum();
            $selesai = (int) ($perStatus[SigapLaporan::STATUS_SELESAI] ?? 0);
            $ditolak = (int) ($perStatus[SigapLaporan::STATUS_DITOLAK] ?? 0);

            // "Giliran saya": tiket yang sedang menunggu role ini (Super Admin = semua tiket aktif)
            $giliran = SigapLaporan::whereNotIn('status', $final)
                ->when(! $isSuper, fn ($q) => $q->whereHas('currentRole', fn ($r) => $r->where('slug', $slug)))
                ->count();

            $laporan = [
                'total' => $total,
                'bulan_ini' => SigapLaporan::where('created_at', '>=', now()->startOfMonth())->count(),
                'aktif' => $total - $selesai - $ditolak,
                'selesai' => $selesai,
                'ditolak' => $ditolak,
                'giliran' => $giliran,
                'korban' => (int) SigapLaporan::sum('jumlah_korban'),
                'progres' => $total > 0 ? (int) round($selesai / $total * 100) : 0,
                'perStatus' => $perStatus,
                'tahap' => $tahapAktif,
                'perJenis' => SigapLaporan::selectRaw('jenis_bencana, count(*) as total')
                    ->groupBy('jenis_bencana')->orderByDesc('total')->pluck('total', 'jenis_bencana'),
            ];

            $terbaru = SigapLaporan::with('currentRole')->latest()->limit(6)->get();

            $grafik['datasets'][] = [
                'label' => 'Laporan masuk',
                'data' => $perBulan(SigapLaporan::where('created_at', '>=', $awal)->pluck('created_at')),
                'color' => '#0a5ca8',
            ];
            $grafik['datasets'][] = [
                'label' => 'Laporan selesai',
                'data' => $perBulan(SigapLaporan::whereNotNull('ditutup_at')->where('ditutup_at', '>=', $awal)->pluck('ditutup_at')),
                'color' => '#2e7d32',
            ];
        }

        // ---------- STOK BARANG ----------
        $stok = null;
        $menipis = collect();
        $mutasi = collect();
        if ($can(['sigap-bencana', 'sigap-bencana.stok'])) {
            $menipisQ = fn () => SigapBarang::aktif()->where('stok_minimum', '>', 0)->whereColumn('stok', '<=', 'stok_minimum');

            $stok = [
                'jenis' => SigapBarang::aktif()->count(),
                'menipis' => $menipisQ()->count(),
                'habis' => SigapBarang::aktif()->where('stok', 0)->count(),
            ];
            $menipis = $menipisQ()->orderBy('stok')->limit(5)->get();
            $mutasi = SigapStokMutasi::with(['barang', 'user'])->latest()->limit(5)->get();
        }

        return view('admin.perlinsos.dashboard', [
            'laporan' => $laporan,
            'stok' => $stok,
            'grafik' => $grafik,
            'terbaru' => $terbaru,
            'menipis' => $menipis,
            'mutasi' => $mutasi,
            'statusLabels' => $labels,
            'bolehMembuat' => $isSuper || $slug === 'tagana',
        ]);
    }
}