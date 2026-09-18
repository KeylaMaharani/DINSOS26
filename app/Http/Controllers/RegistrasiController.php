<?php

namespace App\Http\Controllers;

use App\Models\PbiApbn;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegistrasiController extends Controller
{
    /** GET /registrasi/step-1 */
    public function step1(Request $request)
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $request->session()->put('reg_captcha_a', $a);
        $request->session()->put('reg_captcha_b', $b);

        return view('registrasi.step1', ['captchaA' => $a, 'captchaB' => $b]);
    }

    /** POST /registrasi/step-1 */
    public function step1Store(Request $request)
    {
        $expected = (int) $request->session()->get('reg_captcha_a', 0)
            + (int) $request->session()->get('reg_captcha_b', 0);

        $validated = $request->validate([
            'no_kk' => ['required', 'digits:16'],
            'captcha' => ['required', 'numeric'],
        ]);

        if ((int) $validated['captcha'] !== $expected) {
            throw ValidationException::withMessages([
                'captcha' => 'Jawaban verifikasi salah, silakan coba lagi.',
            ]);
        }

        $request->session()->forget(['reg_captcha_a', 'reg_captcha_b']);
        $request->session()->put('registrasi.no_kk', $validated['no_kk']);

        return redirect()->route('registrasi.step2');
    }

    /** GET /registrasi/step-2 */
    public function step2(Request $request)
    {
        $noKk = $request->session()->get('registrasi.no_kk');

        if (! $noKk) {
            return redirect()->route('registrasi.step1')
                ->with('error', 'Silakan masukan No KK terlebih dahulu.');
        }

        return view('registrasi.step2', ['noKk' => $noKk]);
    }

    /** POST /registrasi/step-2 */
    public function step2Store(Request $request)
    {
        $noKk = $request->session()->get('registrasi.no_kk');

        if (! $noKk) {
            return redirect()->route('registrasi.step1')
                ->with('error', 'Sesi pendaftaran sudah habis, silakan mulai ulang dari Step 1.');
        }

        $validated = $request->validate([
            'status_hubungan' => ['required', Rule::in(['kepala_keluarga', 'suami', 'istri', 'anak', 'lainnya'])],
            'nik' => ['required', 'digits:16', Rule::unique('pbi_apbns', 'nik_kepala_keluarga')],
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
            'alamat' => ['required', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'provinsi' => ['required', 'string'],
            'kab_kota' => ['required', 'string'],
            'kecamatan' => ['required', 'string'],
            'desa_kelurahan' => ['required', 'string'],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'telp' => ['required', 'string', 'max:20'],
            'agama' => ['required', 'string'],
            'pekerjaan' => ['required', 'string'],
            'tempat_lahir' => ['required', 'string'],
            'tgl_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'anggota' => ['nullable', 'array'],
            'anggota.*.nama' => ['required_with:anggota', 'string', 'max:150'],
            'anggota.*.nik' => ['required_with:anggota', 'digits:16'],
            'anggota.*.status' => ['required_with:anggota', 'string'],
        ], [
            'password.regex' => 'Password minimal 8 karakter, memuat 1 huruf kapital, 1 huruf kecil, dan 1 angka.',
            'nik.unique' => 'NIK ini sudah pernah mengajukan PBI APBN.',
            'email.unique' => 'Email ini sudah terdaftar, silakan login.',
        ]);

        $noRegistrasi = 'PBI-APBN-' . now()->year . '-'
            . Str::padLeft((string) (PbiApbn::whereYear('created_at', now()->year)->count() + 1), 4, '0');

        $masyarakatRole = Role::firstOrCreate(
            ['slug' => 'masyarakat'],
            ['name' => 'Masyarakat', 'slug' => 'masyarakat']
        );

        [$user, $pbiApbn] = DB::transaction(function () use ($validated, $noKk, $noRegistrasi, $masyarakatRole) {
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'username' => $validated['nik'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $masyarakatRole->id,
            ]);

            $pbiApbn = PbiApbn::create([
                'user_id' => $user->id,
                'no_registrasi' => $noRegistrasi,
                'no_kk' => $noKk,
                'nama_kepala_keluarga' => $validated['nama_lengkap'],
                'nik_kepala_keluarga' => $validated['nik'],
                'alamat' => $validated['alamat'],
                'rt' => $validated['rt'] ?? null,
                'rw' => $validated['rw'] ?? null,
                'provinsi' => $validated['provinsi'],
                'kabupaten_kota' => $validated['kab_kota'],
                'kecamatan' => $validated['kecamatan'],
                'desa_kelurahan' => $validated['desa_kelurahan'],
                'no_telp' => $validated['telp'],
                'email' => $validated['email'],
                // Tahap awal alur = 'kelurahan' (lihat PbiApbn::STAGES).
                // Kolom `status` di migration default-nya 'operator_dinsos',
                // itu keliru/basi -- override eksplisit di sini.
                'status' => 'kelurahan',
            ]);

            $pbiApbn->anggotaKeluarga()->create([
                'nik' => $validated['nik'],
                'nama' => $validated['nama_lengkap'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tanggal_lahir' => $validated['tgl_lahir'],
                'pekerjaan' => $validated['pekerjaan'],
                'agama' => $validated['agama'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'hubungan_keluarga' => $validated['status_hubungan'],
                'keanggotaan_bpjs' => 'Belum Terdaftar',
            ]);

            foreach ($validated['anggota'] ?? [] as $anggota) {
                $pbiApbn->anggotaKeluarga()->create([
                    'nik' => $anggota['nik'],
                    'nama' => $anggota['nama'],
                    'hubungan_keluarga' => $anggota['status'],
                ]);
            }

            return [$user, $pbiApbn];
        });

        $request->session()->forget('registrasi.no_kk');

        return redirect()->route('login')->with('success', [
            'no_registrasi' => $pbiApbn->no_registrasi,
            'nama' => $pbiApbn->nama_kepala_keluarga,
        ]);
    }
}
