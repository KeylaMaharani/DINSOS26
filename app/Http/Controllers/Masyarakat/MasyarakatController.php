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
use App\Support\DokumenMasyarakat;
use App\Support\DtsenNotifikasi;
use App\Support\GroundCheckSinkron;
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

    /** Padanan label "status hubungan" di Master GC untuk pilihan registrasi (kunci = nilai tersimpan dari registrasi). */
    private const ALIAS_HUBUNGAN = [
        'kepala_keluarga' => ['Kepala Keluarga', 'Kepala Rumah Tangga', 'KRT'],
        'suami' => ['Suami'],
        'istri' => ['Istri'],
        'anak' => ['Anak', 'Anak Kandung'],
        'lainnya' => ['Lainnya', 'Famili Lain'],
    ];

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

        // Pertanyaan yang tampil ke warga = parameter aktif di Master Kriteria Ground Check
        // (akun lama: hanya yang bertaut ke 18 parameter PBI).
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
                // Semua jalur PBI APBD lewat daftar keluarga dulu
                'lengkapi_url' => $pbiApbd ? route('masyarakat.pbi-apbd.keluarga', $pbiApbd) : null,
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
            // Lewat daftar keluarga dulu
            route('masyarakat.pbi-apbd.keluarga', $r)
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
                    // Lewat daftar keluarga dulu
                    route('masyarakat.pbi-apbd.keluarga', $pbi)
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

        $pbi->tambahAnggotaKeluarga($data);

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

        // Ubah data anggota + samakan baris Daftar Sasaran Ground Check-nya
        $pbi->perbaruiAnggotaKeluarga($baris, $data);

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
     * Pertanyaan di form warga = parameter Master Kriteria Ground Check yang AKTIF, urut mengikuti master.
     *  - Pendaftar baru (form_lama = false) -> semua parameter aktif (39 variabel).
     *  - Akun lama (form_lama = true)       -> hanya yang bertaut ke 18 parameter PBI.
     * Hapus/nonaktifkan di Master GC -> otomatis hilang dari halaman warga.
     */
    private function parameterUntukWarga(?PbiApbd $pbi = null)
    {
        return PbiGcForm::parameters($pbi);
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
    private function labelOpsi(GcKriteriaParameter $p, array $master, ?PbiApbd $pbi = null): array
    {
        // Faskes / Puskesmas: pilihannya mengikuti kelurahan (kecamatan) tempat tinggal pemohon
        if ($p->tipe_input === 'pilihan' && ! in_array($p->sumber, ['lapangan_usaha', 'pekerjaan', 'keterampilan', 'penyakit_kronis'], true) && Str::contains(Str::lower((string) $p->nama), ['faskes', 'puskesmas'])) {
            return $this->opsiFaskes($pbi);
        }

        return match ($p->sumber) {
            'lapangan_usaha' => $master['lapangan']->pluck('nama')->all(),
            'pekerjaan' => $master['lapangan']->flatMap(fn ($l) => $l->pekerjaansAktif->pluck('nama'))->unique()->values()->all(),
            'keterampilan' => $master['keterampilan']->pluck('nama')->all(),
            'penyakit_kronis' => $master['penyakit']->pluck('nama')->all(),
            default => $p->opsis->pluck('label')->all(),
        };
    }

    /**
     * Daftar Puskesmas (config/faskes.php) untuk kecamatan tempat kelurahan pemohon berada.
     * Kecamatan dicari dari kelurahan di config/wilayah.php; kalau tidak ketemu dipakai kolom kecamatan PBI.
     * Kalau kecamatan tetap tidak dikenali, semua puskesmas ditampilkan.
     */
    private function opsiFaskes(?PbiApbd $pbi): array
    {
        $puskesmas = config('faskes.puskesmas', []);
        $semua = collect($puskesmas)->flatten()->unique()->values()->all();

        if (! $pbi) {
            return $semua;
        }

        $kel = Str::lower(trim((string) $pbi->desa_kelurahan));
        $kec = null;
        foreach (config('wilayah.kecamatan', []) as $namaKec => $daftarKel) {
            if ($kel !== '' && collect($daftarKel)->contains(fn ($k) => Str::lower(trim((string) $k)) === $kel)) {
                $kec = $namaKec;
                break;
            }
        }
        $kec = Str::lower(trim((string) ($kec ?: $pbi->kecamatan)));

        foreach ($puskesmas as $namaKec => $daftar) {
            if (Str::lower(trim((string) $namaKec)) === $kec) {
                return array_values($daftar);
            }
        }

        return $semua;
    }

    /**
     * Isian awal: jawaban tersimpan, atau (bila belum pernah diisi) data yang sudah diinput warga saat registrasi,
     * supaya warga tidak mengisi data yang sama dua kali. Pencocokan lewat nama pertanyaan di Master Kriteria GC.
     * Pertanyaan berjenis pilihan hanya diisi bila nilainya ada di daftar opsi ($opsiLabels).
     */
    private function nilaiAwal(PbiApbd $pbi, $parameters, array $opsiLabels = []): array
    {
        $nilai = GcPbiJawaban::where('pbi_apbd_id', $pbi->id)->whereNotNull('jawaban')->pluck('jawaban', 'parameter_id')->all();

        // Anggota keluarga yang merupakan pemohon (kepala keluarga/pendaftar) = data pribadi dari registrasi
        $pemohon = $pbi->anggotaKeluarga()->where('nik', $pbi->nik_kepala_keluarga)->first();

        $jk = strtoupper((string) ($pemohon->jenis_kelamin ?? ''));
        $tgl = ($pemohon && $pemohon->tanggal_lahir) ? Carbon::parse($pemohon->tanggal_lahir)->format('Y-m-d') : null;
        $jumlahArt = $pbi->anggotaKeluarga()->count();

        // nama pertanyaan (huruf kecil) => nilai dari registrasi
        $registrasi = [
            'nama' => $pbi->nama_kepala_keluarga,
            'nama lengkap' => $pbi->nama_kepala_keluarga,
            'nama kepala keluarga' => $pbi->nama_kepala_keluarga,
            'nama responden' => $pbi->nama_kepala_keluarga,
            'no kk' => $pbi->no_kk,
            'nomor kk' => $pbi->no_kk,
            'no. kk' => $pbi->no_kk,
            'nik' => $pbi->nik_kepala_keluarga,
            'no nik' => $pbi->nik_kepala_keluarga,
            'nomor nik' => $pbi->nik_kepala_keluarga,
            'alamat' => $pbi->alamat,
            'alamat lengkap' => $pbi->alamat,
            'rt' => $pbi->rt,
            'rw' => $pbi->rw,
            'rt/rw' => ($pbi->rt || $pbi->rw) ? trim($pbi->rt . '/' . $pbi->rw, '/') : null,
            'provinsi' => $pbi->provinsi,
            'kabupaten/kota' => $pbi->kabupaten_kota,
            'kab/kota' => $pbi->kabupaten_kota,
            'kota' => $pbi->kabupaten_kota,
            'kecamatan' => $pbi->kecamatan,
            'kelurahan' => $pbi->desa_kelurahan,
            'desa/kelurahan' => $pbi->desa_kelurahan,
            'no telp' => $pbi->no_telp,
            'no. telp' => $pbi->no_telp,
            'no telepon' => $pbi->no_telp,
            'no hp' => $pbi->no_telp,
            'nomor hp' => $pbi->no_telp,
            'email' => $pbi->email,
            'tempat lahir' => $pemohon->tempat_lahir ?? null,
            'tanggal lahir' => $tgl,
            'tgl lahir' => $tgl,
            'jenis kelamin' => $jk === 'L' ? 'Laki-laki' : ($jk === 'P' ? 'Perempuan' : null),
            'agama' => $pemohon->agama ?? null,
            'jumlah anggota keluarga' => $jumlahArt,
            'jumlah art' => $jumlahArt,
            'jumlah anggota rumah tangga' => $jumlahArt,
        ];

        // Cocokkan nilai dengan opsi tanpa peduli besar-kecil huruf ("islam" -> "Islam", "Laki-laki" -> "Laki-Laki")
        $cocokkan = function ($nilaiReg, array $labels) {
            $t = Str::lower(trim((string) $nilaiReg));
            foreach ($labels as $l) {
                if (Str::lower(trim((string) $l)) === $t) {
                    return $l;
                }
            }

            return null;
        };

        foreach ($parameters as $p) {
            if (isset($nilai[$p->id]) || in_array($p->tipe_input, ['foto', 'pilihan_ganda', 'koordinat'], true)) {
                continue;
            }

            $nama = Str::lower(trim($p->nama));
            $isi = $registrasi[$nama] ?? null;

            if ($isi === null || $isi === '') {
                continue;
            }

            if ($p->tipe_input === 'pilihan') {
                $isi = $cocokkan($isi, $opsiLabels[$p->id] ?? []);
            } elseif ($p->tipe_input === 'angka') {
                // Angka hanya dari pertanyaan jumlah anggota (dropdown 0-50)
                $isi = (is_numeric($isi) && $isi >= 0 && $isi <= 50 && str_starts_with($nama, 'jumlah')) ? (string) (int) $isi : null;
            } elseif ($p->tipe_input === 'tanggal') {
                $isi = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $isi) ? $isi : null;
            }

            if ($isi !== null && $isi !== '') {
                $nilai[$p->id] = (string) $isi;
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

        // Akun lama (form 18 parameter): jawaban PBI yang tidak punya padanan di GC jangan dihapus.
        if ($pbi->form_lama) {
            return;
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

        $parameters = $this->parameterUntukWarga($pbiApbd);
        $master = $this->masterGc();
        $opsiLabels = $parameters->mapWithKeys(fn ($p) => [$p->id => $this->labelOpsi($p, $master, $pbiApbd)])->all();
        $nilai = $this->nilaiAwal($pbiApbd, $parameters, $opsiLabels);
        // Akun lama: isi dari jawaban 18 parameter PBI. Foto kartu keluarga: pakai Scan KK pengajuan.
        $nilai = PbiGcForm::lengkapiNilai($pbiApbd, $parameters, $nilai);
        $perKelompok = $parameters->groupBy('kelompok');

        $gate = PbiGcForm::gate($parameters);
        $kerja = $gate ? ['gate' => $gate->id, 'dependen' => PbiGcForm::dependenIds($parameters)] : null;

        // ---- Alamat domisili: SELALU dari alamat KK & terkunci. ----
        // Bila wilayah berbeda, warga mengisinya di field koreksi (disimpan terpisah), bukan di sini.
        $petaAlamat = $this->petaAlamat($parameters);
        $sesuai = $parameters->first(fn ($p) => $this->isSesuaiKkParam($p));
        $kk = $this->nilaiKk($pbiApbd);
        $wilayah = $this->wilayahOpsi($pbiApbd);

        foreach ($petaAlamat as $id => $peran) {
            if (filled($kk[$peran] ?? null)) {
                $nilai[$id] = $kk[$peran];
            }
        }

        // ---- "Alamat sesuai KK": SELALU dari alamat registrasi & terkunci ----
        $petaKk = $this->petaAlamatKk($parameters);
        foreach ($petaKk as $id => $_) {
            $nilai[$id] = $kk['alamat'];
        }

        // ---- Terkunci dari registrasi: status hubungan, lapangan usaha, pekerjaan ----
        $kunciReg = $this->nilaiTerkunciRegistrasi($pbiApbd, $parameters, $opsiLabels, $master);
        foreach ($kunciReg as $id => $v) {
            $nilai[$id] = $v;
        }
        $terkunci = array_fill_keys(array_keys($kunciReg), true);

        // ---- Lapangan usaha & Pekerjaan: terisi dari registrasi (bila belum pernah dijawab), tetap bisa diubah ----
        $isiAwal = $this->isiAwalPekerjaan($pbiApbd, $parameters, $master);
        foreach ($isiAwal as $id => $v) {
            if (blank($nilai[$id] ?? null)) {
                $nilai[$id] = $v;
            }
        }

        // ---- "Apakah No KK sama?" Ya -> "Apakah nama sama?" ikut Ya ----
        $sama = $this->pasanganSama($parameters);

        // ---- Nama & No KK: SELALU dari registrasi (tampil sebagai teks). ----
        $identitas = $this->petaIdentitas($pbiApbd, $parameters);
        foreach ($identitas as $id => $cfg) {
            $nilai[$id] = $cfg['nilai'];
        }

        // ---- Pemicu field baru bila jawaban "Tidak": id pertanyaan => jenis field koreksi ----
        $koreksiGate = [];
        if ($sama) {
            $koreksiGate[$sama['sumber']] = 'no_kk';
            $koreksiGate[$sama['target']] = 'nama';
        }
        if ($sesuai) {
            $koreksiGate[$sesuai->id] = 'wilayah';
        }
        $koreksi = $pbiApbd->gcKoreksi;

        return view('masyarakat.pbi-apbd.lengkapi', compact(
            'pbiApbd', 'parameters', 'perKelompok', 'opsiLabels', 'nilai', 'master', 'kerja', 'bisaEdit', 'bisaDraft',
            'petaAlamat', 'sesuai', 'kk', 'wilayah', 'petaKk', 'terkunci', 'sama', 'identitas', 'isiAwal',
            'koreksiGate', 'koreksi'
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

        $parameters = $this->parameterUntukWarga($pbiApbd);

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

        // ---- Alamat domisili: SELALU dipaksa dari KK. ----
        $petaAlamat = $this->petaAlamat($parameters);
        $sesuaiParam = $parameters->first(fn ($p) => $this->isSesuaiKkParam($p));
        $kk = $this->nilaiKk($pbiApbd);
        $wilayah = $this->wilayahOpsi($pbiApbd);

        // Alamat sesuai KK + data terkunci dari registrasi: nilainya dipaksa dari server
        $petaKk = $this->petaAlamatKk($parameters);
        $opsiLabelsKunci = $parameters->mapWithKeys(fn ($x) => [$x->id => $this->labelOpsi($x, $master, $pbiApbd)])->all();
        $terkunci = $this->nilaiTerkunciRegistrasi($pbiApbd, $parameters, $opsiLabelsKunci, $master);

        // Nama / No KK: SELALU dipaksa dari registrasi.
        $kunciIdentitas = [];
        foreach ($this->petaIdentitas($pbiApbd, $parameters) as $id => $cfg) {
            $kunciIdentitas[$id] = $cfg['nilai'];
        }

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

            // Kepemilikan perahu: Bogor tidak punya perairan, nilainya dipaksa 0 (lihat di bawah).
            if ($this->isNolParam($p)) {
                $rules[$key] = ['nullable'];
                continue;
            }

            // Alamat sesuai KK & isian terkunci dari registrasi: tidak divalidasi dari kiriman browser
            if (isset($petaKk[$p->id]) || isset($terkunci[$p->id])) {
                $rules[$key] = ['nullable'];
                continue;
            }

            // Nama / No KK terkunci: nilai dipaksa dari registrasi
            if (isset($kunciIdentitas[$p->id])) {
                $rules[$key] = ['nullable'];
                continue;
            }

            // Field alamat domisili: nilai dipaksa dari KK, tidak divalidasi dari kiriman browser.
            if (isset($petaAlamat[$p->id])) {
                $rules[$key] = ['nullable'];
                continue;
            }

            $req = PbiGcForm::opsional($p) ? 'nullable' : 'required';
            $labels = $this->labelOpsi($p, $master, $pbiApbd);

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
                    $rules[$key] = PbiGcForm::lintangBujur($p) ? [$req, 'numeric'] : [$req, 'integer', 'between:0,50'];
                    break;

                case 'tanggal':
                    $rules[$key] = [$req, 'date'];
                    break;

                case 'koordinat':
                    $rules[$key] = [$req, 'string', 'max:100'];
                    break;

                case 'foto':
                    // Foto wajib (bila "Wajib diisi" aktif di master) kecuali sudah pernah diunggah.
                    // Foto kartu keluarga dianggap ada bila Scan KK pengajuan sudah terunggah.
                    $sudahAda = filled(GcPbiJawaban::where('pbi_apbd_id', $pbiApbd->id)
                            ->where('parameter_id', $p->id)->value('jawaban'))
                        || (PbiGcForm::isFotoKk($p) && filled($pbiApbd->scan_kk));
                    $rules[$key] = [($req === 'required' && ! $sudahAda) ? 'required' : 'nullable', 'image', 'max:5120'];
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

        // ---- Field baru bila jawaban "Tidak" (disimpan terpisah, tidak mengubah data awal) ----
        $pasangan = $this->pasanganSama($parameters);
        $tidak = fn ($id) => $id && $request->input("jawaban.{$id}") === 'Tidak';
        $kkTidak = $tidak($pasangan['sumber'] ?? null);
        $namaTidak = $tidak($pasangan['target'] ?? null);
        $wilTidak = $tidak($sesuaiParam->id ?? null);

        $rules['koreksi'] = ['nullable', 'array'];
        $rules['koreksi.no_kk_baru'] = $kkTidak ? ['required', 'digits:16'] : ['nullable'];
        $rules['koreksi.nama_baru'] = $namaTidak ? ['required', 'string', 'max:150'] : ['nullable'];
        // Kabupaten selalu KOTA BOGOR (dipaksa server), jadi tidak divalidasi dari kiriman browser.
        $rules['koreksi.kecamatan_baru'] = $wilTidak ? ['required', 'string', Rule::in(array_keys($wilayah))] : ['nullable'];
        $rules['koreksi.kelurahan_baru'] = $wilTidak
            ? ['required', 'string', function ($attr, $value, $fail) use ($request, $wilayah) {
                if (! in_array($value, $wilayah[(string) $request->input('koreksi.kecamatan_baru')] ?? [], true)) {
                    $fail('Desa/Kelurahan tidak sesuai dengan kecamatan yang dipilih.');
                }
            }]
            : ['nullable'];

        $messages += [
            'koreksi.no_kk_baru.required' => 'No KK baru wajib diisi.',
            'koreksi.no_kk_baru.digits' => 'No KK baru harus 16 digit angka.',
            'koreksi.nama_baru.required' => 'Nama baru wajib diisi.',
            'koreksi.kecamatan_baru.required' => 'Kecamatan wajib dipilih.',
            'koreksi.kecamatan_baru.in' => 'Kecamatan tidak valid.',
            'koreksi.kelurahan_baru.required' => 'Kelurahan/Desa wajib dipilih.',
        ];

        $koreksiData = [
            'no_kk_baru' => $kkTidak ? trim((string) $request->input('koreksi.no_kk_baru')) : null,
            'nama_baru' => $namaTidak ? trim((string) $request->input('koreksi.nama_baru')) : null,
            'kabupaten_baru' => $wilTidak ? config('wilayah.kab_kota') : null,
            'kecamatan_baru' => $wilTidak ? $request->input('koreksi.kecamatan_baru') : null,
            'kelurahan_baru' => $wilTidak ? $request->input('koreksi.kelurahan_baru') : null,
        ];

        $messages += [
            'in' => 'Pilihan pada :attribute tidak valid.',
            'numeric' => ':attribute harus berupa angka.',
            'min.numeric' => ':attribute tidak boleh negatif.',
            'integer' => ':attribute harus berupa angka bulat.',
            'between.numeric' => ':attribute harus antara 0 sampai 50.',
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

        DB::transaction(function () use ($pbiApbd, $parameters, $validated, $request, $ajukanUlang, $dependen, $bekerja, $draft, $petaAlamat, $kk, $petaKk, $terkunci, $kunciIdentitas, $koreksiData) {
            // Simpan data koreksi di tabel terpisah. Memilih "Ya" lagi -> kolom dikosongkan.
            if ($pbiApbd->gcKoreksi()->exists() || collect($koreksiData)->filter()->isNotEmpty()) {
                $pbiApbd->gcKoreksi()->updateOrCreate([], $koreksiData);
            }

            $lama = GcPbiJawaban::where('pbi_apbd_id', $pbiApbd->id)->get()->keyBy('parameter_id');
            $final = [];

            foreach ($parameters as $p) {
                $row = $lama->get($p->id) ?? new GcPbiJawaban(['pbi_apbd_id' => $pbiApbd->id, 'parameter_id' => $p->id]);

                // Responden tidak bekerja -> jawaban field turunan dikosongkan.
                $kosongkan = in_array($p->id, $dependen, true) && ! $bekerja;
                $input = $kosongkan ? null : $request->input("jawaban.{$p->id}");

                // Kepemilikan perahu selalu 0, tidak peduli apa yang dikirim browser.
                if ($this->isNolParam($p)) {
                    $input = 0;
                }

                // "Alamat sesuai KK" selalu dari registrasi; status hubungan/lapangan usaha/pekerjaan
                // dipaksa dari data registrasi (kecuali responden tidak bekerja -> dikosongkan di atas).
                if (isset($petaKk[$p->id])) {
                    $input = $kk['alamat'] ?? null;
                }
                if (! $kosongkan && isset($terkunci[$p->id])) {
                    $input = $terkunci[$p->id];
                }

                // Nama / No KK terkunci: selalu dari registrasi, abaikan kiriman browser
                if (isset($kunciIdentitas[$p->id])) {
                    $input = $kunciIdentitas[$p->id];
                }

                // Alamat domisili: nilai dipaksa dari data registrasi, abaikan kiriman browser.
                if (isset($petaAlamat[$p->id])) {
                    $input = $kk[$petaAlamat[$p->id]] ?? null;
                }

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
                        // Foto kartu keluarga tanpa unggahan baru dibiarkan kosong: tampilannya otomatis
                        // memakai Scan KK pengajuan (tidak disalin, jadi tidak ikut terhapus bila Scan KK diganti).
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

            // Buang jawaban untuk parameter yang kini tidak tampil (dihapus di Master GC).
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

        // Foto KTP / KK dll yang diunggah di form ini ikut masuk ke menu Dokumen (jenis yang sudah ada tidak ditimpa)
        DokumenMasyarakat::imporDariPbi($pbiApbd);

        // Berkas lampiran yang masih kosong diisi otomatis dari menu Dokumen (yang sudah terpasang tidak diubah)
        DokumenMasyarakat::isiBilaKosongPbi($pbiApbd);

        // Submit sungguhan -> anggota keluarga masuk Ground Check + notifikasi petugas
        $pbiApbd->sinkronGroundCheck();

        return redirect()->route('masyarakat.dashboard')
            ->with('success', 'Data berhasil disimpan dan akan diverifikasi oleh petugas kelurahan.');
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

        // Belum punya pengajuan -> langsung buka form kosong (diisi otomatis dari data yang sudah ada)
        return redirect()->route('masyarakat.dtsen.baru');
    }

    /** GET /akun-saya/dtsen/baru — form DTSEN untuk warga yang belum punya pengajuan */
    public function dtsenBaru(Request $request)
    {
        $ada = method_exists($request->user(), 'pengajuanDtsen')
            ? $request->user()->pengajuanDtsen()->latest()->first()
            : null;

        if ($ada) {
            return redirect()->route('masyarakat.dtsen.lengkapi', $ada);
        }

        $permohonan = new DtsenPermohonan();
        [$prefill, $asal] = $this->prefillDtsen($request, $permohonan);

        return view('masyarakat.dtsen.lengkapi', [
            'dtsen' => $permohonan, // belum tersimpan; dibuat saat warga menyimpan
            'bisaEdit' => true,
            'prefill' => $prefill,
            'asal' => $asal,
        ]);
    }

    /** GET /akun-saya/dtsen/{permohonan}/lengkapi */
    public function lengkapiDtsen(Request $request, DtsenPermohonan $permohonan)
    {
        $this->authorizePemilikDtsen($request, $permohonan);

        $permohonan->loadMissing(['detail', 'lampiran', 'logs']);

        // Lampiran yang masih kosong langsung dipasang dari menu Dokumen (yang sudah terpasang tidak diubah)
        if ($this->dtsenBisaEdit($permohonan)) {
            DokumenMasyarakat::isiBilaKosongDtsen($permohonan);
            $permohonan->load('lampiran');
        }

        [$prefill, $asal] = $this->prefillDtsen($request, $permohonan);

        // Form TETAP ditampilkan walau sudah tidak bisa diubah (mode baca saja);
        // penyimpanan tetap ditolak di lengkapiDtsenStore().
        return view('masyarakat.dtsen.lengkapi', [
            'dtsen' => $permohonan,
            'bisaEdit' => $this->dtsenBisaEdit($permohonan),
            'prefill' => $prefill, // [field => nilai]
            'asal' => $asal,       // [field => label sumber]
        ]);
    }

    /** POST /akun-saya/dtsen/{permohonan}/lengkapi */
    public function lengkapiDtsenStore(Request $request, ?DtsenPermohonan $permohonan = null)
    {
        // Tanpa {permohonan} di URL = warga mengajukan DTSEN untuk pertama kali (POST /dtsen/baru)
        $baru = $permohonan === null;

        if ($baru) {
            $ada = method_exists($request->user(), 'pengajuanDtsen')
                ? $request->user()->pengajuanDtsen()->latest()->first()
                : null;

            if ($ada) {
                return redirect()->route('masyarakat.dtsen.lengkapi', $ada);
            }

            $permohonan = new DtsenPermohonan();
            $lampiranLama = $permohonan->lampiran()->getRelated(); // model kosong, belum tersimpan
        } else {
            $this->authorizePemilikDtsen($request, $permohonan);

            if (! $this->dtsenBisaEdit($permohonan)) {
                return redirect()->route('masyarakat.dashboard')
                    ->with('error', 'Pengajuan sedang diproses petugas atau sudah selesai, data tidak dapat diubah lagi.');
            }

            $permohonan->loadMissing(['detail', 'lampiran']);
            $lampiranLama = $permohonan->lampiran;
        }

        // Pengajuan yang ditolak lalu diperbaiki warga = diajukan ulang ke Operator Dinsos
        $ajukanUlang = $permohonan->status === DtsenPermohonan::STATUS_DITOLAK;

        // Lampiran: dipakai dari menu Dokumen bila sudah ada; kalau belum ada, diunggah lewat form ini.
        $rules = [
            'nik' => ['required', 'digits:16'],
            'nama' => ['required', 'string', 'max:255'],
            'no_kk' => ['required', 'digits:16'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'alamat' => ['required', 'string', 'max:1000'],
            'alasan_keputusan_cetak' => ['required', 'string', 'max:1000'],
            'scan_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'screenshot_dtsen' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
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
            'alasan_keputusan_cetak.required' => 'Alasan keputusan cetak wajib diisi.',
            'scan_ktp.mimes' => 'Scan KTP harus berformat JPG, PNG, atau PDF.',
            'scan_ktp.max' => 'Ukuran Scan KTP maksimal 2 MB.',
            'scan_ktp.uploaded' => 'Scan KTP gagal diunggah. Coba lagi dengan berkas yang lebih kecil.',
            'screenshot_dtsen.mimes' => 'Screenshot DTSEN harus berformat JPG, PNG, atau PDF.',
            'screenshot_dtsen.max' => 'Ukuran Screenshot DTSEN maksimal 2 MB.',
            'screenshot_dtsen.uploaded' => 'Screenshot DTSEN gagal diunggah. Coba lagi dengan berkas yang lebih kecil.',
        ];

        $validated = $request->validate($rules, $messages);

        // Field data diri yang terkunci di form dipaksa dari server (readonly di browser bisa dilewati).
        // Tidak berlaku saat petugas meminta perbaikan (ditolak / dikembalikan): semua field terbuka.
        if (! $ajukanUlang && ! ($permohonan->dikembalikan_ke_masyarakat ?? false)) {
            [$prefillKunci] = $this->prefillDtsen($request, $permohonan);

            foreach (['nik', 'nama', 'no_kk', 'tanggal_lahir', 'alamat'] as $f) {
                $v = $prefillKunci[$f] ?? null;

                if (! filled($v)) {
                    continue;
                }

                // Sama seperti di tampilan: nilai angka 16 digit yang tidak valid tidak dikunci
                if (in_array($f, ['nik', 'no_kk'], true) && ! preg_match('/^\d{16}$/', (string) $v)) {
                    continue;
                }

                $validated[$f] = $v;
            }
        }

        // Screenshot DTSEN wajib: sudah terpasang di pengajuan, ada di menu Dokumen, atau diunggah sekarang
        $user = $request->user();
        if (blank($lampiranLama->screenshot_dtsen ?? null)
            && ! $request->hasFile('screenshot_dtsen')
            && ! DokumenMasyarakat::ada($user, 'screenshot_dtsen')) {
            throw ValidationException::withMessages([
                'screenshot_dtsen' => 'Screenshot DTSEN wajib diunggah.',
            ]);
        }

        // Berkas yang diunggah lewat form ini disimpan juga di menu Dokumen, lalu menggantikan lampiran di pengajuan
        $paksa = [];
        foreach (['scan_ktp' => 'ktp', 'screenshot_dtsen' => 'screenshot_dtsen'] as $field => $jenis) {
            if ($request->hasFile($field)) {
                DokumenMasyarakat::tambahDariUnggahan($user, $jenis, $request->file($field));
                $paksa[] = $field;
            }
        }

        // Lampiran yang masih kosong (atau baru diganti) disalin dari menu Dokumen; kalau transaksi gagal, berkas ini dihapus lagi (blok catch di bawah).
        $berkasBaru = DokumenMasyarakat::salinUntukDtsen($user, $lampiranLama, $paksa);

        try {
            DB::transaction(function () use (&$permohonan, $validated, $berkasBaru, $request, $ajukanUlang, $baru) {
                // Pengajuan baru dibuat di sini, langsung masuk antrean Operator Dinsos
                if ($baru) {
                    $operatorRole = Role::firstOrCreate(
                        ['slug' => 'operator_dinsos'],
                        ['name' => 'Operator Dinsos', 'slug' => 'operator_dinsos']
                    );

                    $permohonan = DtsenPermohonan::create([
                        'user_id' => $request->user()->id,
                        'nik' => $validated['nik'],
                        'nama_pemohon' => $validated['nama'],
                        'alamat' => $validated['alamat'],
                        'alasan_cetak' => $validated['alasan_keputusan_cetak'],
                        'status' => DtsenPermohonan::STATUS_AWAL,
                        'current_role_id' => $operatorRole->id,
                        'kelurahan' => optional($this->pbiTerbaru($request))->desa_kelurahan ?? '',
                        'tanggal_insert' => now(),
                        'created_by' => $request->user()->id,
                    ]);
                }

                $dataDetail = Arr::only($validated, ['nik', 'nama', 'no_kk', 'tanggal_lahir', 'alamat', 'alasan_keputusan_cetak']);

                $permohonan->detail()->updateOrCreate([], $dataDetail);

                if ($berkasBaru) {
                    $permohonan->lampiran()->updateOrCreate([], $berkasBaru);
                }

                // Ditolak lalu diperbaiki -> kembali ke antrean Operator Dinsos (status awal alur)
                if ($ajukanUlang) {
                    $permohonan->update([
                        'status' => DtsenPermohonan::STATUS_AWAL,
                        'current_role_id' => Role::where('slug', 'operator_dinsos')->value('id'),
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
                    'taskname' => $baru
                        ? 'Masyarakat mengajukan DTSEN'
                        : ($ajukanUlang
                            ? 'Masyarakat memperbaiki data & mengajukan ulang setelah ditolak'
                            : 'Masyarakat melengkapi data pengajuan'),
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

        GroundCheckSinkron::individu([
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'no_kk' => $validated['no_kk'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'alamat' => $validated['alamat'],
            'kelurahan' => $permohonan->kelurahan,
        ]);

        // Pengajuan baru / diajukan ulang -> beri tahu Operator Dinsos lewat lonceng
        if ($baru || $ajukanUlang) {
            DtsenNotifikasi::keRole(
                $permohonan,
                'operator_dinsos',
                $baru ? 'Pengajuan DTSEN baru dari warga' : 'Pengajuan DTSEN diajukan ulang',
                'Pengajuan ' . $permohonan->no_permohonan . ' atas nama ' . $validated['nama'] . ' menunggu validasi Anda.'
            );
        }

        return redirect()->route('masyarakat.dashboard')
            ->with('success', 'Data DTSEN berhasil disimpan dan akan diperiksa oleh petugas.');
    }

    /** Pemilik = pengajuan PBI APBD ada di relasi User::pengajuanPbiApbd() milik user yang login. */
    private function authorizePemilik(Request $request, PbiApbd $pbiApbd): void
    {
        $milik = $request->user()
            ->pengajuanPbiApbd()
            ->whereKey($pbiApbd->getKey())
            ->exists();

        abort_unless($milik, 403, 'Ini bukan pengajuan Anda.');
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
     * Form terbuka hanya selama pengajuan di tahap awal (Validasi Dinsos), ditolak, atau dikembalikan.
     * Aturannya ada di DtsenPermohonan::masyarakatBisaEdit().
     */
    private function dtsenBisaEdit(DtsenPermohonan $dtsen): bool
    {
        return $dtsen->masyarakatBisaEdit();
    }

    /**
     * Nilai awal field Data Detail DTSEN: nik, nama, no_kk, tanggal_lahir, alamat.
     * @return array{0: array<string,string>, 1: array<string,string>} [nilai, label sumber]
     */
    private function prefillDtsen(Request $request, DtsenPermohonan $permohonan): array
    {
        $user = $request->user();
        $detail = $permohonan->detail;

        $sebelumnya = method_exists($user, 'pengajuanDtsen')
            ? $user->pengajuanDtsen()->when($permohonan->exists, fn ($q) => $q->whereKeyNot($permohonan->getKey()))->with('detail')->latest()->get()
            : collect();

        $pbi = $this->pbiTerbaru($request);
        $pemohonPbi = $pbi
            ? $pbi->anggotaKeluarga()->where('nik', $pbi->nik_kepala_keluarga)->first()
            : null;

        $alamatPbi = $pbi
            ? collect([
                $pbi->alamat,
                ($pbi->rt || $pbi->rw) ? 'RT ' . $pbi->rt . '/RW ' . $pbi->rw : null,
                $pbi->desa_kelurahan,
                $pbi->kecamatan,
            ])->filter()->implode(', ')
            : null;

        $tgl = fn ($v) => filled($v) ? Carbon::parse($v)->format('Y-m-d') : null;

        // Tiap field: daftar kandidat [label, nilai] dengan prioritas dari atas ke bawah
        $kandidat = [
            'nik' => array_merge(
                [['pengajuan ini', $detail->nik ?? null], ['pengajuan ini', $permohonan->nik]],
                $sebelumnya->map(fn ($d) => ['pengajuan DTSEN sebelumnya', $d->detail->nik ?? $d->nik])->all(),
                [['data PBI APBD', $pbi->nik_kepala_keluarga ?? null], ['akun Anda', $user->username]],
            ),
            'nama' => array_merge(
                [['pengajuan ini', $detail->nama ?? null], ['pengajuan ini', $permohonan->nama_pemohon]],
                $sebelumnya->map(fn ($d) => ['pengajuan DTSEN sebelumnya', $d->detail->nama ?? $d->nama_pemohon])->all(),
                [['data PBI APBD', $pbi->nama_kepala_keluarga ?? null], ['akun Anda', $user->name]],
            ),
            'no_kk' => array_merge(
                [['pengajuan ini', $detail->no_kk ?? null]],
                $sebelumnya->map(fn ($d) => ['pengajuan DTSEN sebelumnya', $d->detail->no_kk ?? null])->all(),
                [['data PBI APBD', $pbi->no_kk ?? null]],
            ),
            'tanggal_lahir' => array_merge(
                [['pengajuan ini', $tgl($detail->tanggal_lahir ?? null)]],
                $sebelumnya->map(fn ($d) => ['pengajuan DTSEN sebelumnya', $tgl($d->detail->tanggal_lahir ?? null)])->all(),
                [['data PBI APBD', $tgl($pemohonPbi->tanggal_lahir ?? null)]],
            ),
            'alamat' => array_merge(
                [['pengajuan ini', $detail->alamat ?? null], ['pengajuan ini', $permohonan->alamat]],
                $sebelumnya->map(fn ($d) => ['pengajuan DTSEN sebelumnya', $d->detail->alamat ?? $d->alamat])->all(),
                [['data PBI APBD', $alamatPbi]],
            ),
            'alasan_keputusan_cetak' => array_merge(
                [['pengajuan ini', $detail->alasan_keputusan_cetak ?? null], ['pengajuan ini', $permohonan->alasan_cetak]],
                $sebelumnya->map(fn ($d) => ['pengajuan DTSEN sebelumnya', $d->detail->alasan_keputusan_cetak ?? $d->alasan_cetak])->all(),
            ),
        ];

        $nilai = $asal = [];
        foreach ($kandidat as $field => $daftar) {
            foreach ($daftar as [$label, $v]) {
                if (filled($v)) {
                    $nilai[$field] = (string) $v;
                    $asal[$field] = $label;
                    break;
                }
            }
        }

        return [$nilai, $asal];
    }

    /** Kelengkapan DTSEN: 6 isian data diri + Screenshot DTSEN (Scan KTP opsional, tidak dihitung). */
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
            filled($detail->alasan_keputusan_cetak ?? null),
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

    /** Parameter angka yang selalu 0 di Kota Bogor: kepemilikan perahu & ternak babi. */
    private function isNolParam($p): bool
    {
        return $p->tipe_input === 'angka' && Str::contains(Str::lower((string) $p->nama), ['perahu', 'babi']);
    }

    // ---- Alamat domisili: sesuai KK / input manual (wilayah berantai) ----

    /**
     * Peran field alamat pada sebuah parameter GC: alamat | rt | rw | kecamatan | kelurahan,
     * atau null bila bukan field alamat. Dikenali lewat nama parameter di Master Kriteria GC.
     */
    private function peranAlamat($p): ?string
    {
        if (! in_array($p->tipe_input, ['teks', 'pilihan'], true)) {
            return null;
        }

        $n = Str::lower(trim((string) $p->nama));

        // Pertanyaan Ya/Tidak ("Apakah alamat ... sesuai KK?") bukan isian alamat
        if (Str::contains($n, ['apakah', 'sesuai'])) {
            return null;
        }

        $adaRt = (bool) preg_match('/\brt\b/', $n);
        $adaRw = (bool) preg_match('/\brw\b/', $n);

        return match (true) {
            Str::contains($n, 'kecamatan') => 'kecamatan',
            Str::contains($n, ['kelurahan', 'desa']) => 'kelurahan',
            $adaRt && ! $adaRw => 'rt',
            $adaRw && ! $adaRt => 'rw',
            Str::contains($n, 'alamat') => 'alamat',
            default => null,
        };
    }

    /** Pertanyaan Ya/Tidak pemicu alamat domisili: "Apakah wilayah sama?" (atau "...sesuai KK?"). */
    private function isSesuaiKkParam($p): bool
    {
        if ($p->tipe_input !== 'pilihan') {
            return false;
        }

        $n = Str::lower((string) $p->nama);

        return (Str::contains($n, 'wilayah') && Str::contains($n, 'sama'))
            || (Str::contains($n, 'sesuai') && Str::contains($n, ['kk', 'kartu keluarga']));
    }

    /**
     * [id parameter => peran] untuk grup field alamat yang dikunci/diisi dari KK.
     * Bila ada parameter berkata "domisili", hanya itu yang dipakai; kalau tidak, semua field alamat yang dikenali.
     */
    private function petaAlamat($parameters): array
    {
        $semua = $domisili = [];

        foreach ($parameters as $p) {
            $peran = $this->peranAlamat($p);
            if (! $peran) {
                continue;
            }

            $semua[$p->id] = $peran;

            if (Str::contains(Str::lower((string) $p->nama), 'domisili')) {
                $domisili[$p->id] = $peran;
            }
        }

        return $domisili ?: $semua;
    }

    /** Alamat KK dari data registrasi (sumber isian otomatis). */
    private function nilaiKk(PbiApbd $pbi): array
    {
        return [
            'alamat' => $pbi->alamat,
            'rt' => $pbi->rt,
            'rw' => $pbi->rw,
            'kecamatan' => strtoupper(trim((string) $pbi->kecamatan)),
            'kelurahan' => strtoupper(trim((string) $pbi->desa_kelurahan)),
        ];
    }

    /**
     * [id parameter => true] untuk "Alamat sesuai KK" (teks, selalu terkunci dari alamat registrasi).
     * Pertanyaan Ya/Tidak ("Apakah ...") tidak ikut.
     */
    private function petaAlamatKk($parameters): array
    {
        $peta = [];

        foreach ($parameters as $p) {
            if (! in_array($p->tipe_input, ['teks', 'pilihan'], true)) {
                continue;
            }

            $n = Str::lower(trim((string) $p->nama));

            if (Str::contains($n, 'apakah')) {
                continue;
            }

            if (Str::contains($n, 'alamat') && Str::contains($n, ['kk', 'kartu keluarga'])) {
                $peta[$p->id] = true;
            }
        }

        return $peta;
    }

    /**
     * Pasangan "Apakah No KK sama?" (sumber) -> "Apakah nama sama?" (target): sumber dijawab Ya, target otomatis Ya.
     * Mengembalikan ['sumber' => id, 'target' => id] atau null bila salah satunya tidak ada di master.
     */
    private function pasanganSama($parameters): ?array
    {
        $sumber = $parameters->first(function ($p) {
            $n = Str::lower((string) $p->nama);

            return $p->tipe_input === 'pilihan'
                && Str::contains($n, 'sama')
                && preg_match('/\b(no|nomor)\.?\s*kk\b/', $n);
        });

        $target = $parameters->first(function ($p) {
            $n = Str::lower((string) $p->nama);

            return $p->tipe_input === 'pilihan'
                && Str::contains($n, 'sama')
                && Str::contains($n, 'nama')
                && ! preg_match('/\bkk\b/', $n);
        });

        return ($sumber && $target) ? ['sumber' => $sumber->id, 'target' => $target->id] : null;
    }

    /**
     * Field identitas (Nama, No KK) yang SELALU diambil dari registrasi (ditampilkan sebagai teks).
     * Hasil: [id parameter field => ['gate' => id pertanyaan Ya/Tidak, 'nilai' => nilai registrasi]]
     */
    private function petaIdentitas(PbiApbd $pbi, $parameters): array
    {
        $pasangan = $this->pasanganSama($parameters);

        if (! $pasangan) {
            return [];
        }

        $cari = fn (array $daftar) => $parameters->first(
            fn ($p) => $p->tipe_input === 'teks'
                && in_array(preg_replace('/\s+/', ' ', Str::lower(trim((string) $p->nama))), $daftar, true)
        );

        $peta = [];

        $kk = $cari(['no kk', 'nomor kk', 'no. kk']);
        if ($kk && filled($pbi->no_kk)) {
            $peta[$kk->id] = ['gate' => $pasangan['sumber'], 'nilai' => (string) $pbi->no_kk];
        }

        $nama = $cari(['nama', 'nama lengkap', 'nama kepala keluarga', 'nama responden']);
        if ($nama && filled($pbi->nama_kepala_keluarga)) {
            $peta[$nama->id] = ['gate' => $pasangan['target'], 'nilai' => (string) $pbi->nama_kepala_keluarga];
        }

        return $peta;
    }

    /**
     * Isian yang dikunci dari data registrasi: [id parameter => nilai].
     * - Jenis kelamin, No. telp, Email           <- Step 2
     * - Status hubungan dengan kepala keluarga  <- status hubungan di Step 2
     * (Lapangan usaha & Pekerjaan tidak dikunci: lihat isiAwalPekerjaan()).
     * Hanya yang nilainya BENAR-BENAR cocok dengan opsi yang dikunci; yang tidak cocok tetap bisa diisi warga.
     */
    private function nilaiTerkunciRegistrasi(PbiApbd $pbi, $parameters, array $opsiLabels, array $master): array
    {
        $pemohon = $pbi->anggotaKeluarga()->where('nik', $pbi->nik_kepala_keluarga)->first();

        $hasil = [];

        $cari = fn (array $daftar, string $tipe) => $parameters->first(
            fn ($p) => $p->tipe_input === $tipe
                && in_array(preg_replace('/\s+/', ' ', Str::lower(trim((string) $p->nama))), $daftar, true)
        );

        // ---- No. telp & Email (dari Step 2) ----
        $pTelp = $cari(['no telp', 'no. telp', 'no telepon', 'no. telepon', 'nomor telepon', 'nomor telp', 'no hp', 'no. hp', 'nomor hp', 'no handphone', 'telp', 'telepon'], 'teks');
        if ($pTelp && filled($pbi->no_telp)) {
            $hasil[$pTelp->id] = (string) $pbi->no_telp;
        }

        $pEmail = $cari(['email', 'e-mail', 'alamat email'], 'teks');
        if ($pEmail && filled($pbi->email)) {
            $hasil[$pEmail->id] = (string) $pbi->email;
        }

        if (! $pemohon) {
            return $hasil;
        }

        // ---- Jenis kelamin (dari Step 2) ----
        $pJk = $cari(['jenis kelamin'], 'pilihan');
        if ($pJk) {
            $jk = strtoupper(substr(trim((string) $pemohon->jenis_kelamin), 0, 1));
            $kandidatJk = $jk === 'L' ? ['Laki-laki', 'Pria', 'L'] : ($jk === 'P' ? ['Perempuan', 'Wanita', 'P'] : []);
            $cocokJk = $this->cocokOpsi($kandidatJk, $opsiLabels[$pJk->id] ?? []);

            if ($cocokJk !== null) {
                $hasil[$pJk->id] = $cocokJk;
            }
        }

        // ---- Tanggal lahir (dari Step 2) ----
        $pTgl = $cari(['tanggal lahir', 'tgl lahir', 'tgl. lahir'], 'tanggal');
        if ($pTgl && $pemohon->tanggal_lahir) {
            $hasil[$pTgl->id] = Carbon::parse($pemohon->tanggal_lahir)->format('Y-m-d');
        }

        // ---- Status hubungan ----
        $paramHub = $parameters->first(function ($p) {
            $n = Str::lower((string) $p->nama);

            return $p->tipe_input === 'pilihan'
                && Str::contains($n, 'hubungan')
                && Str::contains($n, 'kepala keluarga');
        });

        if ($paramHub) {
            $kunciHub = strtolower(str_replace([' ', '-'], '_', trim((string) $pemohon->hubungan_keluarga)));
            $kandidat = self::ALIAS_HUBUNGAN[$kunciHub] ?? array_filter([self::HUBUNGAN[$kunciHub] ?? null]);
            $cocok = $this->cocokOpsi($kandidat, $opsiLabels[$paramHub->id] ?? []);

            if ($cocok !== null) {
                $hasil[$paramHub->id] = $cocok;
            }
        }

        return $hasil;
    }

    /**
     * Isian awal Lapangan usaha & Pekerjaan dari pekerjaan saat registrasi (Step 2): [id parameter => nilai].
     * Hanya nilai awal: warga tetap bisa mengubahnya, dan server tidak memaksa nilainya.
     */
    private function isiAwalPekerjaan(PbiApbd $pbi, $parameters, array $master): array
    {
        $pemohon = $pbi->anggotaKeluarga()->where('nik', $pbi->nik_kepala_keluarga)->first();
        $pLap = $parameters->firstWhere('sumber', 'lapangan_usaha');
        $pPek = $parameters->firstWhere('sumber', 'pekerjaan');

        // Master GC bisa hanya punya pertanyaan "Lapangan usaha" tanpa "Pekerjaan": lapangan usaha tetap diisi sendiri
        if (! $pemohon || ! $pLap) {
            return [];
        }

        $pk = $this->temukanPekerjaan((string) $pemohon->pekerjaan, $master['lapangan']);

        if (! $pk) {
            return [];
        }

        $hasil = [$pLap->id => $pk['lapangan']];

        if ($pPek) {
            $hasil[$pPek->id] = $pk['pekerjaan'];
        }

        return $hasil;
    }

    /** Cocokkan salah satu kandidat dengan daftar opsi (abaikan besar-kecil huruf & tanda baca). */
    private function cocokOpsi(array $kandidat, array $labels): ?string
    {
        $n = fn ($t) => preg_replace('/[^a-z0-9]/', '', Str::lower((string) $t));

        foreach ($kandidat as $k) {
            foreach ($labels as $l) {
                if ($n($k) !== '' && $n($k) === $n($l)) {
                    return $l;
                }
            }
        }

        return null;
    }

    /** Cari pekerjaan (nama) di master lapangan usaha; kembalikan nama lapangan & pekerjaan persis seperti di master. */
    private function temukanPekerjaan(string $nama, $lapangan): ?array
    {
        $t = Str::lower(trim($nama));

        if ($t === '') {
            return null;
        }

        foreach ($lapangan as $l) {
            foreach ($l->pekerjaansAktif as $pk) {
                if (Str::lower(trim($pk->nama)) === $t) {
                    return ['lapangan' => $l->nama, 'pekerjaan' => $pk->nama];
                }
            }
        }

        return null;
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
