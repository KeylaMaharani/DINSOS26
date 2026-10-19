@extends('layouts.admin')

@section('title', 'Ground Check 2026')
@section('page_title', 'Ground Check 2026')

@php
    use App\Models\GcVerifikasi;

    $badge = function ($v) {
        if (! $v) {
            return ['—', 'bg-surface-container-highest text-on-surface-variant'];
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
        <div class="rounded-xl bg-error text-on-error px-5 py-4">
            <p class="text-[11px] uppercase tracking-wide opacity-80">Belum</p>
            <p class="text-2xl font-extrabold mt-0.5">{{ number_format($belum, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-success text-white px-5 py-4">
            <p class="text-[11px] uppercase tracking-wide opacity-80">Sudah</p>
            <p class="text-2xl font-extrabold mt-0.5">{{ number_format($sudah, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-brand text-white px-5 py-4">
            <p class="text-[11px] uppercase tracking-wide opacity-80">Progres</p>
            <p class="text-2xl font-extrabold mt-0.5">{{ number_format($progres, 1, ',', '.') }}%</p>
            <div class="mt-2 h-1.5 rounded-full bg-white/25 overflow-hidden">
                <div class="h-full bg-white rounded-full" style="width: {{ min($progres, 100) }}%"></div>
            </div>
        </div>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/30">
            <div>
                <h2 class="text-sm font-bold text-on-surface">Daftar Sasaran Ground Check 2026</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">
                    Klik ikon keluarga pada baris untuk melihat anggota KK dan mulai memasukkan data.
                </p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <select name="per_page" onchange="this.form.submit()"
                        class="rounded-lg border border-outline-variant/50 px-2 py-1.5 text-xs bg-white">
                    @foreach ([10, 25, 50] as $n)
                        <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }} / halaman</option>
                    @endforeach
                </select>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, NIK, atau No KK"
                       class="w-56 rounded-lg border border-outline-variant/50 px-3 py-1.5 text-xs" />
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-primary text-on-primary text-xs font-semibold hover:bg-secondary">
                    Cari
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-surface-container text-on-surface-variant uppercase">
                    <tr>
                        <th class="px-3 py-3 text-center w-14">Act</th>
                        <th class="px-3 py-3 text-left">Nama</th>
                        <th class="px-3 py-3 text-left">NIK</th>
                        <th class="px-3 py-3 text-left">No KK</th>
                        <th class="px-3 py-3 text-left">Tanggal Lahir</th>
                        <th class="px-3 py-3 text-left">Kecamatan</th>
                        <th class="px-3 py-3 text-left">Kelurahan</th>
                        <th class="px-3 py-3 text-left">Alamat</th>
                        <th class="px-3 py-3 text-center">RT</th>
                        <th class="px-3 py-3 text-center">RW</th>
                        <th class="px-3 py-3 text-center">Desil</th>
                        <th class="px-3 py-3 text-center">Status</th>
                        <th class="px-3 py-3 text-center">Progress 1 KK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($sasarans as $s)
                        @php [$label, $kelas] = $badge($s->verifikasis->first()); @endphp
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-3 py-2.5 text-center">
                                <a href="{{ route('ground-check.keluarga', $s->no_kk) }}" title="Lihat anggota keluarga"
                                   class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-secondary text-white hover:bg-primary transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">family_restroom</span>
                                </a>
                            </td>
                            <td class="px-3 py-2.5 font-medium whitespace-nowrap">{{ $s->nama }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap">{{ $s->nik }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap">{{ $s->no_kk }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap">{{ optional($s->tanggal_lahir)->format('d-m-Y') ?? '—' }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap">{{ $s->kecamatan ?? '—' }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap">{{ $s->kelurahan ?? '—' }}</td>
                            <td class="px-3 py-2.5">{{ $s->alamat ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-center">{{ $s->rt ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-center">{{ $s->rw ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-center">{{ $s->desil ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-medium whitespace-nowrap {{ $kelas }}">{{ $label }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">{{ $s->kk_selesai }}/{{ $s->kk_total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="13" class="px-4 py-10 text-center text-on-surface-variant">
                            {{ $q !== '' ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada data sasaran.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-outline-variant/30 flex flex-wrap items-center justify-between gap-2 text-xs text-on-surface-variant">
            <span>Menampilkan {{ $sasarans->firstItem() ?? 0 }} sampai {{ $sasarans->lastItem() ?? 0 }} dari {{ number_format($sasarans->total(), 0, ',', '.') }} data</span>
            <div>{{ $sasarans->links() }}</div>
        </div>
    </div>
</div>
@endsection
