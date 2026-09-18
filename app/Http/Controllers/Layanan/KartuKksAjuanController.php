<?php

namespace App\Http\Controllers\Layanan;

use App\Http\Controllers\Controller;
use App\Models\KartuKksPermohonan;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

        $kelurahanRole = Role::firstOrCreate(
            ['slug' => 'kelurahan'],
            ['name' => 'Kelurahan', 'slug' => 'kelurahan']
        );

        $permohonan = DB::transaction(function () use ($validated, $request, $kelurahanRole) {
            $permohonan = KartuKksPermohonan::create([
                'user_id' => Auth::id(),
                'nik' => $validated['nik'],
                'nama_pemohon' => $validated['nama'],
                'alamat' => $validated['alamat'],
                'masalah_kartu' => $validated['masalah_kartu'],
                'nomor_kehilangan_polisi' => $validated['nomor_kehilangan_polisi'] ?? null,
                'status' => KartuKksPermohonan::STATUS_DIAJUKAN,
                'current_role_id' => $kelurahanRole->id,
                'kelurahan' => $validated['kelurahan'],
                'tanggal_insert' => now(),
                'created_by' => Auth::id(),
            ]);

            $permohonan->detail()->create(collect($validated)->only([
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
            ])->toArray());

            $lampiranData = [];
            foreach (['scan_ktp', 'scan_kartu_keluarga', 'surat_kehilangan_polisi', 'scan_kartu_kks', 'screenshot_dtsen'] as $field) {
                if ($request->hasFile($field)) {
                    $lampiranData[$field] = $request->file($field)->store('kartu-kks/lampiran', 'public');
                }
            }
            $permohonan->lampiran()->create($lampiranData);

            $permohonan->logs()->create([
                'tanggal_proses' => now(),
                'user_id' => Auth::id(),
                'username' => Auth::user()->name,
                'taskname' => 'Permohonan Masuk',
                'rolename' => 'Kelurahan',
                'catatan' => 'Data awal masuk melalui pendaftaran online.',
            ]);

            return $permohonan;
        });

        return redirect()->route('layanan.kartu-kks.riwayat')
            ->with('success', 'Pengajuan Kartu KKS berhasil dikirim, nomor permohonan #' . $permohonan->id);
    }

    /** GET /layanan/kartu-kks/riwayat */
    public function riwayat()
    {
        $data = KartuKksPermohonan::where('user_id', Auth::id())
            ->latest('tanggal_insert')
            ->get();

        return view('layanan.kartu-kks.riwayat', compact('data'));
    }
}
