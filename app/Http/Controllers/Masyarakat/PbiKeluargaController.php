<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\GcSasaran;
use App\Models\PbiApbd;
use Illuminate\Http\Request;

/**
 * Langkah pemilihan sebelum form PBI APBD:
 * warga (kepala keluarga) memilih anggota keluarga mana yang didaftarkan ke Ground Check.
 */
class PbiKeluargaController extends Controller
{
    /** GET /akun-saya/pbi-apbd/{pbiApbd}/keluarga */
    public function index(Request $request, PbiApbd $pbiApbd)
    {
        $this->authorizePemilik($request, $pbiApbd);

        $anggota = $pbiApbd->anggotaKeluarga()->orderBy('id')->get();
        $niks = $anggota->map(fn ($a) => $this->digits($a->nik))->filter()->values()->all();

        $kk = $this->digits($pbiApbd->no_kk);
        $sasaran = GcSasaran::whereIn('nik', $niks)->where('no_kk', $kk);
        $terdaftarNik = (clone $sasaran)->pluck('nik')->all();
        $terkunciNik = (clone $sasaran)->whereHas('verifikasis')->pluck('nik')->all();

        $baris = $anggota->map(function ($a) use ($pbiApbd, $terdaftarNik, $terkunciNik) {
            $nik = $this->digits($a->nik);
            $kepala = $this->isKepala($pbiApbd, $a);
            $hub = trim((string) $a->hubungan_keluarga);

            return [
                'id' => $a->id,
                'nama' => $a->nama,
                'nik' => $a->nik,
                'hubungan' => $hub !== '' ? ucwords(str_replace('_', ' ', $hub)) : '-',
                'kepala' => $kepala,
                'terdaftar' => $kepala || (bool) $a->didaftarkan_gc || in_array($nik, $terdaftarNik, true),
                'terkunci' => ! $kepala && in_array($nik, $terkunciNik, true),
            ];
        });

        return view('masyarakat.pbi-apbd.keluarga', [
            'pbiApbd' => $pbiApbd,
            'baris' => $baris,
            'bisaEdit' => (bool) $pbiApbd->masyarakatBisaEdit(),
        ]);
    }

    /** POST /akun-saya/pbi-apbd/{pbiApbd}/keluarga */
    public function store(Request $request, PbiApbd $pbiApbd)
    {
        $this->authorizePemilik($request, $pbiApbd);

        if (! $pbiApbd->masyarakatBisaEdit()) {
            return redirect()->route('masyarakat.pbi-apbd.keluarga', $pbiApbd)
                ->with('error', 'Pengajuan sudah disetujui, pilihan anggota tidak dapat diubah lagi.');
        }

        $data = $request->validate([
            'anggota' => ['nullable', 'array'],
            'anggota.*' => ['integer'],
        ]);

        $pilih = collect($data['anggota'] ?? [])->map(fn ($x) => (int) $x)->all();
        $kk = $this->digits($pbiApbd->no_kk);

        foreach ($pbiApbd->anggotaKeluarga()->get() as $a) {
            // Kepala keluarga selalu ikut Ground Check
            if ($this->isKepala($pbiApbd, $a)) {
                continue;
            }

            $nik = $this->digits($a->nik);

            // Sudah diverifikasi petugas: tidak boleh diubah lagi
            $terkunci = GcSasaran::where('nik', $nik)->where('no_kk', $kk)->whereHas('verifikasis')->exists();
            if ($terkunci) {
                continue;
            }

            $mau = in_array((int) $a->id, $pilih, true);

            if ($mau !== (bool) $a->didaftarkan_gc) {
                $a->update([
                    'didaftarkan_gc' => $mau,
                    'didaftarkan_gc_at' => $mau ? now() : null,
                ]);
            }

            // Dicabut dari pilihan: hapus dari daftar sasaran selama belum disentuh petugas
            if (! $mau) {
                GcSasaran::where('nik', $nik)->where('no_kk', $kk)->doesntHave('verifikasis')->delete();
            }
        }

        // Anggota yang dipilih langsung masuk Daftar Sasaran Ground Check
        $pbiApbd->sinkronGroundCheck();

        if ($request->boolean('lanjut')) {
            return redirect()->route('masyarakat.pbi-apbd.lengkapi', $pbiApbd)
                ->with('success', 'Pilihan anggota keluarga disimpan. Silakan lengkapi data.');
        }

        return redirect()->route('masyarakat.pbi-apbd.keluarga', $pbiApbd)
            ->with('success', 'Pilihan anggota keluarga berhasil disimpan.');
    }

    private function digits($v): string
    {
        return preg_replace('/\D/', '', (string) $v);
    }

    /** Kepala keluarga: NIK sama dengan pemohon, atau hubungan "kepala keluarga". */
    private function isKepala(PbiApbd $pbi, $anggota): bool
    {
        return $this->digits($anggota->nik) === $this->digits($pbi->nik_kepala_keluarga)
            || PbiApbd::isHubunganKepala($anggota->hubungan_keluarga);
    }

    private function authorizePemilik(Request $request, PbiApbd $pbiApbd): void
    {
        $milik = $request->user()
            ->pengajuanPbiApbd()
            ->whereKey($pbiApbd->getKey())
            ->exists();

        abort_unless($milik, 403, 'Ini bukan pengajuan Anda.');
    }
}