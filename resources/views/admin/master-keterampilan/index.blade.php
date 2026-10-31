@extends('layouts.admin')

@section('title', 'Master Keterampilan Khusus')
@section('page_title', 'Master Keterampilan Khusus')

@php $in = 'w-full px-3 py-1.5 rounded-lg border border-outline-variant/60 bg-white text-sm'; @endphp

@section('content')
<div class="w-full space-y-4">
    @if ($errors->any() || $errors->hasBag('tambah'))
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <ul class="list-disc list-inside">
                @foreach (array_unique(array_merge($errors->all(), $errors->getBag('tambah')->all())) as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('master.keterampilan.index') }}" class="flex flex-wrap items-center gap-2">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant">search</span>
            <input type="search" name="q" value="{{ $q }}" placeholder="Cari keterampilan..."
                   class="w-64 pl-9 pr-3 py-2 rounded-lg border border-outline-variant/60 bg-white text-sm">
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">Cari</button>
        @if (filled($q))
            <a href="{{ route('master.keterampilan.index') }}" class="px-3 py-2 rounded-lg text-sm border border-outline-variant/50 hover:bg-surface-container-low">Reset</a>
        @endif
    </form>

    <form method="POST" action="{{ route('master.keterampilan.store') }}" class="flex gap-2">
        @csrf
        <input name="nama" value="{{ old('nama') }}" placeholder="Nama keterampilan baru" class="{{ $in }}">
        <label class="inline-flex items-center gap-1 text-xs whitespace-nowrap" title="Eksklusif: tidak boleh digabung dengan pilihan lain (mis. Belum ada)"><input type="checkbox" name="eksklusif" value="1" class="rounded"> Eksklusif</label>
        <button class="px-4 py-1.5 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold whitespace-nowrap">Tambah</button>
    </form>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-surface-container-low text-xs text-on-surface-variant">
                <tr>
                    <th class="px-3 py-3 text-left w-20">Urut</th>
                    <th class="px-3 py-3 text-left">Keterampilan</th>
                    <th class="px-3 py-3 text-center">Eksklusif</th>
                    <th class="px-3 py-3 text-center">Aktif</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $it)
                    <tr class="border-t border-outline-variant/30">
                        <td class="px-3 py-2">
                            {{-- Form simpan kosong; input & tombol Simpan menempel lewat atribut form="" (hindari form bersarang) --}}
                            <form id="u-{{ $it->id }}" method="POST" action="{{ route('master.keterampilan.update', $it) }}">@csrf @method('PUT')</form>
                            <input form="u-{{ $it->id }}" type="number" min="1" name="urutan" value="{{ $it->urutan }}" class="{{ $in }}">
                        </td>
                        <td class="px-3 py-2"><input form="u-{{ $it->id }}" name="nama" value="{{ $it->nama }}" class="{{ $in }}"></td>
                        <td class="px-3 py-2 text-center"><input form="u-{{ $it->id }}" type="checkbox" name="eksklusif" value="1" class="rounded" @checked($it->eksklusif)></td>
                        <td class="px-3 py-2 text-center"><input form="u-{{ $it->id }}" type="checkbox" name="aktif" value="1" class="rounded" @checked($it->aktif)></td>
                        <td class="px-3 py-2">
                            <div class="flex justify-end gap-2">
                                <button form="u-{{ $it->id }}" class="px-3 py-1 rounded-lg text-xs border border-outline-variant/50 hover:bg-surface-container-low">Simpan</button>
                                <form method="POST" action="{{ route('master.keterampilan.destroy', $it) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" data-confirm="Hapus keterampilan &quot;{{ $it->nama }}&quot;?" data-confirm-variant="error" class="px-3 py-1 rounded-lg text-xs text-error border border-error/40 hover:bg-error-container">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-on-surface-variant">{{ filled($q) ? 'Tidak ada data yang cocok.' : 'Belum ada data.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-on-surface-variant">
                Menampilkan {{ $items->firstItem() }}–{{ $items->lastItem() }} dari {{ $items->total() }} keterampilan
            </p>
            <div>{{ $items->links() }}</div>
        </div>
    @endif
</div>
@endsection
