{{--
    Dropdown berantai Kecamatan -> Desa/Kelurahan (butuh Alpine.js, sama seperti halaman profil).
    Sumber data: config/wilayah.php.

    Pemakaian (di dalam grid 2 kolom):
        <x-wilayah-select :kec="$data->kecamatan" :kel="$data->desa_kelurahan" />

    Nama field bisa diganti: kec-name="kecamatan" kel-name="desa_kelurahan".
    Tidak wajib diisi: :required="false".  Gaya input kustom: :cls="$kelasInput".
    Isi lama yang tidak ada di config tetap ditampilkan supaya tidak hilang.
--}}
@props([
    'kec' => '',
    'kel' => '',
    'kecName' => 'kecamatan',
    'kelName' => 'desa_kelurahan',
    'cls' => 'w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm',
    'disabled' => false,
    'required' => true,
])
@php
    $wilayah = config('wilayah.kecamatan', []);
    $kecV = strtoupper(trim((string) old($kecName, $kec)));
    $kelV = strtoupper(trim((string) old($kelName, $kel)));

    if ($kecV !== '') {
        $wilayah[$kecV] = $wilayah[$kecV] ?? [];
        if ($kelV !== '' && ! in_array($kelV, $wilayah[$kecV], true)) {
            $wilayah[$kecV][] = $kelV;
        }
    }
@endphp

<div class="contents" x-data="{
        wilayah: @js($wilayah),
        kec: @js($kecV),
        kel: @js($kelV),
        get kelOpts() { return this.wilayah[this.kec] || []; },
        gantiKec() { if (!this.kelOpts.includes(this.kel)) this.kel = ''; }
    }">
    <div>
        <label class="block text-xs text-on-surface-variant mb-1">Kecamatan</label>
        <select name="{{ $kecName }}" x-model="kec" @change="gantiKec()" class="{{ $cls }}" @disabled($disabled) @required($required)>
            <option value="">- Pilih -</option>
            @foreach (array_keys($wilayah) as $k)
                <option value="{{ $k }}">{{ $k }}</option>
            @endforeach
        </select>
        @error($kecName)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs text-on-surface-variant mb-1">Desa/Kelurahan</label>
        <select name="{{ $kelName }}" x-model="kel" class="{{ $cls }}" @disabled($disabled) @required($required)>
            <option value="">- Pilih -</option>
            <template x-for="k in kelOpts" :key="k">
                <option :value="k" x-text="k" :selected="k === kel"></option>
            </template>
        </select>
        @error($kelName)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
