@extends('layouts.admin')

@section('title', 'Arsip PBI APBN')
@section('page_title', 'PBI APBN — Arsip')

@section('content')
<div class="space-y-5">

    <form method="GET" class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-on-surface-variant mb-1">Tanggal Awal</label>
            <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}"
                class="px-3 py-2 rounded-lg border border-outline-variant/50 text-sm" />
        </div>
        <div>
            <label class="block text-xs text-on-surface-variant mb-1">Tanggal Akhir</label>
            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                class="px-3 py-2 rounded-lg border border-outline-variant/50 text-sm" />
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, No. Registrasi..."
                class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-sm" />
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary hover:bg-secondary text-on-primary text-sm font-medium">
            Terapkan
        </button>
    </form>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">No. Registrasi</th>
                    <th class="text-left px-4 py-3">Kepala Keluarga</th>
                    <th class="text-left px-4 py-3">Alamat</th>
                    <th class="text-left px-4 py-3">Status Akhir</th>
                    <th class="text-left px-4 py-3">Diperbarui</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
                @forelse ($arsip as $item)
                    @php
                        $statusColor = $item->status === 'disetujui'
                            ? 'bg-success-container text-on-success-container'
                            : 'bg-error-container text-on-error-container';
                    @endphp
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-4 py-3 font-medium">{{ $item->no_registrasi }}</td>
                        <td class="px-4 py-3">{{ $item->nama_kepala_keluarga }}</td>
                        <td class="px-4 py-3 text-on-surface-variant">{{ $item->kecamatan }}, {{ $item->desa_kelurahan }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-success-container text-on-success-container">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                {{ $item->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-on-surface-variant">{{ $item->updated_at->format('d-m-Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('pbi-apbn.ajuan.show', $item) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-xs font-medium transition-colors">
                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                Lihat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">Belum ada data arsip.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-outline-variant/40">
            {{ $arsip->links() }}
        </div>
    </div>
</div>
@endsection
