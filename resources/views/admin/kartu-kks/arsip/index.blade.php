@extends('layouts.admin')

@section('title', 'Arsip Kartu KKS - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Kartu KKS — Arsip')

@section('content')
<div class="space-y-5">

    @if (session('success'))
        <div id="alert-success" class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs bg-green-50 border border-green-200 text-green-700">
            <span class="material-symbols-outlined text-[16px]">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('kartu-kks.arsip') }}" class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-on-surface-variant mb-1">Tanggal Awal</label>
            <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}"
                class="px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
        </div>
        <div>
            <label class="block text-xs text-on-surface-variant mb-1">Tanggal Akhir</label>
            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                class="px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
        </div>
        <div class="w-48">
            <label class="block text-xs text-on-surface-variant mb-1">Masalah Kartu</label>
            <select name="masalah_kartu" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs">
                <option value="">Semua Masalah</option>
                @foreach ($masalahKartuOptions as $opt)
                    <option value="{{ $opt }}" @selected(request('masalah_kartu') === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, Alamat..."
                class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
        </div>
        <button type="submit" class="px-4 py-1.5 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-medium">
            Terapkan
        </button>
        @if (request()->hasAny(['tanggal_awal', 'tanggal_akhir', 'masalah_kartu', 'search']))
            <a href="{{ route('kartu-kks.arsip') }}" class="px-3 py-1.5 rounded-lg text-xs text-on-surface-variant hover:text-primary">
                Reset
            </a>
        @endif

        <div class="flex items-center gap-2 text-xs text-on-surface-variant ml-auto">
            Tampilkan
            <select name="tampilkan" onchange="this.form.submit()" form="arsip-kks-tampilkan-form"
                class="rounded-lg border border-outline-variant/50 px-2 py-1 text-xs">
                @foreach ([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}" @selected((int) request('tampilkan', 10) === $n)>{{ $n }}</option>
                @endforeach
            </select>
            data
        </div>
    </form>

    {{-- Tabel arsip --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <table class="w-full text-xs table-fixed">
            <colgroup>
                <col style="width: 5%">
                <col style="width: 10%">
                <col style="width: 15%">
                <col style="width: 18%">
                <col style="width: 20%">
                <col style="width: 12%">
                <col style="width: 12%">
                <col style="width: 8%">
            </colgroup>
            <thead class="bg-surface-container text-on-surface-variant uppercase">
                <tr>
                    <th class="text-center px-2 py-2 whitespace-nowrap">No</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">Diperbarui</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">NIK</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">Nama</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">Alamat</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">Masalah KKS</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">Status</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
                @forelse ($arsipList as $item)
                    @php
                        $statusColor = $item->status === 'selesai'
                            ? 'bg-success-container text-on-success-container'
                            : 'bg-error-container text-on-error-container';
                        $statusIcon = $item->status === 'selesai' ? 'check_circle' : 'cancel';
                    @endphp
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-2 py-2 whitespace-nowrap truncate text-center">{{ $arsipList->firstItem() + $loop->index }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-center text-on-surface-variant">{{ $item->updated_at->format('d-m-Y') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap truncate font-medium text-primary text-center" title="{{ $item->nik }}">{{ $item->nik }}</td>
                        <td class="px-2 py-2 whitespace-nowrap truncate text-center" title="{{ $item->nama_pemohon }}">{{ $item->nama_pemohon }}</td>
                        <td class="px-2 py-2 whitespace-nowrap truncate text-on-surface-variant" title="{{ $item->alamat }}">{{ $item->alamat }}</td>
                        <td class="px-2 py-2 whitespace-nowrap truncate text-center" title="{{ $item->masalah_kartu }}">{{ $item->masalah_kartu ?: '-' }}</td>
                        <td class="px-2 py-2 whitespace-nowrap truncate text-center">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[11px] font-medium {{ $statusColor }}">
                                <span class="material-symbols-outlined text-[12px]">{{ $statusIcon }}</span>
                                {{ $statusLabels[$item->status] ?? $item->status }}
                            </span>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-center">
                            <a href="{{ route('kartu-kks.show', $item) }}"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium transition-colors">
                                <span class="material-symbols-outlined text-[14px]">visibility</span>
                                Lihat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[28px] block mb-2 opacity-40">inbox</span>
                            Belum ada data arsip.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-outline-variant/40">
            {{ $arsipList->links() }}
        </div>
    </div>

    {{-- Form tersembunyi untuk fitur Tampilkan data --}}
    <form id="arsip-kks-tampilkan-form" method="GET" action="{{ route('kartu-kks.arsip') }}" class="hidden">
        <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
        <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
        <input type="hidden" name="masalah_kartu" value="{{ request('masalah_kartu') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
    </form>
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
