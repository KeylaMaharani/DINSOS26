@extends('ngopi-pagi.layout')

@section('title', 'Mulai konsultasi - Ngopi Pagi')

@section('content')
@php
    $inp = 'w-full rounded-xl border border-outline-variant/70 bg-white px-4 py-3 text-[15px] outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
    $lbl = 'block text-sm font-semibold mb-1.5';
@endphp
<header class="bg-gradient-to-br from-primary to-brand text-white px-5 pt-[calc(env(safe-area-inset-top,0px)+16px)] pb-6">
    <a href="{{ route('ngopi-pagi.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/80 hover:text-white">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>Kembali
    </a>
    <h1 class="mt-4 text-2xl font-extrabold text-crema">Mulai konsultasi</h1>
    <p class="mt-1 text-sm text-white/85">Isi data singkat. Petugas akan menyapa Anda di ruang obrolan.</p>
</header>

<main class="px-5 py-6">
    <form method="POST" action="{{ route('ngopi-pagi.store') }}" class="space-y-4" novalidate>
        @csrf

        {{-- Honeypot: tersembunyi bagi manusia, bot biasanya mengisinya --}}
        <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <div>
            <label class="{{ $lbl }}" for="nama">Nama atau inisial</label>
            <input id="nama" name="nama" value="{{ old('nama') }}" maxlength="100" class="{{ $inp }}" required>
            @error('nama')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $lbl }}" for="kontak">Nomor yang dapat dihubungi</label>
            <input id="kontak" name="kontak" type="tel" inputmode="tel" value="{{ old('kontak') }}" maxlength="20" placeholder="08xxxxxxxxxx" class="{{ $inp }}" required>
            @error('kontak')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="{{ $lbl }}" for="kecamatan">Kecamatan</label>
                <select id="kecamatan" name="kecamatan" class="{{ $inp }}" required>
                    <option value="">Pilih</option>
                    @foreach ($daftarKecamatan as $kc)
                        <option value="{{ $kc }}" @selected(old('kecamatan') === $kc)>{{ $kc }}</option>
                    @endforeach
                </select>
                @error('kecamatan')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $lbl }}" for="kelurahan">Kelurahan</label>
                <input id="kelurahan" name="kelurahan" value="{{ old('kelurahan') }}" maxlength="100" class="{{ $inp }}" required>
                @error('kelurahan')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="{{ $lbl }}" for="kebutuhan">Ceritakan permasalahan Anda</label>
            <textarea id="kebutuhan" name="kebutuhan" rows="5" maxlength="2000" class="{{ $inp }}" required>{{ old('kebutuhan') }}</textarea>
            @error('kebutuhan')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>

        <fieldset>
            <legend class="{{ $lbl }}">Apakah perlu penanganan segera?</legend>
            <div class="grid grid-cols-2 gap-3">
                @foreach (['0' => 'Tidak mendesak', '1' => 'Ya, segera'] as $v => $t)
                    <label class="cursor-pointer">
                        <input type="radio" name="butuh_segera" value="{{ $v }}" class="peer sr-only" @checked((string) old('butuh_segera', '0') === (string) $v)>
                        <div class="rounded-xl border-2 border-outline-variant/60 py-3 text-center text-sm font-semibold peer-checked:border-brand peer-checked:bg-brand-soft peer-focus-visible:ring-2 peer-focus-visible:ring-brand/40">{{ $t }}</div>
                    </label>
                @endforeach
            </div>
            @error('butuh_segera')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </fieldset>

        <label class="flex items-start gap-3 text-[13px] leading-relaxed">
            <input type="checkbox" name="persetujuan" value="1" class="mt-1 rounded border-outline-variant text-brand focus:ring-brand" @checked(old('persetujuan'))>
            <span>Saya setuju data ini digunakan untuk kepentingan pelayanan dan dijaga kerahasiaannya oleh LK3 Dinas Sosial Kota Bogor.</span>
        </label>
        @error('persetujuan')<p class="text-xs text-error -mt-2">{{ $message }}</p>@enderror

        <p class="text-xs text-on-surface-variant border-t border-outline-variant/40 pt-3">Jika Anda atau seseorang berada dalam bahaya saat ini, hubungi <b>112</b> terlebih dahulu.</p>

        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-brand text-white text-sm font-extrabold shadow-lg active:scale-[.98] transition-transform">
            <span class="material-symbols-outlined text-[20px]">chat</span>Kirim &amp; mulai obrolan
        </button>
    </form>
</main>
@endsection
