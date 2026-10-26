<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbiApbd;
use App\Models\PbiApbdLog;
use App\Support\PbiGcForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Halaman Petugas Kelurahan untuk modul PBI APBD.
 * Alur: Masyarakat isi data -> Kelurahan verifikasi + upload lampiran -> Operator Dinsos -> Kabid -> Kadis.
 * Kelurahan HANYA melihat jawaban (tanpa indeks/bobot/skor).
 * Daftar pertanyaan yang dihitung = yang tampil di form masyarakat (Master Kriteria Ground Check).
 */
class PbiKelurahanController extends Controller
{
    /** field => [label, wajib untuk validasi, mimes] */
    public const LAMPIRAN = [
        'scan_ktp' => ['Scan KTP', true, 'jpg,jpeg,png,pdf'],
        'scan_kk' => ['Scan Kartu Keluarga', true, 'jpg,jpeg,png,pdf'],
        'foto_rumah' => ['Foto Rumah Pemohon', true, 'jpg,jpeg,png'],
        'foto_kamar_mandi' => ['Foto Kamar Mandi', true, 'jpg,jpeg,png'],
        'foto_selfie_ktp' => ['Foto Selfie dengan KTP', true, 'jpg,jpeg,png'],
        'surat_rawat_inap' => ['Surat Ket. Rawat Inap (jika ada)', false, 'jpg,jpeg,png,pdf'],
        'screenshot_pembaharuan_desil' => ['Screenshot Pembaharuan Desil', false, 'jpg,jpeg,png'],
        'screenshot_dtsen' => ['Screenshot DTSEN', false, 'jpg,jpeg,png'],
        'scan_surat_pengantar' => ['Scan Surat Pengantar Kelurahan', false, 'jpg,jpeg,png,pdf'],
    ];

    public function index(Request $request)
    {
        $user = Auth::user()->loadMissing('role');
        $tab = $request->query('tab', 'proses') === 'riwayat' ? 'riwayat' : 'proses';

        // Hitungan di daftar = isian wajib yang selalu berlaku (alias tetap jawabans_count agar view tidak berubah).
        $idTetap = PbiGcForm::idTetap();

        $query = PbiApbd::query()->untukKelurahan($user)->withCount([
            'gcJawabans as jawabans_count' => fn ($q) => $q->whereNotNull('jawaban')->whereIn('parameter_id', $idTetap),
        ]);

        $tab === 'proses'
            ? $query->where('status', 'kelurahan')
            : $query->where('status', '!=', 'kelurahan');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_registrasi', 'like', "%{$s}%")
                    ->orWhere('nik_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_kk', 'like', "%{$s}%");
            });
        }

        $ajuan = $query->latest()->paginate(10)->withQueryString();
        $totalParameter = count($idTetap);
        $wilayahBelumDiatur = ! $user->role?->isSuperAdmin() && blank($user->kelurahan);

        return view('admin.pbi-kelurahan.index', compact('ajuan', 'tab', 'totalParameter', 'wilayahBelumDiatur', 'user'));
    }

    public function show(PbiApbd $pbiApbd)
    {
        $this->pastikanBerwenang($pbiApbd);

        $pbiApbd->load(['anggotaKeluarga', 'logs']);
        $kelengkapan = PbiGcForm::kelengkapan($pbiApbd);

        return view('admin.pbi-kelurahan.show', [
            'data' => $pbiApbd,
            'lampiran' => self::LAMPIRAN,
            'totalParameter' => $kelengkapan['total'],
            'terjawab' => $kelengkapan['terjawab'],
            'gcJawabans' => $pbiApbd->gcJawabans()->get(),
        ]);
    }

    public function aksi(Request $request, PbiApbd $pbiApbd)
    {
        $this->pastikanBerwenang($pbiApbd);

        if ($pbiApbd->status !== 'kelurahan') {
            return redirect()->route('pbi-kelurahan.show', $pbiApbd)
                ->with('error', 'Berkas ini sudah tidak berada di Kelurahan.');
        }

        $rules = [
            'aksi' => ['required', Rule::in(['simpan', 'validasi', 'kembalikan', 'tolak'])],
            'kesimpulan_rekomendasi' => ['nullable', 'string', 'max:2000'],
            'catatan' => [
                Rule::requiredIf(in_array($request->input('aksi'), ['kembalikan', 'tolak'], true)),
                'nullable', 'string', 'max:2000',
            ],
        ];
        foreach (self::LAMPIRAN as $field => [$label, $wajib, $mimes]) {
            $rules[$field] = ['nullable', 'file', "mimes:{$mimes}", 'max:4096'];
        }

        $request->validate($rules, [
            'catatan.required' => 'Catatan wajib diisi untuk aksi ini.',
            'aksi.in' => 'Aksi tidak dikenali.',
            '*.mimes' => 'Format file tidak didukung.',
            '*.max' => 'Ukuran file maksimal 4 MB.',
        ]);

        $user = Auth::user();
        $aksi = $request->input('aksi');
        $catatan = $request->input('catatan');

        // ---- Kembalikan ke masyarakat / Tolak: tidak memproses file ----
        if ($aksi === 'kembalikan') {
            $pbiApbd->update([
                'dikembalikan_ke_masyarakat' => true,
                'catatan_kelurahan' => $catatan,
                'sudah_diverifikasi_kelurahan' => false,
            ]);
            $this->catatLog($pbiApbd, 'Mengembalikan ke Masyarakat untuk perbaikan data', $catatan);

            return redirect()->route('pbi-kelurahan.index')->with('success', 'Berkas dikembalikan ke masyarakat untuk diperbaiki.');
        }

        if ($aksi === 'tolak') {
            $pbiApbd->update(['status' => 'ditolak', 'catatan_kelurahan' => $catatan]);
            $this->catatLog($pbiApbd, 'Kelurahan menolak permohonan', $catatan);

            return redirect()->route('pbi-kelurahan.index')->with('success', 'Permohonan ditolak.');
        }

        // ---- Simpan / Validasi: simpan lampiran + kesimpulan dulu ----
        $update = [];
        foreach (array_keys(self::LAMPIRAN) as $field) {
            if ($request->hasFile($field)) {
                if ($pbiApbd->{$field}) {
                    Storage::disk('public')->delete($pbiApbd->{$field});
                }
                $update[$field] = $request->file($field)->store('pbi-apbd/lampiran', 'public');
            }
        }
        if ($request->filled('kesimpulan_rekomendasi')) {
            $update['kesimpulan_rekomendasi'] = $request->input('kesimpulan_rekomendasi');
        }
        if ($update) {
            $pbiApbd->update($update);
        }

        if ($aksi === 'simpan') {
            $this->catatLog($pbiApbd, 'Kelurahan menyimpan hasil verifikasi lapangan', $catatan);

            return redirect()->route('pbi-kelurahan.show', $pbiApbd)->with('success', 'Hasil verifikasi & lampiran tersimpan.');
        }

        // ---- Validasi & teruskan: cek kelengkapan ----
        $masalah = [];

        $kelengkapan = PbiGcForm::kelengkapan($pbiApbd);
        if ($kelengkapan['total'] === 0 || $kelengkapan['terjawab'] < $kelengkapan['total']) {
            $masalah[] = "Masyarakat belum menjawab semua parameter ({$kelengkapan['terjawab']}/{$kelengkapan['total']}). Gunakan \"Kembalikan ke Masyarakat\" bila perlu.";
        }

        $kurang = [];
        foreach (self::LAMPIRAN as $field => [$label, $wajib]) {
            if ($wajib && ! $pbiApbd->{$field}) {
                $kurang[] = $label;
            }
        }
        if ($kurang) {
            $masalah[] = 'Lampiran wajib belum lengkap: ' . implode(', ', $kurang) . '.';
        }

        if (blank($pbiApbd->kesimpulan_rekomendasi)) {
            $masalah[] = 'Kesimpulan/rekomendasi kelurahan wajib diisi.';
        }

        if ($masalah) {
            return redirect()->route('pbi-kelurahan.show', $pbiApbd)
                ->withInput()
                ->withErrors(['validasi' => $masalah]);
        }

        $pbiApbd->update([
            'status' => $pbiApbd->nextStageKey(), // kelurahan -> operator_dinsos
            'sudah_diverifikasi_kelurahan' => true,
            'dikembalikan_ke_masyarakat' => false,
            'divalidasi_kelurahan_at' => now(),
            'divalidasi_kelurahan_oleh' => $user->id,
        ]);
        $this->catatLog($pbiApbd, 'Verifikasi kelurahan selesai, diteruskan ke Operator Dinsos', $pbiApbd->kesimpulan_rekomendasi);

        return redirect()->route('pbi-kelurahan.index')->with('success', 'Berkas divalidasi dan diteruskan ke Operator Dinsos.');
    }

    private function pastikanBerwenang(PbiApbd $pbiApbd): void
    {
        $user = Auth::user()->loadMissing('role');

        abort_unless(
            PbiApbd::untukKelurahan($user)->whereKey($pbiApbd->getKey())->exists(),
            403,
            'Pengajuan ini bukan dari wilayah kelurahan Anda.'
        );
    }

    private function catatLog(PbiApbd $pbiApbd, string $task, ?string $catatan): void
    {
        $user = Auth::user();

        PbiApbdLog::create([
            'pbi_apbd_id' => $pbiApbd->id,
            'user_id' => $user->id,
            'username' => $user->name,
            'role_name' => $user->role->name ?? '-',
            'task_name' => $task,
            'catatan' => $catatan,
        ]);
    }
}
