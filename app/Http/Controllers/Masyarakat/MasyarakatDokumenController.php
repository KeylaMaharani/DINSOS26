<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\MasyarakatDokumen;
use Illuminate\Http\Request;

/**
 * Halaman "Dokumen" masyarakat: HANYA menampilkan (lihat & unduh).
 * Berkas masuk ke sini otomatis saat warga mengunggahnya di formulir pengajuan
 * (lihat App\Support\DokumenMasyarakat). Semua query dibatasi ke milik user yang login.
 */
class MasyarakatDokumenController extends Controller
{
    /** GET /akun-saya/dokumen */
    public function index(Request $request)
    {
        $dokumens = $request->user()->dokumens()
            ->orderByDesc('created_at')->orderByDesc('id')
            ->get()
            ->groupBy('jenis');

        return view('masyarakat.dokumen', [
            'jenisList' => MasyarakatDokumen::JENIS,
            'dokumens' => $dokumens,
            'maksVersi' => MasyarakatDokumen::MAKS_VERSI,
        ]);
    }
}
