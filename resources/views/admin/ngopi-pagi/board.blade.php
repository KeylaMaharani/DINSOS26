@extends('layouts.admin')

@section('page_title', 'Ngopi Pagi')

@section('content')
@php $K = \App\Models\NgopiPagiKonsultasi::class; @endphp
<div class="space-y-4">

    <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex flex-wrap items-center gap-3">
        <span class="w-11 h-11 rounded-xl bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined">{{ $cfg['icon'] }}</span></span>
        <div class="min-w-0 flex-1 basis-56">
            <h2 class="text-base font-extrabold">{{ $cfg['label'] }} <span class="text-xs font-semibold text-on-surface-variant">· Langkah {{ $cfg['langkah'] }}</span></h2>
            <p class="text-xs text-on-surface-variant">{{ $cfg['desc'] }}</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            @if ($filter)<input type="hidden" name="kategori" value="{{ $filter }}">@endif
            <label class="flex items-center gap-2 h-10 w-56 max-w-full rounded-xl bg-surface-container-low px-3">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari…" class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0">
            </label>
        </form>
    </div>

    {{-- Filter --}}
    <div class="flex flex-wrap gap-1.5 text-xs font-semibold">
        @foreach ([null => 'Semua', 'segera' => 'Butuh segera', 'A' => 'Kategori A', 'B' => 'Kategori B', 'C' => 'Kategori C'] as $fk => $fl)
            <a href="{{ route('ngopi-pagi.admin.tahap.' . $tahap, array_filter(['kategori' => $fk, 'q' => request('q')])) }}"
                class="px-3 py-1.5 rounded-full border {{ $filter === $fk ? 'bg-brand text-white border-brand' : 'bg-white border-outline-variant/50 text-on-surface-variant hover:bg-brand-soft' }}">{{ $fl }}</a>
        @endforeach
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl bg-white border border-outline-variant/40 p-10 text-center text-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[40px] text-outline">inbox</span>
            <p class="mt-1">Tidak ada kasus di tahap ini.</p>
        </div>
    @else
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach ($items as $it)
                @php $kat = $it->kategori ? $K::KATEGORI[$it->kategori] : null; @endphp
                <a href="{{ route('ngopi-pagi.admin.tahap.' . $tahap, ['konsultasi' => $it->id]) }}"
                    class="block rounded-2xl bg-white border border-outline-variant/40 border-l-4 {{ $kat['edge'] ?? 'border-l-outline-variant' }} p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-2">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-extrabold truncate">{{ $it->nama }}</p>
                            <p class="text-[11px] text-on-surface-variant truncate">{{ $it->no_konsultasi }} · {{ $it->kelurahan }}, {{ $it->kecamatan }}</p>
                        </div>
                        @if ($it->belum_dibaca > 0)
                            <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[11px] font-bold flex items-center justify-center">{{ $it->belum_dibaca }}</span>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @if ($it->butuh_segera)<span class="px-2 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-bold">Butuh segera</span>@endif
                        @if ($kat)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $kat['chip'] }}">Kategori {{ $it->kategori }} · {{ $kat['nama'] }}</span>@endif
                        @if ($it->jenis_masalah)<span class="px-2 py-0.5 rounded-full bg-surface-container text-[10px] font-semibold">{{ $K::JENIS_MASALAH[$it->jenis_masalah] ?? $it->jenis_masalah }}</span>@endif
                        @if ($it->jenis_tindak_lanjut)<span class="px-2 py-0.5 rounded-full bg-brand-soft text-brand text-[10px] font-semibold">{{ $K::TINDAK_LANJUT[$it->jenis_tindak_lanjut]['label'] ?? '' }}</span>@endif
                    </div>
                    <p class="mt-2 text-xs text-on-surface-variant line-clamp-2">{{ $it->kebutuhan }}</p>
                    <div class="mt-3 flex items-center gap-1.5 text-[11px] text-on-surface-variant">
                        <span class="material-symbols-outlined text-[14px]">support_agent</span>
                        <span class="truncate">{{ $it->konselor->name ?? $it->petugas->name ?? 'Belum ada petugas' }}</span>
                        <span class="ml-auto shrink-0">{{ optional($it->terakhir_pesan_at)->locale('id')->diffForHumans() }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        <div>{{ $items->links() }}</div>
    @endif
</div>
@endsection
