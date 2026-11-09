<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KesanPilihan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/** Akun Filantropi (donatur). Akun dibuat petugas, lalu diberikan ke donatur. */
class KesanFilantropiController extends Controller
{
    private function query()
    {
        return User::whereHas('role', fn ($r) => $r->where('slug', 'filantropi'));
    }

    public function index(Request $request)
    {
        $q = $this->query();
        if ($s = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('username', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
        }
        $items = $q->latest('id')->paginate(12)->withQueryString();

        $jumlah = KesanPilihan::whereIn('user_id', $items->pluck('id'))->where('status', '!=', 'keranjang')
            ->selectRaw('user_id, count(*) as total')->groupBy('user_id')->pluck('total', 'user_id');

        return view('admin.kesan.filantropi.index', compact('items', 'jumlah'));
    }

    public function create()
    {
        return view('admin.kesan.filantropi.form', ['akun' => null]);
    }

    public function store(Request $request)
    {
        $role = Role::where('slug', 'filantropi')->first();
        if (! $role) {
            return back()->with('error', 'Role "filantropi" belum ada. Jalankan: php artisan db:seed --class=KesanSeeder')->withInput();
        }

        $d = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:60', 'unique:users,username'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
        ]);

        (new User())->forceFill([
            'name' => $d['name'], 'username' => $d['username'], 'email' => $d['email'],
            'password' => Hash::make($d['password']), 'role_id' => $role->id,
        ])->save();

        return redirect()->route('kesan-admin.filantropi.index')->with('success', 'Akun Filantropi dibuat. Berikan username & kata sandi kepada donatur.');
    }

    public function edit(int $akun)
    {
        return view('admin.kesan.filantropi.form', ['akun' => $this->query()->findOrFail($akun)]);
    }

    public function update(Request $request, int $akun)
    {
        $user = $this->query()->findOrFail($akun);

        $d = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:60', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
        ]);

        $user->forceFill(['name' => $d['name'], 'username' => $d['username'], 'email' => $d['email']]);
        if (! empty($d['password'])) {
            $user->password = Hash::make($d['password']);
        }
        $user->save();

        return redirect()->route('kesan-admin.filantropi.index')->with('success', 'Akun Filantropi diperbarui.');
    }

    public function destroy(int $akun)
    {
        $user = $this->query()->findOrFail($akun);
        if (KesanPilihan::where('user_id', $user->id)->where('status', '!=', 'keranjang')->exists()) {
            return back()->with('error', 'Akun ini sudah punya riwayat pilihan dan tidak dapat dihapus.');
        }
        $user->delete();

        return back()->with('success', 'Akun Filantropi dihapus.');
    }
}
