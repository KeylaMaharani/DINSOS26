<?php

namespace App\Http\Controllers;

use App\Models\KartuKksPermohonan;
use Illuminate\Support\Facades\Storage;

class KartuKksVerifikasiController extends Controller
{
    public function pengantar(KartuKksPermohonan $permohonan)
    {
        $path = $permohonan->surat?->surat_pengantar_kelurahan;

        abort_unless($path && Storage::disk('public')->exists($path), 404, 'Surat tidak ditemukan.');

        return response()->file(Storage::disk('public')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Surat-Pengantar-KKS-' . $permohonan->no_permohonan . '.pdf"',
        ]);
    }

    public function keterangan(KartuKksPermohonan $permohonan)
    {
        $path = $permohonan->surat?->surat_keterangan_dinsos;

        abort_unless($path && Storage::disk('public')->exists($path), 404, 'Surat tidak ditemukan.');

        return response()->file(Storage::disk('public')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Surat-Keterangan-KKS-' . $permohonan->no_permohonan . '.pdf"',
        ]);
    }
}