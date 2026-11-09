@props(['src' => null, 'label' => 'Lampiran', 'icon' => 'image_not_supported', 'panel' => false])

@php
    // Deteksi jenis file dari ekstensi URL (query string diabaikan)
    $path   = $src ? parse_url($src, PHP_URL_PATH) : null;
    $ext    = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
    $isImg  = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    $isPdf  = $ext === 'pdf';
    $isWord = in_array($ext, ['doc', 'docx'], true);

    // File baru disimpan dengan format "acak6__nama-asli.ext" -> tampilkan bagian nama aslinya.
    // File lama (tanpa "__") tidak menampilkan nama, hanya label.
    $basename  = $path ? basename(rawurldecode($path)) : null;
    $namaFile  = ($basename && str_contains($basename, '__')) ? \Illuminate\Support\Str::after($basename, '__') : null;

    // Word tidak bisa dirender browser -> buka lewat Office Online Viewer (butuh URL publik)
    $lihat = ($src && $isWord) ? 'https://view.officeapps.live.com/op/view.aspx?src=' . urlencode($src) : $src;

    $ikonFile = $isPdf ? 'picture_as_pdf' : 'description';
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button type="button"
        @if($src) @click="open = !open" @else disabled @endif
        class="group block w-full border border-outline-variant/40 rounded-lg overflow-hidden hover:border-primary transition-all text-left {{ $src ? '' : 'cursor-not-allowed' }}">
        <div class="aspect-square bg-surface-container/50 flex items-center justify-center p-2">
            @if ($src && $isImg)
                <img src="{{ $src }}" class="w-full h-full object-contain mix-blend-multiply transition-transform group-hover:scale-105" alt="{{ $label }}">
            @elseif ($src)
                <div class="flex flex-col items-center gap-1">
                    <span class="material-symbols-outlined text-[40px] {{ $isPdf ? 'text-error' : 'text-secondary' }}">{{ $ikonFile }}</span>
                    <span class="text-[10px] font-bold uppercase text-on-surface-variant">{{ $ext ?: 'file' }}</span>
                </div>
            @else
                <span class="material-symbols-outlined text-[32px] text-on-surface-variant/40">{{ $icon }}</span>
            @endif
        </div>
        <div class="bg-surface-container-lowest border-t border-outline-variant/40 p-2">
            <p class="text-[11px] text-center text-on-surface-variant font-medium">{{ $label }}</p>
            @if ($namaFile)
                <p class="text-[10px] text-center text-primary font-semibold truncate mt-0.5" title="{{ $namaFile }}">{{ $namaFile }}</p>
            @endif
        </div>
    </button>

    @if ($src)
        <div x-show="open" x-cloak x-transition
             class="absolute z-30 left-1/2 -translate-x-1/2 mt-1 w-36 bg-white border border-outline-variant/40 rounded-lg shadow-lg overflow-hidden">
            @if ($panel)
                {{-- Mode panel: "Lihat" tidak membuka tab baru, tapi mengirim event ke halaman
                     (ditangkap x-data pada halaman detail) untuk ditampilkan di card tersendiri. --}}
                <button type="button"
                        @click="open = false; $dispatch('lampiran-panel', { src: @js($src), view: @js($lihat), label: @js($label), img: @js($isImg) })"
                        class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-surface-container text-on-surface text-left">
                    <span class="material-symbols-outlined text-[16px]">visibility</span> Lihat
                </button>
            @else
                <a href="{{ $lihat }}" target="_blank" rel="noopener"
                   class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-surface-container text-on-surface">
                    <span class="material-symbols-outlined text-[16px]">visibility</span> Lihat
                </a>
            @endif
            <a href="{{ $src }}" download="{{ $namaFile ?: $basename }}"
               class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-surface-container text-on-surface border-t border-outline-variant/30">
                <span class="material-symbols-outlined text-[16px]">download</span> Unduh
            </a>
        </div>
    @endif
</div>