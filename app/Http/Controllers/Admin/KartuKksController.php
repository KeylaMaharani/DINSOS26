<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KartuKksPermohonan;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KartuKksController extends Controller
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
     *
     * 'next'          : status setelah aksi "lanjut" disetujui
     * 'allowed_role'  : slug role yang BOLEH memproses status saat ini
     * 'task_lanjut'   : nama task yang dicatat di log saat disetujui/dilanjutkan
     * 'next_role'     : role yang akan "memegang" berkas setelah lanjut (current_role_id)
     * 'return_status' : status tujuan saat aksi "kembalikan" dipakai (opsional)
     * 'return_role'   : role yang akan memegang berkas setelah dikembalikan (opsional)
     */
    private function transitions(): array
    {
        return [
            KartuKksPermohonan::STATUS_DIAJUKAN => [
                'allowed_role' => 'kelurahan',
                'task_lanjut' => 'Verifikasi Kelurahan',
                'next' => KartuKksPermohonan::STATUS_VERIFIKASI_KELURAHAN,
                'next_role' => 'kelurahan',
                // Tahap paling awal, tidak ada tahap sebelumnya untuk dikembalikan.
            ],
            KartuKksPermohonan::STATUS_VERIFIKASI_KELURAHAN => [
                'allowed_role' => 'kelurahan',
                'task_lanjut' => 'TTD Lurah',
                'next' => KartuKksPermohonan::STATUS_TTD_LURAH,
                'next_role' => 'operator_dinsos',
                // Masih di role Kelurahan yang sama, tidak ada return lintas-role.
            ],
            KartuKksPermohonan::STATUS_TTD_LURAH => [
                'allowed_role' => 'operator_dinsos',
                'task_lanjut' => 'Validasi Dinsos',
                'next' => KartuKksPermohonan::STATUS_VALIDASI_DINSOS,
                'next_role' => 'operator_dinsos',
                'return_status' => KartuKksPermohonan::STATUS_VERIFIKASI_KELURAHAN,
                'return_role' => 'kelurahan',
            ],
            KartuKksPermohonan::STATUS_VALIDASI_DINSOS => [
                'allowed_role' => 'operator_dinsos',
                'task_lanjut' => 'Diteruskan ke Kabin',
                'next' => KartuKksPermohonan::STATUS_DIPROSES_KABIN,
                'next_role' => 'kabin',
                'return_status' => KartuKksPermohonan::STATUS_VERIFIKASI_KELURAHAN,
                'return_role' => 'kelurahan',
            ],
            KartuKksPermohonan::STATUS_DIPROSES_KABIN => [
                'allowed_role' => 'kabin',
                'task_lanjut' => 'Disetujui Kabin, diteruskan ke Kadis',
                'next' => KartuKksPermohonan::STATUS_DISETUJUI_KADIS,
                'next_role' => 'kadis',
                'return_status' => KartuKksPermohonan::STATUS_TTD_LURAH,
                'return_role' => 'operator_dinsos',
            ],
            KartuKksPermohonan::STATUS_DISETUJUI_KADIS => [
                'allowed_role' => 'kadis',
                'task_lanjut' => 'Disetujui Kadis',
                'next' => KartuKksPermohonan::STATUS_SELESAI,
                'next_role' => null,
                'return_status' => KartuKksPermohonan::STATUS_DIPROSES_KABIN,
                'return_role' => 'kabin',
            ],
        ];
    }

    /**
     * GET /kartu-kks/ajuan
     */
    public function ajuan(Request $request)
    {
        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isAdmin = $this->isAdmin($user->role);

        $query = KartuKksPermohonan::with('currentRole')
            ->whereNotIn('status', [KartuKksPermohonan::STATUS_SELESAI, KartuKksPermohonan::STATUS_DITOLAK]);

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

        return view('admin.kartu-kks.ajuan.index', [
            'ajuanList' => $ajuanList,
            'statusLabels' => KartuKksPermohonan::statusLabels(),
        ]);
    }

    /**
     * GET /kartu-kks/arsip
     */
    public function arsip(Request $request)
    {
        $query = KartuKksPermohonan::query()
            ->where('status', KartuKksPermohonan::STATUS_SELESAI);

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_insert', '>=', $request->date('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_insert', '<=', $request->date('tanggal_akhir'));
        }
        if ($request->filled('masalah_kartu') && $request->masalah_kartu !== '-Pilih-') {
            $query->where('masalah_kartu', $request->masalah_kartu);
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

        $masalahKartuOptions = KartuKksPermohonan::query()
            ->whereNotNull('masalah_kartu')
            ->distinct()
            ->pluck('masalah_kartu');

        return view('admin.kartu-kks.arsip.index', [
            'arsipList' => $arsipList,
            'masalahKartuOptions' => $masalahKartuOptions,
            'statusLabels' => KartuKksPermohonan::statusLabels(),
        ]);
    }

    /**
     * GET /kartu-kks/monitoring
     */
    public function monitoring(Request $request)
    {
        $query = KartuKksPermohonan::with(['logs' => fn ($q) => $q->latest('tanggal_proses')->limit(1)])
            ->where('status', '!=', KartuKksPermohonan::STATUS_SELESAI);

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

        return view('admin.kartu-kks.monitoring.index', [
            'monitoringList' => $monitoringList,
            'statusLabels' => KartuKksPermohonan::statusLabels(),
        ]);
    }

    /**
     * GET /kartu-kks/{permohonan}
     * Halaman detail penuh (menggantikan modal Log + Peta + Tolak).
     */
    public function show(KartuKksPermohonan $permohonan)
    {
        $permohonan->load([
            'detail',
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
            KartuKksPermohonan::STATUS_SELESAI,
            KartuKksPermohonan::STATUS_DITOLAK,
        ], true);

        $rule = $transitions[$permohonan->status] ?? null;

        $canAct = ! $isFinal
            && $rule
            && ($isAdmin || $roleSlug === 'super_admin' || $roleSlug === $rule['allowed_role']);

        $returnLabel = ($canAct && isset($rule['return_role']))
            ? 'Kembalikan ke ' . $this->roleLabel($rule['return_role'])
            : null;

        return view('admin.kartu-kks.ajuan.show', [
            'data' => $permohonan,
            'statusLabels' => KartuKksPermohonan::statusLabels(),
            'isFinal' => $isFinal,
            'canAct' => $canAct,
            'isAdmin' => $isAdmin,
            'nextTaskLabel' => $rule['task_lanjut'] ?? null,
            'returnLabel' => $returnLabel,
        ]);
    }

    /**
     * GET /kartu-kks/{permohonan}/detail  (dipanggil via fetch() untuk modal Arsip / Log Ajuan)
     */
    public function detail(KartuKksPermohonan $permohonan)
    {
        $permohonan->load(['detail', 'lampiran', 'surat', 'logs.user', 'currentRole']);

        return response()->json($permohonan);
    }

    /**
     * PUT /kartu-kks/{permohonan}/detail
     * Admin/petugas mengedit Data Detail pemohon.
     */
    public function updateDetail(Request $request, KartuKksPermohonan $permohonan)
    {
        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isAdmin = $this->isAdmin($user->role);
        $transitions = $this->transitions();

        $isFinal = in_array($permohonan->status, [
            KartuKksPermohonan::STATUS_SELESAI,
            KartuKksPermohonan::STATUS_DITOLAK,
        ], true);

        $rule = $transitions[$permohonan->status] ?? null;

        $canAct = ! $isFinal
            && $rule
            && ($isAdmin || $roleSlug === 'super_admin' || $roleSlug === $rule['allowed_role']);

        if (! $canAct) {
            abort(403, 'Anda tidak berwenang mengedit data ini.');
        }

        $validated = $request->validate([
            'nik' => 'nullable|string|max:20',
            'nama' => 'nullable|string|max:150',
            'jenis_kelamin' => 'nullable|string|max:20',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghuchu',
            'status_perkawinan' => 'nullable|in:Kawin,Belum Kawin,Cerai Hidup,Cerai Mati',
            'no_pkh' => 'nullable|string|max:50',
            'no_kk' => 'nullable|string|max:30',
            'no_kartu_kks' => 'nullable|string|max:30',
            'no_rekening' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'masalah_kartu' => 'nullable|in:terblokir,rusak,hilang,masa berlaku habis,nama tidak sesuai,keterangan lainnya',
            'nomor_kehilangan_polisi' => 'nullable|string|max:100',
        ]);

        $permohonan->detail()->updateOrCreate([], $validated);

        return back()->with('success', 'Data Detail berhasil diperbarui.');
    }

    /**
     * POST /kartu-kks/{permohonan}/proses
     * Body: action = lanjut|tolak|kembalikan|simpan_catatan
     *       catatan (wajib untuk tolak, opsional untuk lainnya)
     */
    public function proses(Request $request, KartuKksPermohonan $permohonan)
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
            KartuKksPermohonan::STATUS_SELESAI,
            KartuKksPermohonan::STATUS_DITOLAK,
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
                        'status' => KartuKksPermohonan::STATUS_DITOLAK,
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
