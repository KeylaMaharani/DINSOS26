@extends('layouts.admin')

@section('page_title', 'Ngopi Pagi')

@section('content')
@php $K = \App\Models\NgopiPagiKonsultasi::class; @endphp
<div class="space-y-4">
    @include('admin.ngopi-pagi._nav', ['aktif' => 'chat_masuk'])

    <div class="grid lg:grid-cols-12 gap-4 items-start">
        {{-- Kotak masuk --}}
        <aside class="lg:col-span-4 rounded-2xl bg-white border border-outline-variant/40 overflow-hidden">
            <form method="GET" class="p-3 border-b border-outline-variant/30">
                <label class="flex items-center gap-2 h-10 rounded-xl bg-surface-container-low px-3">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, nomor, kelurahan…"
                        class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0">
                </label>
            </form>

            <ul class="max-h-[72vh] overflow-y-auto divide-y divide-outline-variant/20">
                @forelse ($daftar as $x)
                    @php
                        $kat = $x->kategori ? $K::KATEGORI[$x->kategori] : null;
                        $pilih = $terpilih && $terpilih->id === $x->id;
                    @endphp
                    <li>
                        <a href="{{ route('ngopi-pagi.admin.tahap.chat_masuk', ['konsultasi' => $x->id, 'q' => request('q')]) }}"
                            class="flex gap-3 px-3 py-3 hover:bg-brand-soft/60 {{ $pilih ? 'bg-brand-soft' : '' }}">
                            <span class="w-10 h-10 shrink-0 rounded-full bg-brand text-white font-extrabold flex items-center justify-center">{{ $x->inisial() }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-1.5">
                                    <b class="text-sm truncate">{{ $x->nama }}</b>
                                    @if ($x->butuh_segera)
                                        <span class="px-1.5 py-0.5 rounded-full bg-rose-600 text-white text-[9px] font-bold">SEGERA</span>
                                    @endif
                                    <span class="ml-auto text-[10px] text-on-surface-variant shrink-0">{{ optional($x->terakhir_pesan_at)->locale('id')->diffForHumans(null, true, true) }}</span>
                                </span>
                                <span class="block text-[11px] text-on-surface-variant truncate">{{ $x->no_konsultasi }} · {{ $x->kelurahan }}</span>
                                <span class="block text-xs text-on-surface-variant truncate mt-0.5">{{ \Illuminate\Support\Str::limit($x->kebutuhan, 70) }}</span>
                            </span>
                            @if ($x->belum_dibaca > 0)
                                <span class="self-center min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[11px] font-bold flex items-center justify-center">{{ $x->belum_dibaca }}</span>
                            @endif
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-10 text-center text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-[36px] text-outline">forum</span>
                        <p class="mt-1">Belum ada chat masuk.</p>
                    </li>
                @endforelse
            </ul>
        </aside>

        {{-- Ruang kerja --}}
        <div class="lg:col-span-8 min-w-0">
            @if ($terpilih)
                @include('admin.ngopi-pagi._workspace', ['k' => $terpilih])
            @else
                <div class="rounded-2xl bg-white border border-outline-variant/40 p-10 text-center text-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-[44px] text-cup" style="font-variation-settings:'FILL' 1">coffee</span>
                    <p class="mt-2 font-semibold text-on-surface">Tidak ada percakapan yang menunggu.</p>
                    <p class="text-xs mt-1">Chat baru dari masyarakat akan muncul di sini.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
