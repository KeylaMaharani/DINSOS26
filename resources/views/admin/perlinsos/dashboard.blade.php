@extends('layouts.admin')

@section('title', 'Beranda Perlinsos - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Perlinsos — Beranda')

@php
    use App\Models\SigapLaporan as L;

    $role = auth()->user()->role ?? null;
    $card = 'bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)]';
    $kpiBox = 'rounded-2xl bg-slate-50 p-4 shadow-[0_4px_12px_rgba(0,0,0,0.08)]';

    // KPI atas
    $kpis = [];
    if ($laporan) {
        $kpis[] = ['label' => 'Laporan Bencana', 'value' => $laporan['total'], 'sub' => $laporan['bulan_ini'] . ' bulan ini', 'icon' => 'crisis_alert', 'tone' => 'text-primary bg-primary-fixed-dim/40'];
        $kpis[] = ['label' => 'Giliran Saya', 'value' => $laporan['giliran'], 'sub' => 'menunggu tindakan', 'icon' => 'assignment_ind', 'tone' => 'text-warning bg-warning-container'];
        $kpis[] = ['label' => 'Laporan Berjalan', 'value' => $laporan['aktif'], 'sub' => 'belum selesai', 'icon' => 'pending_actions', 'tone' => 'text-secondary bg-secondary-fixed'];
        $kpis[] = ['label' => 'Laporan Selesai', 'value' => $laporan['selesai'], 'sub' => $laporan['ditolak'] . ' ditolak', 'icon' => 'task_alt', 'tone' => 'text-white bg-brand'];
        $kpis[] = ['label' => 'Total Korban', 'value' => $laporan['korban'], 'sub' => 'jiwa terdampak', 'icon' => 'groups', 'tone' => 'text-error bg-error-container'];
    }
    if ($stok) {
        $kpis[] = ['label' => 'Jenis Barang', 'value' => $stok['jenis'], 'sub' => 'barang aktif', 'icon' => 'inventory_2', 'tone' => 'text-success bg-success-container'];
        $kpis[] = ['label' => 'Stok Menipis', 'value' => $stok['menipis'], 'sub' => 'di bawah minimum', 'icon' => 'warning', 'tone' => 'text-warning bg-warning-container'];
        $kpis[] = ['label' => 'Stok Habis', 'value' => $stok['habis'], 'sub' => 'perlu pengadaan', 'icon' => 'production_quantity_limits', 'tone' => 'text-on-surface-variant bg-surface-container'];
    }

    // Aksi cepat
    // $quick = [
    //     ['key' => 'sigap-bencana.laporan', 'label' => 'Laporan Bantuan Bencana', 'icon' => 'crisis_alert', 'route' => 'sigap.index', 'tone' => 'text-primary bg-primary-fixed-dim/40'],
    //     ['key' => 'sigap-bencana.laporan', 'label' => 'Giliran Saya', 'icon' => 'assignment_ind', 'route' => 'sigap.index', 'params' => ['giliran_saya' => 1], 'tone' => 'text-warning bg-warning-container'],
    //     ['key' => 'sigap-bencana', 'label' => 'Buat Laporan', 'icon' => 'add_circle', 'route' => 'sigap.create', 'tone' => 'text-error bg-error-container', 'butuh_buat' => true],
    //     ['key' => 'sigap-bencana.stok', 'label' => 'Stock Barang', 'icon' => 'inventory_2', 'route' => 'sigap.stok.index', 'tone' => 'text-success bg-success-container'],
    //     ['key' => 'sigap-proses', 'label' => 'Proses Bantuan', 'icon' => 'volunteer_activism', 'route' => 'sigap.proses.index', 'tone' => 'text-secondary bg-secondary-fixed'],
    //     ['key' => 'sigap-bencana', 'label' => 'Laporan', 'icon' => 'description', 'route' => 'perlinsos.laporan', 'tone' => 'text-on-surface-variant bg-surface-container'],
    // ];

    // Warna badge status tiket
    $statusTone = [
        L::STATUS_DILAPORKAN => 'bg-warning-container text-on-warning-container',
        L::STATUS_SELESAI => 'bg-success-container text-on-success-container',
        L::STATUS_DITOLAK => 'bg-error-container text-on-error-container',
    ];
    $toneDefault = 'bg-secondary-fixed text-on-secondary-container';

    $pct = $laporan['progres'] ?? 0;
@endphp

@section('content')
    <div class="space-y-6">

        @include('partials.sapaan', ['konteks' => 'Bidang Perlinsos'])

        @if (!$laporan && !$stok)
            <section class="{{ $card }} p-10 text-center text-sm text-on-surface-variant">
                Belum ada modul Perlinsos yang dapat diakses oleh akun ini.
            </section>
        @endif

        {{-- ===== Ringkasan angka ===== --}}
        @if (count($kpis))
            <section class="{{ $card }} p-4 sm:p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ($kpis as $k)
                        <div class="{{ $kpiBox }}">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center {{ $k['tone'] }}">
                                <span class="material-symbols-outlined text-[20px]">{{ $k['icon'] }}</span>
                            </span>
                            <p class="text-[11px] text-on-surface-variant mt-4 truncate">{{ $k['label'] }}</p>
                            <p class="text-xl font-extrabold text-on-surface mt-0.5">{{ number_format($k['value']) }}</p>
                            <p class="text-[10px] text-on-surface-variant mt-0.5 truncate">{{ $k['sub'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== Grafik + Progres ===== --}}
        @if ($laporan)
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div
                    class="lg:col-span-2 rounded-3xl p-5 sm:p-6 bg-gradient-to-b from-[#eaf5fe] to-[#dbeefb] shadow-[0_8px_24px_rgba(10,92,168,0.12)] ring-1 ring-white/60">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-on-surface">Tren Laporan Bencana</p>
                            <p class="text-[11px] text-on-surface-variant mt-0.5">Laporan masuk &amp; laporan selesai per bulan</p>
                        </div>
                        <span class="text-[11px] font-semibold text-on-surface shrink-0">6 bulan terakhir</span>
                    </div>
                    <div class="mt-4 h-64">
                        <canvas id="chartPerlinsos"></canvas>
                    </div>
                </div>

                <div class="{{ $card }} p-5 sm:p-6 flex flex-col">
                    <p class="text-sm font-bold text-on-surface">Penyelesaian Laporan</p>
                    <p class="text-[11px] text-on-surface-variant mt-0.5">{{ number_format($laporan['total']) }} total laporan</p>

                    <div class="flex-1 flex items-center justify-center py-5">
                        <div class="relative w-40 h-40 rounded-full"
                            style="background: conic-gradient(#0a5ca8 {{ $pct }}%, #dbeafb 0);">
                            <div class="absolute inset-4 rounded-full bg-white flex flex-col items-center justify-center shadow-inner">
                                <span class="text-3xl font-extrabold text-brand">{{ $pct }}%</span>
                                <span class="text-[10px] tracking-wide text-on-surface-variant">SELESAI</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="rounded-xl bg-[#eaf2fe] p-3">
                            <p class="text-[9px] tracking-wide text-on-surface-variant">SELESAI</p>
                            <p class="text-sm font-bold text-on-surface">{{ number_format($laporan['selesai']) }}</p>
                        </div>
                        <div class="rounded-xl bg-[#eaf2fe] p-3">
                            <p class="text-[9px] tracking-wide text-on-surface-variant">BERJALAN</p>
                            <p class="text-sm font-bold text-on-surface">{{ number_format($laporan['aktif']) }}</p>
                        </div>
                        <div class="rounded-xl bg-[#eaf2fe] p-3">
                            <p class="text-[9px] tracking-wide text-on-surface-variant">DITOLAK</p>
                            <p class="text-sm font-bold text-on-surface">{{ number_format($laporan['ditolak']) }}</p>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ===== Aksi cepat ===== --}}
        {{-- <section>
            <h2 class="text-lg font-bold text-on-surface mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                @foreach ($quick as $q)
                    @continue(!$role || !$role->hasModule($q['key']) || !\Illuminate\Support\Facades\Route::has($q['route']))
                    @continue(!empty($q['butuh_buat']) && !$bolehMembuat)
                    <a href="{{ route($q['route'], $q['params'] ?? []) }}"
                        class="{{ $card }} h-32 flex flex-col items-center justify-center gap-3 hover:-translate-y-0.5 hover:shadow-[0_12px_28px_rgba(10,92,168,0.18)] transition-all">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center {{ $q['tone'] }}">
                            <span class="material-symbols-outlined text-[22px]">{{ $q['icon'] }}</span>
                        </span>
                        <span class="text-xs font-semibold text-on-surface text-center px-2">{{ $q['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section> --}}

        {{-- ===== Rincian status ===== --}}
        @if ($laporan || $stok)
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                @if ($laporan)
                    {{-- Alur Sigap Bencana --}}
                    <div class="{{ $card }} p-5">
                        <p class="text-sm font-bold text-on-surface">Alur Sigap Bencana</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5 mb-4">Posisi laporan pada tiap tahap</p>

                        <div class="space-y-3">
                            @foreach ($laporan['tahap'] as $t)
                                @php
                                    $n = (int) ($laporan['perStatus'][$t] ?? 0);
                                    $p = $laporan['aktif'] > 0 ? round($n / $laporan['aktif'] * 100) : 0;
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-[11px] mb-1">
                                        <span class="text-on-surface-variant truncate">{{ $statusLabels[$t] ?? $t }}</span>
                                        <span class="font-bold text-on-surface">{{ $n }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-[#dbeafb] overflow-hidden">
                                        <div class="h-full rounded-full bg-brand" style="width: {{ $p }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Jenis bencana --}}
                    <div class="{{ $card }} p-5">
                        <p class="text-sm font-bold text-on-surface">Jenis Bencana</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5 mb-4">Jumlah laporan per jenis</p>

                        @forelse ($laporan['perJenis']->take(6) as $jenis => $n)
                            <div class="flex items-center justify-between py-2 border-b border-outline-variant/30 last:border-0">
                                <span class="text-xs text-on-surface">{{ $jenis }}</span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-secondary-fixed text-on-secondary-container">{{ number_format($n) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant">Belum ada data.</p>
                        @endforelse
                    </div>
                @endif

                {{-- Stok menipis --}}
                @if ($stok)
                    <div class="{{ $card }} p-5">
                        <p class="text-sm font-bold text-on-surface">Stok Menipis</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5 mb-4">Barang di bawah batas minimum</p>

                        @forelse ($menipis as $b)
                            <div class="flex items-center justify-between py-2 border-b border-outline-variant/30 last:border-0">
                                <span class="text-xs text-on-surface truncate pr-2">{{ $b->nama }}</span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold whitespace-nowrap {{ $b->stok == 0 ? 'bg-error-container text-on-error-container' : 'bg-warning-container text-on-warning-container' }}">
                                    {{ number_format($b->stok) }} / {{ number_format($b->stok_minimum) }} {{ $b->satuan }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant">Semua stok masih di atas batas minimum.</p>
                        @endforelse
                    </div>
                @endif
            </section>
        @endif

        {{-- ===== Laporan terbaru ===== --}}
        @if ($laporan)
            <section class="{{ $card }} overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-on-surface">Laporan Bencana Terbaru</p>
                    @if (\Illuminate\Support\Facades\Route::has('sigap.index') && $role->hasModule('sigap-bencana.laporan'))
                        <a href="{{ route('sigap.index') }}" class="text-[11px] font-semibold text-brand hover:underline">Lihat semua</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#e6edf8] text-on-surface-variant uppercase text-[10px] tracking-wide">
                            <tr>
                                <th class="px-5 py-3 font-semibold">No. Tiket</th>
                                <th class="px-3 py-3 font-semibold">Jenis Bencana</th>
                                <th class="px-3 py-3 font-semibold">Lokasi</th>
                                <th class="px-3 py-3 font-semibold">Tgl. Kejadian</th>
                                <th class="px-3 py-3 font-semibold">Menunggu</th>
                                <th class="px-3 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @forelse ($terbaru as $r)
                                <tr class="hover:bg-surface-container-low/60">
                                    <td class="px-5 py-3 font-bold text-brand whitespace-nowrap">
                                        <a href="{{ route('sigap.show', $r) }}" class="hover:underline">{{ $r->no_tiket }}</a>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $r->jenis_bencana }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $r->kelurahan }}{{ $r->kecamatan ? ', ' . $r->kecamatan : '' }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">
                                        {{ $r->tanggal_kejadian ? \Carbon\Carbon::parse($r->tanggal_kejadian)->format('d-m-Y') : '–' }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">{{ $r->currentRole->name ?? '–' }}</td>
                                    <td class="px-3 py-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold whitespace-nowrap {{ $statusTone[$r->status] ?? $toneDefault }}">
                                            {{ $statusLabels[$r->status] ?? $r->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-on-surface-variant">Belum ada laporan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- ===== Mutasi stok terbaru ===== --}}
        @if ($stok)
            <section class="{{ $card }} overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-on-surface">Mutasi Stok Terbaru</p>
                    @if (\Illuminate\Support\Facades\Route::has('sigap.stok.index') && $role->hasModule('sigap-bencana.stok'))
                        <a href="{{ route('sigap.stok.index') }}" class="text-[11px] font-semibold text-brand hover:underline">Lihat semua</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#e6edf8] text-on-surface-variant uppercase text-[10px] tracking-wide">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Barang</th>
                                <th class="px-3 py-3 font-semibold">Jenis</th>
                                <th class="px-3 py-3 font-semibold">Jumlah</th>
                                <th class="px-3 py-3 font-semibold">Saldo</th>
                                <th class="px-3 py-3 font-semibold">Oleh</th>
                                <th class="px-3 py-3 font-semibold">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @forelse ($mutasi as $m)
                                <tr class="hover:bg-surface-container-low/60">
                                    <td class="px-5 py-3 font-bold text-brand whitespace-nowrap">{{ $m->barang->nama ?? '–' }}</td>
                                    <td class="px-3 py-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $m->jenis === 'masuk' ? 'bg-success-container text-on-success-container' : 'bg-warning-container text-on-warning-container' }}">
                                            {{ ucfirst($m->jenis) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ number_format($m->jumlah) }} {{ $m->barang->satuan ?? '' }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">{{ number_format($m->saldo_setelah) }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">{{ $m->user->name ?? '–' }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">{{ optional($m->created_at)->format('d-m-Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-on-surface-variant">Belum ada mutasi stok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const g = @json($grafik);
            const el = document.getElementById('chartPerlinsos');
            if (!el || !g.datasets.length) return;

            new Chart(el, {
                type: 'bar',
                data: {
                    labels: g.labels,
                    datasets: g.datasets.map(d => ({
                        label: d.label,
                        data: d.data,
                        backgroundColor: d.color,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 28,
                    })),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 11 } }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: { color: 'rgba(10,92,168,0.08)' },
                            ticks: { precision: 0, font: { size: 11 } }
                        }
                    },
                },
            });
        });
    </script>
@endpush