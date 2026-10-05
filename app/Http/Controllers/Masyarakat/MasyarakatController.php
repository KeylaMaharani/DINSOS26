<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\PbiApbn;
use App\Models\PbiApbnJawaban;
use App\Models\PbiApbnLog;
use App\Models\PbiKriteriaParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasyarakatController extends Controller
{
    /** GET /akun-saya — ringkasan pengajuan milik user yang login */
    public function dashboard(Request $request)
    {
        $pbiApbn = $request->user()->pengajuanPbiApbn()
            ->with([
                'logs' => fn ($q) => $q->orderBy('created_at'),
                'diagnosaLogs.user',
            ])
            ->latest()
            ->first();

        $totalParameter = PbiKriteriaParameter::aktif()->count();
        $terjawab = $pbiApbn ? $pbiApbn->jawabans()->count() : 0;

        // "Lengkap" = lokasi terambil + semua parameter terjawab. Lampiran kini diurus Kelurahan.
        $isLengkap = $pbiApbn
            && $pbiApbn->latitude
            && $pbiApbn->longitude
            && $totalParameter > 0
            && $terjawab >= $totalParameter;

        $bisaEdit = $pbiApbn ? $pbiApbn->masyarakatBisaEdit() : false;

        return view('masyarakat.dashboard', compact('pbiApbn', 'isLengkap', 'bisaEdit', 'totalParameter', 'terjawab'));
    }

    /** GET /akun-saya/pbi-apbn/{pbiApbn}/lengkapi */
    public function lengkapiData(Request $request, PbiApbn $pbiApbn)
    {
        $this->authorizePemilik($request, $pbiApbn);

        if (! $pbiApbn->masyarakatBisaEdit()) {
            return redirect()->route('masyarakat.dashboard')
                ->with('error', 'Data sudah diteruskan ke tahap berikutnya dan tidak bisa diubah lagi.');
        }

        $parameters = PbiKriteriaParameter::aktif()->with('opsis')->get();
        // parameter_id => opsi_id (HANYA id, tidak ada indeks/bobot yang dikirim ke halaman masyarakat)
        $jawabanTersimpan = $pbiApbn->jawabans()->pluck('opsi_id', 'parameter_id')->all();

        return view('masyarakat.pbi-apbn.lengkapi', compact('pbiApbn', 'parameters', 'jawabanTersimpan'));
    }

    /** POST /akun-saya/pbi-apbn/{pbiApbn}/lengkapi */
    public function lengkapiDataStore(Request $request, PbiApbn $pbiApbn)
    {
        $this->authorizePemilik($request, $pbiApbn);

        if (! $pbiApbn->masyarakatBisaEdit()) {
            return redirect()->route('masyarakat.dashboard')
                ->with('error', 'Data sudah diteruskan ke tahap berikutnya dan tidak bisa diubah lagi.');
        }

        $parameters = PbiKriteriaParameter::aktif()->with('opsis')->get();

        $rules = [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'jawaban' => ['required', 'array'],
        ];
        $messages = [
            'latitude.required' => 'Lokasi belum berhasil diambil. Izinkan akses lokasi lalu coba lagi.',
            'longitude.required' => 'Lokasi belum berhasil diambil. Izinkan akses lokasi lalu coba lagi.',
            'jawaban.required' => 'Semua pertanyaan wajib dijawab.',
        ];

        foreach ($parameters as $p) {
            $rules["jawaban.{$p->id}"] = ['required', Rule::in($p->opsis->pluck('id')->all())];
            $messages["jawaban.{$p->id}.required"] = "Pertanyaan \"{$p->nama}\" wajib dijawab.";
            $messages["jawaban.{$p->id}.in"] = "Jawaban untuk \"{$p->nama}\" tidak valid.";
        }

        $validated = $request->validate($rules, $messages);

        DB::transaction(function () use ($pbiApbn, $parameters, $validated, $request) {
            foreach ($parameters as $p) {
                $opsi = $p->opsis->firstWhere('id', (int) $validated['jawaban'][$p->id]);

                $skor = round((float) $p->indeks * (float) $p->bobot * (float) $opsi->indeks_terintegrasi, 6);

                PbiApbnJawaban::updateOrCreate(
                    ['pbi_apbn_id' => $pbiApbn->id, 'parameter_id' => $p->id],
                    [
                        'opsi_id' => $opsi->id,
                        'urutan' => $p->urutan,
                        'parameter_nama' => $p->nama,
                        'jawaban_label' => $opsi->label,
                        'indeks' => $p->indeks,
                        'bobot' => $p->bobot,
                        'indeks_terintegrasi' => $opsi->indeks_terintegrasi,
                        'skor' => $skor,
                    ]
                );
            }

            // Buang jawaban untuk parameter yang kini nonaktif (baris snapshot yatim tidak disentuh)
            $pbiApbn->jawabans()->whereNotNull('parameter_id')
                ->whereNotIn('parameter_id', $parameters->pluck('id'))->delete();

            $pbiApbn->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'data_diisi_at' => now(),
                'dikembalikan_ke_masyarakat' => false,
            ]);

            $pbiApbn->hitungUlangSkor();

            PbiApbnLog::create([
                'pbi_apbn_id' => $pbiApbn->id,
                'user_id' => $request->user()->id,
                'username' => $request->user()->name,
                'role_name' => 'Masyarakat',
                'task_name' => 'Masyarakat mengisi data & parameter',
                'catatan' => null,
            ]);
        });

        return redirect()->route('masyarakat.dashboard')
            ->with('success', 'Data berhasil disimpan dan akan diverifikasi oleh petugas kelurahan.');
    }

    private function authorizePemilik(Request $request, PbiApbn $pbiApbn): void
    {
        abort_unless($pbiApbn->user_id === $request->user()->id, 403, 'Ini bukan pengajuan Anda.');
    }
}
