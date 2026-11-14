<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\GcKeterampilan;
use App\Models\GcKriteriaParameter;
use App\Models\GcLapanganUsaha;
use App\Models\GcPenyakitKronis;
use App\Models\GcSasaran;
use App\Models\PbiApbd;
use App\Models\PbiApbdAnggotaJawaban;
use App\Support\PbiGcForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * GANTI app/Http/Controllers/Masyarakat/PbiAnggotaController.php dengan file ini (versi 3B).
 * Form data per anggota keluarga (tab A-D). Kepala keluarga memakai form utama (A sampai G).
 * Perubahan 3B: tidak lagi butuh centang; anggota MASUK Daftar Sasaran Ground Check begitu datanya disimpan,
 * terlepas dari apakah ia didaftarkan sebagai peserta PBI APBD.
 */
class PbiAnggotaController extends Controller
{
    /** GET /akun-saya/pbi-apbd/{pbiApbd}/anggota/{anggota} */
    public function edit(Request $request, PbiApbd $pbiApbd, int $anggota)
    {
        [$a, $bisaEdit, $terkunci] = $this->siapkan($request, $pbiApbd, $anggota);

        $parameters = PbiGcForm::untukAnggota();
        $master = $this->master();
        $opsiLabels = $parameters->mapWithKeys(fn ($p) => [$p->id => $this->labelOpsi($p, $master)])->all();

        $nilai = PbiApbdAnggotaJawaban::where('anggota_id', $a->id)
            ->whereNotNull('jawaban')->pluck('jawaban', 'parameter_id')->all();

        $isiAwal = $this->isiAwalPekerjaan($a, $parameters, $master);
        foreach ($isiAwal as $id => $v) {
            if (blank($nilai[$id] ?? null)) {
                $nilai[$id] = $v;
            }
        }

        $gate = PbiGcForm::gate($parameters);
        $kerja = $gate ? ['gate' => $gate->id, 'dependen' => PbiGcForm::dependenIds($parameters)] : null;
        $perKelompok = $parameters->groupBy('kelompok');

        return view('masyarakat.pbi-apbd.anggota', compact(
            'pbiApbd', 'a', 'bisaEdit', 'terkunci', 'parameters', 'perKelompok', 'opsiLabels', 'nilai', 'master', 'kerja', 'isiAwal'
        ));
    }

    /** POST /akun-saya/pbi-apbd/{pbiApbd}/anggota/{anggota} */
    public function update(Request $request, PbiApbd $pbiApbd, int $anggota)
    {
        [$a, $bisaEdit] = $this->siapkan($request, $pbiApbd, $anggota);

        if (! $bisaEdit) {
            return redirect()->route('masyarakat.pbi-apbd.keluarga', $pbiApbd)
                ->with('error', 'Data anggota ini tidak dapat diubah lagi (sudah disetujui atau sudah diproses petugas).');
        }

        $parameters = PbiGcForm::untukAnggota();
        $master = $this->master();

        $gate = PbiGcForm::gate($parameters);
        $bekerja = $gate && $request->input("jawaban.{$gate->id}") === 'Ya';
        $dependen = PbiGcForm::dependenIds($parameters);
        $lapParam = $parameters->firstWhere('sumber', 'lapangan_usaha');
        $jenjangParam = $parameters->first(fn ($x) => $this->isJenjang($x));

        $rules = [];
        $attributes = [];
        $messages = [
            'required' => 'Pertanyaan ":attribute" wajib dijawab.',
            'in' => 'Pilihan pada :attribute tidak valid.',
            'min.array' => 'Pilih minimal satu jawaban untuk :attribute.',
            'integer' => ':attribute harus berupa angka bulat.',
            'between.numeric' => ':attribute harus antara 0 sampai 50.',
            'date' => ':attribute bukan tanggal yang valid.',
        ];

        foreach ($parameters as $p) {
            $key = "jawaban.{$p->id}";
            $attributes[$key] = $p->nama;
            $attributes["{$key}.*"] = $p->nama;

            if (in_array($p->id, $dependen, true) && ! $bekerja) {
                $rules[$key] = ['nullable'];
                continue;
            }

            $req = PbiGcForm::opsional($p) ? 'nullable' : 'required';
            $labels = $this->labelOpsi($p, $master);

            switch ($p->tipe_input) {
                case 'pilihan':
                    if ($labels && $this->isKelas($p)) {
                        $rules[$key] = [$req, 'string', function ($attr, $value, $fail) use ($request, $jenjangParam, $labels, $p) {
                            if ($value === null || $value === '') {
                                return;
                            }
                            $jenjang = $jenjangParam ? (string) $request->input("jawaban.{$jenjangParam->id}") : '';
                            $maks = MasyarakatController::batasKelas($jenjang)['maks'];
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
                    $rules[$key] = $labels ? [$req, 'string', Rule::in($labels)] : [$req, 'string', 'max:500'];
                    break;

                case 'pilihan_ganda':
                    $rules[$key] = ($req === 'required' && $labels) ? ['required', 'array', 'min:1'] : ['nullable', 'array'];
                    $rules["{$key}.*"] = $labels ? ['string', Rule::in($labels)] : ['string', 'max:255'];
                    break;

                case 'angka':
                    $rules[$key] = [$req, 'integer', 'between:0,50'];
                    break;

                case 'tanggal':
                    $rules[$key] = [$req, 'date'];
                    break;

                default:
                    $rules[$key] = [$req, 'string', 'max:500'];
            }

            if ($p->sumber === 'keterampilan') {
                $eksklusif = $master['keterampilan']->where('eksklusif', true)->pluck('nama')->all();
                $rules[$key][] = function ($attr, $value, $fail) use ($eksklusif) {
                    if (is_array($value) && count($value) > 1 && ($bentrok = array_intersect($value, $eksklusif))) {
                        $fail('Pilihan "' . implode('", "', $bentrok) . '" tidak boleh digabung dengan pilihan lain.');
                    }
                };
            }

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

        $request->validate($rules, $messages, $attributes);

        DB::transaction(function () use ($request, $parameters, $a, $pbiApbd, $dependen, $bekerja) {
            foreach ($parameters as $p) {
                $kosongkan = in_array($p->id, $dependen, true) && ! $bekerja;
                $input = $kosongkan ? null : $request->input("jawaban.{$p->id}");

                if ($p->tipe_input === 'pilihan_ganda') {
                    $arr = array_values(array_filter((array) $input, fn ($x) => $x !== null && $x !== ''));
                    $jawaban = $arr ? json_encode($arr, JSON_UNESCAPED_UNICODE) : null;
                } else {
                    $jawaban = ($input === null || $input === '') ? null : (string) $input;
                }

                PbiApbdAnggotaJawaban::updateOrCreate(
                    ['anggota_id' => $a->id, 'parameter_id' => $p->id],
                    [
                        'pbi_apbd_id' => $pbiApbd->id,
                        'kelompok' => $p->kelompok,
                        'urutan' => $p->urutan,
                        'nama_parameter' => $p->nama,
                        'tipe_input' => $p->tipe_input,
                        'jawaban' => $jawaban,
                    ]
                );
            }
        });

        // Data sudah terisi -> anggota masuk Daftar Sasaran Ground Check (tidak bergantung pada status peserta PBI)
        $adaIsi = collect($request->input('jawaban', []))->flatten()
            ->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();

        if ($adaIsi) {
            $a->update([
                'didaftarkan_gc' => true,
                'didaftarkan_gc_at' => $a->didaftarkan_gc_at ?? now(),
            ]);
            $pbiApbd->sinkronGroundCheck();
        }

        return redirect()->route('masyarakat.pbi-apbd.keluarga', $pbiApbd)
            ->with('success', 'Data ' . $a->nama . ' berhasil disimpan' . ($adaIsi ? ' dan masuk daftar Ground Check.' : '.'));
    }

    // ----------------------------------------------------------

    /** Validasi pemilik + syarat anggota. Mengembalikan [anggota, bisaEdit, terkunci]. */
    private function siapkan(Request $request, PbiApbd $pbi, int $anggotaId): array
    {
        $milik = $request->user()->pengajuanPbiApbd()->whereKey($pbi->getKey())->exists();
        abort_unless($milik, 403, 'Ini bukan pengajuan Anda.');

        $a = $pbi->anggotaKeluarga()->findOrFail($anggotaId);

        // Kepala keluarga memakai form utama
        abort_if($this->isKepala($pbi, $a), 404);

        $terkunci = GcSasaran::where('nik', $this->digits($a->nik))
            ->where('no_kk', $this->digits($pbi->no_kk))
            ->whereHas('verifikasis')->exists();

        return [$a, $pbi->masyarakatBisaEdit() && ! $terkunci, $terkunci];
    }

    private function digits($v): string
    {
        return preg_replace('/\D/', '', (string) $v);
    }

    private function isKepala(PbiApbd $pbi, $a): bool
    {
        return $this->digits($a->nik) === $this->digits($pbi->nik_kepala_keluarga)
            || PbiApbd::isHubunganKepala($a->hubungan_keluarga);
    }

    private function master(): array
    {
        return [
            'lapangan' => GcLapanganUsaha::aktif()->with('pekerjaansAktif')->get(),
            'keterampilan' => GcKeterampilan::aktif()->get(),
            'penyakit' => GcPenyakitKronis::aktif()->get(),
        ];
    }

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

    private function isJenjang($p): bool
    {
        $n = Str::lower((string) $p->nama);

        return Str::lower((string) $p->kelompok) === 'pendidikan' && str_contains($n, 'jenjang') && str_contains($n, 'pendidikan');
    }

    private function isKelas($p): bool
    {
        return Str::lower((string) $p->kelompok) === 'pendidikan' && str_contains(Str::lower((string) $p->nama), 'kelas tertinggi');
    }

    /** Nilai awal Lapangan usaha & Pekerjaan dari kolom pekerjaan anggota. */
    private function isiAwalPekerjaan($a, $parameters, array $master): array
    {
        $pLap = $parameters->firstWhere('sumber', 'lapangan_usaha');
        $pPek = $parameters->firstWhere('sumber', 'pekerjaan');
        $t = Str::lower(trim((string) $a->pekerjaan));

        if (! $pLap || $t === '') {
            return [];
        }

        foreach ($master['lapangan'] as $l) {
            foreach ($l->pekerjaansAktif as $pk) {
                if (Str::lower(trim($pk->nama)) === $t) {
                    $hasil = [$pLap->id => $l->nama];
                    if ($pPek) {
                        $hasil[$pPek->id] = $pk->nama;
                    }

                    return $hasil;
                }
            }
        }

        return [];
    }
}