@extends('layouts.admin')

@section('title', 'Master Lapangan Usaha')
@section('page_title', 'Master Lapangan Usaha & Pekerjaan')

@section('content')
<div class="w-full space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('master.lapangan-usaha.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant">search</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari lapangan usaha..."
                       class="w-64 pl-9 pr-3 py-2 rounded-lg border border-outline-variant/60 bg-white text-sm">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">Cari</button>
            @if (filled($q))
                <a href="{{ route('master.lapangan-usaha.index') }}" class="px-3 py-2 rounded-lg text-sm border border-outline-variant/50 hover:bg-surface-container-low">Reset</a>
            @endif
        </form>
        <a href="{{ route('master.lapangan-usaha.create') }}" class="inline-flex items-center gap-1 px-4 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold whitespace-nowrap">
            <span class="material-symbols-outlined text-[18px]">add</span> Tambah
        </a>
    </div>

    <p class="text-sm text-on-surface-variant">Dipakai pada form Ground Check tab C (Ketenagakerjaan). Isi pilihan dropdown tidak diubah; penjelasan dan contoh pekerjaan tampil sebagai panduan bagi pengisi.</p>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-surface-container-low text-xs text-on-surface-variant">
                <tr>
                    <th class="px-4 py-3 text-left w-14">Urut</th>
                    <th class="px-4 py-3 text-left">Lapangan Usaha</th>
                    <th class="px-4 py-3 text-center">Pekerjaan</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $it)
                    <tr class="border-t border-outline-variant/30 hover:bg-surface-container-low/60 transition-colors">
                        <td class="px-4 py-3">{{ $it->urutan }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-2">
                                {{-- Ikon berupa emoji, jadi ditampilkan sebagai teks biasa (bukan Material Symbols) --}}
                                <span class="text-xl leading-none w-6 text-center">{{ $it->ikon ?: '💼' }}</span>
                                <span class="font-medium">{{ $it->nama }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $it->pekerjaans_count }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[11px] {{ $it->aktif ? 'bg-primary-fixed-dim/40 text-primary' : 'bg-surface-container text-on-surface-variant' }}">{{ $it->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('master.lapangan-usaha.edit', $it) }}" class="px-3 py-1 rounded-lg text-xs border border-outline-variant/50 hover:bg-surface-container-low">Edit</a>
                                <form method="POST" action="{{ route('master.lapangan-usaha.destroy', $it) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" data-confirm="Hapus lapangan usaha &quot;{{ $it->nama }}&quot; beserta semua pekerjaannya?" data-confirm-variant="error"
                                            class="px-3 py-1 rounded-lg text-xs text-error border border-error/40 hover:bg-error-container">Hapus</button>
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
                Menampilkan {{ $items->firstItem() }}–{{ $items->lastItem() }} dari {{ $items->total() }} lapangan usaha
            </p>
            <div>{{ $items->links() }}</div>
        </div>
    @endif
</div>
@endsection
