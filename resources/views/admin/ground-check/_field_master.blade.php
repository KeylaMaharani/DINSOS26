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
        @if ($p->sumber === 'lapangan_usaha')
            <select name="{{ $nm }}" data-gc-lapangan class="{{ $kelas }}">
                <option value="">-- Pilih --</option>
                @foreach ($master['lapangan'] as $l)
                    <option value="{{ $l->nama }}" @selected((string) $cur === $l->nama)
                        data-info="{{ json_encode(['ikon' => $l->ikon, 'penjelasan' => $l->penjelasan, 'sendiri' => $l->contoh_berusaha_sendiri, 'buruh' => $l->contoh_buruh], JSON_UNESCAPED_UNICODE) }}">{{ $l->nama }}</option>
                @endforeach
            </select>

            <div data-gc-info hidden class="mt-2 px-3 py-2.5 rounded-lg bg-brand-soft/60 border border-brand/15 text-[11px] leading-relaxed text-on-surface space-y-1">
                <p class="flex items-center gap-1.5 font-semibold text-[12px]">
                    <span data-i="ikon" class="text-base leading-none"></span>
                    <span data-i="nama"></span>
                </p>
                <p><span class="font-semibold">Contoh pekerjaan:</span> <span data-i="penjelasan"></span></p>
                <p data-row="sendiri" hidden><span class="font-semibold">Berusaha sendiri:</span> <span data-i="sendiri"></span></p>
                <p data-row="buruh" hidden><span class="font-semibold">Buruh/karyawan/pegawai:</span> <span data-i="buruh"></span></p>
            </div>

        @elseif ($p->sumber === 'pekerjaan')
            @php
                $peta = $master['lapangan']->mapWithKeys(fn ($l) => [$l->nama => $l->pekerjaans->pluck('nama')->all()])->all();
                $semua = collect($peta)->flatten()->unique()->values();
            @endphp
            <select name="{{ $nm }}" data-gc-pekerjaan data-nilai="{{ $cur }}" data-peta="{{ json_encode($peta, JSON_UNESCAPED_UNICODE) }}" class="{{ $kelas }}">
                <option value="">-- Pilih --</option>
                @foreach ($semua as $n)
                    <option value="{{ $n }}" @selected((string) $cur === $n)>{{ $n }}</option>
                @endforeach
            </select>

        @elseif ($p->sumber === 'keterampilan')
            @php
                $pilihanKet = $master['keterampilan']->map(fn ($k) => ['nama' => $k->nama, 'eksl' => (bool) $k->eksklusif])->values();
                $dipilih = old($key);
                if ($dipilih === null) {
                    $mentah = $nilai[$p->id] ?? null;
                    $dipilih = is_array($mentah) ? $mentah : (json_decode((string) $mentah, true) ?: []);
                }
                $dipilih = array_values(array_filter((array) $dipilih, fn ($x) => is_string($x) && $x !== ''));
            @endphp
            @include('admin.ground-check._multi_dropdown', ['nm' => $nm, 'opsiMulti' => $pilihanKet, 'dipilih' => $dipilih])
        @endif

        @error($key)<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
        @error($key . '.*')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
    </div>
</div>
