<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GcLapanganUsaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Master Lapangan Usaha + Pekerjaan. Route dipagari 'module:kelola-akses' (Super Admin). */
class GcLapanganUsahaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $items = GcLapanganUsaha::withCount('pekerjaans')
            ->when($q !== '', fn ($s) => $s->where('nama', 'like', "%{$q}%"))
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.master-lapangan-usaha.index', compact('items', 'q'));
    }

    public function create()
    {
        $lapanganUsaha = new GcLapanganUsaha([
            'urutan' => ((int) GcLapanganUsaha::max('urutan')) + 1,
            'aktif' => true,
        ]);

        return view('admin.master-lapangan-usaha.form', compact('lapanganUsaha'));
    }

    public function store(Request $request)
    {
        $this->simpan($request, new GcLapanganUsaha());

        return redirect()->route('master.lapangan-usaha.index')->with('success', 'Lapangan usaha berhasil ditambahkan.');
    }

    public function edit(GcLapanganUsaha $lapanganUsaha)
    {
        $lapanganUsaha->load('pekerjaans');

        return view('admin.master-lapangan-usaha.form', compact('lapanganUsaha'));
    }

    public function update(Request $request, GcLapanganUsaha $lapanganUsaha)
    {
        $this->simpan($request, $lapanganUsaha);

        return redirect()->route('master.lapangan-usaha.index')->with('success', 'Lapangan usaha berhasil diperbarui.');
    }

    public function destroy(GcLapanganUsaha $lapanganUsaha)
    {
        // Jawaban lama aman: tersimpan sebagai teks (snapshot). Pekerjaan ikut terhapus (cascade).
        $lapanganUsaha->delete();

        return redirect()->route('master.lapangan-usaha.index')->with('success', 'Lapangan usaha berhasil dihapus.');
    }

    private function simpan(Request $request, GcLapanganUsaha $lap): void
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('gc_lapangan_usahas', 'nama')->ignore($lap->id)],
            'ikon' => ['nullable', 'string', 'max:16'],
            'penjelasan' => ['nullable', 'string', 'max:2000'],
            'contoh_berusaha_sendiri' => ['nullable', 'string', 'max:1000'],
            'contoh_buruh' => ['nullable', 'string', 'max:1000'],
            'urutan' => ['required', 'integer', 'min:1', 'max:65000'],
            'pekerjaan' => ['nullable', 'array'],
            'pekerjaan.*.id' => ['nullable', 'integer'],
            'pekerjaan.*.nama' => ['required', 'string', 'max:255', 'distinct:ignore_case'],
        ], [
            'nama.required' => 'Nama lapangan usaha wajib diisi.',
            'nama.unique' => 'Nama lapangan usaha sudah ada.',
            'urutan.required' => 'Urutan wajib diisi.',
            'pekerjaan.*.nama.required' => 'Nama pekerjaan wajib diisi (hapus baris yang kosong).',
            'pekerjaan.*.nama.distinct' => 'Ada nama pekerjaan yang dobel.',
        ]);

        DB::transaction(function () use ($request, $data, $lap) {
            $lap->fill([
                'nama' => $data['nama'],
                'ikon' => $data['ikon'] ?? null,
                'penjelasan' => $data['penjelasan'] ?? null,
                'contoh_berusaha_sendiri' => $data['contoh_berusaha_sendiri'] ?? null,
                'contoh_buruh' => $data['contoh_buruh'] ?? null,
                'urutan' => $data['urutan'],
                'aktif' => $request->boolean('aktif'),
            ])->save();

            $keep = [];
            $mentah = array_values((array) $request->input('pekerjaan', []));
            foreach (array_values($data['pekerjaan'] ?? []) as $i => $row) {
                $attrs = [
                    'nama' => $row['nama'],
                    'urutan' => $i + 1,
                    'aktif' => ! empty($mentah[$i]['aktif']),
                ];

                $pk = ! empty($row['id']) ? $lap->pekerjaans()->find($row['id']) : null;
                if ($pk) {
                    $pk->update($attrs);
                } else {
                    $pk = $lap->pekerjaans()->create($attrs);
                }
                $keep[] = $pk->id;
            }

            $lap->pekerjaans()->whereNotIn('id', $keep)->delete();
        });
    }
}
