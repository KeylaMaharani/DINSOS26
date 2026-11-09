@extends('layouts.kesan')

@section('page_title', 'Daftar Calon Penerima')
@section('back', route('kesan.app.beranda'))

@section('content')
@php
    $f = request()->only('q', 'kategori', 'kecamatan', 'prioritas');
    $sel = 'w-full h-11 rounded-xl border border-outline-variant/70 bg-white px-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
@endphp
<div class="space-y-4">
    <form method="GET" class="space-y-3">
        @if (request('kategori'))<input type="hidden" name="kategori" value="{{ request('kategori') }}">@endif
        <label class="flex items-center gap-2 h-12 rounded-xl border border-outline-variant/70 bg-white px-3 focus-within:border-brand focus-within:ring-2 focus-within:ring-brand/20">
            <span class="material-symbols-outlined text-[20px] text-on-surface-variant">search</span>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, wilayah, atau jenis bantuan..."
                class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0">
        </label>
        <div class="grid grid-cols-2 gap-3 sm:max-w-md">
            <select name="kecamatan" class="{{ $sel }}" onchange="this.form.submit()" aria-label="Wilayah">
                <option value="">Semua Wilayah</option>
                @foreach (\App\Models\KesanPenerima::KECAMATAN as $kec)<option value="{{ $kec }}" @selected(request('kecamatan') === $kec)>{{ $kec }}</option>@endforeach
            </select>
            <select name="prioritas" class="{{ $sel }}" onchange="this.form.submit()" aria-label="Prioritas">
                <option value="">Prioritas</option>
                @foreach (\App\Models\KesanPenerima::PRIORITAS as $pk => $pv)<option value="{{ $pk }}" @selected(request('prioritas') === $pk)>{{ $pv['label'] }}</option>@endforeach
            </select>
        </div>
    </form>

    {{-- Kategori (geser di HP) --}}
    <div class="kk-scroll -mx-4 px-4 flex gap-2 overflow-x-auto">
        @php $base = collect($f)->except('kategori')->filter()->all(); @endphp
        <a href="{{ route('kesan.app.penerima', $base) }}" class="shrink-0 rounded-full px-4 py-2 text-xs font-bold border {{ ! request('kategori') ? 'bg-brand text-white border-brand' : 'bg-white text-on-surface-variant border-outline-variant/60' }}">Semua</a>
        @foreach ($kategoris as $k)
            <a href="{{ route('kesan.app.penerima', $base + ['kategori' => $k->kode]) }}"
                class="shrink-0 inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-xs font-bold border {{ request('kategori') === $k->kode ? 'bg-brand text-white border-brand' : 'bg-white text-on-surface-variant border-outline-variant/60' }}">
                <span class="material-symbols-outlined text-[16px]">{{ $k->ikon }}</span>{{ $k->label() }}</a>
        @endforeach
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl bg-white border border-outline-variant/40 p-10 text-center text-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[40px] text-outline-variant">search_off</span>
            <p class="mt-1">Tidak ada calon penerima yang cocok. Ubah filter atau kata kunci.</p>
        </div>
    @else
        <div class="grid lg:grid-cols-2 gap-3">
            @foreach ($items as $p)@include('kesan.app._kartu', ['p' => $p])@endforeach
        </div>
        <div>{{ $items->links() }}</div>
    @endif
</div>
@endsection
