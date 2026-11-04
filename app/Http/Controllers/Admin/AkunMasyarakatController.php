<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AkunMasyarakatController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $users = User::whereHas('role', fn ($r) => $r->where('slug', 'masyarakat'))
            ->with(['pengajuanPbiApbd' => fn ($q) => $q->latest('id')])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhereHas('pengajuanPbiApbd', fn ($p) => $p
                            ->where('nik_kepala_keluarga', 'like', "%{$search}%")
                            ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%")
                            ->orWhere('no_telp', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('akun-masyarakat.index', compact('users'));
    }
}
