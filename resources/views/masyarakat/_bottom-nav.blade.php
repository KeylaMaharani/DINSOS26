{{-- resources/views/masyarakat/_bottom-nav.blade.php --}}
@php
    $items = [
        [
            'label'  => 'Beranda',
            'url'    => route('masyarakat.dashboard'),
            // Layanan (permohonan, PBI APBD, DTSEN) sekarang diakses dari Fitur Utama di Beranda,
            // jadi halaman-halamannya ikut menandai tab Beranda sebagai aktif.
            'active' => request()->routeIs('masyarakat.dashboard', 'masyarakat.permohonan', 'masyarakat.pbi-apbd.*', 'masyarakat.dtsen*'),
            'icon'   => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        ],
        [
            'label'  => 'Lacak',
            'url'    => route('masyarakat.tracking'),
            'active' => request()->routeIs('masyarakat.tracking'),
            'icon'   => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        ],
        [
            'label'  => 'Keluarga',
            'url'    => route('masyarakat.keluarga'),
            'active' => request()->routeIs('masyarakat.keluarga*'),
            'icon'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        ],
        [
            'label'  => 'Profil',
            'url'    => route('masyarakat.profil'),
            'active' => request()->routeIs('masyarakat.profil*', 'masyarakat.password*'),
            'icon'   => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        ],
    ];
@endphp

<nav class="m-bottomnav" aria-label="Navigasi utama">
    @foreach ($items as $it)
        <a href="{{ $it['url'] }}" class="m-navitem {{ $it['active'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" aria-hidden="true">{!! $it['icon'] !!}</svg>
            <span>{{ $it['label'] }}</span>
        </a>
    @endforeach
</nav>
