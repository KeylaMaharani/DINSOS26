@extends('layouts.admin')

@section('title', 'Monitoring Kartu KKS - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Kartu KKS — Monitoring')

@section('content')
    <div class="space-y-5">

        @if (session('success'))
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs bg-green-50 border border-green-200 text-green-700">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <!-- BAGIAN FILTER (TIDAK DIUBAH) -->
        <form method="GET" action="{{ route('kartu-kks.monitoring') }}"
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
                        @continue($key === \App\Models\KartuKksPermohonan::STATUS_SELESAI)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIK"
                    class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
            </div>
            <button type="submit"
                class="px-4 py-1.5 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-medium">
                Terapkan
            </button>
            @if (request()->hasAny(['tanggal_awal', 'tanggal_akhir', 'search', 'status']))
                <a href="{{ route('kartu-kks.monitoring') }}" class="px-3 py-1.5 rounded-lg text-xs text-on-surface-variant hover:text-primary">
                    Reset
                </a>
            @endif

            <div class="flex items-center gap-2 text-xs text-on-surface-variant ml-auto">
                Tampilkan
                <select name="display" onchange="this.form.submit()" form="monitoring-kks-tampilkan-form"
                    class="rounded-lg border border-outline-variant/50 px-2 py-1 text-xs">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected((int) request('display', 10) === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                data
            </div>
        </form>
        <!-- AKHIR BAGIAN FILTER -->

        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-on-surface">Permohonan Berjalan</h2>
            <span class="text-xs text-on-surface-variant">{{ $monitoringList->total() }} permohonan</span>
        </div>

        <!-- STRUKTUR TABEL BARU -->
        <div class="overflow-x-auto bg-surface-container-lowest border border-outline-variant/40 text-on-surface">
            <table class="w-full text-xs text-left align-top">
                <thead class="bg-surface-container/50 border-b border-outline-variant/40 text-on-surface-variant">
                    <tr>
                        <th class="px-3 py-3 w-10 text-center border-r border-outline-variant/40 font-medium">No</th>
                        <th class="px-3 py-3 w-56 border-r border-outline-variant/40 font-medium">NIK<br>Nama Pemohon</th>
                        <th class="px-3 py-3 w-40 border-r border-outline-variant/40 font-medium">Jenis Proses Layanan</th>
                        <th class="p-0 border-r border-outline-variant/40 font-medium align-bottom">
                            <div class="px-3 py-2 border-b border-outline-variant/40">Proses Layanan</div>
                            <table class="w-full text-[11px] bg-white">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 w-1/4 text-left border-r border-outline-variant/40 font-medium">Tanggal</th>
                                        <th class="px-3 py-2 w-1/3 text-left border-r border-outline-variant/40 font-medium">Proses</th>
                                        <th class="px-3 py-2 w-1/6 text-left border-r border-outline-variant/40 font-medium">User</th>
                                        <th class="px-3 py-2 text-left font-medium">Catatan</th>
                                    </tr>
                                </thead>
                            </table>
                        </th>
                        <th class="px-3 py-3 w-24 text-center font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/40">
                    @forelse ($monitoringList as $index => $item)
                        @php
                            $isFinalItem = in_array($item->status, [
                                \App\Models\KartuKksPermohonan::STATUS_SELESAI,
                                \App\Models\KartuKksPermohonan::STATUS_DITOLAK,
                            ], true);
                            $isRejected = $item->status === \App\Models\KartuKksPermohonan::STATUS_DITOLAK;
                            $pendingLabel = $statusLabels[$item->status] ?? $item->status;
                            $pendingRole = $item->currentRole?->name;
                        @endphp
                        <tr>
                            <td class="px-3 py-3 text-center border-r border-outline-variant/40">{{ $monitoringList->firstItem() + $index }}.</td>
                            <td class="px-3 py-3 border-r border-outline-variant/40">
                                <div class="text-on-surface-variant">{{ $item->nik }}</div>
                                <div class="font-medium mt-1">{{ $item->nama_pemohon }}</div>
                            </td>
                            <td class="px-3 py-3 border-r border-outline-variant/40">
                                Kartu KKS - {{ $item->masalah_kartu }}
                            </td>
                            <td class="p-0 border-r border-outline-variant/40">
                                <!-- Tabel Nesting Riwayat Log -->
                                <table class="w-full text-[11px]">
                                    <tbody class="divide-y border-outline-variant/40">
                                        {{-- Baris Status Berjalan Saat Ini --}}
                                        @if (! $isFinalItem)
                                            <tr class="bg-yellow-50 text-yellow-800 border-b border-outline-variant/40">
                                                <td class="px-3 py-2 w-1/4 border-r border-outline-variant/40">-</td>
                                                <td class="px-3 py-2 w-1/3 border-r border-outline-variant/40 font-medium">
                                                    {{ $pendingLabel }}<br><span class="opacity-75">({{ strtolower($pendingRole ?? '') }})</span>
                                                </td>
                                                <td class="px-3 py-2 w-1/6 border-r border-outline-variant/40">-</td>
                                                <td class="px-3 py-2">-</td>
                                            </tr>
                                        @endif

                                        {{-- Baris Riwayat Selesai/Ditolak --}}
                                        @foreach ($item->logs as $log)
                                            <tr class="{{ $isRejected ? 'bg-red-50 text-red-800' : 'bg-green-500 text-white' }} border-b border-outline-variant/40 last:border-b-0">
                                                <td class="px-3 py-2 w-1/4 border-r {{ $isRejected ? 'border-outline-variant/40' : 'border-green-600' }} whitespace-nowrap">
                                                    {{ \Carbon\Carbon::parse($log->tanggal_proses)->format('Y-m-d H:i:s') }}
                                                </td>
                                                <td class="px-3 py-2 w-1/3 border-r {{ $isRejected ? 'border-outline-variant/40' : 'border-green-600' }}">
                                                    {{ $log->taskname }}<br>
                                                    <span class="opacity-80">({{ strtolower($log->rolename) }})</span>
                                                </td>
                                                <td class="px-3 py-2 w-1/6 border-r {{ $isRejected ? 'border-outline-variant/40' : 'border-green-600' }}">{{ $log->username }}</td>
                                                <td class="px-3 py-2">{{ $log->catatan ?: '-' }}</td>
                                            </tr>
                                        @endforeach

                                        @if($item->logs->isEmpty() && $isFinalItem)
                                            <tr>
                                                <td colspan="4" class="px-3 py-3 text-center text-on-surface-variant">Belum ada riwayat proses.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <span class="font-medium {{ $isFinalItem ? ($isRejected ? 'text-red-600' : 'text-green-600') : 'text-yellow-600' }}">
                                    {{ $isFinalItem ? ($isRejected ? 'Ditolak' : 'Selesai') : 'Proses' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-10 text-center text-on-surface-variant bg-surface-container-lowest">
                                <span class="material-symbols-outlined text-[28px] block mb-2 opacity-40">inbox</span>
                                Tidak ada permohonan berjalan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- AKHIR STRUKTUR TABEL BARU -->

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl px-4 py-3 mt-4">
            {{ $monitoringList->links() }}
        </div>

        <form id="monitoring-kks-tampilkan-form" method="GET" action="{{ route('kartu-kks.monitoring') }}" class="hidden">
            <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
            <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
        </form>
    </div>
@endsection
