@extends('layouts.kesan')

@section('page_title', 'Riwayat Bantuan')

@section('content')
<div class="space-y-4">
    <div class="flex gap-2 overflow-x-auto kk-scroll">
        @foreach (['semua' => 'Semua', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'] as $k => $l)
            <a href="{{ route('kesan.app.riwayat', ['s' => $k]) }}"
                class="shrink-0 rounded-full px-5 py-2 text-xs font-bold border {{ $tab === $k ? 'bg-brand text-white border-brand' : 'bg-white text-on-surface-variant border-outline-variant/60' }}">{{ $l }}</a>
        @endforeach
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl bg-white border border-outline-variant/40 p-10 text-center text-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[40px] text-outline-variant">history</span>
            <p class="mt-1">Belum ada riwayat bantuan.</p>
            <a href="{{ route('kesan.app.penerima') }}" class="mt-3 inline-flex rounded-xl bg-brand px-4 py-2 text-sm font-bold text-white">Pilih calon penerima</a>
        </div>
    @else
        <div class="grid lg:grid-cols-2 gap-3">
            @foreach ($items as $it)
                @php $p = $it->penerima; $c = $p->kategori->warnaCfg(); $cfg = $it->cfg(); @endphp
                <a href="{{ route('kesan.app.tracking', $it) }}" class="flex gap-3 rounded-2xl bg-white border border-outline-variant/40 p-3 shadow-sm hover:shadow-md transition">
                    <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden {{ $c['soft'] }} flex items-center justify-center">
                        @if ($p->fotoUrl())<img src="{{ $p->fotoUrl() }}" alt="" class="w-full h-full object-cover">
                        @else<span class="material-symbols-outlined msym-fill {{ $c['text'] }} text-[34px]">{{ $p->kategori->ikon }}</span>@endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-extrabold truncate">{{ $p->nama }}</p>
                        <p class="text-[11px] text-on-surface-variant truncate">{{ $p->kategori->nama }}</p>
                        <span class="mt-1.5 inline-block rounded-full px-2.5 py-1 text-[10px] font-bold {{ $cfg['chip'] }}">{{ $cfg['label'] }}</span>
                        <p class="mt-1 text-[11px] text-on-surface-variant">{{ $it->kode }} · {{ optional($it->dipilih_at)->translatedFormat('d M Y') }}</p>
                    </div>
                    <span class="material-symbols-outlined self-center text-outline-variant">chevron_right</span>
                </a>
            @endforeach
        </div>
        <div>{{ $items->links() }}</div>
    @endif
</div>
@endsection
