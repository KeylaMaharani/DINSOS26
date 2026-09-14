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
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], $this->userMessages());

        User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('akun.index', ['tab' => 'pengguna'])
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $akun)
    {
        return response()->json($akun->only(['id', 'username', 'email', 'role_id']));
    }

    public function update(Request $request, User $akun)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($akun->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($akun->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], $this->userMessages());

        $akun->username = $validated['username'];
        $akun->email = $validated['email'];
        $akun->role_id = $validated['role_id'];

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
        ];
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ], $this->roleMessages());

        $validated['slug'] = $this->generateUniqueSlug($validated['name']);

        Role::create($validated);

        return redirect()->route('akun.index', ['tab' => 'role'])
            ->with('success', 'Hak Akses berhasil ditambahkan.');
    }

    public function editRole(Role $role)
    {
        return response()->json($role);
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ], $this->roleMessages());

        if ($role->name !== $validated['name']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $role->id);
        }

        $role->update($validated);

        return redirect()->route('akun.index', ['tab' => 'role'])
            ->with('success', 'Hak Akses berhasil diperbarui.');
    }

    public function destroyRole(Role $role)
    {
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
