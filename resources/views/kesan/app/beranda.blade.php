@extends('layouts.kesan')

@section('page_title', 'Beranda')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-extrabold text-primary">Halo, {{ auth()->user()->name }}</h1>
        <p class="text-sm text-on-surface-variant">Mari bersama wujudkan perubahan bagi mereka yang membutuhkan.</p>
    </div>

    {{-- Banner --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand to-secondary p-5 sm:p-7 text-white shadow-lg">
        <span class="material-symbols-outlined msym-fill absolute -right-4 -bottom-6 text-[150px] sm:text-[220px] text-white/15" aria-hidden="true">volunteer_activism</span>
        <div class="relative max-w-md">
            <h2 class="text-lg sm:text-2xl font-extrabold leading-snug">Kolaborasi untuk Kehidupan yang Lebih Baik</h2>
            <p class="mt-1 text-sm text-white/85">Pilih kategori bantuan, lihat calon penerima, lalu pantau penyalurannya sampai selesai.</p>
            <a href="{{ route('kesan.app.penerima') }}"
                class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-sm font-bold text-primary hover:bg-brand-soft transition-colors">
                Lihat Program<span class="material-symbols-outlined text-[18px]">arrow_forward</span></a>
        </div>
    </section>

    {{-- Ringkasan --}}
    <section class="grid grid-cols-3 gap-3">
        @foreach ([['Calon penerima', $stat['tersedia'], 'groups'], ['Bantuan berjalan', $stat['berjalan'], 'local_shipping'], ['Selesai disalurkan', $stat['selesai'], 'task_alt']] as [$l, $v, $i])
            <div class="rounded-2xl bg-white border border-outline-variant/40 p-3 text-center">
                <span class="material-symbols-outlined text-brand text-[22px]">{{ $i }}</span>
                <p class="text-xl font-extrabold text-primary leading-tight">{{ number_format($v, 0, ',', '.') }}</p>
                <p class="text-[11px] text-on-surface-variant leading-tight">{{ $l }}</p>
            </div>
        @endforeach
    </section>

    {{-- Kategori calon penerima (langkah 2) --}}
    <section>
        <h2 class="text-base font-extrabold text-primary mb-3">Kategori Calon Penerima</h2>
        @if ($kategoris->isEmpty())
            <p class="rounded-2xl bg-white border border-outline-variant/40 p-6 text-center text-sm text-on-surface-variant">Belum ada kategori bantuan.</p>
        @else
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                @foreach ($kategoris as $k)
                    @php $c = $k->warnaCfg(); @endphp
                    <a href="{{ route('kesan.app.penerima', ['kategori' => $k->kode]) }}"
                        class="flex flex-col items-center gap-2 rounded-2xl bg-white border border-outline-variant/40 p-3 text-center shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                        <span class="w-14 h-14 rounded-full {{ $c['solid'] }} flex items-center justify-center">
                            <span class="material-symbols-outlined msym-fill text-white text-[28px]">{{ $k->ikon }}</span>
                        </span>
                        <span class="text-[11px] font-bold leading-tight text-on-surface">{{ $k->label() }}</span>
                        <span class="text-[10px] text-on-surface-variant">{{ $k->tersedia_count }} orang</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Status terbaru --}}
    <section>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-extrabold text-primary">Penyaluran Terbaru</h2>
            @if ($terbaru->isNotEmpty())<a href="{{ route('kesan.app.riwayat') }}" class="text-xs font-bold text-brand">Lihat semua</a>@endif
        </div>
        @forelse ($terbaru as $it)
            @php $cfg = $it->cfg(); @endphp
            <a href="{{ route('kesan.app.tracking', $it) }}" class="mb-2 flex items-center gap-3 rounded-2xl bg-white border border-outline-variant/40 p-3">
                <span class="w-11 h-11 shrink-0 rounded-xl {{ $it->penerima->kategori->warnaCfg()['soft'] }} flex items-center justify-center">
                    <span class="material-symbols-outlined msym-fill {{ $it->penerima->kategori->warnaCfg()['text'] }}">{{ $it->penerima->kategori->ikon }}</span></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold truncate">{{ $it->penerima->nama }}</span>
                    <span class="block text-[11px] text-on-surface-variant">{{ $it->penerima->kategori->label() }}</span>
                </span>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $cfg['chip'] }}">{{ $cfg['label'] }}</span>
            </a>
        @empty
            <p class="rounded-2xl bg-white border border-outline-variant/40 p-6 text-center text-sm text-on-surface-variant">
                Belum ada bantuan yang dipilih. <a href="{{ route('kesan.app.penerima') }}" class="font-bold text-brand">Pilih calon penerima</a></p>
        @endforelse
    </section>
</div>
@endsection
