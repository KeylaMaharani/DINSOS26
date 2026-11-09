@extends('layouts.admin')

@section('page_title', 'KESAN')

@section('content')
@php
    $edit = (bool) $akun;
    $inp = 'w-full rounded-xl border border-outline-variant/60 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
    $lbl = 'block text-xs font-semibold text-on-surface-variant mb-1';
@endphp
<div class="space-y-4">
    @include('admin.kesan._nav', ['aktif' => 'filantropi'])
    <a href="{{ route('kesan-admin.filantropi.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:underline">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>Kembali ke Data Filantropi</a>

    @if ($errors->any())
        <div class="rounded-xl bg-error-container text-on-error-container text-xs px-4 py-3 space-y-0.5">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <form method="POST" class="max-w-2xl rounded-2xl bg-white border border-outline-variant/40 p-4 space-y-3"
        action="{{ $edit ? route('kesan-admin.filantropi.update', $akun->id) : route('kesan-admin.filantropi.store') }}">
        @csrf
        @if ($edit) @method('PUT') @endif
        <h3 class="text-sm font-extrabold">{{ $edit ? 'Ubah akun Filantropi' : 'Akun Filantropi baru' }}</h3>
        <div><label class="{{ $lbl }}">Nama (lembaga / perorangan)</label><input name="name" value="{{ old('name', $akun->name ?? '') }}" class="{{ $inp }}" required></div>
        <div class="grid sm:grid-cols-2 gap-3">
            <div><label class="{{ $lbl }}">Username</label><input name="username" value="{{ old('username', $akun->username ?? '') }}" autocomplete="off" class="{{ $inp }}" required></div>
            <div><label class="{{ $lbl }}">Email</label><input type="email" name="email" value="{{ old('email', $akun->email ?? '') }}" autocomplete="off" class="{{ $inp }}" required></div>
        </div>
        <div><label class="{{ $lbl }}">Kata sandi {{ $edit ? '(kosongkan bila tidak diubah)' : '(min. 8 karakter)' }}</label>
            <input type="password" name="password" autocomplete="new-password" class="{{ $inp }}" @required(! $edit)></div>
        <div class="flex justify-end gap-2 pt-1">
            <a href="{{ route('kesan-admin.filantropi.index') }}" class="px-4 py-2.5 rounded-xl border border-outline-variant/60 text-sm font-semibold hover:bg-surface-container">Batal</a>
            <button class="px-5 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-primary">{{ $edit ? 'Simpan perubahan' : 'Buat akun' }}</button>
        </div>
    </form>
</div>
@endsection
