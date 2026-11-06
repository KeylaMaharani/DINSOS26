@extends('ngopi-pagi.layout')

@section('title', 'Mulai konsultasi - Ngopi Pagi')
@section('portal', '1')

@section('content')
@php
    $inp = 'w-full rounded-xl border border-outline-variant/70 bg-white px-4 py-3 text-[15px] outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
    $lbl = 'block text-sm font-semibold mb-1.5';
    $kembali = $spesialisasi ? route('ngopi-pagi.spesialisasi', $spesialisasi->slug) : route('ngopi-pagi.index');
    $aksi = route('ngopi-pagi.store', $konselor ? ['konselor' => $konselor->id] : []);
    $spId = old('spesialisasi_id', $spesialisasi?->id);
@endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <nav aria-label="Breadcrumb" class="py-3 text-xs text-on-surface-variant flex items-center gap-2 flex-wrap">
        <a href="{{ route('home') }}" class="text-brand font-semibold hover:underline">Beranda</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <a href="{{ route('ngopi-pagi.index') }}" class="text-brand font-semibold hover:underline">Ngopi Pagi</a>
        @if ($spesialisasi)
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <a href="{{ $kembali }}" class="text-brand font-semibold hover:underline">{{ $spesialisasi->nama }}</a>
        @endif
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span>Data awal konsultasi</span>
    </nav>

    <div class="mb-5">
        <h1 class="text-2xl sm:text-3xl font-extrabold">Data awal konsultasi</h1>
        <p class="text-sm text-on-surface-variant mt-1">Petugas akan menyapa Anda di ruang obrolan.</p>
    </div>

    <main class="grid lg:grid-cols-5 gap-5 lg:gap-8 items-start pb-12">
        {{-- Konselor terpilih --}}
        <aside class="lg:col-span-2 lg:sticky lg:top-20 rounded-2xl bg-white border border-outline-variant/40 p-5">
            @if ($konselor)
                @php $st = $konselor->statusCfg(); @endphp
                <div class="flex gap-4">
                    <x-ngopi-avatar :konselor="$konselor" size="w-20 h-20" />
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-on-surface-variant">Anda akan chat dengan</p>
                        <h2 class="text-[15px] font-extrabold leading-snug">{{ $konselor->nama_tampilan }}</h2>
                        <p class="text-xs text-on-surface-variant">{{ $konselor->headline }}@if ($konselor->pengalaman_tahun) &middot; {{ $konselor->pengalaman_tahun }} tahun @endif</p>
                        <span class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $st['chip'] }}"><span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>{{ $st['label'] }}</span>
                    </div>
                </div>
                @if ($konselor->bio)<p class="mt-4 text-[13px] text-on-surface-variant leading-relaxed line-clamp-5">{{ $konselor->bio }}</p>@endif
            @else
                <div class="flex gap-4 items-center">
                    <span class="w-16 h-16 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined text-[32px]">support_agent</span></span>
                    <div><h2 class="text-[15px] font-extrabold">Admin Ngopi Pagi</h2><p class="text-xs text-on-surface-variant mt-0.5">Admin akan membantu mengarahkan Anda ke konselor yang tepat.</p></div>
                </div>
            @endif
            @if ($spesialisasi)
                <p class="mt-4 pt-4 border-t border-outline-variant/40 text-xs text-on-surface-variant">Topik: <b class="text-on-surface">{{ $spesialisasi->nama }}</b></p>
            @endif
        </aside>

        {{-- Formulir --}}
        <form method="POST" action="{{ $aksi }}" class="lg:col-span-3 rounded-3xl bg-white border border-outline-variant/40 p-5 sm:p-8 space-y-5" novalidate>
            @csrf
            <input type="hidden" name="spesialisasi_id" value="{{ $spId }}">

            <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden">
                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
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
                <div class="grid grid-cols-2 gap-3 sm:max-w-md">
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
            @error('persetujuan')<p class="text-xs text-error -mt-3">{{ $message }}</p>@enderror

            <p class="text-xs text-on-surface-variant border-t border-outline-variant/40 pt-4">Jika Anda atau seseorang berada dalam bahaya saat ini, hubungi <b>112</b> terlebih dahulu.</p>

            <button type="submit" class="w-full sm:w-auto sm:min-w-[240px] inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-brand text-white text-sm font-extrabold shadow-lg hover:bg-primary active:scale-[.98] transition">
                <span class="material-symbols-outlined text-[20px]">chat</span>Mulai obrolan
            </button>
        </form>
    </main>
</div>
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
