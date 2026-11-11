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
    /**
     * Daftar pengguna STAF saja. Akun masyarakat punya menu sendiri
     * (Kelola Akses Masyarakat) -> AkunMasyarakatController.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query("search"));

        $users = User::with("role")
            ->where(function ($q) {
                $q->whereNull("role_id")
                    ->orWhereHas("role", fn ($r) => $r->where("slug", "!=", "masyarakat"));
            })
            ->when($search !== "", function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where("username", "like", "%{$search}%")
                        ->orWhere("name", "like", "%{$search}%")
                        ->orWhere("email", "like", "%{$search}%")
                        ->orWhereHas("role", fn ($r) => $r->where("name", "like", "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = Role::withCount("users")->orderBy("name")->get();

        return view("kelola-akses.index", compact("users", "roles"));
    }

    // ================= PENGGUNA =================

    private function userMessages(): array
    {
        return [
            "username.required" => "Username wajib diisi.",
            "username.max" => "Username maksimal 255 karakter.",
            "username.unique" => "Username sudah digunakan, silakan pilih yang lain.",

            "name.max" => "Nama lengkap maksimal 255 karakter.",

            "email.required" => "Email wajib diisi.",
            "email.email" => "Format email tidak valid.",
            "email.max" => "Email maksimal 255 karakter.",
            "email.unique" => "Email sudah terdaftar, silakan gunakan email lain.",

            "role_id.required" => "Hak akses wajib dipilih.",
            "role_id.exists" => "Hak akses yang dipilih tidak valid.",

            "password.required" => "Kata sandi wajib diisi.",
            "password.min" => "Kata sandi minimal 6 karakter.",
            "password.confirmed" => "Konfirmasi kata sandi tidak cocok.",

            "kecamatan.max" => "Kecamatan maksimal 100 karakter.",
            "kelurahan.max" => "Kelurahan maksimal 100 karakter.",

            "nip.max" => "NIP maksimal 30 karakter.",
            "pangkat.max" => "Pangkat maksimal 100 karakter.",
            "jabatan.max" => "Jabatan maksimal 100 karakter.",
        ];
    }

    /** Wilayah tugas (dipakai akun petugas kelurahan). */
    private function wilayahRules(): array
    {
        return [
            "kecamatan" => ["nullable", "string", "max:100"],
            "kelurahan" => ["nullable", "string", "max:100"],
        ];
    }

    /** Nama lengkap + data penandatangan surat (dipakai pada surat pengantar / suket). */
    private function profilRules(): array
    {
        return [
            "name" => ["nullable", "string", "max:255"],
            "nip" => ["nullable", "string", "max:30"],
            "pangkat" => ["nullable", "string", "max:100"],
            "jabatan" => ["nullable", "string", "max:100"],
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "username" => ["required", "string", "max:255", "unique:users,username"],
            "email" => ["required", "email", "max:255", "unique:users,email"],
            "role_id" => ["required", "exists:roles,id"],
            "password" => ["required", "string", "min:6", "confirmed"],
            ...$this->wilayahRules(),
            ...$this->profilRules(),
        ], $this->userMessages());

        User::create([
            // Kolom `name` NOT NULL: kalau Nama Lengkap tidak diisi, pakai username.
            "name" => filled($validated["name"] ?? null) ? trim($validated["name"]) : $validated["username"],
            "username" => $validated["username"],
            "email" => $validated["email"],
            "role_id" => $validated["role_id"],
            "kecamatan" => $validated["kecamatan"] ?? null,
            "kelurahan" => $validated["kelurahan"] ?? null,
            "nip" => $validated["nip"] ?? null,
            "pangkat" => $validated["pangkat"] ?? null,
            "jabatan" => $validated["jabatan"] ?? null,
            // Hanya hash. Password asli TIDAK disimpan / ditampilkan di mana pun.
            "password" => Hash::make($validated["password"]),
        ]);

        return redirect()->route("akun.index", ["tab" => "pengguna"])
            ->with("success", "Pengguna berhasil ditambahkan.");
    }

    public function edit(User $akun)
    {
        return response()->json($akun->only([
            "id", "username", "name", "email", "role_id", "kecamatan", "kelurahan", "nip", "pangkat", "jabatan",
        ]));
    }

    public function update(Request $request, User $akun)
    {
        $validated = $request->validate([
            "username" => ["required", "string", "max:255", Rule::unique("users", "username")->ignore($akun->id)],
            "email" => ["required", "email", "max:255", Rule::unique("users", "email")->ignore($akun->id)],
            "role_id" => ["required", "exists:roles,id"],
            "password" => ["nullable", "string", "min:6", "confirmed"],
            ...$this->wilayahRules(),
            ...$this->profilRules(),
        ], $this->userMessages());

        $akun->username = $validated["username"];
        // Nama lengkap dikosongkan -> jangan timpa nama yang sudah ada dengan string kosong
        if (filled($validated["name"] ?? null)) {
            $akun->name = trim($validated["name"]);
        }
        $akun->email = $validated["email"];
        $akun->role_id = $validated["role_id"];
        $akun->kecamatan = $validated["kecamatan"] ?? null;
        $akun->kelurahan = $validated["kelurahan"] ?? null;
        $akun->nip = $validated["nip"] ?? null;
        $akun->pangkat = $validated["pangkat"] ?? null;
        $akun->jabatan = $validated["jabatan"] ?? null;

        if (!empty($validated["password"])) {
            $akun->password = Hash::make($validated["password"]);
        }

        $akun->save();

        return redirect()->route("akun.index", ["tab" => "pengguna"])
            ->with("success", "Pengguna berhasil diperbarui.");
    }

    /** Dipakai dari dua halaman (staf & masyarakat), jadi kembali ke halaman asal. */
    public function destroy(Request $request, User $akun)
    {
        if ($akun->id === $request->user()->id) {
            return back()->with("error", "Tidak bisa menghapus akun yang sedang Anda pakai.");
        }

        $akun->delete();

        return back()->with("success", "Pengguna berhasil dihapus.");
    }

    // ================= HAK AKSES (ROLE) =================

    private function roleMessages(): array
    {
        return [
            "name.required" => "Nama hak akses wajib diisi.",
            "name.max" => "Nama hak akses maksimal 255 karakter.",
            "description.max" => "Deskripsi maksimal 255 karakter.",

            "permissions.array" => "Format hak akses menu tidak valid.",
            "permissions.*.in" => "Salah satu menu yang dipilih tidak valid.",
        ];
    }

    private function permissionsRules(): array
    {
        return [
            "permissions" => ["nullable", "array"],
            // Sah: kunci modul, modul.submenu, dan modul.submenu.aksi (lihat Role::semuaKunci()).
            "permissions.*" => ["string", Rule::in(Role::semuaKunci())],
        ];
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "description" => ["nullable", "string", "max:255"],
            ...$this->permissionsRules(),
        ], $this->roleMessages());

        $validated["slug"] = $this->generateUniqueSlug($validated["name"]);
        // Rapikan: buang duplikat, aksi tanpa submenu, dan sinkronkan kunci modul.
        $validated["permissions"] = Role::normalisasi($validated["permissions"] ?? []);

        Role::create($validated);

        return redirect()->route("akun.index", ["tab" => "role"])
            ->with("success", "Hak Akses berhasil ditambahkan.");
    }

    public function editRole(Role $role)
    {
        return response()->json($role->only(["id", "name", "slug", "description", "permissions"]));
    }

    public function updateRole(Request $request, Role $role)
    {
        if ($role->isSuperAdmin()) {
            return redirect()->route("akun.index", ["tab" => "role"])
                ->with("error", "Hak Akses Super Admin tidak bisa diubah.");
        }

        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "description" => ["nullable", "string", "max:255"],
            ...$this->permissionsRules(),
        ], $this->roleMessages());

        if ($role->name !== $validated["name"]) {
            $validated["slug"] = $this->generateUniqueSlug($validated["name"], $role->id);
        }

        $validated["permissions"] = Role::normalisasi($validated["permissions"] ?? []);

        $role->update($validated);

        return redirect()->route("akun.index", ["tab" => "role"])
            ->with("success", "Hak Akses berhasil diperbarui.");
    }

    public function destroyRole(Role $role)
    {
        if ($role->isSuperAdmin()) {
            return redirect()->route("akun.index", ["tab" => "role"])
                ->with("error", "Hak Akses Super Admin tidak bisa dihapus.");
        }

        if ($role->users()->exists()) {
            return redirect()->route("akun.index", ["tab" => "role"])
                ->with("error", "Hak Akses tidak bisa dihapus karena masih dipakai oleh user.");
        }

        $role->delete();

        return redirect()->route("akun.index", ["tab" => "role"])
            ->with("success", "Hak Akses berhasil dihapus.");
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Role::where("slug", $slug)
                ->when($ignoreId, fn($q) => $q->where("id", "!=", $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}