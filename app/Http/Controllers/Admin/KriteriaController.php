<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbiKriteriaOpsi;
use App\Models\PbiKriteriaParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Master Kriteria Kemiskinan (SK Wali Kota 460/Kep.196-Dinsos/2021).
 * Route dipagari middleware 'module:kelola-akses' => hanya Super Admin.
 */
class KriteriaController extends Controller
{
    public function index()
    {
        $parameters = PbiKriteriaParameter::with('opsis')->orderBy('urutan')->orderBy('id')->get();

        return view('admin.master-kriteria.index', compact('parameters'));
    }

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
