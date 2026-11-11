<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GcSasaran;
use App\Models\KartuKksPermohonan;
use App\Models\PbiApbd;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PfmDashboardController extends Controller
{
    private const TAHUN_GC = 2026;

    public function index()
    {
        $role = auth()->user()->role ?? null;
        $boleh = fn (string $m) => $role && $role->hasModule($m);

        // 6 bulan terakhir (urut lama -> baru)
        $bulan = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $labels = $bulan->map(fn ($b) => $b->translatedFormat('M'))->all();

        $pbi = $boleh('pbi-apbd') ? $this->ringkas($this->tabelModel(PbiApbd::class, 'pbi_apbds'), $bulan) : null;
        $dtsen = $boleh('dtsen')
            ? $this->ringkas($this->tabelModel('App\\Models\\DtsenPermohonan', 'dtsen_permohonan'), $bulan)
            : null;
        $kks = $boleh('kartu-kks') ? $this->ringkasKks($bulan) : null;
        $gc = $boleh('ground-check.daftar') ? $this->ringkasGroundCheck() : null;

        // Total semua layanan PFM per bulan (untuk grafik)
        $grafik = [
            'labels' => $labels,
            'datasets' => array_values(array_filter([
                $pbi ? ['label' => 'PBI APBD', 'data' => $pbi['per_bulan'], 'color' => '#0a5ca8'] : null,
                $kks ? ['label' => 'Kartu KKS', 'data' => $kks['per_bulan'], 'color' => '#4a9be0'] : null,
                $dtsen ? ['label' => 'DTSEN', 'data' => $dtsen['per_bulan'], 'color' => '#a0caf9'] : null,
            ])),
        ];

        $kksTerbaru = $kks
            ? KartuKksPermohonan::with('currentRole')->latest('tanggal_insert')->latest('id')->limit(5)->get()
            : collect();

        return view('admin.pfm.beranda', [
            'pbi' => $pbi,
            'dtsen' => $dtsen,
            'kks' => $kks,
            'gc' => $gc,
            'grafik' => $grafik,
            'kksTerbaru' => $kksTerbaru,
            'kksLabels' => KartuKksPermohonan::statusLabels(),
        ]);
    }

    private function tabelModel(string $class, string $fallback): string
    {
        try {
            return class_exists($class) ? (new $class)->getTable() : $fallback;
        } catch (Throwable) {
            return $fallback;
        }
    }

    /** Ringkasan generik: total, bulan ini, per bulan, dan rincian status. */
    private function ringkas(string $tabel, $bulan): array
    {
        $hasil = ['total' => 0, 'bulan_ini' => 0, 'per_bulan' => array_fill(0, 6, 0), 'status' => []];

        try {
            if (! Schema::hasTable($tabel)) {
                return $hasil;
            }

            $kolom = collect(['tanggal_insert', 'created_at'])->first(fn ($k) => Schema::hasColumn($tabel, $k));

            $hasil['total'] = DB::table($tabel)->count();

            if ($kolom) {
                $hasil['per_bulan'] = $bulan->map(fn ($b) => DB::table($tabel)
                    ->whereBetween($kolom, [$b->copy()->startOfMonth(), $b->copy()->endOfMonth()])
                    ->count())->all();
                $hasil['bulan_ini'] = end($hasil['per_bulan']);
            }

            if (Schema::hasColumn($tabel, 'status')) {
                $hasil['status'] = DB::table($tabel)
                    ->select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->orderByDesc('total')
                    ->pluck('total', 'status')
                    ->all();
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $hasil;
    }

    private function ringkasKks($bulan): array
    {
        $status = KartuKksPermohonan::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $perBulan = $bulan->map(fn ($b) => KartuKksPermohonan::whereBetween('tanggal_insert', [
            $b->copy()->startOfMonth()->toDateString(),
            $b->copy()->endOfMonth()->toDateString(),
        ])->count())->all();

        $selesai = (int) ($status[KartuKksPermohonan::STATUS_SELESAI] ?? 0);
        $ditolak = (int) ($status[KartuKksPermohonan::STATUS_DITOLAK] ?? 0);
        $total = (int) $status->sum();

        return [
            'total' => $total,
            'bulan_ini' => end($perBulan),
            'per_bulan' => $perBulan,
            'berjalan' => $total - $selesai - $ditolak,
            'selesai' => $selesai,
            'ditolak' => $ditolak,
            'status' => $status->all(),
            // urutan tahap alur aktif, untuk batang progres
            'tahap' => [
                KartuKksPermohonan::STATUS_DIAJUKAN,
                KartuKksPermohonan::STATUS_DIPROSES_KABIN,
                KartuKksPermohonan::STATUS_DISETUJUI_KADIS,
                KartuKksPermohonan::STATUS_VALIDASI_DINSOS,
            ],
        ];
    }

    private function ringkasGroundCheck(): array
    {
        $total = GcSasaran::count();
        $totalKk = GcSasaran::distinct()->count('no_kk');

        $sudah = GcSasaran::whereHas('verifikasis', fn ($v) => $v
            ->where('tahun', self::TAHUN_GC)->where('status_entry', 'submit'))->count();

        $draft = GcSasaran::whereHas('verifikasis', fn ($v) => $v
            ->where('tahun', self::TAHUN_GC)->where('status_entry', 'draft'))->count();

        return [
            'total' => $total,
            'total_kk' => $totalKk,
            'sudah' => $sudah,
            'draft' => $draft,
            'belum' => max($total - $sudah, 0),
            'progres' => $total > 0 ? round($sudah / $total * 100) : 0,
        ];
    }
}