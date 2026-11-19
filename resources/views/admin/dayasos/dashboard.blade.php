@extends('layouts.admin')

@section('title', 'Beranda Dayasos - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Dayasos — Beranda')

@php
    $role = auth()->user()->role ?? null;
    $card = 'bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)]';
    $kpiBox = 'rounded-2xl bg-slate-50 p-4 shadow-[0_4px_12px_rgba(0,0,0,0.08)]';

    // KPI atas
    $kpis = [];
    if ($ngopi) {
        $kpis[] = ['label' => 'Konsultasi Ngopi Pagi', 'value' => $ngopi['total'], 'sub' => $ngopi['bulan_ini'] . ' bulan ini', 'icon' => 'forum', 'tone' => 'text-primary bg-primary-fixed-dim/40'];
        $kpis[] = ['label' => 'Kasus Berjalan', 'value' => $ngopi['aktif'], 'sub' => 'belum selesai', 'icon' => 'pending_actions', 'tone' => 'text-secondary bg-secondary-fixed'];
        $kpis[] = ['label' => 'Butuh Segera', 'value' => $ngopi['segera'], 'sub' => 'kasus aktif', 'icon' => 'priority_high', 'tone' => 'text-error bg-error-container'];
        $kpis[] = ['label' => 'Kasus Selesai', 'value' => $ngopi['selesai'], 'sub' => $ngopi['progres'] . '% dari total', 'icon' => 'task_alt', 'tone' => 'text-white bg-brand'];
    }
    if ($kesan) {
        $kpis[] = ['label' => 'KESAN Menunggu Verifikasi', 'value' => $kesan['dipilih'], 'sub' => $kesan['total'] . ' total pilihan', 'icon' => 'volunteer_activism', 'tone' => 'text-warning bg-warning-container'];
        $kpis[] = ['label' => 'KESAN Penyaluran', 'value' => $kesan['penyaluran'], 'sub' => 'dalam proses', 'icon' => 'local_shipping', 'tone' => 'text-secondary bg-secondary-fixed'];
        $kpis[] = ['label' => 'KESAN Selesai', 'value' => $kesan['selesai'], 'sub' => ($kesan['ditolak'] + $kesan['batal']) . ' ditolak/batal', 'icon' => 'check_circle', 'tone' => 'text-success bg-success-container'];
        $kpis[] = ['label' => 'Calon Penerima', 'value' => $kesan['tersedia'], 'sub' => $kesan['filantropi'] . ' akun filantropi', 'icon' => 'diversity_1', 'tone' => 'text-on-surface-variant bg-surface-container'];
    }

    // Aksi cepat (hanya yang boleh diakses role)
    // $quick = [
    //     ['key' => 'dayasos-ngopi-pagi.chat_masuk', 'label' => 'Chat Masuk', 'icon' => 'mark_chat_unread', 'route' => 'ngopi-pagi.admin.tahap.chat_masuk', 'tone' => 'text-primary bg-primary-fixed-dim/40'],
    //     ['key' => 'dayasos-ngopi-pagi.screening', 'label' => 'Screening', 'icon' => 'filter_alt', 'route' => 'ngopi-pagi.admin.tahap.screening', 'tone' => 'text-warning bg-warning-container'],
    //     ['key' => 'dayasos-ngopi-pagi.rekap', 'label' => 'Rekapitulasi', 'icon' => 'table_chart', 'route' => 'ngopi-pagi.admin.rekap', 'tone' => 'text-success bg-success-container'],
    //     ['key' => 'dayasos-ngopi-pagi.konselor', 'label' => 'Data Konselor', 'icon' => 'psychology', 'route' => 'ngopi-pagi.admin.konselor.index', 'tone' => 'text-secondary bg-secondary-fixed'],
    //     ['key' => 'kesan.pilihan', 'label' => 'Verifikasi Pilihan', 'icon' => 'volunteer_activism', 'route' => 'kesan-admin.pilihan.index', 'tone' => 'text-error bg-error-container'],
    //     ['key' => 'kesan.penerima', 'label' => 'Calon Penerima', 'icon' => 'diversity_1', 'route' => 'kesan-admin.penerima.index', 'tone' => 'text-on-surface-variant bg-surface-container'],
    // ];

    $tahapTone = [
        'chat_masuk' => 'bg-warning-container text-on-warning-container',
        'selesai' => 'bg-success-container text-on-success-container',
    ];
    $kategoriTone = [
        'A' => 'bg-success-container text-on-success-container',
        'B' => 'bg-warning-container text-on-warning-container',
        'C' => 'bg-error-container text-on-error-container',
    ];

    $kesanLabel = [
        'dipilih' => 'Menunggu verifikasi', 'penyaluran' => 'Penyaluran', 'selesai' => 'Selesai',
        'ditolak' => 'Ditolak', 'batal' => 'Batal',
    ];
    $kesanTone = [
        'dipilih' => 'bg-warning-container text-on-warning-container',
        'penyaluran' => 'bg-secondary-fixed text-on-secondary-container',
        'selesai' => 'bg-success-container text-on-success-container',
        'ditolak' => 'bg-error-container text-on-error-container',
        'batal' => 'bg-surface-container text-on-surface-variant',
    ];

    $pct = $ngopi['progres'] ?? 0;
@endphp

@section('content')
    <div class="space-y-6">

        @include('partials.sapaan', ['konteks' => 'Bidang Dayasos'])

        @if (!$ngopi && !$kesan)
            <section class="{{ $card }} p-10 text-center text-sm text-on-surface-variant">
                Belum ada modul Dayasos yang dapat diakses oleh akun ini.
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

        {{-- ===== Grafik + Progres kasus ===== --}}
        @if (count($grafik['datasets']))
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div
                    class="{{ $ngopi ? 'lg:col-span-2' : 'lg:col-span-3' }} rounded-3xl p-5 sm:p-6 bg-gradient-to-b from-[#eaf5fe] to-[#dbeefb] shadow-[0_8px_24px_rgba(10,92,168,0.12)] ring-1 ring-white/60">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-on-surface">Tren Layanan Dayasos</p>
                            <p class="text-[11px] text-on-surface-variant mt-0.5">Konsultasi Ngopi Pagi &amp; pilihan donatur KESAN per bulan</p>
                        </div>
                        <span class="text-[11px] font-semibold text-on-surface shrink-0">6 bulan terakhir</span>
                    </div>
                    <div class="mt-4 h-64">
                        <canvas id="chartDayasos"></canvas>
                    </div>
                </div>

                @if ($ngopi)
                    <div class="{{ $card }} p-5 sm:p-6 flex flex-col">
                        <p class="text-sm font-bold text-on-surface">Penyelesaian Kasus Ngopi Pagi</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5">{{ number_format($ngopi['total']) }} total konsultasi</p>

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
                            @foreach ($ngopi['kategori'] as $kat => $n)
                                <div class="rounded-xl bg-[#eaf2fe] p-3">
                                    <p class="text-[9px] tracking-wide text-on-surface-variant">KATEGORI {{ $kat }}</p>
                                    <p class="text-sm font-bold text-on-surface">{{ number_format($n) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        @endif

        {{-- ===== Aksi cepat ===== --}}
        {{-- <section>
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
        </section> --}}

        {{-- ===== Rincian status ===== --}}
        @if ($ngopi || $kesan)
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Alur Ngopi Pagi --}}
                @if ($ngopi)
                    <div class="{{ $card }} p-5">
                        <p class="text-sm font-bold text-on-surface">Alur Ngopi Pagi</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5 mb-4">Posisi kasus pada tiap tahap</p>

                        <div class="space-y-3">
                            @foreach ($ngopi['tahap'] as $t)
                                @php
                                    $n = (int) ($ngopi['perTahap'][$t] ?? 0);
                                    $p = $ngopi['aktif'] > 0 ? round($n / $ngopi['aktif'] * 100) : 0;
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-[11px] mb-1">
                                        <span class="text-on-surface-variant truncate">{{ $tahapCfg[$t]['label'] ?? $t }}</span>
                                        <span class="font-bold text-on-surface">{{ $n }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-[#dbeafb] overflow-hidden">
                                        <div class="h-full rounded-full bg-brand" style="width: {{ $p }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Jenis tindak lanjut --}}
                    <div class="{{ $card }} p-5">
                        <p class="text-sm font-bold text-on-surface">Tindak Lanjut Ngopi Pagi</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5 mb-4">Jenis layanan yang diberikan</p>

                        @forelse ($ngopi['tindak']->take(6) as $label => $n)
                            <div class="flex items-center justify-between py-2 border-b border-outline-variant/30 last:border-0">
                                <span class="text-xs text-on-surface">{{ $label }}</span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-secondary-fixed text-on-secondary-container">{{ number_format($n) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant">Belum ada data.</p>
                        @endforelse
                    </div>
                @endif

                {{-- Status KESAN --}}
                @if ($kesan)
                    <div class="{{ $card }} p-5">
                        <p class="text-sm font-bold text-on-surface">Status KESAN</p>
                        <p class="text-[11px] text-on-surface-variant mt-0.5 mb-4">{{ number_format($kesan['total']) }} total pilihan donatur</p>

                        @foreach ($kesanLabel as $st => $lbl)
                            <div class="flex items-center justify-between py-2 border-b border-outline-variant/30 last:border-0">
                                <span class="text-xs text-on-surface">{{ $lbl }}</span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $kesanTone[$st] }}">{{ number_format($kesan[$st]) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        {{-- ===== Kasus Ngopi Pagi terbaru ===== --}}
        @if ($ngopi)
            <section class="{{ $card }} overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-on-surface">Konsultasi Ngopi Pagi Terbaru</p>
                    @if (\Illuminate\Support\Facades\Route::has('ngopi-pagi.admin.rekap') && $role->hasModule('dayasos-ngopi-pagi.rekap'))
                        <a href="{{ route('ngopi-pagi.admin.rekap') }}" class="text-[11px] font-semibold text-brand hover:underline">Lihat semua</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#e6edf8] text-on-surface-variant uppercase text-[10px] tracking-wide">
                            <tr>
                                <th class="px-5 py-3 font-semibold">No. Konsultasi</th>
                                <th class="px-3 py-3 font-semibold">Nama</th>
                                <th class="px-3 py-3 font-semibold">Kategori</th>
                                <th class="px-3 py-3 font-semibold">Konselor</th>
                                <th class="px-3 py-3 font-semibold">Tanggal</th>
                                <th class="px-3 py-3 font-semibold">Tahap</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @forelse ($ngopiTerbaru as $r)
                                <tr class="hover:bg-surface-container-low/60">
                                    <td class="px-5 py-3 font-bold text-brand whitespace-nowrap">
                                        <a href="{{ $r->urlAdmin() }}" class="hover:underline">{{ $r->no_konsultasi }}</a>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $r->nama }}</td>
                                    <td class="px-3 py-3">
                                        @if ($r->kategori)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $kategoriTone[$r->kategori] ?? 'bg-surface-container text-on-surface-variant' }}">{{ $r->kategori }}</span>
                                        @else
                                            <span class="text-on-surface-variant">–</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">{{ $r->konselor->name ?? '–' }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">{{ $r->created_at->format('d-m-Y') }}</td>
                                    <td class="px-3 py-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold whitespace-nowrap {{ $tahapTone[$r->tahap] ?? 'bg-secondary-fixed text-on-secondary-container' }}">
                                            {{ $tahapCfg[$r->tahap]['label'] ?? $r->tahap }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-on-surface-variant">Belum ada konsultasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- ===== Pilihan KESAN terbaru ===== --}}
        @if ($kesan)
            <section class="{{ $card }} overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-on-surface">Pilihan Donatur KESAN Terbaru</p>
                    @if (\Illuminate\Support\Facades\Route::has('kesan-admin.pilihan.index') && $role->hasModule('kesan.pilihan'))
                        <a href="{{ route('kesan-admin.pilihan.index') }}" class="text-[11px] font-semibold text-brand hover:underline">Lihat semua</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#e6edf8] text-on-surface-variant uppercase text-[10px] tracking-wide">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Kode</th>
                                <th class="px-3 py-3 font-semibold">Donatur</th>
                                <th class="px-3 py-3 font-semibold">Calon Penerima</th>
                                <th class="px-3 py-3 font-semibold">Kategori</th>
                                <th class="px-3 py-3 font-semibold">Tanggal</th>
                                <th class="px-3 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @forelse ($kesanTerbaru as $r)
                                <tr class="hover:bg-surface-container-low/60">
                                    <td class="px-5 py-3 font-bold text-brand whitespace-nowrap">
                                        @if (\Illuminate\Support\Facades\Route::has('kesan-admin.pilihan.show') && $role->hasModule('kesan.pilihan'))
                                            <a href="{{ route('kesan-admin.pilihan.show', $r) }}" class="hover:underline">{{ $r->kode }}</a>
                                        @else
                                            {{ $r->kode }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $r->user->name ?? '–' }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $r->penerima->nama ?? '–' }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">{{ $r->penerima->kategori->label() ?? '–' }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-on-surface-variant">{{ optional($r->created_at)->format('d-m-Y') }}</td>
                                    <td class="px-3 py-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold whitespace-nowrap {{ $kesanTone[$r->status] ?? 'bg-surface-container text-on-surface-variant' }}">
                                            {{ $kesanLabel[$r->status] ?? $r->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-on-surface-variant">Belum ada pilihan donatur.</td>
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
            const el = document.getElementById('chartDayasos');
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