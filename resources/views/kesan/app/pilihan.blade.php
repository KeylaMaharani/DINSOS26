@extends('layouts.kesan')

@section('page_title', 'Konfirmasi Pilihan')
@section('back', route('kesan.app.penerima'))

@section('content')
@if ($items->isEmpty())
    <div class="max-w-md mx-auto rounded-3xl bg-white border border-outline-variant/40 p-8 text-center">
        <span class="material-symbols-outlined text-[56px] text-outline-variant">favorite</span>
        <h1 class="mt-2 text-lg font-extrabold text-primary">Belum ada pilihan</h1>
        <p class="mt-1 text-sm text-on-surface-variant">Pilih calon penerima terlebih dulu, lalu ringkasannya muncul di sini.</p>
        <a href="{{ route('kesan.app.penerima') }}" class="mt-5 inline-flex rounded-xl bg-brand px-5 py-3 text-sm font-bold text-white">Lihat calon penerima</a>
    </div>
@else
<div class="grid lg:grid-cols-5 gap-4 items-start">

    {{-- Ringkasan + tombol --}}
    <section class="lg:col-span-2 lg:order-2 lg:sticky lg:top-28 rounded-3xl bg-white border border-outline-variant/40 p-5 text-center">
        <span class="mx-auto w-16 h-16 rounded-full bg-success-container flex items-center justify-center">
            <span class="material-symbols-outlined msym-fill text-[34px] text-success">handshake</span></span>
        <h1 class="mt-3 text-2xl font-extrabold text-primary">Berhasil Dipilih!</h1>
        <p class="mt-1 text-sm text-on-surface-variant">
            Anda telah memilih {{ $items->count() }} calon penerima
            {{ $ringkas->count() > 1 ? 'dengan kategori bantuan yang berbeda.' : 'dengan kategori ' . $ringkas->first()['kategori']->label() . '.' }}</p>

        <ul class="mt-4 space-y-2 text-left">
            @foreach ($ringkas as $r)
                @php $c = $r['kategori']->warnaCfg(); @endphp
                <li class="flex items-center gap-3 rounded-xl border border-outline-variant/40 p-2.5">
                    <span class="w-10 h-10 shrink-0 rounded-full {{ $c['solid'] }} flex items-center justify-center">
                        <span class="material-symbols-outlined msym-fill text-white text-[20px]">{{ $r['kategori']->ikon }}</span></span>
                    <span class="min-w-0">
                        <span class="block text-xs font-bold leading-tight">{{ $r['kategori']->nama }}</span>
                        <span class="block text-[11px] text-on-surface-variant">{{ $r['jumlah'] }} orang</span></span>
                </li>
            @endforeach
        </ul>

        <p class="mt-4 flex items-center justify-between rounded-xl bg-surface-container-low px-4 py-2.5 text-sm">
            <span class="text-on-surface-variant">Total estimasi</span>
            <b class="text-primary">Rp {{ number_format($total, 0, ',', '.') }}</b></p>

        <form method="POST" action="{{ route('kesan.app.konfirmasi') }}" class="mt-4"
            onsubmit="return confirm('Lanjutkan {{ $items->count() }} pilihan ke tahap penyaluran? Petugas akan memverifikasinya.');">
            @csrf
            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand py-3.5 text-sm font-bold text-white hover:bg-primary transition-colors">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>Lanjutkan ke Penyaluran</button>
        </form>
        <a href="{{ route('kesan.app.penerima') }}" class="mt-2 flex w-full items-center justify-center rounded-xl border-2 border-brand py-3 text-sm font-bold text-brand hover:bg-brand-soft transition-colors">Pilih Lagi</a>
    </section>

    {{-- Daftar pilihan --}}
    <section class="lg:col-span-3 lg:order-1 space-y-3">
        <h2 class="text-base font-extrabold text-primary">Calon penerima yang Anda pilih</h2>
        @foreach ($items as $it)
            @php $p = $it->penerima; $c = $p->kategori->warnaCfg(); @endphp
            <article class="flex items-center gap-3 rounded-2xl bg-white border border-outline-variant/40 p-3">
                <div class="w-16 h-16 shrink-0 rounded-xl overflow-hidden {{ $c['soft'] }} flex items-center justify-center">
                    @if ($p->fotoUrl())<img src="{{ $p->fotoUrl() }}" alt="" class="w-full h-full object-cover">
                    @else<span class="material-symbols-outlined msym-fill {{ $c['text'] }} text-[30px]">{{ $p->kategori->ikon }}</span>@endif
                </div>
                <div class="min-w-0 flex-1">
                    <a href="{{ route('kesan.app.detail', $p) }}" class="block text-sm font-extrabold leading-snug truncate">{{ $p->nama }}</a>
                    <p class="text-[11px] text-on-surface-variant truncate">{{ $p->kategori->label() }} · Kec. {{ $p->kecamatan }}</p>
                    <p class="text-xs font-bold text-primary">{{ $p->rupiah() }}</p>
                </div>
                <form method="POST" action="{{ route('kesan.app.hapus', $it) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-9 h-9 rounded-full text-error hover:bg-error-container/50 flex items-center justify-center" aria-label="Keluarkan dari pilihan" title="Keluarkan">
                        <span class="material-symbols-outlined text-[20px]">close</span></button>
                </form>
            </article>
        @endforeach
    </section>
</div>
@endif
@endsection
