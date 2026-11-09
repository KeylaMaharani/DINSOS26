<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KesanPilihan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Petugas Dinsos: verifikasi pilihan Filantropi -> penyaluran -> selesai (laporan hasil). */
class KesanPilihanController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('s', 'dipilih');

        $q = KesanPilihan::with(['user', 'penerima.kategori'])->where('status', '!=', 'keranjang');

        if ($tab === 'lainnya') {
            $q->whereIn('status', ['ditolak', 'batal']);
        } elseif ($tab !== 'semua') {
            $q->where('status', $tab);
        }

        if ($s = trim((string) $request->query('q'))) {
            $q->where(function ($w) use ($s) {
                $w->where('kode', 'like', "%{$s}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('penerima', fn ($p) => $p->where('nama', 'like', "%{$s}%"));
            });
        }

        $items = $q->latest('id')->paginate(12)->withQueryString();

        $hitung = KesanPilihan::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
        $stat = [
            'dipilih'    => (int) ($hitung['dipilih'] ?? 0),
            'penyaluran' => (int) ($hitung['penyaluran'] ?? 0),
            'selesai'    => (int) ($hitung['selesai'] ?? 0),
            'lainnya'    => (int) (($hitung['ditolak'] ?? 0) + ($hitung['batal'] ?? 0)),
            'filantropi' => User::whereHas('role', fn ($r) => $r->where('slug', 'filantropi'))->count(),
        ];

        return view('admin.kesan.pilihan.index', compact('items', 'tab', 'stat'));
    }

    public function show(KesanPilihan $pilihan)
    {
        abort_if($pilihan->status === 'keranjang', 404);
        $pilihan->load(['user', 'penerima.kategori', 'verifikator']);

        return view('admin.kesan.pilihan.show', compact('pilihan'));
    }

    public function aksi(Request $request, KesanPilihan $pilihan)
    {
        $aksi = $request->validate(['aksi' => ['required', 'in:verifikasi,tolak,selesai']])['aksi'];

        if ($aksi === 'verifikasi') {
            if ($pilihan->status !== 'dipilih') {
                return back()->with('error', 'Pilihan ini tidak sedang menunggu verifikasi.');
            }
            $d = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);
            $pilihan->update([
                'status' => 'penyaluran', 'verifikasi_at' => now(),
                'verifikator_id' => $request->user()->id, 'catatan' => $d['catatan'] ?? null,
            ]);

            return back()->with('success', 'Pilihan diverifikasi. Status menjadi Dalam Proses Penyaluran.');
        }

        if ($aksi === 'tolak') {
            if ($pilihan->status !== 'dipilih') {
                return back()->with('error', 'Hanya pilihan yang menunggu verifikasi yang dapat ditolak.');
            }
            $d = $request->validate(['catatan' => ['required', 'string', 'max:1000']], ['catatan.required' => 'Alasan penolakan wajib diisi.']);
            DB::transaction(function () use ($pilihan, $d, $request) {
                $pilihan->update(['status' => 'ditolak', 'verifikator_id' => $request->user()->id, 'catatan' => $d['catatan']]);
                $pilihan->penerima()->update(['status' => 'tersedia']);
            });

            return back()->with('success', 'Pilihan ditolak. Calon penerima kembali tersedia.');
        }

        // selesai
        if ($pilihan->status !== 'penyaluran') {
            return back()->with('error', 'Pilihan harus berstatus Dalam Proses Penyaluran sebelum diselesaikan.');
        }
        $d = $request->validate([
            'laporan' => ['required', 'string', 'max:3000'],
            'bukti' => ['nullable', 'image', 'max:4096'],
        ], ['laporan.required' => 'Laporan hasil penyaluran wajib diisi.']);

        DB::transaction(function () use ($pilihan, $d, $request) {
            $pilihan->update([
                'status' => 'selesai', 'selesai_at' => now(), 'laporan' => $d['laporan'],
                'bukti' => $request->hasFile('bukti') ? $request->file('bukti')->store('kesan/bukti', 'public') : $pilihan->bukti,
            ]);
            $pilihan->penerima()->update(['status' => 'selesai']);
        });

        return back()->with('success', 'Penyaluran ditandai selesai. Filantropi dapat melihat laporan hasil.');
    }
}
