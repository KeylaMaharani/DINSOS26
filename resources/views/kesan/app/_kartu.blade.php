{{-- Kartu calon penerima. Variabel: $p (KesanPenerima), $dipilihIds (array id di keranjang) --}}
@php $kat = $p->kategori; $c = $kat->warnaCfg(); $pr = $p->prioritasCfg(); $sudah = in_array($p->id, $dipilihIds ?? [], true); @endphp
<article class="flex gap-3 rounded-2xl bg-white border border-outline-variant/40 p-3 shadow-sm">
    <div class="w-24 h-24 sm:w-28 sm:h-28 shrink-0 rounded-xl overflow-hidden {{ $c['soft'] }} flex items-center justify-center">
        @if ($p->fotoUrl())
            <img src="{{ $p->fotoUrl() }}" alt="Foto {{ $p->nama }}" loading="lazy" class="w-full h-full object-cover">
        @else
            <span class="material-symbols-outlined msym-fill text-[44px] {{ $c['text'] }}">{{ $kat->ikon }}</span>
        @endif
    </div>
    <div class="min-w-0 flex-1 flex flex-col">
        <span class="self-start max-w-full truncate rounded px-2 py-0.5 text-[10px] font-bold text-white {{ $c['solid'] }}">{{ $kat->nama }}</span>
        <h3 class="mt-1 text-sm font-extrabold leading-snug text-on-surface">{{ $p->nama }}</h3>
        <p class="mt-0.5 flex items-center gap-1 text-[11px] text-on-surface-variant">
            <span class="material-symbols-outlined text-[14px] text-brand">location_on</span>Kec. {{ $p->kecamatan }}</p>
        <p class="flex items-center gap-1 text-[11px] font-semibold {{ $pr['text'] }}">
            <span class="w-2 h-2 rounded-full {{ $pr['dot'] }}"></span>{{ $pr['label'] }}</p>
        <div class="mt-auto pt-2 flex items-center justify-end gap-2">
            @if ($sudah)
                <span class="text-[11px] font-semibold text-success">Ada di pilihan Anda</span>
            @endif
            <a href="{{ route('kesan.app.detail', $p) }}"
                class="inline-flex items-center rounded-lg bg-brand px-3.5 py-1.5 text-xs font-bold text-white hover:bg-primary transition-colors">Lihat Detail</a>
        </div>
    </div>
</article>
