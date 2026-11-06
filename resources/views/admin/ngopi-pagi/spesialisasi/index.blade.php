@extends('layouts.admin')

@section('page_title', 'Ngopi Pagi')

@section('content')
@php
    $S = \App\Models\NgopiPagiSpesialisasi::class;
    $inp = 'w-full rounded-xl border border-outline-variant/60 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
    $lbl = 'block text-xs font-semibold text-on-surface-variant mb-1';
    $kosong = ['id' => null, 'nama' => '', 'deskripsi' => '', 'ikon' => 'psychology', 'warna' => 'sky', 'kategori_awal' => '', 'urutan' => 0, 'aktif' => true];
@endphp
<div class="space-y-4" x-data="{
    open: false, base: @js(route('ngopi-pagi.admin.spesialisasi.store')), blank: @js($kosong), f: @js($kosong),
    tambah() { this.f = { ...this.blank }; this.open = true },
    ubah(s) { this.f = { id: s.id, nama: s.nama, deskripsi: s.deskripsi || '', ikon: s.ikon, warna: s.warna, kategori_awal: s.kategori_awal || '', urutan: s.urutan, aktif: !!s.aktif }; this.open = true }
}">
    @include('admin.ngopi-pagi._nav', ['aktif' => 'spesialisasi'])

    @if ($errors->any())
        <div class="rounded-xl bg-error-container text-on-error-container text-xs px-4 py-3 space-y-0.5">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex flex-wrap items-center gap-3">
        <span class="w-11 h-11 rounded-xl bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined">category</span></span>
        <div class="min-w-0 flex-1">
            <h2 class="text-base font-extrabold">Spesialisasi</h2>
            <p class="text-xs text-on-surface-variant">Kategori yang dipilih warga di halaman awal Ngopi Pagi.</p>
        </div>
        <button type="button" @click="tambah()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-primary"><span class="material-symbols-outlined text-[18px]">add</span>Tambah spesialisasi</button>
    </div>

    <div class="rounded-2xl bg-white border border-outline-variant/40 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-surface-container-low text-[11px] uppercase tracking-wide text-on-surface-variant">
                <tr><th class="text-left px-4 py-3">Spesialisasi</th><th class="text-left px-4 py-3">Pra-kategori</th><th class="text-left px-4 py-3">Konselor</th><th class="text-left px-4 py-3">Urutan</th><th class="text-left px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                @forelse ($items as $s)
                    @php $w = $s->warna(); @endphp
                    <tr class="hover:bg-brand-soft/40">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-full {{ $w['bg'] }} flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-[22px] {{ $w['fg'] }}" style="font-variation-settings:'FILL' 1">{{ $s->ikon }}</span></span>
                                <div><p class="font-semibold">{{ $s->nama }}</p><p class="text-[11px] text-on-surface-variant">{{ $s->deskripsi }}</p></div>
                            </div>
                        </td>
                        <td class="px-4 py-3">@if ($s->kategori_awal)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ \App\Models\NgopiPagiKonsultasi::KATEGORI[$s->kategori_awal]['chip'] }}">Kategori {{ $s->kategori_awal }}</span>@else<span class="text-xs text-on-surface-variant">-</span>@endif</td>
                        <td class="px-4 py-3">{{ $s->konselors_count }}</td>
                        <td class="px-4 py-3">{{ $s->urutan }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $s->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">{{ $s->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="ubah(@js($s->only(['id', 'nama', 'deskripsi', 'ikon', 'warna', 'kategori_awal', 'urutan', 'aktif'])))" class="px-3 py-1.5 rounded-lg border border-outline-variant/60 text-xs font-semibold hover:bg-surface-container">Ubah</button>
                                <form method="POST" action="{{ route('ngopi-pagi.admin.spesialisasi.destroy', $s) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg border border-error/40 text-error text-xs font-semibold hover:bg-error-container/40" data-confirm="Hapus spesialisasi {{ $s->nama }}? Konselor tetap ada, hanya tautannya dilepas." data-confirm-variant="error" data-confirm-title="Hapus spesialisasi">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">Belum ada spesialisasi. Jalankan seeder atau tambahkan satu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal tambah / ubah --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="open = false">
        <div @click.outside="open = false" class="bg-white w-full max-w-lg rounded-2xl shadow-lg overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-base font-semibold" x-text="f.id ? 'Ubah spesialisasi' : 'Tambah spesialisasi'"></h3>
                <button type="button" @click="open = false" class="text-on-surface-variant" aria-label="Tutup"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form method="POST" :action="f.id ? base + '/' + f.id : base" class="p-5 space-y-3">
                @csrf
                <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
                <div><label class="{{ $lbl }}">Nama</label><input name="nama" x-model="f.nama" maxlength="80" class="{{ $inp }}" required></div>
                <div><label class="{{ $lbl }}">Deskripsi singkat</label><input name="deskripsi" x-model="f.deskripsi" maxlength="200" class="{{ $inp }}"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="{{ $lbl }}">Ikon</label>
                        <select name="ikon" x-model="f.ikon" class="{{ $inp }}">@foreach ($S::IKON as $ik)<option value="{{ $ik }}">{{ $ik }}</option>@endforeach</select></div>
                    <div><label class="{{ $lbl }}">Warna</label>
                        <select name="warna" x-model="f.warna" class="{{ $inp }}">@foreach (array_keys($S::WARNA) as $wk)<option value="{{ $wk }}">{{ ucfirst($wk) }}</option>@endforeach</select></div>
                </div>
                <div class="flex items-center gap-2 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-[22px] text-brand" x-text="f.ikon"></span>Pratinjau ikon</div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="{{ $lbl }}">Pra-kategori (opsional)</label>
                        <select name="kategori_awal" x-model="f.kategori_awal" class="{{ $inp }}"><option value="">Tidak diisi</option>@foreach (\App\Models\NgopiPagiKonsultasi::KATEGORI as $kk => $kv)<option value="{{ $kk }}">{{ $kk }} · {{ $kv['nama'] }}</option>@endforeach</select></div>
                    <div><label class="{{ $lbl }}">Urutan</label><input type="number" min="0" name="urutan" x-model="f.urutan" class="{{ $inp }}"></div>
                </div>
                <p class="text-[11px] text-on-surface-variant">Pra-kategori mengisi klasifikasi awal kasus. Admin tetap memastikannya di tahap Screening.</p>
                <label class="flex items-center gap-2 text-sm"><input type="hidden" name="aktif" value="0"><input type="checkbox" name="aktif" value="1" x-model="f.aktif" class="rounded border-outline-variant text-brand focus:ring-brand">Aktif (tampil di halaman publik)</label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-outline-variant/60 text-sm font-semibold">Batal</button>
                    <button class="px-5 py-2 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
