@extends('layouts.admin')

@section('title', 'Ajuan Kartu KKS - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Kartu KKS — Ajuan')

@section('content')
<div class="space-y-5">

    @if (session('success'))
        <div id="alert-success" class="flex items-center gap-2 px-4 py-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-700">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2 px-4 py-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700">
            <span class="material-symbols-outlined text-[18px]">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
            <div class="relative">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant absolute left-3 top-1/2 -translate-y-1/2">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, Alamat..."
                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-outline-variant/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
            </div>
        </div>
        <div class="w-56">
            <label class="block text-xs text-on-surface-variant mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-sm">
                <option value="">Semua Status</option>
                @foreach ($statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary hover:bg-secondary text-on-primary text-sm font-medium">
            Terapkan
        </button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('kartu-kks.ajuan') }}" class="px-4 py-2 rounded-lg text-sm text-on-surface-variant hover:text-primary">
                Reset
            </a>
        @endif
    </form>

    {{-- Tabel ajuan --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-on-surface">Daftar Ajuan Menunggu Diproses</h2>
            <span class="text-xs text-on-surface-variant">{{ $ajuanList->total() }} permohonan</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Tgl Insert</th>
                    <th class="text-left px-4 py-3">NIK</th>
                    <th class="text-left px-4 py-3">Nama</th>
                    <th class="text-left px-4 py-3">Alamat</th>
                    <th class="text-left px-4 py-3">Tahap Saat Ini</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
                @forelse ($ajuanList as $item)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $item->tanggal_insert->format('d-m-Y') }}</td>
                        <td class="px-4 py-3 font-medium text-primary">{{ $item->nik }}</td>
                        <td class="px-4 py-3">{{ $item->nama_pemohon }}</td>
                        <td class="px-4 py-3 text-on-surface-variant max-w-xs">{{ $item->alamat }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-secondary-fixed text-on-secondary-container">
                                {{ $statusLabels[$item->status] ?? $item->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('kartu-kks.show', $item) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-xs font-medium transition-colors">
                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[32px] block mb-2 opacity-40">inbox</span>
                            Tidak ada ajuan yang menunggu diproses.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-outline-variant/40">
            {{ $ajuanList->links() }}
        </div>
    </div>
</div>

<script>
    const alertBox = document.getElementById('alert-success');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500);
        }, 3000);
    }
</script>
@endsection
