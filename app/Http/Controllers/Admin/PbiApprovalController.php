<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbiApbd;
use App\Models\PbiApbdLog;
use App\Models\User;
use App\Support\PbiSuratPengantar;
use Illuminate\Http\Request;
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
        // Lurah hanya melihat ajuan di wilayahnya sendiri
        if ($tahap === 'lurah') {
            $query->untukKelurahan($user);
        }

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
        if ($tahap === 'lurah') {
            $basis->untukKelurahan($user);
        }
        $filter($basis);

        $stat = [
            'total' => (clone $basis)->count(),
            'aktivasi' => (clone $basis)->where('status', 'disetujui')->count(),
            'ditolak' => (clone $basis)->where('status', 'ditolak')->count(),
        ];

        $wilayahBelumDiatur = $tahap === 'lurah' && ! $user->role?->isSuperAdmin() && blank($user->kelurahan);

        return view("admin.pbi-{$tahap}.index", compact('tahap', 'cfg', 'ajuan', 'stat', 'tab', 'user', 'wilayahBelumDiatur'));
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

        if ($tahap === 'lurah') {
            abort_unless(
                PbiApbd::untukKelurahan(Auth::user()->loadMissing('role'))->whereKey($pbiApbd->getKey())->exists(),
                403,
                'Pengajuan ini bukan dari wilayah kelurahan Anda.'
            );
        }

        $canAct = $pbiApbd->status === $cfg['status'];

        // Lurah: surat pengantar otomatis tampil (lengkap dengan TTD) begitu TTD Lurah sudah tersimpan.
        // Dibuat ulang bila belum ada, atau bila TTD Lurah lebih baru daripada surat yang tersimpan.
        if ($tahap === 'lurah' && $canAct) {
            $this->siapkanSuratLurah($pbiApbd, Auth::user());
        }
        $canEdit = $canAct && $cfg['edit'];
        $canDiagnosa = $canAct && $cfg['diagnosa'];

        if ($canAct) {
            $pbiApbd->tandaiDilihat(Auth::user());
        }

        $pbiApbd->load(['anggotaKeluarga', 'logs.user', 'diagnosaLogs.user']);

        $faskesOptions = config('faskes.puskesmas', [])[$pbiApbd->kecamatan] ?? [];

        // Diagnosa = jawaban masyarakat di Master Ground Check (hanya dimuat untuk tahap yang boleh mengubahnya)
        $diagnosaGc = $cfg['diagnosa'] ? $this->diagnosaGc($pbiApbd) : collect();

        return view("admin.pbi-{$tahap}.show", [
            'diagnosaGc' => $diagnosaGc,
            'tahap' => $tahap,
            'cfg' => $cfg,
            'data' => $pbiApbd,
            'canAct' => $canAct,
            'canEdit' => $canEdit,
            'canDiagnosa' => $canDiagnosa,
            'faskesOptions' => $faskesOptions,
            // Jawaban Ground Check masyarakat (isian + foto), ditampilkan juga di halaman tahap lanjut
            'gcJawabans' => $pbiApbd->gcJawabans()->get(),
        ]);
    }

    /** Buat / perbarui surat pengantar untuk tampilan Lurah memakai TTD Lurah yang sedang login. */
    private function siapkanSuratLurah(PbiApbd $pbiApbd, User $user): void
    {
        if (blank($user->ttd_path)) {
            return; // TTD belum diunggah: surat dibuat saat berkas diteruskan
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($user->ttd_path)) {
            return;
        }

        $surat = $pbiApbd->scan_surat_pengantar;
        $perluBuat = blank($surat)
            || ! $disk->exists($surat)
            || $disk->lastModified($user->ttd_path) > $disk->lastModified($surat);

        if (! $perluBuat) {
            return;
        }

        try {
            $info = PbiSuratPengantar::generate($pbiApbd, $user, $user->ttd_path);
            $pbiApbd->refresh();
            if (filled($info)) {
                session()->now('error', $info);
            }
        } catch (\Throwable $e) {
            report($e);
            session()->now('error', 'Surat pengantar belum bisa dibuat (cek storage/logs/laravel.log).');
        }
    }

    /**
     * Hanya tahap Operator (route PUT hanya didaftarkan untuk tahap dengan edit = true).
     * Operator hanya boleh mengisi Desil Nasional; field lain tidak disentuh walau dikirim.
     */
    public function update(Request $request, PbiApbd $pbiApbd)
    {
        $tahap = $this->tahap($request);
        $cfg = PbiApbd::TAHAP[$tahap];

        abort_unless($cfg['edit'] && $pbiApbd->status === $cfg['status'], 403);

        $data = $request->validate([
            'desil_nasional' => ['nullable', 'string', 'max:50'],
        ]);

        $pbiApbd->update($data);

        if ($pbiApbd->wasChanged('desil_nasional')) {
            $this->catat($pbiApbd, 'Mengisi Desil Nasional', 'Desil Nasional: ' . ($data['desil_nasional'] ?: '-'));
        }

        return back()->with('success', 'Desil Nasional berhasil disimpan.');
    }

    public function diagnosa(Request $request, PbiApbd $pbiApbd)
    {
        $tahap = $this->tahap($request);
        $cfg = PbiApbd::TAHAP[$tahap];

        abort_unless($cfg['diagnosa'] && $pbiApbd->status === $cfg['status'], 403);

        $v = $request->validate([
            'diagnosa' => ['required', 'array', 'min:1'],
            'diagnosa.*' => ['required', 'string', 'max:500'],
        ], [
            'diagnosa.required' => 'Diagnosa tidak boleh kosong.',
            'diagnosa.*.required' => 'Diagnosa tidak boleh kosong.',
            'diagnosa.*.max' => 'Diagnosa maksimal 500 karakter.',
        ]);

        // Hanya jawaban diagnosa yang SUDAH diisi masyarakat yang bisa diubah (tidak bisa menambah baru).
        $baris = $this->diagnosaGc($pbiApbd)->filter(fn ($r) => filled($r->jawaban))->keyBy('id');

        $diubah = [];
        foreach ($v['diagnosa'] as $id => $teks) {
            $row = $baris->get((int) $id);
            if (! $row) {
                continue; // bukan baris diagnosa milik ajuan ini / belum diisi masyarakat -> diabaikan
            }

            $teks = trim($teks);
            if ($teks === trim((string) $row->jawaban)) {
                continue;
            }

            $diubah[] = $row->nama_parameter . ': "' . $row->jawaban . '" menjadi "' . $teks . '"';
            $row->update(['jawaban' => $teks]); // menimpa jawaban masyarakat
        }

        if (! $diubah) {
            return back()->with('success', 'Tidak ada perubahan pada diagnosa.');
        }

        $this->catat($pbiApbd, 'Mengubah diagnosa', implode('; ', $diubah));

        return back()->with('success', 'Diagnosa berhasil diperbarui.');
    }

    /**
     * Jawaban Ground Check milik ajuan ini yang berupa isian teks "Diagnosa"
     * (dikenali dari nama parameter di Master Kriteria Ground Check).
     */
    private function diagnosaGc(PbiApbd $m)
    {
        return $m->gcJawabans()
            ->whereNotNull('parameter_id')
            ->where('tipe_input', 'teks')
            ->whereRaw('LOWER(nama_parameter) LIKE ?', ['%diagnosa%'])
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Aksi next / back / reject untuk satu atau banyak data (ids[]).
     * BPJS: next = Aktivasi, reject = Tolak (tidak ada back).
     * Operator: hanya next (verifikasi & teruskan ke Kabid).
     * Hanya data yang statusnya = tahap ini yang diproses.
     */
    public function aksi(Request $request)
    {
        $tahap = $this->tahap($request);
        $cfg = PbiApbd::TAHAP[$tahap];

        // Lurah: tanpa reject (menolak = mengembalikan ke Kelurahan).
        // Operator: hanya meneruskan (tidak bisa mengembalikan / menolak).
        // BPJS: boleh mengembalikan (ke Dinkes).
        $diizinkan = match ($tahap) {
            'bpjs' => ['next', 'back', 'reject'],
            'lurah' => ['next', 'back'],
            'operator' => ['next'],
            default => ['next', 'back', 'reject'],
        };

        $v = $request->validate([
            'aksi' => ['required', Rule::in($diizinkan)],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            // Catatan wajib di semua tahap dan aksi, KECUALI Kabid & BPJS saat meneruskan/aktivasi (opsional).
            'catatan' => [
                Rule::requiredIf(! (in_array($tahap, ['kabid', 'bpjs'], true) && $request->input('aksi') === 'next')),
                'nullable', 'string', 'max:2000',
            ],
            'keterangan_lurah' => ['nullable', 'string', 'max:2000'],
        ], [
            'ids.required' => 'Pilih minimal satu data.',
            'catatan.required' => 'Catatan wajib diisi.',
        ]);

        $user = Auth::user();
        $catatan = $v['catatan'] ?? null;
        $berhasil = 0;
        $dilewati = [];

        $itemsQuery = PbiApbd::whereIn('id', $v['ids'])->where('status', $cfg['status']);
        if ($tahap === 'lurah') {
            $itemsQuery->untukKelurahan($user->loadMissing('role'));
        }
        $items = $itemsQuery->get();
        $infoSurat = [];

        foreach ($items as $m) {
            // Lurah: tanda tangan (TTD tersimpan di akun) disalin ke ajuan lalu disisipkan ke surat pengantar
            if ($tahap === 'lurah' && $v['aksi'] === 'next') {
                $disk = Storage::disk('public');
                if (blank($user->ttd_path) || ! $disk->exists($user->ttd_path)) {
                    $dilewati[] = "{$m->no_registrasi} (TTD Anda belum diunggah)";
                    continue;
                }

                $ttd = 'pbi-apbd/lampiran/ttd-lurah-' . $m->id . '-' . now()->timestamp . '.' . pathinfo($user->ttd_path, PATHINFO_EXTENSION);
                $disk->copy($user->ttd_path, $ttd);
                if ($m->ttd_kelurahan && $disk->exists($m->ttd_kelurahan)) {
                    $disk->delete($m->ttd_kelurahan);
                }
                // Kolom ttd_kelurahan sekarang berisi TTD Lurah (nama kolom dipertahankan)
                $m->update([
                    'ttd_kelurahan' => $ttd,
                    'keterangan_lurah' => $v['keterangan_lurah'] ?? null,
                    'divalidasi_lurah_at' => now(),
                    'divalidasi_lurah_oleh' => $user->id,
                ]);
                $infoSurat[] = PbiSuratPengantar::generate($m->fresh(), $user, $ttd);
            }

            $m->tandaiDilihat($user);

            if ($v['aksi'] === 'next') {
                $next = $m->nextStageKey();
                $task = $next === 'disetujui'
                    ? 'Aktivasi PBI APBD'
                    : 'Meneruskan ke ' . (PbiApbd::STAGES[$next]['label'] ?? $next);
                $m->update(['status' => $next, 'catatan_internal' => $catatan]);
            } elseif ($v['aksi'] === 'back') {
                // Semua tahap mengembalikan langsung ke Kelurahan, kecuali BPJS yang mengembalikan ke Dinkes
                $prev = $tahap === 'bpjs' ? 'dinkes' : 'kelurahan';
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
            $redirect->with('success', trim("{$berhasil} data berhasil diproses. " . implode(' ', array_filter($infoSurat))));
        }
        if ($dilewati) {
            $redirect->with('error', 'Tidak diproses: ' . implode('; ', $dilewati));
        }
        if (! $berhasil && ! $dilewati) {
            $redirect->with('error', 'Tidak ada data yang bisa diproses pada tahap ini.');
        }

        return $redirect;
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