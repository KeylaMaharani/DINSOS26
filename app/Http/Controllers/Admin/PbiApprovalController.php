<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbiApbd;
use App\Models\PbiApbdLog;
use App\Support\PbiSuratIsian;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Satu controller, tetapi tiap bagian punya FOLDER VIEW sendiri:
 *   admin/pbi-operator/index.blade.php  &  show.blade.php
 *   admin/pbi-kabid/index.blade.php     &  show.blade.php
 *   admin/pbi-kadis/index.blade.php     &  show.blade.php
 *   admin/pbi-dinkes/index.blade.php    &  show.blade.php
 *   admin/pbi-bpjs/index.blade.php      &  show.blade.php
 * Tahap dibaca dari nama route: pbi-approval.{tahap}.xxx
 * Akses route dibatasi middleware module:pbi-{tahap}.
 */
class PbiApprovalController extends Controller
{
    private function tahap(Request $request): string
    {
        $t = explode('.', (string) $request->route()->getName())[1] ?? '';
        abort_unless(isset(PbiApbd::TAHAP[$t]), 404);

        return $t;
    }

    private function perPage(Request $request): int
    {
        $n = (int) $request->query('tampilkan', 10);

        return in_array($n, [10, 25, 50, 100], true) ? $n : 10;
    }

    public function index(Request $request)
    {
        $tahap = $this->tahap($request);
        $cfg = PbiApbd::TAHAP[$tahap];

        // Filter tanggal & pencarian: dipakai bersama oleh tabel dan kartu ringkasan.
        $filter = function ($query) use ($request) {
            if ($request->filled('tanggal_awal')) {
                $query->whereDate('created_at', '>=', $request->date('tanggal_awal'));
            }
            if ($request->filled('tanggal_akhir')) {
                $query->whereDate('created_at', '<=', $request->date('tanggal_akhir'));
            }
            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(fn ($q) => $q->where('nama_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_registrasi', 'like', "%{$s}%")
                    ->orWhere('nik_kepala_keluarga', 'like', "%{$s}%")
                    ->orWhere('no_kk', 'like', "%{$s}%")
                    ->orWhere('alamat', 'like', "%{$s}%"));
            }
        };

        $tab = $request->query('tab', 'proses') === 'riwayat' ? 'riwayat' : 'proses';

        // Riwayat dibatasi pada log milik role user (kolom role_name, sama seperti saat dicatat).
        // Super Admin tidak punya log tahap sendiri, jadi tidak difilter per role.
        $user = Auth::user()->loadMissing('role');
        $roleName = $user->role?->isSuperAdmin() ? null : ($user->role->name ?? null);

        $query = PbiApbd::with('logs');

        if ($tab === 'proses') {
            // Perlu diproses: berkas yang saat ini berada di tahap ini.
            $query->where('status', $cfg['status']);
        } else {
            // Sudah diteruskan: berkas yang sudah tidak di tahap ini dan pernah diteruskan /
            // dikembalikan / ditolak oleh tahap ini.
            $query->where('status', '!=', $cfg['status'])
                ->whereHas('logs', fn ($l) => $this->logAksi($l, $roleName));
        }
        $filter($query);

        $ajuan = $query->latest()->paginate($this->perPage($request))->withQueryString();

        if ($tab === 'riwayat') {
            // Tanda per baris diambil dari aksi terakhir tahap ini (logs sudah urut terbaru dulu).
            $ajuan->getCollection()->each(function ($m) use ($roleName) {
                $log = $m->logs->first(fn ($l) => $this->cocokAksi($l->task_name)
                    && ($roleName === null || $l->role_name === $roleName));

                $m->tanda_riwayat = $log ? $this->tandaDariTask($log->task_name) : null;
            });
        }

        // Kartu ringkasan: hanya menghitung data yang bisa tampil di daftar tahap ini
        // (tab Proses + tab Riwayat), ikut filter tanggal & pencarian.
        $basis = PbiApbd::query()->where(function ($q) use ($cfg, $roleName) {
            $q->where('status', $cfg['status'])
                ->orWhere(fn ($r) => $r->where('status', '!=', $cfg['status'])
                    ->whereHas('logs', fn ($l) => $this->logAksi($l, $roleName)));
        });
        $filter($basis);

        $stat = [
            'total' => (clone $basis)->count(),
            'aktivasi' => (clone $basis)->where('status', 'disetujui')->count(),
            'ditolak' => (clone $basis)->where('status', 'ditolak')->count(),
        ];

        return view("admin.pbi-{$tahap}.index", compact('tahap', 'cfg', 'ajuan', 'stat', 'tab'));
    }

    /** Awalan task_name pada log yang menandai tahap meneruskan / mengembalikan / menolak. */
    private const AKSI_TASK = ['Meneruskan ke', 'Mengembalikan ke', 'Aktivasi', 'Menolak', 'Tolak'];

    private function cocokAksi(?string $task): bool
    {
        return $task !== null && Str::startsWith($task, self::AKSI_TASK);
    }

    private function tandaDariTask(string $task): string
    {
        if (str_starts_with($task, 'Mengembalikan')) {
            return 'dikembalikan';
        }
        if (str_starts_with($task, 'Menolak') || str_starts_with($task, 'Tolak')) {
            return 'ditolak';
        }

        if (str_starts_with($task, 'Aktivasi')) {
            return 'diaktivasi'; // hanya BPJS (tahap terakhir)
        }

        return 'diteruskan'; // Meneruskan ke ...
    }

    /** Batasi query log ke aksi tahap (dan role user bila diketahui). */
    private function logAksi($logQuery, ?string $roleName)
    {
        return $logQuery
            ->where(function ($w) {
                foreach (self::AKSI_TASK as $awalan) {
                    $w->orWhere('task_name', 'like', $awalan . '%');
                }
            })
            ->when($roleName, fn ($q) => $q->where('role_name', $roleName));
    }

    public function show(Request $request, PbiApbd $pbiApbd)
    {
        $tahap = $this->tahap($request);
        $cfg = PbiApbd::TAHAP[$tahap];

        $canAct = $pbiApbd->status === $cfg['status'];
        $canEdit = $canAct && $cfg['edit'];
        $canDiagnosa = $canAct && $cfg['diagnosa'];

        if ($canAct) {
            $pbiApbd->tandaiDilihat(Auth::user());
        }

        $pbiApbd->load(['anggotaKeluarga', 'logs.user', 'diagnosaLogs.user']);

        $faskesOptions = config('faskes.puskesmas', [])[$pbiApbd->kecamatan] ?? [];

        return view("admin.pbi-{$tahap}.show", [
            'tahap' => $tahap,
            'cfg' => $cfg,
            'data' => $pbiApbd,
            'canAct' => $canAct,
            'canEdit' => $canEdit,
            'canDiagnosa' => $canDiagnosa,
            'faskesOptions' => $faskesOptions,
        ]);
    }

    /** Hanya tahap Operator (route PUT hanya didaftarkan untuk tahap dengan edit = true). */
    public function update(Request $request, PbiApbd $pbiApbd)
    {
        $tahap = $this->tahap($request);
        $cfg = PbiApbd::TAHAP[$tahap];

        abort_unless($cfg['edit'] && $pbiApbd->status === $cfg['status'], 403);

        // Samakan penulisan dengan config/wilayah.php (huruf besar) sebelum divalidasi
        $request->merge([
            'kecamatan' => strtoupper(trim((string) $request->input('kecamatan'))),
            'desa_kelurahan' => strtoupper(trim((string) $request->input('desa_kelurahan'))),
        ]);

        $wilayah = config('wilayah.kecamatan', []);

        // Data lama yang tidak ada di config tetap boleh dipertahankan (kecamatan/kelurahan tersimpan)
        $kecLama = strtoupper(trim((string) $pbiApbd->kecamatan));
        $kelLama = strtoupper(trim((string) $pbiApbd->desa_kelurahan));
        if ($kecLama !== '') {
            $wilayah[$kecLama] = $wilayah[$kecLama] ?? [];
            if ($kelLama !== '' && ! in_array($kelLama, $wilayah[$kecLama], true)) {
                $wilayah[$kecLama][] = $kelLama;
            }
        }

        $data = $request->validate([
            'nama_kepala_keluarga' => ['required', 'string', 'max:255'],
            'no_kk' => ['required', 'string', 'max:20'],
            'nik_kepala_keluarga' => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string', 'max:500'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'kecamatan' => ['required', 'string', Rule::in(array_keys($wilayah))],
            'desa_kelurahan' => ['required', 'string', function ($attr, $value, $fail) use ($request, $wilayah) {
                if (! in_array($value, $wilayah[$request->input('kecamatan')] ?? [], true)) {
                    $fail('Desa/Kelurahan tidak sesuai dengan kecamatan yang dipilih.');
                }
            }],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'desil_nasional' => ['nullable', 'string', 'max:50'],
            'nama_faskes' => ['nullable', 'string', 'max:150'],
            'kelengkapan_kekurangan_berkas' => ['nullable', 'string', 'max:2000'],
            'catatan_berkas' => ['nullable', 'string', 'max:2000'],
            // Suket boleh PDF, Word (DOC/DOCX), atau gambar
            'surat_keterangan' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:4096'],
        ], [
            'kecamatan.in' => 'Kecamatan tidak valid.',
            'surat_keterangan.max' => 'Ukuran suket maksimal 4 MB.',
            'surat_keterangan.mimes' => 'Suket harus berformat PDF, Word (DOC/DOCX), JPG, atau PNG.',
        ]);

        unset($data['surat_keterangan']);

        $suketBaru = null;
        if ($request->hasFile('surat_keterangan')) {
            if ($pbiApbd->surat_keterangan) {
                Storage::disk('public')->delete($pbiApbd->surat_keterangan);
            }
            // Disimpan dengan nama asli yang terbaca (acak6__nama-asli.ext)
            $data['surat_keterangan'] = $this->simpanDenganNama($request->file('surat_keterangan'), 'pbi-suket');
            $suketBaru = $data['surat_keterangan'];
        }

        $pbiApbd->update($data);

        $diubah = array_keys(array_diff_key($pbiApbd->getChanges(), ['updated_at' => 1]));

        if ($diubah) {
            $this->catat($pbiApbd, 'Mengubah data pemohon', 'Kolom diubah: ' . implode(', ', $diubah));
        }

        // Isi placeholder ${...} pada suket (.docx) memakai data TERBARU (setelah update di atas).
        // Nomor surat otomatis = No. Registrasi; isian lain diambil dari data pengajuan & akun user.
        $info = PbiSuratIsian::isiBerkas($pbiApbd->fresh('anggotaKeluarga'), $suketBaru, Auth::user());

        return back()->with('success', trim('Data berhasil disimpan. ' . $info));
    }

    public function diagnosa(Request $request, PbiApbd $pbiApbd)
    {
        $tahap = $this->tahap($request);
        $cfg = PbiApbd::TAHAP[$tahap];

        abort_unless($cfg['diagnosa'] && $pbiApbd->status === $cfg['status'], 403);

        $v = $request->validate([
            'diagnosa' => ['required', 'string', 'max:2000'],
        ], ['diagnosa.required' => 'Diagnosa tidak boleh kosong.']);

        $user = Auth::user();

        $pbiApbd->diagnosaLogs()->create([
            'user_id' => $user->id,
            'username' => $user->name,
            'role_name' => $user->role->name ?? '-',
            'diagnosa' => $v['diagnosa'],
        ]);
        $pbiApbd->update(['diagnosa' => $v['diagnosa']]);

        return back()->with('success', 'Diagnosa baru berhasil ditambahkan.');
    }

    /**
     * Aksi next / back / reject untuk satu atau banyak data (ids[]).
     * BPJS: next = Aktivasi, reject = Tolak (tidak ada back).
     * Hanya data yang statusnya = tahap ini yang diproses.
     */
    public function aksi(Request $request)
    {
        $tahap = $this->tahap($request);
        $cfg = PbiApbd::TAHAP[$tahap];

        $diizinkan = $tahap === 'bpjs' ? ['next', 'reject'] : ['next', 'back', 'reject'];

        $v = $request->validate([
            'aksi' => ['required', Rule::in($diizinkan)],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            // Catatan wajib untuk semua tahap dan semua aksi (Teruskan / Aktivasi / Back / Reject / Tolak)
            'catatan' => ['required', 'string', 'max:2000'],
        ], [
            'ids.required' => 'Pilih minimal satu data.',
            'catatan.required' => 'Catatan wajib diisi.',
        ]);

        $user = Auth::user();
        $catatan = $v['catatan'] ?? null;
        $berhasil = 0;
        $dilewati = [];

        $items = PbiApbd::whereIn('id', $v['ids'])->where('status', $cfg['status'])->get();

        foreach ($items as $m) {
            if ($v['aksi'] === 'next' && $tahap === 'operator' && blank($m->surat_keterangan)) {
                $dilewati[] = "{$m->no_registrasi} (suket belum diunggah)";
                continue;
            }

            $m->tandaiDilihat($user);

            if ($v['aksi'] === 'next') {
                $next = $m->nextStageKey();
                $task = $next === 'disetujui'
                    ? 'Aktivasi PBI APBD'
                    : 'Meneruskan ke ' . (PbiApbd::STAGES[$next]['label'] ?? $next);
                $m->update(['status' => $next, 'catatan_internal' => $catatan]);
            } elseif ($v['aksi'] === 'back') {
                $prev = $m->previousStageKey();
                if (! $prev) {
                    $dilewati[] = "{$m->no_registrasi} (tidak bisa dikembalikan)";
                    continue;
                }
                $task = 'Mengembalikan ke ' . (PbiApbd::STAGES[$prev]['label'] ?? $prev);
                $m->update(['status' => $prev, 'catatan_internal' => $catatan]
                    + ($prev === 'kelurahan' ? ['sudah_diverifikasi_kelurahan' => false] : []));
            } else {
                $task = $tahap === 'bpjs' ? 'Tolak (BPJS)' : 'Menolak permohonan';
                $m->update(['status' => 'ditolak', 'catatan_internal' => $catatan]);
            }

            $this->catat($m, $task, $catatan);
            $berhasil++;
        }

        $redirect = redirect()->route("pbi-approval.{$tahap}.index");

        if ($berhasil) {
            $redirect->with('success', "{$berhasil} data berhasil diproses.");
        }
        if ($dilewati) {
            $redirect->with('error', 'Tidak diproses: ' . implode('; ', $dilewati));
        }
        if (! $berhasil && ! $dilewati) {
            $redirect->with('error', 'Tidak ada data yang bisa diproses pada tahap ini.');
        }

        return $redirect;
    }

    /**
     * Simpan file ke disk public dengan nama asli yang tetap terbaca.
     * Format: "acak6__nama-asli.ext". Komponen <x-lampiran-preview> menampilkan bagian setelah "__".
     */
    private function simpanDenganNama(UploadedFile $file, string $folder): string
    {
        $nama = Str::limit(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)), 60, '');
        $nama = $nama !== '' ? $nama : 'berkas';
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        return $file->storeAs($folder, Str::random(6) . '__' . $nama . '.' . $ext, 'public');
    }

    private function catat(PbiApbd $m, string $task, ?string $catatan): void
    {
        $user = Auth::user();

        PbiApbdLog::create([
            'pbi_apbd_id' => $m->id,
            'user_id' => $user->id,
            'username' => $user->name,
            'role_name' => $user->role->name ?? '-',
            'task_name' => $task,
            'catatan' => $catatan,
        ]);
    }
}
