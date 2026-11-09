<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbiApbd;
use App\Models\PbiApbdLog;
use App\Support\PbiGcForm;
use App\Support\PbiSuratPengantar;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Halaman Petugas Kelurahan untuk modul PBI APBD.
 * Alur: Masyarakat isi data -> Kelurahan verifikasi + lampiran lapangan -> Lurah (TTD) -> Operator Dinsos -> Kabid -> Kadis.
 * Kelurahan HANYA melihat jawaban (tanpa indeks/bobot/skor).
 * Daftar pertanyaan yang dihitung = yang tampil di form masyarakat (Master Kriteria Ground Check).
 */
class PbiKelurahanController extends Controller
{
    /** Diunggah MASYARAKAT. Kelurahan hanya menandai sesuai / tidak sesuai (kecuali yang opsional tanpa verifikasi). */
    public const LAMPIRAN_MASYARAKAT = [
        'scan_ktp' => ['Scan KTP', true, 'jpg,jpeg,png,pdf'],
        'scan_kk' => ['Scan Kartu Keluarga', true, 'jpg,jpeg,png,pdf'],
        'surat_rawat_inap' => ['Surat Ket. Rawat Inap (jika ada)', false, 'jpg,jpeg,png,pdf'],
        'screenshot_pembaharuan_desil' => ['Screenshot Pembaharuan Desil (opsional)', false, 'jpg,jpeg,png'],
    ];

    /** Berkas masyarakat yang dicek Kelurahan (sesuai / tidak sesuai). */
    public const VERIFIKASI = ['scan_ktp', 'scan_kk', 'surat_rawat_inap'];

    /** Diunggah KELURAHAN (hasil verifikasi lapangan). field => [label, wajib, mimes, hanya kamera] */
    public const LAMPIRAN_KELURAHAN = [
        'foto_selfie_ktp' => ['Foto Selfie dengan KTP', true, 'jpg,jpeg,png', true],
        'foto_rumah' => ['Foto Rumah Pemohon', true, 'jpg,jpeg,png', false],
        'foto_kamar_mandi' => ['Foto Kamar Mandi', true, 'jpg,jpeg,png', false],
        'screenshot_dtsen' => ['Screenshot DTSEN', true, 'jpg,jpeg,png', false],
    ];

    /** Semua lampiran (dipakai tampilan read-only). field => [label, wajib, mimes, ...] */
    public const LAMPIRAN = self::LAMPIRAN_MASYARAKAT + self::LAMPIRAN_KELURAHAN;

    public function index(Request $request)
    {
        $user = Auth::user()->loadMissing('role');
        $tab = $request->query('tab', 'proses') === 'riwayat' ? 'riwayat' : 'proses';

        // Hitungan di daftar = isian wajib yang selalu berlaku (alias tetap jawabans_count agar view tidak berubah).
        $idTetap = PbiGcForm::idTetap();

        $query = PbiApbd::query()->untukKelurahan($user)->withCount([
            'gcJawabans as jawabans_count' => fn ($q) => $q->whereNotNull('jawaban')->whereIn('parameter_id', $idTetap),
        ]);

        // Tab "proses" hanya berisi pengajuan sungguhan: draft warga (data_diisi_at kosong) tidak ikut.
        $tab === 'proses'
            ? $query->where('status', 'kelurahan')->whereNotNull('data_diisi_at')
            : $query->where('status', '!=', 'kelurahan');

        // Filter tanggal permohonan (created_at) & pencarian: dipakai bersama oleh tabel dan kartu ringkasan.
        $filter = function ($q) use ($request) {
            if ($request->filled('tanggal_awal')) {
                $q->whereDate('created_at', '>=', $request->tanggal_awal);
            }
            if ($request->filled('tanggal_akhir')) {
                $q->whereDate('created_at', '<=', $request->tanggal_akhir);
            }
            if ($request->filled('search')) {
                $s = $request->search;
                $q->where(function ($w) use ($s) {
                    $w->where('nama_kepala_keluarga', 'like', "%{$s}%")
                        ->orWhere('no_registrasi', 'like', "%{$s}%")
                        ->orWhere('nik_kepala_keluarga', 'like', "%{$s}%")
                        ->orWhere('no_kk', 'like', "%{$s}%");
                });
            }
        };
        $filter($query);

        // Jumlah baris per halaman (hanya nilai yang tersedia di dropdown)
        $perPage = (int) $request->query('tampilkan', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $ajuan = $query->latest()->paginate($perPage)->withQueryString();
        $totalParameter = count($idTetap);
        $wilayahBelumDiatur = ! $user->role?->isSuperAdmin() && blank($user->kelurahan);

        // Kartu ringkasan: hanya data yang bisa tampil di daftar (wilayah petugas, tab Proses + Riwayat,
        // draft warga tidak ikut), ikut filter tanggal & pencarian.
        $basis = PbiApbd::query()->untukKelurahan($user)
            ->where(fn ($q) => $q->where('status', '!=', 'kelurahan')->orWhereNotNull('data_diisi_at'));
        $filter($basis);
        $stat = [
            'total' => (clone $basis)->count(),
            'aktivasi' => (clone $basis)->where('status', 'disetujui')->count(),
            'ditolak' => (clone $basis)->where('status', 'ditolak')->count(),
        ];

        return view('admin.pbi-kelurahan.index', compact('ajuan', 'tab', 'totalParameter', 'wilayahBelumDiatur', 'user', 'stat'));
    }

    public function show(PbiApbd $pbiApbd)
    {
        $this->pastikanBerwenang($pbiApbd);

        // Tandai sudah dibuka oleh user ini pada tahap saat ini (badge notifikasi berkurang)
        $pbiApbd->tandaiDilihat(Auth::user());

        $pbiApbd->load(['anggotaKeluarga', 'logs']);
        $kelengkapan = PbiGcForm::kelengkapan($pbiApbd);

        return view('admin.pbi-kelurahan.show', [
            'data' => $pbiApbd,
            'lampiran' => self::LAMPIRAN,
            'lampiranMasyarakat' => self::LAMPIRAN_MASYARAKAT,
            'lampiranKelurahan' => self::LAMPIRAN_KELURAHAN,
            'fieldVerifikasi' => self::VERIFIKASI,
            'totalParameter' => $kelengkapan['total'],
            'terjawab' => $kelengkapan['terjawab'],
            'gcJawabans' => $pbiApbd->gcJawabans()->get(),
            // id parameter yang sedang tampil di halaman masyarakat (untuk menandai yang disembunyikan)
            'idAktif' => PbiGcForm::parameters()->pluck('id')->all(),
        ]);
    }

    public function aksi(Request $request, PbiApbd $pbiApbd)
    {
        $this->pastikanBerwenang($pbiApbd);

        if ($pbiApbd->status !== 'kelurahan') {
            return redirect()->route('pbi-kelurahan.show', $pbiApbd)
                ->with('error', 'Berkas ini sudah tidak berada di Kelurahan.');
        }

        // Apa pun tombolnya, berkas dianggap sudah ditangani user ini -> badge notifikasi berkurang.
        // Dipanggil SEBELUM status berubah; tahap berikutnya otomatis jadi notifikasi baru (PbiApbd::booted()).
        $pbiApbd->tandaiDilihat(Auth::user());

        $rules = [
            'aksi' => ['required', Rule::in(['simpan', 'validasi', 'kembalikan', 'tolak'])],
            'catatan' => [
                Rule::requiredIf(in_array($request->input('aksi'), ['kembalikan', 'tolak'], true)),
                'nullable', 'string', 'max:2000',
            ],
            'verifikasi' => ['nullable', 'array'],
        ];
        foreach (self::VERIFIKASI as $field) {
            $rules["verifikasi.{$field}"] = ['nullable', Rule::in(['sesuai', 'tidak_sesuai'])];
        }
        // Hanya lampiran milik Kelurahan yang boleh diunggah di sini
        foreach (self::LAMPIRAN_KELURAHAN as $field => [$label, $wajib, $mimes]) {
            $rules[$field] = ['nullable', 'file', "mimes:{$mimes}", 'max:4096'];
        }

        $request->validate($rules, [
            'catatan.required' => 'Catatan wajib diisi untuk aksi ini.',
            'aksi.in' => 'Aksi tidak dikenali.',
            'verifikasi.*.in' => 'Pilihan verifikasi tidak valid.',
            '*.mimes' => 'Format file tidak didukung.',
            '*.max' => 'Ukuran file maksimal 4 MB.',
        ]);

        $user = Auth::user();
        $aksi = $request->input('aksi');
        $catatan = $request->input('catatan');

        // ---- Simpan hasil cek berkas masyarakat (berlaku untuk semua aksi kecuali tolak) ----
        if ($aksi !== 'tolak') {
            $hasil = $pbiApbd->verifikasi_berkas ?? [];
            foreach (self::VERIFIKASI as $field) {
                $nilai = $request->input("verifikasi.{$field}");
                if ($nilai !== null && $pbiApbd->{$field}) {
                    $hasil[$field] = $nilai;
                }
            }
            $pbiApbd->update(['verifikasi_berkas' => $hasil]);
        }

        // ---- Kembalikan ke masyarakat / Tolak: tidak memproses file ----
        if ($aksi === 'kembalikan') {
            $pbiApbd->update([
                'dikembalikan_ke_masyarakat' => true,
                'catatan_kelurahan' => $catatan,
                'sudah_diverifikasi_kelurahan' => false,
            ]);
            $this->catatLog($pbiApbd, 'Mengembalikan ke Masyarakat untuk perbaikan data', $catatan);

            return redirect()->route('pbi-kelurahan.index')->with('success', 'Berkas dikembalikan ke masyarakat untuk diperbaiki.');
        }

        if ($aksi === 'tolak') {
            $pbiApbd->update(['status' => 'ditolak', 'catatan_kelurahan' => $catatan]);
            $this->catatLog($pbiApbd, 'Kelurahan menolak permohonan', $catatan);

            return redirect()->route('pbi-kelurahan.index')->with('success', 'Permohonan ditolak.');
        }

        // ---- Simpan / Verifikasi: simpan lampiran hasil lapangan dulu ----
        $update = [];
        foreach (array_keys(self::LAMPIRAN_KELURAHAN) as $field) {
            if ($request->hasFile($field)) {
                if ($pbiApbd->{$field}) {
                    Storage::disk('public')->delete($pbiApbd->{$field});
                }
                // Disimpan dengan nama asli yang terbaca (acak6__nama-asli.ext)
                $update[$field] = $this->simpanDenganNama($request->file($field), 'pbi-apbd/lampiran');
            }
        }
        if ($update) {
            $pbiApbd->update($update);
        }

        if ($aksi === 'simpan') {
            $this->catatLog($pbiApbd, 'Kelurahan menyimpan hasil verifikasi lapangan', $catatan);

            return redirect()->route('pbi-kelurahan.show', $pbiApbd)
                ->with('success', 'Hasil verifikasi & lampiran tersimpan.');
        }

        // ---- Verifikasi berkas & teruskan ke Lurah: cek kelengkapan ----
        $pbiApbd->refresh();
        $masalah = [];

        $kelengkapan = PbiGcForm::kelengkapan($pbiApbd);
        if ($kelengkapan['total'] === 0 || $kelengkapan['terjawab'] < $kelengkapan['total']) {
            $masalah[] = "Masyarakat belum menjawab semua parameter ({$kelengkapan['terjawab']}/{$kelengkapan['total']}). Gunakan \"Kembalikan ke Masyarakat\" bila perlu.";
        }

        // Berkas wajib: milik masyarakat (harus sudah diunggah) dan milik Kelurahan
        $kurang = [];
        foreach (self::LAMPIRAN as $field => [$label, $wajib]) {
            if ($wajib && ! $pbiApbd->{$field}) {
                $kurang[] = $label;
            }
        }
        if ($kurang) {
            $masalah[] = 'Lampiran wajib belum lengkap: ' . implode(', ', $kurang) . '.';
        }

        // Setiap berkas masyarakat yang ada harus sudah ditandai, dan semuanya harus sesuai
        $belumDicek = [];
        $tidakSesuai = [];
        foreach (self::VERIFIKASI as $field) {
            if (! $pbiApbd->{$field}) {
                continue;
            }
            $status = $pbiApbd->verifikasi_berkas[$field] ?? null;
            if ($status === null) {
                $belumDicek[] = self::LAMPIRAN[$field][0];
            } elseif ($status === 'tidak_sesuai') {
                $tidakSesuai[] = self::LAMPIRAN[$field][0];
            }
        }
        if ($belumDicek) {
            $masalah[] = 'Tandai sesuai / tidak sesuai untuk: ' . implode(', ', $belumDicek) . '.';
        }
        if ($tidakSesuai) {
            $masalah[] = 'Berkas tidak sesuai: ' . implode(', ', $tidakSesuai) . '. Gunakan "Kembalikan ke Masyarakat" (beri catatan) agar diperbaiki.';
        }

        if ($masalah) {
            return redirect()->route('pbi-kelurahan.show', $pbiApbd)
                ->withInput()
                ->withErrors(['validasi' => $masalah]);
        }

        // Surat pengantar dibuat otomatis dari template (tanpa unggah). TTD & nama penandatangan diisi Lurah.
        $infoSurat = PbiSuratPengantar::generate($pbiApbd, $user);

        $pbiApbd->update([
            'status' => $pbiApbd->nextStageKey(), // kelurahan -> lurah
            'sudah_diverifikasi_kelurahan' => true,
            'dikembalikan_ke_masyarakat' => false,
            'divalidasi_kelurahan_at' => now(),
            'divalidasi_kelurahan_oleh' => $user->id,
        ]);
        $this->catatLog($pbiApbd, 'Verifikasi kelurahan selesai, diteruskan ke Lurah', $catatan);

        return redirect()->route('pbi-kelurahan.index')
            ->with('success', trim('Berkas diverifikasi dan diteruskan ke Lurah. ' . $infoSurat));
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

    private function pastikanBerwenang(PbiApbd $pbiApbd): void
    {
        $user = Auth::user()->loadMissing('role');

        abort_unless(
            PbiApbd::untukKelurahan($user)->whereKey($pbiApbd->getKey())->exists(),
            403,
            'Pengajuan ini bukan dari wilayah kelurahan Anda.'
        );
    }

    private function catatLog(PbiApbd $pbiApbd, string $task, ?string $catatan): void
    {
        $user = Auth::user();

        PbiApbdLog::create([
            'pbi_apbd_id' => $pbiApbd->id,
            'user_id' => $user->id,
            'username' => $user->name,
            'role_name' => $user->role->name ?? '-',
            'task_name' => $task,
            'catatan' => $catatan,
        ]);
    }
}
