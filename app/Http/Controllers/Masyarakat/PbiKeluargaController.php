<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\GcSasaran;
use App\Models\PbiApbd;
use App\Models\PbiApbdLog;
use Illuminate\Http\Request;

/**
 * Halaman "Daftar Keluarga". Dua hal yang BERDIRI SENDIRI:
 *  1. Peserta PBI APBD  = kotak centang (didaftarkan_pbi). Berlaku untuk kepala keluarga maupun anggota.
 *     Pengajuan PBI APBD baru masuk antrean Kelurahan bila data sudah diisi DAN ada minimal satu peserta.
 *  2. Sasaran Ground Check = terbentuk SETELAH data diisi (kepala: saat mengirim form pengajuan;
 *     anggota: saat menyimpan form "Isi Data" A-D di PbiAnggotaController). Tidak ada centang untuk ini.
 */
class PbiKeluargaController extends Controller
{
    /** GET /akun-saya/pbi-apbd/{pbiApbd}/keluarga */
    public function index(Request $request, PbiApbd $pbiApbd)
    {
        $this->authorizePemilik($request, $pbiApbd);

        $pbiApbd->sesuaikanVersiForm();

        $semua = $pbiApbd->anggotaKeluarga()->orderBy('id')->get();

        [$daftarKepala, $anggota] = $semua->partition(fn ($a) => $this->isKepala($pbiApbd, $a));
        $kepalaModel = $daftarKepala->first();

        $kk = $this->digits($pbiApbd->no_kk);
        $niks = $anggota->map(fn ($a) => $this->digits($a->nik))->filter()->values()->all();

        // Sudah dicek petugas Ground Check -> data anggota hanya bisa dilihat
        $terkunciNik = GcSasaran::whereIn('nik', $niks)->where('no_kk', $kk)
            ->whereHas('verifikasis')->pluck('nik')->all();

        $baris = $anggota->map(function ($a) use ($terkunciNik, $pbiApbd) {
            $hub = trim((string) $a->hubungan_keluarga);

            return [
                'id' => $a->id,
                'nama' => $a->nama,
                'nik' => $a->nik,
                'hubungan' => $hub !== '' ? ucwords(str_replace('_', ' ', $hub)) : '-',
                'peserta_pbi' => (bool) $a->didaftarkan_pbi,
                'masuk_gc' => (bool) $a->didaftarkan_gc,
                'terkunci' => in_array($this->digits($a->nik), $terkunciNik, true),
                'terisi' => $a->anggotaJawabans()->whereNotNull('jawaban')->exists(),
                'isi_url' => route('masyarakat.pbi-apbd.anggota', [$pbiApbd, $a->id]),
            ];
        })->values();

        $hubKepala = trim((string) ($kepalaModel->hubungan_keluarga ?? 'kepala_keluarga'));

        $pbiApbd->setRelation('anggotaKeluarga', $semua);

        $kepala = [
            'nama' => $kepalaModel->nama ?? $pbiApbd->nama_kepala_keluarga,
            'nik' => $kepalaModel->nik ?? $pbiApbd->nik_kepala_keluarga,
            'hubungan' => $hubKepala !== '' ? ucwords(str_replace('_', ' ', $hubKepala)) : 'Kepala Keluarga',
            'ada_baris' => (bool) $kepalaModel,
            'peserta_pbi' => (bool) ($kepalaModel->didaftarkan_pbi ?? false),
            // Data kepala keluarga sudah diisi (menekan "Simpan Data") -> masuk Ground Check
            'sudah_mengisi' => $pbiApbd->kepalaSudahMengajukan(),
            // PBI APBD benar-benar sudah diajukan ke petugas (data terisi + ada peserta)
            'diajukan_pbi' => $pbiApbd->sudahDiajukanPbi(),
            'jumlah_peserta' => $pbiApbd->daftarPeserta()->count(),
            'status_label' => $pbiApbd->statusLabel(),
            'bisa_isi' => $pbiApbd->kepalaBisaMengisiForm(),
            'dikembalikan' => (bool) ($pbiApbd->dikembalikan_ke_masyarakat || $pbiApbd->status === 'ditolak'),
        ];

        return view('masyarakat.pbi-apbd.keluarga', [
            'pbiApbd' => $pbiApbd,
            'kepala' => $kepala,
            'baris' => $baris,
            'bisaEdit' => (bool) $pbiApbd->masyarakatBisaEdit(),
            'bisaPilih' => (bool) $pbiApbd->pesertaBisaDiubah(),
        ]);
    }

    /** POST /akun-saya/pbi-apbd/{pbiApbd}/keluarga  — menyimpan pilihan peserta PBI APBD. */
    public function store(Request $request, PbiApbd $pbiApbd)
    {
        $this->authorizePemilik($request, $pbiApbd);

        if (! $pbiApbd->masyarakatBisaEdit()) {
            return redirect()->route('masyarakat.pbi-apbd.keluarga', $pbiApbd)
                ->with('error', 'Pengajuan sudah disetujui, pilihan peserta tidak dapat diubah lagi.');
        }

        $data = $request->validate([
            'anggota' => ['nullable', 'array'],
            'anggota.*' => ['integer'],
            'isi' => ['nullable', 'integer'],
        ]);

        $pilih = collect($data['anggota'] ?? [])->map(fn ($x) => (int) $x)->all();
        $isiId = (int) ($data['isi'] ?? 0);
        $lanjut = $request->boolean('lanjut');

        $bolehUbahPeserta = $pbiApbd->pesertaBisaDiubah();

        // Berkas sudah diteruskan petugas: pilihan peserta terkunci. Tombol "Isi Data"/"Lanjut" tetap jalan.
        if (! $bolehUbahPeserta && $isiId === 0 && ! $lanjut) {
            return redirect()->route('masyarakat.pbi-apbd.keluarga', $pbiApbd)
                ->with('error', 'Pengajuan sedang diproses petugas, pilihan peserta tidak dapat diubah.');
        }

        $pesan = 'Pilihan peserta PBI APBD berhasil disimpan.';

        if ($bolehUbahPeserta) {
            $sebelum = $pbiApbd->anggotaKeluarga()->where('didaftarkan_pbi', true)->pluck('id')->map(fn ($i) => (int) $i)->sort()->values()->all();
            $sudahDiajukanSebelumnya = $pbiApbd->sudahDiajukanPbi();

            foreach ($pbiApbd->anggotaKeluarga()->get() as $a) {
                if ($this->isKepala($pbiApbd, $a)) {
                    // Hanya diproses bila kotak kepala memang tampil di form
                    $mau = $request->has('kepala_pbi_form') ? $request->boolean('kepala_pbi') : (bool) $a->didaftarkan_pbi;
                } else {
                    $mau = in_array((int) $a->id, $pilih, true);
                }

                if ($mau !== (bool) $a->didaftarkan_pbi) {
                    $a->update(['didaftarkan_pbi' => $mau]);
                }
            }

            $pbiApbd->unsetRelation('anggotaKeluarga');
            $peserta = $pbiApbd->anggotaKeluarga()->where('didaftarkan_pbi', true)->orderBy('id')->get();
            $sesudah = $peserta->pluck('id')->map(fn ($i) => (int) $i)->sort()->values()->all();

            if ($sebelum !== $sesudah) {
                $sekarangDiajukan = $pbiApbd->sudahDiajukanPbi();

                PbiApbdLog::create([
                    'pbi_apbd_id' => $pbiApbd->id,
                    'user_id' => $request->user()->id,
                    'username' => $request->user()->name,
                    'role_name' => 'Masyarakat',
                    'task_name' => (! $sudahDiajukanSebelumnya && $sekarangDiajukan)
                        ? 'Masyarakat mengajukan PBI APBD'
                        : 'Masyarakat mengubah peserta PBI APBD',
                    'catatan' => $peserta->isEmpty() ? 'Tidak ada peserta' : 'Peserta: ' . $peserta->pluck('nama')->implode(', '),
                ]);

                if (! $sudahDiajukanSebelumnya && $sekarangDiajukan) {
                    $pesan = 'Pilihan peserta disimpan. Pengajuan PBI APBD diteruskan ke Kelurahan.';
                } elseif ($peserta->isNotEmpty() && ! $pbiApbd->kepalaSudahMengajukan()) {
                    $pesan = 'Pilihan peserta disimpan. Lengkapi data kepala keluarga agar pengajuan dikirim ke Kelurahan.';
                } elseif ($peserta->isEmpty() && $pbiApbd->kepalaSudahMengajukan()) {
                    $pesan = 'Pilihan peserta disimpan. Belum ada peserta, pengajuan PBI APBD tidak diteruskan ke Kelurahan.';
                }
            }
        }

        // "Isi Data" pada satu anggota: pilihan di atas sudah tersimpan, lanjut ke form data anggota tersebut
        if ($isiId > 0) {
            $target = $pbiApbd->anggotaKeluarga()->find($isiId);
            if ($target && ! $this->isKepala($pbiApbd, $target)) {
                return redirect()->route('masyarakat.pbi-apbd.anggota', [$pbiApbd, $target->id]);
            }
        }

        if ($lanjut && $pbiApbd->kepalaBisaMengisiForm()) {
            return redirect()->route('masyarakat.pbi-apbd.lengkapi', $pbiApbd)
                ->with('success', 'Pilihan peserta disimpan. Silakan lengkapi data.');
        }

        return redirect()->route('masyarakat.pbi-apbd.keluarga', $pbiApbd)
            ->with('success', $pesan);
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