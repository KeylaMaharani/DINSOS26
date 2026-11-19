<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DtsenPermohonan;
use App\Models\GcSasaran;
use App\Models\KartuKksPermohonan;
use App\Models\KesanPilihan;
use App\Models\NgopiPagiKonsultasi as K;
use App\Models\PbiApbd;
use App\Models\SigapBarang;
use App\Models\SigapLaporan;
use App\Models\SigapStokMutasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    private const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    private const GC_TAHUN = 2026;

    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;
        $can = fn ($m) => $role && collect((array) $m)->contains(fn ($k) => $role->hasModule($k));

        $slug = $role ? strtolower(str_replace(['-', ' '], '_', $role->slug ?: Str::slug($role->name, '_'))) : null;
        // Role tingkat wilayah hanya boleh melihat data wilayahnya, jadi angka global PFM tidak ditampilkan.
        $tingkatWilayah = in_array($slug, ['kelurahan', 'lurah'], true);

        $bulan = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $awal = $bulan->first();
        $awalBulanIni = now()->startOfMonth();

        $perBulan = function ($query) use ($bulan, $awal) {
            $tgl = $query->where('created_at', '>=', $awal)->pluck('created_at');

            return $bulan->map(
                fn ($b) => $tgl->filter(fn ($t) => Carbon::parse($t)->format('Y-m') === $b->format('Y-m'))->count()
            )->all();
        };
        $grafik = ['labels' => $bulan->map(fn ($b) => self::BULAN[$b->month - 1])->all(), 'datasets' => []];
        $aktivitas = collect();

        $label = fn ($s) => ucfirst(str_replace('_', ' ', (string) $s));
        $tone = fn ($s) => str_contains((string) $s, 'selesai') ? 'success'
            : (str_contains((string) $s, 'tolak') ? 'error' : 'warning');

        // ---------- PFM ----------
        $pbi = $kks = $dtsen = null;

        if (! $tingkatWilayah && $can('pbi-apbd')) {
            $pbi = [
                'total' => PbiApbd::count(),
                'bulan_ini' => PbiApbd::where('created_at', '>=', $awalBulanIni)->count(),
                'status' => PbiApbd::selectRaw('status, count(*) as total')->groupBy('status')->orderByDesc('total')->pluck('total', 'status'),
            ];
            $grafik['datasets'][] = ['label' => 'PBI APBD', 'data' => $perBulan(PbiApbd::query()), 'color' => '#0a5ca8'];
        }

        if (! $tingkatWilayah && $can('kartu-kks')) {
            $final = [KartuKksPermohonan::STATUS_SELESAI, KartuKksPermohonan::STATUS_DITOLAK];
            $kks = [
                'total' => KartuKksPermohonan::count(),
                'bulan_ini' => KartuKksPermohonan::where('created_at', '>=', $awalBulanIni)->count(),
                'berjalan' => KartuKksPermohonan::whereNotIn('status', $final)->count(),
                'status' => KartuKksPermohonan::selectRaw('status, count(*) as total')->groupBy('status')->orderByDesc('total')->pluck('total', 'status'),
            ];
            $grafik['datasets'][] = ['label' => 'Kartu KKS', 'data' => $perBulan(KartuKksPermohonan::query()), 'color' => '#b98900'];

            if ($can('kartu-kks.ajuan')) {
                foreach (KartuKksPermohonan::latest()->limit(5)->get() as $r) {
                    $aktivitas->push([
                        'layanan' => 'Kartu KKS', 'id' => $r->no_permohonan, 'nama' => $r->nama_pemohon,
                        'tgl' => $r->created_at, 'status' => $label($r->status), 'tone' => $tone($r->status),
                        'url' => route('kartu-kks.show', $r),
                    ]);
                }
            }
        }

        if (! $tingkatWilayah && $can('dtsen')) {
            $final = [DtsenPermohonan::STATUS_SELESAI, DtsenPermohonan::STATUS_DITOLAK];
            $dtsen = [
                'total' => DtsenPermohonan::count(),
                'bulan_ini' => DtsenPermohonan::where('created_at', '>=', $awalBulanIni)->count(),
                'berjalan' => DtsenPermohonan::whereNotIn('status', $final)->count(),
                'status' => DtsenPermohonan::selectRaw('status, count(*) as total')->groupBy('status')->orderByDesc('total')->pluck('total', 'status'),
            ];
            $grafik['datasets'][] = ['label' => 'DTSEN', 'data' => $perBulan(DtsenPermohonan::query()), 'color' => '#2e7d32'];
        }

        // ---------- GROUND CHECK ----------
        $gc = null;
        if ($can('ground-check')) {
            $totalKk = GcSasaran::distinct()->count('no_kk');
            $belum = GcSasaran::whereDoesntHave('verifikasis', fn ($v) => $v->where('tahun', self::GC_TAHUN)->where('status_entry', 'submit'))
                ->distinct()->count('no_kk');
            $gc = [
                'total' => $totalKk, 'belum' => $belum, 'sudah' => $totalKk - $belum,
                'progres' => $totalKk > 0 ? (int) round(($totalKk - $belum) / $totalKk * 100) : 0,
            ];
        }

        // ---------- NGOPI PAGI (dibatasi per user lewat untukUser) ----------
        $ngopi = null;
        if ($can(['dayasos-ngopi-pagi', 'dayasos-ngopi-konselor'])) {
            $dasar = fn () => K::untukUser($user);
            $total = $dasar()->count();
            $selesai = $dasar()->where('tahap', 'selesai')->count();
            $ngopi = [
                'total' => $total, 'aktif' => $total - $selesai, 'selesai' => $selesai,
                'segera' => $dasar()->where('tahap', '!=', 'selesai')->where('butuh_segera', true)->count(),
            ];
            $grafik['datasets'][] = ['label' => 'Ngopi Pagi', 'data' => $perBulan($dasar()), 'color' => '#136299'];

            foreach ($dasar()->latest()->limit(5)->get() as $r) {
                $aktivitas->push([
                    'layanan' => 'Ngopi Pagi', 'id' => $r->no_konsultasi, 'nama' => $r->nama,
                    'tgl' => $r->created_at, 'status' => K::TAHAP[$r->tahap]['label'] ?? $label($r->tahap),
                    'tone' => $r->tahap === 'selesai' ? 'success' : 'warning', 'url' => $r->urlAdmin(),
                ]);
            }
        }

        // ---------- SIGAP BENCANA ----------
        $sigap = $stok = null;
        if ($can('sigap-bencana')) {
            $final = SigapLaporan::statusFinal();
            $totalL = SigapLaporan::count();
            $sigap = [
                'total' => $totalL,
                'aktif' => SigapLaporan::whereNotIn('status', $final)->count(),
                'selesai' => SigapLaporan::where('status', SigapLaporan::STATUS_SELESAI)->count(),
            ];
            $grafik['datasets'][] = ['label' => 'Sigap Bencana', 'data' => $perBulan(SigapLaporan::query()), 'color' => '#ba1a1a'];

            $labels = SigapLaporan::statusLabels();
            foreach (SigapLaporan::latest()->limit(5)->get() as $r) {
                $aktivitas->push([
                    'layanan' => 'Sigap Bencana', 'id' => $r->no_tiket, 'nama' => $r->jenis_bencana . ' · ' . $r->kelurahan,
                    'tgl' => $r->created_at, 'status' => $labels[$r->status] ?? $label($r->status),
                    'tone' => $r->status === SigapLaporan::STATUS_SELESAI ? 'success' : ($r->status === SigapLaporan::STATUS_DITOLAK ? 'error' : 'warning'),
                    'url' => route('sigap.show', $r),
                ]);
            }

            if ($can('sigap-bencana.stok')) {
                $masuk = (int) SigapStokMutasi::where('jenis', 'masuk')->sum('jumlah');
                $keluar = (int) SigapStokMutasi::where('jenis', 'keluar')->sum('jumlah');
                $stok = [
                    'masuk' => $masuk, 'keluar' => $keluar,
                    'saldo' => (int) SigapBarang::aktif()->sum('stok'),
                    'pct' => $masuk > 0 ? min(100, (int) round($keluar / $masuk * 100)) : 0,
                ];
            }
        }

        // ---------- KESAN ----------
        $kesan = null;
        if ($can('kesan.pilihan')) {
            $kesan = [
                'menunggu' => KesanPilihan::where('status', 'dipilih')->count(),
                'penyaluran' => KesanPilihan::where('status', 'penyaluran')->count(),
                'selesai' => KesanPilihan::where('status', 'selesai')->count(),
            ];
        }

        return view('admin.dashboard', [
            'pbi' => $pbi, 'kks' => $kks, 'dtsen' => $dtsen, 'gc' => $gc,
            'ngopi' => $ngopi, 'sigap' => $sigap, 'stok' => $stok, 'kesan' => $kesan,
            'grafik' => $grafik,
            'aktivitas' => $aktivitas->sortByDesc('tgl')->take(8)->values(),
            'labelStatus' => $label,
        ]);
    }
}