@extends('layouts.admin')

@section('page_title', 'Ngopi Pagi')

@section('content')
@php
    $Kon = \App\Models\NgopiPagiKonselor::class;
    $edit = (bool) $konselor;
    $inp = 'w-full rounded-xl border border-outline-variant/60 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
    $lbl = 'block text-xs font-semibold text-on-surface-variant mb-1';
    $pilihSp = collect(old('spesialisasi_ids', $edit ? $konselor->spesialisasis->pluck('id')->all() : []))->map(fn ($v) => (int) $v)->all();
@endphp
<div class="space-y-4">
    @include('admin.ngopi-pagi._nav', ['aktif' => 'konselor'])

    <a href="{{ route('ngopi-pagi.admin.konselor.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:underline">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>Kembali ke Data Konselor
    </a>

    @if ($errors->any())
        <div class="rounded-xl bg-error-container text-on-error-container text-xs px-4 py-3 space-y-0.5">
            @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" class="grid xl:grid-cols-3 gap-4 items-start"
        action="{{ $edit ? route('ngopi-pagi.admin.konselor.update', $konselor) : route('ngopi-pagi.admin.konselor.store') }}"
        x-data="{ prev: @js($edit ? $konselor->foto_url : null), mode: @js(old('akun_mode', $users->isNotEmpty() ? 'ada' : 'baru')), hapus: false }">
        @csrf
        @if ($edit) @method('PUT') @endif

        {{-- Foto --}}
        <section class="rounded-2xl bg-white border border-outline-variant/40 p-4 space-y-3">
            <h3 class="text-sm font-extrabold">Foto</h3>
            <div class="flex items-center gap-4">
                <div class="w-24 h-24 rounded-2xl overflow-hidden bg-brand-soft flex items-center justify-center shrink-0">
                    <img x-show="prev && !hapus" :src="prev" alt="Pratinjau foto" class="w-full h-full object-cover">
                    <span x-show="!prev || hapus" class="material-symbols-outlined text-[40px] text-brand/50">person</span>
                </div>
                <div class="text-xs text-on-surface-variant space-y-2">
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-brand file:text-white file:px-3 file:py-1.5 file:text-xs file:font-semibold"
                        @change="if ($event.target.files[0]) { prev = URL.createObjectURL($event.target.files[0]); hapus = false }">
                    <p>JPG/PNG/WebP, maks. 2 MB. Rasio persegi paling baik. Tanpa foto, tampil avatar inisial.</p>
                    @if ($edit && $konselor->foto)
                        <label class="flex items-center gap-2"><input type="checkbox" name="hapus_foto" value="1" x-model="hapus" class="rounded border-outline-variant text-brand focus:ring-brand">Hapus foto saat ini</label>
                    @endif
                </div>
            </div>

            <h3 class="pt-3 text-sm font-extrabold border-t border-outline-variant/30">Tampil di halaman publik</h3>
            <div>
                <label class="{{ $lbl }}">Status</label>
                <select name="status" class="{{ $inp }}">
                    @foreach ($Kon::STATUS as $sk => $sv)<option value="{{ $sk }}" @selected(old('status', $konselor->status ?? 'tersedia') === $sk)>{{ $sv['label'] }}</option>@endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div><label class="{{ $lbl }}">Urutan</label><input type="number" min="0" name="urutan" value="{{ old('urutan', $konselor->urutan ?? 0) }}" class="{{ $inp }}"></div>
                <label class="flex items-end gap-2 pb-2 text-sm"><input type="checkbox" name="aktif" value="1" class="rounded border-outline-variant text-brand focus:ring-brand" @checked(old('aktif', $konselor->aktif ?? true))>Aktif (tampil)</label>
            </div>
        </section>

        {{-- Profil --}}
        <section class="rounded-2xl bg-white border border-outline-variant/40 p-4 space-y-3 xl:col-span-2">
            <h3 class="text-sm font-extrabold">Profil konselor</h3>
            <div class="grid sm:grid-cols-2 gap-3">
                <div><label class="{{ $lbl }}">Nama tampilan (dengan gelar)</label><input name="nama_tampilan" value="{{ old('nama_tampilan', $konselor->nama_tampilan ?? '') }}" placeholder="Rina Marlina, M.Psi." class="{{ $inp }}" required></div>
                <div><label class="{{ $lbl }}">Jabatan / profesi</label><input name="headline" value="{{ old('headline', $konselor->headline ?? '') }}" placeholder="Psikolog Keluarga" class="{{ $inp }}" required></div>
            </div>
            <div class="grid sm:grid-cols-3 gap-3">
                <div><label class="{{ $lbl }}">Pengalaman (tahun)</label><input type="number" min="0" max="60" name="pengalaman_tahun" value="{{ old('pengalaman_tahun', $konselor->pengalaman_tahun ?? '') }}" class="{{ $inp }}"></div>
            </div>
            <div><label class="{{ $lbl }}">Deskripsi singkat</label><textarea name="bio" rows="4" maxlength="1500" placeholder="Latar belakang, pendekatan, dan jenis kasus yang biasa ditangani." class="{{ $inp }}">{{ old('bio', $konselor->bio ?? '') }}</textarea></div>

            <div>
                <label class="{{ $lbl }}">Spesialisasi (boleh lebih dari satu)</label>
                <div class="grid sm:grid-cols-2 gap-2">
                    @foreach ($spesialisasis as $sp)
                        @php $w = $sp->warna(); @endphp
                        <label class="cursor-pointer">
                            <input type="checkbox" name="spesialisasi_ids[]" value="{{ $sp->id }}" class="peer sr-only" @checked(in_array($sp->id, $pilihSp, true))>
                            <div class="flex items-center gap-2.5 rounded-xl border-2 border-outline-variant/50 px-3 py-2 peer-checked:border-brand peer-checked:bg-brand-soft peer-focus-visible:ring-2 peer-focus-visible:ring-brand/40">
                                <span class="w-8 h-8 rounded-full {{ $w['bg'] }} flex items-center justify-center"><span class="material-symbols-outlined text-[18px] {{ $w['fg'] }}" style="font-variation-settings:'FILL' 1">{{ $sp->ikon }}</span></span>
                                <span class="text-sm font-semibold">{{ $sp->nama }}@unless ($sp->aktif) <em class="text-[10px] text-on-surface-variant">(nonaktif)</em>@endunless</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @if ($spesialisasis->isEmpty())<p class="text-xs text-error mt-1">Belum ada spesialisasi. Jalankan seeder atau tambahkan di tab Spesialisasi.</p>@endif
            </div>
        </section>

        {{-- Akun login --}}
        <section class="rounded-2xl bg-white border border-outline-variant/40 p-4 space-y-3 xl:col-span-3">
            <h3 class="text-sm font-extrabold">Akun login konselor</h3>
            @if ($edit)
                <p class="text-sm">Akun: <b>{{ $konselor->user->username ?? '-' }}</b> · {{ $konselor->user->email ?? '' }} · peran {{ $konselor->user->role->name ?? '-' }}.
                    <span class="text-xs text-on-surface-variant block mt-1">Ubah kata sandi atau peran lewat menu Kelola Akses.</span></p>
            @else
                <div class="flex gap-4 text-sm">
                    <label class="flex items-center gap-2"><input type="radio" name="akun_mode" value="ada" x-model="mode" class="text-brand focus:ring-brand">Pakai akun yang sudah ada</label>
                    <label class="flex items-center gap-2"><input type="radio" name="akun_mode" value="baru" x-model="mode" class="text-brand focus:ring-brand">Buat akun baru</label>
                </div>

                <div x-show="mode === 'ada'" x-cloak class="max-w-md">
                    <label class="{{ $lbl }}">Akun staf</label>
                    <select name="user_id" class="{{ $inp }}">
                        <option value="">— pilih akun —</option>
                        @foreach ($users as $u)<option value="{{ $u->id }}" @selected((int) old('user_id') === $u->id)>{{ $u->name }} ({{ $u->username }}) · {{ $u->role->name ?? 'tanpa peran' }}</option>@endforeach
                    </select>
                    <p class="text-[11px] text-on-surface-variant mt-1">Hanya akun staf yang belum menjadi konselor.</p>
                </div>

                <div x-show="mode === 'baru'" x-cloak class="grid sm:grid-cols-2 xl:grid-cols-4 gap-3">
                    <div><label class="{{ $lbl }}">Username</label><input name="akun_username" value="{{ old('akun_username') }}" autocomplete="off" class="{{ $inp }}"></div>
                    <div><label class="{{ $lbl }}">Email</label><input type="email" name="akun_email" value="{{ old('akun_email') }}" autocomplete="off" class="{{ $inp }}"></div>
                    <div><label class="{{ $lbl }}">Kata sandi (min. 8)</label><input type="password" name="akun_password" autocomplete="new-password" class="{{ $inp }}"></div>
                    <div><label class="{{ $lbl }}">Peran</label>
                        <select name="akun_role_id" class="{{ $inp }}">
                            <option value="">— pilih peran —</option>
                            @foreach ($roles as $r)<option value="{{ $r->id }}" @selected((int) old('akun_role_id') === $r->id)>{{ $r->name }}@if ($r->hasModule('dayasos-ngopi-pagi')) ✓ akses Ngopi Pagi @endif</option>@endforeach
                        </select>
                        <p class="text-[11px] text-on-surface-variant mt-1">Pilih peran bertanda ✓ agar konselor bisa membuka chat.</p>
                    </div>
                </div>
            @endif
        </section>

        <div class="xl:col-span-3 flex justify-end gap-2">
            <a href="{{ route('ngopi-pagi.admin.konselor.index') }}" class="px-4 py-2.5 rounded-xl border border-outline-variant/60 text-sm font-semibold hover:bg-surface-container">Batal</a>
            <button class="px-5 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-primary">{{ $edit ? 'Simpan perubahan' : 'Tambah konselor' }}</button>
        </div>
    </form>
</div>
@endsection
