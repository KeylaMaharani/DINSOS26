@extends('ngopi-pagi.layout')

@section('portal', '1')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="py-3 text-xs text-on-surface-variant flex items-center gap-2">
        <a href="{{ route('home') }}" class="text-brand font-semibold hover:underline">Beranda</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span>Ngopi Pagi</span>
    </nav>

    <div class="grid lg:grid-cols-12 gap-6 lg:gap-8 pb-8">
        {{-- KIRI: perkenalan layanan --}}
        <aside class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-20 self-start space-y-4">
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-brand text-white p-6 sm:p-8">
                <div class="relative w-16 h-16 rounded-2xl bg-white/10 ring-1 ring-white/25 flex items-center justify-center">
                    <span class="absolute -top-3 left-4 w-1 h-4 rounded-full bg-white/70 uap"></span>
                    <span class="absolute -top-4 left-8 w-1 h-5 rounded-full bg-white/70 uap" style="animation-delay:.7s"></span>
                    <span class="material-symbols-outlined text-[38px] text-crema" style="font-variation-settings:'FILL' 1">coffee</span>
                </div>
                <h1 class="mt-5 text-3xl font-extrabold italic tracking-tight text-crema leading-none">Ngopi Pagi</h1>
                <p class="mt-2 text-sm font-semibold text-white/90">Layanan konsultasi LK3 Dinas Sosial Kota Bogor</p>
                <p class="mt-4 text-[13px] leading-relaxed text-white/85">Ceritakan persoalan keluarga Anda kepada konselor kami. Pilih topik yang paling sesuai, lalu pilih konselornya.</p>

                @if ($lanjut)
                    <a href="{{ route('ngopi-pagi.chat', $lanjut->token) }}" class="mt-6 flex items-center gap-3 rounded-2xl bg-white/12 ring-1 ring-white/25 px-4 py-3 hover:bg-white/20 transition">
                        <span class="material-symbols-outlined text-[22px] text-crema">forum</span>
                        <span class="min-w-0 flex-1 leading-tight">
                            <span class="block text-sm font-bold">Lanjutkan percakapan Anda</span>
                            <span class="block text-[11px] text-white/75 truncate">{{ $lanjut->no_konsultasi }}</span>
                        </span>
                        @if (($lanjut->belum_baca ?? 0) > 0)
                            <span class="min-w-[22px] h-[22px] px-1.5 rounded-full bg-red-500 text-white text-[11px] font-bold flex items-center justify-center" title="Balasan baru dari petugas">{{ $lanjut->belum_baca }}</span>
                        @endif
                        <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                    </a>
                @endif
            </section>

            <section id="info-layanan" class="scroll-mt-24 rounded-2xl bg-white border border-outline-variant/40 p-5 space-y-4 text-sm">
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 shrink-0 rounded-full bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">schedule</span></span>
                    <div><p class="font-bold">Jam layanan</p><p class="text-on-surface-variant text-[13px]">Senin &ndash; Jumat, 08.00 &ndash; 15.00 WIB</p></div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 shrink-0 rounded-full bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">lock</span></span>
                    <div><p class="font-bold">Aman dan rahasia</p><p class="text-on-surface-variant text-[13px]">Data Anda hanya dipakai untuk kepentingan pelayanan.</p></div>
                </div>
            </section>

            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-[13px] leading-relaxed text-rose-900 flex gap-3">
                <span class="material-symbols-outlined text-[20px] text-rose-600 shrink-0">emergency</span>
                <p>Jika Anda atau seseorang berada dalam bahaya saat ini, jangan menunggu balasan chat. Hubungi <b>112</b> (layanan darurat) terlebih dahulu.</p>
            </div>
        </aside>

        {{-- KANAN: pencarian dan daftar spesialisasi --}}
        <section aria-labelledby="judul-cari" class="lg:col-span-7 xl:col-span-8 rounded-3xl bg-white border border-outline-variant/40 p-5 sm:p-8">
            <label class="flex items-center gap-2.5 h-12 rounded-xl border border-outline-variant/70 bg-white px-4 focus-within:border-brand focus-within:ring-2 focus-within:ring-brand/20">
                <input id="cari" type="search" placeholder="Cari topik, mis. pengasuhan anak" class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0" aria-label="Cari spesialisasi">
                <span class="material-symbols-outlined text-[20px] text-on-surface-variant">search</span>
            </label>

            <h2 id="judul-cari" class="mt-7 text-xl sm:text-2xl font-extrabold">Cari konselor atau spesialisasi</h2>
            <p class="text-sm text-on-surface-variant mt-1">Pilih kategori yang sesuai dengan kebutuhan Anda.</p>

            <div id="grid" class="mt-6 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                @foreach ($spesialisasis as $s)
                    @php $w = $s->warna(); @endphp
                    <a href="{{ route('ngopi-pagi.spesialisasi', $s->slug) }}" data-nama="{{ mb_strtolower($s->nama . ' ' . $s->deskripsi) }}"
                        class="group flex flex-col items-center text-center gap-2 px-2 py-4 rounded-2xl border border-transparent hover:border-outline-variant/60 hover:bg-surface focus-visible:ring-2 focus-visible:ring-brand/40 outline-none transition">
                        <span class="w-20 h-20 rounded-full {{ $w['bg'] }} flex items-center justify-center">
                            <span class="material-symbols-outlined text-[38px] {{ $w['fg'] }}" style="font-variation-settings:'FILL' 1">{{ $s->ikon }}</span>
                        </span>
                        <span class="text-sm font-semibold leading-snug group-hover:text-brand">{{ $s->nama }}</span>
                        <span class="text-[11px] text-on-surface-variant">{{ $s->konselors_count }} konselor</span>
                    </a>
                @endforeach

                <a href="{{ route('ngopi-pagi.mulai') }}" data-nama="belum yakin tanya admin"
                    class="group flex flex-col items-center text-center gap-2 px-2 py-4 rounded-2xl border border-transparent hover:border-outline-variant/60 hover:bg-surface focus-visible:ring-2 focus-visible:ring-brand/40 outline-none transition">
                    <span class="w-20 h-20 rounded-full bg-brand-soft border-2 border-dashed border-brand/40 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[36px] text-brand">support_agent</span>
                    </span>
                    <span class="text-sm font-semibold leading-snug group-hover:text-brand">Belum yakin? Tanya admin</span>
                    <span class="text-[11px] text-on-surface-variant">Kami bantu arahkan</span>
                </a>
            </div>
            <p id="kosong" class="hidden mt-6 text-sm text-on-surface-variant">Topik tidak ditemukan. Coba kata lain, atau pilih &ldquo;Belum yakin? Tanya admin&rdquo;.</p>
        </section>
    </div>

    {{-- Cara kerja: baris penuh --}}
    <section id="cara-kerja" class="scroll-mt-24 pb-12">
        <h2 class="text-xl font-extrabold">Bagaimana layanan ini berjalan</h2>
        <ol class="mt-5 grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach ([
                ['category', 'Pilih topik dan konselor', 'Sesuaikan dengan persoalan yang Anda hadapi.'],
                ['edit_note', 'Isi data singkat', 'Nama atau inisial, wilayah, nomor yang bisa dihubungi, dan permasalahan Anda.'],
                ['support_agent', 'Admin menyapa', 'Admin memastikan data dan kebutuhan Anda sudah tepat.'],
                ['psychology', 'Konsultasi dengan konselor LK3', 'Percakapan berlanjut di ruang obrolan yang sama.'],
                ['task_alt', 'Layanan selesai', 'Anda mendapat informasi, konseling, atau rujukan, lalu mengonfirmasi kebutuhan terpenuhi.'],
            ] as [$ic, $jd, $ds])
                <li class="rounded-2xl bg-white border border-outline-variant/40 p-5">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 shrink-0 rounded-full bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">{{ $ic }}</span></span>
                        <span class="text-xs font-bold text-on-surface-variant">Langkah {{ $loop->iteration }}</span>
                    </div>
                    <p class="mt-3 text-sm font-bold leading-snug">{{ $jd }}</p>
                    <p class="mt-1 text-[13px] text-on-surface-variant leading-relaxed">{{ $ds }}</p>
                </li>
            @endforeach
        </ol>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var q = document.getElementById('cari'), items = document.querySelectorAll('#grid [data-nama]'), kosong = document.getElementById('kosong');
    q.addEventListener('input', function () {
        var k = q.value.trim().toLowerCase(), n = 0;
        items.forEach(function (el) { var cocok = !k || el.dataset.nama.indexOf(k) !== -1; el.classList.toggle('hidden', !cocok); if (cocok) n++; });
        kosong.classList.toggle('hidden', n > 0);
    });
})();
</script>
@endpush
