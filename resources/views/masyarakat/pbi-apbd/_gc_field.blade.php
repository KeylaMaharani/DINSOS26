{{-- Satu pertanyaan Master Ground Check pada form masyarakat. Variabel: $p, $nomor, $nilai, $opsiLabels, $master, $bisaEdit --}}
@php
    use App\Support\PbiGcForm;

    $key = 'jawaban.' . $p->id;
    $val = old($key, $nilai[$p->id] ?? null);
    $wajib = ! PbiGcForm::opsional($p);
    $labels = $opsiLabels[$p->id] ?? [];
    $in = 'w-full rounded-lg border border-outline-variant/50 px-3 py-2.5 text-sm bg-white disabled:bg-surface-container disabled:text-on-surface-variant';
@endphp

<div class="min-w-0">
    <label for="jawaban-{{ $p->id }}" class="block text-xs font-semibold mb-1">
        {{ $nomor }}. {{ $p->nama }} @if ($wajib)<span class="text-error">*</span>@endif
    </label>

    @if ($p->tipe_input === 'pilihan' && $labels)
        @php
            $atribut = '';
            if ($p->sumber === 'lapangan_usaha') {
                $atribut = 'data-gc-lapangan';
            } elseif ($p->sumber === 'pekerjaan') {
                $peta = $master['lapangan']->mapWithKeys(fn ($l) => [$l->nama => $l->pekerjaansAktif->pluck('nama')->values()->all()])->all();
            }
        @endphp
        <select id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" @disabled(! $bisaEdit) class="{{ $in }}"
                {!! $atribut !!}
                @if ($p->sumber === 'pekerjaan')
                    data-gc-pekerjaan data-nilai="{{ $val }}" data-peta="{{ json_encode($peta ?? [], JSON_UNESCAPED_UNICODE) }}"
                @endif>
            <option value="">- Pilih -</option>
            @foreach ($labels as $label)
                <option value="{{ $label }}" @selected((string) $val === (string) $label)>{{ $label }}</option>
            @endforeach
        </select>

    @elseif ($p->tipe_input === 'pilihan_ganda' && $labels)
        @php $dipilih = is_array($val) ? $val : (json_decode((string) $val, true) ?: []); @endphp
        <div class="space-y-1.5 rounded-lg border border-outline-variant/50 bg-white p-3 max-h-56 overflow-y-auto">
            @foreach ($labels as $label)
                <label class="flex items-start gap-2 text-sm">
                    <input type="checkbox" name="jawaban[{{ $p->id }}][]" value="{{ $label }}" class="mt-0.5 rounded"
                           @checked(in_array($label, $dipilih, true)) @disabled(! $bisaEdit)>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

    @elseif ($p->tipe_input === 'angka')
        @php $lb = PbiGcForm::lintangBujur($p); @endphp
        <input type="number" step="any" @unless ($lb) min="0" @endunless id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]"
               value="{{ $val }}" @disabled(! $bisaEdit) class="{{ $in }}"
               @if (\Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($p->nama), 'lintang')) data-gc-lat @endif
               @if (\Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($p->nama), 'bujur')) data-gc-lng @endif>

    @elseif ($p->tipe_input === 'tanggal')
        <input type="date" id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" value="{{ $val }}" @disabled(! $bisaEdit) class="{{ $in }}">

    @elseif ($p->tipe_input === 'koordinat')
        <input type="text" id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" value="{{ $val }}" data-gc-koordinat
               placeholder="Terisi otomatis dari titik lokasi rumah" @disabled(! $bisaEdit) class="{{ $in }}">

    @elseif ($p->tipe_input === 'foto')
        @if ($val)
            <p class="flex items-center gap-1 text-xs text-success mb-1.5">
                <span class="material-symbols-outlined text-[15px]">check_circle</span> Sudah diunggah (pilih file baru untuk mengganti)
            </p>
        @endif
        <input type="file" id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" accept="image/*" @disabled(! $bisaEdit)
               class="block w-full text-xs border border-outline-variant/40 rounded-lg bg-white file:mr-3 file:py-2.5 file:px-3 file:border-0 file:bg-surface-container file:text-xs file:font-semibold">

    @else
        {{-- teks, atau pilihan tanpa opsi di master --}}
        <input type="text" id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" value="{{ $val }}" maxlength="500"
               @disabled(! $bisaEdit) class="{{ $in }}">
    @endif

    @error($key)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
    @error($key . '.*')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
</div>
