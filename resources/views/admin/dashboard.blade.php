@extends('layouts.admin')

@section('title', 'Beranda - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Beranda')

@section('content')
    <div class="space-y-6">

        <div>
            <h2 class="text-lg font-semibold text-on-surface">
                Selamat datang, {{ auth()->user()->name ?? 'Admin' }} 👋
            </h2>
            <p class="text-sm text-on-surface-variant">Ringkasan data (dummy) untuk ditampilkan di dashboard.</p>
        </div>

        {{-- Ringkasan angka per layanan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <p class="text-xs text-on-surface-variant">Asesmen SPMB</p>
                <p class="text-2xl font-bold text-primary mt-1">128</p>
                <p class="text-[11px] text-on-surface-variant mt-1">32 menunggu verifikasi</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <p class="text-xs text-on-surface-variant">Reaktivasi PBI APBD</p>
                <p class="text-2xl font-bold text-primary mt-1">76</p>
                <p class="text-[11px] text-on-surface-variant mt-1">14 menunggu verifikasi</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <p class="text-xs text-on-surface-variant">Kedaruratan Medis</p>
                <p class="text-2xl font-bold text-primary mt-1">19</p>
                <p class="text-[11px] text-on-surface-variant mt-1">5 menunggu verifikasi</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <p class="text-xs text-on-surface-variant">Kartu KKS</p>
                <p class="text-2xl font-bold text-primary mt-1">54</p>
                <p class="text-[11px] text-on-surface-variant mt-1">9 menunggu verifikasi</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <p class="text-xs text-on-surface-variant">Total Data</p>
                <p class="text-2xl font-bold text-primary mt-1">277</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <p class="text-xs text-on-surface-variant">Menunggu Verifikasi</p>
                <p class="text-2xl font-bold text-primary mt-1">60</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <p class="text-xs text-on-surface-variant">Selesai</p>
                <p class="text-2xl font-bold text-primary mt-1">217</p>
            </div>
        </div>

        {{-- Grafik --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <p class="text-sm font-semibold text-on-surface mb-3">Tren Pengajuan per Bulan</p>
                <canvas id="chartPengajuan" height="220"></canvas>
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <p class="text-sm font-semibold text-on-surface mb-3">Stok Bantuan (Rehabsos &amp; Perlinsos)</p>
                <canvas id="chartStok" height="220"></canvas>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Chart(document.getElementById('chartPengajuan'), {
                type: 'line',
                data: {
                    labels: ['Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep'],
                    datasets: [{
                            label: 'Asesmen SPMB',
                            data: [12, 19, 15, 22, 28, 32],
                            borderColor: '#003b62',
                            backgroundColor: 'rgba(0,59,98,0.1)',
                            tension: 0.3,
                        },
                        {
                            label: 'Reaktivasi PBI APBD',
                            data: [8, 10, 9, 14, 12, 18],
                            borderColor: '#136299',
                            backgroundColor: 'rgba(19,98,153,0.1)',
                            tension: 0.3,
                        },
                        {
                            label: 'Kedaruratan Medis',
                            data: [2, 3, 1, 4, 3, 5],
                            borderColor: '#ba1a1a',
                            backgroundColor: 'rgba(186,26,26,0.1)',
                            tension: 0.3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                },
            });

            new Chart(document.getElementById('chartStok'), {
                type: 'bar',
                data: {
                    labels: ['Kursi Roda', 'Tongkat', 'Alat Bantu Dengar', 'Kruk', 'Selimut', 'Beras'],
                    datasets: [{
                            label: 'Stok Tersedia',
                            data: [24, 40, 15, 30, 120, 300],
                            backgroundColor: '#24527a',
                            borderRadius: 6,
                        },
                        {
                            label: 'Terpakai Bulan Ini',
                            data: [6, 12, 4, 9, 45, 110],
                            backgroundColor: '#82c1fd',
                            borderRadius: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                },
            });
        });
    </script>
@endpush
