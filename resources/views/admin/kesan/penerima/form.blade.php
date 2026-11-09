@extends('layouts.admin')

@section('page_title', 'KESAN')

@section('content')
@php
    $edit = (bool) $penerima;
    $inp = 'w-full rounded-xl border border-outline-variant/60 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
    $lbl = 'block text-xs font-semibold text-on-surface-variant mb-1';
@endphp
<div class="space-y-4">
    @include('admin.kesan._nav', ['aktif' => 'penerima'])
    <a href="{{ route('kesan-admin.penerima.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:underline">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>Kembali ke Calon Penerima</a>

    @if ($errors->any())
        <div class="rounded-xl bg-error-container text-on-error-container text-xs px-4 py-3 space-y-0.5">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <form method="POST" enctype="multipart/form-data" class="grid xl:grid-cols-3 gap-4 items-start"
        action="{{ $edit ? route('kesan-admin.penerima.update', $penerima) : route('kesan-admin.penerima.store') }}"
        x-data="{ prev: @js($edit ? $penerima->fotoUrl() : null), hapus: false }">
        @csrf
        @if ($edit) @method('PUT') @endif

        <section class="rounded-2xl bg-white border border-outline-variant/40 p-4 space-y-3">
            <h3 class="text-sm font-extrabold">Foto</h3>
            <div class="flex items-center gap-4">
                <div class="w-24 h-24 rounded-2xl overflow-hidden bg-brand-soft flex items-center justify-center shrink-0">
                    <img x-show="prev && !hapus" :src="prev" alt="Pratinjau" class="w-full h-full object-cover">
                    <span x-show="!prev || hapus" class="material-symbols-outlined text-[40px] text-brand/50">image</span>
                </div>
                <div class="text-xs text-on-surface-variant space-y-2 min-w-0">
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-brand file:text-white file:px-3 file:py-1.5 file:text-xs file:font-semibold"
                        @change="if ($event.target.files[0]) { prev = URL.createObjectURL($event.target.files[0]); hapus = false }">
                    <p>JPG/PNG/WebP, maks. 2 MB. Pastikan penerima bersedia fotonya ditampilkan.</p>
                    @if ($edit && $penerima->foto)
                        <label class="flex items-center gap-2"><input type="checkbox" name="hapus_foto" value="1" x-model="hapus" class="rounded border-outline-variant text-brand focus:ring-brand">Hapus foto saat ini</label>
                    @endif
                </div>
            </div>
            <label class="flex items-center gap-2 pt-2 text-sm"><input type="checkbox" name="aktif" value="1" class="rounded border-outline-variant text-brand focus:ring-brand" @checked(old('aktif', $penerima->aktif ?? true))>Aktif (tampil ke Filantropi)</label>
        </section>

        <section class="rounded-2xl bg-white border border-outline-variant/40 p-4 space-y-3 xl:col-span-2">
            <h3 class="text-sm font-extrabold">Data calon penerima</h3>
            <div class="grid sm:grid-cols-2 gap-3">
                <div><label class="{{ $lbl }}">Nama (keluarga / perorangan)</label><input name="nama" value="{{ old('nama', $penerima->nama ?? '') }}" class="{{ $inp }}" required></div>
                <div><label class="{{ $lbl }}">Kategori bantuan</label>
                    <select name="kesan_kategori_id" class="{{ $inp }}" required>
                        <option value="">— pilih —</option>
                        @foreach ($kategoris as $k)<option value="{{ $k->id }}" @selected((int) old('kesan_kategori_id', $penerima->kesan_kategori_id ?? 0) === $k->id)>{{ $k->nama }}</option>@endforeach
                    </select></div>
            </div>
            <div class="grid sm:grid-cols-3 gap-3">
                <div><label class="{{ $lbl }}">Kecamatan</label>
                    <select name="kecamatan" class="{{ $inp }}" required>
                        <option value="">— pilih —</option>
                        @foreach (\App\Models\KesanPenerima::KECAMATAN as $kec)<option value="{{ $kec }}" @selected(old('kecamatan', $penerima->kecamatan ?? '') === $kec)>{{ $kec }}</option>@endforeach
                    </select></div>
                <div><label class="{{ $lbl }}">Kelurahan</label><input name="kelurahan" value="{{ old('kelurahan', $penerima->kelurahan ?? '') }}" class="{{ $inp }}"></div>
                <div><label class="{{ $lbl }}">Prioritas</label>
                    <select name="prioritas" class="{{ $inp }}">
                        @foreach (\App\Models\KesanPenerima::PRIORITAS as $pk => $pv)<option value="{{ $pk }}" @selected(old('prioritas', $penerima->prioritas ?? 'sedang') === $pk)>{{ $pv['label'] }}</option>@endforeach
                    </select></div>
            </div>
            <div class="grid sm:grid-cols-3 gap-3">
                <div><label class="{{ $lbl }}">No. KK (internal, tidak tampil ke Filantropi)</label><input name="no_kk" inputmode="numeric" maxlength="16" value="{{ old('no_kk', $penerima->no_kk ?? '') }}" class="{{ $inp }}"></div>
                <div><label class="{{ $lbl }}">Jumlah jiwa</label><input type="number" min="1" max="50" name="jumlah_jiwa" value="{{ old('jumlah_jiwa', $penerima->jumlah_jiwa ?? '') }}" class="{{ $inp }}"></div>
                <div><label class="{{ $lbl }}">Estimasi bantuan (Rp)</label><input type="number" min="0" name="estimasi_biaya" value="{{ old('estimasi_biaya', $penerima->estimasi_biaya ?? 0) }}" class="{{ $inp }}"></div>
            </div>
            <div><label class="{{ $lbl }}">Profil singkat</label><textarea name="profil" rows="4" maxlength="2000" class="{{ $inp }}" placeholder="Kondisi keluarga / penerima. Jangan cantumkan alamat lengkap atau NIK.">{{ old('profil', $penerima->profil ?? '') }}</textarea></div>
            <div><label class="{{ $lbl }}">Jenis bantuan yang dibutuhkan</label><input name="kebutuhan" maxlength="1000" value="{{ old('kebutuhan', $penerima->kebutuhan ?? '') }}" class="{{ $inp }}" placeholder="Contoh: Perbaikan rumah (atap, dinding, sanitasi)"></div>
        </section>

        <div class="xl:col-span-3 flex justify-end gap-2">
            <a href="{{ route('kesan-admin.penerima.index') }}" class="px-4 py-2.5 rounded-xl border border-outline-variant/60 text-sm font-semibold hover:bg-surface-container">Batal</a>
            <button class="px-5 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-primary">{{ $edit ? 'Simpan perubahan' : 'Tambah penerima' }}</button>
        </div>
    </form>
</div>
@endsection
