<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbiApbn;
use App\Models\PbiApbnLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PbiApbnController extends Controller
{
    /**
     * ================= AJUAN =================
     * List permohonan yang masih berjalan (belum final) + peta lokasi.
     */
    public function ajuanIndex(Request $request)
    {
        $query = PbiApbn::query()->whereNotIn('status', ['disetujui', 'ditolak']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_registrasi', 'like', "%{$s}%")
                    ->orWhere('nik_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_kk', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ajuan = $query->latest()->paginate(10)->withQueryString();

        return view('admin.pbi-apbn.ajuan.index', compact('ajuan'));
        // $markers sudah tidak dikirim ke view
    }

    public function ajuanShow(PbiApbn $pbiApbn)
    {
        $pbiApbn->load(['anggotaKeluarga', 'logs.user', 'diagnosaLogs.user']);

        $canAct = $this->userCanActOn($pbiApbn);
        $faskesOptions = $this->faskesOptions($pbiApbn);

        return view('admin.pbi-apbn.ajuan.show', [
            'data' => $pbiApbn,
            'canAct' => $canAct,
            'faskesOptions' => $faskesOptions,
        ]);
    }

    /**
     * Update Nama Faskes (field overwrite, bukan riwayat).
     * Diagnosa TIDAK lagi ditangani di sini — lihat ajuanTambahDiagnosa().
     */
    public function ajuanUpdateTambahan(Request $request, PbiApbn $pbiApbn)
    {
        if (!$this->userCanActOn($pbiApbn)) {
            return back()->with('error', 'Anda tidak berwenang mengedit data ini.');
        }

        $validated = $request->validate([
            'nama_faskes' => ['nullable', 'string', 'max:150'],
        ]);

        $pbiApbn->update($validated);

        return back()->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Tambah entri baru ke riwayat Diagnosa (bisa diisi berulang kali,
     * tidak menghapus/menimpa entri sebelumnya).
     */
    public function ajuanTambahDiagnosa(Request $request, PbiApbn $pbiApbn)
    {
        if (!$this->userCanActOn($pbiApbn)) {
            return back()->with('error', 'Anda tidak berwenang mengedit data ini.');
        }

        $validated = $request->validate([
            'diagnosa' => ['required', 'string', 'max:2000'],
        ], [
            'diagnosa.required' => 'Diagnosa tidak boleh kosong.',
        ]);

        $user = Auth::user();

        $pbiApbn->diagnosaLogs()->create([
            'user_id' => $user->id,
            'username' => $user->name,
            'role_name' => $user->role->name ?? '-',
            'diagnosa' => $validated['diagnosa'],
        ]);

        // Simpan juga nilai terakhir di kolom utama untuk tampilan ringkas/cepat
        $pbiApbn->update(['diagnosa' => $validated['diagnosa']]);

        return back()->with('success', 'Diagnosa baru berhasil ditambahkan.');
    }

    /**
     * Daftar opsi Nama Faskes berdasarkan kecamatan pada data permohonan.
     * Sementara masih hardcoded di config/faskes.php (belum ada tabel master).
     */
    private function faskesOptions(PbiApbn $pbiApbn): array
    {
        $master = config('faskes.puskesmas', []);

        return $master[$pbiApbn->kecamatan] ?? [];
    }

    /**
     * Aksi: simpan | next | back | reject
     */
    public function ajuanAksi(Request $request, PbiApbn $pbiApbn)
    {
        $request->validate([
            'aksi' => ['required', Rule::in(['simpan', 'next', 'back', 'reject'])],
            'catatan' => ['required', 'string', 'max:2000'],
        ]);

        if ($pbiApbn->isFinal()) {
            return back()->with('error', 'Permohonan ini sudah final (disetujui/ditolak), tidak bisa diproses lagi.');
        }

        if (!$this->userCanActOn($pbiApbn)) {
            return back()->with('error', 'Anda tidak berwenang memproses permohonan pada tahap ini.');
        }

        $user = Auth::user();
        $roleName = $user->role->name ?? '-';
        $aksi = $request->aksi;
        $catatan = $request->catatan;

        switch ($aksi) {
            case 'simpan':
                $pbiApbn->update(['catatan_internal' => $catatan]);
                $taskName = 'Menyimpan catatan';
                break;

            case 'next':
                $next = $pbiApbn->nextStageKey();
                if (!$next) {
                    return back()->with('error', 'Tidak ada tahap selanjutnya.');
                }
                $label = $next === 'disetujui' ? 'Disetujui' : (PbiApbn::STAGES[$next]['label'] ?? $next);
                $taskName = $next === 'disetujui'
                    ? 'Menyetujui permohonan'
                    : "Meneruskan ke {$label}";
                $pbiApbn->update([
                    'status' => $next,
                    'catatan_internal' => $catatan,
                ]);
                break;

            case 'back':
                $prev = $pbiApbn->previousStageKey();
                if (!$prev) {
                    return back()->with('error', 'Permohonan sudah berada di tahap paling awal, tidak bisa dikembalikan.');
                }
                $label = PbiApbn::STAGES[$prev]['label'] ?? $prev;
                $taskName = "Mengembalikan ke {$label}";
                $pbiApbn->update([
                    'status' => $prev,
                    'catatan_internal' => $catatan,
                ]);
                break;

            case 'reject':
                $taskName = 'Menolak permohonan';
                $pbiApbn->update([
                    'status' => 'ditolak',
                    'catatan_internal' => $catatan,
                ]);
                break;
        }

        PbiApbnLog::create([
            'pbi_apbn_id' => $pbiApbn->id,
            'user_id' => $user->id,
            'username' => $user->name,
            'role_name' => $roleName,
            'task_name' => $taskName,
            'catatan' => $catatan,
        ]);

        return redirect()->route('pbi-apbn.ajuan.show', $pbiApbn)
            ->with('success', 'Berhasil diproses.');
    }

    /**
     * ================= ARSIP =================
     * List permohonan yang sudah final (disetujui / ditolak).
     */
    public function arsipIndex(Request $request)
    {
        $query = PbiApbn::query()->where('status', 'disetujui');

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('updated_at', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('updated_at', '<=', $request->tanggal_akhir);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('nik_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_registrasi', 'like', "%{$s}%");
            });
        }

        $arsip = $query->latest('updated_at')->paginate(10)->withQueryString();

        return view('admin.pbi-apbn.arsip.index', compact('arsip'));
    }

    /**
     * ================= MONITORING =================
     * Semua proses (aktif maupun final) + status per tahap.
     */
    public function monitoringIndex(Request $request)
    {
        $query = PbiApbn::with(['logs' => fn($q) => $q->orderBy('created_at')]);

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->tanggal_akhir);
        }
        if ($request->filled('desil')) {
            $query->where('desil_nasional', $request->desil);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_registrasi', 'like', "%{$s}%");
            });
        }

        $perPage = (int) $request->query('display', 10);
        $data = $query->latest()->paginate($perPage)->withQueryString();

        return view('admin.pbi-apbn.monitoring.index', compact('data'));
    }

    /**
     * ================= LOG =================
     * Semua log proses lintas permohonan (untuk modul PBI APBN).
     */
    public function logIndex(Request $request)
    {
        $query = PbiApbnLog::with(['pbiApbn', 'user'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('pbiApbn', function ($q) use ($s) {
                $q->where('nama_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_registrasi', 'like', "%{$s}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.pbi-apbn.monitoring.log', compact('logs'));
    }

    /**
     * User boleh bertindak (simpan/next/back/reject) kalau:
     * - dia super_admin, ATAU
     * - role dia cocok dengan role yang berwenang di status saat ini
     */
    private function userCanActOn(PbiApbn $pbiApbn): bool
    {
        $user = Auth::user();
        $roleSlug = $user->role->slug ?? null;

        if ($roleSlug === 'superadmin') {
            return true;
        }

        return $roleSlug === $pbiApbn->currentStageRole();
    }
}
