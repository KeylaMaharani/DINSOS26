@extends('layouts.admin')

@section('title', 'Beranda - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Beranda')

@php
    $role = auth()->user()->role ?? null;
    $card = 'bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)]';

    // Ringkasan angka (dummy)
    $kpis = [
        ['label' => 'Asesmen SPMB', 'value' => '128', 'trend' => '+12%', 'up' => true, 'icon' => 'fact_check', 'tone' => 'text-secondary bg-secondary-fixed'],
        ['label' => 'Reaktivasi PBI APBD', 'value' => '76', 'trend' => '+8%', 'up' => true, 'icon' => 'health_and_safety', 'tone' => 'text-primary bg-primary-fixed-dim/40'],
        ['label' => 'Kedaruratan Medis', 'value' => '19', 'trend' => '-3%', 'up' => false, 'icon' => 'emergency', 'tone' => 'text-error bg-error-container'],
        ['label' => 'Kartu KKS', 'value' => '54', 'trend' => '+6%', 'up' => true, 'icon' => 'credit_card', 'tone' => 'text-warning bg-warning-container'],
        ['label' => 'Menunggu Verifikasi', 'value' => '60', 'trend' => '14 baru', 'up' => null, 'icon' => 'pending_actions', 'tone' => 'text-warning bg-warning-container'],
        ['label' => 'Selesai', 'value' => '217', 'trend' => '+21%', 'up' => true, 'icon' => 'task_alt', 'tone' => 'text-white bg-brand'],
    ];

    // Stok bantuan (dummy)
    $stokTotal = 529;
    $stokTerpakai = 186;
    $stokPct = round(($stokTerpakai / $stokTotal) * 100);

    // Aksi cepat
    $quick = [
        ['key' => 'asesmen-spmb', 'label' => 'Asesmen SPMB', 'icon' => 'fact_check', 'route' => 'asesmen_spmb.ajuan', 'tone' => 'text-secondary bg-secondary-fixed'],
        ['key' => 'pbi-apbn', 'label' => 'PBI APBN', 'icon' => 'health_and_safety', 'route' => 'pbi-apbn.ajuan.index', 'tone' => 'text-primary bg-primary-fixed-dim/40'],
        ['key' => 'kedaruratan-medis', 'label' => 'Kedaruratan Medis', 'icon' => 'emergency', 'route' => 'kedaruratan_medis.ajuan', 'tone' => 'text-error bg-error-container'],
        ['key' => 'kartu-kks', 'label' => 'Kartu KKS', 'icon' => 'credit_card', 'route' => 'kartu-kks.ajuan', 'tone' => 'text-warning bg-warning-container'],
        ['key' => 'dtsen', 'label' => 'DTSEN', 'icon' => 'database', 'route' => 'dtsen.ajuan', 'tone' => 'text-success bg-success-container'],
        ['key' => 'dokumen', 'label' => 'Dokumen', 'icon' => 'description', 'route' => 'dokumen.index', 'tone' => 'text-on-surface-variant bg-surface-container'],
    ];

    // Ajuan terbaru (dummy)
    $recent = [
        ['id' => '#AJU-2041', 'name' => 'Siti Aminah', 'layanan' => 'Asesmen SPMB', 'date' => '05 Okt 2026', 'status' => 'Menunggu'],
        ['id' => '#AJU-2040', 'name' => 'Budi Santoso', 'layanan' => 'PBI APBD', 'date' => '05 Okt 2026', 'status' => 'Diverifikasi'],
        ['id' => '#AJU-2039', 'name' => 'Rina Marlina', 'layanan' => 'Kedaruratan Medis', 'date' => '04 Okt 2026', 'status' => 'Selesai'],
        ['id' => '#AJU-2038', 'name' => 'Ahmad Fauzi', 'layanan' => 'Kartu KKS', 'date' => '03 Okt 2026', 'status' => 'Selesai'],
        ['id' => '#AJU-2037', 'name' => 'Dewi Lestari', 'layanan' => 'Asesmen SPMB', 'date' => '02 Okt 2026', 'status' => 'Menunggu'],
    ];
    $statusTone = [
        'Menunggu' => 'bg-warning-container text-on-warning-container',
        'Diverifikasi' => 'bg-secondary-fixed text-on-secondary-container',
        'Selesai' => 'bg-success-container text-on-success-container',
    ];

    // Aktivitas terbaru (dummy)
    $activities = [
        ['icon' => 'person_add', 'title' => 'Ajuan baru masuk', 'desc' => 'Siti Aminah mengajukan Asesmen SPMB', 'time' => '2 jam lalu'],
        ['icon' => 'verified', 'title' => 'Ajuan diverifikasi', 'desc' => 'PBI APBD atas nama Budi Santoso', 'time' => '5 jam lalu'],
        ['icon' => 'inventory_2', 'title' => 'Stok diperbarui', 'desc' => 'Kursi roda terpakai 6 unit', 'time' => 'Kemarin'],
        ['icon' => 'task_alt', 'title' => 'Ajuan selesai', 'desc' => 'Kedaruratan Medis atas nama Rina Marlina', 'time' => 'Kemarin'],
    ];
@endphp

@section('content')
    <div class="space-y-6">

        {{-- ===== Ringkasan angka ===== --}}
        <section class="{{ $card }} p-4 sm:p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                @foreach ($kpis as $k)
                    <div class="rounded-2xl bg-slate-50 p-4 shadow-[0_4px_12px_rgba(0,0,0,0.08)]">
                        <div class="flex items-start justify-between">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center {{ $k['tone'] }}">
                                <span class="material-symbols-outlined text-[20px]">{{ $k['icon'] }}</span>
                            </span>
                            <span
                                class="text-[10px] font-semibold {{ $k['up'] === true ? 'text-success' : ($k['up'] === false ? 'text-error' : 'text-warning') }}">
                                {{ $k['trend'] }}
                            </span>
                        </div>
                        <p class="text-[11px] text-on-surface-variant mt-4 truncate">{{ $k['label'] }}</p>
                        <p class="text-xl font-extrabold text-on-surface mt-0.5">{{ $k['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===== Grafik + Stok ===== --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div
                class="lg:col-span-2 rounded-3xl p-5 sm:p-6 bg-gradient-to-b from-[#eaf5fe] to-[#dbeefb] shadow-[0_8px_24px_rgba(10,92,168,0.12)] ring-1 ring-white/60">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-on-surface">Tren Pengajuan</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5">Total ajuan seluruh layanan per bulan</p>
                    </div>
                    <span class="text-[11px] font-semibold text-on-surface flex items-center gap-0.5 shrink-0">
                        6 bulan terakhir
                        <span class="material-symbols-outlined text-[14px]">expand_more</span>
                    </span>
                </div>
                <div class="mt-4 h-64">
                    <canvas id="chartPengajuan"></canvas>
                </div>
            </div>

            <div class="{{ $card }} p-5 sm:p-6 flex flex-col">
                <p class="text-sm font-bold text-on-surface">Stok Bantuan</p>
                <p class="text-[11px] text-on-surface-variant mt-0.5">Pemakaian stok Rehabsos &amp; Perlinsos</p>

                <div class="flex-1 flex items-center justify-center py-5">
                    <div class="relative w-40 h-40 rounded-full"
                        style="background: conic-gradient(#0a5ca8 {{ $stokPct }}%, #dbeafb 0);">
                        <div
                            class="absolute inset-4 rounded-full bg-white flex flex-col items-center justify-center shadow-inner">
                            <span class="text-3xl font-extrabold text-brand">{{ $stokPct }}%</span>
                            <span class="text-[10px] tracking-wide text-on-surface-variant">TERPAKAI</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-[#eaf2fe] p-3">
                        <p class="text-[9px] tracking-wide text-on-surface-variant">TERPAKAI</p>
                        <p class="text-sm font-bold text-on-surface">{{ number_format($stokTerpakai) }} Unit</p>
                    </div>
                    <div class="rounded-xl bg-[#eaf2fe] p-3">
                        <p class="text-[9px] tracking-wide text-on-surface-variant">TOTAL STOK</p>
                        <p class="text-sm font-bold text-on-surface">{{ number_format($stokTotal) }} Unit</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== Aksi cepat ===== --}}
        <section>
            <h2 class="text-lg font-bold text-on-surface mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                @foreach ($quick as $q)
                    @continue(!$role || !$role->hasModule($q['key']))
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

        {{-- ===== Ajuan terbaru + Aktivitas ===== --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 {{ $card }} overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-on-surface">Ajuan Terbaru</p>
                    <span class="text-[11px] text-on-surface-variant">Data contoh</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#e6edf8] text-on-surface-variant uppercase text-[10px] tracking-wide">
                            <tr>
                                <th class="px-5 py-3 font-semibold">ID Ajuan</th>
                                <th class="px-3 py-3 font-semibold">Pemohon</th>
                                <th class="px-3 py-3 font-semibold">Layanan</th>
                                <th class="px-3 py-3 font-semibold">Tanggal</th>
                                <th class="px-3 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @foreach ($recent as $r)
                                <tr class="hover:bg-surface-container-low/60">
                                    <td class="px-5 py-3 font-bold text-brand whitespace-nowrap">{{ $r['id'] }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $r['name'] }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $r['layanan'] }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">{{ $r['date'] }}</td>
                                    <td class="px-3 py-3">
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $statusTone[$r['status']] }}">
                                            {{ $r['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="{{ $card }} p-5">
                <p class="text-sm font-bold text-on-surface mb-4">Aktivitas Terbaru</p>
                <ol class="relative border-l border-outline-variant/50 ml-4 space-y-5">
                    @foreach ($activities as $a)
                        <li class="pl-6 relative">
                            <span
                                class="absolute -left-4 top-0 w-8 h-8 rounded-full bg-brand text-white flex items-center justify-center ring-4 ring-white">
                                <span class="material-symbols-outlined text-[16px]">{{ $a['icon'] }}</span>
                            </span>
                            <p class="text-xs font-semibold text-on-surface">{{ $a['title'] }}</p>
                            <p class="text-[11px] text-on-surface-variant">{{ $a['desc'] }}</p>
                            <p class="text-[10px] text-outline mt-0.5">{{ $a['time'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Total ajuan per bulan = SPMB + PBI APBD + Kedaruratan Medis
            const labels = ['Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep'];
            const data = [22, 32, 25, 40, 43, 55];

            new Chart(document.getElementById('chartPengajuan'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Ajuan',
                        data: data,
                        borderRadius: 10,
                        borderSkipped: false,
                        maxBarThickness: 56,
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const area = chart.chartArea;
                            if (!area) return '#0a5ca8';
                            const g = chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
                            g.addColorStop(0, '#0a5ca8');
                            g.addColorStop(1, '#d6e9f8');
                            return g;
                        },
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            border: {
                                display: false
                            },
                            grid: {
                                color: 'rgba(10,92,168,0.08)'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                },
            });
        });
    </script>
@endpush
