@extends('ngopi-pagi.layout')

@section('title', $spesialisasi->nama . ' - Ngopi Pagi')

@section('content')
{{-- ============ HERO ============ --}}
<section class="np-hero w-full text-on-primary pt-28 pb-8 sm:pt-32 sm:pb-10 md:pt-36 md:pb-12">
    <div class="np-hero-icon" aria-hidden="true">
        <span class="material-symbols-outlined">{{ $spesialisasi->ikon }}</span>
    </div>
    <div class="relative z-10 max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-xs text-center">
        <span class="inline-block font-label-sm text-label-sm tracking-widest uppercase text-secondary-fixed-dim">Pilih Konselor</span>
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-lg md:text-headline-lg font-bold tracking-tight">{{ $spesialisasi->nama }}</h1>
        <p class="max-w-xl mx-auto font-body-md text-body-md text-on-primary/85">{{ $spesialisasi->deskripsi ?: 'Pilih konselor yang akan menemani Anda' }}</p>
    </div>
</section>

{{-- ============ BREADCRUMB ============ --}}
<div class="w-full bg-surface-container-lowest border-b border-outline-variant/20">
   <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop py-space-sm flex items-center gap-x-space-2xs gap-y-1 flex-wrap font-label-sm text-label-sm text-on-surface-variant">
        <a href="{{ route('home') }}" class="hover:text-secondary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <a href="{{ route('ngopi-pagi.index') }}" class="hover:text-secondary transition-colors">Ngopi Pagi</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-primary font-bold">{{ $spesialisasi->nama }}</span>
    </div>
</div>

{{-- ============ KONTEN ============ --}}
<section class="w-full pt-space-lg pb-28 md:py-space-3xl">
    <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
        <div class="np-page-card space-y-space-lg">
            @unless ($buka)
                <p class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-2.5 text-xs text-amber-900">Saat ini di luar jam layanan (Senin &ndash; Jumat, 08.00 &ndash; 15.00 WIB). Anda tetap bisa mengirim pesan, balasan diberikan di hari kerja berikutnya.</p>
            @endunless

            @if ($konselors->isEmpty())
                <div class="rounded-xl bg-surface-container-lowest border border-outline-variant/30 p-10 text-center">
                    <span class="material-symbols-outlined text-[44px] text-outline">person_search</span>
                    <p class="mt-2 text-sm font-semibold text-primary">Belum ada konselor di topik ini.</p>
                    <p class="text-[13px] text-on-surface-variant mt-1">Admin kami siap membantu mengarahkan Anda.</p>
                    <a href="{{ route('ngopi-pagi.mulai', ['s' => $spesialisasi->slug]) }}" class="mt-4 inline-flex px-5 py-2.5 rounded-lg bg-secondary hover:bg-primary text-on-primary text-sm font-bold transition-colors">Tanya admin</a>
                </div>
            @else
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="font-headline-md text-headline-md text-primary font-bold">{{ $konselors->count() }} konselor tersedia</h2>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Pilih konselor yang akan menemani Anda.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if ($konselors->count() > 4)
                            <label class="flex items-center gap-2.5 h-11 rounded-xl border border-outline-variant/70 bg-white px-4 sm:w-72 focus-within:border-secondary focus-within:ring-2 focus-within:ring-secondary/20">
                                <span class="material-symbols-outlined text-[20px] text-on-surface-variant">search</span>
                                <input id="cari" type="search" placeholder="Cari nama konselor" class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0" aria-label="Cari konselor">
                            </label>
                        @endif
                        <a href="{{ route('ngopi-pagi.index') }}" class="hidden sm:inline-flex items-center gap-1.5 shrink-0 px-4 h-11 rounded-xl border border-outline-variant/70 text-sm font-semibold hover:bg-surface-container-low">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>Semua topik
                        </a>
                    </div>
                </div>

                <div id="daftar" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-space-md">
                    @foreach ($konselors as $kn)
                        @php $st = $kn->statusCfg(); @endphp
                        <article data-nama="{{ mb_strtolower($kn->nama_tampilan . ' ' . $kn->headline) }}" class="np-step flex gap-4 hover:border-secondary-fixed-dim hover:shadow-md transition">
                            <x-ngopi-avatar :konselor="$kn" size="w-14 h-14 sm:w-20 sm:h-20" />
                            <div class="min-w-0 flex-1 flex flex-col">
                                <h3 class="text-[15px] font-extrabold text-primary leading-snug">{{ $kn->nama_tampilan }}</h3>
                                <p class="text-xs text-on-surface-variant">{{ $kn->headline }}@if ($kn->pengalaman_tahun) &middot; {{ $kn->pengalaman_tahun }} tahun pengalaman @endif</p>

                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($kn->spesialisasis->take(3) as $sp)
                                        <span class="px-2 py-0.5 rounded-md border border-outline-variant/60 text-[11px] text-on-surface-variant">{{ $sp->nama }}</span>
                                    @endforeach
                                </div>

                                @if ($kn->bio)
                                    <details class="mt-2 text-[13px]">
                                        <summary class="cursor-pointer text-secondary font-semibold text-xs">Lihat profil</summary>
                                        <p class="mt-1.5 text-on-surface-variant leading-relaxed whitespace-pre-line">{{ $kn->bio }}</p>
                                    </details>
                                @endif

                                <div class="mt-auto pt-4 flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $st['chip'] }}"><span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>{{ $st['label'] }}</span>
                                    <a href="{{ route('ngopi-pagi.mulai', ['konselor' => $kn->id, 's' => $spesialisasi->slug]) }}"
                                        class="px-5 py-2 rounded-lg bg-secondary hover:bg-primary text-on-primary text-sm font-bold active:scale-[.98] transition">Chat</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var q = document.getElementById('cari'); if (!q) return;
    var items = document.querySelectorAll('#daftar [data-nama]');
    q.addEventListener('input', function () {
        var k = q.value.trim().toLowerCase();
        items.forEach(function (el) { el.style.display = (!k || el.dataset.nama.indexOf(k) !== -1) ? '' : 'none'; });
    });
})();
</script>
@endpush