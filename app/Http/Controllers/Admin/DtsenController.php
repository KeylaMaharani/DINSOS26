<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DtsenPermohonan;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DtsenController extends Controller
{
    /**
     * Sama seperti logika di CheckRole: role apapun yang namanya mengandung
     * "admin" dianggap admin dan bebas dari pembatasan role di bawah ini.
     */
    private function isAdmin($role): bool
    {
        if (! $role) {
            return false;
        }
        $slug = $role->slug ?: Str::slug($role->name, '_');

        return str_contains(strtolower($role->name ?? ''), 'admin')
            || in_array($slug, ['admin', 'super_admin', 'superadmin'], true);
    }

    private function roleSlug($role): ?string
    {
        if (! $role) {
            return null;
        }

        return $role->slug ?: Str::slug($role->name, '_');
    }

    /**
     * Label tampilan untuk sebuah role slug.
     */
    private function roleLabel(string $slug): string
    {
        return match ($slug) {
            'kelurahan' => 'Kelurahan',
            'operator_dinsos' => 'Operator Dinsos',
            'kabin' => 'Kabin',
            'kadis' => 'Kepala Dinas',
            default => ucfirst(str_replace('_', ' ', $slug)),
        };
    }

    /**
     * Peta alur status -> aksi berikutnya & aksi kembali.
     * Identik dengan alur Kartu KKS:
     *   Kelurahan -> Verifikasi Kelurahan -> TTD Lurah -> Validasi Dinsos
     *   -> Diproses Kabin -> Disetujui Kadis -> Selesai
     */
    private function transitions(): array
    {
        return [
            DtsenPermohonan::STATUS_DIAJUKAN => [
                'allowed_role' => 'kelurahan',
                'task_lanjut' => 'Verifikasi Kelurahan',
                'next' => DtsenPermohonan::STATUS_VERIFIKASI_KELURAHAN,
                'next_role' => 'kelurahan',
            ],
            DtsenPermohonan::STATUS_VERIFIKASI_KELURAHAN => [
                'allowed_role' => 'kelurahan',
                'task_lanjut' => 'TTD Lurah',
                'next' => DtsenPermohonan::STATUS_TTD_LURAH,
                'next_role' => 'operator_dinsos',
            ],
            DtsenPermohonan::STATUS_TTD_LURAH => [
                'allowed_role' => 'operator_dinsos',
                'task_lanjut' => 'Validasi Dinsos',
                'next' => DtsenPermohonan::STATUS_VALIDASI_DINSOS,
                'next_role' => 'operator_dinsos',
                'return_status' => DtsenPermohonan::STATUS_VERIFIKASI_KELURAHAN,
                'return_role' => 'kelurahan',
            ],
            DtsenPermohonan::STATUS_VALIDASI_DINSOS => [
                'allowed_role' => 'operator_dinsos',
                'task_lanjut' => 'Diteruskan ke Kabin',
                'next' => DtsenPermohonan::STATUS_DIPROSES_KABIN,
                'next_role' => 'kabin',
                'return_status' => DtsenPermohonan::STATUS_VERIFIKASI_KELURAHAN,
                'return_role' => 'kelurahan',
            ],
            DtsenPermohonan::STATUS_DIPROSES_KABIN => [
                'allowed_role' => 'kabin',
                'task_lanjut' => 'Disetujui Kabin, diteruskan ke Kadis',
                'next' => DtsenPermohonan::STATUS_DISETUJUI_KADIS,
                'next_role' => 'kadis',
                'return_status' => DtsenPermohonan::STATUS_TTD_LURAH,
                'return_role' => 'operator_dinsos',
            ],
            DtsenPermohonan::STATUS_DISETUJUI_KADIS => [
                'allowed_role' => 'kadis',
                'task_lanjut' => 'Disetujui Kadis',
                'next' => DtsenPermohonan::STATUS_SELESAI,
                'next_role' => null,
                'return_status' => DtsenPermohonan::STATUS_DIPROSES_KABIN,
                'return_role' => 'kabin',
            ],
        ];
    }

    /**
     * GET /dtsen/ajuan
     */
    public function ajuan(Request $request)
    {
        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isAdmin = $this->isAdmin($user->role);

        $query = DtsenPermohonan::with('currentRole')
            ->whereNotIn('status', [DtsenPermohonan::STATUS_SELESAI, DtsenPermohonan::STATUS_DITOLAK]);

        if (! $isAdmin) {
            $query->whereHas('currentRole', fn ($q) => $q->where('slug', $roleSlug));
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nik', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%")
                    ->orWhere('alamat', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ajuanList = $query->orderBy('tanggal_insert')->paginate(10)->withQueryString();

        return view('admin.dtsen.ajuan.index', [
            'ajuanList' => $ajuanList,
            'statusLabels' => DtsenPermohonan::statusLabels(),
        ]);
    }

    /**
     * GET /dtsen/arsip
     */
    public function arsip(Request $request)
    {
        $query = DtsenPermohonan::with('bansos')
            ->where('status', DtsenPermohonan::STATUS_SELESAI);

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_insert', '>=', $request->date('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_insert', '<=', $request->date('tanggal_akhir'));
        }
        if ($request->filled('alasan_cetak') && $request->alasan_cetak !== '-Pilih-') {
            $query->where('alasan_cetak', $request->alasan_cetak);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nik', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%")
                    ->orWhere('alamat', 'like', "%{$s}%");
            });
        }

        $perPage = (int) $request->query('tampilkan', 10);

        $arsipList = $query->orderByDesc('tanggal_insert')->paginate($perPage)->withQueryString();

        $alasanCetakOptions = DtsenPermohonan::query()
            ->whereNotNull('alasan_cetak')
            ->distinct()
            ->pluck('alasan_cetak');

        return view('admin.dtsen.arsip.index', [
            'arsipList' => $arsipList,
            'alasanCetakOptions' => $alasanCetakOptions,
            'statusLabels' => DtsenPermohonan::statusLabels(),
        ]);
    }

    /**
     * GET /dtsen/monitoring
     */
    public function monitoring(Request $request)
    {
        $query = DtsenPermohonan::with([
                'bansos',
                'currentRole',
                // Diubah: sebelumnya hanya ambil 1 log terakhir (->limit(1)).
                // Tampilan Monitoring sekarang butuh SELURUH riwayat log,
                // diurutkan dari yang paling lama -> paling baru.
                'logs' => fn ($q) => $q->orderBy('tanggal_proses'),
            ])
            ->where('status', '!=', DtsenPermohonan::STATUS_SELESAI);

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_insert', '>=', $request->date('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_insert', '<=', $request->date('tanggal_akhir'));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nik', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status') && $request->status !== 'Semua') {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->query('display', 10);

        $monitoringList = $query->orderByDesc('tanggal_insert')->paginate($perPage)->withQueryString();

        return view('admin.dtsen.monitoring.index', [
            'monitoringList' => $monitoringList,
            'statusLabels' => DtsenPermohonan::statusLabels(),
        ]);
    }

    /**
     * GET /dtsen/{permohonan}
     */
    public function show(DtsenPermohonan $permohonan)
    {
        $permohonan->load([
            'detail',
            'bansos',
            'lampiran',
            'surat',
            'logs' => fn ($q) => $q->orderBy('tanggal_proses'),
            'currentRole',
        ]);

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isAdmin = $this->isAdmin($user->role);
        $transitions = $this->transitions();

        $isFinal = in_array($permohonan->status, [
            DtsenPermohonan::STATUS_SELESAI,
            DtsenPermohonan::STATUS_DITOLAK,
        ], true);

        $rule = $transitions[$permohonan->status] ?? null;

        $canAct = ! $isFinal
            && $rule
            && ($isAdmin || $roleSlug === 'super_admin' || $roleSlug === $rule['allowed_role']);

        $returnLabel = ($canAct && isset($rule['return_role']))
            ? 'Kembalikan ke ' . $this->roleLabel($rule['return_role'])
            : null;

        return view('admin.dtsen.ajuan.show', [
            'data' => $permohonan,
            'statusLabels' => DtsenPermohonan::statusLabels(),
            'isFinal' => $isFinal,
            'canAct' => $canAct,
            'isAdmin' => $isAdmin,
            'nextTaskLabel' => $rule['task_lanjut'] ?? null,
            'returnLabel' => $returnLabel,
        ]);
    }

    /**
     * GET /dtsen/{permohonan}/detail (dipanggil via fetch() untuk modal Arsip)
     */
    public function detail(DtsenPermohonan $permohonan)
    {
        $permohonan->load(['detail', 'bansos', 'lampiran', 'surat', 'logs.user', 'currentRole']);

        return response()->json($permohonan);
    }

    /**
     * PUT /dtsen/{permohonan}/detail
     * Mengedit "Data Detail" pemohon (bagian 2 di dokumen kebutuhan).
     */
    public function updateDetail(Request $request, DtsenPermohonan $permohonan)
    {
        $this->authorizeCurrentStage($permohonan);

        $validated = $request->validate([
            'nik' => 'required|string|max:20',
            'nama' => 'required|string|max:150',
            'no_kk' => 'required|string|max:30',
            'alamat' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alasan_keputusan_cetak' => 'required|string|max:1000',
        ]);

        $permohonan->detail()->updateOrCreate([], $validated);

        return back()->with('success', 'Data Detail berhasil diperbarui.');
    }

    /**
     * PUT /dtsen/{permohonan}/bansos
     * Mengedit "Keterangan Penerima BANSOS" (bagian 1 di dokumen kebutuhan).
     * Field ini yang menentukan "Desil" & "Alasan Cetak" yang tampil di Monitoring.
     *
     * "dicetak_oleh" WAJIB diisi -- harus jelas siapa/unit mana yang mencetak
     * kartu DTSEN, untuk keperluan akuntabilitas & audit.
     */
    public function updateBansos(Request $request, DtsenPermohonan $permohonan)
    {
        $this->authorizeCurrentStage($permohonan);

        $validated = $request->validate([
            'peringkat_kesejahteraan_keluarga' => 'required|string|max:50',
            'bnpt' => 'required|boolean',
            'pkh' => 'required|boolean',
            'pbi' => 'required|boolean',
            'yatim_piatu' => 'required|boolean',
            'tanggal_cetak' => 'required|date',
            'berlaku_sampai' => 'required|date|after_or_equal:tanggal_cetak',
            'dicetak_oleh' => 'required|string|max:150',
        ], [
            'dicetak_oleh.required' => 'Kolom "Dicetak Oleh" wajib diisi.',
        ]);

        $permohonan->bansos()->updateOrCreate([], $validated);

        return back()->with('success', 'Keterangan Penerima BANSOS berhasil diperbarui.');
    }

    /**
     * Helper aturan akses yang sama dipakai updateDetail() & updateBansos().
     */
    private function authorizeCurrentStage(DtsenPermohonan $permohonan): void
    {
        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isAdmin = $this->isAdmin($user->role);
        $transitions = $this->transitions();

        $isFinal = in_array($permohonan->status, [
            DtsenPermohonan::STATUS_SELESAI,
            DtsenPermohonan::STATUS_DITOLAK,
        ], true);

        $rule = $transitions[$permohonan->status] ?? null;

        $canAct = ! $isFinal
            && $rule
            && ($isAdmin || $roleSlug === 'super_admin' || $roleSlug === $rule['allowed_role']);

        if (! $canAct) {
            abort(403, 'Anda tidak berwenang mengedit data ini.');
        }
    }

    /**
     * POST /dtsen/{permohonan}/proses
     * Body: action = lanjut|tolak|kembalikan|simpan_catatan
     *       catatan (wajib)
     */
    public function proses(Request $request, DtsenPermohonan $permohonan)
    {
        $request->validate([
            'action' => 'required|in:lanjut,tolak,kembalikan,simpan_catatan',
            'catatan' => 'required|string|max:1000',
        ], [
            'catatan.required' => 'Catatan wajib diisi untuk aksi ini.',
        ]);

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isAdmin = $this->isAdmin($user->role);
        $transitions = $this->transitions();

        $isFinal = in_array($permohonan->status, [
            DtsenPermohonan::STATUS_SELESAI,
            DtsenPermohonan::STATUS_DITOLAK,
        ], true);

        if ($isFinal) {
            return back()->with('error', 'Permohonan ini sudah final, tidak bisa diproses lagi.');
        }

        $rule = $transitions[$permohonan->status] ?? null;

        $canAct = $rule
            && ($isAdmin || $roleSlug === 'super_admin' || $roleSlug === $rule['allowed_role']);

        if (! $canAct) {
            abort(403, 'Anda tidak berwenang memproses permohonan pada tahap ini.');
        }

        switch ($request->action) {

            case 'tolak':
                DB::transaction(function () use ($permohonan, $rule, $user, $request) {
                    $permohonan->update([
                        'status' => DtsenPermohonan::STATUS_DITOLAK,
                        'current_role_id' => null,
                    ]);

                    $permohonan->logs()->create([
                        'tanggal_proses' => now(),
                        'user_id' => $user->id,
                        'username' => $user->username ?? $user->name,
                        'taskname' => 'Ditolak',
                        'rolename' => $this->roleLabel($rule['allowed_role']),
                        'catatan' => $request->catatan,
                    ]);
                });

                return back()->with('success', 'Permohonan telah ditolak.');

            case 'simpan_catatan':
                $permohonan->logs()->create([
                    'tanggal_proses' => now(),
                    'user_id' => $user->id,
                    'username' => $user->username ?? $user->name,
                    'taskname' => 'Simpan Catatan',
                    'rolename' => $this->roleLabel($rule['allowed_role']),
                    'catatan' => $request->catatan,
                ]);

                return back()->with('success', 'Catatan berhasil disimpan.');

            case 'kembalikan':
                if (! isset($rule['return_status'], $rule['return_role'])) {
                    return back()->with('error', 'Permohonan pada tahap ini tidak bisa dikembalikan.');
                }

                DB::transaction(function () use ($permohonan, $rule, $user, $request) {
                    $returnRole = Role::where('slug', $rule['return_role'])->first();
                    $returnLabel = $this->roleLabel($rule['return_role']);

                    $permohonan->update([
                        'status' => $rule['return_status'],
                        'current_role_id' => $returnRole?->id,
                    ]);

                    $permohonan->logs()->create([
                        'tanggal_proses' => now(),
                        'user_id' => $user->id,
                        'username' => $user->username ?? $user->name,
                        'taskname' => 'Dikembalikan ke ' . $returnLabel,
                        'rolename' => $this->roleLabel($rule['allowed_role']),
                        'catatan' => $request->catatan,
                    ]);
                });

                return back()->with('success', 'Permohonan berhasil dikembalikan.');

            case 'lanjut':
            default:
                if (! $rule) {
                    return back()->with('error', 'Permohonan ini sudah pada tahap akhir dan tidak bisa diproses lagi.');
                }

                DB::transaction(function () use ($permohonan, $rule, $user, $request) {
                    $nextRole = $rule['next_role'] ? Role::where('slug', $rule['next_role'])->first() : null;

                    $permohonan->update([
                        'status' => $rule['next'],
                        'current_role_id' => $nextRole?->id,
                    ]);

                    $permohonan->logs()->create([
                        'tanggal_proses' => now(),
                        'user_id' => $user->id,
                        'username' => $user->username ?? $user->name,
                        'taskname' => $rule['task_lanjut'],
                        'rolename' => $this->roleLabel($rule['allowed_role']),
                        'catatan' => $request->catatan,
                    ]);
                });

                return back()->with('success', 'Permohonan berhasil diproses ke tahap berikutnya.');
        }
    }
}
