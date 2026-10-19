@extends('layouts.admin')

@section('title', 'Master Kriteria Ground Check')
@section('page_title', 'Master Kriteria Ground Check')

@php
    use App\Models\GcKriteriaParameter as GcP;
    $totalBobot = $parameters->where('aktif', true)->sum('bobot');
@endphp

@section('content')
<div class="space-y-5 max-w-7xl" x-data="{ f: 'semua' }">

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 flex flex-wrap items-start justify-between gap-3">
        <div class="max-w-3xl">
            <h2 class="text-sm font-bold text-on-surface">Variabel & Pilihan Jawaban Ground Check (SOLID)</h2>
            <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">
                Dasar: dokumen 39 Variabel Ground Check dan Panduan Entry Data Ground Check 2026. Parameter aktif menjadi isian pada form
                entry petugas, dikelompokkan per tab (A. Kependudukan sampai G. Dokumentasi).
                Variabel yang punya padanan di kriteria PBI memakai salinan opsi PBI; opsi yang masih
                <strong>belum bernilai</strong> bersifat sementara dan perlu dilengkapi.
            </p>
            <p class="text-xs text-on-surface-variant mt-2">
                Total {{ $parameters->count() }} parameter &middot; {{ $parameters->where('aktif', true)->count() }} aktif &middot;
                total bobot aktif <strong>{{ number_format($totalBobot, 2, ',', '.') }}</strong>
            </p>
        </div>
        <a href="{{ route('master.gc-kriteria.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-semibold">
            <span class="material-symbols-outlined text-[16px]">add</span> Tambah Parameter
        </a>
    </div>

    {{-- Filter kelompok --}}
    <div class="flex flex-wrap gap-2">
        <button type="button" @click="f = 'semua'"
            :class="f === 'semua' ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface-variant border border-outline-variant/50'"
            class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors">
            Semua ({{ $parameters->count() }})
        </button>
        @foreach (GcP::KELOMPOK as $key => $label)
            <button type="button" @click="f = '{{ $key }}'"
                :class="f === '{{ $key }}' ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface-variant border border-outline-variant/50'"
                class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors">
                {{ $label }} ({{ $parameters->where('kelompok', $key)->count() }})
            </button>
        @endforeach
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-surface-container text-on-surface-variant uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center w-14">No</th>
                        <th class="px-4 py-3 text-left">Parameter</th>
                        <th class="px-4 py-3 text-left">Kelompok</th>
                        <th class="px-4 py-3 text-left">Jenis Isian</th>
                        <th class="px-4 py-3 text-center">Bobot</th>
                        <th class="px-4 py-3 text-center">Opsi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($parameters as $p)
                        @php $kosong = $p->butuhOpsi() ? $p->opsis->whereNull('nilai')->count() : 0; @endphp
                        <tr class="hover:bg-surface-container-low" x-show="f === 'semua' || f === '{{ $p->kelompok }}'">
                            <td class="px-4 py-3 text-center">{{ $p->urutan }}</td>
                            <td class="px-4 py-3 font-medium">
                                {{ $p->nama }}
                                @if ($p->pbiParameter)
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded bg-brand-soft text-brand text-[10px] font-semibold align-middle"
                                          title="Padanan PBI: {{ $p->pbiParameter->nama }}">PBI</span>
                                @endif
                                @if ($p->butuhOpsi() && $p->opsis->isNotEmpty())
                                    <div class="text-[11px] text-on-surface-variant font-normal mt-0.5">
                                        {{ $p->opsis->pluck('label')->take(3)->implode(' · ') }}{{ $p->opsis->count() > 3 ? ' …' : '' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ GcP::KELOMPOK[$p->kelompok] ?? $p->kelompok }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ GcP::TIPE_INPUT[$p->tipe_input] ?? $p->tipe_input }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format((float) $p->bobot, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($p->butuhOpsi())
                                    {{ $p->opsis->count() }}
                                    @if ($kosong > 0)
                                        <div class="mt-1">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium bg-warning-container text-on-warning-container">
                                                {{ $kosong }} belum bernilai
                                            </span>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-on-surface-variant">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-medium {{ $p->aktif ? 'bg-success-container text-on-success-container' : 'bg-surface-container-highest text-on-surface-variant' }}">
                                    {{ $p->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('master.gc-kriteria.edit', $p) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium transition-colors">
                                        <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                                    </a>
                                    <form method="POST" action="{{ route('master.gc-kriteria.destroy', $p) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" data-confirm="Hapus parameter &quot;{{ $p->nama }}&quot; beserta opsinya?" data-confirm-variant="error"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-[11px] font-medium transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">delete</span> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-on-surface-variant">
                            Belum ada parameter. Jalankan <code>php artisan db:seed --class=GcKriteriaSeeder</code> atau tambah manual.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
