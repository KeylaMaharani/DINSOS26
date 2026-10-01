@extends('layouts.admin')

@section('title', 'Monitoring PBI APBN')
@section('page_title', 'PBI APBN — Monitoring')

@section('content')
    <div class="space-y-5">

        @if (session('success'))
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs bg-green-50 border border-green-200 text-green-700">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <!-- BAGIAN FILTER (TIDAK DIUBAH) -->
        <form method="GET" action="{{ route('pbi-apbn.monitoring.index') }}"
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
                    @foreach (\App\Models\PbiApbn::STAGES as $key => $stage)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $stage['label'] }}</option>
                    @endforeach
                    <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                    <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-on-surface-variant mb-1">Desil</label>
                <select name="desil" class="px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs">
                    <option value="">Semua Desil</option>
                    @foreach (['Desil 1', 'Desil 2', 'Desil 3', 'Desil 4'] as $d)
                        <option value="{{ $d }}" @selected(request('desil') === $d)>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="No. Reg / Nama..."
                    class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
            </div>
            <button type="submit"
                class="px-4 py-1.5 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-medium">
                Terapkan
            </button>
            @if (request()->hasAny(['tanggal_awal', 'tanggal_akhir', 'search', 'status', 'desil']))
                <a href="{{ route('pbi-apbn.monitoring.index') }}" class="px-3 py-1.5 rounded-lg text-xs text-on-surface-variant hover:text-primary">
                    Reset
                </a>
            @endif

            <div class="flex items-center gap-2 text-xs text-on-surface-variant ml-auto">
                Tampilkan
                <select name="display" onchange="this.form.submit()" form="monitoring-pbi-tampilkan-form"
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
            <h2 class="text-sm font-semibold text-on-surface">Monitoring Permohonan</h2>
            <span class="text-xs text-on-surface-variant">{{ $data->total() }} permohonan</span>
        </div>

        <!-- STRUKTUR TABEL BARU -->
        <div class="overflow-x-auto bg-surface-container-lowest border border-outline-variant/40 text-on-surface">
            <table class="w-full text-xs text-left align-top">
                <thead class="bg-surface-container/50 border-b border-outline-variant/40 text-on-surface-variant">
                    <tr>
                        <th class="px-3 py-3 w-10 text-center border-r border-outline-variant/40 font-medium">No</th>
                        <th class="px-3 py-3 w-56 border-r border-outline-variant/40 font-medium">No. Registrasi<br>Nama Pemohon</th>
                        <th class="px-3 py-3 w-24 border-r border-outline-variant/40 font-medium text-center">Desil</th>
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
                        <th class="px-3 py-3 w-28 text-center font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/40">
                    @forelse ($data as $index => $item)
                        @php
                            $isFinalItem = in_array($item->status, ['disetujui', 'ditolak'], true);
                            $isRejected = $item->status === 'ditolak';
                            $pendingLabel = $item->currentStageLabel() ?? $item->status;
                        @endphp
                        <tr>
                            <td class="px-3 py-3 text-center border-r border-outline-variant/40">{{ $data->firstItem() + $index }}.</td>
                            <td class="px-3 py-3 border-r border-outline-variant/40">
                                <div class="text-on-surface-variant">{{ $item->no_registrasi }}</div>
                                <div class="font-medium mt-1">{{ $item->nama_kepala_keluarga }}</div>
                            </td>
                            <td class="px-3 py-3 text-center border-r border-outline-variant/40">
                                {{ $item->desil_nasional ?: '-' }}
                            </td>
                            <td class="p-0 border-r border-outline-variant/40">
                                <table class="w-full text-[11px]">
                                    <tbody class="divide-y border-outline-variant/40">
                                        {{-- Row Pending (Tahap Saat Ini) --}}
                                        @if (! $isFinalItem)
                                            <tr class="bg-yellow-50 text-yellow-800 border-b border-outline-variant/40">
                                                <td class="px-3 py-2 w-1/4 border-r border-outline-variant/40">-</td>
                                                <td class="px-3 py-2 w-1/3 border-r border-outline-variant/40 font-medium">
                                                    {{ $pendingLabel }}
                                                </td>
                                                <td class="px-3 py-2 w-1/6 border-r border-outline-variant/40">-</td>
                                                <td class="px-3 py-2">-</td>
                                            </tr>
                                        @endif

                                        {{-- Row Log Riwayat --}}
                                        @foreach ($item->logs as $log)
                                            <tr class="{{ $isRejected ? 'bg-red-50 text-red-800' : 'bg-green-500 text-white' }} border-b border-outline-variant/40 last:border-b-0">
                                                <td class="px-3 py-2 w-1/4 border-r {{ $isRejected ? 'border-outline-variant/40' : 'border-green-600' }} whitespace-nowrap">
                                                    {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                                                </td>
                                                <td class="px-3 py-2 w-1/3 border-r {{ $isRejected ? 'border-outline-variant/40' : 'border-green-600' }}">
                                                    {{ $log->task_name }}<br>
                                                    <span class="opacity-80">({{ strtolower($log->role_name) }})</span>
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
                            <!-- Diubah colspan menjadi 5 karena kolom Aksi dihapus -->
                            <td colspan="5" class="px-3 py-10 text-center text-on-surface-variant bg-surface-container-lowest">
                                <span class="material-symbols-outlined text-[28px] block mb-2 opacity-40">inbox</span>
                                Tidak ada permohonan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- AKHIR STRUKTUR TABEL BARU -->

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl px-4 py-3 mt-4">
            {{ $data->links() }}
        </div>

        <form id="monitoring-pbi-tampilkan-form" method="GET" action="{{ route('pbi-apbn.monitoring.index') }}" class="hidden">
            <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
            <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="desil" value="{{ request('desil') }}">
        </form>
    </div>
@endsection
