@extends('layouts.admin')

@section('page_title', 'KESAN')

@section('content')
@php
    $inp = 'w-full rounded-xl border border-outline-variant/60 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
    $lbl = 'block text-xs font-semibold text-on-surface-variant mb-1';
    $role = auth()->user()->role;
    $kosong = ['id' => null, 'nama' => '', 'singkatan' => '', 'ikon' => 'volunteer_activism', 'warna' => 'blue', 'urutan' => 0, 'aktif' => true];
@endphp
<div class="space-y-4" x-data="{ open: false, f: @js($kosong), action: '{{ route('kesan-admin.kategori.store') }}', put: false,
    baru() { this.f = @js($kosong); this.action = '{{ route('kesan-admin.kategori.store') }}'; this.put = false; this.open = true },
    ubah(k, url) { this.f = k; this.action = url; this.put = true; this.open = true } }">
    @include('admin.kesan._nav', ['aktif' => 'kategori'])

    @if ($errors->any())
        <div class="rounded-xl bg-error-container text-on-error-container text-xs px-4 py-3 space-y-0.5">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex flex-wrap items-center gap-3">
        <span class="w-11 h-11 rounded-xl bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined">category</span></span>
        <div class="min-w-0 flex-1 basis-48">
            <h2 class="text-base font-extrabold">Kategori Bantuan</h2>
            <p class="text-xs text-on-surface-variant">Tampil sebagai ikon pilihan di beranda Filantropi dan halaman KESAN.</p></div>
        @if ($role?->hasModule('kesan.kategori.tambah'))
            <button type="button" @click="baru()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-primary">
                <span class="material-symbols-outlined text-[18px]">add</span>Tambah kategori</button>
        @endif
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach ($items as $k)
            @php $c = $k->warnaCfg(); @endphp
            <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex items-center gap-3 {{ $k->aktif ? '' : 'opacity-60' }}">
                <span class="w-12 h-12 shrink-0 rounded-full {{ $c['solid'] }} flex items-center justify-center"><span class="material-symbols-outlined text-white" style="font-variation-settings:'FILL' 1">{{ $k->ikon }}</span></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-extrabold leading-snug">{{ $k->nama }}</p>
                    <p class="text-xs text-on-surface-variant">{{ $k->penerimas_count }} penerima · urutan {{ $k->urutan }}@unless ($k->aktif) · nonaktif @endunless</p>
                </div>
                @if ($role?->hasModule('kesan.kategori.ubah'))
                    <button type="button" class="px-3 py-1.5 rounded-lg border border-outline-variant/60 text-xs font-semibold hover:bg-surface-container"
                        @click="ubah(@js(['id' => $k->id, 'nama' => $k->nama, 'singkatan' => $k->singkatan, 'ikon' => $k->ikon, 'warna' => $k->warna, 'urutan' => $k->urutan, 'aktif' => $k->aktif]), '{{ route('kesan-admin.kategori.update', $k) }}')">Ubah</button>
                @endif
                @if ($role?->hasModule('kesan.kategori.hapus'))
                    <form method="POST" action="{{ route('kesan-admin.kategori.destroy', $k) }}">@csrf @method('DELETE')
                        <button class="px-2 py-1.5 rounded-lg border border-error/40 text-error hover:bg-error-container/40" aria-label="Hapus" data-confirm="Hapus kategori {{ $k->nama }}?" data-confirm-variant="error" data-confirm-title="Hapus kategori"><span class="material-symbols-outlined text-[18px]">delete</span></button></form>
                @endif
            </div>
        @endforeach
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <form method="POST" :action="action" @click.outside="open = false" class="bg-white w-full max-w-md rounded-xl shadow-lg p-5 space-y-3">
            @csrf
            <template x-if="put"><input type="hidden" name="_method" value="PUT"></template>
            <h3 class="text-base font-semibold" x-text="put ? 'Ubah kategori' : 'Tambah kategori'"></h3>
            <div><label class="{{ $lbl }}">Nama</label><input name="nama" x-model="f.nama" class="{{ $inp }}" required></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="{{ $lbl }}">Singkatan (label pendek)</label><input name="singkatan" x-model="f.singkatan" class="{{ $inp }}"></div>
                <div><label class="{{ $lbl }}">Urutan</label><input type="number" min="0" name="urutan" x-model="f.urutan" class="{{ $inp }}"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="{{ $lbl }}">Ikon (Material Symbols)</label><input name="ikon" x-model="f.ikon" class="{{ $inp }}" required><p class="text-[11px] text-on-surface-variant mt-1">Contoh: home, school, handshake</p></div>
                <div><label class="{{ $lbl }}">Warna</label>
                    <select name="warna" x-model="f.warna" class="{{ $inp }}">@foreach (\App\Models\KesanKategori::WARNA as $wk => $wv)<option value="{{ $wk }}">{{ $wv['label'] }}</option>@endforeach</select></div>
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="aktif" value="1" x-model="f.aktif" class="rounded border-outline-variant text-brand focus:ring-brand">Aktif</label>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" @click="open = false" class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</button>
                <button class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
