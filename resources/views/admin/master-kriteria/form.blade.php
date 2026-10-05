@extends('layouts.admin')

@php
    $isEdit = $parameter->exists;
    $opsiRows = old('opsi');
    if ($opsiRows === null) {
        $opsiRows = $parameter->opsis->map(fn ($o) => [
            'id' => $o->id,
            'label' => $o->label,
            'indeks_terintegrasi' => (string) $o->indeks_terintegrasi,
        ])->values()->all();
    }
    if (empty($opsiRows)) {
        $opsiRows = [
            ['id' => null, 'label' => '', 'indeks_terintegrasi' => ''],
            ['id' => null, 'label' => '', 'indeks_terintegrasi' => ''],
        ];
    }
    $opsiRows = array_values($opsiRows);
@endphp

@section('title', $isEdit ? 'Edit Parameter' : 'Tambah Parameter')
@section('page_title', 'Master Kriteria — ' . ($isEdit ? 'Edit Parameter' : 'Tambah Parameter'))

@section('content')
<div class="max-w-4xl space-y-5">
    <a href="{{ route('master.kriteria.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
    </a>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $isEdit ? route('master.kriteria.update', $parameter) : route('master.kriteria.store') }}"
          class="space-y-5">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold mb-1">Pertanyaan (yang dilihat masyarakat)</label>
                <input type="text" name="nama" value="{{ old('nama', $parameter->nama) }}" required
                       class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Indeks</label>
                <input type="number" step="0.01" min="0" name="indeks" value="{{ old('indeks', $parameter->indeks) }}" required
                       class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Bobot</label>
                <input type="number" step="0.01" min="0" name="bobot" value="{{ old('bobot', $parameter->bobot) }}" required
                       class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Nomor urut pertanyaan</label>
                <input type="number" min="1" name="urutan" value="{{ old('urutan', $parameter->urutan) }}" required
                       class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $isEdit ? $parameter->aktif : true)) class="rounded" />
                    Tampilkan pertanyaan ini di form masyarakat
                </label>
            </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-bold">Pilihan Jawaban</h3>
                <button type="button" id="opsi-tambah"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-xs font-medium">
                    <span class="material-symbols-outlined text-[16px]">add</span> Tambah Pilihan
                </button>
            </div>
            <p class="text-xs text-on-surface-variant mb-3">
                Pilihan akan tampil di dropdown sesuai urutan di bawah ini. "Nilai" dipakai untuk menghitung skor
                dan tidak terlihat oleh masyarakat maupun kelurahan.
            </p>

            <div class="grid grid-cols-12 gap-2 text-[11px] font-semibold text-on-surface-variant uppercase mb-1">
                <span class="col-span-1 text-center">No</span>
                <span class="col-span-7">Pilihan jawaban</span>
                <span class="col-span-3">Nilai</span>
                <span class="col-span-1"></span>
            </div>

            <div id="opsi-list" class="space-y-2">
                @foreach ($opsiRows as $i => $row)
                    <div class="opsi-row grid grid-cols-12 gap-2 items-center">
                        <input type="hidden" name="opsi[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                        <span class="opsi-no col-span-1 text-xs text-center text-on-surface-variant"></span>
                        <input type="text" name="opsi[{{ $i }}][label]" value="{{ $row['label'] ?? '' }}" required
                               placeholder="Contoh: Milik sendiri"
                               class="col-span-7 rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                        <input type="number" step="0.01" min="0" name="opsi[{{ $i }}][indeks_terintegrasi]"
                               value="{{ $row['indeks_terintegrasi'] ?? '' }}" required placeholder="Contoh: 1,00"
                               class="col-span-3 rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                        <button type="button" title="Hapus pilihan ini"
                                class="opsi-hapus col-span-1 w-8 h-8 justify-self-center rounded-full text-error hover:bg-error-container/50 disabled:opacity-30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
                @endforeach
            </div>

            <template id="opsi-template">
                <div class="opsi-row grid grid-cols-12 gap-2 items-center">
                    <input type="hidden" name="opsi[__IDX__][id]" value="">
                    <span class="opsi-no col-span-1 text-xs text-center text-on-surface-variant"></span>
                    <input type="text" name="opsi[__IDX__][label]" required placeholder="Contoh: Milik sendiri"
                           class="col-span-7 rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                    <input type="number" step="0.01" min="0" name="opsi[__IDX__][indeks_terintegrasi]" required placeholder="Contoh: 1,00"
                           class="col-span-3 rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                    <button type="button" title="Hapus pilihan ini"
                            class="opsi-hapus col-span-1 w-8 h-8 justify-self-center rounded-full text-error hover:bg-error-container/50 disabled:opacity-30 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
            </template>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('master.kriteria.index') }}" class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</a>
            <button type="submit" class="px-5 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">Simpan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var list = document.getElementById('opsi-list');
    var tpl = document.getElementById('opsi-template').innerHTML;
    var nextIndex = {{ count($opsiRows) }};

    function refresh() {
        var rows = list.querySelectorAll('.opsi-row');
        rows.forEach(function (row, i) {
            row.querySelector('.opsi-no').textContent = i + 1;
            row.querySelector('.opsi-hapus').disabled = rows.length <= 2; // minimal 2 pilihan
        });
    }

    document.getElementById('opsi-tambah').addEventListener('click', function () {
        var wrap = document.createElement('div');
        wrap.innerHTML = tpl.replace(/__IDX__/g, nextIndex++).trim();
        list.appendChild(wrap.firstElementChild);
        refresh();
    });

    list.addEventListener('click', function (e) {
        var btn = e.target.closest('.opsi-hapus');
        if (!btn || btn.disabled) return;
        btn.closest('.opsi-row').remove();
        refresh();
    });

    refresh();
})();
</script>
@endpush
