<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\DtsenPermohonan;
use App\Models\MasyarakatDokumen;
use App\Models\PbiApbd;
use App\Support\DokumenMasyarakat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Halaman "Dokumen" masyarakat: unggah / lihat / pakai di pengajuan / hapus.
 * Tiap jenis menyimpan maksimal 2 versi terbaru. Semua query dibatasi ke milik user yang login.
 */
class MasyarakatDokumenController extends Controller
{
    /** GET /akun-saya/dokumen */
    public function index(Request $request)
    {
        $user = $request->user();

        $dokumens = $user->dokumens()
            ->orderByDesc('created_at')->orderByDesc('id')
            ->get()
            ->groupBy('jenis');

        // Tombol "Pakai di pengajuan" hanya berguna bila ada pengajuan yang masih bisa diubah
        $pbi = $user->pengajuanPbiApbd()->latest()->first();
        $dtsen = $user->pengajuanDtsen()->latest()->first();

        $bisaPakai = [
            'pbi' => (bool) ($pbi && $pbi->masyarakatBisaEdit()),
            'dtsen' => (bool) ($dtsen && $dtsen->status !== DtsenPermohonan::STATUS_SELESAI),
        ];

        return view('masyarakat.dokumen', [
            'jenisList' => MasyarakatDokumen::JENIS,
            'dokumens' => $dokumens,
            'target' => DokumenMasyarakat::TARGET,
            'bisaPakai' => $bisaPakai,
            'maksVersi' => MasyarakatDokumen::MAKS_VERSI,
            'maksMb' => MasyarakatDokumen::MAKS_KB / 1024,
        ]);
    }

    /** POST /akun-saya/dokumen */
    public function store(Request $request)
    {
        $v = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(MasyarakatDokumen::JENIS))],
            'berkas' => ['required', 'file', 'mimes:' . MasyarakatDokumen::MIMES, 'max:' . MasyarakatDokumen::MAKS_KB],
        ], [
            'berkas.required' => 'Pilih berkas yang akan diunggah.',
            'berkas.mimes' => 'Berkas harus berformat JPG, PNG, atau PDF.',
            'berkas.max' => 'Ukuran berkas maksimal ' . (MasyarakatDokumen::MAKS_KB / 1024) . ' MB.',
            'berkas.uploaded' => 'Berkas gagal diunggah. Coba lagi dengan berkas yang lebih kecil.',
        ]);

        $user = $request->user();
        $file = $request->file('berkas');
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        $path = $file->storeAs(
            "masyarakat-dokumen/{$user->id}",
            $v['jenis'] . '-' . now()->format('YmdHis') . '-' . Str::random(5) . '.' . $ext,
            'public'
        );

        $user->dokumens()->create([
            'jenis' => $v['jenis'],
            'path' => $path,
            'nama_asli' => Str::limit($file->getClientOriginalName(), 200, ''),
        ]);

        // Simpan hanya versi terbaru; yang lebih lama otomatis dihapus (berkas di pengajuan tidak terpengaruh)
        $kelebihan = $user->dokumens()->where('jenis', $v['jenis'])
            ->orderByDesc('created_at')->orderByDesc('id')
            ->skip(MasyarakatDokumen::MAKS_VERSI)->take(100)->get();

        foreach ($kelebihan as $lama) {
            Storage::disk('public')->delete($lama->path);
            $lama->delete();
        }

        // Pengajuan yang berkasnya masih kosong diisi otomatis (yang sudah terisi tidak diubah)
        $pbi = $user->pengajuanPbiApbd()->latest()->first();
        if ($pbi && $pbi->masyarakatBisaEdit()) {
            DokumenMasyarakat::isiBilaKosongPbi($pbi, $v['jenis']);
        }
        $dtsen = $user->pengajuanDtsen()->latest()->first();
        if ($dtsen && $dtsen->status !== DtsenPermohonan::STATUS_SELESAI) {
            DokumenMasyarakat::isiBilaKosongDtsen($dtsen, $v['jenis']);
        }

        return redirect()->route('masyarakat.dokumen')
            ->with('success', 'Dokumen berhasil diunggah.');
    }

    /** POST /akun-saya/dokumen/{dokumen}/pakai */
    public function pakai(Request $request, int $dokumen)
    {
        $dok = $request->user()->dokumens()->findOrFail($dokumen);

        $layanan = DokumenMasyarakat::pakai($dok);

        if (! $layanan) {
            return redirect()->route('masyarakat.dokumen')
                ->with('error', 'Tidak ada pengajuan yang bisa diperbarui. Dokumen ini dipakai di pengajuan yang masih bisa diubah.');
        }

        return redirect()->route('masyarakat.dokumen')
            ->with('success', 'Dokumen dipasang di pengajuan ' . implode(' dan ', $layanan) . '.');
    }

    /** DELETE /akun-saya/dokumen/{dokumen} */
    public function destroy(Request $request, int $dokumen)
    {
        $dok = $request->user()->dokumens()->findOrFail($dokumen);

        Storage::disk('public')->delete($dok->path);
        $dok->delete();

        return redirect()->route('masyarakat.dokumen')
            ->with('success', 'Dokumen dihapus. Berkas yang sudah melekat di pengajuan tidak ikut terhapus.');
    }
}