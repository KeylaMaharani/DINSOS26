@extends('layouts.admin')

@section('page_title', 'KESAN')

@section('content')
@php
    $p = $pilihan->penerima; $c = $p->kategori->warnaCfg(); $cfg = $pilihan->cfg();
    $bisaProses = auth()->user()->role?->hasModule('kesan.pilihan.proses');
    $inp = 'w-full rounded-xl border border-outline-variant/60 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
    $tgl = fn ($d) => $d ? $d->translatedFormat('d M Y H:i') : '-';
@endphp
<div class="space-y-4">
    @include('admin.kesan._nav', ['aktif' => 'pilihan'])
    <a href="{{ route('kesan-admin.pilihan.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:underline">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>Kembali ke daftar</a>

    @if ($errors->any())
        <div class="rounded-xl bg-error-container text-on-error-container text-xs px-4 py-3 space-y-0.5">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <div class="grid xl:grid-cols-3 gap-4 items-start">
        <div class="xl:col-span-2 space-y-4">
            <section class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex flex-wrap gap-4">
                <div class="w-32 h-32 shrink-0 rounded-2xl overflow-hidden {{ $c['soft'] }} flex items-center justify-center">
                    @if ($p->fotoUrl())<img src="{{ $p->fotoUrl() }}" alt="" class="w-full h-full object-cover">@else<span class="material-symbols-outlined {{ $c['text'] }} text-[48px]" style="font-variation-settings:'FILL' 1">{{ $p->kategori->ikon }}</span>@endif
                </div>
                <div class="min-w-0 flex-1 basis-60 space-y-1">
                    <span class="inline-block rounded px-2 py-0.5 text-[10px] font-bold text-white {{ $c['solid'] }}">{{ $p->kategori->nama }}</span>
                    <h2 class="text-base font-extrabold">{{ $p->nama }}</h2>
                    <p class="text-xs text-on-surface-variant">{{ $p->wilayah() }} · {{ $p->prioritasCfg()['label'] }}@if ($p->jumlah_jiwa) · {{ $p->jumlah_jiwa }} jiwa @endif @if ($p->no_kk) · KK {{ $p->no_kk }} @endif</p>
                    <p class="text-sm">{{ $p->profil }}</p>
                    <p class="text-sm"><b>Kebutuhan:</b> {{ $p->kebutuhan ?: '-' }}</p>
                    <p class="text-sm font-extrabold text-primary">{{ $p->rupiah() }}</p>
                </div>
            </section>

            <section class="rounded-2xl bg-white border border-outline-variant/40 p-4">
                <h3 class="text-sm font-extrabold mb-2">Filantropi</h3>
                <p class="text-sm"><b>{{ $pilihan->user->name ?? '-' }}</b> · {{ $pilihan->user->username ?? '' }} · {{ $pilihan->user->email ?? '' }}</p>
            </section>

            <section class="rounded-2xl bg-white border border-outline-variant/40 p-4">
                <h3 class="text-sm font-extrabold mb-2">Riwayat proses</h3>
                <dl class="divide-y divide-outline-variant/30 text-sm">
                    @foreach ([['Kode', $pilihan->kode], ['Dipilih', $tgl($pilihan->dipilih_at)], ['Diverifikasi', $tgl($pilihan->verifikasi_at) . ($pilihan->verifikator ? ' oleh ' . $pilihan->verifikator->name : '')], ['Selesai', $tgl($pilihan->selesai_at)], ['Catatan petugas', $pilihan->catatan ?: '-']] as [$l, $v])
                        <div class="flex justify-between gap-4 py-2"><dt class="text-on-surface-variant">{{ $l }}</dt><dd class="text-right font-semibold">{{ $v }}</dd></div>
                    @endforeach
                </dl>
                @if ($pilihan->status === 'selesai')
                    <div class="mt-3 rounded-xl bg-success-container p-3 text-sm text-on-success-container">
                        <p class="font-bold">Laporan penyaluran</p><p class="whitespace-pre-line">{{ $pilihan->laporan }}</p>
                        @if ($pilihan->buktiUrl())<a href="{{ $pilihan->buktiUrl() }}" target="_blank" rel="noopener"><img src="{{ $pilihan->buktiUrl() }}" alt="Bukti" class="mt-2 max-h-56 rounded-lg"></a>@endif
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-3 xl:sticky xl:top-2">
            <div class="rounded-2xl bg-white border border-outline-variant/40 p-4">
                <p class="text-xs text-on-surface-variant">Status saat ini</p>
                <span class="mt-1 inline-block rounded-full px-3 py-1 text-xs font-bold {{ $cfg['chip'] }}">{{ $cfg['label'] }}</span>
            </div>

            @if ($bisaProses && $pilihan->status === 'dipilih')
                <form method="POST" action="{{ route('kesan-admin.pilihan.aksi', $pilihan) }}" class="rounded-2xl bg-white border border-outline-variant/40 p-4 space-y-3">
                    @csrf <input type="hidden" name="aksi" value="verifikasi">
                    <h3 class="text-sm font-extrabold">Verifikasi & mulai penyaluran</h3>
                    <textarea name="catatan" rows="3" maxlength="1000" placeholder="Catatan untuk arsip (opsional)" class="{{ $inp }}"></textarea>
                    <button class="w-full rounded-xl bg-brand py-2.5 text-sm font-semibold text-white hover:bg-primary" data-confirm="Verifikasi pilihan ini dan mulai proses penyaluran?" data-confirm-title="Verifikasi pilihan">Verifikasi</button>
                </form>
                <form method="POST" action="{{ route('kesan-admin.pilihan.aksi', $pilihan) }}" class="rounded-2xl bg-white border border-error/30 p-4 space-y-3">
                    @csrf <input type="hidden" name="aksi" value="tolak">
                    <h3 class="text-sm font-extrabold text-error">Tolak pilihan</h3>
                    <textarea name="catatan" rows="3" maxlength="1000" required placeholder="Alasan penolakan (wajib, terlihat oleh Filantropi)" class="{{ $inp }}"></textarea>
                    <button class="w-full rounded-xl border border-error/50 py-2.5 text-sm font-semibold text-error hover:bg-error-container/40" data-confirm="Tolak pilihan ini? Calon penerima kembali tersedia." data-confirm-variant="error" data-confirm-title="Tolak pilihan">Tolak</button>
                </form>
            @elseif ($bisaProses && $pilihan->status === 'penyaluran')
                <form method="POST" action="{{ route('kesan-admin.pilihan.aksi', $pilihan) }}" enctype="multipart/form-data" class="rounded-2xl bg-white border border-outline-variant/40 p-4 space-y-3">
                    @csrf <input type="hidden" name="aksi" value="selesai">
                    <h3 class="text-sm font-extrabold">Selesaikan penyaluran</h3>
                    <textarea name="laporan" rows="4" maxlength="3000" required placeholder="Laporan hasil penyaluran (tanggal, bentuk bantuan, penerima)" class="{{ $inp }}"></textarea>
                    <input type="file" name="bukti" accept="image/*" class="block w-full text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-brand file:text-white file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                    <p class="text-[11px] text-on-surface-variant">Foto bukti (opsional), maks. 4 MB.</p>
                    <button class="w-full rounded-xl bg-green-600 py-2.5 text-sm font-semibold text-white hover:bg-green-700" data-confirm="Tandai penyaluran selesai? Laporan akan terlihat oleh Filantropi." data-confirm-title="Selesaikan penyaluran">Tandai selesai</button>
                </form>
            @endif
        </aside>
    </div>
</div>
@endsection
