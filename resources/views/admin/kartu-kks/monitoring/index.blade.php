@extends('layouts.admin')

@section('title', 'Monitoring Kartu KKS - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Kartu KKS - Monitoring')

@section('content')
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
        <h2 class="text-base font-semibold text-on-surface mb-4">Monitoring Proses Layanan</h2>
        <p class="text-xs text-on-surface-variant mb-4">Tab ini hanya untuk memantau progres, tanpa akses ke detail berkas.
        </p>

        <form method="GET" action="{{ route('kartu-kks.monitoring') }}" class="mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium mb-1 text-on-surface-variant">Display</label>
                    <select name="display" class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">
                        @foreach ([10, 25, 50, 100] as $n)
                            <option value="{{ $n }}" @selected((int) request('display', 10) === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-on-surface-variant">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}"
                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-on-surface-variant">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-on-surface-variant">Status</label>
                    <select name="status" class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">
                        <option value="Semua">Semua</option>
                        @foreach ($statusLabels as $key => $label)
                            @continue($key === \App\Models\KartuKksPermohonan::STATUS_SELESAI)
                            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-on-surface-variant">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm"
                        placeholder="NIK / Nama" />
                </div>
            </div>
            <div class="mt-3">
                <button type="submit"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-primary text-on-primary">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    Proses
                </button>
            </div>
        </form>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-on-surface-variant border-b border-outline-variant/40">
                    <th class="py-2 w-12">No</th>
                    <th class="py-2">NIK</th>
                    <th class="py-2">Nama Pemohon</th>
                    <th class="py-2">Jenis Proses Layanan</th>
                    <th class="py-2">Proses Layanan</th>
                    <th class="py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($monitoringList as $item)
                    <tr class="border-b border-outline-variant/20">
                        <td class="py-2">{{ $monitoringList->firstItem() + $loop->index }}</td>
                        <td class="py-2">{{ $item->nik }}</td>
                        <td class="py-2">{{ $item->nama_pemohon }}</td>
                        <td class="py-2">Kartu KKS - {{ $item->masalah_kartu }}</td>
                        <td class="py-2">{{ optional($item->logs->first())->taskname ?? '-' }}</td>
                        <td class="py-2">
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $item->status === 'selesai' ? 'bg-success-container text-on-success-container' : ($item->status === 'ditolak' ? 'bg-error-container text-on-error-container' : 'bg-secondary-fixed text-on-secondary-container') }}">
                                {{ $statusLabels[$item->status] ?? $item->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-on-surface-variant">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">{{ $monitoringList->links() }}</div>
    </div>
@endsection
