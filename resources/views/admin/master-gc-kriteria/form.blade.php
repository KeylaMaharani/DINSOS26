@extends('layouts.admin')

@php
    use App\Models\GcKriteriaParameter as GcP;

    $isEdit = $parameter->exists;
    $dariMaster = (bool) $parameter->sumber;

    $opsiAwal = old('opsi', $isEdit
        ? $parameter->opsis->map(fn ($o) => ['id' => $o->id, 'label' => $o->label, 'nilai' => $o->nilai])->all()
        : []);
    $opsiAwal = collect($opsiAwal)->map(fn ($o) => [
        'id' => $o['id'] ?? null,
        'label' => $o['label'] ?? '',
        'nilai' => $o['nilai'] ?? '',
    ])->values()->all();

    if (! $dariMaster && count($opsiAwal) === 0) {
        $opsiAwal = [['id' => null, 'label' => '', 'nilai' => ''], ['id' => null, 'label' => '', 'nilai' => '']];
    }

    $in = 'w-full px-3 py-2 rounded-lg border border-outline-variant/60 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/30';
@endphp

@section('title', $isEdit ? 'Edit Parameter Ground Check' : 'Tambah Parameter Ground Check')
@section('page_title', 'Master Kriteria Ground Check — ' . ($isEdit ? 'Edit' : 'Tambah'))

@section('content')
<div class="max-w-4xl space-y-5">
    <a href="{{ route('master.gc-kriteria.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
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
          action="{{ $isEdit ? route('master.gc-kriteria.update', $parameter) : route('master.gc-kriteria.store') }}"
          x-data="{
              tipe: @js(old('tipe_input', $parameter->tipe_input)),
              dariMaster: @js($dariMaster),
              rows: @js($opsiAwal).map((r, i) => ({ ...r, k: i })),
              n: {{ count($opsiAwal) }},
              get butuh() { return !this.dariMaster && ['pilihan', 'pilihan_ganda'].includes(this.tipe); },
              tambah() { this.rows.push({ id: null, label: '', nilai: '', k: this.n++ }); }
          }"
          class="space-y-5">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 grid grid-cols-1 sm:grid-cols-6 gap-4">
            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold mb-1">Kelompok *</label>
                <select name="kelompok" class="{{ $in }}">
                    @foreach (GcP::KELOMPOK as $k => $label)
                        <option value="{{ $k }}" @selected(old('kelompok', $parameter->kelompok) === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold mb-1">Jenis isian *</label>
                @if ($dariMaster)
                    <input type="hidden" name="tipe_input" value="{{ $parameter->tipe_input }}">
                    <select class="{{ $in }} bg-surface-container" disabled>
                        <option>{{ GcP::TIPE_INPUT[$parameter->tipe_input] ?? $parameter->tipe_input }}</option>
                    </select>
                @else
                    <select name="tipe_input" x-model="tipe" class="{{ $in }}">
                        @foreach (GcP::TIPE_INPUT as $k => $label)
                            <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="sm:col-span-6">
                <label class="block text-xs font-semibold mb-1">Pertanyaan *</label>
                <input type="text" name="nama" value="{{ old('nama', $parameter->nama) }}" class="{{ $in }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold mb-1">Bobot *</label>
                <input type="number" step="0.01" min="0" max="99.99" name="bobot" value="{{ old('bobot', $parameter->bobot) }}" class="{{ $in }}">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold mb-1">Urutan *</label>
                <input type="number" min="1" name="urutan" value="{{ old('urutan', $parameter->urutan) }}" class="{{ $in }}">
            </div>
            <div class="sm:col-span-2 flex items-end">
                <label class="inline-flex items-center gap-2 text-sm pb-2">
                    <input type="checkbox" name="aktif" value="1" class="rounded" @checked(old('aktif', $parameter->aktif))>
                    Tampil di form entry
                </label>
            </div>

            <div class="sm:col-span-6">
                <label class="block text-xs font-semibold mb-1">Keterangan</label>
                <textarea name="keterangan" rows="2" class="{{ $in }}">{{ old('keterangan', $parameter->keterangan) }}</textarea>
            </div>

            @if ($parameter->pbiParameter)
                <p class="sm:col-span-6 text-xs text-on-surface-variant">
                    Padanan PBI: <strong>{{ $parameter->pbiParameter->nama }}</strong>
                </p>
            @endif
        </div>

        @if ($dariMaster)
            <div class="px-4 py-3 rounded-lg bg-surface-container-low border border-outline-variant/40 text-xs">
                Pilihan jawaban diambil dari <strong>{{ GcP::SUMBER[$parameter->sumber] ?? $parameter->sumber }}</strong>.
                Kelola di menu Master Lapangan Usaha / Master Keterampilan. Di sini hanya bobot, urutan, dan status yang bisa diubah.
            </div>
        @endif

        {{-- Pilihan jawaban (hanya untuk tipe pilihan/pilihan ganda yang bukan dari master) --}}
        <div x-show="butuh" x-cloak class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-bold">Pilihan jawaban</h3>
                    <p class="text-xs text-on-surface-variant">Minimal 2. Nilai (indeks) boleh dikosongkan bila belum ditentukan.</p>
                </div>
                <button type="button" @click="tambah()" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs border border-outline-variant/50 hover:bg-surface-container-low">
                    <span class="material-symbols-outlined text-[16px]">add</span> Tambah pilihan
                </button>
            </div>

            <template x-for="(r, i) in rows" :key="r.k">
                <div class="flex items-center gap-2 mb-2">
                    <input type="hidden" :name="`opsi[${i}][id]`" :value="r.id ?? ''">
                    <input type="text" :name="`opsi[${i}][label]`" x-model="r.label" placeholder="Label pilihan" class="{{ $in }}">
                    <input type="number" step="0.01" min="0" max="99.99" :name="`opsi[${i}][nilai]`" x-model="r.nilai" placeholder="Nilai" class="{{ $in }} !w-28">
                    <button type="button" @click="rows.splice(i, 1)" class="p-1 text-error" title="Hapus baris">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </div>
            </template>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('master.gc-kriteria.index') }}" class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</a>
            <button type="submit" class="px-5 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">Simpan</button>
        </div>
    </form>
</div>
@endsection 
