@extends('layouts.masyarakat')

@section('title', 'Dokumen - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Dokumen')
@section('container', 'max-w-3xl')

@php
    $card = 'rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30';
@endphp

@section('content')
    <div class="space-y-4 sm:space-y-5">

        <div>
            <h1 class="text-xl font-extrabold text-brand">Dokumen Saya</h1>
            <p class="mt-1 text-xs text-on-surface-variant">
                Dokumen di sini berasal dari berkas yang Anda unggah saat mengisi pengajuan (PBI APBD dan DTSEN),
                dan dipakai otomatis di pengajuan berikutnya. Anda dapat melihat dan mengunduhnya.
                Setiap jenis menyimpan maksimal {{ $maksVersi }} versi terbaru.
            </p>
        </div>

        @foreach ($jenisList as $jenis => [$label, $keterangan, $ikon])
            @php
                $items = $dokumens->get($jenis, collect());
            @endphp

            <section class="{{ $card }} p-4 sm:p-5">
                <div class="flex items-start gap-3 mb-3">
                    <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">{{ $ikon }}</span>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm font-extrabold text-on-surface">{{ $label }}</h2>
                        <p class="text-xs text-on-surface-variant">{{ $keterangan }}</p>
                    </div>
                </div>

                {{-- Daftar versi (terbaru di atas) --}}
                <div class="space-y-2.5">
                    @forelse ($items as $i => $d)
                        <div class="flex items-center gap-3 rounded-2xl border border-outline-variant/40 p-2.5">
                            <div class="w-16 h-16 shrink-0 rounded-xl border border-outline-variant/40 bg-surface-container overflow-hidden flex items-center justify-center">
                                @if ($d->isGambar())
                                    <img src="{{ $d->url() }}" alt="{{ $label }}" class="w-full h-full object-cover">
                                @elseif ($d->isPdf())
                                    <span class="material-symbols-outlined text-[28px] text-error/70">picture_as_pdf</span>
                                @else
                                    <span class="material-symbols-outlined text-[26px] text-on-surface-variant/50">description</span>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-on-surface truncate" title="{{ $d->nama_asli }}">{{ $d->nama_asli ?: basename($d->path) }}</p>
                                <p class="text-[11px] text-on-surface-variant">
                                    Diunggah {{ $d->created_at->format('d M Y, H:i') }}
                                    @if ($i === 0)
                                        <span class="ml-1 inline-flex px-1.5 py-0.5 rounded-full bg-success-container text-on-success-container text-[10px] font-bold">Terbaru</span>
                                    @endif
                                </p>

                                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <a href="{{ $d->url() }}" target="_blank" rel="noopener" data-lihat="{{ $d->isPdf() ? 'pdf' : 'gambar' }}" data-judul="{{ $d->nama_asli ?: $label }}" class="text-xs font-semibold text-primary underline">Lihat</a>
                                    <a href="{{ $d->url() }}" download="{{ $d->nama_asli ?: basename($d->path) }}" class="text-xs font-semibold text-primary underline">Unduh</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-on-surface-variant italic">Belum ada dokumen. Dokumen akan muncul setelah Anda mengunggahnya di formulir pengajuan.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    @include('masyarakat._lihat-berkas')
@endsection
