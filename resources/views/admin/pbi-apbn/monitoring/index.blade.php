@extends('layouts.admin')

@section('title', 'Monitoring PBI APBN')
@section('page_title', 'PBI APBN — Monitoring')

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
        <div>
            <label class="block text-xs text-on-surface-variant mb-1">Desil</label>
            <select name="desil" class="px-3 py-2 rounded-lg border border-outline-variant/50 text-sm">
                <option value="">Semua Desil</option>
                @foreach (['Desil 1', 'Desil 2', 'Desil 3', 'Desil 4'] as $d)
                    <option value="{{ $d }}" @selected(request('desil') === $d)>{{ $d }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-on-surface-variant mb-1">Status Proses</label>
            <select name="status" class="px-3 py-2 rounded-lg border border-outline-variant/50 text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\PbiApbn::STATUS_LABELS as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / No. Registrasi"
                class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-sm" />
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary hover:bg-secondary text-on-primary text-sm font-medium">
            Terapkan
        </button>
         <a href="{{ route('pbi-apbn.log.index') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-surface-container text-on-surface text-sm font-medium hover:bg-surface-container-high transition-colors">
            <span class="material-symbols-outlined text-[18px]">history</span>
            Lihat Semua Log
        </a>
        @if (request()->hasAny(['tanggal_awal','tanggal_akhir','desil','status','search']))
            <a href="{{ route('pbi-apbn.monitoring.index') }}" class="px-4 py-2 rounded-lg text-sm text-on-surface-variant hover:text-primary">
                Reset
            </a>
        @endif
    </form>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-on-surface">Monitoring Seluruh Proses</h2>
            <span class="text-xs text-on-surface-variant">{{ $data->total() }} permohonan</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3 w-10">No</th>
                    <th class="text-left px-4 py-3">No. Registrasi</th>
                    <th class="text-left px-4 py-3">Nama Pemohon</th>
                    <th class="text-left px-4 py-3">Tahap Saat Ini</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
                @forelse ($data as $i => $item)
                    @php
                        $badge = match($item->status) {
                            'disetujui' => ['bg-success-container text-on-success-container', 'check_circle'],
                            'ditolak' => ['bg-error-container text-on-error-container', 'cancel'],
                            default => ['bg-secondary-fixed text-on-secondary-container', 'hourglass_top'],
                        };
                    @endphp
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-4 py-3 text-on-surface-variant">{{ $data->firstItem() + $i }}</td>
                        <td class="px-4 py-3 font-medium text-primary">{{ $item->no_registrasi }}</td>
                        <td class="px-4 py-3">{{ $item->nama_kepala_keluarga }}</td>
                        <td class="px-4 py-3 text-on-surface-variant">{{ $item->currentStageLabel() ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $badge[0] }}">
                                <span class="material-symbols-outlined text-[14px]">{{ $badge[1] }}</span>
                                {{ $item->statusLabel() }}
                            </span>
                        </td>
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
                        <td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[32px] block mb-2 opacity-40">search_off</span>
                            Tidak ada data yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-outline-variant/40">
            {{ $data->links() }}
        </div>
    </div>
</div>
@endsection
