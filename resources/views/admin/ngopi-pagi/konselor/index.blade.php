@extends('layouts.admin')

@section('page_title', 'Ngopi Pagi')

@section('content')
@php $Kon = \App\Models\NgopiPagiKonselor::class; @endphp
<div class="space-y-4">
    @include('admin.ngopi-pagi._nav', ['aktif' => 'konselor'])

    <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex flex-wrap items-center gap-3">
        <span class="w-11 h-11 rounded-xl bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined">badge</span></span>
        <div class="min-w-0 flex-1">
            <h2 class="text-base font-extrabold">Data Konselor</h2>
            <p class="text-xs text-on-surface-variant">Profil yang tampil di halaman publik Ngopi Pagi. Tiap konselor punya akun login untuk membalas chat.</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <label class="flex items-center gap-2 h-10 w-56 rounded-xl bg-surface-container-low px-3">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari konselor…" class="w-full bg-transparent border-0 outline-none text-sm focus:ring-0">
            </label>
        </form>
        <a href="{{ route('ngopi-pagi.admin.konselor.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-primary">
            <span class="material-symbols-outlined text-[18px]">add</span>Tambah konselor
        </a>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl bg-white border border-outline-variant/40 p-10 text-center text-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[40px] text-outline">person_add</span>
            <p class="mt-1">Belum ada konselor. Tambahkan konselor pertama agar warga bisa memilih.</p>
        </div>
    @else
        <div class="grid md:grid-cols-2 2xl:grid-cols-3 gap-3">
            @foreach ($items as $kn)
                @php $st = $kn->statusCfg(); @endphp
                <div class="rounded-2xl bg-white border border-outline-variant/40 p-4 flex gap-4 {{ $kn->aktif ? '' : 'opacity-60' }}">
                    <x-ngopi-avatar :konselor="$kn" size="w-20 h-20" />
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-extrabold leading-snug">{{ $kn->nama_tampilan }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $kn->headline }}@if ($kn->pengalaman_tahun) · {{ $kn->pengalaman_tahun }} th @endif</p>
                            </div>
                            @unless ($kn->aktif)<span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold">Nonaktif</span>@endunless
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach ($kn->spesialisasis as $sp)
                                <span class="px-2 py-0.5 rounded-md bg-surface-container text-[10px] font-semibold">{{ $sp->nama }}</span>
                            @endforeach
                        </div>
                        <p class="mt-2 text-[11px] text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">account_circle</span>{{ $kn->user->username ?? '-' }}</p>
                        <div class="mt-3 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $st['chip'] }}"><span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>{{ $st['label'] }}</span>
                            <a href="{{ route('ngopi-pagi.admin.konselor.edit', $kn) }}" class="ml-auto inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant/60 text-xs font-semibold hover:bg-surface-container"><span class="material-symbols-outlined text-[16px]">edit</span>Ubah</a>
                            <form method="POST" action="{{ route('ngopi-pagi.admin.konselor.destroy', $kn) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-error/40 text-error text-xs font-semibold hover:bg-error-container/40"
                                    data-confirm="Hapus profil {{ $kn->nama_tampilan }}? Akun login-nya tetap ada." data-confirm-variant="error" data-confirm-title="Hapus konselor"><span class="material-symbols-outlined text-[16px]">delete</span>Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
