<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KartuKksPermohonan;
use App\Models\KartuKksLog;
use App\Models\Role;
use App\Support\KartuKksSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class KartuKksController extends Controller
{
    /**
     * Nama aktivitas di log saat Kepala Dinas menyetujui + menandatangani.
     * Dipakai juga untuk mencari "siapa yang TTD" di show().
     */
    public const TASK_TTD_KADIS = 'Disetujui Kadis (TTD Digital)';

    /**
     * GET /kartu-kks/log
     */
    public function logIndex(Request $request)
    {
        $query = KartuKksLog::query()->with('permohonan');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('username', 'like', "%{$s}%")
                    ->orWhereHas('permohonan', function ($q2) use ($s) {
                        $q2->where('nik', 'like', "%{$s}%")
                            ->orWhere('nama_pemohon', 'like', "%{$s}%");
                    });
            });
        }

        $logs = $query->orderByDesc('tanggal_proses')->paginate(15)->withQueryString();

        return view('admin.kartu-kks.monitoring.log', [
            'logs' => $logs,
        ]);
    }

    /**
     * Slug role yang sudah dinormalisasi (lowercase, strip/spasi -> underscore)
     * supaya "super-admin", "Super Admin", "SUPERADMIN", dll dianggap sama.
     */
    private function roleSlug($role): ?string
    {
        if (! $role) {
            return null;
        }

        $slug = $role->slug ?: Str::slug($role->name, '_');

        return strtolower(str_replace(['-', ' '], '_', $slug));
    }

    /**
     * Role apapun yang namanya mengandung "admin", atau slug-nya (setelah
     * dinormalisasi) termasuk admin/super_admin/superadmin, dianggap admin
     * dan bebas dari pembatasan role di bawah ini — termasuk melihat SEMUA
     * data tanpa terikat tahap/role manapun (dipakai untuk visibilitas
     * daftar Ajuan, lihat ajuan()).
     *
     * PENTING: method ini TIDAK dipakai untuk menentukan siapa yang boleh
     * menjalankan alur Proses Permohonan maupun mengedit Data Detail.
     * Untuk itu pakai isSuperAdmin() di bawah.
     */
    private function isAdmin($role): bool
    {
        if (! $role) {
            return false;
        }

        $slug = $this->roleSlug($role);

        return str_contains(strtolower($role->name ?? ''), 'admin')
            || in_array($slug, ['admin', 'super_admin', 'superadmin'], true);
    }

    /**
     * Role yang berwenang menjalankan alur Proses Permohonan di tahap
     * manapun. HANYA super admin — role "admin" biasa tidak termasuk.
     */
    private function isSuperAdmin($role): bool
    {
        if (! $role) {
            return false;
        }

        $slug = $this->roleSlug($role);

        return in_array($slug, ['super_admin', 'superadmin'], true);
    }

    /**
     * Pastikan petugas Kelurahan (akun lama) hanya bisa membuka / mengubah /
     * memproses permohonan dari wilayah kelurahannya sendiri. Admin & role
     * Dinsos lainnya tidak dibatasi wilayah (mereka dibatasi oleh tahap/role).
     */
    private function pastikanWilayah(KartuKksPermohonan $permohonan): void
    {
        $user = Auth::user();
        $role = $user->role;

        if ($this->isAdmin($role)) {
            return;
        }

        if ($this->roleSlug($role) === 'kelurahan') {
            abort_unless(
                filled($user->kelurahan) && $permohonan->kelurahan === $user->kelurahan,
                403,
                'Pengajuan ini bukan dari wilayah kelurahan Anda.'
            );
        }
    }

    /**
     * Label tampilan untuk sebuah role slug.
     */
    private function roleLabel(string $slug): string
    {
        return match ($slug) {
            'kelurahan' => 'Kelurahan',
            'operator_dinsos' => 'Operator Dinsos',
            'kabin' => 'Kepala Bidang',
            'kadis' => 'Kepala Dinas',
            default => ucfirst(str_replace('_', ' ', $slug)),
        };
    }

    /**
     * Urutan tahap alur yang AKTIF (dipakai dropdown filter di daftar Ajuan).
     * Status lama (verifikasi_kelurahan, ttd_lurah) sengaja tidak dimasukkan.
     */
    private function statusAlur(): array
    {
        return [
            KartuKksPermohonan::STATUS_DIAJUKAN,
            KartuKksPermohonan::STATUS_DIPROSES_KABIN,
            KartuKksPermohonan::STATUS_DISETUJUI_KADIS,
            KartuKksPermohonan::STATUS_VALIDASI_DINSOS,
        ];
    }

    /**
     * Peta alur status -> aksi berikutnya & aksi kembali.
     *
     * ALUR BARU:
     *   1. diajukan         (Operator Dinsos)  : input & lengkapi data ajuan
     *   2. diproses_kabin   (Kepala Bidang)    : cek data, teruskan ke Kadis / kembalikan ke operator
     *   3. disetujui_kadis  (Kepala Dinas)     : approve + TTD digital / kembalikan ke Kabid
     *   4. validasi_dinsos  (Operator Dinsos)  : hasil kembali ke operator, memberitahu pemohon
     *   5. selesai
     *
     * Nilai status memakai konstanta yang sudah ada (tidak perlu ubah kolom
     * database); yang berubah hanya urutan & labelnya (lihat statusLabels()).
     */
    private function transitions(): array
    {
        return [
            KartuKksPermohonan::STATUS_DIAJUKAN => [
                'allowed_role' => 'operator_dinsos',
                'task_lanjut' => 'Diteruskan ke Kabid',
                'tombol_lanjut' => 'Teruskan ke Kepala Bidang',
                'next' => KartuKksPermohonan::STATUS_DIPROSES_KABIN,
                'next_role' => 'kabin',
            ],
            KartuKksPermohonan::STATUS_DIPROSES_KABIN => [
                'allowed_role' => 'kabin',
                'task_lanjut' => 'Disetujui Kabid, diteruskan ke Kadis',
                'tombol_lanjut' => 'Setujui & Teruskan ke Kepala Dinas',
                'next' => KartuKksPermohonan::STATUS_DISETUJUI_KADIS,
                'next_role' => 'kadis',
                'return_status' => KartuKksPermohonan::STATUS_DIAJUKAN,
                'return_role' => 'operator_dinsos',
            ],
            KartuKksPermohonan::STATUS_DISETUJUI_KADIS => [
                'allowed_role' => 'kadis',
                'task_lanjut' => self::TASK_TTD_KADIS,
                'tombol_lanjut' => 'Setujui & Tanda Tangani (TTD Digital)',
                'next' => KartuKksPermohonan::STATUS_VALIDASI_DINSOS,
                'next_role' => 'operator_dinsos',
                'return_status' => KartuKksPermohonan::STATUS_DIPROSES_KABIN,
                'return_role' => 'kabin',
                'butuh_ttd' => true,
            ],
            KartuKksPermohonan::STATUS_VALIDASI_DINSOS => [
                'allowed_role' => 'operator_dinsos',
                'task_lanjut' => 'Hasil Diberitahukan ke Pemohon',
                'tombol_lanjut' => 'Tandai Selesai (Pemohon Sudah Diberitahu)',
                'next' => KartuKksPermohonan::STATUS_SELESAI,
                'next_role' => null,
            ],
        ];
    }

    /**
     * Tambahkan kondisi OR pencarian pada kolom tanggal_insert.
     * Mendukung input sebagian maupun penuh, format dd-mm-yyyy (tampilan di tabel)
     * atau yyyy-mm-dd. Tanda "/" otomatis dianggap "-".
     * Contoh: "25-09-2026", "25-09", "09-2026", "2026-09-25".
     */
    private function orWhereTanggalLike($q, string $term): void
    {
        $term = str_replace('/', '-', trim($term));

        $formats = match (DB::connection()->getDriverName()) {
            'sqlite' => ["strftime('%d-%m-%Y', tanggal_insert)", "strftime('%Y-%m-%d', tanggal_insert)"],
            'pgsql' => ["to_char(tanggal_insert, 'DD-MM-YYYY')", "to_char(tanggal_insert, 'YYYY-MM-DD')"],
            default => ["DATE_FORMAT(tanggal_insert, '%d-%m-%Y')", "DATE_FORMAT(tanggal_insert, '%Y-%m-%d')"],
        };

        foreach ($formats as $expr) {
            $q->orWhereRaw("{$expr} LIKE ?", ["%{$term}%"]);
        }
    }

    /**
     * GET /kartu-kks/ajuan
     *
     * Daftar permohonan yang masih berjalan (belum Selesai / Ditolak).
     * Filter: tanggal, tahap saat ini, pencarian (termasuk tanggal).
     */
    public function ajuan(Request $request)
    {
        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isAdmin = $this->isAdmin($user->role);

        $statusFinal = [
            KartuKksPermohonan::STATUS_SELESAI,
            KartuKksPermohonan::STATUS_DITOLAK,
        ];

        $query = KartuKksPermohonan::with('currentRole')
            ->whereNotIn('status', $statusFinal);

        // Superadmin/admin bisa melihat SEMUA data tanpa dibatasi role/tahap.
        if (! $isAdmin) {
            $query->whereHas('currentRole', fn($q) => $q->where('slug', $roleSlug));

            // Petugas Kelurahan (akun lama) hanya melihat ajuan dari kelurahannya sendiri.
            if ($roleSlug === 'kelurahan') {
                $query->when(
                    filled($user->kelurahan),
                    fn($q) => $q->where('kelurahan', $user->kelurahan),
                    fn($q) => $q->whereRaw('1 = 0') // wilayah belum diatur -> tidak tampil apa pun
                );
            }
        }

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
                    ->orWhere('nama_pemohon', 'like', "%{$s}%")
                    ->orWhere('alamat', 'like', "%{$s}%");
                // Pencarian juga mencocokkan Tgl Insert (dd-mm-yyyy atau yyyy-mm-dd)
                $this->orWhereTanggalLike($q, $s);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->query('tampilkan', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $ajuanList = $query->orderByDesc('tanggal_insert')->orderByDesc('id')->paginate($perPage)->withQueryString();

        $statusLabels = KartuKksPermohonan::statusLabels();

        // Dropdown tahap hanya berisi tahap alur baru yang masih berjalan, berurutan.
        $statusOptions = collect($this->statusAlur())
            ->mapWithKeys(fn($s) => [$s => $statusLabels[$s] ?? $s])
            ->all();

        return view('admin.kartu-kks.ajuan.index', [
            'ajuanList' => $ajuanList,
            'statusLabels' => $statusLabels,
            'statusOptions' => $statusOptions,
        ]);
    }

    /**
     * GET /kartu-kks/arsip
     *
     * Arsip berisi permohonan yang sudah final: Selesai + Ditolak.
     * Bisa difilter per status lewat query string ?status=selesai|ditolak.
     */
    public function arsip(Request $request)
    {
        $statusArsip = [
            KartuKksPermohonan::STATUS_SELESAI,
            KartuKksPermohonan::STATUS_DITOLAK,
        ];

        $query = KartuKksPermohonan::query()
            ->whereIn('status', $statusArsip);

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_insert', '>=', $request->date('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_insert', '<=', $request->date('tanggal_akhir'));
        }

        // Filter status (hanya menerima selesai / ditolak)
        if ($request->filled('status') && in_array($request->status, $statusArsip, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nik', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%")
                    ->orWhere('alamat', 'like', "%{$s}%");
                // Pencarian juga mencocokkan Tgl Insert (dd-mm-yyyy atau yyyy-mm-dd)
                $this->orWhereTanggalLike($q, $s);
            });
        }

        $perPage = (int) $request->query('tampilkan', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $arsipList = $query->orderByDesc('tanggal_insert')->orderByDesc('id')->paginate($perPage)->withQueryString();

        $statusLabels = KartuKksPermohonan::statusLabels();

        return view('admin.kartu-kks.arsip.index', [
            'arsipList' => $arsipList,
            'statusLabels' => $statusLabels,
            'statusOptions' => [
                KartuKksPermohonan::STATUS_SELESAI => $statusLabels[KartuKksPermohonan::STATUS_SELESAI] ?? 'Selesai',
                KartuKksPermohonan::STATUS_DITOLAK => $statusLabels[KartuKksPermohonan::STATUS_DITOLAK] ?? 'Ditolak',
            ],
        ]);
    }

    /**
     * GET /kartu-kks/monitoring
     */
    public function monitoring(Request $request)
    {
        // Menarik SEMUA LOG secara urut layaknya DTSEN, bukan cuma 1
        $query = KartuKksPermohonan::with(['currentRole', 'logs' => fn($q) => $q->orderBy('tanggal_proses')])
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

        $monitoringList = $query->orderByDesc('tanggal_insert')->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('admin.kartu-kks.monitoring.index', [
            'monitoringList' => $monitoringList,
            'statusLabels' => KartuKksPermohonan::statusLabels(),
        ]);
    }

    /**
     * GET /kartu-kks/{permohonan}
     */
    public function show(KartuKksPermohonan $permohonan)
    {
        $this->pastikanWilayah($permohonan);

        $permohonan->load([
            'detail',
            'lampiran',
            'surat',
            'logs' => fn($q) => $q->orderBy('tanggal_proses'),
            'logs.user',
            'currentRole',
        ]);

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isAdmin = $this->isAdmin($user->role);
        $isSuperAdmin = $this->isSuperAdmin($user->role);
        $transitions = $this->transitions();

        $isFinal = in_array($permohonan->status, [
            KartuKksPermohonan::STATUS_SELESAI,
            KartuKksPermohonan::STATUS_DITOLAK,
        ], true);

        $rule = $transitions[$permohonan->status] ?? null;

        // Hanya super admin yang selalu bisa bertindak di tahap manapun
        // selama permohonan belum final. Role lain harus sesuai dengan role
        // yang berwenang pada tahap saat ini.
        $canAct = ! $isFinal
            && $rule
            && ($isSuperAdmin || $roleSlug === $rule['allowed_role']);

        // Data Detail, lampiran & surat HANYA boleh diinput / diubah oleh
        // Operator Dinsos (dan Super Admin). Kabid & Kadis hanya memeriksa;
        // kalau ada yang kurang, mereka mengembalikan ajuan ke tahap sebelumnya.
        $canEdit = $canAct && ($isSuperAdmin || $roleSlug === 'operator_dinsos');

        $returnLabel = ($canAct && isset($rule['return_role']))
            ? 'Kembalikan ke ' . $this->roleLabel($rule['return_role'])
            : null;

        // ---- TTD digital Kepala Dinas ----
        // Tahap Kadis: tampilkan TTD milik user yang sedang login sebagai pratinjau.
        $butuhTtd = $canAct && ! empty($rule['butuh_ttd']);
        $ttdSaatIni = ($butuhTtd && filled($user->ttd_path) && Storage::disk('public')->exists($user->ttd_path))
            ? $user->ttd_path
            : null;

        // Setelah disetujui: ambil TTD dari user yang tercatat di log persetujuan Kadis.
        $logTtd = $permohonan->logs->first(fn($l) => $l->taskname === self::TASK_TTD_KADIS);
        $ttdKadis = $logTtd ? [
            'path' => $logTtd->user?->ttd_path,
            'nama' => $logTtd->user?->name ?? $logTtd->username,
            'tanggal' => $logTtd->tanggal_proses,
        ] : null;

        return view('admin.kartu-kks.ajuan.show', [
            'data' => $permohonan,
            'statusLabels' => KartuKksPermohonan::statusLabels(),
            'isFinal' => $isFinal,
            'canAct' => $canAct,
            'canEdit' => $canEdit,
            // Lampiran (foto/dokumen) hanya boleh diunggah / diganti oleh
            // Super Admin dan Operator Dinsos, pada tahap yang menjadi gilirannya.
            'canUploadLampiran' => $canEdit,
            'isAdmin' => $isAdmin,
            'nextTaskLabel' => $rule['task_lanjut'] ?? null,
            'lanjutLabel' => $rule['tombol_lanjut'] ?? 'Teruskan',
            'returnLabel' => $returnLabel,
            'butuhTtd' => $butuhTtd,
            'ttdSaatIni' => $ttdSaatIni,
            'ttdKadis' => $ttdKadis,
            'tteAktif' => \App\Support\TteSigner::aktif(),
            'bisaBuatSurat' => ! \App\Support\TteSigner::aktif()
                && in_array($permohonan->status, [
                    KartuKksPermohonan::STATUS_VALIDASI_DINSOS,
                    KartuKksPermohonan::STATUS_SELESAI,
                ], true)
                && ($isSuperAdmin || $roleSlug === 'operator_dinsos'),
        ]);
    }

    /**
     * GET /kartu-kks/{permohonan}/detail (dipanggil via fetch() untuk modal Arsip)
     */
    public function detail(KartuKksPermohonan $permohonan)
    {
        $permohonan->load(['detail', 'lampiran', 'surat', 'logs.user', 'currentRole']);

        return response()->json($permohonan);
    }

    /**
     * PUT /kartu-kks/{permohonan}/detail
     */
    public function updateDetail(Request $request, KartuKksPermohonan $permohonan)
    {
        $this->pastikanWilayah($permohonan);

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isSuperAdmin = $this->isSuperAdmin($user->role);
        $transitions = $this->transitions();

        $isFinal = in_array($permohonan->status, [
            KartuKksPermohonan::STATUS_SELESAI,
            KartuKksPermohonan::STATUS_DITOLAK,
        ], true);

        $rule = $transitions[$permohonan->status] ?? null;

        $canAct = ! $isFinal
            && $rule
            && ($isSuperAdmin || $roleSlug === $rule['allowed_role']);

        // Hanya Operator Dinsos (dan Super Admin) yang boleh mengisi / mengubah
        // Data Detail, Lampiran & Surat. Kabid & Kadis hanya memeriksa.
        $bolehUnggahLampiran = $isSuperAdmin || $roleSlug === 'operator_dinsos';

        if (! $canAct || ! $bolehUnggahLampiran) {
            abort(403, 'Anda tidak berwenang mengedit data ini.');
        }

        $permohonan->loadMissing('lampiran', 'surat');
        $hasScreenshotDtsen = optional($permohonan->lampiran)->screenshot_dtsen;

        $validated = $request->validate([
            // ---- Data Detail — wajib ----
            'nik' => 'required|string|max:20',
            'nama' => 'required|string|max:150',
            'jenis_kelamin' => 'required|string|max:20',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghuchu',
            'status_perkawinan' => 'required|in:Kawin,Belum Kawin,Cerai Hidup,Cerai Mati',
            'no_pkh' => 'required|string|max:50',
            'no_kk' => 'required|string|max:30',
            'no_kartu_kks' => 'required|string|max:30',
            'no_rekening' => 'required|string|max:50',
            'pekerjaan' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'masalah_kartu' => 'required|in:terblokir,rusak,hilang,masa berlaku habis,nama tidak sesuai,keterangan lainnya',

            // ---- Data Detail — opsional ----
            'nomor_kehilangan_polisi' => 'nullable|string|max:100',

            // ---- Lampiran ----
            'scan_ktp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'scan_kartu_keluarga' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'surat_kehilangan_polisi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'scan_kartu_kks' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'screenshot_dtsen' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048',
                function ($attribute, $value, $fail) use ($hasScreenshotDtsen) {
                    if (! $value && ! $hasScreenshotDtsen) {
                        $fail('Screenshot DTSEN wajib diunggah.');
                    }
                },
            ],

            // ---- Surat ----
            'surat_keterangan_dinsos' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // ---- 1. Data Detail ----
        $detailData = collect($validated)->only([
            'nik',
            'nama',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'agama',
            'status_perkawinan',
            'no_pkh',
            'no_kk',
            'no_kartu_kks',
            'no_rekening',
            'pekerjaan',
            'alamat',
            'masalah_kartu',
            'nomor_kehilangan_polisi',
        ])->toArray();

        $permohonan->detail()->updateOrCreate([], $detailData);

        // ---- 2. Lampiran ----
        $lampiranFields = ['scan_ktp', 'scan_kartu_keluarga', 'surat_kehilangan_polisi', 'scan_kartu_kks', 'screenshot_dtsen'];
        $lampiranData = [];
        foreach ($lampiranFields as $field) {
            if ($request->hasFile($field)) {
                $oldPath = optional($permohonan->lampiran)->{$field};
                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
                $lampiranData[$field] = $request->file($field)->store('kartu-kks/lampiran', 'public');
            }
        }
        if (! empty($lampiranData)) {
            $permohonan->lampiran()->updateOrCreate([], $lampiranData);
        }

        // ---- 3. Surat (disesuaikan dengan model; kolom *_at ikut dicatat) ----
        $suratFields = ['surat_keterangan_dinsos'];
        $suratData = [];
        foreach ($suratFields as $field) {
            if ($request->hasFile($field)) {
                $oldPath = optional($permohonan->surat)->{$field};
                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
                $suratData[$field] = $request->file($field)->store('kartu-kks/surat', 'public');
                $suratData[$field . '_at'] = now();
            }
        }
        if (! empty($suratData)) {
            $permohonan->surat()->updateOrCreate([], $suratData);
        }

        return back()->with('success', 'Data Detail berhasil diperbarui.');
    }

    /**
     * POST /kartu-kks/{permohonan}/proses
     */
    public function proses(Request $request, KartuKksPermohonan $permohonan)
    {
        $this->pastikanWilayah($permohonan);

        $request->validate([
            'action' => 'required|in:lanjut,tolak,kembalikan,simpan_catatan',
            'catatan' => 'required|string|max:1000',
            'password' => 'nullable|string|max:255',
        ], [
            'catatan.required' => 'Catatan wajib diisi untuk aksi ini.',
        ]);

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isSuperAdmin = $this->isSuperAdmin($user->role);
        $transitions = $this->transitions();

        $isFinal = in_array($permohonan->status, [
            KartuKksPermohonan::STATUS_SELESAI,
            KartuKksPermohonan::STATUS_DITOLAK,
        ], true);

        if ($isFinal) {
            return back()->with('error', 'Permohonan ini sudah final, tidak bisa diproses lagi.');
        }

        $rule = $transitions[$permohonan->status] ?? null;

        // Hanya super admin yang selalu boleh memproses pada tahap apapun.
        // Role lain harus sesuai role yang berwenang pada tahap ini.
        $canAct = $rule
            && ($isSuperAdmin || $roleSlug === $rule['allowed_role']);

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

                if ($permohonan->status === KartuKksPermohonan::STATUS_DIAJUKAN) {
                    $permohonan->loadMissing('lampiran');

                    if (! optional($permohonan->lampiran)->screenshot_dtsen) {
                        return back()->with('error', 'Screenshot DTSEN wajib diunggah sebelum permohonan diteruskan.');
                    }
                }

                $passphrase = null;

                if (! empty($rule['butuh_ttd'])) {
                    if (! filled($user->ttd_path) || ! Storage::disk('public')->exists($user->ttd_path)) {
                        return back()->with('error', 'Tanda tangan digital belum tersedia. Unggah dulu lewat menu Edit Profil (pojok kanan atas), lalu coba lagi.');
                    }

                    if (\App\Support\TteSigner::aktif()) {
                        $passphrase = $request->input('password');
                        if (blank($passphrase)) {
                            return back()->with('error', 'Password TTE wajib diisi untuk menandatangani.');
                        }
                    }
                }

                try {
                    DB::transaction(function () use ($permohonan, $rule, $user, $request, $passphrase) {
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

                        if (! empty($rule['butuh_ttd'])) {
                            KartuKksSurat::buatDanSimpan($permohonan, $user, $passphrase);
                        }
                    });
                } catch (\Throwable $e) {
                    report($e);

                    return back()->with('error', 'Gagal memproses permohonan: ' . $e->getMessage());
                }

                return back()->with('success', 'Permohonan berhasil diproses ke tahap berikutnya.');
        }
    }

    public function buatSurat(Request $request, KartuKksPermohonan $permohonan)
    {
        $this->pastikanWilayah($permohonan);

        $user = Auth::user();
        abort_unless(
            $this->isSuperAdmin($user->role) || $this->roleSlug($user->role) === 'operator_dinsos',
            403,
            'Anda tidak berwenang membuat surat.'
        );

        if (\App\Support\TteSigner::aktif()) {
            return back()->with('error', 'TTE aktif: surat hanya dibuat saat Kepala Dinas menandatangani.');
        }

        if (! in_array($permohonan->status, [
            KartuKksPermohonan::STATUS_VALIDASI_DINSOS,
            KartuKksPermohonan::STATUS_SELESAI,
        ], true)) {
            return back()->with('error', 'Surat baru bisa dibuat setelah Kepala Dinas menyetujui.');
        }

        $permohonan->loadMissing('logs.user');
        $logTtd = $permohonan->logs->first(fn($l) => $l->taskname === self::TASK_TTD_KADIS);

        // Penandatangan = user yang tercatat di log persetujuan; fallback ke user ber-role kadis.
        $kadis = $logTtd?->user
            ?? \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'kadis'))->first();

        if (! $kadis) {
            return back()->with('error', 'Data Kepala Dinas penandatangan tidak ditemukan.');
        }

        try {
            KartuKksSurat::buatDanSimpan($permohonan, $kadis);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal membuat surat: ' . $e->getMessage());
        }

        $permohonan->logs()->create([
            'tanggal_proses' => now(),
            'user_id' => $user->id,
            'username' => $user->username ?? $user->name,
            'taskname' => 'Surat Keterangan Dibuat Ulang',
            'rolename' => $this->roleLabel($this->roleSlug($user->role) ?? 'operator_dinsos'),
            'catatan' => null,
        ]);

        return back()->with('success', 'Surat Keterangan berhasil dibuat.');
    }
}