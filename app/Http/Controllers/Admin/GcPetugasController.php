<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\GcVerifikasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpWord\TemplateProcessor;

class GcPetugasController extends Controller
{
    private const TAHUN = 2026;

    /**
     * Dokumen petugas di Home. Kunci = kolom `kode` pada tabel dokumens
     * (upload di menu Dokumen oleh Super Admin dengan kode yang sama).
     */
    private const DOKUMEN = [
        'fakta-integritas' => 'Fakta Integritas',
        'form-isian' => 'Form Isian',
        'surat-pernyataan-petugas-gc' => 'Surat Pernyataan Petugas Ground Check',
    ];

    public function home(Request $request)
    {
        return view('admin.gc-petugas.home', [
            'user' => $request->user(),
            'daftarDokumen' => self::DOKUMEN,
            'dokumenAda' => Dokumen::whereIn('kode', array_keys(self::DOKUMEN))->pluck('id', 'kode'),
        ]);
    }

    public function profilUpdate(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nik' => ['required', 'digits:16', Rule::unique('users', 'nik')->ignore($user->id)],
            'name' => ['required', 'string', 'max:255'],
            'no_telp' => ['required', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'alamat' => ['required', 'string', 'max:1000'],
            'pekerjaan' => ['required', 'string', 'max:100'],
            'no_rekening' => ['required', 'regex:/^[0-9]{5,25}$/'],
        ], [
            'required' => ':attribute wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik.unique' => 'NIK sudah dipakai akun lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah dipakai akun lain.',
            'no_telp.regex' => 'No Telp tidak valid (contoh: 08123456789).',
            'no_rekening.regex' => 'Nomor rekening harus angka (5-25 digit).',
        ], [
            'nik' => 'NIK', 'name' => 'Nama Lengkap', 'no_telp' => 'No Telp', 'email' => 'Email',
            'alamat' => 'Alamat', 'pekerjaan' => 'Pekerjaan', 'no_rekening' => 'Nomor Rekening',
        ]);

        // forceFill: tidak bergantung pada $fillable di model User
        $user->forceFill($data + ['gc_profil_at' => now()])->save();

        return redirect()->route('gc.home')
            ->with('success', 'Data petugas tersimpan. Dokumen siap diunduh dan Ground Check bisa dimulai.');
    }

    /**
     * Unduh dokumen petugas (template .docx dari menu Dokumen, diisi otomatis).
     *
     * Placeholder data petugas:
     *   ${nama_petugas} ${alamat_petugas} ${no_hp_petugas} ${nik_petugas} ${email_petugas}
     *   ${pekerjaan_petugas} ${no_rekening_petugas} ${username_petugas}
     *   ${kelurahan} ${kecamatan} ${tanggal} ${tanggal_ttd} ${tanggal_input}
     * Alias lama (tetap jalan di dokumen yang TIDAK punya tabel KPM):
     *   ${nama} ${username} ${nik} ${no_telp} ${email} ${alamat} ${pekerjaan} ${no_rekening}
     *
     * Tabel KPM: kalau template punya baris berisi ${x}, baris itu digandakan satu per KPM
     * yang sudah disubmit petugas:
     *   ${x} ${no_kk} ${nik} ${nama} ${alamat} ${tgl_jadwal} ${st}
     */
    public function dokumen(Request $request, string $kode)
    {
        abort_unless(isset(self::DOKUMEN[$kode]), 404);

        $dok = Dokumen::where('kode', $kode)->first();
        abort_unless($dok, 404, 'Template dokumen belum diunggah oleh Super Admin.');

        $disk = Storage::disk('local');
        abort_unless($disk->exists($dok->file_path), 404, 'File template tidak ditemukan.');

        $path = $disk->path($dok->file_path);
        $ext = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
        $user = $request->user();

        if ($ext === 'docx' && class_exists(TemplateProcessor::class)) {
            try {
                $tp = new TemplateProcessor($path);
                $ada = $tp->getVariables();
                $punyaTabel = in_array('x', $ada, true);

                $verifs = $this->verifikasiPetugas($user->id);
                $esc = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES | ENT_XML1);
                $tgl = fn ($d) => Carbon::parse($d)->locale('id')->translatedFormat('d F Y');

                // Wilayah dari data sasaran yang dicek; kalau belum ada, pakai wilayah tugas di akun.
                $wilayah = fn ($kolom) => $verifs->map(fn ($v) => strtoupper(trim((string) $v->sasaran->{$kolom})))
                    ->filter()->unique()->values()->implode(', ') ?: strtoupper((string) ($user->{$kolom} ?? '-'));

                $tanggalPertama = $verifs->map(fn ($v) => $v->submitted_at ?? $v->created_at)->filter()->sort()->first();

                $nilai = [
                    'nama_petugas' => $user->name,
                    'alamat_petugas' => $user->alamat,
                    'no_hp_petugas' => $user->no_telp,
                    'nik_petugas' => $user->nik,
                    'email_petugas' => $user->email,
                    'pekerjaan_petugas' => $user->pekerjaan,
                    'no_rekening_petugas' => $user->no_rekening,
                    'username_petugas' => $user->username,
                    'kelurahan' => $wilayah('kelurahan'),
                    'kecamatan' => $wilayah('kecamatan'),
                    'tanggal' => $tgl(now()),
                    'tanggal_ttd' => $tgl(now()),
                    'tanggal_input' => $tgl($tanggalPertama ?? now()),
                ];

                // Alias lama hanya bila dokumen tidak punya tabel (di tabel, ${nama}/${nik}/${alamat} = data KPM)
                if (! $punyaTabel) {
                    $nilai += [
                        'nama' => $user->name, 'username' => $user->username, 'nik' => $user->nik,
                        'no_telp' => $user->no_telp, 'email' => $user->email, 'alamat' => $user->alamat,
                        'pekerjaan' => $user->pekerjaan, 'no_rekening' => $user->no_rekening,
                    ];
                }

                if ($punyaTabel) {
                    $jumlah = max($verifs->count(), 1);
                    $tp->cloneRow('x', $jumlah);

                    if ($verifs->isEmpty()) {
                        foreach (['x', 'no_kk', 'nik', 'nama', 'alamat', 'tgl_jadwal', 'st'] as $k) {
                            $tp->setValue("{$k}#1", '-');
                        }
                    }

                    foreach ($verifs->values() as $i => $v) {
                        $n = $i + 1;
                        $s = $v->sasaran;
                        $waktu = $v->submitted_at ?? $v->created_at;

                        $tp->setValue("x#{$n}", $n);
                        $tp->setValue("no_kk#{$n}", $esc($s->no_kk));
                        $tp->setValue("nik#{$n}", $esc($s->nik));
                        $tp->setValue("nama#{$n}", $esc($s->nama));
                        $tp->setValue("alamat#{$n}", $esc($s->alamat));
                        $tp->setValue("tgl_jadwal#{$n}", $waktu ? Carbon::parse($waktu)->format('d-m-Y') : '-');
                        $tp->setValue("st#{$n}", $esc(GcVerifikasi::STATUS_TEMUAN[$v->status_temuan] ?? $v->status_temuan));
                    }
                }

                foreach ($nilai as $k => $v) {
                    $tp->setValue($k, $esc($v));
                }

                $tmp = tempnam(sys_get_temp_dir(), 'gcdok_');
                $tp->saveAs($tmp);

                return response()->download($tmp, Str::slug(self::DOKUMEN[$kode]) . '-' . Str::slug($user->name) . '.docx')
                    ->deleteFileAfterSend(true);
            } catch (\Throwable $e) {
                report($e); // gagal mengisi template -> jatuh ke unduhan file asli di bawah
            }
        }

        return response()->download($path, $dok->file_name);
    }

    /** KPM yang sudah disubmit petugas ini (tahun Ground Check berjalan), urut waktu submit. */
    private function verifikasiPetugas(int $userId)
    {
        return GcVerifikasi::with('sasaran')
            ->where('user_id', $userId)
            ->where('tahun', self::TAHUN)
            ->where('status_entry', 'submit')
            ->orderBy('submitted_at')
            ->orderBy('id')
            ->get()
            ->filter(fn ($v) => $v->sasaran)
            ->values();
    }
}