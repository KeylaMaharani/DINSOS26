@extends('ngopi-pagi.layout')

@section('content')
{{-- Hero --}}
<header class="relative overflow-hidden bg-gradient-to-br from-primary to-brand text-white px-6 pt-[calc(env(safe-area-inset-top,0px)+20px)] pb-10">
    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/80 hover:text-white">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>Beranda
    </a>

    <div class="mt-6 flex items-end gap-4">
        <div class="relative shrink-0 w-20 h-20 rounded-3xl bg-white/10 ring-1 ring-white/25 flex items-center justify-center">
            <span class="absolute -top-3 left-6 w-1 h-5 rounded-full bg-white/70 uap"></span>
            <span class="absolute -top-4 left-9 w-1 h-6 rounded-full bg-white/70 uap" style="animation-delay:.7s"></span>
            <span class="absolute -top-3 left-12 w-1 h-5 rounded-full bg-white/70 uap" style="animation-delay:1.4s"></span>
            <span class="material-symbols-outlined text-[44px] text-crema" style="font-variation-settings:'FILL' 1">coffee</span>
        </div>
        <div>
            <h1 class="text-4xl font-extrabold italic tracking-tight text-crema leading-none">Ngopi Pagi</h1>
            <p class="mt-2 text-sm text-white/85">Layanan konsultasi LK3 Dinas Sosial Kota Bogor</p>
        </div>
    </div>

    <p class="mt-6 text-[15px] leading-relaxed text-white/90">Ngobrol, konsultasi, pendampingan, dan informasi seputar kesejahteraan keluarga. Ceritakan persoalan Anda, petugas kami menemani mencari solusinya.</p>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('ngopi-pagi.mulai') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl bg-crema text-primary text-sm font-extrabold shadow-lg active:scale-[.98] transition-transform">
            <span class="material-symbols-outlined text-[20px]">chat</span>Mulai konsultasi
        </a>
        @if ($lanjut)
            <a href="{{ route('ngopi-pagi.chat', $lanjut->token) }}" class="inline-flex items-center gap-2 px-5 py-3.5 rounded-2xl border border-white/40 text-sm font-semibold hover:bg-white/10">
                Lanjutkan percakapan {{ $lanjut->no_konsultasi }}
                @if (($lanjut->belum_baca ?? 0) > 0)
                    <span class="min-w-[22px] h-[22px] px-1.5 rounded-full bg-red-500 text-white text-[11px] font-bold flex items-center justify-center" title="Balasan baru dari petugas">{{ $lanjut->belum_baca }}</span>
                @endif
            </a>
        @endif
    </div>

    <div class="mt-5 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold {{ $buka ? 'bg-emerald-400/20 text-emerald-100' : 'bg-white/10 text-white/80' }}">
        <span class="w-2 h-2 rounded-full {{ $buka ? 'bg-emerald-300' : 'bg-white/50' }}"></span>
        {{ $buka ? 'Petugas sedang melayani' : 'Di luar jam layanan, pesan dibalas di hari kerja berikutnya' }}
    </div>
</header>

<main class="px-5 py-7 space-y-8">
    {{-- Alur --}}
    <section>
        <h2 class="text-lg font-extrabold">Bagaimana layanan ini berjalan</h2>
        <ol class="mt-4 space-y-0">
            @foreach ([
                ['edit_note', 'Isi data singkat', 'Nama atau inisial, wilayah, nomor yang bisa dihubungi, dan permasalahan Anda.'],
                ['support_agent', 'Admin menyapa', 'Admin menjelaskan layanan dan memastikan data serta kebutuhan Anda sudah tepat.'],
                ['psychology', 'Konsultasi dengan petugas LK3', 'Kasus yang butuh pendalaman dihubungkan langsung dengan konselor LK3 di ruang obrolan yang sama, tanpa pindah aplikasi.'],
                ['task_alt', 'Layanan selesai', 'Anda mendapat informasi, konseling, atau rujukan, lalu mengonfirmasi bahwa kebutuhan terpenuhi.'],
            ] as [$ic, $jd, $ds])
                <li class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <span class="w-10 h-10 rounded-full bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">{{ $ic }}</span></span>
                        @unless ($loop->last)<span class="w-px flex-1 bg-outline-variant my-1"></span>@endunless
                    </div>
                    <div class="pb-6">
                        <p class="text-sm font-bold">{{ $jd }}</p>
                        <p class="text-[13px] text-on-surface-variant leading-relaxed">{{ $ds }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Jenis layanan --}}
    <section>
        <h2 class="text-lg font-extrabold">Yang bisa Anda dapatkan</h2>
        <div class="mt-3 grid gap-3">
            @foreach ([
                ['info', 'Informasi', 'Program dan kegiatan sosial, bantuan sosial, layanan Dinas Sosial, serta lembaga atau layanan sosial.'],
                ['forum', 'Konseling', 'Pendampingan oleh petugas LK3 untuk persoalan keluarga, pengasuhan, dan psikososial.'],
                ['share', 'Rujukan', 'Diarahkan ke layanan yang tepat: Puskesmas, rumah sakit, UPT, LKS, P2TP2A, dan lembaga terkait.'],
            ] as [$ic, $jd, $ds])
                <div class="flex gap-3 rounded-2xl bg-white border border-outline-variant/40 p-4">
                    <span class="material-symbols-outlined text-[26px] text-cup shrink-0">{{ $ic }}</span>
                    <div><p class="text-sm font-bold">{{ $jd }}</p><p class="text-[13px] text-on-surface-variant leading-relaxed">{{ $ds }}</p></div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Prinsip --}}
    <section>
        <h2 class="text-lg font-extrabold">Prinsip layanan</h2>
        <div class="mt-3 grid grid-cols-2 gap-3">
            @foreach ([['verified_user', 'Ramah dan profesional'], ['lock', 'Aman dan rahasia'], ['bolt', 'Cepat dan tepat'], ['favorite', 'Berorientasi pada kebutuhan pengguna']] as [$ic, $tx])
                <div class="rounded-2xl bg-brand-soft p-4">
                    <span class="material-symbols-outlined text-[24px] text-brand">{{ $ic }}</span>
                    <p class="mt-2 text-[13px] font-bold leading-snug">{{ $tx }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Jam & kanal --}}
    <section class="rounded-2xl bg-white border border-outline-variant/40 p-5 space-y-3 text-sm">
        <div class="flex items-center gap-3"><span class="material-symbols-outlined text-brand">schedule</span><div><p class="font-bold">Jam layanan</p><p class="text-on-surface-variant text-[13px]">Senin – Jumat, 08.00 – 15.00 WIB</p></div></div>
        <p class="text-xs text-on-surface-variant leading-relaxed border-t border-outline-variant/40 pt-3">Jika Anda atau seseorang berada dalam bahaya saat ini, jangan menunggu balasan chat. Hubungi <b>112</b> (layanan darurat) terlebih dahulu.</p>
    </section>
</main>

<footer class="px-5 pb-10 text-center text-[11px] text-on-surface-variant">Ngopi Pagi · LK3 Dinas Sosial Kota Bogor</footer>
@endsection
