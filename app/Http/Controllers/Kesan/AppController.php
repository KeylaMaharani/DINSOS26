<?php

namespace App\Http\Controllers\Kesan;

use App\Http\Controllers\Controller;
use App\Models\KesanKategori;
use App\Models\KesanPenerima;
use App\Models\KesanPilihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Alur Filantropi: beranda -> penerima -> detail -> pilih -> konfirmasi -> riwayat/tracking. */
class AppController extends Controller
{
    // ---- 2. Pilih jenis bantuan (beranda) ----
    public function beranda(Request $request)
    {
        $uid = $request->user()->id;

        $kategoris = KesanKategori::aktif()
            ->withCount(['penerimas as tersedia_count' => fn ($q) => $q->tersedia()])
            ->get();

        $stat = [
            'tersedia' => KesanPenerima::tersedia()->count(),
            'berjalan' => KesanPilihan::where('user_id', $uid)->berjalan()->count(),
            'selesai'  => KesanPilihan::where('user_id', $uid)->where('status', 'selesai')->count(),
        ];

        $terbaru = KesanPilihan::with('penerima.kategori')
            ->where('user_id', $uid)->where('status', '!=', 'keranjang')
            ->latest('updated_at')->limit(3)->get();

        return view('kesan.app.beranda', compact('kategoris', 'stat', 'terbaru'));
    }

    // ---- 3. Daftar calon penerima (filter wilayah / prioritas / kategori) ----
    public function penerima(Request $request)
    {
        $q = KesanPenerima::with('kategori')->tersedia();

        if ($s = trim((string) $request->query('q'))) {
            $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', "%{$s}%")
                    ->orWhere('kecamatan', 'like', "%{$s}%")
                    ->orWhere('kelurahan', 'like', "%{$s}%")
                    ->orWhereHas('kategori', fn ($k) => $k->where('nama', 'like', "%{$s}%"));
            });
        }
        if ($kode = $request->query('kategori')) {
            $q->whereHas('kategori', fn ($k) => $k->where('kode', $kode));
        }
        if ($kec = $request->query('kecamatan')) {
            $q->where('kecamatan', $kec);
        }
        if ($pr = $request->query('prioritas')) {
            $q->where('prioritas', $pr);
        }

        $items = $q->orderByRaw(KesanPenerima::URUT_PRIORITAS)->latest('id')->paginate(8)->withQueryString();
        $kategoris = KesanKategori::aktif()->get();
        $dipilihIds = $this->keranjangIds($request);

        return view('kesan.app.penerima', compact('items', 'kategoris', 'dipilihIds'));
    }

    // ---- 4. Detail calon penerima ----
    public function detail(Request $request, KesanPenerima $penerima)
    {
        abort_unless($penerima->aktif, 404);
        $penerima->load('kategori');

        $diKeranjang = in_array($penerima->id, $this->keranjangIds($request), true);
        $milikSaya = KesanPilihan::where('user_id', $request->user()->id)
            ->where('kesan_penerima_id', $penerima->id)
            ->whereIn('status', ['dipilih', 'penyaluran', 'selesai'])
            ->latest('id')->first();

        return view('kesan.app.detail', compact('penerima', 'diKeranjang', 'milikSaya'));
    }

    // ---- 4 -> 5. Pilih penerima (masuk ringkasan) ----
    public function pilih(Request $request, KesanPenerima $penerima)
    {
        if (! $penerima->aktif || $penerima->status !== 'tersedia') {
            return back()->with('error', 'Calon penerima ini sudah tidak tersedia untuk dipilih.');
        }

        KesanPilihan::firstOrCreate([
            'user_id' => $request->user()->id,
            'kesan_penerima_id' => $penerima->id,
            'status' => 'keranjang',
        ]);

        return redirect()->route('kesan.app.pilihan');
    }

    // ---- 5. Konfirmasi pilihan (ringkasan) ----
    public function pilihan(Request $request)
    {
        $items = KesanPilihan::with('penerima.kategori')
            ->where('user_id', $request->user()->id)->where('status', 'keranjang')
            ->latest('id')->get();

        $ringkas = $items->groupBy(fn ($i) => $i->penerima->kesan_kategori_id)
            ->map(fn ($g) => ['kategori' => $g->first()->penerima->kategori, 'jumlah' => $g->count()])
            ->values();

        $total = $items->sum(fn ($i) => $i->penerima->estimasi_biaya);

        return view('kesan.app.pilihan', compact('items', 'ringkas', 'total'));
    }

    public function hapus(Request $request, KesanPilihan $pilihan)
    {
        abort_unless($pilihan->user_id === $request->user()->id && $pilihan->status === 'keranjang', 404);
        $pilihan->delete();

        return back()->with('success', 'Calon penerima dikeluarkan dari pilihan Anda.');
    }

    public function konfirmasi(Request $request)
    {
        $uid = $request->user()->id;
        $berhasil = 0;
        $gagal = [];

        DB::transaction(function () use ($uid, &$berhasil, &$gagal) {
            $items = KesanPilihan::where('user_id', $uid)->where('status', 'keranjang')->lockForUpdate()->get();

            foreach ($items as $it) {
                $p = KesanPenerima::whereKey($it->kesan_penerima_id)->lockForUpdate()->first();

                // Dipilih Filantropi lain lebih dulu / dinonaktifkan petugas
                if (! $p || ! $p->aktif || $p->status !== 'tersedia') {
                    $gagal[] = $p->nama ?? 'Penerima';
                    $it->delete();
                    continue;
                }

                $it->forceFill([
                    'status' => 'dipilih',
                    'dipilih_at' => now(),
                    'kode' => 'KSN-' . now()->format('ymd') . '-' . str_pad((string) $it->id, 5, '0', STR_PAD_LEFT),
                ])->save();
                $p->update(['status' => 'dipilih']);
                $berhasil++;
            }
        });

        if ($berhasil === 0) {
            return redirect()->route('kesan.app.penerima')
                ->with('error', $gagal ? 'Semua pilihan sudah diambil Filantropi lain: ' . implode(', ', $gagal) . '.' : 'Belum ada penerima yang dipilih.');
        }

        $redirect = redirect()->route('kesan.app.riwayat')
            ->with('success', "{$berhasil} calon penerima dilanjutkan ke tahap penyaluran. Petugas akan memverifikasi pilihan Anda.");

        return $gagal ? $redirect->with('peringatan', 'Tidak dapat dilanjutkan (sudah diambil pihak lain): ' . implode(', ', $gagal) . '.') : $redirect;
    }

    // ---- 6. Pantau penyaluran ----
    public function riwayat(Request $request)
    {
        $tab = $request->query('s', 'semua');

        $q = KesanPilihan::with('penerima.kategori')
            ->where('user_id', $request->user()->id)->where('status', '!=', 'keranjang');

        if ($tab === 'berjalan') {
            $q->berjalan();
        } elseif ($tab === 'selesai') {
            $q->where('status', 'selesai');
        }

        $items = $q->latest('id')->paginate(10)->withQueryString();

        return view('kesan.app.riwayat', compact('items', 'tab'));
    }

    public function tracking(Request $request, KesanPilihan $pilihan)
    {
        abort_unless($pilihan->user_id === $request->user()->id, 404);
        if ($pilihan->status === 'keranjang') {
            return redirect()->route('kesan.app.pilihan');
        }
        $pilihan->load('penerima.kategori', 'verifikator');

        return view('kesan.app.tracking', compact('pilihan'));
    }

    public function batal(Request $request, KesanPilihan $pilihan)
    {
        abort_unless($pilihan->user_id === $request->user()->id, 404);
        if ($pilihan->status !== 'dipilih') {
            return back()->with('error', 'Pilihan yang sudah diverifikasi tidak dapat dibatalkan sendiri. Hubungi petugas.');
        }

        DB::transaction(function () use ($pilihan) {
            $pilihan->update(['status' => 'batal']);
            $pilihan->penerima()->where('status', 'dipilih')->update(['status' => 'tersedia']);
        });

        return redirect()->route('kesan.app.riwayat')->with('success', 'Pilihan dibatalkan. Calon penerima kembali tersedia.');
    }

    private function keranjangIds(Request $request): array
    {
        return KesanPilihan::where('user_id', $request->user()->id)->where('status', 'keranjang')
            ->pluck('kesan_penerima_id')->all();
    }
}
