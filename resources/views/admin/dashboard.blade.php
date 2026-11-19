@extends('layouts.admin')

@section('title', 'Beranda - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Beranda')

@php
    $role = auth()->user()->role ?? null;
    $card = 'bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)]';

    // KPI atas (hanya modul yang boleh diakses role)
    $kpis = [];
    if ($pbi) {
        $kpis[] = ['label' => 'PBI APBD', 'value' => $pbi['total'], 'sub' => $pbi['bulan_ini'] . ' bulan ini', 'icon' => 'health_and_safety', 'tone' => 'text-primary bg-primary-fixed-dim/40'];
    }
    if ($kks) {
        $kpis[] = ['label' => 'Kartu KKS', 'value' => $kks['total'], 'sub' => $kks['berjalan'] . ' berjalan', 'icon' => 'credit_card', 'tone' => 'text-warning bg-warning-container'];
    }
    if ($dtsen) {
        $kpis[] = ['label' => 'DTSEN', 'value' => $dtsen['total'], 'sub' => $dtsen['berjalan'] . ' berjalan', 'icon' => 'database', 'tone' => 'text-success bg-success-container'];
    }
    if ($gc) {
        $kpis[] = ['label' => 'Ground Check', 'value' => $gc['progres'] . '%', 'sub' => $gc['sudah'] . ' / ' . $gc['total'] . ' KK', 'icon' => 'fact_check', 'tone' => 'text-error bg-error-container'];
    }
    if ($ngopi) {
        $kpis[] = ['label' => 'Ngopi Pagi Aktif', 'value' => $ngopi['aktif'], 'sub' => $ngopi['segera'] . ' butuh segera', 'icon' => 'forum', 'tone' => 'text-secondary bg-secondary-fixed'];
    }
    if ($sigap) {
        $kpis[] = ['label' => 'Sigap Bencana Aktif', 'value' => $sigap['aktif'], 'sub' => $sigap['selesai'] . ' selesai', 'icon' => 'crisis_alert', 'tone' => 'text-error bg-error-container'];
    }
    if ($kesan) {
        $kpis[] = ['label' => 'KESAN Menunggu', 'value' => $kesan['menunggu'], 'sub' => $kesan['penyaluran'] . ' penyaluran', 'icon' => 'volunteer_activism', 'tone' => 'text-white bg-brand'];
    }

    // Aksi cepat
    // $quick = [
    //     ['key' => 'pbi-apbd.ajuan', 'label' => 'PBI APBD', 'icon' => 'health_and_safety', 'route' => 'pbi-apbd.ajuan.index', 'tone' => 'text-primary bg-primary-fixed-dim/40'],
    //     ['key' => 'kartu-kks.ajuan', 'label' => 'Kartu KKS', 'icon' => 'credit_card', 'route' => 'kartu-kks.ajuan', 'tone' => 'text-warning bg-warning-container'],
    //     ['key' => 'dtsen.ajuan', 'label' => 'DTSEN', 'icon' => 'database', 'route' => 'dtsen.ajuan', 'tone' => 'text-success bg-success-container'],
    //     ['key' => 'ground-check.daftar', 'label' => 'Ground Check', 'icon' => 'fact_check', 'route' => 'ground-check.index', 'tone' => 'text-error bg-error-container'],
    //     ['key' => 'dayasos-ngopi-pagi.chat_masuk', 'label' => 'Ngopi Pagi', 'icon' => 'forum', 'route' => 'ngopi-pagi.admin.tahap.chat_masuk', 'tone' => 'text-secondary bg-secondary-fixed'],
    //     ['key' => 'sigap-bencana.laporan', 'label' => 'Sigap Bencana', 'icon' => 'crisis_alert', 'route' => 'sigap.index', 'tone' => 'text-error bg-error-container'],
    //     ['key' => 'kesan.pilihan', 'label' => 'KESAN', 'icon' => 'volunteer_activism', 'route' => 'kesan-admin.pilihan.index', 'tone' => 'text-white bg-brand'],
    //     ['key' => 'dokumen.daftar', 'label' => 'Dokumen', 'icon' => 'description', 'route' => 'dokumen.index', 'tone' => 'text-on-surface-variant bg-surface-container'],
    // ];

    $tones = [
        'success' => 'bg-success-container text-on-success-container',
        'error' => 'bg-error-container text-on-error-container',
        'warning' => 'bg-warning-container text-on-warning-container',
    ];

    // Kartu rincian status per layanan PFM
    $rincian = array_filter([
        ['judul' => 'Status PBI APBD', 'data' => $pbi, 'pill' => 'bg-secondary-fixed text-on-secondary-container'],
        ['judul' => 'Status Kartu KKS', 'data' => $kks, 'pill' => 'bg-warning-container text-on-warning-container'],
        ['judul' => 'Status DTSEN', 'data' => $dtsen, 'pill' => 'bg-success-container text-on-success-container'],
    ], fn ($r) => $r['data']);
@endphp

@section('content')
    <div class="space-y-6">

        @include('partials.sapaan')

        @if (!count($kpis) && !$stok)
            <section class="{{ $card }} p-10 text-center text-sm text-on-surface-variant">
                Belum ada data yang dapat ditampilkan untuk akun ini.
            </section>
        @endif

        {{-- ===== Ringkasan angka ===== --}}
        @if (count($kpis))
            <section class="{{ $card }} p-4 sm:p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ($kpis as $k)
                        <div class="rounded-2xl bg-slate-50 p-4 shadow-[0_4px_12px_rgba(0,0,0,0.08)]">
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

        {{-- ===== Grafik + Stok ===== --}}
        @if (count($grafik['datasets']))
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div
                    class="{{ $stok ? 'lg:col-span-2' : 'lg:col-span-3' }} rounded-3xl p-5 sm:p-6 bg-gradient-to-b from-[#eaf5fe] to-[#dbeefb] shadow-[0_8px_24px_rgba(10,92,168,0.12)] ring-1 ring-white/60">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-on-surface">Tren Pengajuan</p>
                            <p class="text-[11px] text-on-surface-variant mt-0.5">Data masuk per layanan setiap bulan</p>
                        </div>
                        <span class="text-[11px] font-semibold text-on-surface shrink-0">6 bulan terakhir</span>
                    </div>
                    <div class="mt-4 h-64">
                        <canvas id="chartPengajuan"></canvas>
                    </div>
                </div>

                @if ($stok)
                    <div class="{{ $card }} p-5 sm:p-6 flex flex-col">
                        <p class="text-sm font-bold text-on-surface">Stok Bantuan</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5">Pemakaian stok Sigap Bencana</p>

                        <div class="flex-1 flex items-center justify-center py-5">
                            <div class="relative w-40 h-40 rounded-full"
                                style="background: conic-gradient(#0a5ca8 {{ $stok['pct'] }}%, #dbeafb 0);">
                                <div class="absolute inset-4 rounded-full bg-white flex flex-col items-center justify-center shadow-inner">
                                    <span class="text-3xl font-extrabold text-brand">{{ $stok['pct'] }}%</span>
                                    <span class="text-[10px] tracking-wide text-on-surface-variant">TERPAKAI</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-[#eaf2fe] p-3">
                                <p class="text-[9px] tracking-wide text-on-surface-variant">TERPAKAI</p>
                                <p class="text-sm font-bold text-on-surface">{{ number_format($stok['keluar']) }} Unit</p>
                            </div>
                            <div class="rounded-xl bg-[#eaf2fe] p-3">
                                <p class="text-[9px] tracking-wide text-on-surface-variant">SISA STOK</p>
                                <p class="text-sm font-bold text-on-surface">{{ number_format($stok['saldo']) }} Unit</p>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        {{-- ===== Aksi cepat ===== --}}
        {{-- <section>
            <h2 class="text-lg font-bold text-on-surface mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-4">
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
        </section> --}}

        {{-- ===== Rincian status PFM ===== --}}
        @if (count($rincian))
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @foreach ($rincian as $r)
                    <div class="{{ $card }} p-5">
                        <p class="text-sm font-bold text-on-surface">{{ $r['judul'] }}</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5 mb-4">{{ number_format($r['data']['total']) }} total pengajuan</p>

                        @forelse ($r['data']['status']->take(6) as $st => $n)
                            <div class="flex items-center justify-between py-2 border-b border-outline-variant/30 last:border-0">
                                <span class="text-xs text-on-surface">{{ $labelStatus($st) }}</span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $r['pill'] }}">{{ number_format($n) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant">Belum ada data.</p>
                        @endforelse
                    </div>
                @endforeach
            </section>
        @endif

        {{-- ===== Aktivitas terbaru lintas layanan ===== --}}
        @if ($aktivitas->count())
            <section class="{{ $card }} overflow-hidden">
                <div class="px-5 py-4">
                    <p class="text-sm font-bold text-on-surface">Aktivitas Terbaru</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#e6edf8] text-on-surface-variant uppercase text-[10px] tracking-wide">
                            <tr>
                                <th class="px-5 py-3 font-semibold">ID</th>
                                <th class="px-3 py-3 font-semibold">Nama / Keterangan</th>
                                <th class="px-3 py-3 font-semibold">Layanan</th>
                                <th class="px-3 py-3 font-semibold">Tanggal</th>
                                <th class="px-3 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @foreach ($aktivitas as $a)
                                <tr class="hover:bg-surface-container-low/60">
                                    <td class="px-5 py-3 font-bold text-brand whitespace-nowrap">
                                        <a href="{{ $a['url'] }}" class="hover:underline">{{ $a['id'] }}</a>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $a['nama'] }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $a['layanan'] }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">{{ optional($a['tgl'])->format('d-m-Y') }}</td>
                                    <td class="px-3 py-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold whitespace-nowrap {{ $tones[$a['tone']] }}">{{ $a['status'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
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
            const el = document.getElementById('chartPengajuan');
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
                        maxBarThickness: 22,
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