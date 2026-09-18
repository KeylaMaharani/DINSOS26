<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\PbiApbn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $isLengkap = $pbiApbn
            && $pbiApbn->latitude
            && $pbiApbn->longitude
            && $pbiApbn->scan_ktp
            && $pbiApbn->scan_kk
            && $pbiApbn->foto_rumah
            && $pbiApbn->foto_kamar_mandi
            && $pbiApbn->foto_selfie_ktp;

        return view('masyarakat.dashboard', compact('pbiApbn', 'isLengkap'));
    }

    /** GET /akun-saya/pbi-apbn/{pbiApbn}/lengkapi */
    public function lengkapiData(Request $request, PbiApbn $pbiApbn)
    {
        $this->authorizePemilik($request, $pbiApbn);

        return view('masyarakat.pbi-apbn.lengkapi', compact('pbiApbn'));
    }

    /** POST /akun-saya/pbi-apbn/{pbiApbn}/lengkapi */
    public function lengkapiDataStore(Request $request, PbiApbn $pbiApbn)
    {
        $this->authorizePemilik($request, $pbiApbn);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            'scan_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'scan_kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'foto_rumah' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'foto_kamar_mandi' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'foto_selfie_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'surat_rawat_inap' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'screenshot_pembaharuan_desil' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'screenshot_dtsen' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'latitude.required' => 'Lokasi belum berhasil diambil. Izinkan akses lokasi lalu coba lagi.',
            'longitude.required' => 'Lokasi belum berhasil diambil. Izinkan akses lokasi lalu coba lagi.',
        ]);

        $fileFields = [
            'scan_ktp', 'scan_kk', 'foto_rumah', 'foto_kamar_mandi',
            'foto_selfie_ktp', 'surat_rawat_inap',
            'screenshot_pembaharuan_desil', 'screenshot_dtsen',
        ];

        $dataToUpdate = [
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Hapus file lama supaya storage tidak menumpuk
                if ($pbiApbn->{$field}) {
                    Storage::disk('public')->delete($pbiApbn->{$field});
                }

                $dataToUpdate[$field] = $request->file($field)
                    ->store('pbi-apbn/lampiran', 'public');
            }
        }

        $pbiApbn->update($dataToUpdate);

        return redirect()->route('masyarakat.dashboard')
            ->with('success', 'Data berhasil dilengkapi dan akan diproses oleh petugas.');
    }

    private function authorizePemilik(Request $request, PbiApbn $pbiApbn): void
    {
        abort_unless($pbiApbn->user_id === $request->user()->id, 403, 'Ini bukan pengajuan Anda.');
    }
}
