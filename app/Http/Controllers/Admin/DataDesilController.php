<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataPendudukAsli as P;
use App\Models\GcSasaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Entri Data > Data Desil Kependudukan
 * Sumber: tabel data_penduduk_asli. Akses: Super Admin + role yang dicentang 'data-desil'.
 */
class DataDesilController extends Controller
{
    private const TAHUN_GC = 2026;

    public function index(Request $request)
    {
        if ($request->boolean('refresh')) {
            Cache::forget('data_desil.stats');
            Cache::forget('data_desil.wilayah');
        }

        $perPage = in_array((int) $request->input('per_page'), [15, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 15;

        $rows = $this->query($request)->orderBy('id')->paginate($perPage)->withQueryString();

        // Status Ground Check per KK untuk baris yang tampil saja (murah: <= 100 KK)
        $kkHalaman = $rows->pluck('nomor_kartu_keluarga')->filter()->unique()->values();
        $gcSelesai = $this->gcQuery()->whereIn('no_kk', $kkHalaman)->pluck('no_kk')->flip();

        $detail = $rows->getCollection()->mapWithKeys(fn ($r) => [
            $r->id => collect(P::KOLOM)->map(fn ($l, $c) => trim(str_replace("\r", '', (string) $r->{$c})))->all(),
        ]);

        return view('admin.data-desil.index', [
            'rows'     => $rows,
            'gcSelesai' => $gcSelesai,
            'detail'   => $detail,
            'stats'    => $this->stats(),
            'wilayah'  => $this->wilayah(),
            'filters'  => $this->filters($request),
            'perPage'  => $perPage,
        ]);
    }

    public function export(Request $request)
    {
        $kolom = array_keys(P::KOLOM);
        $teks = ['nomor_kartu_keluarga', 'nomor_induk_kependudukan']; // dicegah jadi notasi ilmiah di Excel
        $query = $this->query($request)->select(array_merge(['id'], $kolom));

        return response()->streamDownload(function () use ($query, $kolom, $teks) {
            set_time_limit(0);
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_values(P::KOLOM));

            $query->chunkById(2000, function ($chunk) use ($out, $kolom, $teks) {
                foreach ($chunk as $r) {
                    $baris = [];
                    foreach ($kolom as $c) {
                        $v = trim(str_replace("\r", '', (string) $r->{$c}));
                        $baris[] = in_array($c, $teks, true) && $v !== '' ? "\t" . $v : $v;
                    }
                    fputcsv($out, $baris);
                }
            });

            fclose($out);
        }, 'data-desil-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /* ------------------------------------------------------------------ */

    private function filters(Request $r): array
    {
        return [
            'q'         => trim((string) $r->input('q')),
            'kecamatan' => (string) $r->input('kecamatan'),
            'kelurahan' => (string) $r->input('kelurahan'),
            'desil'     => collect((array) $r->input('desil', []))
                ->filter(fn ($d) => is_numeric($d) && $d >= 0 && $d <= 10)
                ->map(fn ($d) => (int) $d)->unique()->values()->all(),
            'rw'        => (string) $r->input('rw'),
            'rt'        => (string) $r->input('rt'),
            'umur_min'  => (string) $r->input('umur_min'),
            'umur_max'  => (string) $r->input('umur_max'),
            'kepala_kk' => $r->boolean('kepala_kk'),
            'usia_kerja' => $r->boolean('usia_kerja'),
            'gc'        => (string) $r->input('gc'),
            'bansos'    => collect((array) $r->input('bansos', []))
                ->filter(fn ($b) => isset(P::BANSOS[$b]))->values()->all(),
        ];
    }

    private function query(Request $request)
    {
        $f = $this->filters($request);
        $q = P::query();

        if ($f['q'] !== '') {
            if (ctype_digit($f['q'])) {
                // NIK / No. KK: pencarian awalan agar index terpakai
                $q->where(fn ($w) => $w
                    ->where('nomor_induk_kependudukan', 'like', $f['q'] . '%')
                    ->orWhere('nomor_kartu_keluarga', 'like', $f['q'] . '%'));
            } else {
                $q->where('nama', 'like', '%' . $f['q'] . '%');
            }
        }

        if ($f['kecamatan'] !== '') $q->where('kecamatan', $f['kecamatan']);
        if ($f['kelurahan'] !== '') $q->where('desa_kelurahan', $f['kelurahan']);

        if ($f['desil']) {
            $q->where(function ($w) use ($f) {
                $ids = array_values(array_diff($f['desil'], [0]));
                if ($ids) $w->whereIn('desil_nasional', $ids);
                if (in_array(0, $f['desil'], true)) {
                    $w->orWhereNull('desil_nasional')->orWhere('desil_nasional', 0);
                }
            });
        }

        if (ctype_digit($f['rw'])) $q->whereRaw('CAST(rw AS UNSIGNED) = ?', [(int) $f['rw']]);
        if (ctype_digit($f['rt'])) $q->whereRaw('CAST(rt AS UNSIGNED) = ?', [(int) $f['rt']]);
        if (ctype_digit($f['umur_min'])) $q->where('usia', '>=', (int) $f['umur_min']);
        if (ctype_digit($f['umur_max'])) $q->where('usia', '<=', (int) $f['umur_max']);
        if ($f['kepala_kk']) $q->where('hubungan_keluarga', 'like', 'Kepala%');
        if ($f['usia_kerja']) $q->whereBetween('usia', [15, 64]);

        foreach ($f['bansos'] as $key) {
            $q->whereRaw(P::sqlAktif(P::BANSOS[$key][0]));
        }

        if ($f['gc'] === 'sudah') {
            $q->whereIn('nomor_kartu_keluarga', $this->gcQuery()->select('no_kk'));
        } elseif ($f['gc'] === 'belum') {
            $q->whereNotIn('nomor_kartu_keluarga', $this->gcQuery()->select('no_kk'));
        }

        return $q;
    }

    /** Sasaran Ground Check yang sudah submit pada tahun berjalan (per No. KK). */
    private function gcQuery()
    {
        return GcSasaran::query()->whereNotNull('no_kk')->whereHas('verifikasis', fn ($v) => $v
            ->where('tahun', self::TAHUN_GC)->where('status_entry', 'submit'));
    }

    /** Statistik kartu & sebaran desil -- di-cache karena tabelnya >1 juta baris. */
    private function stats(): array
    {
        return Cache::remember('data_desil.stats', 600, function () {
            $pbi = P::sqlAktif('PBI_JK');
            $pkh = P::sqlAktif('BANSOS_PKH');

            $r = DB::table('data_penduduk_asli')->selectRaw(
                "COUNT(*) total,
                 SUM(desil_nasional BETWEEN 1 AND 4) prioritas,
                 SUM(desil_nasional BETWEEN 5 AND 10) mampu,
                 SUM(desil_nasional IS NULL OR desil_nasional = 0) non_desil,
                 SUM({$pbi}) pbi,
                 SUM({$pkh}) pkh"
            )->first();

            $sebaran = DB::table('data_penduduk_asli')
                ->selectRaw('COALESCE(desil_nasional, 0) d, COUNT(*) n')
                ->groupBy('d')->pluck('n', 'd')->map(fn ($n) => (int) $n)->all();

            return [
                'total'     => (int) $r->total,
                'prioritas' => (int) $r->prioritas,
                'mampu'     => (int) $r->mampu,
                'non_desil' => (int) $r->non_desil,
                'pbi'       => (int) $r->pbi,
                'pkh'       => (int) $r->pkh,
                'gc_kk'     => (int) $this->gcQuery()->distinct()->count('no_kk'),
                'sebaran'   => $sebaran,
            ];
        });
    }

    /** [kecamatan => [kelurahan, ...]] untuk dropdown bertingkat. */
    private function wilayah(): array
    {
        return Cache::remember('data_desil.wilayah', 86400, function () {
            return DB::table('data_penduduk_asli')
                ->whereNotNull('kecamatan')->where('kecamatan', '<>', '')
                ->select('kecamatan', 'desa_kelurahan')->distinct()
                ->orderBy('kecamatan')->orderBy('desa_kelurahan')->get()
                ->groupBy('kecamatan')
                ->map(fn ($g) => $g->pluck('desa_kelurahan')->filter()->values()->all())
                ->all();
        });
    }
}
