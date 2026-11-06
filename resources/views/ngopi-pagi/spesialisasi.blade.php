@extends('ngopi-pagi.layout')

@section('title', $spesialisasi->nama . ' - Ngopi Pagi')
@section('portal', '1')

@section('content')
@php $w = $spesialisasi->warna(); @endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <nav aria-label="Breadcrumb" class="py-3 text-xs text-on-surface-variant flex items-center gap-2 flex-wrap">
        <a href="{{ route('home') }}" class="text-brand font-semibold hover:underline">Beranda</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <a href="{{ route('ngopi-pagi.index') }}" class="text-brand font-semibold hover:underline">Ngopi Pagi</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span>{{ $spesialisasi->nama }}</span>
    </nav>

    <header class="rounded-3xl bg-white border border-outline-variant/40 p-5 sm:p-7 flex items-center gap-4 sm:gap-6">
        <span class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-full {{ $w['bg'] }} flex items-center justify-center">
            <span class="material-symbols-outlined text-[34px] sm:text-[42px] {{ $w['fg'] }}" style="font-variation-settings:'FILL' 1">{{ $spesialisasi->ikon }}</span>
        </span>
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight">{{ $spesialisasi->nama }}</h1>
            <p class="mt-1 text-sm text-on-surface-variant max-w-2xl">{{ $spesialisasi->deskripsi ?: 'Pilih konselor yang akan menemani Anda' }}</p>
        </div>
        <a href="{{ route('ngopi-pagi.index') }}" class="hidden sm:inline-flex items-center gap-1.5 shrink-0 px-4 py-2.5 rounded-xl border border-outline-variant/70 text-sm font-semibold hover:bg-surface">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>Semua topik
        </a>
    </header>

    <main class="py-6 pb-12">
        @unless ($buka)
            <p class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-2.5 text-xs text-amber-900">Saat ini di luar jam layanan (Senin &ndash; Jumat, 08.00 &ndash; 15.00 WIB). Anda tetap bisa mengirim pesan, balasan diberikan di hari kerja berikutnya.</p>
        @endunless

        @if ($konselors->isEmpty())
            <div class="rounded-2xl bg-white border border-outline-variant/40 p-10 text-center">
                <span class="material-symbols-outlined text-[44px] text-outline">person_search</span>
                <p class="mt-2 text-sm font-semibold">Belum ada konselor di topik ini.</p>
                <p class="text-[13px] text-on-surface-variant mt-1">Admin kami siap membantu mengarahkan Anda.</p>
                <a href="{{ route('ngopi-pagi.mulai', ['s' => $spesialisasi->slug]) }}" class="mt-4 inline-flex px-5 py-2.5 rounded-xl bg-brand text-white text-sm font-bold hover:bg-primary">Tanya admin</a>
            </div>
        @else
            <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="text-lg font-extrabold">{{ $konselors->count() }} konselor tersedia</h2>
                @if ($konselors->count() > 4)
                    <label class="flex items-center gap-2.5 h-11 rounded-xl border border-outline-variant/70 bg-white px-4 sm:w-80">
                        <span class="material-symbols-outlined text-[20px] text-on-surface-variant">search</span>
                        <input id="cari" type="search" placeholder="Cari nama konselor" class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0" aria-label="Cari konselor">
                    </label>
                @endif
            </div>

            <div id="daftar" class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($konselors as $kn)
                    @php $st = $kn->statusCfg(); @endphp
                    <article data-nama="{{ mb_strtolower($kn->nama_tampilan . ' ' . $kn->headline) }}" class="rounded-2xl bg-white border border-outline-variant/40 p-5 flex gap-4 hover:border-brand/40 hover:shadow-sm transition">
                        <x-ngopi-avatar :konselor="$kn" size="w-20 h-20" />
                        <div class="min-w-0 flex-1 flex flex-col">
                            <h2 class="text-[15px] font-extrabold leading-snug">{{ $kn->nama_tampilan }}</h2>
                            <p class="text-xs text-on-surface-variant">{{ $kn->headline }}@if ($kn->pengalaman_tahun) &middot; {{ $kn->pengalaman_tahun }} tahun pengalaman @endif</p>

                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($kn->spesialisasis->take(3) as $sp)
                                    <span class="px-2 py-0.5 rounded-md border border-outline-variant/60 text-[11px] text-on-surface-variant">{{ $sp->nama }}</span>
                                @endforeach
                            </div>

                            @if ($kn->bio)
                                <details class="mt-2 text-[13px]">
                                    <summary class="cursor-pointer text-brand font-semibold text-xs">Lihat profil</summary>
                                    <p class="mt-1.5 text-on-surface-variant leading-relaxed whitespace-pre-line">{{ $kn->bio }}</p>
                                </details>
                            @endif

                            <div class="mt-auto pt-4 flex flex-wrap items-center justify-between gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $st['chip'] }}"><span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>{{ $st['label'] }}</span>
                                <a href="{{ route('ngopi-pagi.mulai', ['konselor' => $kn->id, 's' => $spesialisasi->slug]) }}"
                                    class="px-5 py-2 rounded-lg bg-brand text-white text-sm font-bold hover:bg-primary active:scale-[.98] transition">Chat</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </main>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var q = document.getElementById('cari'); if (!q) return;
    var items = document.querySelectorAll('#daftar [data-nama]');
    q.addEventListener('input', function () {
        var k = q.value.trim().toLowerCase();
        items.forEach(function (el) { el.classList.toggle('hidden', k && el.dataset.nama.indexOf(k) === -1); });
    });
})();
</script>
@endpush
