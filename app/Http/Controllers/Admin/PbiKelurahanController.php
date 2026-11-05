<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbiApbd;
use App\Models\PbiApbdLog;
use App\Support\PbiGcForm;
use App\Support\PbiSuratIsian;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Halaman Petugas Kelurahan untuk modul PBI APBD.
 * Alur: Masyarakat isi data -> Kelurahan verifikasi + upload lampiran -> Operator Dinsos -> Kabid -> Kadis.
 * Kelurahan HANYA melihat jawaban (tanpa indeks/bobot/skor).
 * Daftar pertanyaan yang dihitung = yang tampil di form masyarakat (Master Kriteria Ground Check).
 */
class PbiKelurahanController extends Controller
{
    /** field => [label, wajib untuk validasi, mimes] */
    public const LAMPIRAN = [
        'scan_ktp' => ['Scan KTP', true, 'jpg,jpeg,png,pdf'],
        'scan_kk' => ['Scan Kartu Keluarga', true, 'jpg,jpeg,png,pdf'],
        'foto_rumah' => ['Foto Rumah Pemohon', true, 'jpg,jpeg,png'],
        'foto_kamar_mandi' => ['Foto Kamar Mandi', true, 'jpg,jpeg,png'],
        'foto_selfie_ktp' => ['Foto Selfie dengan KTP', true, 'jpg,jpeg,png'],
        'surat_rawat_inap' => ['Surat Ket. Rawat Inap (jika ada)', false, 'jpg,jpeg,png,pdf'],
        'screenshot_pembaharuan_desil' => ['Screenshot Pembaharuan Desil', false, 'jpg,jpeg,png'],
        'screenshot_dtsen' => ['Screenshot DTSEN', false, 'jpg,jpeg,png'],
        // Surat pengantar boleh PDF, Word (DOC/DOCX), atau gambar.
        // Kalau .docx berisi placeholder ${...}, otomatis diisi dari data pengajuan (lihat PbiSuratIsian).
        'scan_surat_pengantar' => ['Scan Surat Pengantar Kelurahan', false, 'jpg,jpeg,png,pdf,doc,docx'],
        'ttd_kelurahan' => ['Tanda Tangan Kelurahan', false, 'jpg,jpeg,png'],
    ];

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

        // Apa pun tombolnya (simpan/validasi/kembalikan/tolak), berkas dianggap sudah ditangani
        // oleh user ini -> badge notifikasinya berkurang. Dipanggil SEBELUM status berubah;
        // tahap berikutnya otomatis jadi notifikasi baru (lihat PbiApbd::booted()).
        $pbiApbd->tandaiDilihat(Auth::user());

        $rules = [
            'aksi' => ['required', Rule::in(['simpan', 'simpan_surat', 'validasi', 'kembalikan', 'tolak'])],
            'kesimpulan_rekomendasi' => ['nullable', 'string', 'max:2000'],
            'catatan' => [
                Rule::requiredIf(in_array($request->input('aksi'), ['kembalikan', 'tolak'], true)),
                'nullable', 'string', 'max:2000',
            ],
        ];
        foreach (self::LAMPIRAN as $field => [$label, $wajib, $mimes]) {
            $rules[$field] = ['nullable', 'file', "mimes:{$mimes}", 'max:4096'];
        }
        // TTD yang digambar langsung di layar (kanvas) dikirim sebagai data URL PNG
        $rules['ttd_kelurahan_data'] = ['nullable', 'string', 'starts_with:data:image/png;base64,', 'max:1500000'];

        $request->validate($rules, [
            'catatan.required' => 'Catatan wajib diisi untuk aksi ini.',
            'aksi.in' => 'Aksi tidak dikenali.',
            'ttd_kelurahan_data.starts_with' => 'Tanda tangan di layar tidak valid, silakan ulangi.',
            'ttd_kelurahan_data.max' => 'Tanda tangan di layar terlalu besar, silakan ulangi.',
            'scan_surat_pengantar.mimes' => 'Surat pengantar harus berformat PDF, Word (DOC/DOCX), JPG, atau PNG.',
            '*.mimes' => 'Format file tidak didukung.',
            '*.max' => 'Ukuran file maksimal 4 MB.',
        ]);

        $user = Auth::user();
        $aksi = $request->input('aksi');
        $catatan = $request->input('catatan');

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

        // ---- TTD digital (digambar di layar): cek dulu SEBELUM ada file yang disimpan ----
        // Kalau petugas juga mengunggah file TTD, file itu yang dipakai.
        $ttdBiner = null;
        if (! $request->hasFile('ttd_kelurahan') && $request->filled('ttd_kelurahan_data')) {
            $dataUrl = (string) $request->input('ttd_kelurahan_data');
            $biner = base64_decode(substr($dataUrl, strlen('data:image/png;base64,')), true);
            $info = $biner !== false ? @getimagesizefromstring($biner) : false;

            if ($biner === false || ! $info || $info[2] !== IMAGETYPE_PNG || strlen($biner) > 1048576) {
                return redirect()->route('pbi-kelurahan.show', $pbiApbd)
                    ->withInput()
                    ->withErrors(['ttd_kelurahan_data' => 'Tanda tangan di layar tidak valid, silakan ulangi.']);
            }

            $ttdBiner = $biner;
        }

        // ---- Simpan / Validasi: simpan lampiran + kesimpulan dulu ----
        $update = [];
        foreach (array_keys(self::LAMPIRAN) as $field) {
            if ($request->hasFile($field)) {
                if ($pbiApbd->{$field}) {
                    Storage::disk('public')->delete($pbiApbd->{$field});
                    if ($field === 'scan_surat_pengantar') {
                        Storage::disk('public')->delete($this->pathTemplate($pbiApbd->{$field}));
                    }
                }
                // Disimpan dengan nama asli yang terbaca (acak6__nama-asli.ext)
                $update[$field] = $this->simpanDenganNama($request->file($field), 'pbi-apbd/lampiran');

                if ($field === 'scan_surat_pengantar') {
                    // Simpan salinan template asli (masih ber-placeholder) agar surat bisa
                    // diisi ulang setiap kali data / tanda tangan berubah.
                    $disk = Storage::disk('public');
                    $disk->put($this->pathTemplate($update[$field]), $disk->get($update[$field]));
                }
            }
        }
        if ($ttdBiner !== null) {
            if ($pbiApbd->ttd_kelurahan) {
                Storage::disk('public')->delete($pbiApbd->ttd_kelurahan);
            }
            $pathTtd = 'pbi-apbd/lampiran/ttd-kelurahan-' . $pbiApbd->id . '-' . now()->timestamp . '.png';
            Storage::disk('public')->put($pathTtd, $ttdBiner);
            $update['ttd_kelurahan'] = $pathTtd;
        }
        if ($request->filled('kesimpulan_rekomendasi')) {
            $update['kesimpulan_rekomendasi'] = $request->input('kesimpulan_rekomendasi');
        }
        if ($update) {
            $pbiApbd->update($update);
        }

        // Isi surat pengantar (.docx): reset ke template, sisipkan TTD, isi placeholder ${...}
        $infoSurat = $this->isiSuratPengantar($pbiApbd->fresh(), $user);
        $sambung = fn (string $pesan) => trim($pesan . ' ' . $infoSurat);

        if (in_array($aksi, ['simpan', 'simpan_surat'], true)) {
            $this->catatLog($pbiApbd, 'Kelurahan menyimpan hasil verifikasi lapangan', $catatan);

            return redirect()->route('pbi-kelurahan.show', $pbiApbd)
                ->with('success', $sambung('Hasil verifikasi & lampiran tersimpan.'));
        }

        // ---- Validasi & teruskan: cek kelengkapan ----
        $masalah = [];

        $kelengkapan = PbiGcForm::kelengkapan($pbiApbd);
        if ($kelengkapan['total'] === 0 || $kelengkapan['terjawab'] < $kelengkapan['total']) {
            $masalah[] = "Masyarakat belum menjawab semua parameter ({$kelengkapan['terjawab']}/{$kelengkapan['total']}). Gunakan \"Kembalikan ke Masyarakat\" bila perlu.";
        }

        $kurang = [];
        foreach (self::LAMPIRAN as $field => [$label, $wajib]) {
            if ($wajib && ! $pbiApbd->{$field}) {
                $kurang[] = $label;
            }
        }
        if ($kurang) {
            $masalah[] = 'Lampiran wajib belum lengkap: ' . implode(', ', $kurang) . '.';
        }

        if (blank($pbiApbd->kesimpulan_rekomendasi)) {
            $masalah[] = 'Kesimpulan/rekomendasi kelurahan wajib diisi.';
        }

        if ($masalah) {
            return redirect()->route('pbi-kelurahan.show', $pbiApbd)
                ->withInput()
                ->withErrors(['validasi' => $masalah]);
        }

        $pbiApbd->update([
            'status' => $pbiApbd->nextStageKey(), // kelurahan -> operator_dinsos
            'sudah_diverifikasi_kelurahan' => true,
            'dikembalikan_ke_masyarakat' => false,
            'divalidasi_kelurahan_at' => now(),
            'divalidasi_kelurahan_oleh' => $user->id,
        ]);
        $this->catatLog($pbiApbd, 'Verifikasi kelurahan selesai, diteruskan ke Operator Dinsos', $pbiApbd->kesimpulan_rekomendasi);

        return redirect()->route('pbi-kelurahan.index')
            ->with('success', $sambung('Berkas divalidasi dan diteruskan ke Operator Dinsos.'));
    }

    /** Lokasi salinan template asli surat pengantar (masih ber-placeholder). */
    private function pathTemplate(string $path): string
    {
        return 'pbi-apbd/template/' . basename($path);
    }

    /**
     * Isi ulang surat pengantar .docx dari template asli:
     *  1) reset file ke template, 2) sisipkan gambar TTD di ${ttd_kelurahan},
     *  3) isi placeholder teks ${...} lewat PbiSuratIsian.
     * Mengembalikan pesan info untuk notifikasi (string kosong bila tidak ada yang diproses).
     */
    private function isiSuratPengantar(PbiApbd $pbiApbd, $user): string
    {
        $path = $pbiApbd->scan_surat_pengantar;
        if (! $path || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'docx') {
            return '';
        }

        $disk = Storage::disk('public');
        $tpl = $this->pathTemplate($path);
        if (! $disk->exists($tpl)) {
            // Surat diunggah sebelum fitur ini: placeholder sudah terisi, TTD tidak bisa disisipkan.
            return 'TTD belum masuk ke surat: surat ini belum punya template asli. '
                . 'Unggah ulang template surat (berisi ${ttd_kelurahan}), lalu simpan lagi.';
        }

        $disk->put($path, $disk->get($tpl)); // kembali ke template asli

        $peringatan = '';
        if (! $pbiApbd->ttd_kelurahan || ! $disk->exists($pbiApbd->ttd_kelurahan)) {
            $peringatan = 'TTD belum dibuat, jadi belum disisipkan ke surat.';
        } else {
            try {
                $tp = new \PhpOffice\PhpWord\TemplateProcessor($disk->path($path));
                if (in_array('ttd_kelurahan', $tp->getVariables(), true)) {
                    $tp->setImageValue('ttd_kelurahan', [
                        'path' => $disk->path($pbiApbd->ttd_kelurahan),
                        'width' => 140,
                        'height' => 70,
                        'ratio' => true,
                    ]);
                    $tp->saveAs($disk->path($path));
                } else {
                    $peringatan = 'TTD tidak disisipkan: template surat belum punya placeholder ${ttd_kelurahan}.';
                }
            } catch (\Throwable $e) {
                report($e);
                $peringatan = 'TTD gagal disisipkan ke surat (cek storage/logs/laravel.log).';
            }
        }

        return trim(PbiSuratIsian::isiBerkas($pbiApbd, $path, $user, null) . ' ' . $peringatan);
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