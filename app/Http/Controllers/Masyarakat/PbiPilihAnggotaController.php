<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\PbiApbd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PbiPilihAnggotaController extends Controller
{
    /** Anggota selain kepala keluarga (kepala selalu ikut, tidak perlu dicentang). */
    private function anggotaQuery(PbiApbd $pbiApbd)
    {
        return $pbiApbd->anggotaKeluarga()
            ->where('nik', '!=', $pbiApbd->nik_kepala_keluarga)
            ->where(function ($q) {
                $q->whereNull('hubungan_keluarga')
                  ->orWhereRaw("LOWER(REPLACE(hubungan_keluarga, '_', ' ')) NOT LIKE '%kepala%'");
            });
    }

    private function otorisasi(PbiApbd $pbiApbd): void
    {
        // ASUMSI: pemilik pengajuan = akun dengan NIK yang sama dengan kepala keluarga.
        // Ganti dengan pengecekan yang sama persis seperti di PbiKeluargaController.
        abort_unless(Auth::user()->nik === $pbiApbd->nik_kepala_keluarga, 403);
    }

    public function index(PbiApbd $pbiApbd)
    {
        $this->otorisasi($pbiApbd);

        $baris = $this->anggotaQuery($pbiApbd)->orderBy('id')->get();
        $bisaEdit = $pbiApbd->status !== 'disetujui';

        return view('masyarakat.pbi-apbd.pilih-anggota', compact('pbiApbd', 'baris', 'bisaEdit'));
    }

    public function store(Request $request, PbiApbd $pbiApbd)
    {
        $this->otorisasi($pbiApbd);
        abort_if($pbiApbd->status === 'disetujui', 403);

        $request->validate([
            'anggota'   => ['nullable', 'array'],
            'anggota.*' => ['integer'],
        ]);

        $semua = $this->anggotaQuery($pbiApbd)->pluck('id');
        $dipilih = collect($request->input('anggota', []))->map(fn ($v) => (int) $v)->intersect($semua)->values();

        DB::transaction(function () use ($pbiApbd, $semua, $dipilih) {
            $pbiApbd->anggotaKeluarga()->whereIn('id', $semua)->update([
                'didaftarkan_pbi' => false,
                'didaftarkan_gc' => false,
                'didaftarkan_gc_at' => null,
            ]);

            if ($dipilih->isNotEmpty()) {
                $pbiApbd->anggotaKeluarga()->whereIn('id', $dipilih)->update([
                    'didaftarkan_pbi' => true,
                    'didaftarkan_gc' => true,       // hapus baris ini bila tidak ingin ikut Ground Check
                    'didaftarkan_gc_at' => now(),
                ]);
            }
        });

        return redirect()->route('masyarakat.pbi-apbd.lengkapi', $pbiApbd);
    }
}