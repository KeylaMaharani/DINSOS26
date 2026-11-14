{{-- Taruh di resources/views/masyarakat/pbi-apbd/_layanan_lampiran.blade.php
     Variabel: $pbiApbd, $bisaEdit, $faskesOpsi (array), $lampiran (['scan_ktp'|'scan_kk'|'surat_rawat_inap' => bool sudah ada])
     Data ini disimpan langsung ke kolom pbi_apbds (bukan tabel jawaban), jadi tidak ikut Ground Check. --}}
@php
    $in = 'w-full rounded-xl border border-outline-variant px-3.5 py-3 text-base sm:text-sm bg-white focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 disabled:bg-surface-container disabled:text-on-surface-variant';
    $faskesSekarang = old('nama_faskes', $pbiApbd->nama_faskes);
    $faskesOpsi = collect($faskesOpsi ?? [])->values()->all();
    if ($faskesSekarang && ! in_array($faskesSekarang, $faskesOpsi, true)) {
        $faskesOpsi[] = $faskesSekarang;
    }
    $berkas = [
        ['scan_ktp', 'Scan KTP', true],
        ['scan_kk', 'Scan Kartu Keluarga', true],
        ['surat_rawat_inap', 'Surat Keterangan Rawat Inap (opsional)', false],
    ];
@endphp

<div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6">
    <h2 class="mb-1.5 flex items-center gap-3 text-base font-extrabold text-on-surface">
        <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
            <span class="material-symbols-outlined text-[22px]">local_hospital</span>
        </span>
        Layanan Kesehatan &amp; Lampiran
    </h2>
    <p class="text-xs text-on-surface-variant mb-4">
        Bagian ini khusus untuk pengajuan PBI APBD. Isian bertanda <span class="text-error">*</span> wajib diisi.
        Berkas yang sudah ada di menu Dokumen dipakai otomatis.
    </p>

    <div class="grid grid-cols-1 gap-5 max-w-xl">
        <div class="min-w-0">
            <label for="diagnosa" class="block mb-2 text-sm font-semibold text-on-surface">
                Diagnosa <span class="text-error">*</span>
            </label>
            <textarea id="diagnosa" name="diagnosa" rows="3" maxlength="500" @disabled(! $bisaEdit) class="{{ $in }}">{{ old('diagnosa', $pbiApbd->diagnosa) }}</textarea>
            @error('diagnosa')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="min-w-0">
            <label for="nama_faskes" class="block mb-2 text-sm font-semibold text-on-surface">
                Nama Faskes <span class="text-error">*</span>
            </label>
            <select id="nama_faskes" name="nama_faskes" @disabled(! $bisaEdit) class="{{ $in }}">
                <option value="">- Pilih faskes -</option>
                @foreach ($faskesOpsi as $f)
                    <option value="{{ $f }}" @selected((string) $faskesSekarang === (string) $f)>{{ $f }}</option>
                @endforeach
            </select>
            @error('nama_faskes')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        @foreach ($berkas as [$field, $label, $wajib])
            @php $sudah = $lampiran[$field] ?? false; @endphp
            <div class="min-w-0">
                <label for="{{ $field }}" class="block mb-2 text-sm font-semibold text-on-surface">
                    {{ $label }} @if ($wajib)<span class="text-error">*</span>@endif
                </label>
                @if ($sudah)
                    <p class="flex items-center gap-1 text-xs text-success mb-1.5">
                        <span class="material-symbols-outlined text-[15px]">check_circle</span>
                        Sudah ada (pilih berkas baru hanya bila ingin mengganti)
                    </p>
                @endif
                <input type="file" id="{{ $field }}" name="{{ $field }}" accept="image/jpeg,image/png,application/pdf"
                       @disabled(! $bisaEdit)
                       class="block w-full text-sm border border-outline-variant rounded-xl bg-white file:mr-3 file:py-3 file:px-4 file:border-0 file:bg-primary file:text-white file:text-xs file:font-semibold">
                <p class="text-[11px] text-on-surface-variant mt-1">JPG, PNG, atau PDF. Maksimal 2 MB.</p>
                @error($field)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
</div>