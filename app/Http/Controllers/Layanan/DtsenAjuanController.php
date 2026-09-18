<?php

namespace App\Http\Controllers\Layanan;

use App\Http\Controllers\Controller;
use App\Models\DtsenPermohonan;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DtsenAjuanController extends Controller
{
    /** GET /layanan/dtsen/ajukan */
    public function create()
    {
        return view('layanan.dtsen.ajukan');
    }

    /** POST /layanan/dtsen/ajukan */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|max:20',
            'nama' => 'required|string|max:150',
            'no_kk' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'alasan_cetak' => ['required', Rule::in([
                'Kartu DTSEN rusak',
                'Kartu DTSEN hilang',
                'Perubahan data anggota keluarga',
                'Pencetakan ulang karena data tidak sesuai',
                'Kartu DTSEN sudah habis masa berlaku',
                'Pengajuan baru untuk keluarga penerima manfaat',
            ])],
            'screenshot_dtsen' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'scan_ktp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $kelurahanRole = Role::firstOrCreate(
            ['slug' => 'kelurahan'],
            ['name' => 'Kelurahan', 'slug' => 'kelurahan']
        );

        $permohonan = DB::transaction(function () use ($validated, $request, $kelurahanRole) {
            $permohonan = DtsenPermohonan::create([
                'user_id' => Auth::id(),
                'nik' => $validated['nik'],
                'nama_pemohon' => $validated['nama'],
                'alamat' => $validated['alamat'],
                'alasan_cetak' => $validated['alasan_cetak'],
                'status' => DtsenPermohonan::STATUS_DIAJUKAN,
                'current_role_id' => $kelurahanRole->id,
                'kelurahan' => $validated['kelurahan'],
                'tanggal_insert' => now(),
                'created_by' => Auth::id(),
            ]);

            $permohonan->detail()->create([
                'nik' => $validated['nik'],
                'nama' => $validated['nama'],
                'no_kk' => $validated['no_kk'],
                'alamat' => $validated['alamat'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
            ]);

            $lampiranData = [];
            foreach (['screenshot_dtsen', 'scan_ktp'] as $field) {
                if ($request->hasFile($field)) {
                    $lampiranData[$field] = $request->file($field)->store('dtsen/lampiran', 'public');
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

        return redirect()->route('layanan.dtsen.riwayat')
            ->with('success', 'Pengajuan DTSEN berhasil dikirim, nomor permohonan #' . $permohonan->id);
    }

    /** GET /layanan/dtsen/riwayat */
    public function riwayat()
    {
        $data = DtsenPermohonan::where('user_id', Auth::id())
            ->latest('tanggal_insert')
            ->get();

        return view('layanan.dtsen.riwayat', compact('data'));
    }
}
