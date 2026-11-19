<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KesanPenerima;
use App\Models\KesanPilihan;
use App\Models\NgopiPagiKonsultasi as K;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** Beranda bidang Dayasos: ringkasan Ngopi Pagi (LK3) dan KESAN (Filantropi). */
class DayasosDashboardController extends Controller
{
    private const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;
        $can = fn ($m) => $role && collect((array) $m)->contains(fn ($k) => $role->hasModule($k));

        // 6 bulan terakhir (untuk grafik)
        $bulan = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $awal = $bulan->first();
        $perBulan = fn ($tanggal) => $bulan->map(
            fn ($b) => $tanggal->filter(fn ($t) => Carbon::parse($t)->format('Y-m') === $b->format('Y-m'))->count()
        )->all();

        $grafik = ['labels' => $bulan->map(fn ($b) => self::BULAN[$b->month - 1])->all(), 'datasets' => []];

        // ---------- NGOPI PAGI (dibatasi per user lewat untukUser) ----------
        $ngopi = null;
        $ngopiTerbaru = collect();
        if ($can(['dayasos-ngopi-pagi', 'dayasos-ngopi-konselor'])) {
            $dasar = fn () => K::untukUser($user);

            $perTahap = $dasar()->selectRaw('tahap, count(*) as total')->groupBy('tahap')->pluck('total', 'tahap');
            $total = (int) $perTahap->sum();
            $selesai = (int) ($perTahap['selesai'] ?? 0);

            $perKategori = $dasar()->whereNotNull('kategori')
                ->selectRaw('kategori, count(*) as total')->groupBy('kategori')->pluck('total', 'kategori');

            $perTindak = $dasar()->whereNotNull('jenis_tindak_lanjut')
                ->selectRaw('jenis_tindak_lanjut, count(*) as total')->groupBy('jenis_tindak_lanjut')->pluck('total', 'jenis_tindak_lanjut');

            $ngopi = [
                'total' => $total,
                'bulan_ini' => $dasar()->where('created_at', '>=', now()->startOfMonth())->count(),
                'aktif' => $total - $selesai,
                'selesai' => $selesai,
                'segera' => $dasar()->where('tahap', '!=', 'selesai')->where('butuh_segera', true)->count(),
                'progres' => $total > 0 ? (int) round($selesai / $total * 100) : 0,
                'kategori' => ['A' => (int) ($perKategori['A'] ?? 0), 'B' => (int) ($perKategori['B'] ?? 0), 'C' => (int) ($perKategori['C'] ?? 0)],
                'perTahap' => $perTahap,
                'tahap' => array_keys(array_diff_key(K::TAHAP, ['selesai' => 1])),
                'tindak' => $perTindak->mapWithKeys(fn ($n, $k) => [(K::TINDAK_LANJUT[$k]['label'] ?? $k) => (int) $n]),
            ];

            $ngopiTerbaru = $dasar()->with('konselor')->latest()->limit(6)->get();

            $grafik['datasets'][] = [
                'label' => 'Ngopi Pagi (konsultasi masuk)',
                'data' => $perBulan($dasar()->where('created_at', '>=', $awal)->pluck('created_at')),
                'color' => '#0a5ca8',
            ];
        }

        // ---------- KESAN ----------
        $kesan = null;
        $kesanTerbaru = collect();
        if ($can('kesan')) {
            $hitung = KesanPilihan::where('status', '!=', 'keranjang')
                ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

            $kesan = [
                'dipilih' => (int) ($hitung['dipilih'] ?? 0),
                'penyaluran' => (int) ($hitung['penyaluran'] ?? 0),
                'selesai' => (int) ($hitung['selesai'] ?? 0),
                'ditolak' => (int) ($hitung['ditolak'] ?? 0),
                'batal' => (int) ($hitung['batal'] ?? 0),
                'total' => (int) $hitung->sum(),
                'tersedia' => KesanPenerima::tersedia()->count(),
                'filantropi' => User::whereHas('role', fn ($r) => $r->where('slug', 'filantropi'))->count(),
            ];

            $kesanTerbaru = KesanPilihan::with(['user', 'penerima.kategori'])
                ->where('status', '!=', 'keranjang')->latest('id')->limit(5)->get();

            $grafik['datasets'][] = [
                'label' => 'KESAN (pilihan donatur)',
                'data' => $perBulan(
                    KesanPilihan::where('status', '!=', 'keranjang')->where('created_at', '>=', $awal)->pluck('created_at')
                ),
                'color' => '#f59e0b',
            ];
        }

        return view('admin.dayasos.dashboard', [
            'ngopi' => $ngopi,
            'kesan' => $kesan,
            'grafik' => $grafik,
            'ngopiTerbaru' => $ngopiTerbaru,
            'kesanTerbaru' => $kesanTerbaru,
            'tahapCfg' => K::TAHAP,
        ]);
    }
}