<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DtsenPermohonan;
use App\Models\Role;
use App\Support\DtsenNotifikasi;
use App\Support\DtsenSuratGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DtsenController extends Controller
{
    /**
     * Jarak otomatis antara "Tanggal Cetak" dan "Berlaku Sampai" (dalam hari).
     * Nilai ini TIDAK bisa diubah petugas: Berlaku Sampai selalu dihitung server
     * dari Tanggal Cetak + MASA_BERLAKU_HARI.
     */
    public const MASA_BERLAKU_HARI = 2;

    /**
     * Sama seperti logika di CheckRole: role apapun yang namanya mengandung
     * "admin" dianggap admin dan bebas dari pembatasan role di bawah ini.
     *
     * PENTING: method ini TIDAK dipakai lagi untuk menentukan siapa yang
     * boleh menjalankan alur Proses Permohonan (Teruskan/Tolak/
     * Kembalikan/Simpan Catatan) maupun mengedit Data Detail/BANSOS.
     * Untuk itu pakai isSuperAdmin() di bawah, supaya role "admin" biasa
     * tidak ikut mendapat hak proses — hanya super admin (disamakan
     * dengan Kartu KKS & PBI APBD).
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

    /**
     * Role yang benar-benar berwenang menjalankan alur Proses Permohonan
     * serta mengedit Data Detail/BANSOS, tanpa terikat role pada tahap
     * manapun. HANYA super admin — role "admin" biasa TIDAK termasuk di
     * sini, meskipun tetap bisa melihat semua data lewat isAdmin() di
     * tempat lain (mis. daftar Ajuan). Disamakan dengan isSuperAdmin() di
     * KartuKksController / userCanActOn() di PbiApbdController.
     */
    private function isSuperAdmin($role): bool
    {
        if (! $role) {
            return false;
        }

        $slug = $role->slug ?: Str::slug($role->name, '_');
        $slug = strtolower(str_replace(['-', ' '], '_', $slug));

        return in_array($slug, ['super_admin', 'superadmin'], true);
    }

    private function roleSlug($role): ?string
    {
        if (! $role) {
            return null;
        }

        return strtolower(str_replace(['-', ' '], '_', (string) ($role->slug ?: $role->name)));
    }

    /**
     * Label tampilan untuk sebuah role slug.
     */
    private function roleLabel(string $slug): string
    {
        return match ($slug) {
            'operator_dinsos' => 'Operator Dinsos',
            'kabin' => 'Kabin',
            'kadis' => 'Kepala Dinas',
            default => ucfirst(str_replace('_', ' ', $slug)),
        };
    }

    /**
     * Peta alur status -> aksi berikutnya & aksi kembali.
     *   Validasi Dinsos (Operator) -> Diproses Kabin -> Disetujui Kadis -> Selesai
     * (Ditolak dapat terjadi di tahap mana pun.)
     */
    private function transitions(): array
    {
        return [
            DtsenPermohonan::STATUS_VALIDASI_DINSOS => [
                'allowed_role' => 'operator_dinsos',
                'task_lanjut' => 'Diteruskan ke Kabin',
                'next' => DtsenPermohonan::STATUS_DIPROSES_KABIN,
                'next_role' => 'kabin',
            ],
            DtsenPermohonan::STATUS_DIPROSES_KABIN => [
                'allowed_role' => 'kabin',
                'task_lanjut' => 'Disetujui Kabin, diteruskan ke Kadis',
                'next' => DtsenPermohonan::STATUS_DISETUJUI_KADIS,
                'next_role' => 'kadis',
                'return_status' => DtsenPermohonan::STATUS_VALIDASI_DINSOS,
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
     * GET /dtsen/ajuan
     *
     * Daftar permohonan yang masih berjalan (belum Selesai / Ditolak).
     * Filter: tanggal, tahap saat ini, pencarian (termasuk tanggal).
     * Urutan: yang terbaru di-insert tampil paling atas.
     */
    public function ajuan(Request $request)
    {
        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isAdmin = $this->isAdmin($user->role);

        $statusFinal = [
            DtsenPermohonan::STATUS_SELESAI,
            DtsenPermohonan::STATUS_DITOLAK,
        ];

        $query = DtsenPermohonan::with(['currentRole', 'bansos'])
            ->whereNotIn('status', $statusFinal);

        if (! $isAdmin) {
            $query->where(function ($q) use ($user, $roleSlug) {
                $q->where('current_role_id', $user->role_id)
                    ->orWhereHas('currentRole', fn ($r) => $r->where('slug', $roleSlug));
            });
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

        // tanggal_insert bertipe date (tanpa jam), jadi created_at & id dipakai
        // sebagai pembanding supaya ajuan di hari yang sama tetap urut dari yang terbaru.
        $ajuanList = $query
            ->orderByDesc('tanggal_insert')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $statusLabels = DtsenPermohonan::statusLabels();

        return view('admin.dtsen.ajuan.index', [
            'ajuanList' => $ajuanList,
            'statusLabels' => $statusLabels,
            // Dropdown tahap hanya berisi tahap yang masih berjalan
            'statusOptions' => collect($statusLabels)->except($statusFinal)->all(),
        ]);
    }

    /**
     * GET /dtsen/arsip
     *
     * Arsip berisi permohonan yang sudah final: Selesai + Ditolak.
     * Bisa difilter per status lewat query string ?status=selesai|ditolak.
     *
     * Filter "Alasan Cetak" sudah dihapus.
     */
    public function arsip(Request $request)
    {
        $statusArsip = [
            DtsenPermohonan::STATUS_SELESAI,
            DtsenPermohonan::STATUS_DITOLAK,
        ];

        $query = DtsenPermohonan::with('bansos')
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

        $arsipList = $query->orderByDesc('tanggal_insert')->paginate($perPage)->withQueryString();

        $statusLabels = DtsenPermohonan::statusLabels();

        return view('admin.dtsen.arsip.index', [
            'arsipList' => $arsipList,
            'statusLabels' => $statusLabels,
            'statusOptions' => [
                DtsenPermohonan::STATUS_SELESAI => $statusLabels[DtsenPermohonan::STATUS_SELESAI] ?? 'Selesai',
                DtsenPermohonan::STATUS_DITOLAK => $statusLabels[DtsenPermohonan::STATUS_DITOLAK] ?? 'Ditolak',
            ],
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
                // Tampilan Monitoring butuh SELURUH riwayat log,
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
            'logs' => fn ($q) => $q->orderBy('tanggal_proses'),
            'currentRole',
        ]);

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isAdmin = $this->isAdmin($user->role);
        $isSuperAdmin = $this->isSuperAdmin($user->role);
        $transitions = $this->transitions();

        $isFinal = in_array($permohonan->status, [
            DtsenPermohonan::STATUS_SELESAI,
            DtsenPermohonan::STATUS_DITOLAK,
        ], true);

        $rule = $transitions[$permohonan->status] ?? null;

        // Hanya super admin yang selalu bisa bertindak di tahap manapun
        // selama permohonan belum final. Role "admin" biasa tetap harus
        // sesuai dengan role yang berwenang pada tahap saat ini — sama
        // seperti alur Kartu KKS & PBI APBD.
        $canAct = ! $isFinal
            && $rule
            && ($isSuperAdmin || $roleSlug === $rule['allowed_role']);

        $returnLabel = ($canAct && isset($rule['return_role']))
            ? 'Kembalikan ke ' . $this->roleLabel($rule['return_role'])
            : null;

        return view('admin.dtsen.ajuan.show', [
            'data' => $permohonan,
            'statusLabels' => DtsenPermohonan::statusLabels(),
            'isFinal' => $isFinal,
            'canAct' => $canAct,
            // Dokumen Tambahan hanya boleh diunggah / diganti oleh
            // Super Admin dan Operator Dinsos, pada tahap yang menjadi gilirannya.
            // (Lampiran Scan KTP & Screenshot DTSEN dari warga hanya ditampilkan.)
            'canUploadLampiran' => $canAct && ($isSuperAdmin || $roleSlug === 'operator_dinsos'),
            'isAdmin' => $isAdmin,
            'nextTaskLabel' => $rule['task_lanjut'] ?? null,
            'returnLabel' => $returnLabel,
            'masaBerlakuHari' => self::MASA_BERLAKU_HARI,
            'butuhTtd' => false,
            'tteAktif' => false,
            'bisaBuatSurat' => $permohonan->status === DtsenPermohonan::STATUS_SELESAI
                && $this->bolehBuatSurat($user),
        ]);
    }

    /**
     * GET /dtsen/{permohonan}/detail (dipanggil via fetch() untuk modal Arsip)
     */
    public function detail(DtsenPermohonan $permohonan)
    {
        $permohonan->load(['detail', 'bansos', 'lampiran', 'logs.user', 'currentRole']);

        return response()->json($permohonan);
    }

    /**
     * PUT /dtsen/{permohonan}/detail
     * Mengedit "Data Detail" pemohon (bagian 2 di dokumen kebutuhan).
     *
     * Tanggal lahir pemohon TERKUNCI: kalau sudah tercatat, nilainya tidak bisa diubah petugas
     * (kiriman dari browser diabaikan). Field lain tetap bisa diedit.
     */
    public function updateDetail(Request $request, DtsenPermohonan $permohonan)
    {
        $this->authorizeCurrentStage($permohonan);

        $permohonan->loadMissing('detail');
        $tanggalLahirTerkunci = filled(optional($permohonan->detail)->tanggal_lahir);

        $validated = $request->validate([
            'nik' => 'required|string|max:20',
            'nama' => 'required|string|max:150',
            'no_kk' => 'required|string|max:30',
            'alamat' => 'required|string|max:255',
            'tanggal_lahir' => $tanggalLahirTerkunci ? 'nullable' : 'required|date',
            'alasan_keputusan_cetak' => 'required|string|max:1000',
        ]);

        if ($tanggalLahirTerkunci) {
            unset($validated['tanggal_lahir']);
        }

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
     *
     * "tanggal_cetak" TERKUNCI: tidak diterima dari form. Kalau sudah tercatat, nilainya
     * dipertahankan; kalau belum (simpan pertama), diisi tanggal hari ini oleh server.
     *
     * "berlaku_sampai" juga TIDAK diterima dari form: selalu dihitung server =
     * tanggal_cetak + MASA_BERLAKU_HARI (2 hari), sehingga tidak bisa diubah/dimanipulasi.
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
            'dicetak_oleh' => 'required|string|max:150',
            'upload_lainnya' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'dicetak_oleh.required' => 'Kolom "Dicetak Oleh" wajib diisi.',
        ]);

        // File dipisah dari data biasa; hanya diganti kalau ada file baru
        unset($validated['upload_lainnya']);

        if ($request->hasFile('upload_lainnya')) {
            $permohonan->loadMissing('bansos');

            $oldPath = optional($permohonan->bansos)->upload_lainnya;
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $validated['upload_lainnya'] = $request->file('upload_lainnya')->store('dtsen/bansos', 'public');
        }

        $permohonan->loadMissing('bansos');
        $tanggalCetak = filled(optional($permohonan->bansos)->tanggal_cetak)
            ? Carbon::parse($permohonan->bansos->tanggal_cetak)
            : now();

        $validated['tanggal_cetak'] = $tanggalCetak->toDateString();
        $validated['berlaku_sampai'] = $tanggalCetak->copy()
            ->addDays(self::MASA_BERLAKU_HARI)
            ->toDateString();

        $permohonan->bansos()->updateOrCreate([], $validated);

        return back()->with('success', 'Keterangan Penerima BANSOS berhasil diperbarui.');
    }

    /**
     * PUT /dtsen/{permohonan}/dokumen-tambahan
     * Upload "Dokumen Tambahan" (bebas: gambar maupun berkas dokumen).
     *
     * Lampiran Scan KTP & Screenshot DTSEN berasal dari warga dan TIDAK bisa diubah petugas,
     * jadi method updateLampiran() yang lama sudah dihapus.
     *
     * Jenis berkas dibatasi ke daftar aman (tanpa .php, .exe, .js, dst) karena disimpan di disk publik.
     */
    public function updateDokumenTambahan(Request $request, DtsenPermohonan $permohonan)
    {
        $this->authorizeCurrentStage($permohonan);

        $user = Auth::user();
        abort_unless(
            $this->isSuperAdmin($user->role) || $this->roleSlug($user->role) === 'operator_dinsos',
            403,
            'Hanya Super Admin dan Operator Dinsos yang dapat mengunggah dokumen tambahan.'
        );

        $request->validate([
            'upload_lainnya' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,csv,txt,zip', 'max:5120'],
        ], [
            'upload_lainnya.required' => 'Pilih berkas yang akan diunggah.',
            'upload_lainnya.mimes' => 'Berkas harus berupa gambar, PDF, Word, Excel, CSV, TXT, atau ZIP.',
            'upload_lainnya.max' => 'Ukuran berkas maksimal 5 MB.',
            'upload_lainnya.uploaded' => 'Berkas gagal diunggah. Coba lagi dengan berkas yang lebih kecil.',
        ]);

        $bansos = $permohonan->bansos()->first();

        if (! $bansos) {
            return back()->with('error', 'Simpan Keterangan Penerima BANSOS terlebih dahulu sebelum mengunggah dokumen tambahan.');
        }

        $lama = $bansos->upload_lainnya;
        $baru = $request->file('upload_lainnya')->store('dtsen/dokumen-tambahan', 'public');

        $bansos->forceFill(['upload_lainnya' => $baru])->save();

        if ($lama) {
            Storage::disk('public')->delete($lama);
        }

        return back()->with('success', 'Dokumen tambahan berhasil diunggah.');
    }

    /**
     * Helper aturan akses yang sama dipakai updateDetail(), updateBansos()
     * & updateDokumenTambahan().
     */
    private function authorizeCurrentStage(DtsenPermohonan $permohonan): void
    {
        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isSuperAdmin = $this->isSuperAdmin($user->role);
        $transitions = $this->transitions();

        $isFinal = in_array($permohonan->status, [
            DtsenPermohonan::STATUS_SELESAI,
            DtsenPermohonan::STATUS_DITOLAK,
        ], true);

        $rule = $transitions[$permohonan->status] ?? null;

        // Hanya super admin yang selalu boleh mengedit data selama belum
        // final. Role "admin" biasa tetap harus sesuai role yang
        // berwenang pada tahap saat ini.
        $canAct = ! $isFinal
            && $rule
            && ($isSuperAdmin || $roleSlug === $rule['allowed_role']);

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
            'password' => 'nullable|string|max:255',
        ], [
            'catatan.required' => 'Catatan wajib diisi untuk aksi ini.',
        ]);

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isSuperAdmin = $this->isSuperAdmin($user->role);
        $transitions = $this->transitions();

        $isFinal = in_array($permohonan->status, [
            DtsenPermohonan::STATUS_SELESAI,
            DtsenPermohonan::STATUS_DITOLAK,
        ], true);

        if ($isFinal) {
            return back()->with('error', 'Permohonan ini sudah final, tidak bisa diproses lagi.');
        }

        $rule = $transitions[$permohonan->status] ?? null;

        // Hanya super admin yang selalu boleh memproses (lanjut/tolak/
        // kembalikan/simpan catatan) pada tahap apapun. Role "admin"
        // biasa tetap harus sesuai role yang berwenang pada tahap ini —
        // sama seperti alur Kartu KKS & PBI APBD.
        $canAct = $rule
            && ($isSuperAdmin || $roleSlug === $rule['allowed_role']);

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

                DtsenNotifikasi::keWarga(
                    $permohonan,
                    'Pengajuan DTSEN ditolak',
                    'Pengajuan ' . $permohonan->no_permohonan . ' ditolak. Catatan: ' . $request->catatan
                );

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

                DtsenNotifikasi::keRole(
                    $permohonan,
                    $rule['return_role'],
                    'Pengajuan DTSEN dikembalikan',
                    'Pengajuan ' . $permohonan->no_permohonan . ' dikembalikan ke tahap ' . $this->roleLabel($rule['return_role']) . '. Catatan: ' . $request->catatan
                );

                return back()->with('success', 'Permohonan berhasil dikembalikan.');

            case 'lanjut':
            default:
                if (! $rule) {
                    return back()->with('error', 'Permohonan ini sudah pada tahap akhir dan tidak bisa diproses lagi.');
                }

                // Operator Dinsos wajib melampirkan Screenshot DTSEN sebelum
                // meneruskan berkas dari Validasi Dinsos ke Kabin.
                if ($permohonan->status === DtsenPermohonan::STATUS_VALIDASI_DINSOS) {
                    $permohonan->loadMissing('lampiran');

                    if (! optional($permohonan->lampiran)->screenshot_dtsen) {
                        return back()->with('error', 'Screenshot DTSEN wajib diunggah sebelum permohonan diteruskan.');
                    }
                }

                $buatSurat = false;   // ubah ke: $rule['next'] === DtsenPermohonan::STATUS_SELESAI  saat TTD diaktifkan lagi
                $passphrase = null;

                try {
                    DB::transaction(function () use ($permohonan, $rule, $user, $request, $buatSurat, $passphrase) {
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

                        if ($buatSurat) {
                            DtsenSuratGenerator::buatDanSimpan($permohonan, $user, $passphrase);
                        }
                    });
                } catch (\Throwable $e) {
                    report($e);

                    return back()->with('error', 'Gagal memproses permohonan: ' . $e->getMessage());
                }

                if ($rule['next_role']) {
                    DtsenNotifikasi::keRole(
                        $permohonan,
                        $rule['next_role'],
                        'Pengajuan DTSEN menunggu tindakan Anda',
                        'Pengajuan ' . $permohonan->no_permohonan . ' atas nama ' . $permohonan->nama_pemohon . ' masuk ke tahap ' . $this->roleLabel($rule['next_role']) . '.'
                    );
                }

                if ($rule['next'] === DtsenPermohonan::STATUS_SELESAI) {
                    DtsenNotifikasi::keWarga(
                        $permohonan,
                        'Pengajuan DTSEN selesai',
                        'Pengajuan ' . $permohonan->no_permohonan . ' telah disetujui dan selesai diproses.'
                    );
                }

                return back()->with('success', 'Permohonan berhasil diproses ke tahap berikutnya.');
        }
    }

    public function buatSurat(DtsenPermohonan $permohonan)
    {
        $user = Auth::user();
        abort_unless($this->bolehBuatSurat($user), 403, 'Anda tidak berwenang membuat surat.');

        if ($permohonan->status !== DtsenPermohonan::STATUS_SELESAI) {
            return back()->with('error', 'Surat baru bisa dibuat setelah permohonan selesai.');
        }

        if (! DtsenSuratGenerator::templateTersedia()) {
            return back()->with('error', 'Template "Surat Keterangan DTSEN" belum diunggah di menu Dokumen.');
        }

        $permohonan->loadMissing('logs.user');
        $logKadis = $permohonan->logs->first(fn ($l) => $l->taskname === 'Disetujui Kadis');
        $kadis = $logKadis?->user
            ?? \App\Models\User::whereHas('role', fn ($q) => $q->where('slug', 'kadis'))->first();

        if (! $kadis) {
            return back()->with('error', 'Data Kepala Dinas penandatangan tidak ditemukan.');
        }

        try {
            DtsenSuratGenerator::buatDanSimpan($permohonan, $kadis);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal membuat surat: ' . $e->getMessage());
        }

        $permohonan->logs()->create([
            'tanggal_proses' => now(),
            'user_id' => $user->id,
            'username' => $user->username ?? $user->name,
            'taskname' => 'Surat Keterangan Dibuat',
            'rolename' => $this->roleLabel($this->roleSlug($user->role) ?? 'operator_dinsos'),
            'catatan' => null,
        ]);

        return back()->with('success', 'Surat Keterangan berhasil dibuat.');
    }

    private function bolehBuatSurat($user): bool
    {
        return $this->isSuperAdmin($user->role)
            || in_array($this->roleSlug($user->role), ['operator_dinsos', 'kadis'], true);
    }
}
