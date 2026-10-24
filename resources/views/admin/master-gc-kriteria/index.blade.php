@extends('layouts.admin')

@php use App\Models\GcKriteriaParameter as GcP; @endphp

@section('title', 'Master Kriteria Ground Check')
@section('page_title', 'Master Kriteria Ground Check')

@section('content')
<div class="max-w-7xl space-y-4" x-data="{ kel: '' }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <select x-model="kel" class="px-3 py-2 rounded-lg border border-outline-variant/60 bg-white text-sm">
            <option value="">Semua kelompok</option>
            @foreach (GcP::KELOMPOK as $k => $label)
                <option value="{{ $k }}">{{ $label }}</option>
            @endforeach
        </select>
        <a href="{{ route('master.gc-kriteria.create') }}" class="inline-flex items-center gap-1 px-4 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">
            <span class="material-symbols-outlined text-[18px]">add</span> Tambah Parameter
        </a>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-surface-container text-on-surface-variant uppercase">
                <tr>
                    <th class="px-3 py-3 text-center w-14">Urut</th>
                    <th class="px-3 py-3 text-left">Kelompok</th>
                    <th class="px-3 py-3 text-left">Pertanyaan</th>
                    <th class="px-3 py-3 text-left">Jenis</th>
                    <th class="px-3 py-3 text-center">Bobot</th>
                    <th class="px-3 py-3 text-left">Pilihan</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
                @forelse ($parameters as $p)
                    <tr x-show="kel === '' || kel === '{{ $p->kelompok }}'" class="hover:bg-surface-container-low">
                        <td class="px-3 py-3 text-center">{{ $p->urutan }}</td>
                        <td class="px-3 py-3 whitespace-nowrap">{{ GcP::KELOMPOK[$p->kelompok] ?? $p->kelompok }}</td>
                        <td class="px-3 py-3">
                            <p class="font-medium">{{ $p->nama }}</p>
                            @if ($p->pbiParameter)
                                <p class="text-[11px] text-on-surface-variant">Padanan PBI: {{ $p->pbiParameter->nama }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">{{ GcP::TIPE_INPUT[$p->tipe_input] ?? $p->tipe_input }}</td>
                        <td class="px-3 py-3 text-center">{{ rtrim(rtrim(number_format($p->bobot, 2, '.', ''), '0'), '.') }}</td>
                        <td class="px-3 py-3">
                            @if ($p->sumber)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-brand-soft text-brand text-[11px]">
                                    {{ GcP::SUMBER[$p->sumber] ?? $p->sumber }}
                                </span>
                            @elseif ($p->butuhOpsi())
                                {{ $p->opsis->count() }} pilihan
                            @else
                                <span class="text-on-surface-variant">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-medium {{ $p->aktif ? 'bg-brand-soft text-brand' : 'bg-surface-container-highest text-on-surface-variant' }}">
                                {{ $p->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('master.gc-kriteria.edit', $p) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                                </a>
                                <form method="POST" action="{{ route('master.gc-kriteria.destroy', $p) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" data-confirm="Hapus parameter &quot;{{ $p->nama }}&quot;?" data-confirm-variant="error"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-[11px] font-medium transition-colors">
                                        <span class="material-symbols-outlined text-[14px]">delete</span> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-on-surface-variant">Belum ada parameter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
