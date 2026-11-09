<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KesanKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KesanKategoriController extends Controller
{
    public function index()
    {
        $items = KesanKategori::withCount('penerimas')->orderBy('urutan')->get();

        return view('admin.kesan.kategori.index', compact('items'));
    }

    public function store(Request $request)
    {
        $d = $this->validated($request);
        $kode = Str::slug($d['nama']);
        if (KesanKategori::where('kode', $kode)->exists()) {
            $kode .= '-' . Str::lower(Str::random(3));
        }
        KesanKategori::create($d + ['kode' => $kode]);

        return back()->with('success', 'Kategori bantuan ditambahkan.');
    }

    public function update(Request $request, KesanKategori $kategori)
    {
        $kategori->update($this->validated($request));

        return back()->with('success', 'Kategori bantuan diperbarui.');
    }

    public function destroy(KesanKategori $kategori)
    {
        if ($kategori->penerimas()->exists()) {
            return back()->with('error', 'Kategori masih dipakai calon penerima. Nonaktifkan saja.');
        }
        $kategori->delete();

        return back()->with('success', 'Kategori dihapus.');
    }

    private function validated(Request $request): array
    {
        $d = $request->validate([
            'nama' => ['required', 'string', 'max:80'],
            'singkatan' => ['nullable', 'string', 'max:40'],
            'ikon' => ['required', 'regex:/^[a-z0-9_]{2,40}$/'],
            'warna' => ['required', Rule::in(array_keys(KesanKategori::WARNA))],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], ['ikon.regex' => 'Nama ikon hanya huruf kecil, angka, dan garis bawah (contoh: home, school).']);
        $d['urutan'] = (int) ($d['urutan'] ?? 0);
        $d['aktif'] = $request->boolean('aktif');

        return $d;
    }
}
