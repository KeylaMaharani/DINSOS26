@extends('layouts.admin')

@section('title', 'Arsip PBI APBN')
@section('page_title', 'PBI APBN — Arsip')

@section('content')
<div class="space-y-5">

    {{-- Filter --}}
    <form method="GET" action="{{ route('pbi-apbn.arsip.index') }}" class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 flex flex-wrap items-end gap-3">
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
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, No. Registrasi..."
                class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
        </div>
        <button type="submit" class="px-4 py-1.5 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-medium">
            Terapkan
        </button>
        @if (request()->hasAny(['tanggal_awal', 'tanggal_akhir', 'search']))
            <a href="{{ route('pbi-apbn.arsip.index') }}" class="px-3 py-1.5 rounded-lg text-xs text-on-surface-variant hover:text-primary">
                Reset
            </a>
        @endif

        <div class="flex items-center gap-2 text-xs text-on-surface-variant ml-auto">
            Tampilkan
            <select name="display" onchange="this.form.submit()" form="arsip-pbi-tampilkan-form"
                class="rounded-lg border border-outline-variant/50 px-2 py-1 text-xs">
                @foreach ([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}" @selected((int) request('display', 10) === $n)>{{ $n }}</option>
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
                <col style="width: 18%">
                <col style="width: 22%">
                <col style="width: 25%">
                <col style="width: 12%">
                <col style="width: 8%">
            </colgroup>
            <thead class="bg-surface-container text-on-surface-variant uppercase">
                <tr>
                    <th class="text-center px-2 py-2 whitespace-nowrap">No</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">Diperbarui</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">No. Registrasi</th>
                    <th class="text-left px-2 py-2 whitespace-nowrap">Kepala Keluarga</th>
                    <th class="text-left px-2 py-2 whitespace-nowrap">Alamat</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">Status Akhir</th>
                    <th class="text-center px-2 py-2 whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
                @forelse ($arsip as $item)
                    @php
                        $statusColor = $item->status === 'disetujui'
                            ? 'bg-success-container text-on-success-container'
                            : 'bg-error-container text-on-error-container';
                        $statusIcon = $item->status === 'disetujui' ? 'check_circle' : 'cancel';
                    @endphp
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-2 py-2 whitespace-nowrap truncate text-center">{{ $arsip->firstItem() + $loop->index }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-center text-on-surface-variant">{{ $item->updated_at->format('d-m-Y') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap truncate font-medium text-primary text-center" title="{{ $item->no_registrasi }}">{{ $item->no_registrasi }}</td>
                        <td class="px-2 py-2 whitespace-nowrap truncate" title="{{ $item->nama_kepala_keluarga }}">{{ $item->nama_kepala_keluarga }}</td>
                        <td class="px-2 py-2 whitespace-nowrap truncate text-on-surface-variant" title="{{ $item->kecamatan }}, {{ $item->desa_kelurahan }}">{{ $item->kecamatan }}, {{ $item->desa_kelurahan }}</td>
                        <td class="px-2 py-2 whitespace-nowrap truncate text-center">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[11px] font-medium {{ $statusColor }}">
                                <span class="material-symbols-outlined text-[12px]">{{ $statusIcon }}</span>
                                {{ $item->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-center">
                            <a href="{{ route('pbi-apbn.ajuan.show', $item) }}"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium transition-colors">
                                <span class="material-symbols-outlined text-[14px]">visibility</span>
                                Lihat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[28px] block mb-2 opacity-40">inbox</span>
                            Belum ada data arsip.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-outline-variant/40">
            {{ $arsip->links() }}
        </div>
    </div>

    {{-- Form tersembunyi untuk fitur Tampilkan data --}}
    <form id="arsip-pbi-tampilkan-form" method="GET" action="{{ route('pbi-apbn.arsip.index') }}" class="hidden">
        <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
        <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
    </form>
</div>
@endsection