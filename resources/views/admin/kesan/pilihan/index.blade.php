@extends('layouts.admin')

@section('page_title', 'KESAN')

@section('content')
<div class="space-y-4">
    @include('admin.kesan._nav', ['aktif' => 'pilihan'])

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach ([['Menunggu verifikasi', $stat['dipilih'], 'hourglass_top', 'text-amber-600'], ['Dalam penyaluran', $stat['penyaluran'], 'local_shipping', 'text-orange-600'], ['Selesai', $stat['selesai'], 'task_alt', 'text-green-600'], ['Akun Filantropi', $stat['filantropi'], 'volunteer_activism', 'text-brand']] as [$l, $v, $i, $cls])
            <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex items-center gap-3">
                <span class="material-symbols-outlined text-[30px] {{ $cls }}">{{ $i }}</span>
                <div><p class="text-2xl font-extrabold leading-none">{{ number_format($v, 0, ',', '.') }}</p><p class="text-xs text-on-surface-variant mt-1">{{ $l }}</p></div>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap gap-2 flex-1">
            @foreach (['dipilih' => 'Menunggu verifikasi', 'penyaluran' => 'Dalam penyaluran', 'selesai' => 'Selesai', 'lainnya' => 'Ditolak / batal', 'semua' => 'Semua'] as $k => $l)
                <a href="{{ route('kesan-admin.pilihan.index', ['s' => $k]) }}" class="px-3 py-1.5 rounded-full text-xs font-semibold border {{ $tab === $k ? 'bg-brand text-white border-brand' : 'border-outline-variant/60 text-on-surface-variant hover:bg-surface-container' }}">{{ $l }}</a>
            @endforeach
        </div>
        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="s" value="{{ $tab }}">
            <label class="flex items-center gap-2 h-10 w-64 max-w-full rounded-xl bg-surface-container-low px-3">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Kode, filantropi, penerima…" class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0">
            </label>
        </form>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl bg-white border border-outline-variant/40 p-10 text-center text-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[40px] text-outline">inbox</span><p class="mt-1">Tidak ada data pada tab ini.</p></div>
    @else
        <div class="space-y-2">
            @foreach ($items as $it)
                @php $p = $it->penerima; $c = $p->kategori->warnaCfg(); $cfg = $it->cfg(); @endphp
                <a href="{{ route('kesan-admin.pilihan.show', $it) }}" class="flex flex-wrap items-center gap-3 rounded-2xl bg-white border border-outline-variant/40 p-3 hover:shadow-md transition">
                    <div class="w-14 h-14 shrink-0 rounded-xl overflow-hidden {{ $c['soft'] }} flex items-center justify-center">
                        @if ($p->fotoUrl())<img src="{{ $p->fotoUrl() }}" alt="" class="w-full h-full object-cover">@else<span class="material-symbols-outlined {{ $c['text'] }}" style="font-variation-settings:'FILL' 1">{{ $p->kategori->ikon }}</span>@endif
                    </div>
                    <div class="min-w-0 flex-1 basis-56">
                        <p class="text-sm font-extrabold truncate">{{ $p->nama }} <span class="font-medium text-on-surface-variant">· {{ $p->kategori->label() }}</span></p>
                        <p class="text-xs text-on-surface-variant truncate">Oleh <b>{{ $it->user->name ?? '-' }}</b> · {{ $it->kode }} · {{ optional($it->dipilih_at)->translatedFormat('d M Y H:i') }}</p>
                    </div>
                    <span class="text-xs font-bold text-primary">{{ $p->rupiah() }}</span>
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $cfg['chip'] }}">{{ $cfg['label'] }}</span>
                    <span class="material-symbols-outlined text-outline">chevron_right</span>
                </a>
            @endforeach
        </div>
        <div>{{ $items->links() }}</div>
    @endif
</div>
@endsection
