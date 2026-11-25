<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GcKriteriaOpsi;
use App\Models\GcKriteriaParameter;
use App\Models\PbiApbd;
use App\Models\PbiKriteriaOpsi;
use App\Models\PbiKriteriaParameter;
use App\Support\PbiSkorSinkron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Master Kriteria PBI (hanya Super Admin).
 *
 * Halaman utama kini memuat pertanyaan form Lengkapi Data (Master Kriteria Ground Check)
 * yang berlaku untuk PBI APBD (target pbi / pbi_gc), dengan:
 *   - saklar "Tampil di verifikasi"  -> baris muncul di kartu Isian Parameter & Verifikasi Kelurahan
 *   - saklar "Ikut skor"             -> parameter skor PBI padanannya dihitung (pbi_kriteria_parameters.aktif)
 *   - pemetaan opsi jawaban GC -> opsi PBI (dasar nilai skor)
 * Bobot / indeks / opsi skor tetap diedit lewat form parameter PBI (edit / tambah di bawah).
 */
class KriteriaController extends Controller
{
    public function index(Request $request)
    {
        $tabs = collect(['semua' => ['label' => 'Semua', 'icon' => 'list', 'kelompok' => []]])
            ->merge(collect(GcKriteriaParameter::TAB)->except('identitas'));
        $tab = $request->query('tab');
        if (! $tabs->has($tab)) {
            $tab = 'semua';
        }

        $q = trim((string) $request->query('q'));

        $parameters = GcKriteriaParameter::with(['pbiParameter', 'opsis'])
            ->where('aktif', true)                                   // hanya yang ada di form Lengkapi Data
            ->where('kelompok', '!=', 'identitas_keluarga')
            ->when($tab !== 'semua', fn($s) => $s->whereIn('kelompok', $tabs->get($tab)['kelompok']))
            ->when($q !== '', fn($s) => $s->where('nama', 'like', "%{$q}%"))
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
            
        $skorParams = PbiKriteriaParameter::with('opsis')->orderBy('urutan')->orderBy('id')->get();

        return view('admin.master-kriteria.index', [
            'tabs' => $tabs,
            'tab' => $tab,
            'q' => $q,
            'parameters' => $parameters,
            'skorParams' => $skorParams,
            'skorAktif' => $skorParams->where('aktif', true)->count(),
            'skorTotal' => $skorParams->count(),
        ]);
    }

    /** PUT /master/kriteria/gc/{gcParameter}/toggle  (kolom = tampil_verifikasi | ikut_skor) */
    public function toggle(Request $request, GcKriteriaParameter $gcParameter)
    {
        $data = $request->validate(['kolom' => ['required', Rule::in(['tampil_verifikasi', 'ikut_skor'])]]);

        if ($data['kolom'] === 'tampil_verifikasi') {
            $gcParameter->update(['tampil_verifikasi' => ! $gcParameter->tampil_verifikasi]);

            return back()->with('success', 'Tampilan di verifikasi untuk "' . $gcParameter->nama . '" diubah.');
        }

        $pp = $gcParameter->pbiParameter;
        abort_unless($pp, 404, 'Pertanyaan ini tidak punya parameter skor PBI.');

        $pp->update(['aktif' => ! $pp->aktif]);

        return back()->with('success', 'Parameter skor "' . $pp->nama . '" ' . ($pp->aktif ? 'ikut dihitung.' : 'tidak ikut dihitung.')
            . ' Klik "Hitung Ulang Skor" agar pengajuan yang berjalan ikut berubah.');
    }

    /** GET /master/kriteria/gc/{gcParameter}/peta */
    public function peta(GcKriteriaParameter $gcParameter)
    {
        abort_unless($gcParameter->pbi_parameter_id && $gcParameter->tipe_input === 'pilihan', 404);

        $gcParameter->load(['opsis', 'pbiParameter.opsis']);

        return view('admin.master-kriteria.peta', ['p' => $gcParameter]);
    }

    /** PUT /master/kriteria/gc/{gcParameter}/peta */
    public function petaUpdate(Request $request, GcKriteriaParameter $gcParameter)
    {
        abort_unless($gcParameter->pbi_parameter_id && $gcParameter->tipe_input === 'pilihan', 404);

        $gcParameter->load(['opsis', 'pbiParameter.opsis']);
        $sah = $gcParameter->pbiParameter->opsis->pluck('id')->all();

        $request->validate([
            'peta' => ['nullable', 'array'],
            'peta.*' => ['nullable', 'integer', Rule::in($sah)],
        ], ['peta.*.in' => 'Pilihan skor tidak valid.']);

        foreach ($gcParameter->opsis as $o) {
            $v = $request->input("peta.{$o->id}");
            GcKriteriaOpsi::whereKey($o->id)->update(['pbi_opsi_id' => $v ?: null]);
        }

        return redirect()->route('master.kriteria.index')
            ->with('success', 'Pemetaan jawaban "' . $gcParameter->nama . '" disimpan.');
    }

    /** POST /master/kriteria/hitung-ulang — hanya pengajuan yang masih berjalan (bukan disetujui / ditolak). */
    public function hitungUlang()
    {
        $n = 0;

        PbiApbd::query()
            ->whereNotIn('status', ['disetujui', 'ditolak'])
            ->whereHas('gcJawabans', fn($q) => $q->whereNotNull('jawaban'))
            ->chunkById(100, function ($rows) use (&$n) {
                foreach ($rows as $pbi) {
                    PbiSkorSinkron::hitungUlang($pbi);
                    $n++;
                }
            });

        return back()->with('success', "Skor {$n} pengajuan yang masih berjalan dihitung ulang.");
    }

    // ------------------------------------------------------------------
    // Parameter skor PBI (bobot, indeks, opsi + indeks terintegrasi)
    // ------------------------------------------------------------------

    public function create()
    {
        $parameter = new PbiKriteriaParameter([
            'urutan' => ((int) PbiKriteriaParameter::max('urutan')) + 1,
            'indeks' => 1.00,
            'aktif' => true,
        ]);

        return view('admin.master-kriteria.form', compact('parameter'));
    }

    public function store(Request $request)
    {
        $this->simpan($request, new PbiKriteriaParameter());

        return redirect()->route('master.kriteria.index')->with('success', 'Parameter berhasil ditambahkan.');
    }

    public function edit(PbiKriteriaParameter $parameter)
    {
        $parameter->load('opsis');

        return view('admin.master-kriteria.form', compact('parameter'));
    }

    public function update(Request $request, PbiKriteriaParameter $parameter)
    {
        $this->simpan($request, $parameter);

        return redirect()->route('master.kriteria.index')->with('success', 'Parameter berhasil diperbarui.');
    }

    public function destroy(PbiKriteriaParameter $parameter)
    {
        // Jawaban pengajuan lama aman: tersimpan sebagai snapshot (parameter_id jadi NULL).
        GcKriteriaParameter::where('pbi_parameter_id', $parameter->id)->update(['pbi_parameter_id' => null]);
        $parameter->delete();

        return redirect()->route('master.kriteria.index')->with('success', 'Parameter berhasil dihapus.');
    }

    private function simpan(Request $request, PbiKriteriaParameter $parameter): void
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'bobot' => ['required', 'numeric', 'between:0,99.99'],
            'indeks' => ['required', 'numeric', 'between:0,99.99'],
            'urutan' => ['required', 'integer', 'min:1', 'max:65000'],
            'opsi' => ['required', 'array', 'min:2'],
            'opsi.*.id' => ['nullable', 'integer'],
            'opsi.*.label' => ['required', 'string', 'max:255'],
            'opsi.*.indeks_terintegrasi' => ['required', 'numeric', 'between:0,99.99'],
        ], [
            'nama.required' => 'Nama parameter wajib diisi.',
            'bobot.required' => 'Bobot wajib diisi.',
            'bobot.numeric' => 'Bobot harus berupa angka.',
            'indeks.required' => 'Indeks wajib diisi.',
            'urutan.required' => 'Urutan wajib diisi.',
            'opsi.required' => 'Minimal 2 opsi jawaban.',
            'opsi.min' => 'Minimal 2 opsi jawaban.',
            'opsi.*.label.required' => 'Label opsi jawaban wajib diisi.',
            'opsi.*.indeks_terintegrasi.required' => 'Indeks terintegrasi tiap opsi wajib diisi.',
            'opsi.*.indeks_terintegrasi.numeric' => 'Indeks terintegrasi harus berupa angka.',
        ]);

        DB::transaction(function () use ($request, $data, $parameter) {
            $parameter->fill([
                'nama' => $data['nama'],
                'bobot' => $data['bobot'],
                'indeks' => $data['indeks'],
                'urutan' => $data['urutan'],
                'aktif' => $request->boolean('aktif'),
            ])->save();

            $keepIds = [];

            foreach (array_values($data['opsi']) as $i => $row) {
                $attrs = [
                    'label' => $row['label'],
                    'indeks_terintegrasi' => $row['indeks_terintegrasi'],
                    'urutan' => $i + 1,
                ];

                $opsi = ! empty($row['id'])
                    ? PbiKriteriaOpsi::where('parameter_id', $parameter->id)->find($row['id'])
                    : null;

                if ($opsi) {
                    $opsi->update($attrs);
                } else {
                    $opsi = $parameter->opsis()->create($attrs);
                }

                $keepIds[] = $opsi->id;
            }

            PbiKriteriaOpsi::where('parameter_id', $parameter->id)->whereNotIn('id', $keepIds)->delete();
        });
    }
}
