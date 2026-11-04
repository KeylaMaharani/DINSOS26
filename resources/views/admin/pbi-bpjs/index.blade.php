@extends('layouts.admin')

@section('title', 'Approval PBI APBD - BPJS Kesehatan')
@section('page_title', 'PBI APBD — BPJS Kesehatan')

@section('content')
<div class="space-y-5" x-data="{ sel: [], modal: null, aksiBaris(id, a) { this.sel = [id]; this.modal = a; } }">

    <div class="px-4 py-2.5 rounded-lg bg-surface-container text-xs text-on-surface-variant">
        BPJS adalah tahap terakhir. <strong>Aktivasi</strong> menyelesaikan permohonan (status Disetujui/Teraktivasi), <strong>Tolak</strong> menolak permohonan (catatan wajib).
    </div>

    {{-- Filter + statistik --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 shadow-sm space-y-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-semibold mb-1">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}" class="px-3 py-2 rounded-lg border border-outline-variant/50 text-xs">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="px-3 py-2 rounded-lg border border-outline-variant/50 text-xs">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold mb-1">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}" data-live-search placeholder="Nama, NIK, No. KK, No. Reg..."
                       class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-xs">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Tampilkan</label>
                <select name="tampilkan" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-outline-variant/50 text-xs">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected((int) request('tampilkan', 10) === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-4 py-2 rounded-lg bg-brand text-white text-xs font-semibold">Filter</button>
            <a href="{{ route('pbi-approval.bpjs.index') }}" class="px-4 py-2 rounded-lg bg-amber-400 text-white text-xs font-semibold">Reset</a>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-3xl mx-auto">
            <div class="rounded-lg border border-purple-300 text-center py-3">
                <p class="text-sm text-purple-600">Total Usulan PBI APBD</p>
                <p class="text-3xl font-semibold text-purple-700">{{ number_format($stat['total']) }}</p>
            </div>
            <div class="rounded-lg border border-green-300 text-center py-3">
                <p class="text-sm text-green-600">Usulan di Aktivasi</p>
                <p class="text-3xl font-semibold text-green-700">{{ number_format($stat['aktivasi']) }}</p>
            </div>
            <div class="rounded-lg border border-red-300 text-center py-3">
                <p class="text-sm text-red-600">Usulan di Tolak</p>
                <p class="text-3xl font-semibold text-red-700">{{ number_format($stat['ditolak']) }}</p>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-surface-container text-on-surface-variant">
                    <tr>
                        <th class="px-3 py-3 text-left">NIK</th>
                        <th class="px-3 py-3 text-left">Layanan</th>
                        <th class="px-3 py-3 text-left">Pemohon</th>
                        <th class="px-3 py-3 text-left">Alamat</th>
                        <th class="px-3 py-3 text-left">Tgl Permohonan</th>
                        <th class="px-3 py-3 text-left">Proses Terakhir</th>
                        <th class="px-3 py-3 text-left">Proses Selanjutnya</th>
                        <th class="px-3 py-3 text-left">Catatan</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($ajuan as $item)
                        @php $last = $item->logs->first(); @endphp
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-3 py-3 whitespace-nowrap">
                                <div class="font-semibold text-primary">{{ $item->nik_kepala_keluarga }}</div>
                                <div class="text-[10px] text-on-surface-variant">{{ $item->no_registrasi }}</div>
                            </td>
                            <td class="px-3 py-3">{{ $item->nama_layanan }}</td>
                            <td class="px-3 py-3 font-medium">{{ $item->nama_kepala_keluarga }}</td>
                            <td class="px-3 py-3 text-on-surface-variant">{{ $item->alamat }}, {{ $item->desa_kelurahan }}, {{ $item->kecamatan }}</td>
                            <td class="px-3 py-3 whitespace-nowrap">{{ $item->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-3 py-3">
                                @if ($last)
                                    {{ $last->task_name }}<div class="font-semibold">{{ $last->role_name }}</div>
                                @else - @endif
                            </td>
                            <td class="px-3 py-3">Aktivasi PBI APBD</td>
                            <td class="px-3 py-3 text-on-surface-variant">{{ $item->catatan_internal ?: '-' }}</td>
                            <td class="px-3 py-3 text-center whitespace-nowrap">
                                <a href="{{ route('pbi-approval.bpjs.show', $item) }}" class="inline-block px-2.5 py-1 rounded bg-sky-600 text-white text-[11px] font-semibold">Detail</a>
                                <button type="button" @click="aksiBaris({{ $item->id }}, 'next')" class="px-2.5 py-1 rounded bg-green-600 text-white text-[11px] font-semibold">Aktivasi</button>
                                <button type="button" @click="aksiBaris({{ $item->id }}, 'reject')" class="px-2.5 py-1 rounded bg-red-600 text-white text-[11px] font-semibold">Tolak</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-10 text-center text-on-surface-variant">Tidak ada usulan yang menunggu aktivasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-outline-variant/40 flex items-center justify-between text-xs">
            <span>Menampilkan {{ $ajuan->firstItem() ?? 0 }} sampai {{ $ajuan->lastItem() ?? 0 }} dari {{ $ajuan->total() }} data</span>
            <div>{{ $ajuan->links() }}</div>
        </div>
    </div>

    {{-- Modal catatan --}}
    <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <form method="POST" action="{{ route('pbi-approval.bpjs.aksi') }}" @click.outside="modal = null"
              class="bg-surface-container-lowest w-full max-w-md rounded-xl shadow-lg p-5 space-y-4">
            @csrf
            <input type="hidden" name="aksi" :value="modal">
            <template x-for="id in sel" :key="id"><input type="hidden" name="ids[]" :value="id"></template>

            <h3 class="text-base font-semibold" x-text="modal === 'next' ? 'Aktivasi PBI APBD' : 'Tolak Usulan'"></h3>
            <div>
                <label class="block text-xs font-semibold mb-1">Catatan <span x-show="modal !== 'next'" class="text-error">*</span></label>
                <textarea name="catatan" rows="3" :required="modal !== 'next'" class="w-full px-3 py-2 rounded-lg border border-outline-variant/60 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" @click="modal = null" class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm text-white"
                        :class="modal === 'next' ? 'bg-green-600' : 'bg-red-600'"
                        x-text="modal === 'next' ? 'Aktivasi' : 'Tolak'"></button>
            </div>
        </form>
    </div>
</div>
@endsection