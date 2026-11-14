@extends('layouts.admin')

@section('title', 'Verifikasi Kelurahan PBI APBD')
@section('page_title', 'PBI APBD — Verifikasi Kelurahan')

@section('content')
<div class="space-y-5">

    @if ($wilayahBelumDiatur)
        <div class="px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm">
            Akun Anda belum dikaitkan ke kelurahan/kecamatan, sehingga belum ada ajuan yang tampil. Hubungi Super Admin untuk mengatur wilayah tugas Anda.
        </div>
    @elseif (! $user->role?->isSuperAdmin())
        <div class="text-xs text-on-surface-variant">
            Wilayah tugas: <strong>Kel. {{ $user->kelurahan }}{{ $user->kecamatan ? ', Kec. ' . $user->kecamatan : '' }}</strong>
        </div>
    @endif

    {{-- Tab --}}
    <div class="flex gap-2 border-b border-outline-variant/40">
        <a href="{{ route('pbi-kelurahan.index', ['tab' => 'proses']) }}"
           class="px-4 py-2 text-sm font-medium border-b-2 {{ $tab === 'proses' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-primary' }}">
            Perlu Diverifikasi
        </a>
        <a href="{{ route('pbi-kelurahan.index', ['tab' => 'riwayat']) }}"
           class="px-4 py-2 text-sm font-medium border-b-2 {{ $tab === 'riwayat' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-primary' }}">
            Sudah Diteruskan
        </a>
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
            <a href="{{ route('pbi-kelurahan.index', ['tab' => $tab]) }}" class="px-4 py-2 rounded-lg bg-amber-400 text-white text-xs font-semibold">Reset</a>
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
                        <th class="px-3 py-3 text-left">Data Masyarakat</th>
                        <th class="px-3 py-3 text-left">Status</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($ajuan as $item)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-3 py-3 font-semibold text-primary whitespace-nowrap">{{ $item->no_registrasi }}</td>
                            <td class="px-3 py-3">{{ $item->nama_layanan }}</td>
                            <td class="px-3 py-3 font-medium">{{ $item->pemohonLabel() }}</td>
                            <td class="px-3 py-3 text-on-surface-variant">{{ $item->alamat }}, RT {{ $item->rt ?: '-' }}/RW {{ $item->rw ?: '-' }}</td>
                            <td class="px-3 py-3 whitespace-nowrap">{{ $item->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-3 py-3">
                                @php $lengkap = $totalParameter > 0 && $item->jawabans_count >= $totalParameter; @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-medium {{ $lengkap ? 'bg-success-container text-on-success-container' : 'bg-warning-container text-on-warning-container' }}">
                                    {{ $lengkap ? 'Sudah diisi' : 'Belum lengkap' }}
                                </span>
                                @if ($item->dikembalikan_ke_masyarakat && $item->status === 'kelurahan')
                                    <div class="text-[10px] text-on-warning-container mt-1">Dikembalikan, menunggu perbaikan</div>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-medium bg-secondary-fixed text-on-secondary-container">{{ $item->statusLabel() }}</span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('pbi-kelurahan.show', $item) }}"
                                   title="{{ $item->status === 'kelurahan' ? 'Verifikasi ajuan' : 'Lihat detail' }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary text-on-primary text-[11px] font-semibold shadow-sm hover:bg-secondary transition-colors whitespace-nowrap">
                                    <span class="material-symbols-outlined text-[15px]">{{ $item->status === 'kelurahan' ? 'fact_check' : 'visibility' }}</span>
                                    {{ $item->status === 'kelurahan' ? 'Verifikasi' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-on-surface-variant">Tidak ada data pada tahap ini.</td></tr>
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
