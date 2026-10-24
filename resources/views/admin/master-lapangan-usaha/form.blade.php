@extends('layouts.admin')

@php
    $ada = $lapanganUsaha->exists;
    $baris = old('pekerjaan', $ada
        ? $lapanganUsaha->pekerjaans->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama, 'aktif' => (bool) $p->aktif])->all()
        : []);
    $baris = collect($baris)->map(fn ($r) => ['id' => $r['id'] ?? null, 'nama' => $r['nama'] ?? '', 'aktif' => ! empty($r['aktif'])])->values()->all();
    $in = 'w-full px-3 py-2 rounded-lg border border-outline-variant/60 bg-white text-sm';
@endphp

@section('title', $ada ? 'Edit Lapangan Usaha' : 'Tambah Lapangan Usaha')
@section('page_title', $ada ? 'Edit Lapangan Usaha' : 'Tambah Lapangan Usaha')

@section('content')
<div class="max-w-3xl space-y-4">
    <a href="{{ route('master.lapangan-usaha.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
    </a>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <ul class="list-disc list-inside">@foreach (array_unique($errors->all()) as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $ada ? route('master.lapangan-usaha.update', $lapanganUsaha) : route('master.lapangan-usaha.store') }}"
          x-data="{ rows: @js($baris).map((r, i) => ({ ...r, k: i })), n: {{ count($baris) }},
                    tambah() { this.rows.push({ id: null, nama: '', aktif: true, k: this.n++ }); } }"
          class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 space-y-4">
        @csrf
        @if ($ada) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold mb-1">Nama lapangan usaha *</label>
                <input name="nama" value="{{ old('nama', $lapanganUsaha->nama) }}" class="{{ $in }}">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Urutan *</label>
                <input type="number" min="1" name="urutan" value="{{ old('urutan', $lapanganUsaha->urutan) }}" class="{{ $in }}">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold mb-1">Ikon (emoji)</label>
            <input name="ikon" maxlength="16" value="{{ old('ikon', $lapanganUsaha->ikon) }}" class="{{ $in }}">
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Penjelasan</label>
            <textarea name="penjelasan" rows="2" class="{{ $in }}">{{ old('penjelasan', $lapanganUsaha->penjelasan) }}</textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold mb-1">Contoh: usaha sendiri</label>
                <textarea name="contoh_berusaha_sendiri" rows="2" class="{{ $in }}">{{ old('contoh_berusaha_sendiri', $lapanganUsaha->contoh_berusaha_sendiri) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Contoh: buruh/karyawan</label>
                <textarea name="contoh_buruh" rows="2" class="{{ $in }}">{{ old('contoh_buruh', $lapanganUsaha->contoh_buruh) }}</textarea>
            </div>
        </div>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="aktif" value="1" class="rounded" @checked(old('aktif', $lapanganUsaha->aktif))> Aktif
        </label>

        <div class="pt-2 border-t border-outline-variant/40">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-bold">Daftar pekerjaan</h3>
                <button type="button" @click="tambah()" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs border border-outline-variant/50 hover:bg-surface-container-low">
                    <span class="material-symbols-outlined text-[16px]">add</span> Tambah pekerjaan
                </button>
            </div>
            <template x-for="(r, i) in rows" :key="r.k">
                <div class="flex items-center gap-2 mb-2">
                    <input type="hidden" :name="`pekerjaan[${i}][id]`" :value="r.id ?? ''">
                    <input type="text" :name="`pekerjaan[${i}][nama]`" x-model="r.nama" placeholder="Nama pekerjaan" class="{{ $in }}">
                    <label class="inline-flex items-center gap-1 text-xs whitespace-nowrap">
                        <input type="checkbox" :name="`pekerjaan[${i}][aktif]`" value="1" x-model="r.aktif" class="rounded"> Aktif
                    </label>
                    <button type="button" @click="rows.splice(i, 1)" class="p-1 text-error" title="Hapus baris">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </div>
            </template>
            <p x-show="rows.length === 0" class="text-xs text-on-surface-variant">Belum ada pekerjaan.</p>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('master.lapangan-usaha.index') }}" class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</a>
            <button class="px-5 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">Simpan</button>
        </div>
    </form>
</div>
@endsection
