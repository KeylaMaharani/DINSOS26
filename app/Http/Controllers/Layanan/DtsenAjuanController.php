<?php

namespace App\Http\Controllers\Layanan;

use App\Http\Controllers\Controller;
use App\Models\DtsenPermohonan;
use App\Models\Role;
use App\Models\User;
use App\Support\DtsenNotifikasi;
use App\Support\GroundCheckSinkron;
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

    /**
     * POST /layanan/dtsen/ajukan
     * Pengajuan diinput petugas (Operator Dinsos) dan langsung masuk ke tahap Validasi Dinsos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|digits:16',
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

        $operatorRole = Role::firstOrCreate(
            ['slug' => 'operator_dinsos'],
            ['name' => 'Operator Dinsos', 'slug' => 'operator_dinsos']
        );

        $wargaId = User::where('username', $validated['nik'])->value('id');

        $permohonan = DB::transaction(function () use ($validated, $request, $operatorRole, $wargaId) {
            $permohonan = DtsenPermohonan::create([
                'user_id' => $wargaId,
                'nik' => $validated['nik'],
                'nama_pemohon' => $validated['nama'],
                'alamat' => $validated['alamat'],
                'alasan_cetak' => $validated['alasan_cetak'],
                'status' => DtsenPermohonan::STATUS_AWAL,
                'current_role_id' => $operatorRole->id,
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
                'rolename' => 'Operator Dinsos',
                'catatan' => 'Data awal diinput oleh petugas Dinas Sosial.',
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

        // Beri tahu Operator Dinsos lewat lonceng bahwa ada ajuan DTSEN baru
        DtsenNotifikasi::keRole(
            $permohonan,
            'operator_dinsos',
            'Pengajuan DTSEN baru',
            'Pengajuan ' . $permohonan->no_permohonan . ' atas nama ' . $validated['nama'] . ' menunggu validasi Anda.'
        );

        return redirect()->route('layanan.dtsen.riwayat')
            ->with('success', 'Pengajuan DTSEN berhasil dikirim, nomor permohonan ' . $permohonan->no_permohonan);
    }

    /** GET /layanan/dtsen/riwayat — riwayat pengajuan yang DIBUAT petugas yang login */
    public function riwayat()
    {
        $data = DtsenPermohonan::where('created_by', Auth::id())
            ->latest('tanggal_insert')
            ->latest('created_at')
            ->latest('id')
            ->get();

        return view('layanan.dtsen.riwayat', compact('data'));
    }
}
