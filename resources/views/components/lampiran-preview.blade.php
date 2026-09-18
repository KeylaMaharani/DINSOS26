@props(['src' => null, 'label' => 'Lampiran', 'icon' => 'image_not_supported'])

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button type="button"
        @if($src) @click="open = !open" @else disabled @endif
        class="group block w-full border border-outline-variant/40 rounded-lg overflow-hidden hover:border-primary transition-all text-left {{ $src ? '' : 'cursor-not-allowed' }}">
        <div class="aspect-square bg-surface-container/50 flex items-center justify-center p-2">
            @if ($src)
                <img src="{{ $src }}" class="w-full h-full object-contain mix-blend-multiply transition-transform group-hover:scale-105" alt="{{ $label }}">
            @else
                <span class="material-symbols-outlined text-[32px] text-on-surface-variant/40">{{ $icon }}</span>
            @endif
        </div>
        <div class="bg-surface-container-lowest border-t border-outline-variant/40 p-2">
            <p class="text-[11px] text-center text-on-surface-variant font-medium">{{ $label }}</p>
        </div>
    </button>

    @if ($src)
        <div x-show="open" x-cloak x-transition
             class="absolute z-30 left-1/2 -translate-x-1/2 mt-1 w-36 bg-white border border-outline-variant/40 rounded-lg shadow-lg overflow-hidden">
            <a href="{{ $src }}" target="_blank" rel="noopener"
               class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-surface-container text-on-surface">
                <span class="material-symbols-outlined text-[16px]">visibility</span> Lihat
            </a>
            <a href="{{ $src }}" download
               class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-surface-container text-on-surface border-t border-outline-variant/30">
                <span class="material-symbols-outlined text-[16px]">download</span> Unduh
            </a>
        </div>
    @endif
</div>
