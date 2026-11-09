@extends('ngopi-pagi.layout')

@section('title', 'Mulai konsultasi - Ngopi Pagi')

@section('content')
@php
    $inp = 'w-full rounded-xl border border-outline-variant/70 bg-white px-4 py-3 text-base outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20';
    $lbl = 'block text-sm font-semibold mb-1.5';
    $kembali = $spesialisasi ? route('ngopi-pagi.spesialisasi', $spesialisasi->slug) : route('ngopi-pagi.index');
    $aksi = route('ngopi-pagi.store', $konselor ? ['konselor' => $konselor->id] : []);
    $spId = old('spesialisasi_id', $spesialisasi?->id);
@endphp

{{-- ============ HERO ============ --}}
<section class="np-hero w-full text-on-primary pt-28 pb-8 sm:pt-32 sm:pb-10 md:pt-36 md:pb-12">
    <div class="np-hero-icon" aria-hidden="true">
        <span class="material-symbols-outlined">edit_note</span>
    </div>
    <div class="relative z-10 max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-xs text-center">
        <span class="inline-block font-label-sm text-label-sm tracking-widest uppercase text-secondary-fixed-dim">Ngopi Pagi</span>
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-lg md:text-headline-lg font-bold tracking-tight">Data awal konsultasi</h1>
        <p class="max-w-xl mx-auto font-body-md text-body-md text-on-primary/85">Petugas akan menyapa Anda di ruang obrolan.</p>
    </div>
</section>

{{-- ============ BREADCRUMB ============ --}}
<div class="w-full bg-surface-container-lowest border-b border-outline-variant/20">
    <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop py-space-sm flex items-center gap-x-space-2xs gap-y-1 flex-wrap font-label-sm text-label-sm text-on-surface-variant">
        <a href="{{ route('home') }}" class="hover:text-secondary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <a href="{{ route('ngopi-pagi.index') }}" class="hover:text-secondary transition-colors">Ngopi Pagi</a>
        @if ($spesialisasi)
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ $kembali }}" class="hover:text-secondary transition-colors">{{ $spesialisasi->nama }}</a>
        @endif
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-primary font-bold">Data awal konsultasi</span>
    </div>
</div>

{{-- ============ KONTEN ============ --}}
<section class="w-full pt-space-lg pb-28 md:py-space-3xl">
    <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
        <div class="np-page-card grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-start">

            {{-- Konselor terpilih --}}
            <aside class="lg:col-span-4 lg:sticky lg:top-28 rounded-xl bg-surface-container-lowest border border-outline-variant/30 p-5">
                @if ($konselor)
                    @php $st = $konselor->statusCfg(); @endphp
                    <div class="flex gap-4">
                        <x-ngopi-avatar :konselor="$konselor" size="w-14 h-14 sm:w-20 sm:h-20" />
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold text-on-surface-variant">Anda akan chat dengan</p>
                            <h2 class="text-[15px] font-extrabold text-primary leading-snug">{{ $konselor->nama_tampilan }}</h2>
                            <p class="text-xs text-on-surface-variant">{{ $konselor->headline }}@if ($konselor->pengalaman_tahun) &middot; {{ $konselor->pengalaman_tahun }} tahun @endif</p>
                            <span class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $st['chip'] }}"><span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>{{ $st['label'] }}</span>
                        </div>
                    </div>
                    @if ($konselor->bio)<p class="mt-4 text-[13px] text-on-surface-variant leading-relaxed line-clamp-3 sm:line-clamp-5">{{ $konselor->bio }}</p>@endif
                @else
                    <div class="flex gap-4 items-center">
                        <span class="w-16 h-16 shrink-0 rounded-2xl bg-surface-container text-secondary flex items-center justify-center"><span class="material-symbols-outlined text-[32px]">support_agent</span></span>
                        <div><h2 class="text-[15px] font-extrabold text-primary">Admin Ngopi Pagi</h2><p class="text-xs text-on-surface-variant mt-0.5">Admin akan membantu mengarahkan Anda ke konselor yang tepat.</p></div>
                    </div>
                @endif
                @if ($spesialisasi)
                    <p class="mt-4 pt-4 border-t border-outline-variant/40 text-xs text-on-surface-variant">Topik: <b class="text-on-surface">{{ $spesialisasi->nama }}</b></p>
                @endif
            </aside>

            {{-- Formulir --}}
            <form method="POST" action="{{ $aksi }}" class="lg:col-span-8 space-y-5 relative max-sm:bg-white max-sm:border max-sm:border-outline-variant/40 max-sm:rounded-xl max-sm:p-4" novalidate>
                @csrf
                <input type="hidden" name="spesialisasi_id" value="{{ $spId }}">

                <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $lbl }}" for="kecamatan">Kecamatan</label>
                        <select id="kecamatan" name="kecamatan" class="{{ $inp }}" required>
                            <option value="">Pilih kecamatan</option>
                            @foreach ($daftarKecamatan as $kc)
                                <option value="{{ $kc }}" @selected(old('kecamatan') === $kc)>{{ $kc }}</option>
                            @endforeach
                        </select>
                        @error('kecamatan')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="kelurahan">Kelurahan</label>
                        <select id="kelurahan" name="kelurahan" class="{{ $inp }} disabled:bg-surface-container disabled:text-on-surface-variant" required disabled>
                            <option value="">Pilih kecamatan dulu</option>
                        </select>
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach (['0' => 'Tidak mendesak', '1' => 'Ya, segera'] as $v => $t)
                            <label class="cursor-pointer">
                                <input type="radio" name="butuh_segera" value="{{ $v }}" class="peer sr-only" @checked((string) old('butuh_segera', '0') === (string) $v)>
                                <div class="rounded-xl border-2 border-outline-variant/60 py-3 text-center text-sm font-semibold peer-checked:border-secondary peer-checked:bg-surface-container peer-checked:text-primary peer-focus-visible:ring-2 peer-focus-visible:ring-secondary/40">{{ $t }}</div>
                            </label>
                        @endforeach
                    </div>
                    @error('butuh_segera')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                </fieldset>

                <label class="flex items-start gap-3 text-[13px] leading-relaxed">
                    <input type="checkbox" name="persetujuan" value="1" class="mt-1 rounded border-outline-variant text-secondary focus:ring-secondary" @checked(old('persetujuan'))>
                    <span>Saya setuju data ini digunakan untuk kepentingan pelayanan dan dijaga kerahasiaannya oleh LK3 Dinas Sosial Kota Bogor.</span>
                </label>
                @error('persetujuan')<p class="text-xs text-error -mt-3">{{ $message }}</p>@enderror

                <p class="text-xs text-on-surface-variant border-t border-outline-variant/40 pt-4">Jika Anda atau seseorang berada dalam bahaya saat ini, hubungi <a href="tel:112" class="font-bold text-secondary hover:underline">112</a> terlebih dahulu.</p>

                <button type="submit" class="w-full sm:w-auto sm:min-w-[240px] inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-lg bg-secondary hover:bg-primary text-on-primary text-sm font-bold shadow-sm active:scale-[.98] transition">
                    <span class="material-symbols-outlined text-[20px]">chat</span>Mulai obrolan
                </button>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var W = @json(config('ngopi-wilayah')), kc = document.getElementById('kecamatan'), kl = document.getElementById('kelurahan');
    var lama = @json(old('kelurahan'));
    function isi() {
        var d = W[kc.value] || [];
        kl.innerHTML = '';
        kl.add(new Option(kc.value ? 'Pilih kelurahan' : 'Pilih kecamatan dulu', ''));
        d.forEach(function (n) { var o = new Option(n, n); if (n === lama) o.selected = true; kl.add(o); });
        kl.disabled = !kc.value;
    }
    kc.addEventListener('change', function () { lama = null; isi(); });
    isi();
})();
</script>
@endpush