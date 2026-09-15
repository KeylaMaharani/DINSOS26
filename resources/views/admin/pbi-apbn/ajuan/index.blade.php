@extends('layouts.admin')

@section('title', 'Ajuan PBI APBN')
@section('page_title', 'PBI APBN — Ajuan')

@section('content')
<div class="space-y-5">

    {{-- Filter --}}
    <form method="GET" class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
            <div class="relative">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant absolute left-3 top-1/2 -translate-y-1/2">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, No. Registrasi..."
                    class="w-full pl-9 pr-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs focus:outline-none focus:ring-2 focus:ring-primary/30" />
            </div>
        </div>
        <div class="w-56">
            <label class="block text-xs text-on-surface-variant mb-1">Tahap Saat Ini</label>
            <select name="status" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs">
                <option value="">Semua Tahap</option>
                @foreach (\App\Models\PbiApbn::STAGES as $key => $stage)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $stage['label'] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-1.5 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-medium">
            Terapkan
        </button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('pbi-apbn.ajuan.index') }}" class="px-4 py-1.5 rounded-lg text-xs text-on-surface-variant hover:text-primary">
                Reset
            </a>
        @endif

        <div class="flex items-center gap-2 text-xs text-on-surface-variant ml-auto">
            Tampilkan
            <select name="tampilkan" onchange="this.form.submit()" form="ajuan-pbi-tampilkan-form"
                class="rounded-lg border border-outline-variant/50 px-2 py-1 text-xs">
                @foreach ([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}" @selected((int) request('tampilkan', 10) === $n)>{{ $n }}</option>
                @endforeach
            </select>
            data
        </div>
    </form>

    {{-- Tabel ajuan --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-on-surface">Daftar Ajuan Berjalan</h2>
            <span class="text-xs text-on-surface-variant">{{ $ajuan->total() }} permohonan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs table-fixed">
                <colgroup>
                    <col style="width: 15%">
                    <col style="width: 20%">
                    <col style="width: 30%">
                    <col style="width: 20%">
                    <col style="width: 15%">
                </colgroup>
                <thead class="bg-surface-container text-on-surface-variant uppercase">
                    <tr>
                        <th class="text-center px-4 py-3 whitespace-nowrap">No. Registrasi</th>
                        <th class="text-center px-4 py-3 whitespace-nowrap">Kepala Keluarga</th>
                        <th class="text-center px-4 py-3 whitespace-nowrap">Alamat</th>
                        <th class="text-center px-4 py-3 whitespace-nowrap">Tahap Saat Ini</th>
                        <th class="text-center px-4 py-3 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($ajuan as $item)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-4 py-3 font-medium text-primary whitespace-nowrap text-center">{{ $item->no_registrasi }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">{{ $item->nama_kepala_keluarga }}</td>
                            <td class="px-4 py-3 text-on-surface-variant truncate text-center" title="{{ $item->kecamatan }}, {{ $item->desa_kelurahan }}">{{ $item->kecamatan }}, {{ $item->desa_kelurahan }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-secondary-fixed text-on-secondary-container">
                                    {{ $item->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('pbi-apbn.ajuan.show', $item) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium transition-colors whitespace-nowrap">
                                    <span class="material-symbols-outlined text-[14px]">visibility</span>
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[32px] block mb-2 opacity-40">inbox</span>
                                Belum ada ajuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-outline-variant/40">
            {{ $ajuan->links() }}
        </div>
    </div>

    {{-- Form tersembunyi untuk fitur Tampilkan data --}}
    <form id="ajuan-pbi-tampilkan-form" method="GET" action="{{ route('pbi-apbn.ajuan.index') }}" class="hidden">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
    </form>
</div>
@endsection