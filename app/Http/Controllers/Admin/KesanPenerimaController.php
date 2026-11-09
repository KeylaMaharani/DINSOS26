<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KesanKategori;
use App\Models\KesanPenerima;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KesanPenerimaController extends Controller
{
    public function index(Request $request)
    {
        $q = KesanPenerima::with('kategori');

        if ($s = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('nama', 'like', "%{$s}%")->orWhere('kecamatan', 'like', "%{$s}%")->orWhere('kelurahan', 'like', "%{$s}%"));
        }
        if ($k = $request->query('kategori')) {
            $q->where('kesan_kategori_id', $k);
        }
        if ($st = $request->query('status')) {
            $q->where('status', $st);
        }

        $items = $q->latest('id')->paginate(12)->withQueryString();
        $kategoris = KesanKategori::orderBy('urutan')->get();

        return view('admin.kesan.penerima.index', compact('items', 'kategoris'));
    }

    public function create()
    {
        return view('admin.kesan.penerima.form', [
            'penerima' => null,
            'kategoris' => KesanKategori::orderBy('urutan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['foto'] = $request->hasFile('foto') ? $request->file('foto')->store('kesan/penerima', 'public') : null;
        $data['status'] = 'tersedia';
        KesanPenerima::create($data);

        return redirect()->route('kesan-admin.penerima.index')->with('success', 'Calon penerima ditambahkan.');
    }

    public function edit(KesanPenerima $penerima)
    {
        return view('admin.kesan.penerima.form', [
            'penerima' => $penerima,
            'kategoris' => KesanKategori::orderBy('urutan')->get(),
        ]);
    }

    public function update(Request $request, KesanPenerima $penerima)
    {
        $data = $this->validated($request);

        if ($request->hasFile('foto')) {
            $this->hapusFoto($penerima);
            $data['foto'] = $request->file('foto')->store('kesan/penerima', 'public');
        } elseif ($request->boolean('hapus_foto')) {
            $this->hapusFoto($penerima);
            $data['foto'] = null;
        }

        $penerima->update($data);

        return redirect()->route('kesan-admin.penerima.index')->with('success', 'Data calon penerima diperbarui.');
    }

    public function destroy(KesanPenerima $penerima)
    {
        if ($penerima->pilihans()->exists()) {
            return back()->with('error', 'Calon penerima sudah punya riwayat pilihan. Nonaktifkan saja lewat menu Ubah.');
        }
        $this->hapusFoto($penerima);
        $penerima->delete();

        return back()->with('success', 'Calon penerima dihapus.');
    }

    private function validated(Request $request): array
    {
        $d = $request->validate([
            'kesan_kategori_id' => ['required', 'exists:kesan_kategoris,id'],
            'nama' => ['required', 'string', 'max:120'],
            'no_kk' => ['nullable', 'digits:16'],
            'kecamatan' => ['required', Rule::in(KesanPenerima::KECAMATAN)],
            'kelurahan' => ['nullable', 'string', 'max:60'],
            'prioritas' => ['required', Rule::in(array_keys(KesanPenerima::PRIORITAS))],
            'jumlah_jiwa' => ['nullable', 'integer', 'min:1', 'max:50'],
            'profil' => ['nullable', 'string', 'max:2000'],
            'kebutuhan' => ['nullable', 'string', 'max:1000'],
            'estimasi_biaya' => ['nullable', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        unset($d['foto']);
        $d['estimasi_biaya'] = (int) ($d['estimasi_biaya'] ?? 0);
        $d['aktif'] = $request->boolean('aktif');

        return $d;
    }

    private function hapusFoto(KesanPenerima $p): void
    {
        if ($p->foto) {
            Storage::disk('public')->delete($p->foto);
        }
    }
}
