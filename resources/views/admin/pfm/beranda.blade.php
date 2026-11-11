@extends('layouts.admin')

@section('title', 'Beranda PFM - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'PFM — Beranda')

@php
    $role = auth()->user()->role ?? null;
    $card = 'bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)]';
    $kpiBox = 'rounded-2xl bg-slate-50 p-4 shadow-[0_4px_12px_rgba(0,0,0,0.08)]';

    // KPI atas
    $kpis = [];
    if ($pbi) {
        $kpis[] = ['label' => 'PBI APBD', 'value' => $pbi['total'], 'sub' => $pbi['bulan_ini'] . ' bulan ini', 'icon' => 'health_and_safety', 'tone' => 'text-primary bg-primary-fixed-dim/40'];
    }
    if ($kks) {
        $kpis[] = ['label' => 'Kartu KKS', 'value' => $kks['total'], 'sub' => $kks['bulan_ini'] . ' bulan ini', 'icon' => 'credit_card', 'tone' => 'text-warning bg-warning-container'];
        $kpis[] = ['label' => 'KKS Sedang Berjalan', 'value' => $kks['berjalan'], 'sub' => 'menunggu tindakan', 'icon' => 'pending_actions', 'tone' => 'text-secondary bg-secondary-fixed'];
        $kpis[] = ['label' => 'KKS Selesai', 'value' => $kks['selesai'], 'sub' => $kks['ditolak'] . ' ditolak', 'icon' => 'task_alt', 'tone' => 'text-white bg-brand'];
    }
    if ($dtsen) {
        $kpis[] = ['label' => 'DTSEN', 'value' => $dtsen['total'], 'sub' => $dtsen['bulan_ini'] . ' bulan ini', 'icon' => 'database', 'tone' => 'text-success bg-success-container'];
    }
    if ($gc) {
        $kpis[] = ['label' => 'Ground Check', 'value' => $gc['progres'] . '%', 'sub' => $gc['sudah'] . ' / ' . $gc['total'] . ' orang', 'icon' => 'fact_check', 'tone' => 'text-error bg-error-container'];
    }

    // Aksi cepat
    $quick = [
        ['key' => 'pbi-apbd', 'label' => 'Ajuan PBI APBD', 'icon' => 'health_and_safety', 'route' => 'pbi-apbd.ajuan.index', 'tone' => 'text-primary bg-primary-fixed-dim/40'],
        ['key' => 'kartu-kks', 'label' => 'Ajuan Kartu KKS', 'icon' => 'credit_card', 'route' => 'kartu-kks.ajuan', 'tone' => 'text-warning bg-warning-container'],
        ['key' => 'dtsen', 'label' => 'Ajuan DTSEN', 'icon' => 'database', 'route' => 'dtsen.ajuan', 'tone' => 'text-success bg-success-container'],
        ['key' => 'ground-check.daftar', 'label' => 'Ground Check', 'icon' => 'fact_check', 'route' => 'ground-check.index', 'tone' => 'text-error bg-error-container'],
        ['key' => 'kartu-kks.monitoring', 'label' => 'Monitoring KKS', 'icon' => 'monitoring', 'route' => 'kartu-kks.monitoring', 'tone' => 'text-secondary bg-secondary-fixed'],
        ['key' => 'kartu-kks.arsip', 'label' => 'Arsip KKS', 'icon' => 'inventory_2', 'route' => 'kartu-kks.arsip', 'tone' => 'text-on-surface-variant bg-surface-container'],
    ];

    $statusTone = [
        'diajukan' => 'bg-warning-container text-on-warning-container',
        'diproses_kabin' => 'bg-secondary-fixed text-on-secondary-container',
        'disetujui_kadis' => 'bg-secondary-fixed text-on-secondary-container',
        'validasi_dinsos' => 'bg-primary-fixed-dim/40 text-primary',
        'selesai' => 'bg-success-container text-on-success-container',
        'ditolak' => 'bg-error-container text-on-error-container',
    ];

    $gcPct = $gc['progres'] ?? 0;
@endphp

@section('content')
    <div class="space-y-6">

        {{-- ===== Ringkasan angka ===== --}}
        @if (count($kpis))
            <section class="{{ $card }} p-4 sm:p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-{{ min(count($kpis), 6) }} gap-4">
                    @foreach ($kpis as $k)
                        <div class="{{ $kpiBox }}">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center {{ $k['tone'] }}">
                                <span class="material-symbols-outlined text-[20px]">{{ $k['icon'] }}</span>
                            </span>
                            <p class="text-[11px] text-on-surface-variant mt-4 truncate">{{ $k['label'] }}</p>
                            <p class="text-xl font-extrabold text-on-surface mt-0.5">
                                {{ is_numeric($k['value']) ? number_format($k['value']) : $k['value'] }}</p>
                            <p class="text-[10px] text-on-surface-variant mt-0.5 truncate">{{ $k['sub'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== Grafik + Ground Check ===== --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div
                class="lg:col-span-2 rounded-3xl p-5 sm:p-6 bg-gradient-to-b from-[#eaf5fe] to-[#dbeefb] shadow-[0_8px_24px_rgba(10,92,168,0.12)] ring-1 ring-white/60">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-on-surface">Tren Pengajuan PFM</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5">PBI APBD, Kartu KKS &amp; DTSEN per bulan</p>
                    </div>
                    <span class="text-[11px] font-semibold text-on-surface shrink-0">6 bulan terakhir</span>
                </div>
                <div class="mt-4 h-64">
                    <canvas id="chartPfm"></canvas>
                </div>
            </div>

            @if ($gc)
                <div class="{{ $card }} p-5 sm:p-6 flex flex-col">
                    <p class="text-sm font-bold text-on-surface">Progres Ground Check 2026</p>
                    <p class="text-[11px] text-on-surface-variant mt-0.5">{{ number_format($gc['total_kk']) }} KK &middot;
                        {{ number_format($gc['total']) }} sasaran</p>

                    <div class="flex-1 flex items-center justify-center py-5">
                        <div class="relative w-40 h-40 rounded-full"
                            style="background: conic-gradient(#0a5ca8 {{ $gcPct }}%, #dbeafb 0);">
                            <div
                                class="absolute inset-4 rounded-full bg-white flex flex-col items-center justify-center shadow-inner">
                                <span class="text-3xl font-extrabold text-brand">{{ $gcPct }}%</span>
                                <span class="text-[10px] tracking-wide text-on-surface-variant">SELESAI</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="rounded-xl bg-[#eaf2fe] p-3">
                            <p class="text-[9px] tracking-wide text-on-surface-variant">SUBMIT</p>
                            <p class="text-sm font-bold text-on-surface">{{ number_format($gc['sudah']) }}</p>
                        </div>
                        <div class="rounded-xl bg-[#eaf2fe] p-3">
                            <p class="text-[9px] tracking-wide text-on-surface-variant">DRAF</p>
                            <p class="text-sm font-bold text-on-surface">{{ number_format($gc['draft']) }}</p>
                        </div>
                        <div class="rounded-xl bg-[#eaf2fe] p-3">
                            <p class="text-[9px] tracking-wide text-on-surface-variant">BELUM</p>
                            <p class="text-sm font-bold text-on-surface">{{ number_format($gc['belum']) }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- ===== Aksi cepat ===== --}}
        <section>
            <h2 class="text-lg font-bold text-on-surface mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                @foreach ($quick as $q)
                    @continue(!$role || !$role->hasModule($q['key']) || !\Illuminate\Support\Facades\Route::has($q['route']))
                    <a href="{{ route($q['route']) }}"
                        class="{{ $card }} h-32 flex flex-col items-center justify-center gap-3 hover:-translate-y-0.5 hover:shadow-[0_12px_28px_rgba(10,92,168,0.18)] transition-all">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center {{ $q['tone'] }}">
                            <span class="material-symbols-outlined text-[22px]">{{ $q['icon'] }}</span>
                        </span>
                        <span class="text-xs font-semibold text-on-surface text-center px-2">{{ $q['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ===== Rincian status per layanan ===== --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Alur Kartu KKS --}}
            @if ($kks)
                <div class="{{ $card }} p-5">
                    <p class="text-sm font-bold text-on-surface">Alur Kartu KKS</p>
                    <p class="text-[11px] text-on-surface-variant mt-0.5 mb-4">Posisi berkas pada tiap tahap</p>

                    <div class="space-y-3">
                        @foreach ($kks['tahap'] as $s)
                            @php
                                $n = (int) ($kks['status'][$s] ?? 0);
                                $pct = $kks['berjalan'] > 0 ? round($n / $kks['berjalan'] * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-on-surface-variant truncate">{{ $kksLabels[$s] ?? $s }}</span>
                                    <span class="font-bold text-on-surface">{{ $n }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-[#dbeafb] overflow-hidden">
                                    <div class="h-full rounded-full bg-brand" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Status PBI APBD --}}
            @if ($pbi)
                <div class="{{ $card }} p-5">
                    <p class="text-sm font-bold text-on-surface">Status PBI APBD</p>
                    <p class="text-[11px] text-on-surface-variant mt-0.5 mb-4">{{ number_format($pbi['total']) }} total pengajuan</p>

                    @forelse (collect($pbi['status'])->take(6) as $st => $n)
                        <div class="flex items-center justify-between py-2 border-b border-outline-variant/30 last:border-0">
                            <span class="text-xs text-on-surface">{{ ucfirst(str_replace('_', ' ', (string) $st)) }}</span>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-secondary-fixed text-on-secondary-container">{{ number_format($n) }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-on-surface-variant">Belum ada data.</p>
                    @endforelse
                </div>
            @endif

            {{-- Status DTSEN --}}
            @if ($dtsen)
                <div class="{{ $card }} p-5">
                    <p class="text-sm font-bold text-on-surface">Status DTSEN</p>
                    <p class="text-[11px] text-on-surface-variant mt-0.5 mb-4">{{ number_format($dtsen['total']) }} total pengajuan</p>

                    @forelse (collect($dtsen['status'])->take(6) as $st => $n)
                        <div class="flex items-center justify-between py-2 border-b border-outline-variant/30 last:border-0">
                            <span class="text-xs text-on-surface">{{ ucfirst(str_replace('_', ' ', (string) $st)) }}</span>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-success-container text-on-success-container">{{ number_format($n) }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-on-surface-variant">Belum ada data.</p>
                    @endforelse
                </div>
            @endif
        </section>

        {{-- ===== Ajuan Kartu KKS terbaru ===== --}}
        @if ($kks)
            <section class="{{ $card }} overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-on-surface">Ajuan Kartu KKS Terbaru</p>
                    @if (\Illuminate\Support\Facades\Route::has('kartu-kks.ajuan') && $role->hasModule('kartu-kks.ajuan'))
                        <a href="{{ route('kartu-kks.ajuan') }}" class="text-[11px] font-semibold text-brand hover:underline">Lihat semua</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#e6edf8] text-on-surface-variant uppercase text-[10px] tracking-wide">
                            <tr>
                                <th class="px-5 py-3 font-semibold">No. Permohonan</th>
                                <th class="px-3 py-3 font-semibold">Pemohon</th>
                                <th class="px-3 py-3 font-semibold">Masalah Kartu</th>
                                <th class="px-3 py-3 font-semibold">Tanggal</th>
                                <th class="px-3 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @forelse ($kksTerbaru as $r)
                                <tr class="hover:bg-surface-container-low/60">
                                    <td class="px-5 py-3 font-bold text-brand whitespace-nowrap">
                                        @if ($role->hasModule('kartu-kks.ajuan'))
                                            <a href="{{ route('kartu-kks.show', $r) }}" class="hover:underline">{{ $r->no_permohonan }}</a>
                                        @else
                                            {{ $r->no_permohonan }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $r->nama_pemohon }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap capitalize">{{ $r->masalah_kartu }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">
                                        {{ optional($r->tanggal_insert)->format('d-m-Y') }}</td>
                                    <td class="px-3 py-3">
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-semibold whitespace-nowrap {{ $statusTone[$r->status] ?? 'bg-surface-container text-on-surface-variant' }}">
                                            {{ $kksLabels[$r->status] ?? $r->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-on-surface-variant">Belum ada ajuan.</td>
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
            const el = document.getElementById('chartPfm');
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