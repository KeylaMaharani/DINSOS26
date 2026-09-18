@extends('layouts.admin')

@section('title', 'Kelola Pengguna & Hak Akses - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Kelola Pengguna & Hak Akses')

@section('content')
    @php
        $activeTab = request()->query('tab', 'pengguna');
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

        {{-- ============== TAB PENGGUNA ============== --}}
        <div x-show="tab === 'pengguna'" x-cloak x-data="userManager()"
            class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-on-surface">Daftar Pengguna</h2>
                </div>
                <button @click="openCreate()"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-primary text-on-primary">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tambah Pengguna
                </button>
            </div>

            @if (session('success') && $activeTab === 'pengguna')
                <div id="alert-success"
                    class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-700 transition-opacity duration-500">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    {{ session('success') }}
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
                                    <p class="leading-tight">{{ $user->username }}</p>
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
                                    <button @click="openEdit({{ $user->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-100 text-amber-700 hover:bg-amber-200 transition">
                                        <span class="material-symbols-outlined text-[14px]">edit</span>
                                        Edit
                                    </button>
                                    <form action="{{ route('akun.destroy', $user) }}?tab=pengguna" method="POST"
                                        class="inline" onsubmit="return confirm('Yakin hapus akun ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition">
                                            <span class="material-symbols-outlined text-[14px]">delete</span>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $users->links() }}
            </div>

            {{-- ===== MODAL TAMBAH / EDIT USER ===== --}}
            <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div @click.outside="modalOpen = false" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="bg-surface-container-lowest w-full max-w-lg rounded-xl shadow-lg overflow-hidden">

                    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                        <h3 class="text-base font-semibold text-on-surface"
                            x-text="mode === 'create' ? 'Tambah Pengguna' : 'Edit Pengguna'"></h3>
                        <button @click="modalOpen = false" class="text-on-surface-variant">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <form :action="mode === 'create' ? '{{ route('akun.store', ['tab' => 'pengguna']) }}' : editUrl"
                        method="POST" class="p-5 space-y-4">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div>
                            <label class="block text-sm font-medium mb-1">Username</label>
                            <input name="username" x-model="form.username" type="text"
                                class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" required />
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

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                <span x-text="mode === 'create' ? 'Kata Sandi' : 'Kata Sandi Baru'"></span>
                            </label>
                            <template x-if="mode === 'edit'">
                                <p class="text-xs text-red-600 font-medium mb-1">Kosongkan jika tidak ingin
                                    mengubah kata sandi.</p>
                            </template>
                            <div class="relative" x-data="{ show: false }">
                                <input name="password" :type="show ? 'text' : 'password'"
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

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ============== TAB ROLE & PERMISSION (LANGSUNG DI SINI, TIDAK DI-INCLUDE) ============== --}}
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
                                    <span class="ml-1 text-[10px] uppercase tracking-wide text-on-surface-variant">(bawaan sistem)</span>
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
                    class="bg-surface-container-lowest w-full max-w-md rounded-xl shadow-lg overflow-hidden">

                    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                        <h3 class="text-base font-semibold text-on-surface"
                            x-text="mode === 'create' ? 'Tambah Hak Akses' : 'Edit Hak Akses'"></h3>
                        <button @click="modalOpen = false" class="text-on-surface-variant">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <form :action="mode === 'create' ? '{{ route('akun.role.store', ['tab' => 'role']) }}' : editUrl"
                        method="POST" class="p-5 space-y-4">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Hak Akses</label>
                            <input name="name" x-model="form.name" type="text"
                                class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm"
                                placeholder="Contoh: Admin PBI APBN" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Deskripsi</label>
                            <textarea name="description" x-model="form.description" rows="2"
                                class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" placeholder="Opsional"></textarea>
                        </div>

                        {{-- ===== Checklist modul (kolom permissions) =====
                             Daftar checkbox diambil dari Role::MODULES, jadi kalau ada
                             modul baru ditambahkan di sana, otomatis muncul di sini tanpa
                             perlu ubah view ini lagi. --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Hak Akses Modul</label>

                            <template x-if="isSuperAdminEdit">
                                <p class="text-xs text-on-surface-variant italic bg-surface-container rounded-lg px-3 py-2">
                                    Super Admin otomatis memiliki akses ke seluruh modul dan tidak bisa diubah.
                                </p>
                            </template>

                            <div x-show="!isSuperAdminEdit" class="grid grid-cols-2 gap-x-3 gap-y-2">
                                @foreach (\App\Models\Role::MODULES as $key => $label)
                                    <label class="flex items-center gap-2 text-sm text-on-surface">
                                        <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                            x-model="form.permissions"
                                            class="rounded border-outline-variant/50 text-primary focus:ring-primary" />
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            @if (\App\Models\Role::MODULES === [])
                                <p class="text-xs text-on-surface-variant italic">Belum ada modul yang bisa dipilih.</p>
                            @endif
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
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
                return {
                    modalOpen: false,
                    mode: 'create',
                    editUrl: '',
                    form: {
                        username: '',
                        email: '',
                        role_id: '',
                    },
                    openCreate() {
                        this.mode = 'create';
                        this.form = {
                            username: '',
                            email: '',
                            role_id: ''
                        };
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
                                email: data.email,
                                role_id: data.role_id ?? '',
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
                                permissions: data.permissions ?? [],
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
