@extends('layouts.admin')

@section('page_title', 'KESAN')

@section('content')
@php $sel = 'h-10 rounded-xl border border-outline-variant/60 bg-white px-3 text-sm'; $role = auth()->user()->role; @endphp
<div class="space-y-4">
    @include('admin.kesan._nav', ['aktif' => 'penerima'])

    <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex flex-wrap items-center gap-3">
        <span class="w-11 h-11 rounded-xl bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined">groups</span></span>
        <div class="min-w-0 flex-1 basis-48">
            <h2 class="text-base font-extrabold">Calon Penerima</h2>
            <p class="text-xs text-on-surface-variant">Data yang dilihat Filantropi di aplikasi KESAN.</p>
        </div>
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label class="flex items-center gap-2 h-10 w-48 rounded-xl bg-surface-container-low px-3">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari…" class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0"></label>
            <select name="kategori" class="{{ $sel }}" onchange="this.form.submit()"><option value="">Semua kategori</option>
                @foreach ($kategoris as $k)<option value="{{ $k->id }}" @selected((int) request('kategori') === $k->id)>{{ $k->label() }}</option>@endforeach</select>
            <select name="status" class="{{ $sel }}" onchange="this.form.submit()"><option value="">Semua status</option>
                @foreach (\App\Models\KesanPenerima::STATUS as $sk => $sv)<option value="{{ $sk }}" @selected(request('status') === $sk)>{{ $sv['label'] }}</option>@endforeach</select>
        </form>
        @if ($role?->hasModule('kesan.penerima.tambah'))
            <a href="{{ route('kesan-admin.penerima.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-primary">
                <span class="material-symbols-outlined text-[18px]">add</span>Tambah penerima</a>
        @endif
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl bg-white border border-outline-variant/40 p-10 text-center text-sm text-on-surface-variant">Belum ada data calon penerima.</div>
    @else
        <div class="grid md:grid-cols-2 2xl:grid-cols-3 gap-3">
            @foreach ($items as $p)
                @php $c = $p->kategori->warnaCfg(); $st = $p->statusCfg(); $pr = $p->prioritasCfg(); @endphp
                <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex gap-3 {{ $p->aktif ? '' : 'opacity-60' }}">
                    <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden {{ $c['soft'] }} flex items-center justify-center">
                        @if ($p->fotoUrl())<img src="{{ $p->fotoUrl() }}" alt="" class="w-full h-full object-cover">@else<span class="material-symbols-outlined {{ $c['text'] }} text-[34px]" style="font-variation-settings:'FILL' 1">{{ $p->kategori->ikon }}</span>@endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-extrabold leading-snug">{{ $p->nama }}</p>
                        <p class="text-xs text-on-surface-variant">{{ $p->kategori->label() }} · Kec. {{ $p->kecamatan }}</p>
                        <p class="text-xs font-semibold {{ $pr['text'] }}">{{ $pr['label'] }} · {{ $p->rupiah() }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $st['chip'] }}">{{ $st['label'] }}</span>
                            @unless ($p->aktif)<span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-700">Nonaktif</span>@endunless
                            <span class="ml-auto flex gap-2">
                                @if ($role?->hasModule('kesan.penerima.ubah'))
                                    <a href="{{ route('kesan-admin.penerima.edit', $p) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant/60 text-xs font-semibold hover:bg-surface-container"><span class="material-symbols-outlined text-[16px]">edit</span>Ubah</a>
                                @endif
                                @if ($role?->hasModule('kesan.penerima.hapus'))
                                    <form method="POST" action="{{ route('kesan-admin.penerima.destroy', $p) }}">@csrf @method('DELETE')
                                        <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-error/40 text-error text-xs font-semibold hover:bg-error-container/40" data-confirm="Hapus {{ $p->nama }}?" data-confirm-variant="error" data-confirm-title="Hapus calon penerima"><span class="material-symbols-outlined text-[16px]">delete</span>Hapus</button></form>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div>{{ $items->links() }}</div>
    @endif
</div>
@endsection
