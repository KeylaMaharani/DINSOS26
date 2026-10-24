<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GcPenyakitKronis;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Master Penyakit Kronis/Menahun (satu halaman, edit per baris). Super Admin saja. */
class GcPenyakitKronisController extends Controller
{
    public function index()
    {
        $items = GcPenyakitKronis::orderBy('urutan')->orderBy('id')->get();

        return view('admin.master-penyakit-kronis.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validateWithBag('tambah', [
            'nama' => ['required', 'string', 'max:255', 'unique:gc_penyakit_kronis,nama'],
        ], [
            'nama.required' => 'Nama penyakit wajib diisi.',
            'nama.unique' => 'Penyakit sudah ada.',
        ]);

        GcPenyakitKronis::create([
            'nama' => $data['nama'],
            'urutan' => ((int) GcPenyakitKronis::max('urutan')) + 1,
            'aktif' => true,
        ]);

        return back()->with('success', 'Penyakit berhasil ditambahkan.');
    }

    public function update(Request $request, GcPenyakitKronis $penyakit)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('gc_penyakit_kronis', 'nama')->ignore($penyakit->id)],
            'urutan' => ['required', 'integer', 'min:1', 'max:65000'],
        ], [
            'nama.required' => 'Nama penyakit wajib diisi.',
            'nama.unique' => 'Penyakit sudah ada.',
        ]);

        $penyakit->update($data + ['aktif' => $request->boolean('aktif')]);

        return back()->with('success', 'Penyakit berhasil diperbarui.');
    }

    public function destroy(GcPenyakitKronis $penyakit)
    {
        $penyakit->delete();

        return back()->with('success', 'Penyakit berhasil dihapus.');
    }
}
