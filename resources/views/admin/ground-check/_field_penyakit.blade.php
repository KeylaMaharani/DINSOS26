@php
    $key = "jawaban.{$p->id}";
    $nm = "jawaban[{$p->id}]";
    $cur = old($key, $nilai[$p->id] ?? null);
    $kelas = 'w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/30';
@endphp

<div class="col-span-1 py-2 px-1 space-y-1.5" data-wajib="pilihan">
    <label class="block text-xs font-medium text-on-surface">{{ $p->nama }} <span class="text-error">*</span></label>

    <div>
        <select name="{{ $nm }}" class="{{ $kelas }}">
            <option value="">-- Pilih --</option>
            @foreach ($master['penyakit'] as $it)
                <option value="{{ $it->nama }}" @selected((string) $cur === $it->nama)>{{ $it->nama }}</option>
            @endforeach
        </select>

        @error($key)<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
    </div>
</div>
