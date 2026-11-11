<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GcJawaban;
use App\Models\GcKeterampilan;
use App\Models\GcKriteriaParameter;
use App\Models\GcLapanganUsaha;
use App\Models\GcPbiJawaban;
use App\Models\GcPenyakitKronis;
use App\Models\GcSasaran;
use App\Models\GcVerifikasi;
use App\Models\PbiApbd;
use App\Support\PbiGcForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GroundCheckController extends Controller
{
    private const TAHUN = 2026;

    /** Kelompok (tab) yang boleh diisi anggota selain kepala keluarga: A sampai D. */
    private const KELOMPOK_NON_KEPALA = ['kependudukan', 'pendidikan', 'ketenagakerjaan', 'kesehatan'];

    /** Langkah 3: daftar sasaran (1 baris = 1 KK) + ringkasan (dengan filter tahun, rentang tanggal & RW). */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;

        // Filter tahun & tanggal = waktu masyarakat MENGIRIM PENGAJUAN (tabel pbi_apbds.created_at),
        // dihubungkan ke sasaran lewat No KK. Badge/progres/ringkasan tetap memakai tahun Ground Check.
        $tahunPilih = $request->query('tahun');
        $tahunPilih = ctype_digit((string) $tahunPilih) ? (int) $tahunPilih : null;
        $tglDari = $request->query('tgl_dari') ?: null;
        $tglSampai = $request->query('tgl_sampai') ?: null;
        $tahun = self::TAHUN;

        // Filter RW (RW berasal dari isian RT/RW saat registrasi, disalin ke gc_sasarans).
        $rw = trim((string) $request->query('rw')) ?: null;

        $adaFilterPengajuan = $tahunPilih || $tglDari || $tglSampai;

        // Syarat pengajuan (dipakai bersama oleh daftar dan kartu ringkasan).
        $filterPengajuan = function ($s) use ($tahunPilih, $tglDari, $tglSampai) {
            $s->whereExists(function ($e) use ($tahunPilih, $tglDari, $tglSampai) {
                $e->select(DB::raw(1))
                    ->from('pbi_apbds as p')
                    ->whereRaw('TRIM(p.no_kk) = TRIM(gc_sasarans.no_kk)')
                    ->when($tahunPilih, fn ($x) => $x->whereYear('p.created_at', $tahunPilih))
                    ->when($tglDari, fn ($x) => $x->whereDate('p.created_at', '>=', $tglDari))
                    ->when($tglSampai, fn ($x) => $x->whereDate('p.created_at', '<=', $tglSampai));
            });
        };

        // Syarat RW: keluarga (No KK) yang punya anggota ber-RW tersebut.
        $filterRw = function ($s) use ($rw) {
            $s->whereIn('gc_sasarans.no_kk', function ($x) use ($rw) {
                $x->from('gc_sasarans as r')->select('r.no_kk')->whereRaw('TRIM(r.rw) = ?', [$rw]);
            });
        };

        $sasarans = GcSasaran::query()
            ->select('gc_sasarans.*')
            ->selectSub(function ($s) {
                $s->from('gc_sasarans as m')->selectRaw('count(*)')->whereColumn('m.no_kk', 'gc_sasarans.no_kk');
            }, 'kk_total')
            ->selectSub(function ($s) use ($tahun) {
                $s->from('gc_sasarans as m')
                    ->join('gc_verifikasis as v', 'v.sasaran_id', '=', 'm.id')
                    ->selectRaw('count(*)')
                    ->whereColumn('m.no_kk', 'gc_sasarans.no_kk')
                    ->where('v.tahun', $tahun)
                    ->where('v.status_entry', 'submit');
            }, 'kk_selesai')
            ->with(['verifikasis' => fn ($v) => $v->where('tahun', $tahun)])
            // SATU BARIS PER KK: wakil keluarga = anggota yang NIK-nya sama dengan kepala keluarga
            // pada pengajuan masyarakat; kalau tidak ada, anggota dengan id terkecil.
            ->whereRaw('gc_sasarans.id = (
                select m2.id from gc_sasarans m2
                left join pbi_apbds p2
                    on TRIM(p2.no_kk) = m2.no_kk and TRIM(p2.nik_kepala_keluarga) = m2.nik
                where m2.no_kk = gc_sasarans.no_kk
                order by case when p2.id is null then 1 else 0 end, m2.id
                limit 1
            )')
            // Filter tahun & tanggal pengajuan: hanya sasaran yang No KK-nya punya pengajuan
            // masyarakat sesuai (tahun & rentang tanggal dicek pada satu pengajuan yang sama).
            ->when($adaFilterPengajuan, $filterPengajuan)
            ->when($rw, $filterRw)
            // Pencarian mencocokkan SEMUA anggota KK (mencari "Siti" tetap memunculkan keluarganya).
            ->when($q !== '', function ($s) use ($q) {
                $like = "%{$q}%";
                $s->where(function ($w) use ($like) {
                    $w->where('gc_sasarans.no_kk', 'like', $like)
                        ->orWhereIn('gc_sasarans.no_kk', function ($x) use ($like) {
                            $x->from('gc_sasarans as a')->select('a.no_kk')
                                ->where(fn ($y) => $y->where('a.nama', 'like', $like)->orWhere('a.nik', 'like', $like));
                        });
                });
            })
            ->orderBy('kelurahan')->orderBy('no_kk')->orderBy('nama')
            ->paginate($perPage)
            ->withQueryString();

        // Kartu ringkasan mengikuti filter tahun/tanggal/RW (tanpa pencarian nama), dihitung per orang.
        $basis = GcSasaran::query()
            ->when($adaFilterPengajuan, $filterPengajuan)
            ->when($rw, $filterRw);
        $total = (clone $basis)->count();
        $sudah = (clone $basis)
            ->whereHas('verifikasis', fn ($v) => $v->where('tahun', $tahun)->where('status_entry', 'submit'))
            ->count();
        $belum = max($total - $sudah, 0);
        $progres = $total > 0 ? round($sudah / $total * 100, 1) : 0;

        // Daftar tahun untuk dropdown: tahun pengajuan yang ada + 2024 sampai tahun ini.
        $tahunList = DB::table('pbi_apbds')
            ->selectRaw('YEAR(created_at) as th')
            ->whereNotNull('created_at')
            ->distinct()
            ->pluck('th')
            ->map(fn ($t) => (int) $t)
            ->merge(range((int) now()->year, 2024))
            ->unique()->sortDesc()->values();

        // Pilihan RW untuk dropdown. Kalau petugas punya kelurahan tugas (kolom users.kelurahan),
        // pilihan dibatasi ke RW di kelurahan itu; Super Admin / tanpa kelurahan melihat semua RW.
        $petugas = Auth::user();
        $kelurahanPetugas = ($petugas && ! optional($petugas->role)->isSuperAdmin() && filled($petugas->kelurahan ?? null))
            ? strtoupper(trim((string) $petugas->kelurahan))
            : null;

        $rwList = GcSasaran::query()
            ->when($kelurahanPetugas, fn ($s) => $s->whereRaw('UPPER(TRIM(kelurahan)) = ?', [$kelurahanPetugas]))
            ->whereNotNull('rw')
            ->whereRaw("TRIM(rw) <> ''")
            ->selectRaw('TRIM(rw) as rw_bersih')
            ->distinct()
            ->pluck('rw_bersih')
            ->sort(SORT_NATURAL)
            ->values();

        // Kecamatan/Kelurahan di form Tambah kini memakai dropdown berantai (config/wilayah.php),
        // jadi daftar saran dari data sasaran tidak diperlukan lagi.
        return view('admin.ground-check.index', compact(
            'sasarans', 'total', 'sudah', 'belum', 'progres', 'q', 'perPage', 'tahunList', 'tahun', 'rwList', 'rw'
        ));
    }

    /**
     * TAMBAH sasaran baru ke Daftar Sasaran Ground Check.
     * Bila No KK sudah ada, orang ini otomatis menjadi anggota KK tersebut dan
     * wilayah/alamat/desil yang dikosongkan diambil dari anggota KK yang sudah ada.
     */
    public function store(Request $request)
    {
        // Samakan penulisan dengan config/wilayah.php (huruf besar) sebelum divalidasi
        $request->merge([
            'kecamatan' => strtoupper(trim((string) $request->input('kecamatan'))) ?: null,
            'kelurahan' => strtoupper(trim((string) $request->input('kelurahan'))) ?: null,
        ]);

        $wilayah = config('wilayah.kecamatan', []);

        $data = $request->validateWithBag('tambah', [
            'nik' => ['required', 'digits:16', 'unique:gc_sasarans,nik'],
            'nama' => ['required', 'string', 'max:255'],
            'no_kk' => ['required', 'digits:16'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'kecamatan' => ['nullable', 'string', Rule::in(array_keys($wilayah))],
            'kelurahan' => ['nullable', 'string', function ($attr, $value, $fail) use ($request, $wilayah) {
                if (! in_array($value, $wilayah[$request->input('kecamatan')] ?? [], true)) {
                    $fail('Desa/Kelurahan tidak sesuai dengan kecamatan yang dipilih.');
                }
            }],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'desil' => ['nullable', 'integer', 'between:1,10'],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar di Daftar Sasaran.',
            'nama.required' => 'Nama wajib diisi.',
            'no_kk.required' => 'No KK wajib diisi.',
            'no_kk.digits' => 'No KK harus 16 digit angka.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'kecamatan.in' => 'Kecamatan tidak valid.',
            'desil.integer' => 'Desil harus berupa angka.',
            'desil.between' => 'Desil harus antara 1 sampai 10.',
        ]);

        $data['nama'] = Str::upper(trim($data['nama']));

        // Anggota KK yang sudah ada -> lengkapi data wilayah yang kosong.
        $anggotaLama = GcSasaran::where('no_kk', $data['no_kk'])->first();
        if ($anggotaLama) {
            foreach (['kecamatan', 'kelurahan', 'alamat', 'rt', 'rw', 'desil'] as $f) {
                if (blank($data[$f] ?? null)) {
                    $data[$f] = $anggotaLama->{$f};
                }
            }
        }

        $sasaran = GcSasaran::create($data);

        return redirect()->route('ground-check.keluarga', $sasaran->no_kk)
            ->with('success', $sasaran->nama . ' berhasil ditambahkan ke Daftar Sasaran. Klik ikon pensil untuk mulai mengisi data.');
    }

    /** Langkah 5: anggota satu KK (kepala keluarga ditampilkan paling atas). */
    public function keluarga(string $noKk)
    {
        $tahun = self::TAHUN;

        // Kepala keluarga: dari hubungan "kepala keluarga" pada anggota pengajuan masyarakat.
        $kepalaNik = trim((string) $this->kepalaNik($noKk));

        $anggota = GcSasaran::where('no_kk', $noKk)
            ->with(['verifikasis' => fn ($v) => $v->where('tahun', $tahun)])
            ->get()
            ->sortBy(fn ($a) => (trim((string) $a->nik) === $kepalaNik ? '0' : '1') . Str::lower((string) $a->nama))
            ->values();

        abort_if($anggota->isEmpty(), 404);

        return view('admin.ground-check.keluarga', compact('noKk', 'anggota', 'kepalaNik'));
    }

    /** Langkah 6: simpan status (Form Verifikasi Data). */
    public function statusStore(Request $request, GcSasaran $sasaran)
    {
        $data = $request->validate([
            'status_temuan' => ['required', Rule::in(array_keys(GcVerifikasi::STATUS_TEMUAN))],
        ], [
            'status_temuan.required' => 'Status wajib dipilih.',
            'status_temuan.in' => 'Status tidak valid.',
        ]);

        $v = GcVerifikasi::firstOrNew(['sasaran_id' => $sasaran->id, 'tahun' => self::TAHUN]);

        // KUNCI: status yang sudah disimpan tidak bisa diubah lewat Form Verifikasi lagi.
        if ($v->exists) {
            if ($v->status_temuan === 'ditemukan') {
                // Sudah "Ditemukan": lanjut isi form saja.
                return redirect()->route('ground-check.form', $sasaran);
            }

            return redirect()->route('ground-check.keluarga', $sasaran->no_kk)
                ->with('error', $sasaran->nama . ' berstatus ' . (GcVerifikasi::STATUS_TEMUAN[$v->status_temuan] ?? $v->status_temuan) . ' dan tidak dapat diubah.');
        }

        $v->status_temuan = $data['status_temuan'];
        $v->user_id = Auth::id();

        if ($data['status_temuan'] === 'ditemukan') {
            $v->status_entry = 'draft';
            $v->submitted_at = null;
            $v->pernyataan = false;
        } else {
            // Pindah/Tidak Ditemukan/Meninggal: tidak ada form lanjutan, langsung selesai & terkunci.
            $v->status_entry = 'submit';
            $v->submitted_at = now();
            $v->pernyataan = false;
        }

        $v->save();

        if ($data['status_temuan'] === 'ditemukan') {
            return redirect()->route('ground-check.form', $sasaran);
        }

        // Kembali ke daftar keluarga untuk lanjut memverifikasi anggota lain.
        return redirect()->route('ground-check.keluarga', $sasaran->no_kk)
            ->with('success', 'Status ' . $sasaran->nama . ' disimpan: ' . GcVerifikasi::STATUS_TEMUAN[$data['status_temuan']] . '.');
    }

    /** Langkah 7-13: form tab A-G (kepala keluarga) atau A-D (istri/anak/anggota lain). */
    public function form(Request $request, GcSasaran $sasaran)
    {
        $v = $this->verifikasiDitemukan($sasaran);
        if (! $v) {
            return redirect()->route('ground-check.keluarga', $sasaran->no_kk)
                ->with('error', 'Pilih status "Ditemukan" terlebih dahulu sebelum mengisi form.');
        }

        // Petugas melihat SEMUA parameter (flag aktif hanya untuk halaman masyarakat),
        // tetapi anggota non-kepala dibatasi tab A-D.
        $params = $this->paramsUntuk($sasaran);
        $master = $this->masterKetenagakerjaan();

        $tabs = [];
        foreach (GcKriteriaParameter::TAB as $key => $tab) {
            $isi = $params->whereIn('kelompok', $tab['kelompok'])->values();
            if ($isi->isNotEmpty()) {
                $tabs[] = $tab + ['key' => $key, 'params' => $isi];
            }
        }

        // Hanya jawaban yang benar-benar terisi (draf yang berisi null/kosong tidak dihitung).
        $tersimpan = [];
        foreach ($v->jawabans()->get() as $j) {
            if ($j->parameter_id && $j->jawaban !== null && $j->jawaban !== '') {
                $tersimpan[$j->parameter_id] = $j->jawaban;
            }
        }

        // Prefill (identitas, jawaban warga, data keluarga) hanya bila belum ada satu pun jawaban tersimpan.
        $nilai = $tersimpan ?: $this->prefill($sasaran, $params);

        // Perahu & ternak babi: di Kota Bogor selalu 0 (menimpa draf lama yang mungkin sudah berisi angka lain).
        $nolIds = $params->filter(fn ($p) => $this->isNolParam($p))->pluck('id')->values()->all();
        foreach ($nolIds as $id) {
            $nilai[$id] = 0;
        }

        // Foto kartu keluarga: tampilkan Scan KK dari pengajuan PBI bila petugas belum unggah sendiri.
        $scanKk = $this->pbiSasaran($sasaran)?->scan_kk;
        if (filled($scanKk)) {
            foreach ($params as $p) {
                if (PbiGcForm::isFotoKk($p) && blank($nilai[$p->id] ?? null)) {
                    $nilai[$p->id] = $scanKk;
                }
            }
        }

        // Pertanyaan pemicu di tab Ketenagakerjaan + daftar field yang bergantung padanya.
        $gate = $this->gateKerja($params);
        $kerja = $gate ? [
            'gate' => $gate->id,
            'dependen' => $params->where('kelompok', 'ketenagakerjaan')
                ->where('id', '!=', $gate->id)->pluck('id')->values()->all(),
        ] : null;

        // Badge progres tab mengikuti isian yang tampil (termasuk hasil prefill).
        $terisi = array_filter($nilai, fn ($x) => $x !== null && $x !== '');
        $statusTab = $this->statusTab($tabs, $terisi, $kerja);

        $tabAwal = (int) session('tab', 0);

        return view('admin.ground-check.form', compact(
            'sasaran', 'v', 'tabs', 'nilai', 'tabAwal', 'master', 'kerja', 'statusTab', 'nolIds'
        ));
    }

    /** Simpan draf / submit. Saat submit, semua isian wajib diisi. */
    public function simpan(Request $request, GcSasaran $sasaran)
    {
        $v = $this->verifikasiDitemukan($sasaran);
        abort_if(! $v, 403);

        $submit = $request->input('aksi') === 'submit';
        // Hanya parameter yang berlaku untuk anggota ini (non-kepala: A-D saja).
        $params = $this->paramsUntuk($sasaran);
        $existing = $v->jawabans()->get()->keyBy('parameter_id');
        $master = $this->masterKetenagakerjaan();
        $lapParam = $params->firstWhere('sumber', 'lapangan_usaha');
        $scanKk = $this->pbiSasaran($sasaran)?->scan_kk;

        // Field Ketenagakerjaan lain hanya berlaku jika responden bekerja ("Ya").
        $gate = $this->gateKerja($params);
        $bekerja = $gate && $request->input("jawaban.{$gate->id}") === 'Ya';
        $dependenIds = $gate
            ? $params->where('kelompok', 'ketenagakerjaan')->where('id', '!=', $gate->id)->pluck('id')->all()
            : [];

        $rules = [];
        $attributes = [];

        foreach ($params as $p) {
            $key = "jawaban.{$p->id}";
            $attributes[$key] = $p->nama;

            // Responden tidak bekerja -> field turunan tidak diwajibkan & akan dikosongkan.
            if (in_array($p->id, $dependenIds, true) && ! $bekerja) {
                $rules[$key] = ['nullable'];
                continue;
            }

            // Perahu & ternak babi: nilainya dipaksa 0 saat disimpan (lihat di bawah), apa pun kiriman browser.
            if ($this->isNolParam($p)) {
                $rules[$key] = ['nullable'];
                continue;
            }

            $req = $submit ? 'required' : 'nullable';
            $labels = $this->labelOpsi($p, $master);

            switch ($p->tipe_input) {
                case 'pilihan':
                    $rules[$key] = $labels
                        ? [$req, 'string', Rule::in($labels)]
                        : [$req, 'string', 'max:500'];
                    break;

                case 'pilihan_ganda':
                    // Tanpa opsi di master tidak ada yang bisa dipilih, jadi tidak diwajibkan.
                    $rules[$key] = $submit && $labels ? ['required', 'array', 'min:1'] : ['nullable', 'array'];
                    $rules["{$key}.*"] = $labels ? ['string', Rule::in($labels)] : ['string', 'max:255'];
                    $attributes["{$key}.*"] = $p->nama;
                    break;

                case 'angka':
                    $rules[$key] = $this->koordinatAngka($p)
                        ? [$req, 'numeric']
                        : [$req, 'numeric', 'min:0'];
                    break;

                case 'tanggal':
                    $rules[$key] = [$req, 'date'];
                    break;

                case 'koordinat':
                    $rules[$key] = [$req, 'string', 'max:100'];
                    break;

                case 'foto':
                    // Foto kartu keluarga dianggap ada bila Scan KK pengajuan PBI sudah terunggah.
                    $sudahAda = ! empty(optional($existing->get($p->id))->jawaban)
                        || (PbiGcForm::isFotoKk($p) && filled($scanKk));
                    $rules[$key] = [$submit && ! $sudahAda ? 'required' : 'nullable', 'image', 'max:5120'];
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
                    if (! $lap || ! $lap->pekerjaans->contains('nama', $value)) {
                        $fail('Pekerjaan tidak sesuai dengan lapangan usaha yang dipilih.');
                    }
                };
            }
        }

        if ($submit) {
            $rules['pernyataan'] = ['accepted'];
        }

        $request->validate($rules, [
            'required' => ':attribute wajib diisi.',
            'in' => 'Pilihan pada :attribute tidak valid.',
            'numeric' => ':attribute harus berupa angka.',
            'min.numeric' => ':attribute tidak boleh negatif.',
            'min.array' => 'Pilih minimal satu jawaban untuk :attribute.',
            'image' => ':attribute harus berupa gambar.',
            'max.file' => ':attribute maksimal 5 MB.',
            'date' => ':attribute bukan tanggal yang valid.',
            'accepted' => 'Centang pernyataan kebenaran data terlebih dahulu.',
        ], $attributes);

        DB::transaction(function () use ($request, $params, $existing, $v, $submit, $bekerja, $dependenIds) {
            foreach ($params as $p) {
                $row = $existing->get($p->id) ?? new GcJawaban(['verifikasi_id' => $v->id, 'parameter_id' => $p->id]);

                // Responden tidak bekerja -> jawaban field turunan dikosongkan.
                $kosongkan = in_array($p->id, $dependenIds, true) && ! $bekerja;
                $input = $kosongkan ? null : $request->input("jawaban.{$p->id}");

                // Kepemilikan perahu & ternak babi selalu 0, tidak peduli apa yang dikirim browser.
                if ($this->isNolParam($p)) {
                    $input = 0;
                }

                $jawaban = null;
                $skor = null;

                switch ($p->tipe_input) {
                    case 'pilihan':
                        $jawaban = ($input === null || $input === '') ? null : (string) $input;
                        $skor = $jawaban !== null ? optional($p->opsis->firstWhere('label', $jawaban))->nilai : null;
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
                            $jawaban = $request->file("jawaban.{$p->id}")->store("ground-check/{$v->id}", 'public');
                        }
                        break;

                    default:
                        $jawaban = ($input === null || $input === '') ? null : (string) $input;
                }

                $row->fill([
                    'nama_parameter' => $p->nama,
                    'bobot' => $p->bobot,
                    'jawaban' => $jawaban,
                    'skor' => $skor,
                ])->save();
            }

            $v->pernyataan = $submit;
            $v->status_entry = $submit ? 'submit' : 'draft';
            $v->submitted_at = $submit ? now() : null;
            $v->user_id = Auth::id();
            $v->save();
        });

        // Submit maupun Simpan Draft: kembali ke daftar anggota keluarga (bukan ke index awal).
        if ($submit) {
            return redirect()->route('ground-check.keluarga', $sasaran->no_kk)
                ->with('success', 'Data ' . $sasaran->nama . ' berhasil disubmit.');
        }

        return redirect()->route('ground-check.keluarga', $sasaran->no_kk)
            ->with('success', 'Draf ' . $sasaran->nama . ' tersimpan. Klik ikon form untuk melanjutkan kapan saja.');
    }

    private function verifikasiDitemukan(GcSasaran $sasaran): ?GcVerifikasi
    {
        return GcVerifikasi::where('sasaran_id', $sasaran->id)
            ->where('tahun', self::TAHUN)
            ->where('status_temuan', 'ditemukan')
            ->first();
    }

    // ==========================================================
    // Kepala keluarga & pembatasan tab
    // ==========================================================

    /** Hubungan keluarga bertulisan "kepala keluarga" (toleran: kepala_keluarga / Kepala Keluarga). */
    private function isHubunganKepala(?string $hubungan): bool
    {
        return str_contains(Str::lower(str_replace('_', ' ', (string) $hubungan)), 'kepala');
    }

    /**
     * NIK kepala keluarga untuk sebuah No KK.
     *  1. Anggota pengajuan masyarakat yang hubungannya "kepala keluarga";
     *  2. kalau tidak ada: nik_kepala_keluarga pada pengajuan (pemilik akun);
     *  3. kalau tidak ada pengajuan sama sekali (sasaran ditambah manual): anggota dengan id terkecil di KK.
     */
    private function kepalaNik(string $noKk): ?string
    {
        $pbi = PbiApbd::whereRaw('TRIM(no_kk) = ?', [$noKk])->latest('id')->first();

        if ($pbi) {
            $kepala = $pbi->anggotaKeluarga()->get()
                ->first(fn ($a) => $this->isHubunganKepala($a->hubungan_keluarga));

            $nik = trim((string) ($kepala->nik ?? $pbi->nik_kepala_keluarga));
            if ($nik !== '') {
                return $nik;
            }
        }

        $nik = GcSasaran::where('no_kk', $noKk)->orderBy('id')->value('nik');

        return $nik !== null ? trim((string) $nik) : null;
    }

    private function isKepala(GcSasaran $sasaran): bool
    {
        return trim((string) $this->kepalaNik($sasaran->no_kk)) === trim((string) $sasaran->nik);
    }

    /** Parameter yang berlaku: kepala keluarga = semua (A-G), anggota lain = A-D. */
    private function paramsUntuk(GcSasaran $sasaran)
    {
        $query = GcKriteriaParameter::untukPetugas()->with('opsis');

        if (! $this->isKepala($sasaran)) {
            $query->whereIn('kelompok', self::KELOMPOK_NON_KEPALA);
        }

        return $query->get();
    }

    // ==========================================================

    private function koordinatAngka(GcKriteriaParameter $p): bool
    {
        return Str::contains(Str::lower($p->nama), ['lintang', 'bujur']);
    }

    /** Parameter angka yang selalu 0 di Kota Bogor: kepemilikan perahu & ternak babi. */
    private function isNolParam(GcKriteriaParameter $p): bool
    {
        return $p->tipe_input === 'angka' && Str::contains(Str::lower((string) $p->nama), ['perahu', 'babi']);
    }

    /** Pertanyaan pemicu di tab Ketenagakerjaan ("Apakah responden bekerja ... seminggu yang lalu?"). */
    private function gateKerja($params): ?GcKriteriaParameter
    {
        return $params->first(fn ($p) => $p->kelompok === 'ketenagakerjaan'
            && Str::startsWith(Str::lower(trim($p->nama)), 'apakah responden bekerja'));
    }

    /**
     * Progres tiap tab dari jawaban yang terisi: kosong | draft | lengkap.
     * Field Ketenagakerjaan turunan tidak dihitung bila responden tidak bekerja.
     */
    private function statusTab(array $tabs, array $tersimpan, ?array $kerja): array
    {
        $bekerja = $kerja && (($tersimpan[$kerja['gate']] ?? null) === 'Ya');
        $hasil = [];

        foreach ($tabs as $i => $t) {
            $total = 0;
            $isi = 0;

            foreach ($t['params'] as $p) {
                if ($kerja && ! $bekerja && in_array($p->id, $kerja['dependen'], true)) {
                    continue;
                }
                // Pilihan ganda tanpa opsi tidak bisa diisi -> tidak dihitung.
                if ($p->tipe_input === 'pilihan_ganda' && ! $p->sumber && $p->opsis->isEmpty()) {
                    continue;
                }

                $total++;
                if (isset($tersimpan[$p->id])) {
                    $isi++;
                }
            }

            $hasil[$i] = [
                'isi' => $isi,
                'total' => $total,
                'status' => ($total === 0 || $isi >= $total) ? 'lengkap' : ($isi === 0 ? 'kosong' : 'draft'),
            ];
        }

        return $hasil;
    }

    /** Data master untuk petugas: SEMUA data (aktif maupun disembunyikan dari masyarakat). */
    private function masterKetenagakerjaan(): array
    {
        return [
            'lapangan' => GcLapanganUsaha::orderBy('urutan')->orderBy('id')->with('pekerjaans')->get(),
            'keterampilan' => GcKeterampilan::orderBy('urutan')->orderBy('id')->get(),
            'penyakit' => GcPenyakitKronis::orderBy('urutan')->orderBy('id')->get(),
        ];
    }

    /** Daftar jawaban yang sah untuk sebuah parameter (dari master atau dari opsi). */
    private function labelOpsi(GcKriteriaParameter $p, array $master): array
    {
        return match ($p->sumber) {
            'lapangan_usaha' => $master['lapangan']->pluck('nama')->all(),
            'pekerjaan' => $master['lapangan']->flatMap(fn ($l) => $l->pekerjaans->pluck('nama'))->unique()->values()->all(),
            'keterampilan' => $master['keterampilan']->pluck('nama')->all(),
            'penyakit_kronis' => $master['penyakit']->pluck('nama')->all(),
            default => $p->opsis->pluck('label')->all(),
        };
    }

    /** Pengajuan PBI APBD terbaru milik No KK sasaran ini (sumber jawaban warga). */
    private function pbiSasaran(GcSasaran $sasaran): ?PbiApbd
    {
        return PbiApbd::whereRaw('TRIM(no_kk) = ?', [$sasaran->no_kk])->latest('id')->first();
    }

    /**
     * Isian awal form petugas, berurutan (yang belakangan menimpa yang depan):
     *  1. data identitas & alamat dari daftar sasaran;
     *  2. jawaban warga di halaman masyarakat (pbi_apbd_gc_jawabans):
     *     - kepala keluarga -> semua jawaban (foto dilewati);
     *     - anggota lain    -> hanya kelompok Keluarga & Aset (data milik satu KK);
     *       (untuk anggota non-kepala, kelompok ini tidak ada di form sehingga otomatis terlewati);
     *     lalu untuk akun lama (form 18 parameter) yang masih kosong diisi dari jawaban PBI
     *     (pbi_apbd_jawabans) dengan padanan yang sama;
     *  3. isian Keluarga/Aset dari anggota satu KK yang sudah disubmit petugas.
     */
    private function prefill(GcSasaran $sasaran, $params): array
    {
        $nilai = [];

        $kecamatan = filled($sasaran->kecamatan) ? strtoupper(trim((string) $sasaran->kecamatan)) : null;
        $kelurahan = filled($sasaran->kelurahan) ? strtoupper(trim((string) $sasaran->kelurahan)) : null;

        $identitas = [
            'nama' => $sasaran->nama,
            'no kk' => $sasaran->no_kk,
            'alamat' => $sasaran->alamat,
            'rt' => $sasaran->rt,
            'rw' => $sasaran->rw,
            'kecamatan' => $kecamatan,
            'kelurahan' => $kelurahan,
            'desa/kelurahan' => $kelurahan,
            'tanggal lahir' => optional($sasaran->tanggal_lahir)->format('Y-m-d'),
        ];
        foreach ($params as $p) {
            $nama = Str::lower(trim($p->nama));
            if (array_key_exists($nama, $identitas) && $identitas[$nama] !== null && $identitas[$nama] !== '') {
                $nilai[$p->id] = $identitas[$nama];
            }
        }

        $kelompokKeluarga = ['keluarga', 'aset_bergerak', 'aset_tidak_bergerak'];

        // 2. Jawaban warga
        $pbi = $this->pbiSasaran($sasaran);
        if ($pbi) {
            $kepala = $this->isKepala($sasaran);
            $idBoleh = $params->where('tipe_input', '!=', 'foto')->pluck('id')->all();
            $idKeluarga = $params->whereIn('kelompok', $kelompokKeluarga)->pluck('id')->all();

            $jawabanWarga = GcPbiJawaban::where('pbi_apbd_id', $pbi->id)
                ->whereNotNull('parameter_id')
                ->whereNotNull('jawaban')
                ->where('jawaban', '!=', '')
                ->get();

            foreach ($jawabanWarga as $j) {
                if (! in_array($j->parameter_id, $idBoleh, true)) {
                    continue;
                }
                if (! $kepala && ! in_array($j->parameter_id, $idKeluarga, true)) {
                    continue;
                }
                $nilai[$j->parameter_id] = $j->jawaban;
            }

            // Akun lama (form 18 parameter): isi yang masih kosong dari jawaban PBI dengan padanan sama.
            $kosong = $params->filter(fn ($p) => $p->tipe_input !== 'foto'
                && blank($nilai[$p->id] ?? null)
                && ($kepala || in_array($p->id, $idKeluarga, true)));
            $nilai = PbiGcForm::isiDariJawabanPbi($pbi, $kosong, $nilai);
        }

        // 3. Anggota KK lain yang sudah disubmit petugas
        $sumber = GcVerifikasi::where('tahun', self::TAHUN)
            ->where('status_entry', 'submit')
            ->where('status_temuan', 'ditemukan')
            ->whereHas('sasaran', fn ($s) => $s->where('no_kk', $sasaran->no_kk)->where('id', '!=', $sasaran->id))
            ->latest('submitted_at')
            ->first();

        if ($sumber) {
            $idKeluarga = $params->whereIn('kelompok', $kelompokKeluarga)->pluck('id')->all();
            foreach ($sumber->jawabans()->whereIn('parameter_id', $idKeluarga)->get() as $j) {
                if ($j->jawaban !== null) {
                    $nilai[$j->parameter_id] = $j->jawaban;
                }
            }
        }

        return $nilai;
    }
}