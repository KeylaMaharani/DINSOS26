<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GcKeterampilan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Master Keterampilan Khusus (satu halaman, edit per baris). Super Admin saja. */
class GcKeterampilanController extends Controller
{
    public function index()
    {
        $items = GcKeterampilan::orderBy('urutan')->orderBy('id')->get();

        return view('admin.master-keterampilan.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validateWithBag('tambah', [
            'nama' => ['required', 'string', 'max:255', 'unique:gc_keterampilans,nama'],
        ], [
            'nama.required' => 'Nama keterampilan wajib diisi.',
            'nama.unique' => 'Keterampilan sudah ada.',
        ]);

        GcKeterampilan::create([
            'nama' => $data['nama'],
            'urutan' => ((int) GcKeterampilan::max('urutan')) + 1,
            'eksklusif' => $request->boolean('eksklusif'),
            'aktif' => true,
        ]);

        return back()->with('success', 'Keterampilan berhasil ditambahkan.');
    }

    public function update(Request $request, GcKeterampilan $keterampilan)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('gc_keterampilans', 'nama')->ignore($keterampilan->id)],
            'urutan' => ['required', 'integer', 'min:1', 'max:65000'],
        ], [
            'nama.required' => 'Nama keterampilan wajib diisi.',
            'nama.unique' => 'Keterampilan sudah ada.',
        ]);

        $keterampilan->update($data + ['aktif' => $request->boolean('aktif'), 'eksklusif' => $request->boolean('eksklusif')]);

        return back()->with('success', 'Keterampilan berhasil diperbarui.');
    }

    public function destroy(GcKeterampilan $keterampilan)
    {
        $keterampilan->delete();

        return back()->with('success', 'Keterampilan berhasil dihapus.');
    }
}
