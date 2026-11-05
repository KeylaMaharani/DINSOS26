@extends('layouts.admin')

@section('title', 'Kelola Pengguna & Hak Akses - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Kelola Pengguna & Hak Akses')

@section('content')
    @php
        $activeTab = request()->query('tab', 'pengguna');

        // Kelompokkan modul berdasarkan prefix label sebelum " - ".
        // Contoh: "PFM - Kartu KKS" => grup "PFM", item "Kartu KKS".
        // Label tanpa " - " (mis. "Beranda") masuk grup "Umum".
        // Tetap otomatis: modul baru di Role::MODULES langsung ikut muncul.
        $moduleGroups = [];
        foreach (\App\Models\Role::MODULES as $key => $label) {
            if (str_contains($label, ' - ')) {
                [$groupName, $itemLabel] = explode(' - ', $label, 2);
            } else {
                $groupName = 'Umum';
                $itemLabel = $label;
            }
            $moduleGroups[$groupName][$key] = $itemLabel;
        }
        // Pastikan "Umum" tampil paling atas.
        if (isset($moduleGroups['Umum'])) {
            $moduleGroups = ['Umum' => $moduleGroups['Umum']] + $moduleGroups;
        }
    @endphp
    <div x-data="{ tab: '{{ $activeTab }}' }">

        {{-- Tab Switcher --}}
        <div class="flex items-center gap-2 mb-4">
            <button @click="tab = 'pengguna'"
                :class="tab === 'pengguna' ? 'bg-primary text-white' :
                    'bg-surface-container-lowest text-on-surface-variant border border-outline-variant/40'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <span class="material-symbols-outlined text-[18px]">group</span>
                Pengguna
            </button>
            <button @click="tab = 'role'"
                :class="tab === 'role' ? 'bg-primary text-white' :
                    'bg-surface-container-lowest text-on-surface-variant border border-outline-variant/40'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <span class="material-symbols-outlined text-[18px]">shield_person</span>
                Hak Akses
            </button>
        </div>

        {{-- ============== TAB PENGGUNA (staf) ============== --}}
        <div x-show="tab === 'pengguna'" x-cloak x-data="userManager()"
            class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-on-surface">Daftar Pengguna</h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">Akun staf. Akun masyarakat ada di menu
                        "Kelola Akses Masyarakat".</p>
                </div>
                <button @click="openCreate()"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-primary text-on-primary">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tambah Pengguna
                </button>
            </div>

            <form method="GET" action="{{ route('akun.index') }}" class="mb-4">
                <input type="hidden" name="tab" value="pengguna">
                <label class="w-full sm:w-72 h-9 rounded-lg bg-surface-container-low border border-outline-variant/40 flex items-center gap-2 px-3">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" data-live-search
                        placeholder="Cari username, nama, email, atau hak akses..."
                        class="w-full min-w-0 bg-transparent border-0 outline-none text-xs focus:ring-0" />
                </label>
            </form>

            @if (session('success') && $activeTab === 'pengguna')
                <div id="alert-success"
                    class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-700 transition-opacity duration-500">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error') && $activeTab === 'pengguna')
                <div
                    class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700">
                    <span class="material-symbols-outlined text-[18px]">error</span>
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any() && $activeTab === 'pengguna')
                <div class="mb-4 text-sm text-error">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-on-surface-variant border-b border-outline-variant/40">
                            <th class="py-2 w-12">No</th>
                            <th class="py-2">Username</th>
                            <th class="py-2">Email</th>
                            <th class="py-2">Hak Akses</th>
                            <th class="py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-b border-outline-variant/20">
                                <td class="py-2">{{ $users->firstItem() + $loop->index }}</td>
                                <td class="py-2">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 shrink-0 rounded-full bg-primary text-on-primary flex items-center justify-center text-xs font-semibold uppercase">
                                            {{ substr($user->username, 0, 1) }}
                                        </div>
                                        <div class="leading-tight">
                                            <p>{{ $user->username }}</p>
                                            @if ($user->name && $user->name !== $user->username)
                                                <p class="text-xs text-on-surface-variant">{{ $user->name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2">{{ $user->email }}</td>
                                <td class="py-2">
                                    @if ($user->role)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-secondary-fixed text-on-secondary-container">
                                            {{ $user->role->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-on-surface-variant italic">Belum ada role</span>
                                    @endif
                                </td>

                                <td class="py-2 text-center">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        {{-- Butuh izin pemilik akun + OTP. Tidak tersedia untuk diri sendiri / Super Admin lain. --}}
                                        @if ($user->id !== auth()->id() && !$user->role?->isSuperAdmin())
                                            <form action="{{ route('akun.impersonate', ['akun' => $user->id]) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Minta izin masuk sebagai {{ $user->username }}"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-brand text-white hover:opacity-90 transition">
                                                    <span class="material-symbols-outlined text-[14px]">login</span>
                                                    Masuk sebagai
                                                </button>
                                            </form>
                                        @endif
                                        <button @click="openEdit({{ $user->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-100 text-amber-700 hover:bg-amber-200 transition">
                                            <span class="material-symbols-outlined text-[14px]">edit</span>
                                            Edit
                                        </button>
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('akun.destroy', ['akun' => $user->id]) }}?tab=pengguna"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Yakin hapus akun ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition">
                                                    <span class="material-symbols-outlined text-[14px]">delete</span>
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>

            {{-- ===== MODAL TAMBAH / EDIT USER ===== --}}
            <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div @click.outside="modalOpen = false" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="bg-surface-container-lowest w-full max-w-lg max-h-[90vh] flex flex-col rounded-xl shadow-lg overflow-hidden">

                    {{-- Header (tetap di atas) --}}
                    <div class="shrink-0 flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                        <h3 class="text-base font-semibold text-on-surface"
                            x-text="mode === 'create' ? 'Tambah Pengguna' : 'Edit Pengguna'"></h3>
                        <button type="button" @click="modalOpen = false" class="text-on-surface-variant">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <form :action="mode === 'create' ? '{{ route('akun.store', ['tab' => 'pengguna']) }}' : editUrl"
                        method="POST" class="flex flex-col min-h-0 flex-1">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        {{-- Isi (scroll di sini kalau kepanjangan) --}}
                        <div class="flex-1 min-h-0 overflow-y-auto p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Username</label>
                                <input name="username" x-model="form.username" type="text"
                                    class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" required />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                                <input name="name" x-model="form.name" type="text"
                                    placeholder="Nama + gelar, sesuai yang dicetak di surat"
                                    class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                                <p class="text-xs text-on-surface-variant mt-1">Kosong = memakai username.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input name="email" x-model="form.email" type="email"
                                    class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" required />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Role</label>
                                <select name="role_id" x-model="form.role_id"
                                    class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" required>
                                    <option value="" disabled>Pilih role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @if ($roles->isEmpty())
                                    <p class="text-xs text-error mt-1">Belum ada Hak Akses. Tambahkan Hak Akses dulu di tab
                                        "Hak Akses".</p>
                                @endif
                            </div>

                            <div x-show="isKelurahan" x-cloak class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Kecamatan</label>
                                    <input name="kecamatan" x-model="form.kecamatan" type="text"
                                        placeholder="Contoh: Bogor Tengah"
                                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Kelurahan</label>
                                    <input name="kelurahan" x-model="form.kelurahan" type="text"
                                        placeholder="Contoh: Paledang"
                                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                                </div>
                                <p class="col-span-2 text-xs text-on-surface-variant -mt-1">
                                    Wilayah tugas petugas. Harus sama dengan nama kelurahan/kecamatan yang diisi masyarakat
                                    saat mendaftar.
                                </p>
                            </div>

                            {{-- Data penandatangan: dipakai pada surat pengantar Kelurahan & suket Dinsos --}}
                            <div class="rounded-lg border border-outline-variant/40 p-3 space-y-3">
                                <div>
                                    <p class="text-sm font-medium">Data Penandatangan Surat <span
                                            class="text-xs font-normal text-on-surface-variant">(opsional)</span></p>
                                    <p class="text-xs text-on-surface-variant mt-0.5">Isi untuk akun Lurah dan Kepala Dinas.
                                        Otomatis muncul di surat pengantar / suket.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1">NIP</label>
                                    <input name="nip" x-model="form.nip" type="text" maxlength="30"
                                        placeholder="Contoh: 19700101 199501 1 001"
                                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium mb-1">Pangkat / Golongan</label>
                                        <input name="pangkat" x-model="form.pangkat" type="text" maxlength="100"
                                            placeholder="Contoh: Pembina Utama Muda"
                                            class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1">Jabatan</label>
                                        <input name="jabatan" x-model="form.jabatan" type="text" maxlength="100"
                                            placeholder="Contoh: Lurah"
                                            class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    <span x-text="mode === 'create' ? 'Kata Sandi' : 'Kata Sandi Baru'"></span>
                                </label>
                                <template x-if="mode === 'edit'">
                                    <p class="text-xs text-red-600 font-medium mb-1">Kosongkan jika tidak ingin
                                        mengubah kata sandi.</p>
                                </template>
                                <div class="relative" x-data="{ show: false }">
                                    <input name="password" :type="show ? 'text' : 'password'" autocomplete="new-password"
                                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 pr-10 text-sm"
                                        :required="mode === 'create'" />
                                    <button type="button" @click="show = !show" tabindex="-1"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-on-surface-variant"
                                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                        <span class="material-symbols-outlined text-[16px]"
                                            x-text="show ? 'visibility_off' : 'visibility'"></span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    <span
                                        x-text="mode === 'create' ? 'Konfirmasi Kata Sandi' : 'Konfirmasi Kata Sandi Baru'"></span>
                                </label>
                                <div class="relative" x-data="{ show: false }">
                                    <input name="password_confirmation" :type="show ? 'text' : 'password'"
                                        autocomplete="new-password"
                                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 pr-10 text-sm"
                                        :required="mode === 'create'" />
                                    <button type="button" @click="show = !show" tabindex="-1"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-on-surface-variant"
                                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                        <span class="material-symbols-outlined text-[16px]"
                                            x-text="show ? 'visibility_off' : 'visibility'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Footer (tetap di bawah) --}}
                        <div class="shrink-0 flex justify-end gap-2 px-5 py-3 border-t border-outline-variant/40">
                            <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ============== TAB ROLE & PERMISSION ============== --}}
        <div x-show="tab === 'role'" x-cloak x-data="roleManager()"
            class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-on-surface">Daftar Hak Akses</h2>
                </div>
                <button @click="openCreate()"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-primary text-on-primary">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tambah Hak Akses
                </button>
            </div>

            @if (session('success') && $activeTab === 'role')
                <div id="alert-success-role"
                    class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-700 transition-opacity duration-500">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error') && $activeTab === 'role')
                <div
                    class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700">
                    <span class="material-symbols-outlined text-[18px]">error</span>
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any() && $activeTab === 'role')
                <div class="mb-4 text-sm text-error">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-on-surface-variant border-b border-outline-variant/40">
                        <th class="py-2 w-12">No</th>
                        <th class="py-2">Nama Hak Akses</th>
                        <th class="py-2">Deskripsi</th>
                        <th class="py-2">Jumlah User</th>
                        <th class="py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr class="border-b border-outline-variant/20">
                            <td class="py-2">{{ $loop->iteration }}</td>
                            <td class="py-2 font-medium">
                                {{ $role->name }}
                                @if ($role->isSuperAdmin())
                                    <span class="ml-1 text-[10px] uppercase tracking-wide text-on-surface-variant">(bawaan
                                        sistem)</span>
                                @endif
                            </td>
                            <td class="py-2 text-on-surface-variant">{{ $role->description ?? '-' }}</td>
                            <td class="py-2">{{ $role->users_count }}</td>
                            <td class="py-2 text-center">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <button @click="openEdit('{{ $role->slug }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-100 text-amber-700 hover:bg-amber-200 transition">
                                        <span class="material-symbols-outlined text-[14px]">edit</span>
                                        Edit
                                    </button>
                                    @unless ($role->isSuperAdmin())
                                        <form action="{{ route('akun.role.destroy', $role) }}?tab=role" method="POST"
                                            class="inline" onsubmit="return confirm('Yakin hapus role ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition">
                                                <span class="material-symbols-outlined text-[14px]">delete</span>
                                                Hapus
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- ===== MODAL TAMBAH / EDIT ROLE ===== --}}
            <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div @click.outside="modalOpen = false" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="bg-surface-container-lowest w-full max-w-3xl max-h-[90vh] flex flex-col rounded-xl shadow-lg overflow-hidden">

                    {{-- Header (tetap di atas) --}}
                    <div class="shrink-0 flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                        <h3 class="text-base font-semibold text-on-surface"
                            x-text="mode === 'create' ? 'Tambah Hak Akses' : 'Edit Hak Akses'"></h3>
                        <button type="button" @click="modalOpen = false" class="text-on-surface-variant">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <form :action="mode === 'create' ? '{{ route('akun.role.store', ['tab' => 'role']) }}' : editUrl"
                        method="POST" class="flex flex-col min-h-0 flex-1">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        {{-- Isi (scroll di sini) --}}
                        <div class="flex-1 min-h-0 overflow-y-auto p-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Nama Hak Akses</label>
                                    <input name="name" x-model="form.name" type="text"
                                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm"
                                        placeholder="Contoh: Admin PBI APBD" required />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Deskripsi</label>
                                    <textarea name="description" x-model="form.description" rows="1"
                                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm resize-none" placeholder="Opsional"></textarea>
                                </div>
                            </div>

                            {{-- ===== Checklist modul (kolom permissions) =====
                                 Daftar diambil dari Role::MODULES dan dikelompokkan otomatis
                                 berdasarkan prefix label ("PFM - ...", "Rehabsos - ..." dst).
                                 Modul baru di Role::MODULES otomatis muncul tanpa ubah view ini. --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium">Hak Akses Menu</label>
                                    <div x-show="!isSuperAdminEdit" class="flex items-center gap-3 text-xs">
                                        <span class="text-on-surface-variant">
                                            <span x-text="form.permissions.length"></span> dipilih
                                        </span>
                                        <button type="button" @click="form.permissions = []"
                                            class="text-red-600 hover:underline">Kosongkan</button>
                                    </div>
                                </div>

                                <template x-if="isSuperAdminEdit">
                                    <p
                                        class="text-xs text-on-surface-variant italic bg-surface-container rounded-lg px-3 py-2">
                                        Super Admin otomatis memiliki akses ke seluruh menu dan tidak bisa diubah.
                                    </p>
                                </template>

                                <div x-show="!isSuperAdminEdit" class="space-y-3">
                                    @foreach ($moduleGroups as $groupName => $items)
                                        <div class="rounded-lg border border-outline-variant/40 overflow-hidden">
                                            <div
                                                class="flex items-center justify-between px-3 py-2 bg-surface-container-low border-b border-outline-variant/40">
                                                <span
                                                    class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $groupName }}</span>
                                                <button type="button"
                                                    @click="toggleGroup(@js(array_map('strval', array_keys($items))))"
                                                    class="text-xs font-medium text-primary hover:underline"
                                                    x-text="groupAll(@js(array_map('strval', array_keys($items)))) ? 'Batalkan semua' : 'Pilih semua'"></button>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-0.5 p-2">
                                                @foreach ($items as $key => $itemLabel)
                                                    <label
                                                        class="flex items-start gap-2.5 px-2 py-1.5 rounded-md text-sm text-on-surface leading-snug cursor-pointer hover:bg-surface-container-low">
                                                        <input type="checkbox" name="permissions[]"
                                                            value="{{ $key }}" x-model="form.permissions"
                                                            class="mt-0.5 shrink-0 rounded border-outline-variant/50 text-primary focus:ring-primary" />
                                                        <span>{{ $itemLabel }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if (\App\Models\Role::MODULES === [])
                                    <p class="text-xs text-on-surface-variant italic">Belum ada menu yang bisa dipilih.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Footer (tetap di bawah, tombol Simpan selalu kelihatan) --}}
                        <div class="shrink-0 flex justify-end gap-2 px-5 py-3 border-t border-outline-variant/40 bg-surface-container-lowest">
                            <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function userManager() {
                const kosong = () => ({
                    username: '',
                    name: '',
                    email: '',
                    role_id: '',
                    kecamatan: '',
                    kelurahan: '',
                    nip: '',
                    pangkat: '',
                    jabatan: ''
                });

                return {
                    modalOpen: false,
                    mode: 'create',
                    editUrl: '',
                    roleSlugs: @js($roles->pluck('slug', 'id')),
                    form: kosong(),
                    get isKelurahan() {
                        return String(this.roleSlugs[this.form.role_id] ?? '').toLowerCase().includes('kelurahan');
                    },
                    openCreate() {
                        this.mode = 'create';
                        this.form = kosong();
                        this.modalOpen = true;
                    },
                    async openEdit(userId) {
                        this.mode = 'edit';
                        this.editUrl = `{{ url('kelola-role-user') }}/${userId}?tab=pengguna`;
                        try {
                            const res = await fetch(`{{ url('kelola-role-user') }}/${userId}/edit`, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const data = await res.json();
                            this.form = {
                                username: data.username,
                                name: data.name ?? '',
                                email: data.email,
                                role_id: data.role_id ?? '',
                                kecamatan: data.kecamatan ?? '',
                                kelurahan: data.kelurahan ?? '',
                                nip: data.nip ?? '',
                                pangkat: data.pangkat ?? '',
                                jabatan: data.jabatan ?? '',
                            };
                        } catch (e) {
                            console.error('Gagal memuat data user', e);
                        }
                        this.modalOpen = true;
                    },
                }
            }

            function roleManager() {
                return {
                    modalOpen: false,
                    mode: 'create',
                    editUrl: '',
                    // true kalau role yang sedang dibuka di modal edit adalah Super Admin
                    // (checklist modul disembunyikan karena tidak berlaku untuknya).
                    isSuperAdminEdit: false,
                    form: {
                        name: '',
                        description: '',
                        permissions: [],
                    },
                    // true kalau semua modul di grup ini sudah tercentang
                    groupAll(keys) {
                        return keys.length > 0 && keys.every(k => this.form.permissions.includes(k));
                    },
                    // centang / batalkan semua modul dalam satu grup
                    toggleGroup(keys) {
                        if (this.groupAll(keys)) {
                            this.form.permissions = this.form.permissions.filter(p => !keys.includes(p));
                        } else {
                            this.form.permissions = [...new Set([...this.form.permissions, ...keys])];
                        }
                    },
                    openCreate() {
                        this.mode = 'create';
                        this.isSuperAdminEdit = false;
                        this.form = {
                            name: '',
                            description: '',
                            permissions: [],
                        };
                        this.modalOpen = true;
                    },
                    async openEdit(roleSlug) {
                        this.mode = 'edit';
                        this.editUrl = `{{ url('kelola-role-user/role') }}/${roleSlug}?tab=role`;
                        this.isSuperAdminEdit = false;
                        try {
                            const res = await fetch(`{{ url('kelola-role-user/role') }}/${roleSlug}/edit`, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const data = await res.json();
                            const normalizedSlug = String(data.slug ?? '').toLowerCase().replace(/[-\s]/g, '_');
                            this.isSuperAdminEdit = normalizedSlug === 'superadmin' || normalizedSlug === 'super_admin';
                            this.form = {
                                name: data.name,
                                description: data.description ?? '',
                                permissions: (data.permissions ?? []).map(String),
                            };
                        } catch (e) {
                            console.error('Gagal memuat data role', e);
                        }
                        this.modalOpen = true;
                    },
                }
            }

            const alertBox = document.getElementById('alert-success');
            if (alertBox) {
                setTimeout(() => {
                    alertBox.style.opacity = '0';
                    setTimeout(() => alertBox.remove(), 500);
                }, 3000);
            }

            const alertBoxRole = document.getElementById('alert-success-role');
            if (alertBoxRole) {
                setTimeout(() => {
                    alertBoxRole.style.opacity = '0';
                    setTimeout(() => alertBoxRole.remove(), 500);
                }, 3000);
            }
        </script>
    @endpush
@endsection