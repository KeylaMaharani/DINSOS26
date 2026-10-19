<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GcJawaban;
use App\Models\GcKriteriaParameter;
use App\Models\GcSasaran;
use App\Models\GcVerifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Alur entry Ground Check 2026 (mengikuti Panduan Entry Data SOLID):
 *  daftar sasaran -> klik ikon keluarga -> daftar anggota KK -> klik pensil (Form Verifikasi Data)
 *  -> pilih status -> bila Ditemukan: form tab A-G -> centang pernyataan -> Submit.
 */
class GroundCheckController extends Controller
{
    private const TAHUN = 2026;

    /** Langkah 3: daftar sasaran + ringkasan. */
    public function index(Request $request)
    {
        $tahun = self::TAHUN;
        $q = trim((string) $request->query('q'));
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;

        $sasarans = GcSasaran::query()
            ->select('gc_sasarans.*')
            ->selectSub(function ($s) {
                $s->from('gc_sasarans as m')->selectRaw('count(*)')->whereColumn('m.no_kk', 'gc_sasarans.no_kk');
            }, 'kk_total')
            ->selectSub(function ($s) use ($tahun) {
                $s->from('gc_sasarans as m')
                    ->join('gc_verifikasis as v', 'v.sasaran_id', '=', 'm.id')
                    ->selectRaw('count(*)')
                    ->whereColumn('m.no_kk', 'gc_sasarans.no_kk')
                    ->where('v.tahun', $tahun)
                    ->where('v.status_entry', 'submit');
            }, 'kk_selesai')
            ->with(['verifikasis' => fn ($v) => $v->where('tahun', $tahun)])
            ->when($q !== '', function ($s) use ($q) {
                $like = "%{$q}%";
                $s->where(fn ($w) => $w->where('nama', 'like', $like)->orWhere('nik', 'like', $like)->orWhere('no_kk', 'like', $like));
            })
            ->orderBy('kelurahan')->orderBy('no_kk')->orderBy('nama')
            ->paginate($perPage)
            ->withQueryString();

        $total = GcSasaran::count();
        $sudah = GcVerifikasi::where('tahun', $tahun)->where('status_entry', 'submit')->count();
        $belum = max($total - $sudah, 0);
        $progres = $total > 0 ? round($sudah / $total * 100, 1) : 0;

        return view('admin.ground-check.index', compact('sasarans', 'total', 'sudah', 'belum', 'progres', 'q', 'perPage'));
    }

    /** Langkah 5: anggota satu KK. */
    public function keluarga(string $noKk)
    {
        $tahun = self::TAHUN;

        $anggota = GcSasaran::where('no_kk', $noKk)
            ->with(['verifikasis' => fn ($v) => $v->where('tahun', $tahun)])
            ->orderBy('nama')
            ->get();

        abort_if($anggota->isEmpty(), 404);

        return view('admin.ground-check.keluarga', compact('noKk', 'anggota'));
    }

    /** Langkah 6: simpan status (Form Verifikasi Data). */
    public function statusStore(Request $request, GcSasaran $sasaran)
    {
        $data = $request->validate([
            'status_temuan' => ['required', Rule::in(array_keys(GcVerifikasi::STATUS_TEMUAN))],
        ], [
            'status_temuan.required' => 'Status wajib dipilih.',
            'status_temuan.in' => 'Status tidak valid.',
        ]);

        $v = GcVerifikasi::firstOrNew(['sasaran_id' => $sasaran->id, 'tahun' => self::TAHUN]);
        $sebelumnya = $v->status_temuan;

        $v->status_temuan = $data['status_temuan'];
        $v->user_id = auth()->id();

        if ($data['status_temuan'] === 'ditemukan') {
            // Baru ditemukan (atau sebelumnya pindah/meninggal): mulai ulang sebagai draf.
            if (! $v->exists || $sebelumnya !== 'ditemukan') {
                $v->status_entry = 'draft';
                $v->submitted_at = null;
                $v->pernyataan = false;
            }
        } else {
            // Pindah/Tidak Ditemukan/Meninggal: tidak ada form lanjutan, langsung selesai.
            $v->status_entry = 'submit';
            $v->submitted_at = now();
            $v->pernyataan = false;
        }

        $v->save();

        if ($data['status_temuan'] === 'ditemukan') {
            return redirect()->route('ground-check.form', $sasaran);
        }

        return redirect()->route('ground-check.keluarga', $sasaran->no_kk)
            ->with('success', 'Status ' . $sasaran->nama . ' disimpan: ' . GcVerifikasi::STATUS_TEMUAN[$data['status_temuan']] . '.');
    }

    /** Langkah 7-13: form tab A-G. */
    public function form(Request $request, GcSasaran $sasaran)
    {
        $v = $this->verifikasiDitemukan($sasaran);
        if (! $v) {
            return redirect()->route('ground-check.keluarga', $sasaran->no_kk)
                ->with('error', 'Pilih status "Ditemukan" terlebih dahulu sebelum mengisi form.');
        }

        $params = GcKriteriaParameter::aktif()->with('opsis')->get();

        $tabs = [];
        foreach (GcKriteriaParameter::TAB as $key => $tab) {
            $isi = $params->whereIn('kelompok', $tab['kelompok'])->values();
            if ($isi->isNotEmpty()) {
                $tabs[] = $tab + ['key' => $key, 'params' => $isi];
            }
        }

        $nilai = [];
        foreach ($v->jawabans()->get() as $j) {
            if ($j->parameter_id) {
                $nilai[$j->parameter_id] = $j->jawaban;
            }
        }

        if (empty($nilai)) {
            $nilai = $this->prefill($sasaran, $params);
        }

        $tabAwal = (int) session('tab', 0);

        return view('admin.ground-check.form', compact('sasaran', 'v', 'tabs', 'nilai', 'tabAwal'));
    }

    /** Simpan draf / submit. */
    public function simpan(Request $request, GcSasaran $sasaran)
    {
        $v = $this->verifikasiDitemukan($sasaran);
        abort_if(! $v, 403);

        $submit = $request->input('aksi') === 'submit';
        $params = GcKriteriaParameter::aktif()->with('opsis')->get();
        $existing = $v->jawabans()->get()->keyBy('parameter_id');

        $rules = [];
        $attributes = [];

        foreach ($params as $p) {
            $key = "jawaban.{$p->id}";
            $attributes[$key] = $p->nama;
            $req = $submit ? 'required' : 'nullable';
            $labels = $p->opsis->pluck('label')->all();

            switch ($p->tipe_input) {
                case 'pilihan':
                    $rules[$key] = $labels
                        ? [$req, 'string', Rule::in($labels)]
                        : ['nullable', 'string', 'max:500'];
                    break;

                case 'pilihan_ganda':
                    $rules[$key] = $submit && $labels ? ['required', 'array', 'min:1'] : ['nullable', 'array'];
                    $rules["{$key}.*"] = $labels ? ['string', Rule::in($labels)] : ['string', 'max:255'];
                    $attributes["{$key}.*"] = $p->nama;
                    break;

                case 'angka':
                    $rules[$key] = $this->koordinatAngka($p)
                        ? [$req, 'numeric']
                        : [$req, 'numeric', 'min:0'];
                    break;

                case 'tanggal':
                    $rules[$key] = ['nullable', 'date'];
                    break;

                case 'koordinat':
                    $rules[$key] = [$req, 'string', 'max:100'];
                    break;

                case 'foto':
                    $sudahAda = ! empty(optional($existing->get($p->id))->jawaban);
                    $rules[$key] = [$submit && ! $sudahAda ? 'required' : 'nullable', 'image', 'max:5120'];
                    break;

                default: // teks
                    $rules[$key] = ['nullable', 'string', 'max:500'];
            }
        }

        if ($submit) {
            $rules['pernyataan'] = ['accepted'];
        }

        $request->validate($rules, [
            'required' => ':attribute wajib diisi.',
            'in' => 'Pilihan pada :attribute tidak valid.',
            'numeric' => ':attribute harus berupa angka.',
            'min.numeric' => ':attribute tidak boleh negatif.',
            'min.array' => 'Pilih minimal satu jawaban untuk :attribute.',
            'image' => ':attribute harus berupa gambar.',
            'max.file' => ':attribute maksimal 5 MB.',
            'date' => ':attribute bukan tanggal yang valid.',
            'accepted' => 'Centang pernyataan kebenaran data terlebih dahulu.',
        ], $attributes);

        DB::transaction(function () use ($request, $params, $existing, $v, $submit) {
            foreach ($params as $p) {
                $row = $existing->get($p->id) ?? new GcJawaban(['verifikasi_id' => $v->id, 'parameter_id' => $p->id]);
                $input = $request->input("jawaban.{$p->id}");
                $jawaban = null;
                $skor = null;

                switch ($p->tipe_input) {
                    case 'pilihan':
                        $jawaban = ($input === null || $input === '') ? null : (string) $input;
                        $skor = $jawaban !== null ? optional($p->opsis->firstWhere('label', $jawaban))->nilai : null;
                        break;

                    case 'pilihan_ganda':
                        $arr = array_values(array_filter((array) $input, fn ($x) => $x !== null && $x !== ''));
                        $jawaban = $arr ? json_encode($arr, JSON_UNESCAPED_UNICODE) : null;
                        break;

                    case 'foto':
                        $jawaban = $row->jawaban;
                        if ($request->hasFile("jawaban.{$p->id}")) {
                            if ($jawaban) {
                                Storage::disk('public')->delete($jawaban);
                            }
                            $jawaban = $request->file("jawaban.{$p->id}")->store("ground-check/{$v->id}", 'public');
                        }
                        break;

                    default:
                        $jawaban = ($input === null || $input === '') ? null : (string) $input;
                }

                $row->fill([
                    'nama_parameter' => $p->nama,
                    'bobot' => $p->bobot,
                    'jawaban' => $jawaban,
                    'skor' => $skor,
                ])->save();
            }

            $v->pernyataan = $submit;
            $v->status_entry = $submit ? 'submit' : 'draft';
            $v->submitted_at = $submit ? now() : null;
            $v->user_id = auth()->id();
            $v->save();
        });

        if ($submit) {
            return redirect()->route('ground-check.keluarga', $sasaran->no_kk)
                ->with('success', 'Data ' . $sasaran->nama . ' berhasil disubmit.');
        }

        return redirect()->route('ground-check.form', $sasaran)
            ->with('success', 'Draf tersimpan. Kamu bisa melanjutkan kapan saja.')
            ->with('tab', (int) $request->input('tab_aktif', 0));
    }

    private function verifikasiDitemukan(GcSasaran $sasaran): ?GcVerifikasi
    {
        return GcVerifikasi::where('sasaran_id', $sasaran->id)
            ->where('tahun', self::TAHUN)
            ->where('status_temuan', 'ditemukan')
            ->first();
    }

    private function koordinatAngka(GcKriteriaParameter $p): bool
    {
        return Str::contains(Str::lower($p->nama), ['lintang', 'bujur']);
    }

    /**
     * Isian awal: data identitas dari daftar sasaran, serta isian keluarga/aset
     * dari anggota satu KK yang sudah disubmit (supaya tidak mengetik ulang).
     */
    private function prefill(GcSasaran $sasaran, $params): array
    {
        $nilai = [];

        $identitas = [
            'nama' => $sasaran->nama,
            'no kk' => $sasaran->no_kk,
            'alamat' => $sasaran->alamat,
            'tanggal lahir' => optional($sasaran->tanggal_lahir)->format('Y-m-d'),
        ];
        foreach ($params as $p) {
            $nama = Str::lower(trim($p->nama));
            if (array_key_exists($nama, $identitas) && $identitas[$nama] !== null) {
                $nilai[$p->id] = $identitas[$nama];
            }
        }

        $sumber = GcVerifikasi::where('tahun', self::TAHUN)
            ->where('status_entry', 'submit')
            ->where('status_temuan', 'ditemukan')
            ->whereHas('sasaran', fn ($s) => $s->where('no_kk', $sasaran->no_kk)->where('id', '!=', $sasaran->id))
            ->latest('submitted_at')
            ->first();

        if ($sumber) {
            $idKeluarga = $params->whereIn('kelompok', ['keluarga', 'aset_bergerak', 'aset_tidak_bergerak'])->pluck('id')->all();
            foreach ($sumber->jawabans()->whereIn('parameter_id', $idKeluarga)->get() as $j) {
                if ($j->jawaban !== null) {
                    $nilai[$j->parameter_id] = $j->jawaban;
                }
            }
        }

        return $nilai;
    }
}
