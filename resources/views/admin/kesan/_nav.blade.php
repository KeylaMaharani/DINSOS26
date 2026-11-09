@php
    $r = auth()->user()->role ?? null;
    $tabs = [
        ['k' => 'pilihan', 'label' => 'Verifikasi Pilihan', 'icon' => 'fact_check', 'route' => 'kesan-admin.pilihan.index', 'mod' => 'kesan.pilihan'],
        ['k' => 'penerima', 'label' => 'Calon Penerima', 'icon' => 'groups', 'route' => 'kesan-admin.penerima.index', 'mod' => 'kesan.penerima'],
        ['k' => 'kategori', 'label' => 'Kategori Bantuan', 'icon' => 'category', 'route' => 'kesan-admin.kategori.index', 'mod' => 'kesan.kategori'],
        ['k' => 'filantropi', 'label' => 'Data Filantropi', 'icon' => 'volunteer_activism', 'route' => 'kesan-admin.filantropi.index', 'mod' => 'kesan.filantropi'],
    ];
@endphp
<div class="flex flex-wrap gap-2">
    @foreach ($tabs as $t)
        @if ($r && $r->hasModule($t['mod']))
            <a href="{{ route($t['route']) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-colors {{ $aktif === $t['k'] ? 'bg-brand text-white' : 'bg-white border border-outline-variant/50 text-on-surface-variant hover:bg-surface-container' }}">
                <span class="material-symbols-outlined text-[18px]">{{ $t['icon'] }}</span>{{ $t['label'] }}</a>
        @endif
    @endforeach
</div>
