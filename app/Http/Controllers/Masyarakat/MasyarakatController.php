<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\DtsenPermohonan;
use App\Models\GcKriteriaParameter;
use App\Models\PbiApbd;
use App\Models\PbiApbdJawaban;
use App\Models\GcKeterampilan;
use App\Models\GcLapanganUsaha;
use App\Models\GcPbiJawaban;
use App\Models\GcPenyakitKronis;
use App\Models\PbiApbdLog;
use App\Models\PbiKriteriaParameter;
use App\Support\PbiGcForm;
use Illuminate\Support\Str;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class MasyarakatController extends Controller
{
    /**
     * Tahapan bawaan untuk layanan yang modelnya belum punya method progressSteps()
     * (urutan = progres; kunci = nilai kolom status yang sudah di-slug).
     */
    private const TAHAPAN_BAWAAN = [
        'diajukan' => 'Diajukan',
        'verifikasi_kelurahan' => 'Verifikasi Kelurahan',
        'ttd_lurah' => 'TTD Lurah',
        'validasi_dinsos' => 'Validasi Dinsos',
        'diproses_kabin' => 'Diproses Kabin',
        'disetujui_kadis' => 'Disetujui Kadis',
        'selesai' => 'Selesai',
    ];

    /** Pilihan "Hubungan Keluarga" pada form anggota keluarga (kunci = nilai tersimpan). */
    private const HUBUNGAN = [
        'kepala_keluarga' => 'Kepala Keluarga',
        'istri' => 'Istri',
        'suami' => 'Suami',
        'anak' => 'Anak',
        'orang_tua' => 'Orang Tua',
        'menantu' => 'Menantu',
        'cucu' => 'Cucu',
        'famili_lain' => 'Famili Lain',
        'lainnya' => 'Lainnya',
    ];

    private const AGAMA = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    /** GET /akun-saya — dashboard: ringkasan pengajuan milik user yang login (per layanan) */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // ---------- PBI APBD ----------
        $pbiApbd = $user->pengajuanPbiApbd()
            ->with([
                'logs' => fn ($q) => $q->orderBy('created_at'),
                'diagnosaLogs.user',
                'jawabans',
            ])
            ->latest()
            ->first();

        // Pertanyaan yang tampil ke warga = semua parameter aktif di Master Kriteria Ground Check.
        // Yang dihitung hanya isian wajib (foto & koordinat opsional, field "bekerja" bersyarat).
        $kelengkapanGc = PbiGcForm::kelengkapan($pbiApbd);
        $totalParameter = $kelengkapanGc['total'];
        $terjawab = $kelengkapanGc['terjawab'];

        // "Lengkap" = lokasi terambil + semua parameter terjawab. Lampiran kini diurus Kelurahan.
        $isLengkap = $pbiApbd
            && $pbiApbd->latitude
            && $pbiApbd->longitude
            && $totalParameter > 0
            && $terjawab >= $totalParameter;

        $bisaEdit = $pbiApbd ? $pbiApbd->masyarakatBisaEdit() : false;

        $persenTerisi = PbiGcForm::persen($kelengkapanGc);

        // Layanan PBI baru dianggap "terdaftar" di Beranda setelah warga mulai mengisi:
        //  - draft  = belum pernah diajukan, tapi sudah ada jawaban tersimpan (persen sudah terbaca)
        //  - diajukan = sudah pernah menekan "Simpan Data" / sudah diproses petugas
        // Record yang baru dibuat saat registrasi dan belum diisi sama sekali -> belum terdaftar.
        $masihDraft = $pbiApbd ? $pbiApbd->masyarakatMasihDraft() : false;
        $pbiMulai = (bool) $pbiApbd && (! $masihDraft || $terjawab > 0);
        $pbiDraft = $pbiMulai && $masihDraft;

        // ---------- DTSEN ----------
        // Memakai relasi User::pengajuanDtsen() kalau sudah ada.
        $dtsen = method_exists($user, 'pengajuanDtsen')
            ? $user->pengajuanDtsen()->with(['detail', 'lampiran'])->latest()->first()
            : null;

        // Kelengkapan: pakai $dtsen->kelengkapanData() milik model kalau ada, kalau tidak hitung di sini.
        //   ['persen' => 0-100, 'terisi' => int, 'total' => int, 'lengkap' => bool]
        $kelDtsen = null;
        if ($dtsen) {
            $kelDtsen = method_exists($dtsen, 'kelengkapanData')
                ? $dtsen->kelengkapanData()
                : $this->kelengkapanDtsen($dtsen);
        }

        $dtsenBisaEdit = $dtsen ? $this->dtsenBisaEdit($dtsen) : false;

        // ---------- Ringkasan per layanan (dipakai kartu di dashboard) ----------
        $layanan = [
            'pbi' => $this->ringkas('PBI APBD', 'health_and_safety', $pbiApbd, $this->urlRoute('registrasi.step1'), [
                'terdaftar' => $pbiMulai,
                'draft' => $pbiDraft,
                'punya_record' => (bool) $pbiApbd,
                'persen' => $persenTerisi,
                'terisi' => $terjawab,
                'total' => $totalParameter,
                'lengkap' => (bool) $isLengkap,
                'bisa_edit' => $bisaEdit,
                // Ditolak diperlakukan sama seperti "dikembalikan": warga diminta memperbaiki data
                'dikembalikan' => (bool) ($pbiApbd
                    && ($pbiApbd->dikembalikan_ke_masyarakat || $pbiApbd->status === 'ditolak')
                    && $bisaEdit),
                'lengkapi_url' => $pbiApbd ? route('masyarakat.pbi-apbd.lengkapi', $pbiApbd) : null,
                'lokasi' => $pbiApbd ? (bool) ($pbiApbd->latitude && $pbiApbd->longitude) : null,
            ]),
            'dtsen' => $this->ringkas('DTSEN', 'groups', $dtsen, $this->urlRoute('masyarakat.dtsen'), [
                'persen' => $kelDtsen['persen'] ?? null,
                'terisi' => $kelDtsen['terisi'] ?? null,
                'total' => $kelDtsen['total'] ?? null,
                'lengkap' => $kelDtsen['lengkap'] ?? true,
                'bisa_edit' => $dtsenBisaEdit,
                // Sama seperti PBI: ditolak = warga diminta memperbaiki data
                'dikembalikan' => (bool) ($dtsen
                    && (($dtsen->dikembalikan_ke_masyarakat ?? false) || $dtsen->status === DtsenPermohonan::STATUS_DITOLAK)
                    && $dtsenBisaEdit),
                'lengkapi_url' => $dtsen ? $this->urlRoute('masyarakat.dtsen.lengkapi', $dtsen) : null,
                'lokasi' => null,
            ]),
        ];

        $totalPengajuan = $user->pengajuanPbiApbd()->count();

        return view('masyarakat.dashboard', compact(
            'layanan', 'pbiApbd', 'isLengkap', 'bisaEdit', 'totalParameter', 'terjawab', 'persenTerisi', 'totalPengajuan'
        ));
    }

    // ==========================================================
    // PERMOHONAN SAYA
    // ==========================================================

    /** GET /akun-saya/permohonan — daftar semua permohonan milik user (PBI APBD + DTSEN) */
    public function permohonan(Request $request)
    {
        $user = $request->user();

        $pbi = $user->pengajuanPbiApbd()->latest()->get()->map(fn ($r) => $this->barisPermohonan(
            $r,
            $r->no_registrasi,
            $r->nama_kepala_keluarga,
            collect([
                $r->alamat,
                ($r->rt || $r->rw) ? 'RT ' . $r->rt . '/RW ' . $r->rw : null,
                $r->desa_kelurahan,
                $r->kecamatan,
            ])->filter()->implode(', '),
            route('masyarakat.pbi-apbd.lengkapi', $r)
        ));

        $dtsen = $user->pengajuanDtsen()->with('detail')->latest()->get()->map(fn ($r) => $this->barisPermohonan(
            $r,
            $r->no_permohonan,
            $r->nama_pemohon ?? $r->detail->nama ?? null,
            $r->alamat ?? $r->detail->alamat ?? null,
            route('masyarakat.dtsen.lengkapi', $r)
        ));

        return view('masyarakat.permohonan', compact('pbi', 'dtsen'));
    }

    /** Satu baris tabel "Permohonan Saya" (dipakai PBI & DTSEN). */
    private function barisPermohonan($r, $no, $pemohon, $alamat, string $url): array
    {
        $status = strtolower((string) $r->status);
        $final = $this->isFinal($r);
        $tanggal = $r->tanggal_insert ?? $r->created_at;

        return [
            'no' => $no ?: '-',
            'tanggal' => $tanggal ? Carbon::parse($tanggal) : null,
            'pemohon' => $pemohon ?: '-',
            'alamat' => $alamat ?: '-',
            'proses' => $final ? null : $this->statusLabel($r),
            'status_label' => $final ? ($status === 'ditolak' ? 'Ditolak' : 'Selesai') : 'Diproses',
            'status_tone' => $final ? ($status === 'ditolak' ? 'error' : 'success') : 'warning',
            'url' => $url,
        ];
    }

    // ==========================================================
    // TRACKING PERMOHONAN
    // ==========================================================

    /**
     * GET /akun-saya/tracking?no=... — lacak permohonan milik user.
     * Warga memilih dari daftar permohonannya sendiri (tanpa kotak cari).
     * Kalau belum memilih, permohonan terbaru dipilih otomatis.
     */
    public function tracking(Request $request)
    {
        $request->validate(['no' => ['nullable', 'string', 'max:50']]);

        $user = $request->user();
        $no = trim((string) $request->query('no', ''));

        // PBI yang masih draft (warga belum menekan "Simpan Data") belum menjadi pengajuan,
        // jadi tidak ditampilkan di tracking.
        $pbiList = $user->pengajuanPbiApbd()->latest()->get()
            ->reject(fn ($r) => $r->masyarakatMasihDraft());
        $dtsenList = $user->pengajuanDtsen()->latest()->get();

        // Daftar pilihan (kartu): semua permohonan milik user
        $daftar = collect();

        foreach ($pbiList as $r) {
            if (! $r->no_registrasi) {
                continue;
            }
            [$label, $tone] = $this->labelRingkas($r);
            $daftar->push([
                'jenis' => 'PBI APBD',
                'no' => $r->no_registrasi,
                'status_label' => $label,
                'status_tone' => $tone,
                'urut' => $r->tanggal_insert ?? $r->created_at,
            ]);
        }

        foreach ($dtsenList as $r) {
            if (! $r->no_permohonan) {
                continue;
            }
            [$label, $tone] = $this->labelRingkas($r);
            $daftar->push([
                'jenis' => 'DTSEN',
                'no' => $r->no_permohonan,
                'status_label' => $label,
                'status_tone' => $tone,
                'urut' => $r->tanggal_insert ?? $r->created_at,
            ]);
        }

        $daftar = $daftar
            ->sortByDesc(fn ($d) => $d['urut'] ? Carbon::parse($d['urut'])->timestamp : 0)
            ->values();

        // Belum memilih -> otomatis permohonan terbaru
        if ($no === '' && $daftar->isNotEmpty()) {
            $no = $daftar->first()['no'];
        }

        $hasil = null;

        if ($no !== '') {
            $pbi = $user->pengajuanPbiApbd()->with('logs')->where('no_registrasi', $no)->first();

            // Draft tidak dilacak (lihat catatan di atas)
            if ($pbi && $pbi->masyarakatMasihDraft()) {
                $pbi = null;
            }

            if ($pbi) {
                $hasil = $this->susunTracking(
                    $pbi,
                    'PBI APBD',
                    $pbi->no_registrasi,
                    $pbi->nama_kepala_keluarga,
                    $pbi->logs->map(fn ($l) => [
                        'waktu' => $l->created_at,
                        'tugas' => $l->task_name,
                        'peran' => $l->role_name,
                        'catatan' => $l->catatan,
                    ]),
                    route('masyarakat.pbi-apbd.lengkapi', $pbi)
                );
            } else {
                $dtsen = $user->pengajuanDtsen()->with(['detail', 'logs'])->where('no_permohonan', $no)->first();

                if ($dtsen) {
                    $hasil = $this->susunTracking(
                        $dtsen,
                        'DTSEN',
                        $dtsen->no_permohonan,
                        $dtsen->nama_pemohon ?? $dtsen->detail->nama ?? null,
                        $dtsen->logs->map(fn ($l) => [
                            'waktu' => $l->tanggal_proses ?? $l->created_at,
                            'tugas' => $l->taskname,
                            'peran' => $l->rolename,
                            'catatan' => $l->catatan,
                        ]),
                        route('masyarakat.dtsen.lengkapi', $dtsen),
                    );
                }
            }
        }

        return view('masyarakat.tracking', compact('no', 'daftar', 'hasil'));
    }

    /** Label & warna status ringkas (Diproses / Selesai / Ditolak) untuk kartu pilihan. */
    private function labelRingkas($r): array
    {
        $status = strtolower((string) $r->status);
        $final = $this->isFinal($r);

        return [
            $final ? ($status === 'ditolak' ? 'Ditolak' : 'Selesai') : 'Diproses',
            $final ? ($status === 'ditolak' ? 'error' : 'success') : 'warning',
        ];
    }

    /** Titik koordinat lokasi pengiriman permohonan. null = belum tercatat. */
    private function lokasi($lat, $lng, $waktu): ?array
    {
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        return [
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'waktu' => $waktu ? Carbon::parse($waktu) : null,
        ];
    }

    /** Susun data satu hasil tracking (dipakai PBI & DTSEN). */
    private function susunTracking($r, string $jenis, $no, $pemohon, $logs, string $url, ?array $lokasi = null): array
    {
        $status = strtolower((string) $r->status);
        $final = $this->isFinal($r);
        $tanggal = $r->tanggal_insert ?? $r->created_at;

        return [
            'jenis' => $jenis,
            'no' => $no ?: '-',
            'pemohon' => $pemohon ?: '-',
            'tanggal' => $tanggal ? Carbon::parse($tanggal) : null,
            'proses' => $this->statusLabel($r),
            'status_label' => $final ? ($status === 'ditolak' ? 'Ditolak' : 'Selesai') : 'Diproses',
            'status_tone' => $final ? ($status === 'ditolak' ? 'error' : 'success') : 'warning',
            'dikembalikan' => (bool) ($r->dikembalikan_ke_masyarakat ?? false),
            'steps' => $this->progress($r),
            'lokasi' => $lokasi,
            'logs' => $logs
                ->map(fn ($l) => [
                    'waktu' => $l['waktu'] ? Carbon::parse($l['waktu']) : null,
                    'tugas' => $l['tugas'] ?: '-',
                    'peran' => $l['peran'] ?: null,
                    'catatan' => $l['catatan'] ?: null,
                ])
                ->sortByDesc(fn ($l) => $l['waktu']?->timestamp ?? 0)
                ->values(),
            'url' => $url,
        ];
    }

    // ==========================================================
    // ANGGOTA KELUARGA
    // ==========================================================

    /** GET /akun-saya/keluarga — daftar anggota keluarga dari pengajuan PBI APBD milik user */
    public function keluarga(Request $request)
    {
        $pbi = $this->pbiTerbaru($request);
        $bisaEdit = $pbi ? (bool) $pbi->masyarakatBisaEdit() : false;

        $anggota = collect();
        if ($pbi) {
            $anggota = $pbi->anggotaKeluarga()->orderBy('id')->get()->map(function ($a) {
                $jk = strtoupper((string) $a->jenis_kelamin);
                $hub = strtolower(str_replace([' ', '-'], '_', trim((string) $a->hubungan_keluarga)));
                $hub = array_key_exists($hub, self::HUBUNGAN) ? $hub : (string) $a->hubungan_keluarga;

                return [
                    'id' => $a->id,
                    'nik' => $a->nik,
                    'nama' => $a->nama,
                    'tempat_lahir' => $a->tempat_lahir,
                    'tanggal_lahir' => $a->tanggal_lahir ? Carbon::parse($a->tanggal_lahir)->format('Y-m-d') : null,
                    'jenis_kelamin' => str_starts_with($jk, 'L') ? 'L' : (str_starts_with($jk, 'P') ? 'P' : null),
                    'agama' => $a->agama ? ucfirst(strtolower((string) $a->agama)) : null,
                    'pekerjaan' => $a->pekerjaan,
                    'hubungan' => $hub,
                    'hubungan_label' => self::HUBUNGAN[$hub] ?? ucwords(str_replace('_', ' ', $hub)),
                    'bpjs' => $a->keanggotaan_bpjs,
                    'no_bpjs' => $a->no_bpjs ?? null,
                    'status_peserta' => $a->status_peserta ?? null,
                ];
            });
        }

        return view('masyarakat.keluarga', [
            'pbi' => $pbi,
            'anggota' => $anggota,
            'bisaEdit' => $bisaEdit,
            'hubunganOpsi' => self::HUBUNGAN,
            'agamaOpsi' => self::AGAMA,
        ]);
    }

    /** POST /akun-saya/keluarga — tambah anggota keluarga */
    public function keluargaStore(Request $request)
    {
        $pbi = $this->pbiTerbaru($request);

        if (! $pbi) {
            return redirect()->route('masyarakat.keluarga')
                ->with('error', 'Anda belum memiliki pengajuan PBI APBD, data anggota keluarga belum bisa ditambahkan.');
        }

        if (! $pbi->masyarakatBisaEdit()) {
            return redirect()->route('masyarakat.keluarga')
                ->with('error', 'Pengajuan sudah disetujui, data anggota keluarga tidak dapat diubah lagi.');
        }

        $data = $this->validasiKeluarga($request, $pbi);

        $pbi->anggotaKeluarga()->create($data + ['keanggotaan_bpjs' => 'Belum Terdaftar']);

        return redirect()->route('masyarakat.keluarga')
            ->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }

    /** PUT /akun-saya/keluarga/{anggota} — ubah anggota keluarga */
    public function keluargaUpdate(Request $request, int $anggota)
    {
        $pbi = $this->pbiTerbaru($request);
        abort_unless($pbi, 404);

        // Dicari lewat relasi pengajuan milik user, jadi anggota keluarga orang lain tidak bisa disentuh.
        $baris = $pbi->anggotaKeluarga()->findOrFail($anggota);

        if (! $pbi->masyarakatBisaEdit()) {
            return redirect()->route('masyarakat.keluarga')
                ->with('error', 'Pengajuan sudah disetujui, data anggota keluarga tidak dapat diubah lagi.');
        }

        $data = $this->validasiKeluarga($request, $pbi, $baris->id);

        $baris->update($data);

        return redirect()->route('masyarakat.keluarga')
            ->with('success', 'Data anggota keluarga berhasil diperbarui.');
    }

    /** Pengajuan PBI APBD terbaru milik user (induk dari data anggota keluarga & alamat profil). */
    private function pbiTerbaru(Request $request): ?PbiApbd
    {
        return $request->user()->pengajuanPbiApbd()->latest()->first();
    }

    /** Validasi form anggota keluarga (tambah & ubah). $abaikanId = baris yang sedang diubah. */
    private function validasiKeluarga(Request $request, PbiApbd $pbi, ?int $abaikanId = null): array
    {
        $data = $request->validate([
            'nik' => ['required', 'digits:16'],
            'nama' => ['required', 'string', 'max:150'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'agama' => ['required', Rule::in(self::AGAMA)],
            'pekerjaan' => ['required', 'string', 'max:100'],
            'hubungan_keluarga' => ['required', 'string', 'max:50'],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nama.required' => 'Nama wajib diisi.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
            'agama.required' => 'Agama wajib dipilih.',
            'agama.in' => 'Agama tidak valid.',
            'pekerjaan.required' => 'Pekerjaan wajib diisi.',
            'hubungan_keluarga.required' => 'Hubungan keluarga wajib dipilih.',
        ]);

        $sudahAda = $pbi->anggotaKeluarga()
            ->where('nik', $data['nik'])
            ->when($abaikanId, fn ($q) => $q->where('id', '!=', $abaikanId))
            ->exists();

        if ($sudahAda) {
            throw ValidationException::withMessages([
                'nik' => 'NIK ini sudah terdaftar sebagai anggota keluarga Anda.',
            ]);
        }

        return $data;
    }

    // ==========================================================
    // PROFIL (DATA DIRI) + AKUN & KATA SANDI
    // ==========================================================

    /** GET /akun-saya/profil — tab Data Diri */
    public function profil(Request $request)
    {
        return $this->tampilProfil($request, 'data');
    }

    /** GET /akun-saya/password — tab Akun & Kata Sandi */
    public function password(Request $request)
    {
        return $this->tampilProfil($request, 'akun');
    }

    /** Dipakai kedua tab: $tab = 'data' | 'akun' */
    private function tampilProfil(Request $request, string $tab)
    {
        $user = $request->user();
        $pbi = $this->pbiTerbaru($request);
        $alamatBisaEdit = $pbi ? (bool) $pbi->masyarakatBisaEdit() : false;

        // Soal verifikasi hanya dibutuhkan di tab Data Diri
        $a = $b = null;
        if ($tab === 'data') {
            $a = random_int(1, 9);
            $b = random_int(1, 9);
            $request->session()->put('profil_captcha_a', $a);
            $request->session()->put('profil_captcha_b', $b);
        }

        $profil = [
            'nik' => $pbi->nik_kepala_keluarga ?? $user->username,
            'nama' => $pbi->nama_kepala_keluarga ?? $user->name,
            'alamat' => $pbi->alamat ?? '',
            'rt' => $pbi->rt ?? '',
            'rw' => $pbi->rw ?? '',
            'provinsi' => $pbi->provinsi ?? config('wilayah.provinsi'),
            'kab_kota' => $pbi->kabupaten_kota ?? config('wilayah.kab_kota'),
            'kecamatan' => strtoupper(trim((string) ($pbi->kecamatan ?? ''))),
            'kelurahan' => strtoupper(trim((string) ($pbi->desa_kelurahan ?? ''))),
            'no_telp' => $pbi->no_telp ?? '',
            'email' => $user->email,
        ];

        return view('masyarakat.profil', [
            'tab' => $tab,
            'user' => $user,
            'pbi' => $pbi,
            'profil' => $profil,
            'wilayah' => $this->wilayahOpsi($pbi),
            'alamatBisaEdit' => $alamatBisaEdit,
            'captchaA' => $a,
            'captchaB' => $b,
        ]);
    }

    /** PUT /akun-saya/profil — simpan data diri warga */
    public function profilUpdate(Request $request)
    {
        $user = $request->user();
        $pbi = $this->pbiTerbaru($request);
        $alamatBisaEdit = $pbi ? (bool) $pbi->masyarakatBisaEdit() : false;
        $wilayah = $this->wilayahOpsi($pbi);

        // No. telp & email selalu bisa diubah; alamat hanya selama pengajuan belum disetujui.
        $rules = [
            'no_telp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'captcha' => ['required', 'numeric'],
        ];

        if ($alamatBisaEdit) {
            $rules += [
                'alamat' => ['required', 'string', 'max:1000'],
                'rt' => ['required', 'string', 'max:5'],
                'rw' => ['required', 'string', 'max:5'],
                'kecamatan' => ['required', Rule::in(array_keys($wilayah))],
                'desa_kelurahan' => ['required', 'string', 'max:100'],
            ];
        }

        $validated = $request->validate($rules, [
            'no_telp.regex' => 'No. telp hanya boleh berisi angka, spasi, tanda + atau -.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'captcha.required' => 'Jawaban verifikasi wajib diisi.',
            'captcha.numeric' => 'Jawaban verifikasi harus berupa angka.',
            'alamat.required' => 'Alamat wajib diisi.',
            'rt.required' => 'RT wajib diisi.',
            'rw.required' => 'RW wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib dipilih.',
            'kecamatan.in' => 'Kecamatan tidak valid.',
            'desa_kelurahan.required' => 'Desa/Kelurahan wajib dipilih.',
        ]);

        $expected = (int) $request->session()->get('profil_captcha_a', 0)
            + (int) $request->session()->get('profil_captcha_b', 0);

        if ((int) $validated['captcha'] !== $expected) {
            throw ValidationException::withMessages([
                'captcha' => 'Jawaban verifikasi salah, silakan coba lagi.',
            ]);
        }

        if ($alamatBisaEdit && ! in_array($validated['desa_kelurahan'], $wilayah[$validated['kecamatan']] ?? [], true)) {
            throw ValidationException::withMessages([
                'desa_kelurahan' => 'Desa/Kelurahan tidak sesuai dengan kecamatan yang dipilih.',
            ]);
        }

        DB::transaction(function () use ($user, $pbi, $validated, $alamatBisaEdit) {
            $user->update(['email' => $validated['email']]);

            if ($pbi) {
                $pbi->update([
                    'no_telp' => $validated['no_telp'] ?? '',
                    'email' => $validated['email'],
                ] + ($alamatBisaEdit ? [
                    'alamat' => $validated['alamat'],
                    'rt' => $validated['rt'],
                    'rw' => $validated['rw'],
                    'kecamatan' => $validated['kecamatan'],
                    'desa_kelurahan' => $validated['desa_kelurahan'],
                ] : []));
            }
        });

        $request->session()->forget(['profil_captcha_a', 'profil_captcha_b']);

        return redirect()->route('masyarakat.profil')
            ->with('success', 'Data diri berhasil diperbarui.');
    }

    /** PUT /akun-saya/password — simpan nama akun & (opsional) kata sandi baru */
    public function akunUpdate(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            // Kata sandi lama wajib hanya kalau mau ganti kata sandi
            'password_lama' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Nama akun wajib diisi.',
            'password_lama.required_with' => 'Kata sandi lama wajib diisi untuk mengganti kata sandi.',
            'password_lama.current_password' => 'Kata sandi lama salah.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
        ]);

        $user->name = $validated['name'];

        if (filled($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('masyarakat.password')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    /**
     * Peta kecamatan => daftar kelurahan dari config/wilayah.php.
     * Nilai yang sudah tersimpan di pengajuan (kalau tidak ada di daftar) ikut ditambahkan,
     * supaya tidak hilang dari pilihan.
     */
    private function wilayahOpsi(?PbiApbd $pbi): array
    {
        $wilayah = config('wilayah.kecamatan', []);

        $kec = strtoupper(trim((string) ($pbi->kecamatan ?? '')));
        $kel = strtoupper(trim((string) ($pbi->desa_kelurahan ?? '')));

        if ($kec !== '') {
            $wilayah[$kec] = $wilayah[$kec] ?? [];

            if ($kel !== '' && ! in_array($kel, $wilayah[$kec], true)) {
                $wilayah[$kec][] = $kel;
            }
        }

        return $wilayah;
    }

    // ==========================================================
    // HELPER RINGKASAN
    // ==========================================================

    /**
     * Susun ringkasan satu layanan. $record = null berarti user belum pernah mendaftar layanan itu.
     */
    private function ringkas(string $label, string $icon, $record, string $shortcut, array $extra): array
    {
        $base = [
            'label' => $label,
            'icon' => $icon,
            'record' => $record,
            'terdaftar' => (bool) $record,
            'shortcut' => $shortcut,
            // PBI memakai no_registrasi, DTSEN memakai no_permohonan
            'no' => $record->no_registrasi ?? $record->no_permohonan ?? null,
            'status' => $record ? strtolower((string) ($record->status ?? '')) : null,
            'status_label' => $record ? $this->statusLabel($record) : null,
            'steps' => $record ? $this->progress($record) : [],
            'final' => $record ? $this->isFinal($record) : false,
            'updated_at' => $record?->updated_at,
        ];

        return array_merge($base, $extra);
    }

    /** Progress per layanan: pakai progressSteps() milik model kalau ada, kalau tidak pakai tahapan bawaan. */
    private function progress($record): array
    {
        if (method_exists($record, 'progressSteps')) {
            return $record->progressSteps();
        }

        $slug = strtolower(str_replace([' ', '-'], '_', trim((string) $record->status)));
        $keys = array_keys(self::TAHAPAN_BAWAAN);
        $idx = array_search($slug, $keys, true);
        $idx = $idx === false ? 0 : $idx;
        $selesai = $slug === 'selesai';

        $steps = [];
        foreach (array_values(self::TAHAPAN_BAWAAN) as $i => $label) {
            $steps[] = [
                'label' => $label,
                'state' => $selesai || $i < $idx ? 'done' : ($i === $idx ? 'current' : 'pending'),
            ];
        }

        return $steps;
    }

    /** Label status: pakai statusLabel() model kalau ada; DTSEN pakai DtsenPermohonan::statusLabels(). */
    private function statusLabel($record): string
    {
        if (method_exists($record, 'statusLabel')) {
            return (string) $record->statusLabel();
        }

        if ($record instanceof DtsenPermohonan) {
            return DtsenPermohonan::statusLabels()[$record->status] ?? ucwords(str_replace('_', ' ', (string) $record->status));
        }

        return ucwords(str_replace('_', ' ', (string) $record->status));
    }

    private function isFinal($record): bool
    {
        if (method_exists($record, 'isFinal')) {
            return (bool) $record->isFinal();
        }

        return in_array(strtolower((string) $record->status), ['selesai', 'disetujui', 'ditolak'], true);
    }

    private function urlRoute(string $name, $param = null): string
    {
        return Route::has($name) ? route($name, $param) : '#';
    }

    // ==========================================================
    // PBI APBD
    // ==========================================================

    /**
     * Pertanyaan di form warga = SEMUA parameter Master Kriteria Ground Check yang AKTIF,
     * urut mengikuti master. Hapus/nonaktifkan di Master GC -> otomatis hilang dari halaman warga.
     */
    private function parameterUntukWarga()
    {
        return PbiGcForm::parameters();
    }

    /** Data master ketenagakerjaan/kesehatan (hanya yang aktif), sama seperti form petugas. */
    private function masterGc(): array
    {
        return [
            'lapangan' => GcLapanganUsaha::aktif()->with('pekerjaansAktif')->get(),
            'keterampilan' => GcKeterampilan::aktif()->get(),
            'penyakit' => GcPenyakitKronis::aktif()->get(),
        ];
    }

    /** Daftar jawaban yang sah untuk sebuah parameter (dari master atau dari opsi). */
    private function labelOpsi(GcKriteriaParameter $p, array $master): array
    {
        return match ($p->sumber) {
            'lapangan_usaha' => $master['lapangan']->pluck('nama')->all(),
            'pekerjaan' => $master['lapangan']->flatMap(fn ($l) => $l->pekerjaansAktif->pluck('nama'))->unique()->values()->all(),
            'keterampilan' => $master['keterampilan']->pluck('nama')->all(),
            'penyakit_kronis' => $master['penyakit']->pluck('nama')->all(),
            default => $p->opsis->pluck('label')->all(),
        };
    }

    /** Isian awal: jawaban tersimpan, atau data identitas dari pengajuan bila belum pernah diisi. */
    private function nilaiAwal(PbiApbd $pbi, $parameters): array
    {
        $nilai = GcPbiJawaban::where('pbi_apbd_id', $pbi->id)->whereNotNull('jawaban')->pluck('jawaban', 'parameter_id')->all();

        $identitas = [
            'nama' => $pbi->nama_kepala_keluarga,
            'no kk' => $pbi->no_kk,
            'alamat' => $pbi->alamat,
        ];

        foreach ($parameters as $p) {
            $nama = Str::lower(trim($p->nama));
            if (! isset($nilai[$p->id]) && ! empty($identitas[$nama])) {
                $nilai[$p->id] = $identitas[$nama];
            }
        }

        return $nilai;
    }

    /**
     * Kelas kemiskinan (hitungUlangSkor) tetap memakai rumus SK 460: indeks x bobot x indeks terintegrasi PBI.
     * Pertanyaan GC yang punya padanan PBI (pbi_parameter_id) & opsinya cocok (label sama) ikut dihitung;
     * pertanyaan lain tidak masuk skor. $jawaban = peta id parameter GC => jawaban tersimpan.
     */
    private function sinkronSkorPbi(PbiApbd $pbi, $parameters, array $jawaban): void
    {
        $dipakai = [];

        foreach ($parameters as $p) {
            if (! $p->pbi_parameter_id || $p->tipe_input !== 'pilihan') {
                continue;
            }

            $label = $jawaban[$p->id] ?? null;
            $pp = $label !== null ? PbiKriteriaParameter::find($p->pbi_parameter_id) : null;
            $opsi = $pp ? $pp->opsis()->where('label', $label)->first() : null;

            if (! $opsi) {
                continue;
            }

            $dipakai[] = $pp->id;

            PbiApbdJawaban::updateOrCreate(
                ['pbi_apbd_id' => $pbi->id, 'parameter_id' => $pp->id],
                [
                    'opsi_id' => $opsi->id,
                    'urutan' => $pp->urutan,
                    'parameter_nama' => $pp->nama,
                    'jawaban_label' => $opsi->label,
                    'indeks' => $pp->indeks,
                    'bobot' => $pp->bobot,
                    'indeks_terintegrasi' => $opsi->indeks_terintegrasi,
                    'skor' => round((float) $pp->indeks * (float) $pp->bobot * (float) $opsi->indeks_terintegrasi, 6),
                ]
            );
        }

        // Padanan PBI dari parameter GC yang hanya disembunyikan (aktif = false) tetap dipertahankan.
        $padananMaster = GcKriteriaParameter::whereNotNull('pbi_parameter_id')->pluck('pbi_parameter_id')->all();

        $pbi->jawabans()->whereNotNull('parameter_id')
            ->whereNotIn('parameter_id', array_merge($dipakai, $padananMaster))
            ->delete();
    }

    /** GET /akun-saya/pbi-apbd/{pbiApbd}/lengkapi */
    public function lengkapiData(Request $request, PbiApbd $pbiApbd)
    {
        $this->authorizePemilik($request, $pbiApbd);

        // Form TETAP ditampilkan walau data sudah tidak bisa diubah (mode baca saja);
        // penyimpanan tetap ditolak di lengkapiDataStore().
        $bisaEdit = $pbiApbd->masyarakatBisaEdit();

        // Draft otomatis hanya selama pengajuan belum pernah diajukan
        $bisaDraft = $bisaEdit && $pbiApbd->masyarakatMasihDraft();

        $parameters = $this->parameterUntukWarga();
        $master = $this->masterGc();
        $opsiLabels = $parameters->mapWithKeys(fn ($p) => [$p->id => $this->labelOpsi($p, $master)])->all();
        $nilai = $this->nilaiAwal($pbiApbd, $parameters);
        $perKelompok = $parameters->groupBy('kelompok');

        $gate = PbiGcForm::gate($parameters);
        $kerja = $gate ? ['gate' => $gate->id, 'dependen' => PbiGcForm::dependenIds($parameters)] : null;

        return view('masyarakat.pbi-apbd.lengkapi', compact(
            'pbiApbd', 'parameters', 'perKelompok', 'opsiLabels', 'nilai', 'master', 'kerja', 'bisaEdit', 'bisaDraft'
        ));
    }

    /**
     * POST /akun-saya/pbi-apbd/{pbiApbd}/lengkapi
     * aksi=draft -> simpan sebagian (dikirim otomatis dari halaman): isian tidak wajib lengkap,
     * status tidak berubah, skor tidak dihitung, dan belum dianggap diajukan ke Kelurahan.
     */
    public function lengkapiDataStore(Request $request, PbiApbd $pbiApbd)
    {
        $this->authorizePemilik($request, $pbiApbd);

        $draft = $request->input('aksi') === 'draft';

        // Sudah pernah diajukan: draft tidak boleh menimpa data yang sudah lengkap
        if ($draft && ! $pbiApbd->masyarakatMasihDraft()) {
            return response()->json(['message' => 'Pengajuan ini sudah diajukan, draft tidak dapat disimpan lagi.'], 409);
        }

        if (! $pbiApbd->masyarakatBisaEdit()) {
            return redirect()->route('masyarakat.dashboard')
                ->with('error', 'Pengajuan sudah disetujui, data tidak dapat diubah lagi.');
        }

        // Pengajuan yang ditolak lalu diperbaiki warga = diajukan ulang ke kelurahan
        $ajukanUlang = ! $draft && $pbiApbd->status === 'ditolak';

        $parameters = $this->parameterUntukWarga();

        $rules = [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'jawaban' => ['nullable', 'array'],
        ];
        $messages = [
            'latitude.required' => 'Lokasi belum berhasil diambil. Izinkan akses lokasi lalu coba lagi.',
            'longitude.required' => 'Lokasi belum berhasil diambil. Izinkan akses lokasi lalu coba lagi.',
            'jawaban.required' => 'Semua pertanyaan wajib dijawab.',
        ];

        $master = $this->masterGc();
        $attributes = [];

        // Field Ketenagakerjaan lain hanya berlaku bila responden bekerja ("Ya").
        $gate = PbiGcForm::gate($parameters);
        $bekerja = $gate && $request->input("jawaban.{$gate->id}") === 'Ya';
        $dependen = PbiGcForm::dependenIds($parameters);
        $lapParam = $parameters->firstWhere('sumber', 'lapangan_usaha');

        foreach ($parameters as $p) {
            $key = "jawaban.{$p->id}";
            $attributes[$key] = $p->nama;
            $attributes["{$key}.*"] = $p->nama;
            $messages["{$key}.required"] = "Pertanyaan \"{$p->nama}\" wajib dijawab.";
            $messages["{$key}.min"] = "Pilih minimal satu jawaban untuk \"{$p->nama}\".";

            // Responden tidak bekerja -> field turunan tidak diwajibkan & dikosongkan.
            if (in_array($p->id, $dependen, true) && ! $bekerja) {
                $rules[$key] = ['nullable'];
                continue;
            }

            $req = PbiGcForm::opsional($p) ? 'nullable' : 'required';
            $labels = $this->labelOpsi($p, $master);

            switch ($p->tipe_input) {
                case 'pilihan':
                    if ($labels && $this->isKelasParam($p)) {
                        // Kelas/semester: batas mengikuti jenjang (SMP/SMA = 3, SD = 6, D3 = 6, S1/D4 = 8 semester, dst).
                        $jenjangParam = $parameters->first(fn ($x) => $this->isJenjangParam($x));
                        $rules[$key] = [$req, 'string', function ($attr, $value, $fail) use ($request, $jenjangParam, $labels, $p) {
                            if ($value === null || $value === '') {
                                return;
                            }
                            $jenjang = $jenjangParam ? (string) $request->input("jawaban.{$jenjangParam->id}") : '';
                            $maks = self::batasKelas($jenjang)['maks'];
                            $angka = preg_match('/\d+/', (string) $value, $m) ? (int) $m[0] : null;

                            if ($maks > 0 && $angka !== null) {
                                if ($angka < 1 || $angka > $maks) {
                                    $fail("Jawaban \"{$p->nama}\" tidak sesuai dengan jenjang pendidikan yang dipilih (maksimal {$maks}).");
                                }
                                return;
                            }
                            if (! in_array($value, $labels, true)) {
                                $fail("Pilihan pada \"{$p->nama}\" tidak valid.");
                            }
                        }];
                        break;
                    }
                    $rules[$key] = $labels
                        ? [$req, 'string', Rule::in($labels)]
                        : [$req, 'string', 'max:500'];
                    break;

                case 'pilihan_ganda':
                    $rules[$key] = ($req === 'required' && $labels) ? ['required', 'array', 'min:1'] : ['nullable', 'array'];
                    $rules["{$key}.*"] = $labels ? ['string', Rule::in($labels)] : ['string', 'max:255'];
                    break;

                case 'angka':
                    $rules[$key] = PbiGcForm::lintangBujur($p) ? [$req, 'numeric'] : [$req, 'numeric', 'min:0'];
                    break;

                case 'tanggal':
                    $rules[$key] = [$req, 'date'];
                    break;

                case 'koordinat':
                    $rules[$key] = [$req, 'string', 'max:100'];
                    break;

                case 'foto':
                    $rules[$key] = ['nullable', 'image', 'max:5120'];
                    break;

                default: // teks
                    $rules[$key] = [$req, 'string', 'max:500'];
            }

            // Keterampilan bertanda eksklusif (mis. "Belum Ada") tidak boleh digabung dengan pilihan lain.
            if ($p->sumber === 'keterampilan') {
                $eksklusif = $master['keterampilan']->where('eksklusif', true)->pluck('nama')->all();
                $rules[$key][] = function ($attr, $value, $fail) use ($eksklusif) {
                    if (! is_array($value) || count($value) < 2) {
                        return;
                    }
                    $bentrok = array_intersect($value, $eksklusif);
                    if ($bentrok) {
                        $fail('Pilihan "' . implode('", "', $bentrok) . '" tidak boleh digabung dengan pilihan lain.');
                    }
                };
            }

            // Pekerjaan harus termasuk dalam lapangan usaha yang dipilih.
            if ($p->sumber === 'pekerjaan' && $lapParam) {
                $rules[$key][] = function ($attr, $value, $fail) use ($request, $lapParam, $master) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $lap = $master['lapangan']->firstWhere('nama', $request->input("jawaban.{$lapParam->id}"));
                    if (! $lap || ! $lap->pekerjaansAktif->contains('nama', $value)) {
                        $fail('Pekerjaan tidak sesuai dengan lapangan usaha yang dipilih.');
                    }
                };
            }
        }

        $messages += [
            'in' => 'Pilihan pada :attribute tidak valid.',
            'numeric' => ':attribute harus berupa angka.',
            'min.numeric' => ':attribute tidak boleh negatif.',
            'image' => ':attribute harus berupa gambar.',
            'max.file' => ':attribute maksimal 5 MB.',
            'date' => ':attribute bukan tanggal yang valid.',
        ];

        // Draft: tidak ada isian yang diwajibkan (yang terisi tetap divalidasi formatnya)
        if ($draft) {
            $rules = array_map(function ($r) {
                $r = array_map(fn ($x) => $x === 'required' ? 'nullable' : $x, (array) $r);

                return array_values(array_filter($r, fn ($x) => $x !== 'min:1'));
            }, $rules);
        }

        $validated = $request->validate($rules, $messages, $attributes);

        // Draft kosong dan belum pernah ada jawaban: jangan membuat baris apa pun
        if ($draft) {
            $adaIsian = collect($request->input('jawaban', []))->flatten()
                ->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
            $adaLama = GcPbiJawaban::where('pbi_apbd_id', $pbiApbd->id)->whereNotNull('jawaban')->exists();

            if (! $adaIsian && ! $adaLama) {
                return response()->json(['tersimpan' => false, 'persen' => 0]);
            }
        }

        DB::transaction(function () use ($pbiApbd, $parameters, $validated, $request, $ajukanUlang, $dependen, $bekerja, $draft) {
            $lama = GcPbiJawaban::where('pbi_apbd_id', $pbiApbd->id)->get()->keyBy('parameter_id');
            $final = [];

            foreach ($parameters as $p) {
                $row = $lama->get($p->id) ?? new GcPbiJawaban(['pbi_apbd_id' => $pbiApbd->id, 'parameter_id' => $p->id]);

                // Responden tidak bekerja -> jawaban field turunan dikosongkan.
                $kosongkan = in_array($p->id, $dependen, true) && ! $bekerja;
                $input = $kosongkan ? null : $request->input("jawaban.{$p->id}");
                $jawaban = null;
                $skor = null;

                switch ($p->tipe_input) {
                    case 'pilihan':
                        $jawaban = ($input === null || $input === '') ? null : (string) $input;
                        $opsi = $jawaban !== null ? $p->opsis->firstWhere('label', $jawaban) : null;
                        $skor = ($opsi && $opsi->nilai !== null) ? round((float) $p->bobot * (float) $opsi->nilai, 6) : null;
                        break;

                    case 'pilihan_ganda':
                        $arr = array_values(array_filter((array) $input, fn ($x) => $x !== null && $x !== ''));
                        $jawaban = $arr ? json_encode($arr, JSON_UNESCAPED_UNICODE) : null;
                        break;

                    case 'foto':
                        $jawaban = $row->jawaban;
                        if ($request->hasFile("jawaban.{$p->id}")) {
                            if ($jawaban) {
                                Storage::disk('public')->delete($jawaban);
                            }
                            $jawaban = $request->file("jawaban.{$p->id}")->store("pbi-apbd/gc/{$pbiApbd->id}", 'public');
                        }
                        break;

                    default:
                        $jawaban = ($input === null || $input === '') ? null : (string) $input;
                }

                $row->fill([
                    'kelompok' => $p->kelompok,
                    'urutan' => $p->urutan,
                    'nama_parameter' => $p->nama,
                    'tipe_input' => $p->tipe_input,
                    'bobot' => $p->bobot,
                    'jawaban' => $jawaban,
                    'skor' => $skor,
                ])->save();

                $final[$p->id] = $jawaban;
            }

            // Buang jawaban untuk parameter yang kini tidak tampil (dihapus/nonaktif di Master GC).
            // Baris snapshot yatim (parameter_id NULL) tidak disentuh.
            GcPbiJawaban::where('pbi_apbd_id', $pbiApbd->id)->whereNotNull('parameter_id')
                ->whereNotIn('parameter_id', GcKriteriaParameter::pluck('id'))->delete();
            // Draft berhenti di sini: hanya lokasi (kalau sudah terambil) yang ikut disimpan.
            // Status, skor/kelas kemiskinan, data_diisi_at, dan log baru diproses saat warga menekan "Simpan Data".
            if ($draft) {
                $lat = $validated['latitude'] ?? null;
                $lng = $validated['longitude'] ?? null;

                if (filled($lat) && filled($lng)) {
                    $pbiApbd->update(['latitude' => $lat, 'longitude' => $lng]);
                }

                return;
            }

            // Skor & kelas kemiskinan (rumus PBI) dihitung dari pertanyaan yang punya padanan PBI.
            $this->sinkronSkorPbi($pbiApbd, $parameters, $final);

            $pbiApbd->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'data_diisi_at' => now(),
                'dikembalikan_ke_masyarakat' => false,
            ] + ($ajukanUlang ? [
                // Masuk lagi ke antrean petugas kelurahan
                'status' => 'kelurahan',
                'sudah_diverifikasi_kelurahan' => false,
            ] : []));

            $pbiApbd->hitungUlangSkor();

            PbiApbdLog::create([
                'pbi_apbd_id' => $pbiApbd->id,
                'user_id' => $request->user()->id,
                'username' => $request->user()->name,
                'role_name' => 'Masyarakat',
                'task_name' => $ajukanUlang
                    ? 'Masyarakat memperbaiki data & mengajukan ulang setelah ditolak'
                    : 'Masyarakat mengisi data & parameter',
                'catatan' => null,
            ]);
        });

        if ($draft) {
            return response()->json([
                'tersimpan' => true,
                'persen' => PbiGcForm::persen(PbiGcForm::kelengkapan($pbiApbd)),
            ]);
        }

        return redirect()->route('masyarakat.dashboard')
            ->with('success', 'Data berhasil disimpan dan akan diverifikasi oleh petugas kelurahan.');
    }

    private function authorizePemilik(Request $request, PbiApbd $pbiApbd): void
    {
        abort_unless($pbiApbd->user_id === $request->user()->id, 403, 'Ini bukan pengajuan Anda.');
    }

    // ==========================================================
    // DTSEN
    // ==========================================================

    /** GET /akun-saya/dtsen — dari menu sidebar: buka form DTSEN milik user, atau kembali ke Beranda */
    public function dtsen(Request $request)
    {
        $permohonan = method_exists($request->user(), 'pengajuanDtsen')
            ? $request->user()->pengajuanDtsen()->latest()->first()
            : null;

        if ($permohonan) {
            return redirect()->route('masyarakat.dtsen.lengkapi', $permohonan);
        }

        return redirect()->route('masyarakat.dashboard')
            ->with('error', 'Anda belum memiliki pengajuan DTSEN. Silakan hubungi petugas kelurahan untuk mengajukan.');
    }

    /** GET /akun-saya/dtsen/{permohonan}/lengkapi */
    public function lengkapiDtsen(Request $request, DtsenPermohonan $permohonan)
    {
        $this->authorizePemilikDtsen($request, $permohonan);

        $permohonan->loadMissing(['detail', 'lampiran', 'logs']);

        // Form TETAP ditampilkan walau sudah tidak bisa diubah (mode baca saja);
        // penyimpanan tetap ditolak di lengkapiDtsenStore().
        return view('masyarakat.dtsen.lengkapi', [
            'dtsen' => $permohonan,
            'bisaEdit' => $this->dtsenBisaEdit($permohonan),
        ]);
    }

    /** POST /akun-saya/dtsen/{permohonan}/lengkapi */
    public function lengkapiDtsenStore(Request $request, DtsenPermohonan $permohonan)
    {
        $this->authorizePemilikDtsen($request, $permohonan);

        if (! $this->dtsenBisaEdit($permohonan)) {
            return redirect()->route('masyarakat.dashboard')
                ->with('error', 'Pengajuan sudah selesai diproses, data tidak dapat diubah lagi.');
        }

        $permohonan->loadMissing(['detail', 'lampiran']);
        $lampiranLama = $permohonan->lampiran;

        // Pengajuan yang ditolak lalu diperbaiki warga = diajukan ulang ke Kelurahan
        $ajukanUlang = $permohonan->status === DtsenPermohonan::STATUS_DITOLAK;

        $rules = [
            'nik' => ['required', 'digits:16'],
            'nama' => ['required', 'string', 'max:255'],
            'no_kk' => ['required', 'digits:16'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'alamat' => ['required', 'string', 'max:1000'],
            'scan_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            // Screenshot DTSEN wajib, kecuali sudah pernah diunggah
            'screenshot_dtsen' => [
                Rule::requiredIf(fn () => empty($lampiranLama->screenshot_dtsen ?? null)),
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048',
            ],
        ];
        $messages = [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'no_kk.required' => 'No. KK wajib diisi.',
            'no_kk.digits' => 'No. KK harus 16 digit angka.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'alamat.required' => 'Alamat wajib diisi.',
            'scan_ktp.mimes' => 'Scan KTP harus berformat JPG, PNG, atau PDF.',
            'scan_ktp.max' => 'Ukuran Scan KTP maksimal 2 MB.',
            'screenshot_dtsen.required' => 'Screenshot DTSEN wajib diunggah.',
            'screenshot_dtsen.mimes' => 'Screenshot DTSEN harus berformat JPG, PNG, atau PDF.',
            'screenshot_dtsen.max' => 'Ukuran Screenshot DTSEN maksimal 2 MB.',
        ];

        $validated = $request->validate($rules, $messages);

        // Simpan berkas baru dulu; kalau transaksi gagal, berkas ini dihapus lagi.
        $berkasBaru = [];
        foreach (['scan_ktp', 'screenshot_dtsen'] as $field) {
            if ($request->hasFile($field)) {
                $berkasBaru[$field] = $request->file($field)->store('dtsen/lampiran', 'public');
            }
        }

        try {
            DB::transaction(function () use ($permohonan, $validated, $berkasBaru, $request, $ajukanUlang) {
                $dataDetail = Arr::only($validated, ['nik', 'nama', 'no_kk', 'tanggal_lahir', 'alamat']);

                // Baris detail belum ada -> isi kolom wajib milik petugas supaya insert tidak gagal
                if (! $permohonan->detail) {
                    $dataDetail['alasan_keputusan_cetak'] = $permohonan->alasan_cetak ?? '';
                }

                $permohonan->detail()->updateOrCreate([], $dataDetail);

                if ($berkasBaru) {
                    $permohonan->lampiran()->updateOrCreate([], $berkasBaru);
                }

                // Ditolak lalu diperbaiki -> kembali ke antrean Kelurahan (status awal alur)
                if ($ajukanUlang) {
                    $permohonan->update([
                        'status' => DtsenPermohonan::STATUS_DIAJUKAN,
                        'current_role_id' => Role::where('slug', 'kelurahan')->value('id'),
                    ]);
                }

                // Tandai sudah diperbaiki (hanya kalau kolomnya ada di tabel)
                if (array_key_exists('dikembalikan_ke_masyarakat', $permohonan->getAttributes())) {
                    $permohonan->update(['dikembalikan_ke_masyarakat' => false]);
                }

                $permohonan->logs()->create([
                    'tanggal_proses' => now(),
                    'user_id' => $request->user()->id,
                    'username' => $request->user()->username ?? $request->user()->name,
                    'rolename' => 'Masyarakat',
                    'taskname' => $ajukanUlang
                        ? 'Masyarakat memperbaiki data & mengajukan ulang setelah ditolak'
                        : 'Masyarakat melengkapi data pengajuan',
                    'catatan' => null,
                ]);
            });
        } catch (\Throwable $e) {
            foreach ($berkasBaru as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        // Transaksi sukses -> hapus berkas lama yang sudah diganti
        foreach ($berkasBaru as $field => $path) {
            $lama = $lampiranLama->$field ?? null;
            if ($lama && $lama !== $path) {
                Storage::disk('public')->delete($lama);
            }
        }

        return redirect()->route('masyarakat.dashboard')
            ->with('success', 'Data DTSEN berhasil disimpan dan akan diperiksa oleh petugas.');
    }

    /** Pemilik = pengajuan ada di relasi User::pengajuanDtsen() milik user yang login. */
    private function authorizePemilikDtsen(Request $request, DtsenPermohonan $permohonan): void
    {
        $user = $request->user();

        $milik = method_exists($user, 'pengajuanDtsen')
            && $user->pengajuanDtsen()->whereKey($permohonan->getKey())->exists();

        abort_unless($milik, 403, 'Ini bukan pengajuan Anda.');
    }

    /**
     * Aturan sementara (sama seperti PBI): terkunci hanya kalau sudah selesai.
     * Kalau DtsenPermohonan punya masyarakatBisaEdit(), method itu yang dipakai.
     */
    private function dtsenBisaEdit(DtsenPermohonan $dtsen): bool
    {
        if (method_exists($dtsen, 'masyarakatBisaEdit')) {
            return (bool) $dtsen->masyarakatBisaEdit();
        }

        return $dtsen->status !== DtsenPermohonan::STATUS_SELESAI;
    }

    /** Kelengkapan DTSEN: 5 isian data diri + Screenshot DTSEN (Scan KTP opsional, tidak dihitung). */
    private function kelengkapanDtsen(DtsenPermohonan $dtsen): array
    {
        $detail = $dtsen->detail;
        $lampiran = $dtsen->lampiran;

        $cek = [
            filled($detail->nik ?? null),
            filled($detail->nama ?? null),
            filled($detail->no_kk ?? null),
            filled($detail->tanggal_lahir ?? null),
            filled($detail->alamat ?? null),
            filled($lampiran->screenshot_dtsen ?? null),
        ];

        $total = count($cek);
        $terisi = count(array_filter($cek));

        return [
            'persen' => (int) round($terisi / $total * 100),
            'terisi' => $terisi,
            'total' => $total,
            'lengkap' => $terisi === $total,
        ];
    }

    // ---- Aturan jenjang pendidikan -> kelas/semester (cermin dari aturanKelas() di _gc_field.blade.php) ----

    private function isJenjangParam($p): bool
    {
        $n = Str::lower((string) $p->nama);

        return Str::lower((string) $p->kelompok) === 'pendidikan' && str_contains($n, 'jenjang') && str_contains($n, 'pendidikan');
    }

    private function isKelasParam($p): bool
    {
        return Str::lower((string) $p->kelompok) === 'pendidikan' && str_contains(Str::lower((string) $p->nama), 'kelas tertinggi');
    }

    /** @return array{maks:int, semester:bool}  maks 0 = tidak dibatasi */
    public static function batasKelas(string $jenjang): array
    {
        $t = ' ' . trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower($jenjang))) . ' ';
        $has = fn (string $re) => (bool) preg_match('/\b(' . $re . ')\b/', $t);

        if ($has('s ?[23]|pasca|magister|doktor|profesi|spesialis')) return ['maks' => 0, 'semester' => true];
        if ($has('d ?(4|iv)|s ?1|sarjana')) return ['maks' => 8, 'semester' => true];
        if ($has('d ?(3|iii)')) return ['maks' => 6, 'semester' => true];
        if ($has('d ?(2|ii)')) return ['maks' => 4, 'semester' => true];
        if ($has('d ?(1|i)')) return ['maks' => 2, 'semester' => true];
        if ($has('perguruan tinggi|universitas|akademi|diploma|institut')) return ['maks' => 0, 'semester' => true];
        if ($has('smp|mts|smplb|sma|ma|mak|smk|smlb|paket b|paket c')) return ['maks' => 3, 'semester' => false];
        if ($has('sd|mi|sdlb|paket a|sekolah dasar')) return ['maks' => 6, 'semester' => false];

        return ['maks' => 0, 'semester' => false];
    }
}
