@extends('layouts.kesan')

@section('page_title', 'Detail Calon Penerima')
@section('back', route('kesan.app.penerima'))

@section('content')
@php $kat = $penerima->kategori; $c = $kat->warnaCfg(); $pr = $penerima->prioritasCfg(); @endphp
<div class="grid lg:grid-cols-3 gap-4 items-start">

    <div class="lg:col-span-2 space-y-4">
        <div class="relative h-56 sm:h-80 rounded-3xl overflow-hidden {{ $c['soft'] }} flex items-center justify-center">
            @if ($penerima->fotoUrl())
                <img src="{{ $penerima->fotoUrl() }}" alt="Foto {{ $penerima->nama }}" class="absolute inset-0 w-full h-full object-cover">
            @else
                <span class="material-symbols-outlined msym-fill text-[96px] {{ $c['text'] }}">{{ $kat->ikon }}</span>
            @endif
            <span class="absolute left-3 bottom-3 rounded-md px-2.5 py-1 text-xs font-bold text-white {{ $c['solid'] }}">{{ $kat->nama }}</span>
        </div>

        <div>
            <h1 class="text-xl font-extrabold text-primary">{{ $penerima->nama }}</h1>
            <p class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-on-surface-variant">
                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-brand">location_on</span>{{ $penerima->wilayah() }}</span>
                <span class="flex items-center gap-1 font-semibold {{ $pr['text'] }}"><span class="w-2 h-2 rounded-full {{ $pr['dot'] }}"></span>{{ $pr['label'] }}</span>
                @if ($penerima->jumlah_jiwa)<span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">group</span>{{ $penerima->jumlah_jiwa }} jiwa</span>@endif
            </p>
        </div>

        <section class="rounded-2xl bg-white border border-outline-variant/40 p-4">
            <h2 class="text-sm font-extrabold text-primary">Profil Singkat</h2>
            <p class="mt-1 text-sm leading-relaxed text-on-surface-variant">{{ $penerima->profil ?: 'Profil belum dilengkapi petugas.' }}</p>
        </section>

        <section class="rounded-2xl bg-white border border-outline-variant/40 p-4">
            <h2 class="text-sm font-extrabold text-primary">Jenis Bantuan yang Dibutuhkan</h2>
            <p class="mt-2 flex items-start gap-2 text-sm text-on-surface-variant">
                <span class="w-7 h-7 shrink-0 rounded-full {{ $c['solid'] }} flex items-center justify-center">
                    <span class="material-symbols-outlined msym-fill text-white text-[16px]">{{ $kat->ikon }}</span></span>
                <span class="pt-0.5">{{ $penerima->kebutuhan ?: $kat->nama }}</span>
            </p>
        </section>
    </div>

    {{-- Estimasi + aksi --}}
    <aside class="space-y-3 lg:sticky lg:top-28">
        <div class="rounded-2xl bg-white border border-outline-variant/40 p-4">
            <p class="text-xs font-semibold text-on-surface-variant">Estimasi Bantuan</p>
            <p class="mt-1 rounded-xl bg-success-container px-4 py-3 text-xl font-extrabold text-on-success-container">{{ $penerima->rupiah() }}</p>

            <div class="mt-4">
                @if ($milikSaya)
                    <a href="{{ route('kesan.app.tracking', $milikSaya) }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-surface-container py-3.5 text-sm font-bold text-primary">
                        <span class="material-symbols-outlined text-[20px]">local_shipping</span>Sudah Anda pilih, pantau penyaluran</a>
                @elseif ($diKeranjang)
                    <a href="{{ route('kesan.app.pilihan') }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-success-container py-3.5 text-sm font-bold text-on-success-container">
                        <span class="material-symbols-outlined text-[20px]">check_circle</span>Ada di pilihan Anda, lihat ringkasan</a>
                @elseif ($penerima->aktif && $penerima->status === 'tersedia')
                    <form method="POST" action="{{ route('kesan.app.pilih', $penerima) }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand py-3.5 text-sm font-bold text-white shadow-sm hover:bg-primary transition-colors">
                            <span class="material-symbols-outlined text-[20px]">check_circle</span>Pilih Penerima</button>
                    </form>
                @else
                    <p class="rounded-xl bg-slate-100 py-3.5 text-center text-sm font-semibold text-slate-600">Sudah dipilih Filantropi lain</p>
                @endif
            </div>
        </div>
    </aside>
</div>
@endsection
