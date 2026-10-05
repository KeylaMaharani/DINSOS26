<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController
{
    public function index()
    {
        $users = User::with('role')->latest()->paginate(10);
        $roles = Role::withCount('users')->orderBy('name')->get();

        return view('kelola-akses.index', compact('users', 'roles'));
    }

    // ================= PENGGUNA =================

    private function userMessages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',

            'role_id.required' => 'Hak akses wajib dipilih.',
            'role_id.exists' => 'Hak akses yang dipilih tidak valid.',

            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',

            'kecamatan.max' => 'Kecamatan maksimal 100 karakter.',
            'kelurahan.max' => 'Kelurahan maksimal 100 karakter.',
        ];
    }

    /** Wilayah tugas (dipakai akun petugas kelurahan). */
    private function wilayahRules(): array
    {
        return [
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            ...$this->wilayahRules(),
        ], $this->userMessages());

        User::create([
            // Form "Tambah Pengguna" tidak punya field nama terpisah, sedangkan
            // kolom `name` di tabel users NOT NULL tanpa default -- jadi
            // dipakaikan nilai username supaya insert tidak gagal.
            'name' => $validated['username'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'kecamatan' => $validated['kecamatan'] ?? null,
            'kelurahan' => $validated['kelurahan'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('akun.index', ['tab' => 'pengguna'])
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $akun)
    {
        return response()->json($akun->only(['id', 'username', 'email', 'role_id', 'kecamatan', 'kelurahan']));
    }

    public function update(Request $request, User $akun)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($akun->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($akun->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            ...$this->wilayahRules(),
        ], $this->userMessages());

        $akun->username = $validated['username'];
        $akun->email = $validated['email'];
        $akun->role_id = $validated['role_id'];
        $akun->kecamatan = $validated['kecamatan'] ?? null;
        $akun->kelurahan = $validated['kelurahan'] ?? null;

        if (!empty($validated['password'])) {
            $akun->password = Hash::make($validated['password']);
        }

        $akun->save();

        return redirect()->route('akun.index', ['tab' => 'pengguna'])
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $akun)
    {
        $akun->delete();

        return redirect()->route('akun.index', ['tab' => 'pengguna'])
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    // ================= HAK AKSES (ROLE) =================

    private function roleMessages(): array
    {
        return [
            'name.required' => 'Nama hak akses wajib diisi.',
            'name.max' => 'Nama hak akses maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',

            'permissions.array' => 'Format hak akses modul tidak valid.',
            'permissions.*.in' => 'Salah satu modul yang dipilih tidak valid.',
        ];
    }

    private function permissionsRules(): array
    {
        return [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(Role::MODULES))],
        ];
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            ...$this->permissionsRules(),
        ], $this->roleMessages());

        $validated['slug'] = $this->generateUniqueSlug($validated['name']);
        $validated['permissions'] = $validated['permissions'] ?? [];

        Role::create($validated);

        return redirect()->route('akun.index', ['tab' => 'role'])
            ->with('success', 'Hak Akses berhasil ditambahkan.');
    }

    public function editRole(Role $role)
    {
        return response()->json($role->only(['id', 'name', 'slug', 'description', 'permissions']));
    }

    public function updateRole(Request $request, Role $role)
    {
        if ($role->isSuperAdmin()) {
            return redirect()->route('akun.index', ['tab' => 'role'])
                ->with('error', 'Hak Akses Super Admin tidak bisa diubah.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            ...$this->permissionsRules(),
        ], $this->roleMessages());

        if ($role->name !== $validated['name']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $role->id);
        }

        $validated['permissions'] = $validated['permissions'] ?? [];

        $role->update($validated);

        return redirect()->route('akun.index', ['tab' => 'role'])
            ->with('success', 'Hak Akses berhasil diperbarui.');
    }

    public function destroyRole(Role $role)
    {
        if ($role->isSuperAdmin()) {
            return redirect()->route('akun.index', ['tab' => 'role'])
                ->with('error', 'Hak Akses Super Admin tidak bisa dihapus.');
        }

        if ($role->users()->exists()) {
            return redirect()->route('akun.index', ['tab' => 'role'])
                ->with('error', 'Hak Akses tidak bisa dihapus karena masih dipakai oleh user.');
        }

        $role->delete();

        return redirect()->route('akun.index', ['tab' => 'role'])
            ->with('success', 'Hak Akses berhasil dihapus.');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Role::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
