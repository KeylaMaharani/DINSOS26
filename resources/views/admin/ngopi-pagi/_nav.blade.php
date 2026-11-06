{{-- Tab alur Ngopi Pagi. Variabel: $hitung (jumlah per tahap), $aktif (key tahap | 'rekap' | 'konselor' | 'spesialisasi') --}}
@php
    $K = \App\Models\NgopiPagiKonsultasi::class;
    $hitung = $hitung ?? [];
    $tab = fn ($on) => $on ? 'bg-brand text-white shadow-sm' : 'bg-white border border-outline-variant/50 text-on-surface-variant hover:bg-brand-soft';
@endphp
<nav class="flex gap-1.5 overflow-x-auto pb-1" aria-label="Tahap layanan">
    @foreach ($K::TAHAP as $key => $c)
        @continue(($c['menu'] ?? true) === false)
        @php $on = ($aktif ?? null) === $key; @endphp
        <a href="{{ route('ngopi-pagi.admin.tahap.' . $key) }}"
            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $tab($on) }}">
            <span class="material-symbols-outlined text-[16px]">{{ $c['icon'] }}</span>{{ $c['label'] }}
            <span class="min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-bold flex items-center justify-center {{ $on ? 'bg-white/25 text-white' : 'bg-surface-container text-on-surface-variant' }}">{{ $hitung[$key] ?? 0 }}</span>
        </a>
    @endforeach
    <a href="{{ route('ngopi-pagi.admin.rekap') }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $tab(($aktif ?? null) === 'rekap') }}">
        <span class="material-symbols-outlined text-[16px]">table_chart</span>Rekapitulasi
    </a>
    <span class="shrink-0 w-px bg-outline-variant/60 mx-1"></span>
    <a href="{{ route('ngopi-pagi.admin.konselor.index') }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $tab(($aktif ?? null) === 'konselor') }}">
        <span class="material-symbols-outlined text-[16px]">badge</span>Data Konselor
    </a>
    <a href="{{ route('ngopi-pagi.admin.spesialisasi.index') }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $tab(($aktif ?? null) === 'spesialisasi') }}">
        <span class="material-symbols-outlined text-[16px]">category</span>Spesialisasi
    </a>
</nav>
