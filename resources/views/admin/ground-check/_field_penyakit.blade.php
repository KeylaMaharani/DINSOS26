@php
    $key = "jawaban.{$p->id}";
    $nm = "jawaban[{$p->id}]";
    $cur = old($key, $nilai[$p->id] ?? null);
    $kelas = 'w-full rounded-lg border border-outline-variant/50 px-2.5 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-primary/30';
@endphp

<div class="py-1.5 sm:flex sm:items-start sm:gap-4" data-wajib="pilihan">
    <label class="block sm:w-64 shrink-0 text-xs font-medium text-on-surface sm:pt-2 mb-1 sm:mb-0">
        {{ $p->nama }} <span class="text-error">*</span>
    </label>

    <div class="flex-1 min-w-0 max-w-md">
        <select name="{{ $nm }}" class="{{ $kelas }}">
            <option value="">-- Pilih --</option>
            @foreach ($master['penyakit'] as $it)
                <option value="{{ $it->nama }}" @selected((string) $cur === $it->nama)>{{ $it->nama }}</option>
            @endforeach
        </select>

        @error($key)<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
    </div>
</div>
