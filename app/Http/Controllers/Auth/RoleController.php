<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController
{
    /**
     * Pesan error validasi dalam Bahasa Indonesia, dipakai bareng di store() & update().
     */
    private function messages(): array
    {
        return [
            'name.required' => 'Nama hak akses wajib diisi.',
            'name.max' => 'Nama hak akses maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
        ];
    }

    public function index()
    {
        $roles = Role::withCount('users')->orderBy('name')->get();
        $users = User::with('role')->latest()->paginate(10);

        return view('user-role.index', compact('roles', 'users'));
    }

    /**
     * Simpan role baru (dari modal tambah role).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ], $this->messages());

        $validated['slug'] = $this->generateUniqueSlug($validated['name']);

        Role::create($validated);

        return redirect()->route('akun.role.index', ['tab' => 'role'])->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        return response()->json($role);
    }

    /**
     * Update role.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ], $this->messages());

        // Hanya regenerate slug kalau nama berubah
        if ($role->name !== $validated['name']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $role->id);
        }

        $role->update($validated);

        return redirect()->route('akun.role.index', ['tab' => 'role'])->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return redirect()->route('akun.role.index', ['tab' => 'role'])
                ->with('error', 'Role tidak bisa dihapus karena masih dipakai oleh user.');
        }

        $role->delete();

        return redirect()->route('akun.role.index', ['tab' => 'role'])->with('success', 'Role berhasil dihapus.');
    }

    /**
     * Generate slug unik dari nama role, tambahkan angka di belakang kalau duplikat.
     */
    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Role::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
