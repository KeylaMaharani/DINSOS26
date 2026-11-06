<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NgopiPagiKonselor as Kon;
use App\Models\NgopiPagiKonsultasi as K;
use App\Models\NgopiPagiSpesialisasi as S;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class NgopiPagiKonselorController extends Controller
{
    public function index(Request $request)
    {
        $items = Kon::with(['user', 'spesialisasis'])
            ->when($request->query('q'), fn ($q, $k) => $q->where(fn ($w) => $w->where('nama_tampilan', 'like', "%{$k}%")->orWhere('headline', 'like', "%{$k}%")))
            ->urut()->get();

        return view('admin.ngopi-pagi.konselor.index', ['items' => $items] + $this->umum());
    }

    public function create()
    {
        return view('admin.ngopi-pagi.konselor.form', ['konselor' => null] + $this->formData(null));
    }

    public function store(Request $request)
    {
        $d = $this->validasi($request, null);

        DB::transaction(function () use ($request, $d) {
            if ($d['akun_mode'] === 'baru') {
                $userId = User::create([
                    'name' => $d['nama_tampilan'],
                    'username' => $d['akun_username'],
                    'email' => $d['akun_email'],
                    'password' => Hash::make($d['akun_password']),
                    'role_id' => $d['akun_role_id'],
                ])->id;
            } else {
                $userId = $d['user_id'];
            }

            $kon = Kon::create($this->atribut($d, $request) + ['user_id' => $userId]);
            $kon->spesialisasis()->sync($d['spesialisasi_ids']);
        });

        return redirect()->route('ngopi-pagi.admin.konselor.index')->with('success', 'Konselor ditambahkan.');
    }

    public function edit(Kon $konselor)
    {
        return view('admin.ngopi-pagi.konselor.form', ['konselor' => $konselor->load(['user.role', 'spesialisasis'])] + $this->formData($konselor));
    }

    public function update(Request $request, Kon $konselor)
    {
        $d = $this->validasi($request, $konselor);

        DB::transaction(function () use ($request, $d, $konselor) {
            $attr = $this->atribut($d, $request);

            if ($request->hasFile('foto') || $request->boolean('hapus_foto')) {
                $this->hapusFoto($konselor);
                $attr['foto'] = $request->hasFile('foto') ? $attr['foto'] : null;
            } else {
                unset($attr['foto']);
            }

            $konselor->update($attr);
            $konselor->spesialisasis()->sync($d['spesialisasi_ids']);
        });

        return redirect()->route('ngopi-pagi.admin.konselor.index')->with('success', 'Data konselor diperbarui.');
    }

    public function destroy(Kon $konselor)
    {
        $this->hapusFoto($konselor);
        $konselor->delete(); // akun login (users) tidak ikut dihapus

        return back()->with('success', 'Profil konselor dihapus. Akun login-nya tetap ada di Kelola Akses.');
    }

    // ---------- util ----------
    private function validasi(Request $r, ?Kon $kon): array
    {
        $baru = ! $kon && $r->input('akun_mode') === 'baru';
        $ada = ! $kon && $r->input('akun_mode') !== 'baru';

        return $r->validate([
            'nama_tampilan' => ['required', 'string', 'max:100'],
            'headline' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1500'],
            'pengalaman_tahun' => ['nullable', 'integer', 'min:0', 'max:60'],
            'status' => ['required', Rule::in(array_keys(Kon::STATUS))],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
            'spesialisasi_ids' => ['required', 'array', 'min:1'],
            'spesialisasi_ids.*' => ['integer', Rule::exists('ngopi_pagi_spesialisasis', 'id')],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'akun_mode' => [$kon ? 'nullable' : 'required', Rule::in(['ada', 'baru'])],
            'user_id' => [Rule::requiredIf($ada), 'nullable', Rule::exists('users', 'id'), Rule::unique('ngopi_pagi_konselors', 'user_id')],
            'akun_username' => [Rule::requiredIf($baru), 'nullable', 'string', 'max:50', Rule::unique('users', 'username')],
            'akun_email' => [Rule::requiredIf($baru), 'nullable', 'email', Rule::unique('users', 'email')],
            'akun_password' => [Rule::requiredIf($baru), 'nullable', 'string', 'min:8'],
            'akun_role_id' => [Rule::requiredIf($baru), 'nullable', Rule::exists('roles', 'id')],
        ], [
            'spesialisasi_ids.required' => 'Pilih minimal satu spesialisasi.',
            'user_id.required' => 'Pilih akun yang akan dipakai konselor.',
            'user_id.unique' => 'Akun ini sudah dipakai konselor lain.',
            'akun_username.unique' => 'Username sudah dipakai.',
            'akun_email.unique' => 'Email sudah dipakai.',
            'akun_password.min' => 'Kata sandi minimal 8 karakter.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);
    }

    private function atribut(array $d, Request $r): array
    {
        return [
            'nama_tampilan' => $d['nama_tampilan'],
            'headline' => $d['headline'],
            'bio' => $d['bio'] ?? null,
            'pengalaman_tahun' => $d['pengalaman_tahun'] ?? null,
            'status' => $d['status'],
            'urutan' => $d['urutan'] ?? 0,
            'aktif' => $r->boolean('aktif'),
            'foto' => $r->hasFile('foto') ? $r->file('foto')->store('ngopi-pagi/konselor', 'public') : null,
        ];
    }

    private function hapusFoto(Kon $kon): void
    {
        if ($kon->foto) {
            Storage::disk('public')->delete($kon->foto);
        }
    }

    private function umum(): array
    {
        return ['hitung' => K::selectRaw('tahap, count(*) as total')->groupBy('tahap')->pluck('total', 'tahap')];
    }

    private function formData(?Kon $kon): array
    {
        $dipakai = Kon::when($kon, fn ($q) => $q->where('id', '!=', $kon->id))->pluck('user_id');

        return $this->umum() + [
            'spesialisasis' => S::urut()->get(),
            'users' => User::with('role')->whereNotIn('id', $dipakai)
                ->whereHas('role', fn ($q) => $q->where('slug', '!=', 'masyarakat'))->orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get()->where('slug', '!=', 'masyarakat')->values(),
        ];
    }
}
