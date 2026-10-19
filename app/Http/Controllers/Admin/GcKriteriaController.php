<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GcKriteriaOpsi;
use App\Models\GcKriteriaParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Master Kriteria GROUND CHECK (39 variabel SOLID).
 * Route dipagari middleware 'module:kelola-akses' => hanya Super Admin.
 */
class GcKriteriaController extends Controller
{
    public function index()
    {
        $parameters = GcKriteriaParameter::with(['opsis', 'pbiParameter'])->orderBy('urutan')->orderBy('id')->get();

        return view('admin.master-gc-kriteria.index', compact('parameters'));
    }

    public function create()
    {
        $parameter = new GcKriteriaParameter([
            'kelompok' => 'kependudukan',
            'tipe_input' => 'pilihan',
            'bobot' => 1,
            'urutan' => ((int) GcKriteriaParameter::max('urutan')) + 1,
            'aktif' => true,
        ]);

        return view('admin.master-gc-kriteria.form', compact('parameter'));
    }

    public function store(Request $request)
    {
        $this->simpan($request, new GcKriteriaParameter());

        return redirect()->route('master.gc-kriteria.index')->with('success', 'Parameter Ground Check berhasil ditambahkan.');
    }

    public function edit(GcKriteriaParameter $parameter)
    {
        $parameter->load(['opsis', 'pbiParameter']);

        return view('admin.master-gc-kriteria.form', compact('parameter'));
    }

    public function update(Request $request, GcKriteriaParameter $parameter)
    {
        $this->simpan($request, $parameter);

        return redirect()->route('master.gc-kriteria.index')->with('success', 'Parameter Ground Check berhasil diperbarui.');
    }

    public function destroy(GcKriteriaParameter $parameter)
    {
        // Jawaban lama aman: tersimpan sebagai snapshot (parameter_id jadi NULL).
        $parameter->delete();

        return redirect()->route('master.gc-kriteria.index')->with('success', 'Parameter Ground Check berhasil dihapus.');
    }

    private function simpan(Request $request, GcKriteriaParameter $parameter): void
    {
        $butuhOpsi = in_array($request->input('tipe_input'), ['pilihan', 'pilihan_ganda'], true);

        // Tipe non-pilihan tidak punya opsi: buang baris opsi dari input sebelum validasi.
        if (! $butuhOpsi) {
            $request->request->remove('opsi');
        }

        $data = $request->validate([
            'kelompok' => ['required', Rule::in(array_keys(GcKriteriaParameter::KELOMPOK))],
            'nama' => ['required', 'string', 'max:255'],
            'tipe_input' => ['required', Rule::in(array_keys(GcKriteriaParameter::TIPE_INPUT))],
            'bobot' => ['required', 'numeric', 'between:0,99.99'],
            'urutan' => ['required', 'integer', 'min:1', 'max:65000'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'opsi' => [$butuhOpsi ? 'required' : 'nullable', 'array', $butuhOpsi ? 'min:2' : 'max:0'],
            'opsi.*.id' => ['nullable', 'integer'],
            'opsi.*.label' => ['required', 'string', 'max:255'],
            'opsi.*.nilai' => ['nullable', 'numeric', 'between:0,99.99'],
        ], [
            'kelompok.required' => 'Kelompok wajib dipilih.',
            'nama.required' => 'Pertanyaan wajib diisi.',
            'tipe_input.required' => 'Jenis isian wajib dipilih.',
            'bobot.required' => 'Bobot wajib diisi.',
            'bobot.numeric' => 'Bobot harus berupa angka.',
            'urutan.required' => 'Urutan wajib diisi.',
            'opsi.required' => 'Minimal 2 pilihan jawaban.',
            'opsi.min' => 'Minimal 2 pilihan jawaban.',
            'opsi.*.label.required' => 'Label pilihan jawaban wajib diisi.',
            'opsi.*.nilai.numeric' => 'Nilai pilihan harus berupa angka.',
        ]);

        DB::transaction(function () use ($request, $data, $parameter, $butuhOpsi) {
            $parameter->fill([
                'kelompok' => $data['kelompok'],
                'nama' => $data['nama'],
                'tipe_input' => $data['tipe_input'],
                'bobot' => $data['bobot'],
                'urutan' => $data['urutan'],
                'keterangan' => $data['keterangan'] ?? null,
                'aktif' => $request->boolean('aktif'),
            ])->save();

            if (! $butuhOpsi) {
                GcKriteriaOpsi::where('parameter_id', $parameter->id)->delete();

                return;
            }

            $keepIds = [];

            foreach (array_values($data['opsi']) as $i => $row) {
                $attrs = [
                    'label' => $row['label'],
                    'nilai' => ($row['nilai'] ?? '') === '' ? null : $row['nilai'],
                    'urutan' => $i + 1,
                ];

                $opsi = ! empty($row['id'])
                    ? GcKriteriaOpsi::where('parameter_id', $parameter->id)->find($row['id'])
                    : null;

                if ($opsi) {
                    $opsi->update($attrs);
                } else {
                    $opsi = $parameter->opsis()->create($attrs);
                }

                $keepIds[] = $opsi->id;
            }

            GcKriteriaOpsi::where('parameter_id', $parameter->id)->whereNotIn('id', $keepIds)->delete();
        });
    }
}
