{{-- Tab alur Ngopi Pagi. Variabel: $hitung (jumlah per tahap), $aktif (key tahap | 'rekap') --}}
@php $K = \App\Models\NgopiPagiKonsultasi::class; @endphp
<nav class="flex gap-1.5 overflow-x-auto pb-1" aria-label="Tahap layanan">
    @foreach ($K::TAHAP as $key => $c)
        @continue(($c['menu'] ?? true) === false)
        @php $on = ($aktif ?? null) === $key; @endphp
        <a href="{{ route('ngopi-pagi.admin.tahap.' . $key) }}"
            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $on ? 'bg-brand text-white shadow-sm' : 'bg-white border border-outline-variant/50 text-on-surface-variant hover:bg-brand-soft' }}">
            <span class="material-symbols-outlined text-[16px]">{{ $c['icon'] }}</span>{{ $c['label'] }}
            <span class="min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-bold flex items-center justify-center {{ $on ? 'bg-white/25 text-white' : 'bg-surface-container text-on-surface-variant' }}">{{ $hitung[$key] ?? 0 }}</span>
        </a>
    @endforeach
    <a href="{{ route('ngopi-pagi.admin.rekap') }}"
        class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ ($aktif ?? null) === 'rekap' ? 'bg-brand text-white shadow-sm' : 'bg-white border border-outline-variant/50 text-on-surface-variant hover:bg-brand-soft' }}">
        <span class="material-symbols-outlined text-[16px]">table_chart</span>Rekapitulasi
    </a>
</nav>
