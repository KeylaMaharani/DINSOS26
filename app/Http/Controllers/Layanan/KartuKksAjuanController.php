<?php

namespace App\Http\Controllers\Layanan;

use App\Http\Controllers\Controller;
use App\Models\KartuKksPermohonan;
use App\Models\Role;
use App\Support\GroundCheckSinkron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Input ajuan Kartu KKS.
 *
 * Dua pintu masuk:
 *  1. KELURAHAN menginput -> status "ttd_lurah" (dipegang Lurah: TTD digital +
 *     surat pengantar) -> Operator Dinsos -> Kabid -> Kadis (TTD) -> Operator -> selesai.
 *  2. OPERATOR DINSOS menginput (pemohon datang langsung) -> status "diajukan"
 *     (dipegang Operator) -> Kabid -> Kadis (TTD) -> Operator -> selesai.
 * (Lihat KartuKksController::transitions().)
 */
class KartuKksAjuanController extends Controller
{
    /** GET /layanan/kartu-kks/ajukan */
    public function create()
    {
        return view('layanan.kartu-kks.ajukan');
    }

    /** POST /layanan/kartu-kks/ajukan */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|max:20',
            'nama' => 'required|string|max:150',
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'agama' => ['required', Rule::in(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghuchu'])],
            'status_perkawinan' => ['required', Rule::in(['Kawin', 'Belum Kawin', 'Cerai Hidup', 'Cerai Mati'])],
            'no_pkh' => 'required|string|max:50',
            'no_kk' => 'required|string|max:20',
            'no_kartu_kks' => 'required|string|max:30',
            'no_rekening' => 'required|string|max:30',
            'pekerjaan' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:100',
            'masalah_kartu' => ['required', Rule::in(['terblokir', 'rusak', 'hilang', 'masa berlaku habis', 'nama tidak sesuai', 'keterangan lainnya'])],
            'nomor_kehilangan_polisi' => 'required_if:masalah_kartu,hilang|nullable|string|max:100',

            'scan_ktp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'scan_kartu_keluarga' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'surat_kehilangan_polisi' => 'required_if:masalah_kartu,hilang|nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'scan_kartu_kks' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'screenshot_dtsen' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'nomor_kehilangan_polisi.required_if' => 'Nomor laporan kehilangan wajib diisi jika masalah kartu adalah "hilang".',
            'surat_kehilangan_polisi.required_if' => 'Surat kehilangan dari kepolisian wajib diunggah jika masalah kartu adalah "hilang".',
        ]);

        $user = Auth::user();
        $asKelurahan = ($user->role->slug ?? null) === 'kelurahan';

        if ($asKelurahan && filled($user->kelurahan)) {
            $validated['kelurahan'] = $user->kelurahan;
        }

        // Tahap awal: Kelurahan -> Lurah (TTD + surat pengantar); Operator -> Operator.
        if ($asKelurahan) {
            $tahapAwal = KartuKksPermohonan::STATUS_TTD_LURAH;
            $roleAwal = Role::firstOrCreate(
                ['slug' => 'lurah'],
                ['name' => 'Lurah', 'slug' => 'lurah']
            );
        } else {
            $tahapAwal = KartuKksPermohonan::STATUS_DIAJUKAN;
            $roleAwal = Role::firstOrCreate(
                ['slug' => 'operator_dinsos'],
                ['name' => 'Operator Dinsos', 'slug' => 'operator_dinsos']
            );
        }

        $permohonan = DB::transaction(function () use ($validated, $request, $roleAwal, $tahapAwal, $user, $asKelurahan) {
            $permohonan = KartuKksPermohonan::create([
                // user_id / created_by = petugas yang menginput ajuan atas nama pemohon
                'user_id' => $user->id,
                'nik' => $validated['nik'],
                'nama_pemohon' => $validated['nama'],
                'alamat' => $validated['alamat'],
                'masalah_kartu' => $validated['masalah_kartu'],
                'nomor_kehilangan_polisi' => $validated['nomor_kehilangan_polisi'] ?? null,
                'status' => $tahapAwal,
                'current_role_id' => $roleAwal->id,
                'kelurahan' => $validated['kelurahan'],
                'tanggal_insert' => now(),
                'created_by' => $user->id,
            ]);

            // Form detail di halaman Ajuan (KartuKksController) memakai opsi
            // "Laki-laki" / "Perempuan", jadi simpan dalam bentuk itu supaya
            // dropdown-nya langsung terpilih saat Operator membuka detail.
            $detailData = collect($validated)->only([
                'nik',
                'nama',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'agama',
                'status_perkawinan',
                'no_pkh',
                'no_kk',
                'no_kartu_kks',
                'no_rekening',
                'pekerjaan',
                'alamat',
                'masalah_kartu',
                'nomor_kehilangan_polisi',
            ])->toArray();
            $detailData['jenis_kelamin'] = $detailData['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';

            $permohonan->detail()->create($detailData);

            $lampiranData = [];
            foreach (['scan_ktp', 'scan_kartu_keluarga', 'surat_kehilangan_polisi', 'scan_kartu_kks', 'screenshot_dtsen'] as $field) {
                if ($request->hasFile($field)) {
                    $lampiranData[$field] = $request->file($field)->store('kartu-kks/lampiran', 'public');
                }
            }
            $permohonan->lampiran()->create($lampiranData);

            $permohonan->logs()->create([
                'tanggal_proses' => now(),
                'user_id' => $user->id,
                'username' => $user->username ?? $user->name,
                'taskname' => $asKelurahan ? 'Ajuan Diinput Kelurahan' : 'Ajuan Diinput Operator',
                'rolename' => $asKelurahan ? 'Kelurahan' : 'Operator Dinsos',
                'catatan' => $asKelurahan
                    ? 'Data awal diinput petugas kelurahan, diteruskan ke Lurah untuk TTD digital dan pembuatan surat pengantar.'
                    : 'Data awal diinput operator dari pemohon yang datang langsung ke Dinas Sosial.',
            ]);

            return $permohonan;
        });

        GroundCheckSinkron::individu([
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'no_kk' => $validated['no_kk'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'alamat' => $validated['alamat'],
            'kelurahan' => $validated['kelurahan'],
        ]);

        return redirect()->route('kartu-kks.ajuan')
            ->with('success', $asKelurahan
                ? 'Ajuan Kartu KKS berhasil dikirim dengan nomor permohonan ' . $permohonan->no_permohonan
                    . '. Ajuan diteruskan ke Lurah untuk ditandatangani secara digital dan dibuatkan surat pengantar,'
                : 'Ajuan Kartu KKS berhasil diinput dengan nomor permohonan ' . $permohonan->no_permohonan
                    . '. Silakan cek kelengkapan data, lalu teruskan ke Kepala Bidang.');
    }

    /** GET /layanan/kartu-kks/riwayat — ajuan yang diinput oleh user yang login */
    public function riwayat()
    {
        $data = KartuKksPermohonan::where('user_id', Auth::id())
            ->latest('tanggal_insert')
            ->get();

        return view('layanan.kartu-kks.riwayat', compact('data'));
    }
}