@extends('layouts.admin')

@section('title', 'Ground Check 2026')
@section('page_title', 'Ground Check 2026')

@php
    use App\Models\GcVerifikasi;

    $badge = function ($v) {
        if (! $v) {
            return ['Belum', 'bg-surface-container-highest text-on-surface-variant'];
        }
        $label = GcVerifikasi::STATUS_TEMUAN[$v->status_temuan] ?? $v->status_temuan;
        if ($v->status_temuan === 'ditemukan') {
            return $v->status_entry === 'submit'
                ? [$label . ' · Selesai', 'bg-success-container text-on-success-container']
                : [$label . ' · Draf', 'bg-warning-container text-on-warning-container'];
        }

        return [$label, 'bg-surface-container-highest text-on-surface-variant'];
    };
@endphp

@section('content')
<div class="space-y-5 max-w-7xl">

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="relative overflow-hidden rounded-2xl bg-error text-on-error px-5 py-4 shadow-sm">
            <span class="material-symbols-outlined absolute -right-2 -bottom-3 text-[84px] opacity-15">pending_actions</span>
            <p class="text-[11px] uppercase tracking-wider opacity-80">Belum</p>
            <p class="text-3xl font-extrabold mt-0.5">{{ number_format($belum, 0, ',', '.') }}</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-success text-white px-5 py-4 shadow-sm">
            <span class="material-symbols-outlined absolute -right-2 -bottom-3 text-[84px] opacity-15">task_alt</span>
            <p class="text-[11px] uppercase tracking-wider opacity-80">Sudah</p>
            <p class="text-3xl font-extrabold mt-0.5">{{ number_format($sudah, 0, ',', '.') }}</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-brand text-white px-5 py-4 shadow-sm">
            <span class="material-symbols-outlined absolute -right-2 -bottom-3 text-[84px] opacity-15">monitoring</span>
            <p class="text-[11px] uppercase tracking-wider opacity-80">Progres</p>
            <p class="text-3xl font-extrabold mt-0.5">{{ number_format($progres, 1, ',', '.') }}%</p>
            <div class="mt-2 h-1.5 rounded-full bg-white/25 overflow-hidden">
                <div class="h-full bg-white rounded-full" style="width: {{ min($progres, 100) }}%"></div>
            </div>
        </div>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl overflow-hidden shadow-sm">
        {{-- Header & pencarian --}}
        <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/30">
            <div>
                <h2 class="text-sm font-bold text-on-surface">Daftar Sasaran Ground Check 2026</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">
                    Klik <span class="font-semibold">Detail</span> pada kolom Aksi untuk melihat anggota KK dan mulai memasukkan data.
                </p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <select name="per_page" onchange="this.form.submit()"
                        class="rounded-lg border border-outline-variant/50 px-2 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-primary/30">
                    @foreach ([10, 25, 50] as $n)
                        <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }} / halaman</option>
                    @endforeach
                </select>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-on-surface-variant">search</span>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, NIK, atau No KK"
                           class="w-60 rounded-lg border border-outline-variant/50 pl-8 pr-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary/30" />
                </div>
                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-primary text-on-primary text-xs font-semibold hover:bg-secondary transition-colors">
                    Cari
                </button>
            </form>
        </div>

        {{-- Tabel tanpa scroll horizontal --}}
        <table class="w-full text-xs">
            <thead class="bg-surface-container text-on-surface-variant uppercase text-[10.5px] tracking-wider">
                <tr>
                    <th class="pl-5 pr-2 py-3 text-center w-10">No</th>
                    <th class="px-3 py-3 text-left">Nama / NIK</th>
                    <th class="px-3 py-3 text-left">No KK / Tgl Lahir</th>
                    <th class="px-3 py-3 text-left">Wilayah</th>
                    <th class="px-3 py-3 text-left">Alamat</th>
                    <th class="px-3 py-3 text-center">Desil</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-left">Progress KK</th>
                    <th class="pl-3 pr-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
                @forelse ($sasarans as $s)
                    @php
                        [$label, $kelas] = $badge($s->verifikasis->first());
                        $inisial = mb_strtoupper(mb_substr($s->nama ?? '?', 0, 1));
                        $pct = $s->kk_total > 0 ? round($s->kk_selesai / $s->kk_total * 100) : 0;
                    @endphp
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="pl-5 pr-2 py-3 text-center text-on-surface-variant">{{ $sasarans->firstItem() + $loop->index }}</td>

                        {{-- Nama / NIK --}}
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2.5">
                                <span class="shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary text-[12px] font-bold flex items-center justify-center">{{ $inisial }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-on-surface leading-tight">{{ $s->nama }}</p>
                                    <p class="text-[11px] text-on-surface-variant mt-0.5 tabular-nums whitespace-nowrap">{{ $s->nik }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- No KK / Tgl Lahir --}}
                        <td class="px-3 py-3">
                            <p class="text-on-surface tabular-nums whitespace-nowrap">{{ $s->no_kk }}</p>
                            <p class="text-[11px] text-on-surface-variant mt-0.5 inline-flex items-center gap-1 whitespace-nowrap">
                                <span class="material-symbols-outlined text-[13px]">cake</span>
                                {{ optional($s->tanggal_lahir)->format('d-m-Y') ?? '—' }}
                            </p>
                        </td>

                        {{-- Wilayah --}}
                        <td class="px-3 py-3">
                            <p class="font-medium text-on-surface leading-tight">{{ $s->kelurahan ?? '—' }}</p>
                            <p class="text-[11px] text-on-surface-variant mt-0.5">Kec. {{ $s->kecamatan ?? '—' }}</p>
                        </td>

                        {{-- Alamat --}}
                        <td class="px-3 py-3">
                            <p class="leading-tight">{{ $s->alamat ?? '—' }}</p>
                            <div class="mt-1 flex gap-1">
                                <span class="px-1.5 py-0.5 rounded bg-surface-container text-[10px] font-medium text-on-surface-variant">RT {{ $s->rt ?? '—' }}</span>
                                <span class="px-1.5 py-0.5 rounded bg-surface-container text-[10px] font-medium text-on-surface-variant">RW {{ $s->rw ?? '—' }}</span>
                            </div>
                        </td>

                        {{-- Desil --}}
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex w-7 h-7 items-center justify-center rounded-full bg-primary/10 text-primary font-bold">{{ $s->desil ?? '—' }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium whitespace-nowrap {{ $kelas }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $label }}
                            </span>
                        </td>

                        {{-- Progress KK --}}
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2 min-w-[84px]">
                                <div class="flex-1 h-1.5 rounded-full bg-surface-container-highest overflow-hidden">
                                    <div class="h-full rounded-full bg-success" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold tabular-nums whitespace-nowrap">{{ $s->kk_selesai }}/{{ $s->kk_total }}</span>
                            </div>
                        </td>

                        {{-- Aksi --}}
                        <td class="pl-3 pr-5 py-3 text-center">
                            <a href="{{ route('ground-check.keluarga', $s->no_kk) }}" title="Lihat detail & anggota KK"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary text-on-primary text-[11px] font-semibold shadow-sm hover:bg-secondary transition-colors whitespace-nowrap">
                                <span class="material-symbols-outlined text-[15px]">visibility</span>
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-12 text-center text-on-surface-variant">
                        <span class="material-symbols-outlined text-[32px] opacity-40 block mb-1">search_off</span>
                        {{ $q !== '' ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada data sasaran.' }}
                    </td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-3 border-t border-outline-variant/30 flex flex-wrap items-center justify-between gap-2 text-xs text-on-surface-variant">
            <span>Menampilkan {{ $sasarans->firstItem() ?? 0 }} sampai {{ $sasarans->lastItem() ?? 0 }} dari {{ number_format($sasarans->total(), 0, ',', '.') }} data</span>
            <div>{{ $sasarans->links() }}</div>
        </div>
    </div>
</div>
@endsection
