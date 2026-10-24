{{-- Field Ground Check: "Apakah memiliki keluhan kesehatan kronis/menahun?"
     Pilihan diambil dari Master Penyakit Kronis (gc_penyakit_kronis).
     Tata letak sama dengan _field / _field_master: label kiri, dropdown kanan. --}}
@php
    $key = "jawaban.{$p->id}";
    $nm = "jawaban[{$p->id}]";
    $cur = old($key, $nilai[$p->id] ?? null);
    $kelas = 'w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/30';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 sm:gap-6 py-3 px-2 border-b border-outline-variant/30 items-start rounded-lg" data-wajib="pilihan">
    <label class="text-xs font-medium text-on-surface pt-2">{{ $p->nama }} <span class="text-error">*</span></label>

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
