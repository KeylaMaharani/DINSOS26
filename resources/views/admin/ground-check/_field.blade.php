@php
    use Illuminate\Support\Str;

    $id = $p->id;
    $val = old("jawaban.{$id}", $nilai[$id] ?? null);
    $tipe = $p->tipe_input;
    $wajib = $tipe !== 'pilihan_ganda' || $p->opsis->isNotEmpty();
    $namaLower = Str::lower($p->nama);
    $lat = $tipe === 'angka' && Str::contains($namaLower, 'lintang');
    $lng = $tipe === 'angka' && Str::contains($namaLower, 'bujur');
    $angkaKoordinat = $lat || $lng;

    if ($tipe === 'pilihan_ganda' && is_string($val)) {
        $val = json_decode($val, true) ?: [];
    }
    $val = $tipe === 'pilihan_ganda' ? array_values(array_filter((array) $val, fn ($x) => is_string($x) && $x !== '')) : $val;

    $pesan = collect($errors->getMessages())
        ->filter(fn ($m, $k) => $k === "jawaban.{$id}" || str_starts_with($k, "jawaban.{$id}."))
        ->flatten()->first();

    // Lebar input dibuat penuh secara proporsional di dalam kotaknya masing-masing
    $input = 'w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-white';

    // Pengecekan khusus untuk field yang sebaiknya memanjang penuh 1 baris (seperti peta koordinat)
    $fullSpan = in_array($tipe, ['koordinat', 'foto']);
@endphp

<div class="{{ $fullSpan ? 'col-span-1 sm:col-span-2' : 'col-span-1' }} py-2 px-1 space-y-1.5"
     @if ($wajib) data-wajib="{{ $tipe === 'pilihan_ganda' ? 'pilihan' : $tipe }}" @endif
     @if ($tipe === 'foto' && ! empty($nilai[$id])) data-ada="1" @endif>

    <label class="block text-xs font-medium text-on-surface">
        {{ $p->nama }} @if ($wajib)<span class="text-error">*</span>@endif
    </label>

    <div>
        @switch($tipe)
            @case('pilihan')
                @if ($p->opsis->isNotEmpty())
                    <select name="jawaban[{{ $id }}]" class="{{ $input }}">
                        <option value="">-- Pilih --</option>
                        @foreach ($p->opsis as $o)
                            <option value="{{ $o->label }}" @selected((string) $val === $o->label)>{{ $o->label }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="jawaban[{{ $id }}]" value="{{ $val }}" class="{{ $input }}" />
                @endif
                @break

            @case('pilihan_ganda')
                @php
                    $opsiMulti = $p->opsis->map(fn ($o) => [
                        'nama' => $o->label,
                        'eksl' => in_array(Str::lower(trim($o->label)), ['tidak ada', 'belum ada'], true),
                    ])->values();
                @endphp
                @include('admin.ground-check._multi_dropdown', ['nm' => "jawaban[{$id}]", 'opsiMulti' => $opsiMulti, 'dipilih' => $val])
                @break

            @case('angka')
                <input type="number" step="{{ $angkaKoordinat ? 'any' : '1' }}" @if (! $angkaKoordinat) min="0" @endif
                       name="jawaban[{{ $id }}]" value="{{ $val }}"
                       @if ($lat) data-gc-lat @endif @if ($lng) data-gc-lng @endif
                       class="{{ $input }}" />
                @break

            @case('tanggal')
                <input type="date" name="jawaban[{{ $id }}]" value="{{ $val }}" class="{{ $input }}" />
                @break

            @case('koordinat')
                <div class="space-y-2">
                    <div id="gc-map" class="w-full h-64 rounded-lg border border-outline-variant/50 z-0"></div>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly data-gc-koordinat name="jawaban[{{ $id }}]" value="{{ $val }}"
                               placeholder="Klik peta untuk menandai lokasi" class="{{ $input }} bg-surface-container-low" />
                        <button type="button" id="gc-lokasi-saya"
                                class="shrink-0 inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-xs font-medium transition-colors">
                            <span class="material-symbols-outlined text-[16px]">my_location</span> Lokasi saya
                        </button>
                    </div>
                </div>
                @break

            @case('foto')
                <div>
                    <input type="file" name="jawaban[{{ $id }}]" accept="image/*" capture="environment"
                           class="hidden" data-gc-file onchange="gcPreview(this)" />
                    <button type="button" onclick="gcBukaKamera(this)"
                            class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-xs font-medium transition-colors">
                        <span class="material-symbols-outlined text-[16px]">photo_camera</span> Ambil Foto
                    </button>
                    <img @if (! empty($nilai[$id])) src="{{ asset('storage/' . $nilai[$id]) }}" @else hidden @endif
                         alt="Pratinjau {{ $p->nama }}" class="gc-preview mt-2 h-32 rounded-lg border border-outline-variant/50 object-cover" />
                </div>
                @break

            @default
                <input type="text" name="jawaban[{{ $id }}]" value="{{ $val }}" class="{{ $input }}" />
        @endswitch

        @if ($pesan)
            <p class="text-xs text-error mt-1">{{ $pesan }}</p>
        @endif
    </div>
</div>
