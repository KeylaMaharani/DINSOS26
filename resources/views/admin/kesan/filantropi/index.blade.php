@extends('layouts.admin')

@section('page_title', 'KESAN')

@section('content')
@php $role = auth()->user()->role; @endphp
<div class="space-y-4">
    @include('admin.kesan._nav', ['aktif' => 'filantropi'])

    <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex flex-wrap items-center gap-3">
        <span class="w-11 h-11 rounded-xl bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined">volunteer_activism</span></span>
        <div class="min-w-0 flex-1 basis-48">
            <h2 class="text-base font-extrabold">Data Filantropi</h2>
            <p class="text-xs text-on-surface-variant">Akun donatur yang dapat masuk lewat "Masuk sebagai Filantropi".</p></div>
        <form method="GET"><label class="flex items-center gap-2 h-10 w-56 max-w-full rounded-xl bg-surface-container-low px-3">
            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari filantropi…" class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0"></label></form>
        @if ($role?->hasModule('kesan.filantropi.tambah'))
            <a href="{{ route('kesan-admin.filantropi.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-primary">
                <span class="material-symbols-outlined text-[18px]">add</span>Tambah akun</a>
        @endif
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl bg-white border border-outline-variant/40 p-10 text-center text-sm text-on-surface-variant">Belum ada akun Filantropi.</div>
    @else
        <div class="space-y-2">
            @foreach ($items as $u)
                <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-white border border-outline-variant/40 p-3">
                    <span class="w-11 h-11 shrink-0 rounded-full bg-primary text-white font-extrabold flex items-center justify-center">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                    <div class="min-w-0 flex-1 basis-52">
                        <p class="text-sm font-extrabold truncate">{{ $u->name }}</p>
                        <p class="text-xs text-on-surface-variant truncate">{{ $u->username }} · {{ $u->email }}</p></div>
                    <span class="text-xs text-on-surface-variant">{{ $jumlah[$u->id] ?? 0 }} pilihan</span>
                    @if ($role?->hasModule('kesan.filantropi.ubah'))
                        <a href="{{ route('kesan-admin.filantropi.edit', $u->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant/60 text-xs font-semibold hover:bg-surface-container"><span class="material-symbols-outlined text-[16px]">edit</span>Ubah</a>
                    @endif
                    @if ($role?->hasModule('kesan.filantropi.hapus'))
                        <form method="POST" action="{{ route('kesan-admin.filantropi.destroy', $u->id) }}">@csrf @method('DELETE')
                            <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-error/40 text-error text-xs font-semibold hover:bg-error-container/40" data-confirm="Hapus akun {{ $u->name }}?" data-confirm-variant="error" data-confirm-title="Hapus akun"><span class="material-symbols-outlined text-[16px]">delete</span>Hapus</button></form>
                    @endif
                </div>
            @endforeach
        </div>
        <div>{{ $items->links() }}</div>
    @endif
</div>
@endsection
