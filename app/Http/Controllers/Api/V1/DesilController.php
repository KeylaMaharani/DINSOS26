<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DesilResource;
use App\Models\DataPendudukAsli as P;
use App\Models\GcSasaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DesilController extends Controller
{
    private const TAHUN_GC = 2026;

    /** GET /api/v1/desil */
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'min:3', 'max:100', 'required_without_all:kecamatan,kelurahan'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'rw' => ['nullable', 'digits_between:1,3'],
            'rt' => ['nullable', 'digits_between:1,3'],
            'umur_min' => ['nullable', 'integer', 'between:0,150'],
            'umur_max' => ['nullable', 'integer', 'between:0,150'],
            'kepala_kk' => ['nullable', 'boolean'],
            'usia_kerja' => ['nullable', 'boolean'],
            'gc' => ['nullable', 'in:sudah,belum'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'with_total' => ['nullable', 'boolean'],
        ]);

        $perPage = min((int) $request->input('per_page', 25), $this->klien()->per_page_max);
        $query = $this->filtered($request)->orderBy('id');

        // COUNT(*) pada 1 juta+ baris lambat, jadi total hanya dihitung bila diminta
        $rows = $request->boolean('with_total')
            ? $query->paginate($perPage)
            : $query->simplePaginate($perPage);

        return DesilResource::collection($rows->withQueryString())->additional(['success' => true]);
    }

    /** GET /api/v1/desil/nik/{nik} */
    public function nik(string $nik)
    {
        $row = P::where('nomor_induk_kependudukan', $nik)->first();

        if (! $row || ! $this->klien()->wilayahBoleh($row->kecamatan)) {
            return $this->tidakAda('NIK tidak ditemukan.');
        }

        return response()->json([
            'success' => true,
            'data' => (new DesilResource($row))->resolve(),
            'ground_check_selesai' => $this->gcQuery()->where('no_kk', $row->nomor_kartu_keluarga)->exists(),
        ]);
    }

    /** GET /api/v1/desil/kk/{noKk} */
    public function kk(string $noKk)
    {
        $rows = P::where('nomor_kartu_keluarga', $noKk)->orderBy('id')->get();

        if ($rows->isEmpty() || ! $this->klien()->wilayahBoleh($rows->first()->kecamatan)) {
            return $this->tidakAda('No. KK tidak ditemukan.');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'no_kk' => $noKk,
                'jumlah_anggota' => $rows->count(),
                'ground_check_selesai' => $this->gcQuery()->where('no_kk', $noKk)->exists(),
                'anggota' => DesilResource::collection($rows)->resolve(),
            ],
        ]);
    }

    /** GET /api/v1/desil/statistik */
    public function statistik()
    {
        $data = Cache::remember('api.desil.stats', 600, function () {
            $pbi = P::sqlAktif('PBI_JK');
            $pkh = P::sqlAktif('BANSOS_PKH');

            $r = DB::table('data_penduduk_asli')->selectRaw(
                "COUNT(*) total,
                 SUM(desil_nasional BETWEEN 1 AND 4) prioritas,
                 SUM(desil_nasional BETWEEN 5 AND 10) mampu,
                 SUM(desil_nasional IS NULL OR desil_nasional = 0) tidak_terdaftar,
                 SUM({$pbi}) penerima_pbi_jk,
                 SUM({$pkh}) penerima_pkh"
            )->first();

            $sebaran = DB::table('data_penduduk_asli')
                ->selectRaw('COALESCE(desil_nasional, 0) d, COUNT(*) n')
                ->groupBy('d')->orderBy('d')->pluck('n', 'd');

            return [
                'total_penduduk' => (int) $r->total,
                'desil_prioritas_1_4' => (int) $r->prioritas,
                'desil_mampu_5_10' => (int) $r->mampu,
                'tidak_terdaftar_desil' => (int) $r->tidak_terdaftar,
                'penerima_pbi_jk' => (int) $r->penerima_pbi_jk,
                'penerima_pkh' => (int) $r->penerima_pkh,
                'sebaran_desil' => $sebaran->map(fn ($n, $d) => [
                    'desil' => (int) $d,
                    'label' => P::DESIL_LABEL[(int) $d] ?? null,
                    'jumlah' => (int) $n,
                ])->values()->all(),
            ];
        });

        $data['kk_ground_check_selesai'] = (int) $this->gcQuery()->distinct()->count('no_kk');

        return response()->json(['success' => true, 'data' => $data]);
    }

    /** GET /api/v1/desil/wilayah  (cache sama dengan menu Data Desil di admin) */
    public function wilayah()
    {
        $data = Cache::remember('data_desil.wilayah', 86400, function () {
            return DB::table('data_penduduk_asli')
                ->whereNotNull('kecamatan')->where('kecamatan', '<>', '')
                ->select('kecamatan', 'desa_kelurahan')->distinct()
                ->orderBy('kecamatan')->orderBy('desa_kelurahan')->get()
                ->groupBy('kecamatan')
                ->map(fn ($g) => $g->pluck('desa_kelurahan')->filter()->values()->all())
                ->all();
        });

        $data = collect($data);
        if ($kec = $this->klien()->kecamatan) {
            $data = $data->only($kec);
        }

        return response()->json([
            'success' => true,
            'data' => $data->map(fn ($kel, $kec) => ['kecamatan' => $kec, 'kelurahan' => $kel])->values(),
        ]);
    }

    /* ------------------------------------------------------------------ */

    private function filtered(Request $r)
    {
        $q = P::query();

        if ($kec = $this->klien()->kecamatan) {
            $q->whereIn('kecamatan', $kec);
        }

        $cari = trim((string) $r->input('q'));

        if ($cari !== '') {
            if (ctype_digit($cari)) {
                // NIK / No. KK: pencarian awalan supaya index terpakai
                $q->where(fn ($w) => $w
                    ->where('nomor_induk_kependudukan', 'like', $cari . '%')
                    ->orWhere('nomor_kartu_keluarga', 'like', $cari . '%'));
            } else {
                $q->where('nama', 'like', '%' . $cari . '%');
            }
        }

        if ($r->filled('kecamatan')) $q->where('kecamatan', $r->input('kecamatan'));
        if ($r->filled('kelurahan')) $q->where('desa_kelurahan', $r->input('kelurahan'));

        $desil = $this->daftar($r->input('desil'))
            ->filter(fn ($d) => is_numeric($d) && $d >= 0 && $d <= 10)->map(fn ($d) => (int) $d)->unique()->values()->all();

        if ($desil) {
            $q->where(function ($w) use ($desil) {
                $ids = array_values(array_diff($desil, [0]));
                if ($ids) $w->whereIn('desil_nasional', $ids);
                if (in_array(0, $desil, true)) {
                    $w->orWhereNull('desil_nasional')->orWhere('desil_nasional', 0);
                }
            });
        }

        if ($r->filled('rw')) $q->whereRaw('CAST(rw AS UNSIGNED) = ?', [(int) $r->input('rw')]);
        if ($r->filled('rt')) $q->whereRaw('CAST(rt AS UNSIGNED) = ?', [(int) $r->input('rt')]);
        if ($r->filled('umur_min')) $q->where('usia', '>=', (int) $r->input('umur_min'));
        if ($r->filled('umur_max')) $q->where('usia', '<=', (int) $r->input('umur_max'));
        if ($r->boolean('kepala_kk')) $q->where('hubungan_keluarga', 'like', 'Kepala%');
        if ($r->boolean('usia_kerja')) $q->whereBetween('usia', [15, 64]);

        $this->daftar($r->input('bansos'))->filter(fn ($b) => isset(P::BANSOS[$b]))->unique()
            ->each(fn ($b) => $q->whereRaw(P::sqlAktif(P::BANSOS[$b][0])));

        if ($r->input('gc') === 'sudah') {
            $q->whereIn('nomor_kartu_keluarga', $this->gcQuery()->select('no_kk'));
        } elseif ($r->input('gc') === 'belum') {
            $q->whereNotIn('nomor_kartu_keluarga', $this->gcQuery()->select('no_kk'));
        }

        return $q;
    }

    /** Terima array (desil[]=1&desil[]=2) maupun koma (desil=1,2). */
    private function daftar($v)
    {
        return collect(is_array($v) ? $v : explode(',', (string) $v))
            ->map(fn ($x) => trim((string) $x))->filter(fn ($x) => $x !== '');
    }

    private function gcQuery()
    {
        return GcSasaran::query()->whereNotNull('no_kk')->whereHas('verifikasis', fn ($v) => $v
            ->where('tahun', self::TAHUN_GC)->where('status_entry', 'submit'));
    }

    private function tidakAda(string $pesan)
    {
        return response()->json(['success' => false, 'message' => $pesan], 404);
    }

    private function klien(): \App\Models\ApiClient
    {
        return request()->attributes->get('api_client');
    }
}