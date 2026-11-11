<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbiApbd;
use App\Models\PbiApbdLog;
use App\Support\Faskes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PbiApbdController extends Controller
{
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

    private function perPage(Request $request, string $key = 'tampilkan'): int
    {
        $perPage = (int) $request->query($key, 10);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
    }

    /**
     * ================= AJUAN =================
     * List permohonan yang masih berjalan (belum final), lintas semua tahap.
     */
    public function ajuanIndex(Request $request)
    {
        $query = PbiApbd::query()
            ->whereNotIn('status', ['disetujui', 'ditolak'])
            ->where(fn ($q) => $q->where('status', '!=', 'kelurahan')->orWhereNotNull('data_diisi_at'));

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
        $pbiApbd->tandaiDilihat(Auth::user());

        $pbiApbd->load(['anggotaKeluarga', 'logs.user', 'diagnosaLogs.user']);

        $canAct = $this->userCanActOn($pbiApbd);
        $faskesOptions = $this->faskesOptions($pbiApbd);

        return view('admin.pbi-apbd.ajuan.show', [
            'data' => $pbiApbd,
            'canAct' => $canAct,
            'faskesOptions' => $faskesOptions,
        ]);
    }

    public function ajuanUpdateTambahan(Request $request, PbiApbd $pbiApbd)
    {
        if (!$this->userCanActOn($pbiApbd)) {
            return back()->with('error', 'Anda tidak berwenang mengedit data ini.');
        }

        $pbiApbd->tandaiDilihat(Auth::user());

        $opsi = $this->faskesOptions($pbiApbd);

        $validated = $request->validate([
            'nama_faskes' => ['nullable', 'string', 'max:150', function ($attr, $value, $fail) use ($opsi, $pbiApbd) {
                if ($value === null || $value === '' || $value === $pbiApbd->nama_faskes) {
                    return; // kosong / nilai tersimpan lama tetap boleh
                }
                if (! in_array($value, $opsi, true)) {
                    $fail('Faskes tidak sesuai dengan wilayah pemohon.');
                }
            }],
        ]);

        $pbiApbd->update($validated);

        return back()->with('success', 'Data berhasil diperbarui.');
    }

    public function ajuanTambahDiagnosa(Request $request, PbiApbd $pbiApbd)
    {
        if (!$this->userCanActOn($pbiApbd)) {
            return back()->with('error', 'Anda tidak berwenang mengedit data ini.');
        }

        $pbiApbd->tandaiDilihat(Auth::user());

        $validated = $request->validate([
            'diagnosa' => ['required', 'string', 'max:2000'],
        ], [
            'diagnosa.required' => 'Diagnosa tidak boleh kosong.',
        ]);

        $user = Auth::user();

        if ($pbiApbd->status === 'kelurahan') {
            return back()->with('error', 'Diagnosa belum bisa ditambahkan selama berkas masih di Kelurahan.');
        }

        $pbiApbd->diagnosaLogs()->create([
            'user_id' => $user->id,
            'username' => $user->name,
            'role_name' => $user->role->name ?? '-',
            'diagnosa' => $validated['diagnosa'],
        ]);

        $pbiApbd->update(['diagnosa' => $validated['diagnosa']]);

        return back()->with('success', 'Diagnosa baru berhasil ditambahkan.');
    }

    /** Puskesmas sesuai wilayah pemohon (kelurahan -> kecamatan, tanpa peduli besar-kecil huruf). */
    private function faskesOptions(PbiApbd $pbiApbd): array
    {
        return Faskes::untuk($pbiApbd->kecamatan, $pbiApbd->desa_kelurahan);
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

        $pbiApbd->tandaiDilihat(Auth::user());

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
                // Aturan suket: Operator Dinsos wajib mengunggah suket sebelum meneruskan ke Kabid
                if ($pbiApbd->status === 'operator_dinsos' && blank($pbiApbd->surat_keterangan)) {
                    return back()->with('error', 'Suket belum diunggah. Unggah dulu sebelum meneruskan ke Kepala Bidang.');
                }

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

        if ($request->filled('status') && in_array($request->status, $statusArsip, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('nik_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_registrasi', 'like', "%{$s}%");
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

    public function arsipDetail(PbiApbd $pbiApbd)
    {
        abort_unless($pbiApbd->isFinal(), 404);

        $pbiApbd->load(['anggotaKeluarga', 'logs.user', 'diagnosaLogs.user']);

        return response()->json($pbiApbd);
    }

    /**
     * ================= MONITORING =================
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