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
 * ALUR BARU: masyarakat datang ke Dinas Sosial -> OPERATOR DINSOS menginput
 * ajuan lewat halaman ini -> berkas langsung dipegang Operator (status
 * "diajukan") untuk dicek/dilengkapi, lalu diteruskan ke Kepala Bidang
 * -> Kepala Dinas (TTD digital) -> kembali ke Operator untuk memberitahu pemohon.
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

        // Berkas yang baru diinput dipegang Operator Dinsos (tahap 1 alur baru).
        $operatorRole = Role::firstOrCreate(
            ['slug' => 'operator_dinsos'],
            ['name' => 'Operator Dinsos', 'slug' => 'operator_dinsos']
        );

        $user = Auth::user();

        $permohonan = DB::transaction(function () use ($validated, $request, $operatorRole, $user) {
            $permohonan = KartuKksPermohonan::create([
                // user_id / created_by = operator yang menginput ajuan atas nama pemohon
                'user_id' => $user->id,
                'nik' => $validated['nik'],
                'nama_pemohon' => $validated['nama'],
                'alamat' => $validated['alamat'],
                'masalah_kartu' => $validated['masalah_kartu'],
                'nomor_kehilangan_polisi' => $validated['nomor_kehilangan_polisi'] ?? null,
                'status' => KartuKksPermohonan::STATUS_DIAJUKAN,
                'current_role_id' => $operatorRole->id,
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
                'taskname' => 'Ajuan Diinput Operator',
                'rolename' => 'Operator Dinsos',
                'catatan' => 'Data awal diinput operator dari pemohon yang datang langsung ke Dinas Sosial.',
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
            ->with('success', 'Ajuan Kartu KKS berhasil diinput, nomor permohonan ' . $permohonan->no_permohonan . '. Silakan cek kelengkapan data lalu teruskan ke Kepala Bidang.');
    }

    /** GET /layanan/kartu-kks/riwayat — ajuan yang diinput oleh user (operator) yang login */
    public function riwayat()
    {
        $data = KartuKksPermohonan::where('user_id', Auth::id())
            ->latest('tanggal_insert')
            ->get();

        return view('layanan.kartu-kks.riwayat', compact('data'));
    }
}