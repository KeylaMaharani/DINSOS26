@extends('layouts.admin')

@section('page_title', 'Ngopi Pagi')

@section('content')
@php
    $K = \App\Models\NgopiPagiKonsultasi::class;
    $inp = 'w-full rounded-xl border border-outline-variant/60 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
@endphp
<div class="space-y-4">
    @include('admin.ngopi-pagi._nav', ['aktif' => 'rekap'])

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach ([['Total layanan', $stat['total'], 'forum', 'text-brand'], ['Sedang berjalan', $stat['aktif'], 'pending_actions', 'text-amber-700'], ['Selesai', $stat['selesai'], 'task_alt', 'text-emerald-700'], ['Butuh segera', $stat['segera'], 'emergency', 'text-rose-600']] as [$lb, $nl, $ic, $wr])
            <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex items-center gap-3">
                <span class="material-symbols-outlined text-[28px] {{ $wr }}">{{ $ic }}</span>
                <div><p class="text-2xl font-extrabold leading-none">{{ $nl }}</p><p class="text-[11px] text-on-surface-variant mt-1">{{ $lb }}</p></div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-3">
        <div class="rounded-2xl bg-white border border-outline-variant/40 p-4">
            <p class="text-xs font-bold mb-2">Per kategori</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($K::KATEGORI as $kk => $kv)
                    <span class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $kv['chip'] }}">{{ $kk }} · {{ $kv['nama'] }}: <b>{{ $perKategori[$kk] ?? 0 }}</b></span>
                @endforeach
                <span class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-surface-container">Belum diklasifikasi: <b>{{ $perKategori[''] ?? 0 }}</b></span>
            </div>
        </div>
        <div class="rounded-2xl bg-white border border-outline-variant/40 p-4">
            <p class="text-xs font-bold mb-2">Per jenis tindak lanjut</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($K::TINDAK_LANJUT as $tk => $tv)
                    <span class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-brand-soft text-brand">{{ $tv['label'] }}: <b>{{ $perJenis[$tk] ?? 0 }}</b></span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="rounded-2xl bg-white border border-outline-variant/40 p-4 grid sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
        <div class="lg:col-span-2"><label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Cari</label>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Nama, nomor, kontak, kelurahan" class="{{ $inp }}"></div>
        <div><label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Dari tanggal</label>
            <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}" class="{{ $inp }}"></div>
        <div><label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Sampai tanggal</label>
            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="{{ $inp }}"></div>
        <div><label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Tahap</label>
            <select name="tahap" class="{{ $inp }}"><option value="">Semua</option>
                @foreach ($K::TAHAP as $tk => $tv)<option value="{{ $tk }}" @selected(request('tahap') === $tk)>{{ $tv['label'] }}</option>@endforeach
            </select></div>
        <div><label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Kategori</label>
            <select name="kategori" class="{{ $inp }}"><option value="">Semua</option>
                @foreach ($K::KATEGORI as $kk => $kv)<option value="{{ $kk }}" @selected(request('kategori') === $kk)>{{ $kk }} · {{ $kv['nama'] }}</option>@endforeach
            </select></div>
        <div><label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Kecamatan</label>
            <select name="kecamatan" class="{{ $inp }}"><option value="">Semua</option>
                @foreach ($K::KECAMATAN as $kc)<option @selected(request('kecamatan') === $kc)>{{ $kc }}</option>@endforeach
            </select></div>
        <div class="lg:col-span-5 flex flex-wrap gap-2 justify-end">
            <a href="{{ route('ngopi-pagi.admin.rekap') }}" class="px-4 py-2 rounded-xl border border-outline-variant/60 text-sm font-semibold hover:bg-surface-container">Reset</a>
            <button class="px-4 py-2 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-primary">Terapkan</button>
            <a href="{{ route('ngopi-pagi.admin.rekap.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border-2 border-brand text-brand text-sm font-semibold hover:bg-brand-soft">
                <span class="material-symbols-outlined text-[18px]">download</span>Unduh CSV
            </a>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="rounded-2xl bg-white border border-outline-variant/40 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-surface-container-low text-[11px] uppercase tracking-wide text-on-surface-variant">
                <tr>
                    <th class="text-left px-4 py-3">No. konsultasi</th>
                    <th class="text-left px-4 py-3">Masuk</th>
                    <th class="text-left px-4 py-3">Nama</th>
                    <th class="text-left px-4 py-3">Wilayah</th>
                    <th class="text-left px-4 py-3">Kategori</th>
                    <th class="text-left px-4 py-3">Tindak lanjut</th>
                    <th class="text-left px-4 py-3">Tahap</th>
                    <th class="text-left px-4 py-3">Konselor</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                @forelse ($items as $r)
                    @php $kat = $r->kategori ? $K::KATEGORI[$r->kategori] : null; @endphp
                    <tr class="hover:bg-brand-soft/40">
                        <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $r->no_konsultasi }}@if ($r->butuh_segera) <span class="ml-1 px-1.5 py-0.5 rounded-full bg-rose-600 text-white text-[9px] font-bold">SEGERA</span>@endif</td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-on-surface-variant">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $r->nama }}</td>
                        <td class="px-4 py-3 text-xs">{{ $r->kelurahan }}, {{ $r->kecamatan }}</td>
                        <td class="px-4 py-3">@if ($kat)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $kat['chip'] }}">{{ $r->kategori }} · {{ $kat['nama'] }}</span>@else <span class="text-xs text-on-surface-variant">-</span>@endif</td>
                        <td class="px-4 py-3 text-xs">{{ $K::TINDAK_LANJUT[$r->jenis_tindak_lanjut]['label'] ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $r->tahap === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $K::TAHAP[$r->tahap]['label'] ?? $r->tahap }}</span></td>
                        <td class="px-4 py-3 text-xs">{{ $r->konselor->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('ngopi-pagi.admin.kasus', $r) }}" class="text-xs font-semibold text-brand hover:underline">Buka</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center text-on-surface-variant">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $items->links() }}</div>
</div>
@endsection
