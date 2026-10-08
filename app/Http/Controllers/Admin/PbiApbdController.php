<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbiApbd;
use App\Models\PbiApbdLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PbiApbdController extends Controller
{
    /**
     * Tambahkan kondisi OR pencarian pada kolom created_at (Tgl Insert).
     * Mendukung input sebagian maupun penuh, format dd-mm-yyyy (tampilan di tabel)
     * atau yyyy-mm-dd. Tanda "/" otomatis dianggap "-".
     * Contoh: "25-09-2026", "25-09", "09-2026", "2026-09-25".
     * Disamakan dengan DtsenController::orWhereTanggalLike().
     */
    private function orWhereTanggalLike($q, string $term): void
    {
        $term = str_replace('/', '-', trim($term));

        $formats = match (DB::connection()->getDriverName()) {
            'sqlite' => ["strftime('%d-%m-%Y', created_at)", "strftime('%Y-%m-%d', created_at)"],
            'pgsql' => ["to_char(created_at, 'DD-MM-YYYY')", "to_char(created_at, 'YYYY-MM-DD')"],
            default => ["DATE_FORMAT(created_at, '%d-%m-%Y')", "DATE_FORMAT(created_at, '%Y-%m-%d')"],
        };

        foreach ($formats as $expr) {
            $q->orWhereRaw("{$expr} LIKE ?", ["%{$term}%"]);
        }
    }

    /**
     * Ambil jumlah data per halaman dari query string dan batasi
     * hanya ke pilihan yang valid (10/25/50/100).
     */
    private function perPage(Request $request, string $key = 'tampilkan'): int
    {
        $perPage = (int) $request->query($key, 10);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
    }

    /**
     * ================= AJUAN =================
     * List permohonan yang masih berjalan (belum final).
     * Filter: tanggal, tahap saat ini, pencarian (termasuk tanggal).
     */
    public function ajuanIndex(Request $request)
    {
        $query = PbiApbd::query()->whereNotIn('status', ['disetujui', 'ditolak']);

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->date('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->date('tanggal_akhir'));
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_registrasi', 'like', "%{$s}%")
                    ->orWhere('nik_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_kk', 'like', "%{$s}%");
                // Pencarian juga mencocokkan Tgl Insert (dd-mm-yyyy atau yyyy-mm-dd)
                $this->orWhereTanggalLike($q, $s);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ajuan = $query->latest()->paginate($this->perPage($request))->withQueryString();

        return view('admin.pbi-apbd.ajuan.index', compact('ajuan'));
    }

    public function ajuanShow(PbiApbd $pbiApbd)
    {
        $pbiApbd->load(['anggotaKeluarga', 'logs.user', 'diagnosaLogs.user']);

        $canAct = $this->userCanActOn($pbiApbd);
        $faskesOptions = $this->faskesOptions($pbiApbd);

        return view('admin.pbi-apbd.ajuan.show', [
            'data' => $pbiApbd,
            'canAct' => $canAct,
            'faskesOptions' => $faskesOptions,
        ]);
    }

    /**
     * Update Nama Faskes (field overwrite, bukan riwayat).
     * Diagnosa TIDAK lagi ditangani di sini — lihat ajuanTambahDiagnosa().
     */
    public function ajuanUpdateTambahan(Request $request, PbiApbd $pbiApbd)
    {
        if (!$this->userCanActOn($pbiApbd)) {
            return back()->with('error', 'Anda tidak berwenang mengedit data ini.');
        }

        $validated = $request->validate([
            'nama_faskes' => ['nullable', 'string', 'max:150'],
        ]);

        $pbiApbd->update($validated);

        return back()->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Tambah entri baru ke riwayat Diagnosa (bisa diisi berulang kali,
     * tidak menghapus/menimpa entri sebelumnya).
     */
    public function ajuanTambahDiagnosa(Request $request, PbiApbd $pbiApbd)
    {
        if (!$this->userCanActOn($pbiApbd)) {
            return back()->with('error', 'Anda tidak berwenang mengedit data ini.');
        }

        $validated = $request->validate([
            'diagnosa' => ['required', 'string', 'max:2000'],
        ], [
            'diagnosa.required' => 'Diagnosa tidak boleh kosong.',
        ]);

        $user = Auth::user();

        if ($pbiApbd->status === 'kelurahan') {
            return false;
        }

        $pbiApbd->diagnosaLogs()->create([
            'user_id' => $user->id,
            'username' => $user->name,
            'role_name' => $user->role->name ?? '-',
            'diagnosa' => $validated['diagnosa'],
        ]);

        // Simpan juga nilai terakhir di kolom utama untuk tampilan ringkas/cepat
        $pbiApbd->update(['diagnosa' => $validated['diagnosa']]);

        return back()->with('success', 'Diagnosa baru berhasil ditambahkan.');
    }

    /**
     * Daftar opsi Nama Faskes berdasarkan kecamatan pada data permohonan.
     * Sementara masih hardcoded di config/faskes.php (belum ada tabel master).
     */
    private function faskesOptions(PbiApbd $pbiApbd): array
    {
        $master = config('faskes.puskesmas', []);

        return $master[$pbiApbd->kecamatan] ?? [];
    }

    /**
     * Aksi: simpan | next | back | reject
     */
    public function ajuanAksi(Request $request, PbiApbd $pbiApbd)
    {
        $request->validate([
            'aksi' => ['required', Rule::in(['simpan', 'next', 'back', 'reject'])],
            'catatan' => ['required', 'string', 'max:2000'],
        ]);

        if ($pbiApbd->isFinal()) {
            return back()->with('error', 'Permohonan ini sudah final (disetujui/ditolak), tidak bisa diproses lagi.');
        }

        if (!$this->userCanActOn($pbiApbd)) {
            return back()->with('error', 'Anda tidak berwenang memproses permohonan pada tahap ini.');
        }

        $user = Auth::user();
        $roleName = $user->role->name ?? '-';
        $aksi = $request->aksi;
        $catatan = $request->catatan;

        switch ($aksi) {
            case 'simpan':
                $pbiApbd->update(['catatan_internal' => $catatan]);
                $taskName = 'Menyimpan catatan';
                break;

            case 'next':
                $next = $pbiApbd->nextStageKey();
                if (!$next) {
                    return back()->with('error', 'Tidak ada tahap selanjutnya.');
                }
                $label = $next === 'disetujui' ? 'Disetujui' : (PbiApbd::STAGES[$next]['label'] ?? $next);
                $taskName = $next === 'disetujui'
                    ? 'Menyetujui permohonan'
                    : "Meneruskan ke {$label}";
                $pbiApbd->update([
                    'status' => $next,
                    'catatan_internal' => $catatan,
                ]);
                break;

            case 'back':
                $prev = $pbiApbd->previousStageKey();
                if (!$prev) {
                    return back()->with('error', 'Permohonan sudah berada di tahap paling awal, tidak bisa dikembalikan.');
                }
                $label = PbiApbd::STAGES[$prev]['label'] ?? $prev;
                $taskName = "Mengembalikan ke {$label}";
                $pbiApbd->update([
                    'status' => $prev,
                    'catatan_internal' => $catatan,
                ] + ($prev === 'kelurahan' ? ['sudah_diverifikasi_kelurahan' => false] : []));
                break;

            case 'reject':
                $taskName = 'Menolak permohonan';
                $pbiApbd->update([
                    'status' => 'ditolak',
                    'catatan_internal' => $catatan,
                ]);
                break;
        }

        PbiApbdLog::create([
            'pbi_apbd_id' => $pbiApbd->id,
            'user_id' => $user->id,
            'username' => $user->name,
            'role_name' => $roleName,
            'task_name' => $taskName,
            'catatan' => $catatan,
        ]);

        return redirect()->route('pbi-apbd.ajuan.show', $pbiApbd)
            ->with('success', 'Berhasil diproses.');
    }

    /**
     * ================= ARSIP =================
     * List permohonan yang sudah final: Disetujui + Ditolak.
     * Bisa difilter per status lewat query string ?status=disetujui|ditolak.
     * Disamakan dengan DtsenController::arsip().
     */
    public function arsipIndex(Request $request)
    {
        $statusArsip = ['disetujui', 'ditolak'];

        $query = PbiApbd::query()->whereIn('status', $statusArsip);

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->date('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->date('tanggal_akhir'));
        }

        // Filter status (hanya menerima disetujui / ditolak)
        if ($request->filled('status') && in_array($request->status, $statusArsip, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('nik_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_registrasi', 'like', "%{$s}%");
                // Pencarian juga mencocokkan Tgl Insert (dd-mm-yyyy atau yyyy-mm-dd)
                $this->orWhereTanggalLike($q, $s);
            });
        }

        $arsip = $query->latest()->paginate($this->perPage($request))->withQueryString();

        return view('admin.pbi-apbd.arsip.index', [
            'arsip' => $arsip,
            'statusOptions' => [
                'disetujui' => 'Disetujui',
                'ditolak' => 'Ditolak',
            ],
        ]);
    }

    /**
     * GET /pbi-apbd/arsip/{pbiApbd}/detail (dipanggil via fetch() untuk modal Arsip)
     * Hanya untuk permohonan yang sudah final.
     */
    public function arsipDetail(PbiApbd $pbiApbd)
    {
        abort_unless($pbiApbd->isFinal(), 404);

        $pbiApbd->load(['anggotaKeluarga', 'logs.user', 'diagnosaLogs.user']);

        return response()->json($pbiApbd);
    }

    /**
     * ================= MONITORING =================
     * Semua proses (aktif maupun final) + status per tahap.
     */
    public function monitoringIndex(Request $request)
    {
        $query = PbiApbd::with(['logs' => fn($q) => $q->orderBy('created_at')]);

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

        $data = $query->latest()->paginate($this->perPage($request, 'display'))->withQueryString();

        return view('admin.pbi-apbd.monitoring.index', compact('data'));
    }

    /**
     * ================= LOG =================
     * Semua log proses lintas permohonan (untuk modul PBI APBD).
     */
    public function logIndex(Request $request)
    {
        $query = PbiApbdLog::with(['pbiApbd', 'user'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('pbiApbd', function ($q) use ($s) {
                $q->where('nama_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_registrasi', 'like', "%{$s}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.pbi-apbd.monitoring.log', compact('logs'));
    }

    /**
     * User boleh bertindak (simpan/next/back/reject) kalau:
     * - dia super_admin (menerima variasi penulisan: superadmin, super_admin, super-admin), ATAU
     * - role dia cocok dengan role yang berwenang di status saat ini
     */
    private function userCanActOn(PbiApbd $pbiApbd): bool
    {
        $user = Auth::user();
        $roleSlug = strtolower(str_replace(['-', ' '], '_', $user->role->slug ?? ''));

        if (in_array($roleSlug, ['superadmin', 'super_admin'])) {
            return true;
        }

        $stageRole = strtolower(str_replace(['-', ' '], '_', $pbiApbd->currentStageRole() ?? ''));

        return $roleSlug !== '' && $roleSlug === $stageRole;
    }
}
