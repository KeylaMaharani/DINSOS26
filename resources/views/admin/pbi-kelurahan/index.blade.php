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

    <form method="GET" class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 flex flex-wrap items-end gap-3">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, No. KK, No. Registrasi..."
                   class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
        </div>
        <button type="submit" class="px-4 py-1.5 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-medium">Terapkan</button>
        @if (request()->filled('search'))
            <a href="{{ route('pbi-kelurahan.index', ['tab' => $tab]) }}" class="px-4 py-1.5 text-xs text-on-surface-variant hover:text-primary">Reset</a>
        @endif
    </form>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40 flex items-center justify-between">
            <h2 class="text-sm font-semibold">{{ $tab === 'proses' ? 'Menunggu Verifikasi Kelurahan' : 'Sudah Diproses Kelurahan' }}</h2>
            <span class="text-xs text-on-surface-variant">{{ $ajuan->total() }} permohonan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-surface-container text-on-surface-variant uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center">No. Registrasi</th>
                        <th class="px-4 py-3 text-center">Kepala Keluarga</th>
                        <th class="px-4 py-3 text-center">Alamat</th>
                        <th class="px-4 py-3 text-center">Data Masyarakat</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($ajuan as $item)
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-4 py-3 text-center font-medium text-primary whitespace-nowrap">{{ $item->no_registrasi }}</td>
                            <td class="px-4 py-3 text-center">{{ $item->nama_kepala_keluarga }}</td>
                            <td class="px-4 py-3 text-center text-on-surface-variant">{{ $item->alamat }}, RT {{ $item->rt ?: '-' }}/RW {{ $item->rw ?: '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $lengkap = $totalParameter > 0 && $item->jawabans_count >= $totalParameter; @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-medium {{ $lengkap ? 'bg-success-container text-on-success-container' : 'bg-warning-container text-on-warning-container' }}">
                                    {{ $lengkap ? 'Sudah diisi' : 'Belum lengkap' }}
                                </span>
                                @if ($item->dikembalikan_ke_masyarakat && $item->status === 'kelurahan')
                                    <div class="text-[10px] text-on-warning-container mt-1">Dikembalikan, menunggu perbaikan</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-medium bg-secondary-fixed text-on-secondary-container">{{ $item->statusLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('pbi-kelurahan.show', $item) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium transition-colors whitespace-nowrap">
                                    <span class="material-symbols-outlined text-[14px]">{{ $item->status === 'kelurahan' ? 'fact_check' : 'visibility' }}</span>
                                    {{ $item->status === 'kelurahan' ? 'Verifikasi' : 'Lihat' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[32px] block mb-2 opacity-40">inbox</span>Tidak ada data.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-outline-variant/40">{{ $ajuan->links() }}</div>
    </div>
</div>
@endsection
