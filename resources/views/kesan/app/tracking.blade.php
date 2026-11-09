@extends('layouts.kesan')

@section('page_title', 'Tracking Bantuan')
@section('back', route('kesan.app.riwayat'))

@section('content')
@php
    $p = $pilihan->penerima; $c = $p->kategori->warnaCfg(); $cfg = $pilihan->cfg();
    $done = $pilihan->langkah(); $aktif = $pilihan->tahapAktif(); $berhenti = in_array($pilihan->status, ['ditolak', 'batal'], true);
    $tgl = fn ($d) => $d ? $d->translatedFormat('d F Y') : '-';
@endphp
<div class="max-w-3xl mx-auto space-y-4">

    {{-- Stepper --}}
    <section class="rounded-3xl bg-white border border-outline-variant/40 p-5">
        <ol class="flex">
            @foreach (\App\Models\KesanPilihan::TAHAP as $i => $nama)
                @php
                    $no = $i + 1; $sudah = $no <= $done; $sedang = $aktif === $no;
                    $garis = ($no + 1) <= $done;
                @endphp
                <li class="relative flex-1 flex flex-col items-center text-center">
                    @unless ($loop->last)
                        <span class="absolute top-4 left-1/2 w-full h-0.5 {{ $garis ? 'bg-green-600' : 'bg-slate-200' }}"></span>
                    @endunless
                    <span class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center
                        {{ $sudah ? 'bg-green-600 text-white' : ($sedang ? 'bg-accent text-white ring-4 ring-orange-100' : 'bg-white border-2 border-slate-300 text-slate-400') }}">
                        <span class="material-symbols-outlined msym-fill text-[18px]">{{ $sudah ? 'check' : ($sedang ? 'more_horiz' : 'check') }}</span>
                    </span>
                    <span class="mt-1.5 text-[11px] font-bold {{ $sudah || $sedang ? 'text-primary' : 'text-slate-400' }}">{{ $nama }}</span>
                </li>
            @endforeach
        </ol>
        @if ($berhenti)
            <p class="mt-4 rounded-xl {{ $cfg['chip'] }} px-4 py-2.5 text-xs font-semibold">{{ $cfg['label'] }}: {{ $pilihan->status === 'ditolak' ? ($pilihan->catatan ?: 'Pilihan tidak dapat dilanjutkan.') : 'Anda membatalkan pilihan ini.' }}</p>
        @endif
    </section>

    {{-- Detail --}}
    <section class="rounded-3xl bg-white border border-outline-variant/40 p-4">
        <div class="flex gap-3">
            <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden {{ $c['soft'] }} flex items-center justify-center">
                @if ($p->fotoUrl())<img src="{{ $p->fotoUrl() }}" alt="" class="w-full h-full object-cover">
                @else<span class="material-symbols-outlined msym-fill {{ $c['text'] }} text-[34px]">{{ $p->kategori->ikon }}</span>@endif
            </div>
            <div class="min-w-0">
                <a href="{{ route('kesan.app.detail', $p) }}" class="block text-base font-extrabold text-primary leading-snug">{{ $p->nama }}</a>
                <p class="text-xs text-on-surface-variant">{{ $p->kategori->nama }}</p>
                <span class="mt-1.5 inline-block rounded-full px-3 py-1 text-[11px] font-bold {{ $cfg['chip'] }}">{{ $cfg['label'] }}</span>
            </div>
        </div>
        <dl class="mt-4 divide-y divide-outline-variant/30 text-sm">
            @foreach ([['Kode', $pilihan->kode], ['Tanggal Dipilih', $tgl($pilihan->dipilih_at)], ['Tanggal Verifikasi', $tgl($pilihan->verifikasi_at)], ['Tanggal Selesai', $tgl($pilihan->selesai_at)], ['Estimasi Bantuan', $p->rupiah()], ['Status Saat Ini', $cfg['info']]] as [$l, $v])
                <div class="flex justify-between gap-4 py-2.5"><dt class="text-on-surface-variant">{{ $l }}</dt><dd class="text-right font-semibold">{{ $v }}</dd></div>
            @endforeach
        </dl>
        @if ($pilihan->status === 'selesai')
            <div class="mt-3 rounded-xl bg-success-container p-4 text-sm text-on-success-container">
                <p class="font-extrabold">Laporan hasil penyaluran</p>
                <p class="mt-1 whitespace-pre-line">{{ $pilihan->laporan }}</p>
                @if ($pilihan->buktiUrl())
                    <a href="{{ $pilihan->buktiUrl() }}" target="_blank" rel="noopener"><img src="{{ $pilihan->buktiUrl() }}" alt="Bukti penyaluran" class="mt-3 max-h-64 rounded-lg border border-green-200"></a>
                @endif
            </div>
        @endif
    </section>

    <section class="flex items-center gap-4 rounded-3xl bg-brand-soft border border-secondary-container/60 p-4">
        <span class="material-symbols-outlined msym-fill text-[40px] text-brand shrink-0">volunteer_activism</span>
        <div class="text-sm">
            <p class="font-extrabold text-primary">Terima kasih</p>
            <p class="text-on-surface-variant">atas kolaborasi dan kepedulian Anda. Bersama kita wujudkan lebih banyak kehidupan yang lebih baik.</p>
        </div>
    </section>

    @if ($pilihan->status === 'dipilih')
        <form method="POST" action="{{ route('kesan.app.batal', $pilihan) }}" onsubmit="return confirm('Batalkan pilihan ini? Calon penerima akan kembali tersedia.');">
            @csrf
            <button type="submit" class="w-full rounded-xl border border-error/40 py-3 text-sm font-bold text-error hover:bg-error-container/40">Batalkan pilihan</button>
        </form>
    @endif
</div>
@endsection
