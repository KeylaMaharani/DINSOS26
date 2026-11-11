@php
    // $jenis : no_kk | nama | wilayah
    // $gate  : id parameter pertanyaan Ya/Tidak pemicu
    // Dari view induk: $koreksi (boleh null), $nilai, $wilayah, $bisaEdit
    $awal = old("jawaban.{$gate}", $nilai[$gate] ?? null) === 'Tidak';
    $dis = (! $awal || ! ($bisaEdit ?? true)) ? 'disabled' : '';
    $inK = 'w-full rounded-xl border border-outline-variant px-3.5 py-3 text-base sm:text-sm bg-white focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 disabled:bg-surface-container';
    $kecSel = old('koreksi.kecamatan_baru', $koreksi->kecamatan_baru ?? '');
    $kelSel = old('koreksi.kelurahan_baru', $koreksi->kelurahan_baru ?? '');
@endphp

<div class="min-w-0 rounded-2xl bg-brand-soft/60 ring-1 ring-brand/15 p-3.5 space-y-3"
     data-gc-koreksi="{{ $jenis }}" data-gate="{{ $gate }}"
     @if ($jenis === 'wilayah') data-peta="{{ json_encode($wilayah ?? [], JSON_UNESCAPED_UNICODE) }}" @endif
     @unless ($awal) hidden @endunless>

    @if ($jenis === 'no_kk')
        <div>
            <label class="block text-sm font-semibold mb-1.5">Masukkan No KK baru <span class="text-error">*</span></label>
            <input type="text" name="koreksi[no_kk_baru]" inputmode="numeric" maxlength="16" {{ $dis }}
                   value="{{ old('koreksi.no_kk_baru', $koreksi->no_kk_baru ?? '') }}"
                   oninput="this.value=this.value.replace(/\D/g,'')" class="{{ $inK }}">
            @error('koreksi.no_kk_baru')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
        </div>

    @elseif ($jenis === 'nama')
        <div>
            <label class="block text-sm font-semibold mb-1.5">Masukkan nama <span class="text-error">*</span></label>
            <input type="text" name="koreksi[nama_baru]" maxlength="150" {{ $dis }}
                   value="{{ old('koreksi.nama_baru', $koreksi->nama_baru ?? '') }}" class="{{ $inK }}">
            @error('koreksi.nama_baru')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
        </div>

    @else
        <div>
            <label class="block text-sm font-semibold mb-1.5">Kabupaten/Kota</label>
            <input type="text" value="{{ config('wilayah.kab_kota') }}" readonly
                   class="{{ $inK }} bg-surface-container opacity-70">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1.5">Kecamatan <span class="text-error">*</span></label>
            <select name="koreksi[kecamatan_baru]" data-kor-kec {{ $dis }} class="{{ $inK }}">
                <option value="">- Pilih -</option>
                @foreach (array_keys($wilayah ?? []) as $k)
                    <option value="{{ $k }}" @selected((string) $kecSel === (string) $k)>{{ $k }}</option>
                @endforeach
            </select>
            @error('koreksi.kecamatan_baru')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1.5">Kelurahan/Desa <span class="text-error">*</span></label>
            <select name="koreksi[kelurahan_baru]" data-kor-kel data-nilai="{{ $kelSel }}" {{ $dis }} class="{{ $inK }}">
                <option value="">- Pilih kecamatan dahulu -</option>
            </select>
            @error('koreksi.kelurahan_baru')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('form-lengkapi');
            if (!form) { return; }
            var bisaEdit = !!document.getElementById('btn-submit');

            function isiKel(box, pilih) {
                var kec = box.querySelector('[data-kor-kec]');
                var kel = box.querySelector('[data-kor-kel]');
                if (!kec || !kel) { return; }
                var peta = {};
                try { peta = JSON.parse(box.dataset.peta || '{}'); } catch (e) {}
                var daftar = peta[kec.value] || [];
                kel.innerHTML = '';
                kel.add(new Option(kec.value ? '- Pilih -' : '- Pilih kecamatan dahulu -', ''));
                daftar.forEach(function (n) { kel.add(new Option(n, n)); });
                kel.value = daftar.indexOf(pilih) >= 0 ? pilih : '';
            }

            function tampil(box, on) {
                box.hidden = !on;
                box.querySelectorAll('input:not([readonly]), select').forEach(function (el) {
                    el.disabled = !on || !bisaEdit;
                });
            }

            form.querySelectorAll('[data-gc-koreksi]').forEach(function (box) {
                var gate = form.querySelector('[name="jawaban[' + box.dataset.gate + ']"]');
                if (box.dataset.gcKoreksi === 'wilayah') {
                    var kel = box.querySelector('[data-kor-kel]');
                    isiKel(box, kel ? kel.dataset.nilai : '');
                    var kec = box.querySelector('[data-kor-kec]');
                    if (kec) { kec.addEventListener('change', function () { isiKel(box, ''); }); }
                }
                tampil(box, !!gate && gate.value === 'Tidak');
            });

            form.addEventListener('change', function (e) {
                if (!e.target.name) { return; }
                form.querySelectorAll('[data-gc-koreksi]').forEach(function (box) {
                    if (e.target.name === 'jawaban[' + box.dataset.gate + ']') {
                        tampil(box, e.target.value === 'Tidak');
                    }
                });
            });
        });
        </script>
    @endpush
@endonce
