@extends('ngopi-pagi.layout')

@section('title', 'Ngopi Pagi - Konsultasi LK3 Dinas Sosial Kota Bogor')

@push('head')
    <meta name="description" content="Layanan konsultasi LK3 Dinas Sosial Kota Bogor. Ceritakan persoalan keluarga Anda kepada konselor kami." />
@endpush

@section('content')
    {{-- ============ HERO ============ --}}
    <section class="np-hero w-full text-on-primary pt-28 pb-10 sm:pt-32 sm:pb-16 md:pt-40 md:pb-20">
        <div class="np-hero-icon" aria-hidden="true">
            <span class="material-symbols-outlined">coffee</span>
        </div>
        <div class="relative z-10 max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop space-y-space-sm text-center">
            <span class="inline-block font-label-sm text-label-sm tracking-widest uppercase text-secondary-fixed-dim">
                Layanan Konsultasi LK3 Kota Bogor
            </span>
            <h1 class="font-headline-lg-mobile text-headline-lg-mobile md:font-display-lg md:text-display-lg font-bold tracking-tight max-w-3xl mx-auto">
                Ngopi Pagi
            </h1>
            <p class="max-w-xl mx-auto font-body-md text-body-md text-on-primary/85">
                Ceritakan persoalan keluarga Anda kepada konselor kami. Pilih topik yang paling sesuai, lalu pilih konselornya.
            </p>
            <div class="pt-space-sm space-y-space-sm">
                <a href="#topik" class="np-btn np-btn-accent">
                    <span class="material-symbols-outlined text-[18px]">forum</span>
                    Mulai Konsultasi
                </a>
                @isset($buka)
                    <p class="flex items-center justify-center gap-2 text-[13px] text-on-primary/85">
                        <span class="w-2 h-2 rounded-full {{ $buka ? 'bg-emerald-400' : 'bg-white/40' }}"></span>
                        {{ $buka
                            ? 'Layanan buka sekarang · Senin–Jumat, 08.00–15.00 WIB'
                            : 'Layanan tutup · Buka Senin–Jumat, 08.00–15.00 WIB. Pesan tetap bisa dikirim dan dibalas saat jam layanan.' }}
                    </p>
                @endisset
            </div>
        </div>
    </section>

    {{-- ============ BREADCRUMB ============ --}}
    <div class="w-full bg-surface-container-lowest border-b border-outline-variant/20">
        <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop py-space-sm flex items-center gap-space-2xs font-label-sm text-label-sm text-on-surface-variant">
            <a href="{{ route('home') }}" class="hover:text-secondary transition-colors">Beranda</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-primary font-bold">Ngopi Pagi</span>
        </div>
    </div>

    {{-- ============ KONTEN UTAMA ============ --}}
    <section class="w-full py-space-2xl md:py-space-3xl">
        <div class="max-w-[1400px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
            <div class="np-page-card grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-start">

                {{-- Sidebar --}}
                <aside class="np-toc lg:col-span-3 lg:sticky lg:top-28 space-y-space-sm">
                    @if ($lanjut)
                        <a href="{{ route('ngopi-pagi.chat', $lanjut->token) }}" class="flex items-center gap-3 rounded-xl bg-secondary text-on-primary px-4 py-3 hover:bg-primary transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-[22px]">forum</span>
                            <span class="min-w-0 flex-1 leading-tight">
                                <span class="block text-sm font-bold">Lanjutkan percakapan</span>
                                <span class="block text-[11px] text-on-primary/75 truncate">{{ $lanjut->no_konsultasi }}</span>
                            </span>
                            @if (($lanjut->belum_baca ?? 0) > 0)
                                <span class="min-w-[22px] h-[22px] px-1.5 rounded-full bg-red-500 text-white text-[11px] font-bold flex items-center justify-center" title="Balasan baru dari petugas">{{ $lanjut->belum_baca }}</span>
                            @endif
                        </a>
                    @endif

                    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/20 p-space-md space-y-1">
                        <a class="np-toc-link" data-toc href="#topik"><span class="np-toc-dot"></span>Pilih Topik</a>
                        <a class="np-toc-link" data-toc href="#cara-kerja"><span class="np-toc-dot"></span>Cara Kerja</a>
                        <a class="np-toc-link" data-toc href="#info-layanan"><span class="np-toc-dot"></span>Jam Layanan</a>
                    </div>

                    <a href="{{ route('ngopi-pagi.mulai') }}"
                        class="w-full flex items-center justify-center gap-space-xs px-space-md py-3 rounded-lg bg-secondary hover:bg-primary text-on-primary font-label-md text-label-md transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">support_agent</span>
                        <span>Belum yakin? Tanya admin</span>
                    </a>
                </aside>

                {{-- Isi --}}
                <div class="lg:col-span-9 space-y-space-2xl">

                    {{-- Pilih topik --}}
                    <div id="topik" class="space-y-space-md">
                        <div class="space-y-space-2xs max-w-3xl">
                            <span class="font-label-sm text-label-sm text-secondary tracking-widest uppercase">Konsultasi</span>
                            <h2 class="font-headline-lg text-headline-lg text-primary font-bold leading-snug flex items-center gap-space-xs">
                                <span class="material-symbols-outlined text-[28px] text-secondary">psychology</span>
                                Cari konselor atau spesialisasi
                            </h2>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Pilih kategori yang sesuai dengan kebutuhan Anda.</p>
                        </div>

                        <label class="flex items-center gap-2.5 h-12 rounded-xl border border-outline-variant/70 bg-white px-4 focus-within:border-secondary focus-within:ring-2 focus-within:ring-secondary/20">
                            <input id="cari" type="search" placeholder="Cari topik, mis. pengasuhan anak" class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0" aria-label="Cari spesialisasi">
                            <span class="material-symbols-outlined text-[20px] text-on-surface-variant">search</span>
                        </label>

                        <div id="grid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-space-md">
                            @foreach ($spesialisasis as $s)
                                @php $w = $s->warna(); @endphp
                                <a href="{{ route('ngopi-pagi.spesialisasi', $s->slug) }}" data-nama="{{ mb_strtolower($s->nama . ' ' . $s->deskripsi) }}" class="np-topic">
                                    <span class="np-topic-icon {{ $w['bg'] }}">
                                        <span class="material-symbols-outlined {{ $w['fg'] }}">{{ $s->ikon }}</span>
                                    </span>
                                    <span class="np-topic-title">{{ $s->nama }}</span>
                                    <span class="np-topic-sub">{{ $s->konselors_count }} konselor</span>
                                </a>
                            @endforeach

                            <a href="{{ route('ngopi-pagi.mulai') }}" data-nama="belum yakin tanya admin" class="np-topic">
                                <span class="np-topic-icon bg-surface-container border-2 border-dashed border-secondary/40">
                                    <span class="material-symbols-outlined text-secondary">support_agent</span>
                                </span>
                                <span class="np-topic-title">Belum yakin? Tanya admin</span>
                                <span class="np-topic-sub">Kami bantu arahkan</span>
                            </a>
                        </div>
                        <p id="kosong" class="hidden text-sm text-on-surface-variant">Topik tidak ditemukan. Coba kata lain, atau pilih &ldquo;Belum yakin? Tanya admin&rdquo;.</p>
                    </div>

                    {{-- Cara kerja --}}
                    <div id="cara-kerja" class="space-y-space-md">
                        <h3 class="font-headline-md text-headline-md text-primary font-bold">Bagaimana layanan ini berjalan</h3>
                        <ol class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-space-md">
                            @foreach ([
                                ['category', 'Pilih topik dan konselor', 'Sesuaikan dengan persoalan yang Anda hadapi.'],
                                ['edit_note', 'Isi data singkat', 'Nama atau inisial, wilayah, nomor yang bisa dihubungi, dan permasalahan Anda.'],
                                ['support_agent', 'Admin menyapa', 'Admin memastikan data dan kebutuhan Anda sudah tepat.'],
                                ['psychology', 'Konsultasi dengan konselor LK3', 'Percakapan berlanjut di ruang obrolan yang sama.'],
                                ['task_alt', 'Layanan selesai', 'Anda mendapat informasi, konseling, atau rujukan, lalu mengonfirmasi kebutuhan terpenuhi.'],
                            ] as [$ic, $jd, $ds])
                                <li class="np-step">
                                    <div class="flex items-center gap-3">
                                        <span class="w-10 h-10 shrink-0 rounded-full bg-surface-container text-secondary flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">{{ $ic }}</span></span>
                                        <span class="text-xs font-bold text-on-surface-variant">Langkah {{ $loop->iteration }}</span>
                                    </div>
                                    <p class="mt-3 text-sm font-bold text-primary leading-snug">{{ $jd }}</p>
                                    <p class="mt-1 text-[13px] text-on-surface-variant leading-relaxed">{{ $ds }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    {{-- Info layanan --}}
                    <div id="info-layanan" class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
                        <div class="np-step flex items-start gap-3">
                            <span class="w-10 h-10 shrink-0 rounded-full bg-surface-container text-secondary flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">schedule</span></span>
                            <div><p class="font-bold text-primary text-sm">Jam layanan</p><p class="text-on-surface-variant text-[13px]">Senin &ndash; Jumat, 08.00 &ndash; 15.00 WIB</p></div>
                        </div>
                        <div class="np-step flex items-start gap-3">
                            <span class="w-10 h-10 shrink-0 rounded-full bg-surface-container text-secondary flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">lock</span></span>
                            <div><p class="font-bold text-primary text-sm">Aman dan rahasia</p><p class="text-on-surface-variant text-[13px]">Data Anda hanya dipakai untuk kepentingan pelayanan.</p></div>
                        </div>
                    </div>

                    {{-- Darurat (CTA penutup bergaya Kesan) --}}
                    <div class="bg-primary rounded-xl p-space-xl flex flex-col md:flex-row items-center justify-between gap-space-md text-on-primary">
                        <div class="space-y-1">
                            <h4 class="font-headline-sm text-headline-sm font-bold">Dalam keadaan darurat?</h4>
                            <p class="font-body-sm text-body-sm text-on-primary/80">
                                Jika Anda atau seseorang berada dalam bahaya saat ini, jangan menunggu balasan chat. Hubungi 112 (layanan darurat) terlebih dahulu.
                            </p>
                        </div>
                        <a href="tel:112" class="shrink-0 inline-flex items-center gap-space-xs px-space-lg py-3 rounded-lg bg-surface-container-lowest text-primary font-label-md text-label-md hover:bg-white transition-colors">
                            <span class="material-symbols-outlined text-[20px]">emergency</span>
                            Hubungi 112
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    /* Pencarian topik */
    var q = document.getElementById('cari'), items = document.querySelectorAll('#grid [data-nama]'), kosong = document.getElementById('kosong');
    q.addEventListener('input', function () {
        var k = q.value.trim().toLowerCase(), n = 0;
        items.forEach(function (el) { var cocok = !k || el.dataset.nama.indexOf(k) !== -1; el.style.display = cocok ? '' : 'none'; if (cocok) n++; });
        kosong.classList.toggle('hidden', n > 0);
    });

    /* Scrollspy Daftar Isi */
    var links = Array.prototype.slice.call(document.querySelectorAll('.np-toc-link[data-toc]'));
    var sections = links.map(function (l) { return document.getElementById(l.getAttribute('href').slice(1)); }).filter(Boolean);
    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) links.forEach(function (l) { l.classList.toggle('is-active', l.getAttribute('href') === '#' + e.target.id); });
        });
    }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });
    sections.forEach(function (s) { obs.observe(s); });
})();
</script>
@endpush