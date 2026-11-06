<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NgopiPagiKonsultasi as K;
use App\Models\NgopiPagiSpesialisasi as S;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NgopiPagiSpesialisasiController extends Controller
{
    public function index()
    {
        return view('admin.ngopi-pagi.spesialisasi.index', [
            'items' => S::urut()->withCount('konselors')->get(),
            'hitung' => K::selectRaw('tahap, count(*) as total')->groupBy('tahap')->pluck('total', 'tahap'),
        ]);
    }

    public function store(Request $request)
    {
        $d = $this->validasi($request);
        $d['slug'] = $this->slugUnik($d['nama']);
        S::create($d);

        return back()->with('success', 'Spesialisasi ditambahkan.');
    }

    public function update(Request $request, S $spesialisasi)
    {
        $spesialisasi->update($this->validasi($request)); // slug tetap agar tautan lama tidak putus

        return back()->with('success', 'Spesialisasi diperbarui.');
    }

    public function destroy(S $spesialisasi)
    {
        $spesialisasi->delete(); // konsultasi lama tetap ada (spesialisasi_id jadi null)

        return back()->with('success', 'Spesialisasi dihapus.');
    }

    private function validasi(Request $r): array
    {
        $d = $r->validate([
            'nama' => ['required', 'string', 'max:80'],
            'deskripsi' => ['nullable', 'string', 'max:200'],
            'ikon' => ['required', Rule::in(S::IKON)],
            'warna' => ['required', Rule::in(array_keys(S::WARNA))],
            'kategori_awal' => ['nullable', Rule::in(['A', 'B', 'C'])],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
        $d['urutan'] = $d['urutan'] ?? 0;
        $d['kategori_awal'] = $d['kategori_awal'] ?: null;
        $d['aktif'] = $r->boolean('aktif');

        return $d;
    }

    private function slugUnik(string $nama): string
    {
        $dasar = Str::slug($nama) ?: 'spesialisasi';
        $slug = $dasar;
        for ($i = 2; S::where('slug', $slug)->exists(); $i++) {
            $slug = "{$dasar}-{$i}";
        }

        return $slug;
    }
}
