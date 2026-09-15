@extends('layouts.admin')

@section('title', 'Monitoring DTSEN - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'DTSEN - Monitoring')

@section('content')
    <div class="space-y-5">

        @if (session('success'))
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs bg-green-50 border border-green-200 text-green-700">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('dtsen.monitoring') }}"
            class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 flex flex-wrap items-end gap-3">
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
                <label class="block text-xs text-on-surface-variant mb-1">Tahap Saat Ini</label>
                <select name="status" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs">
                    <option value="">Semua Tahap</option>
                    @foreach ($statusLabels as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK..."
                    class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
            </div>
            <button type="submit"
                class="px-4 py-1.5 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-medium">
                Terapkan
            </button>
            @if (request()->hasAny(['tanggal_awal', 'tanggal_akhir', 'search', 'status']))
                <a href="{{ route('dtsen.monitoring') }}" class="px-3 py-1.5 rounded-lg text-xs text-on-surface-variant hover:text-primary">
                    Reset
                </a>
            @endif

            <div class="flex items-center gap-2 text-xs text-on-surface-variant ml-auto">
                Tampilkan
                <select name="display" onchange="this.form.submit()" form="monitoring-dtsen-tampilkan-form"
                    class="rounded-lg border border-outline-variant/50 px-2 py-1 text-xs">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected((int) request('display', 10) === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                data
            </div>
        </form>

        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-on-surface">Permohonan Berjalan</h2>
            <span class="text-xs text-on-surface-variant">{{ $monitoringList->total() }} permohonan</span>
        </div>

        @forelse ($monitoringList as $item)
            @php
                $isFinalItem = in_array($item->status, [
                    \App\Models\DtsenPermohonan::STATUS_SELESAI,
                    \App\Models\DtsenPermohonan::STATUS_DITOLAK,
                ], true);
                $isRejected = $item->status === \App\Models\DtsenPermohonan::STATUS_DITOLAK;
                $pendingLabel = $statusLabels[$item->status] ?? $item->status;
                $pendingRole = $item->currentRole?->name;
                $logCount = $item->logs->count();
            @endphp

            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
                <div class="flex flex-wrap gap-x-8 gap-y-1 px-4 py-2.5 border-b border-outline-variant/40 text-xs">
                    <span><span class="text-on-surface-variant">NIK:</span> <span class="font-medium text-primary">{{ $item->nik }}</span></span>
                    <span><span class="text-on-surface-variant">Nama Pemohon:</span> <span class="font-medium">{{ $item->nama_pemohon }}</span></span>
                    <span><span class="text-on-surface-variant">Jenis Proses Pelayanan:</span> <span class="font-medium">{{ $item->alasan_cetak }}</span></span>
                    <a href="{{ route('dtsen.show', $item) }}" class="ml-auto inline-flex items-center gap-1 text-primary hover:underline font-medium">
                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                        Lihat Detail
                    </a>
                </div>

                <table class="w-full text-[11px] table-fixed">
                    <colgroup>
                        <col style="width: 14%">
                        <col style="width: 26%">
                        <col style="width: 16%">
                        <col style="width: 32%">
                        <col style="width: 12%">
                    </colgroup>
                    <thead class="bg-surface-container text-on-surface-variant uppercase">
                        <tr>
                            <th class="text-left px-3 py-2">Tanggal</th>
                            <th class="text-left px-3 py-2">Proses</th>
                            <th class="text-left px-3 py-2">User</th>
                            <th class="text-left px-3 py-2">Catatan</th>
                            <th class="text-center px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (! $isFinalItem)
                            <tr class="bg-warning-container text-on-warning-container">
                                <td class="px-3 py-2">-</td>
                                <td class="px-3 py-2 font-medium">
                                    {{ $pendingLabel }}{{ $pendingRole ? ' (' . strtolower($pendingRole) . ')' : '' }}
                                </td>
                                <td class="px-3 py-2">-</td>
                                <td class="px-3 py-2">-</td>
                                <td class="px-3 py-2 text-center font-semibold">Proses</td>
                            </tr>
                        @endif

                        @forelse ($item->logs as $i => $log)
                            <tr class="{{ $isRejected ? 'bg-error-container text-on-error-container' : 'bg-success-container text-on-success-container' }}">
                                <td class="px-3 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->tanggal_proses)->format('d-m-Y H:i') }}</td>
                                <td class="px-3 py-2 truncate" title="{{ $log->taskname }} ({{ $log->rolename }})">
                                    {{ $log->taskname }} ({{ strtolower($log->rolename) }})
                                </td>
                                <td class="px-3 py-2 truncate" title="{{ $log->username }}">{{ $log->username }}</td>
                                <td class="px-3 py-2 truncate" title="{{ $log->catatan }}">{{ $log->catatan ?: '-' }}</td>
                                @if ($i === 0)
                                    <td class="px-3 py-2 text-center font-semibold" rowspan="{{ $logCount }}">
                                        {{ $isRejected ? 'Ditolak' : 'Selesai' }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            @if ($isFinalItem)
                                <tr>
                                    <td colspan="5" class="px-3 py-3 text-center text-on-surface-variant">Belum ada riwayat proses.</td>
                                </tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl px-4 py-10 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[28px] block mb-2 opacity-40">inbox</span>
                Tidak ada permohonan berjalan.
            </div>
        @endforelse

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl px-4 py-3">
            {{ $monitoringList->links() }}
        </div>

        <form id="monitoring-dtsen-tampilkan-form" method="GET" action="{{ route('dtsen.monitoring') }}" class="hidden">
            <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
            <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
        </form>
    </div>
@endsection
