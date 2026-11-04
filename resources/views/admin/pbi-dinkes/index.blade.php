@extends('layouts.admin')

@section('title', 'Approval PBI APBD - Dinas Kesehatan')
@section('page_title', 'PBI APBD — Dinas Kesehatan')

@section('content')
<div class="space-y-5">

    {{-- Tab: Perlu Diverifikasi / Sudah Diteruskan --}}
    <div class="flex gap-2 border-b border-outline-variant/40">
        @foreach (['proses' => 'Perlu Diverifikasi', 'riwayat' => 'Sudah Diteruskan'] as $key => $label)
            <a href="{{ route("pbi-approval.dinkes.index", ['tab' => $key]) }}"
               class="px-4 py-2 text-sm font-medium border-b-2 {{ $tab === $key ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-primary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Filter + statistik --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 shadow-sm space-y-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div>
                <label class="block text-xs font-semibold mb-1">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-outline-variant/50 text-xs">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-outline-variant/50 text-xs">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold mb-1">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}" autocomplete="off" oninput="clearTimeout(this._t); this._t = setTimeout(() => this.form.submit(), 500)" @if (request()->filled('search')) autofocus onfocus="this.setSelectionRange(this.value.length, this.value.length)" @endif placeholder="Nama, NIK, No. KK, No. Reg..."
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
            {{-- Filter berjalan otomatis (tanggal, tampilkan, dan pencarian live), tombol Filter tidak diperlukan --}}
            <a href="{{ route('pbi-approval.dinkes.index', ['tab' => $tab]) }}" class="px-4 py-2 rounded-lg bg-amber-400 text-white text-xs font-semibold">Reset</a>
        </form>

        {{-- Kartu ringkasan (gaya sama dengan Ground Check) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative overflow-hidden rounded-2xl bg-brand text-white px-5 py-4 shadow-sm">
                <span class="material-symbols-outlined absolute -right-2 -bottom-3 text-[84px] opacity-15">inventory_2</span>
                <p class="text-[11px] uppercase tracking-wider opacity-80">Total Usulan PBI APBD</p>
                <p class="text-3xl font-extrabold mt-0.5">{{ number_format($stat['total'], 0, ',', '.') }}</p>
            </div>
            <div class="relative overflow-hidden rounded-2xl bg-success text-white px-5 py-4 shadow-sm">
                <span class="material-symbols-outlined absolute -right-2 -bottom-3 text-[84px] opacity-15">task_alt</span>
                <p class="text-[11px] uppercase tracking-wider opacity-80">Usulan di Aktivasi</p>
                <p class="text-3xl font-extrabold mt-0.5">{{ number_format($stat['aktivasi'], 0, ',', '.') }}</p>
            </div>
            <div class="relative overflow-hidden rounded-2xl bg-error text-on-error px-5 py-4 shadow-sm">
                <span class="material-symbols-outlined absolute -right-2 -bottom-3 text-[84px] opacity-15">cancel</span>
                <p class="text-[11px] uppercase tracking-wider opacity-80">Usulan di Tolak</p>
                <p class="text-3xl font-extrabold mt-0.5">{{ number_format($stat['ditolak'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-surface-container text-on-surface-variant">
                    <tr>
                        <th class="px-3 py-3 text-left">No Reg</th>
                        <th class="px-3 py-3 text-left">Layanan</th>
                        <th class="px-3 py-3 text-left">Pemohon</th>
                        <th class="px-3 py-3 text-left">Alamat</th>
                        <th class="px-3 py-3 text-left">Tgl Permohonan</th>
                        <th class="px-3 py-3 text-left">Proses Terakhir</th>
                        <th class="px-3 py-3 text-left">{{ $tab === 'riwayat' ? 'Posisi Saat Ini' : 'Proses Selanjutnya' }}</th>
                        @if ($tab === 'riwayat')
                            <th class="px-3 py-3 text-left">Tanda</th>
                        @endif
                        <th class="px-3 py-3 text-left">Catatan</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($ajuan as $item)
                        @php $last = $item->logs->first(); @endphp
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-3 py-3 font-semibold text-primary whitespace-nowrap">{{ $item->no_registrasi }}</td>
                            <td class="px-3 py-3">{{ $item->nama_layanan }}</td>
                            <td class="px-3 py-3 font-medium">{{ $item->nama_kepala_keluarga }}</td>
                            <td class="px-3 py-3 text-on-surface-variant">{{ $item->alamat }}, {{ $item->desa_kelurahan }}, {{ $item->kecamatan }}</td>
                            <td class="px-3 py-3 whitespace-nowrap">{{ $item->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-3 py-3">
                                @if ($last)
                                    {{ $last->task_name }}<div class="font-semibold">{{ $last->role_name }}</div>
                                @else - @endif
                            </td>
                            <td class="px-3 py-3">{{ $tab === 'riwayat' ? $item->statusLabel() : 'BPJS Kesehatan' }}</td>
                            @if ($tab === 'riwayat')
                                <td class="px-3 py-3">@switch($item->tanda_riwayat ?? null)
                                        @case('diteruskan')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-success-container text-on-success-container whitespace-nowrap"><span class="material-symbols-outlined text-[14px]">forward_to_inbox</span>Diteruskan</span>
                                            @break
                                        @case('dikembalikan')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-warning-container text-on-warning-container whitespace-nowrap"><span class="material-symbols-outlined text-[14px]">undo</span>Dikembalikan</span>
                                            @break
                                        @case('ditolak')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-error text-on-error whitespace-nowrap"><span class="material-symbols-outlined text-[14px]">cancel</span>Ditolak</span>
                                            @break
                                        @default
                                            <span class="text-on-surface-variant">-</span>
                                    @endswitch</td>
                            @endif
                            <td class="px-3 py-3 text-on-surface-variant">{{ $item->catatan_internal ?: '-' }}</td>
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('pbi-approval.dinkes.show', $item) }}" title="{{ $tab === 'riwayat' ? 'Lihat detail' : 'Lihat detail & proses' }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary text-on-primary text-[11px] font-semibold shadow-sm hover:bg-secondary transition-colors whitespace-nowrap">
                                    <span class="material-symbols-outlined text-[15px]">visibility</span>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $tab === 'riwayat' ? 10 : 9 }}" class="px-4 py-10 text-center text-on-surface-variant">
                            {{ $tab === 'riwayat' ? 'Belum ada usulan yang diteruskan atau dikembalikan dari tahap ini.' : 'Tidak ada data pada tahap ini.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-outline-variant/40 flex items-center justify-between text-xs">
            <span>Menampilkan {{ $ajuan->firstItem() ?? 0 }} sampai {{ $ajuan->lastItem() ?? 0 }} dari {{ $ajuan->total() }} data</span>
            <div>{{ $ajuan->links() }}</div>
        </div>
    </div>
</div>
@endsection
