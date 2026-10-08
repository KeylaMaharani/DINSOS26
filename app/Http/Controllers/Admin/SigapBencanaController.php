<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SigapBarang;
use App\Models\SigapLaporan;
use App\Models\SigapStokMutasi;
use App\Services\NomorSuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * SIGAP BENCANA -- Laporan Bantuan Bencana (Perlinsos).
 *
 * Satu tiket melewati: Dilaporkan -> Assessment (Tagana) -> Verifikasi (Perlinsos)
 * -> Proses Barang (Pengurus Barang) -> TTD Kadis (stok berkurang) -> Pengiriman
 * -> BAST (Kelurahan/OPD) -> Penyelesaian -> Selesai.
 *
 * Pola hak akses sama dengan Kartu KKS: Super Admin boleh bertindak di tahap
 * mana pun, role lain hanya di tahap yang menjadi gilirannya.
 */
class SigapBencanaController extends Controller
{
    public function __construct(private NomorSuratService $nomor)
    {
    }

    /* ==================================================================
     |  Helper role & wilayah
     * ================================================================== */

    private function roleSlug($role): ?string
    {
        if (! $role) {
            return null;
        }

        $slug = $role->slug ?: Str::slug($role->name, '_');

        return strtolower(str_replace(['-', ' '], '_', $slug));
    }

    private function isSuperAdmin($role): bool
    {
        return in_array($this->roleSlug($role), ['super_admin', 'superadmin'], true);
    }

    private function roleLabel(string $slug): string
    {
        return match ($slug) {
            'tagana' => 'Tagana',
            'perlinsos' => 'Bidang Perlinsos',
            'pengurus_barang' => 'Pengurus Barang',
            'kadis' => 'Kepala Dinas',
            'kelurahan' => 'Kelurahan',
            default => ucfirst(str_replace('_', ' ', $slug)),
        };
    }

    /** Petugas kelurahan hanya boleh menyentuh tiket dari kelurahannya sendiri. */
    private function pastikanWilayah(SigapLaporan $laporan): void
    {
        $user = Auth::user();

        if ($this->roleSlug($user->role) === 'kelurahan') {
            abort_unless(
                filled($user->kelurahan) && $laporan->kelurahan === $user->kelurahan,
                403,
                'Laporan ini bukan dari wilayah kelurahan Anda.'
            );
        }
    }

    /** Role yang boleh membuat laporan baru (tahap 1). */
    private function bolehMembuatLaporan(): bool
    {
        $role = Auth::user()->role;

        return $this->isSuperAdmin($role)
            || in_array($this->roleSlug($role), ['tagana', 'kelurahan'], true);
    }

    private function bolehKelolaStok(): bool
    {
        $role = Auth::user()->role;

        return $this->isSuperAdmin($role) || $this->roleSlug($role) === 'pengurus_barang';
    }

    /* ==================================================================
     |  Peta alur
     * ================================================================== */

    /**
     * status saat ini => aturan.
     *  allowed_role : role yang berwenang di tahap ini
     *  task_lanjut  : nama aktivitas yang dicatat di log saat "Teruskan"
     *  next/next_role : status & pemegang tiket berikutnya
     *  return_*     : tujuan jika "Kembalikan" (tidak ada = tidak bisa dikembalikan)
     *  bisa_tolak   : "Tolak" hanya boleh SEBELUM barang keluar (stok belum berkurang)
     */
    private function transitions(): array
    {
        return [
            SigapLaporan::STATUS_DILAPORKAN => [
                'allowed_role' => 'tagana',
                'task_lanjut' => 'Assessment & Nota Dinas Tagana',
                'next' => SigapLaporan::STATUS_VERIFIKASI_PERLINSOS,
                'next_role' => 'perlinsos',
                'bisa_tolak' => true,
            ],
            SigapLaporan::STATUS_VERIFIKASI_PERLINSOS => [
                'allowed_role' => 'perlinsos',
                'task_lanjut' => 'Verifikasi & Permohonan Barang',
                'next' => SigapLaporan::STATUS_PROSES_BARANG,
                'next_role' => 'pengurus_barang',
                'return_status' => SigapLaporan::STATUS_DILAPORKAN,
                'return_role' => 'tagana',
                'bisa_tolak' => true,
            ],
            SigapLaporan::STATUS_PROSES_BARANG => [
                'allowed_role' => 'pengurus_barang',
                'task_lanjut' => 'Cek Stok & Surat Pengeluaran Barang',
                'next' => SigapLaporan::STATUS_TTD_KADIS,
                'next_role' => 'kadis',
                'return_status' => SigapLaporan::STATUS_VERIFIKASI_PERLINSOS,
                'return_role' => 'perlinsos',
                'bisa_tolak' => true,
            ],
            SigapLaporan::STATUS_TTD_KADIS => [
                'allowed_role' => 'kadis',
                'task_lanjut' => 'Persetujuan Kadis (Barang Keluar)',
                'next' => SigapLaporan::STATUS_PENGIRIMAN,
                'next_role' => 'perlinsos',
                'return_status' => SigapLaporan::STATUS_PROSES_BARANG,
                'return_role' => 'pengurus_barang',
                'bisa_tolak' => true,
            ],
            SigapLaporan::STATUS_PENGIRIMAN => [
                'allowed_role' => 'perlinsos',
                'task_lanjut' => 'Bantuan Dikirim',
                'next' => SigapLaporan::STATUS_BAST,
                'next_role' => 'kelurahan',
                'bisa_tolak' => false,
            ],
            SigapLaporan::STATUS_BAST => [
                'allowed_role' => 'kelurahan',
                'task_lanjut' => 'BAST & Dokumentasi Penerimaan',
                'next' => SigapLaporan::STATUS_PENYELESAIAN,
                'next_role' => 'perlinsos',
                'bisa_tolak' => false,
            ],
            SigapLaporan::STATUS_PENYELESAIAN => [
                'allowed_role' => 'perlinsos',
                'task_lanjut' => 'Tutup Tiket',
                'next' => SigapLaporan::STATUS_SELESAI,
                'next_role' => null,
                'return_status' => SigapLaporan::STATUS_BAST,
                'return_role' => 'kelurahan',
                'bisa_tolak' => false,
            ],
        ];
    }

    private function catat(SigapLaporan $laporan, string $task, string $roleLabel, ?string $catatan): void
    {
        $user = Auth::user();

        $laporan->logs()->create([
            'tanggal_proses' => now(),
            'user_id' => $user->id,
            'username' => $user->username ?? $user->name,
            'taskname' => $task,
            'rolename' => $roleLabel,
            'catatan' => $catatan,
        ]);
    }

    /* ==================================================================
     |  Daftar
     * ================================================================== */

    private function terapkanFilter($query, Request $request): void
    {
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_kejadian', '>=', $request->date('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_kejadian', '<=', $request->date('tanggal_akhir'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('jenis_bencana')) {
            $query->where('jenis_bencana', $request->jenis_bencana);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_tiket', 'like', "%{$s}%")
                    ->orWhere('nomor_surat_laporan', 'like', "%{$s}%")
                    ->orWhere('jenis_bencana', 'like', "%{$s}%")
                    ->orWhere('kelurahan', 'like', "%{$s}%")
                    ->orWhere('alamat', 'like', "%{$s}%");
            });
        }
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('tampilkan', 10);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
    }

    /** GET /sigap-bencana/laporan  -- menu "Laporan Bantuan Bencana" (staf Dinsos) */
    public function index(Request $request)
    {
        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);

        $query = SigapLaporan::with('currentRole');
        $this->terapkanFilter($query, $request);

        // "Giliran saya": hanya tiket yang sedang menunggu role pengguna ini.
        if ($request->boolean('giliran_saya') && ! $this->isSuperAdmin($user->role)) {
            $query->whereHas('currentRole', fn ($q) => $q->where('slug', $roleSlug));
        }

        $list = $query->latest('tanggal_kejadian')->paginate($this->perPage($request))->withQueryString();

        return view('admin.sigap-bencana.index', [
            'list' => $list,
            'statusLabels' => SigapLaporan::statusLabels(),
            'jenisBencana' => SigapLaporan::JENIS_BENCANA,
            'bolehMembuat' => $this->bolehMembuatLaporan(),
        ]);
    }

    /** GET /sigap-bencana/proses  -- menu "Proses Bantuan Kebencanaan" (Kelurahan / OPD wilayah) */
    public function prosesIndex(Request $request)
    {
        $user = Auth::user();

        $query = SigapLaporan::with('currentRole');

        if (! $this->isSuperAdmin($user->role)) {
            $query->when(
                filled($user->kelurahan),
                fn ($q) => $q->where('kelurahan', $user->kelurahan),
                fn ($q) => $q->whereRaw('1 = 0') // wilayah belum diatur -> tidak tampil apa pun
            );
        }

        $this->terapkanFilter($query, $request);

        // Tiket yang menunggu BAST dari kelurahan ini ditandai/ditaruh di atas.
        $list = $query
            ->orderByRaw('status = ? desc', [SigapLaporan::STATUS_BAST])
            ->latest('tanggal_kejadian')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.sigap-bencana.proses', [
            'list' => $list,
            'statusLabels' => SigapLaporan::statusLabels(),
            'jenisBencana' => SigapLaporan::JENIS_BENCANA,
            'bolehMembuat' => $this->bolehMembuatLaporan(),
        ]);
    }

    /* ==================================================================
     |  Tahap 1 -- Buat laporan
     * ================================================================== */

    /** GET /sigap-bencana/buat */
    public function create()
    {
        abort_unless($this->bolehMembuatLaporan(), 403, 'Anda tidak berwenang membuat laporan.');

        return view('admin.sigap-bencana.create', [
            'jenisBencana' => SigapLaporan::JENIS_BENCANA,
            'user' => Auth::user(),
        ]);
    }

    /** POST /sigap-bencana/buat */
    public function store(Request $request)
    {
        abort_unless($this->bolehMembuatLaporan(), 403, 'Anda tidak berwenang membuat laporan.');

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isKelurahan = $roleSlug === 'kelurahan';

        if ($isKelurahan && (blank($user->kelurahan) || blank($user->kecamatan))) {
            return back()->withInput()->with('error', 'Wilayah (kecamatan/kelurahan) akun Anda belum diatur. Hubungi Super Admin.');
        }

        $validated = $request->validate([
            'jenis_bencana' => ['required', Rule::in(SigapLaporan::JENIS_BENCANA)],
            'tanggal_kejadian' => 'required|date|before_or_equal:now',
            'jumlah_korban' => 'required|integer|min:0|max:100000',
            'no_kk' => 'nullable|string|max:255',
            'kerusakan' => 'nullable|string|max:2000',
            'kerugian' => 'nullable|string|max:2000',
            'bantuan_dibutuhkan' => 'required|string|max:2000',
            'kecamatan' => $isKelurahan ? 'nullable' : 'required|string|max:100',
            'kelurahan' => $isKelurahan ? 'nullable' : 'required|string|max:100',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'alamat' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'latitude.required' => 'Titik lokasi wajib ditentukan di peta.',
            'longitude.required' => 'Titik lokasi wajib ditentukan di peta.',
        ]);

        // Petugas kelurahan: wilayah dipaksa dari akunnya, tidak bisa diubah dari form.
        if ($isKelurahan) {
            $validated['kecamatan'] = $user->kecamatan;
            $validated['kelurahan'] = $user->kelurahan;
        }

        $taganaRole = Role::where('slug', 'tagana')->first();

        $laporan = DB::transaction(function () use ($validated, $user, $roleSlug, $taganaRole) {
            $laporan = SigapLaporan::create($validated + [
                'no_tiket' => $this->nomor->tiket(),
                'nomor_surat_laporan' => $this->nomor->surat(NomorSuratService::LAPORAN),
                'user_id' => $user->id,
                'nama_pelapor' => $user->name,
                'peran_pelapor' => $roleSlug,
                'status' => SigapLaporan::STATUS_DILAPORKAN,
                'current_role_id' => $taganaRole?->id,
            ]);

            $this->catat(
                $laporan,
                'Laporan Kejadian Bencana Masuk',
                $this->roleLabel($roleSlug ?? 'pelapor'),
                'Laporan dibuat melalui aplikasi.'
            );

            return $laporan;
        });

        return redirect()->route('sigap.show', $laporan)
            ->with('success', 'Laporan berhasil dibuat dengan nomor tiket ' . $laporan->no_tiket . '.');
    }

    /* ==================================================================
     |  Detail
     * ================================================================== */

    /** GET /sigap-bencana/{laporan} */
    public function show(SigapLaporan $laporan)
    {
        $this->pastikanWilayah($laporan);

        $laporan->load([
            'assessment', 'permohonanBarang', 'pengeluaran', 'bast',
            'items.barang', 'dokumentasi', 'logs', 'currentRole',
        ]);

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isSuperAdmin = $this->isSuperAdmin($user->role);
        $rule = $this->transitions()[$laporan->status] ?? null;
        $isFinal = in_array($laporan->status, SigapLaporan::statusFinal(), true);

        $canAct = ! $isFinal && $rule && ($isSuperAdmin || $roleSlug === $rule['allowed_role']);

        return view('admin.sigap-bencana.show', [
            'data' => $laporan,
            'statusLabels' => SigapLaporan::statusLabels(),
            'isFinal' => $isFinal,
            'canAct' => $canAct,
            'canTolak' => $canAct && ($rule['bisa_tolak'] ?? false),
            'rule' => $rule,
            'nextTaskLabel' => $rule['task_lanjut'] ?? null,
            'returnLabel' => ($canAct && isset($rule['return_role']))
                ? 'Kembalikan ke ' . $this->roleLabel($rule['return_role'])
                : null,
            // Pilihan barang untuk form assessment (hanya dibutuhkan di tahap 'dilaporkan')
            'barangList' => $canAct && $laporan->status === SigapLaporan::STATUS_DILAPORKAN
                ? SigapBarang::aktif()->orderBy('nama')->get()
                : collect(),
            // Stok saat ini, untuk ditampilkan di tahap Perlinsos/Pengurus Barang/Kadis
            'stokMap' => SigapBarang::whereIn('id', $laporan->items->pluck('barang_id'))->pluck('stok', 'id'),
            'bolehEditLokasi' => $canAct && $laporan->status === SigapLaporan::STATUS_DILAPORKAN
                && ($isSuperAdmin || $roleSlug === 'tagana'),
        ]);
    }

    /**
     * PATCH /sigap-bencana/{laporan}/lokasi
     * Dipanggil otomatis dari halaman saat Tagana tiba di lokasi (GPS perangkat),
     * atau saat titik di peta digeser.
     */
    public function updateLokasi(Request $request, SigapLaporan $laporan)
    {
        $this->pastikanWilayah($laporan);

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);

        abort_unless(
            $laporan->status === SigapLaporan::STATUS_DILAPORKAN
                && ($this->isSuperAdmin($user->role) || $roleSlug === 'tagana'),
            403,
            'Lokasi hanya dapat diperbarui oleh Tagana pada tahap assessment.'
        );

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alamat' => 'nullable|string|max:500',
        ]);

        $laporan->update($validated + ['lokasi_diperbarui_at' => now()]);

        $this->catat($laporan, 'Lokasi Diperbarui (Tagana di Lokasi)', $this->roleLabel($roleSlug ?? 'tagana'), null);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'lokasi_diperbarui_at' => $laporan->lokasi_diperbarui_at]);
        }

        return back()->with('success', 'Titik lokasi berhasil diperbarui.');
    }

    /* ==================================================================
     |  Proses tahap (Teruskan / Kembalikan / Tolak / Simpan Catatan)
     * ================================================================== */

    /** POST /sigap-bencana/{laporan}/proses */
    public function aksi(Request $request, SigapLaporan $laporan)
    {
        $this->pastikanWilayah($laporan);

        $request->validate([
            'action' => 'required|in:lanjut,tolak,kembalikan,simpan_catatan',
            'catatan' => 'required|string|max:1000',
        ], [
            'catatan.required' => 'Catatan wajib diisi untuk aksi ini.',
        ]);

        $user = Auth::user();
        $roleSlug = $this->roleSlug($user->role);
        $isSuperAdmin = $this->isSuperAdmin($user->role);

        if (in_array($laporan->status, SigapLaporan::statusFinal(), true)) {
            return back()->with('error', 'Laporan ini sudah final, tidak bisa diproses lagi.');
        }

        $rule = $this->transitions()[$laporan->status] ?? null;

        if (! $rule || ! ($isSuperAdmin || $roleSlug === $rule['allowed_role'])) {
            abort(403, 'Anda tidak berwenang memproses laporan pada tahap ini.');
        }

        $roleLabel = $this->roleLabel($rule['allowed_role']);

        switch ($request->action) {
            case 'simpan_catatan':
                $this->catat($laporan, 'Simpan Catatan', $roleLabel, $request->catatan);

                return back()->with('success', 'Catatan berhasil disimpan.');

            case 'tolak':
                if (! ($rule['bisa_tolak'] ?? false)) {
                    return back()->with('error', 'Laporan tidak bisa ditolak pada tahap ini karena barang sudah keluar.');
                }

                DB::transaction(function () use ($laporan, $request, $roleLabel) {
                    $laporan->update(['status' => SigapLaporan::STATUS_DITOLAK, 'current_role_id' => null]);
                    $this->catat($laporan, 'Ditolak', $roleLabel, $request->catatan);
                });

                return back()->with('success', 'Laporan telah ditolak.');

            case 'kembalikan':
                if (! isset($rule['return_status'], $rule['return_role'])) {
                    return back()->with('error', 'Laporan pada tahap ini tidak bisa dikembalikan.');
                }

                DB::transaction(function () use ($laporan, $rule, $request, $roleLabel) {
                    $returnRole = Role::where('slug', $rule['return_role'])->first();

                    $laporan->update([
                        'status' => $rule['return_status'],
                        'current_role_id' => $returnRole?->id,
                    ]);

                    $this->catat(
                        $laporan,
                        'Dikembalikan ke ' . $this->roleLabel($rule['return_role']),
                        $roleLabel,
                        $request->catatan
                    );
                });

                return back()->with('success', 'Laporan berhasil dikembalikan.');

            case 'lanjut':
            default:
                // 1) validasi isian khusus tahap ini (di luar transaksi)
                $data = $this->validasiTahap($request, $laporan);

                // 2) jalankan: simpan isian tahap + pindah status + log, satu transaksi
                try {
                    DB::transaction(function () use ($request, $laporan, $rule, $user, $roleLabel, $data) {
                        $this->jalankanTahap($laporan, $request, $user, $data);

                        $nextRole = $rule['next_role'] ? Role::where('slug', $rule['next_role'])->first() : null;

                        $laporan->update([
                            'status' => $rule['next'],
                            'current_role_id' => $nextRole?->id,
                            'ditutup_at' => $rule['next'] === SigapLaporan::STATUS_SELESAI ? now() : null,
                        ]);

                        $this->catat($laporan, $rule['task_lanjut'], $roleLabel, $request->catatan);
                    });
                } catch (RuntimeException $e) {
                    // mis. stok tidak cukup: semua perubahan di atas otomatis dibatalkan
                    return back()->withInput()->with('error', $e->getMessage());
                }

                return back()->with('success', 'Laporan berhasil diproses ke tahap berikutnya.');
        }
    }

    /** Validasi isian yang wajib ada sebelum tahap bisa diteruskan. */
    private function validasiTahap(Request $request, SigapLaporan $laporan): array
    {
        switch ($laporan->status) {
            case SigapLaporan::STATUS_DILAPORKAN:
                $data = $request->validate([
                    'nama_petugas' => 'required|string|max:255',
                    'tanggal_assessment' => 'required|date|before_or_equal:today',
                    'hasil_assessment' => 'required|string|max:3000',
                    'items' => 'required|array|min:1',
                    'items.*.barang_id' => 'required|integer|exists:sigap_barang,id',
                    'items.*.jumlah' => 'required|integer|min:1|max:1000000',
                ], [
                    'items.required' => 'Barang yang dibutuhkan wajib diisi minimal satu.',
                ]);

                $ids = collect($data['items'])->pluck('barang_id');
                if ($ids->count() !== $ids->unique()->count()) {
                    throw ValidationException::withMessages(['items' => 'Satu barang tidak boleh dipilih lebih dari sekali.']);
                }

                return $data;

            case SigapLaporan::STATUS_VERIFIKASI_PERLINSOS:
                $rules = ['hasil_verifikasi' => 'required|string|max:3000'];
                foreach ($laporan->items as $item) {
                    $rules["jumlah_disetujui.{$item->id}"] = 'required|integer|min:0|max:1000000';
                }
                $data = $request->validate($rules);

                if (collect($data['jumlah_disetujui'] ?? [])->sum() < 1) {
                    throw ValidationException::withMessages([
                        'jumlah_disetujui' => 'Minimal satu barang harus disetujui lebih dari 0.',
                    ]);
                }

                return $data;

            case SigapLaporan::STATUS_BAST:
                $laporan->loadMissing('dokumentasi');

                return $request->validate([
                    'tanggal_terima' => 'required|date|before_or_equal:today',
                    'nama_penerima' => 'required|string|max:150',
                    'jabatan_penerima' => 'required|string|max:150',
                    'catatan_bast' => 'nullable|string|max:1000',
                    'dokumentasi' => [Rule::requiredIf($laporan->dokumentasi->isEmpty()), 'nullable', 'array', 'max:10'],
                    'dokumentasi.*' => 'image|mimes:jpg,jpeg,png|max:4096',
                ], [
                    'dokumentasi.required' => 'Foto dokumentasi penerimaan wajib diunggah minimal satu.',
                ]);

            default:
                return [];
        }
    }

    /** Menyimpan isian khusus tiap tahap. Dipanggil di dalam transaksi. */
    private function jalankanTahap(SigapLaporan $laporan, Request $request, $user, array $data): void
    {
        switch ($laporan->status) {

            // ---- Tahap 2: Assessment Tagana -> Nota Dinas ----
            case SigapLaporan::STATUS_DILAPORKAN:
                $assessment = $laporan->assessment()->updateOrCreate([], [
                    'petugas_id' => $user->id,
                    'nama_petugas' => $data['nama_petugas'],
                    'tanggal_assessment' => $data['tanggal_assessment'],
                    'hasil_assessment' => $data['hasil_assessment'],
                    'ttd_tagana_at' => now(),
                ]);

                if (blank($assessment->nomor_nota_dinas)) {
                    $assessment->update(['nomor_nota_dinas' => $this->nomor->surat(NomorSuratService::NOTA_DINAS)]);
                }

                $barang = SigapBarang::aktif()
                    ->whereIn('id', collect($data['items'])->pluck('barang_id'))
                    ->get()
                    ->keyBy('id');

                $laporan->items()->delete();
                foreach ($data['items'] as $row) {
                    $b = $barang->get((int) $row['barang_id']);
                    if (! $b) {
                        throw new RuntimeException('Ada barang yang sudah tidak aktif. Muat ulang halaman lalu pilih barang lagi.');
                    }

                    $laporan->items()->create([
                        'barang_id' => $b->id,
                        'nama_barang' => $b->nama,
                        'satuan' => $b->satuan,
                        'jumlah_dibutuhkan' => (int) $row['jumlah'],
                    ]);
                }
                break;

            // ---- Tahap 3: Verifikasi Perlinsos + Permohonan Barang ----
            case SigapLaporan::STATUS_VERIFIKASI_PERLINSOS:
                foreach ($laporan->items as $item) {
                    $item->update(['jumlah_disetujui' => (int) $data['jumlah_disetujui'][$item->id]]);
                }

                $pb = $laporan->permohonanBarang()->updateOrCreate([], [
                    'tanggal' => now()->toDateString(),
                    'verifikator_id' => $user->id,
                    'nama_verifikator' => $user->name,
                    'hasil_verifikasi' => $data['hasil_verifikasi'],
                    'ttd_perlinsos_at' => now(),
                ]);

                if (blank($pb->nomor_surat)) {
                    $pb->update(['nomor_surat' => $this->nomor->surat(NomorSuratService::PERMOHONAN)]);
                }
                break;

            // ---- Tahap 4: Pengurus Barang cek stok + Surat Pengeluaran ----
            case SigapLaporan::STATUS_PROSES_BARANG:
                $this->pastikanStokCukup($laporan, false);

                $sp = $laporan->pengeluaran()->updateOrCreate([], [
                    'tanggal' => now()->toDateString(),
                    'pengurus_barang_id' => $user->id,
                    'nama_pengurus_barang' => $user->name,
                    'ttd_pengurus_at' => now(),
                ]);

                if (blank($sp->nomor_surat)) {
                    $sp->update(['nomor_surat' => $this->nomor->surat(NomorSuratService::PENGELUARAN)]);
                }
                break;

            // ---- Tahap 5: TTD Kadis -> stok berkurang otomatis ----
            case SigapLaporan::STATUS_TTD_KADIS:
                $this->pastikanStokCukup($laporan, true);

                $laporan->pengeluaran()->update([
                    'kadis_id' => $user->id,
                    'nama_kadis' => $user->name,
                    'ttd_kadis_at' => now(),
                ]);
                break;

            // ---- Tahap 6: BAST + dokumentasi (OPD wilayah) ----
            case SigapLaporan::STATUS_BAST:
                $bast = $laporan->bast()->updateOrCreate([], [
                    'tanggal_terima' => $data['tanggal_terima'],
                    'penerima_id' => $user->id,
                    'nama_penerima' => $data['nama_penerima'],
                    'jabatan_penerima' => $data['jabatan_penerima'],
                    'instansi_penerima' => 'Kelurahan ' . $laporan->kelurahan,
                    'catatan' => $data['catatan_bast'] ?? null,
                    'ttd_penerima_at' => now(),
                ]);

                if (blank($bast->nomor_bast)) {
                    $bast->update(['nomor_bast' => $this->nomor->surat(NomorSuratService::BAST)]);
                }

                foreach ($request->file('dokumentasi', []) as $foto) {
                    $laporan->dokumentasi()->create([
                        'path' => $foto->store('sigap-bencana/dokumentasi', 'public'),
                        'uploaded_by' => $user->id,
                    ]);
                }
                break;
        }
    }

    /**
     * Cek stok semua barang yang disetujui. Kalau $kurangi = true, stok
     * langsung dikurangi (dengan baris barang dikunci) dan dicatat ke mutasi.
     * Dilempar RuntimeException jika ada yang kurang -> transaksi dibatalkan.
     */
    private function pastikanStokCukup(SigapLaporan $laporan, bool $kurangi): void
    {
        $items = $laporan->items()->where('jumlah_disetujui', '>', 0)->get();

        if ($items->isEmpty()) {
            throw new RuntimeException('Belum ada barang yang disetujui Perlinsos pada laporan ini.');
        }

        $barang = SigapBarang::whereIn('id', $items->pluck('barang_id'))
            ->orderBy('id') // urutan tetap supaya dua proses bersamaan tidak saling kunci (deadlock)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $b = $barang->get($item->barang_id);
            $butuh = (int) $item->jumlah_disetujui;

            if (! $b || $b->stok < $butuh) {
                throw new RuntimeException(sprintf(
                    'Stok "%s" tidak cukup (tersedia %d %s, dibutuhkan %d).',
                    $item->nama_barang,
                    $b->stok ?? 0,
                    $item->satuan,
                    $butuh
                ));
            }
        }

        if (! $kurangi) {
            return;
        }

        foreach ($items as $item) {
            $b = $barang->get($item->barang_id);
            $keluar = (int) $item->jumlah_disetujui;
            $saldo = $b->stok - $keluar;

            $b->update(['stok' => $saldo]);

            SigapStokMutasi::create([
                'barang_id' => $b->id,
                'jenis' => 'keluar',
                'jumlah' => $keluar,
                'saldo_setelah' => $saldo,
                'laporan_id' => $laporan->id,
                'keterangan' => 'Bantuan bencana ' . $laporan->no_tiket,
                'user_id' => Auth::id(),
            ]);

            $item->update(['jumlah_keluar' => $keluar]);
        }
    }

    /* ==================================================================
     |  Stok barang
     * ================================================================== */

    /** GET /sigap-bencana/stok */
    public function stokIndex(Request $request)
    {
        $query = SigapBarang::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('nama', 'like', "%{$s}%")->orWhere('kode', 'like', "%{$s}%"));
        }

        return view('admin.sigap-bencana.stok', [
            'barangList' => $query->orderBy('nama')->paginate($this->perPage($request))->withQueryString(),
            'mutasiTerbaru' => SigapStokMutasi::with(['barang', 'user', 'laporan'])->latest()->limit(15)->get(),
            'bolehKelola' => $this->bolehKelolaStok(),
        ]);
    }

    /** POST /sigap-bencana/stok/barang */
    public function stokStoreBarang(Request $request)
    {
        abort_unless($this->bolehKelolaStok(), 403, 'Hanya Pengurus Barang yang dapat mengelola stok.');

        $validated = $request->validate([
            'kode' => 'nullable|string|max:50|unique:sigap_barang,kode',
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:30',
            'stok_awal' => 'required|integer|min:0|max:10000000',
            'stok_minimum' => 'nullable|integer|min:0|max:10000000',
        ]);

        DB::transaction(function () use ($validated) {
            $barang = SigapBarang::create([
                'kode' => $validated['kode'] ?? null,
                'nama' => $validated['nama'],
                'satuan' => $validated['satuan'],
                'stok' => $validated['stok_awal'],
                'stok_minimum' => $validated['stok_minimum'] ?? 0,
            ]);

            if ($validated['stok_awal'] > 0) {
                SigapStokMutasi::create([
                    'barang_id' => $barang->id,
                    'jenis' => 'masuk',
                    'jumlah' => $validated['stok_awal'],
                    'saldo_setelah' => $validated['stok_awal'],
                    'keterangan' => 'Stok awal',
                    'user_id' => Auth::id(),
                ]);
            }
        });

        return back()->with('success', 'Barang berhasil ditambahkan.');
    }

    /** POST /sigap-bencana/stok/barang/{barang}/masuk */
    public function stokMasuk(Request $request, SigapBarang $barang)
    {
        abort_unless($this->bolehKelolaStok(), 403, 'Hanya Pengurus Barang yang dapat mengelola stok.');

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:1|max:10000000',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($barang, $validated) {
            $b = SigapBarang::whereKey($barang->id)->lockForUpdate()->firstOrFail();
            $saldo = $b->stok + $validated['jumlah'];

            $b->update(['stok' => $saldo]);

            SigapStokMutasi::create([
                'barang_id' => $b->id,
                'jenis' => 'masuk',
                'jumlah' => $validated['jumlah'],
                'saldo_setelah' => $saldo,
                'keterangan' => $validated['keterangan'] ?? 'Stok masuk',
                'user_id' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Stok "' . $barang->nama . '" bertambah ' . $validated['jumlah'] . ' ' . $barang->satuan . '.');
    }
}
