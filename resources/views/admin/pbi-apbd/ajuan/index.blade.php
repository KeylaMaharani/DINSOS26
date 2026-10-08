@extends('layouts.admin')

@section('title', 'Ajuan PBI APBD')
@section('page_title', 'PBI APBD — Ajuan')

@section('content')
    <div class="space-y-6">

        @if (session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-sm text-sm bg-green-50 border border-green-200 text-green-700 transition-all">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter Section -->
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 shadow-sm">
            <form method="GET" action="{{ route('pbi-apbd.ajuan.index') }}" data-auto-filter>
                <!-- Grid layout untuk input filter -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Tanggal Awal</label>
                        <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}"
                            class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-xs focus:ring-1 focus:outline-none transition-colors" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                            class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-xs focus:ring-1 focus:outline-none transition-colors" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Tahap Saat Ini</label>
                        <select name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-xs focus:ring-1 focus:outline-none transition-colors">
                            <option value="">Semua Tahap</option>
                            @foreach (\App\Models\PbiApbd::STAGES as $key => $stage)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $stage['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Cari Data</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, No. Reg..."
                                class="w-full pl-8 pr-3 py-2 rounded-lg border border-outline-variant/50 text-xs focus:ring-1 focus:outline-none transition-colors" />
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-outline-variant/80">search</span>
                        </div>
                    </div>
                </div>

                <!-- Bagian Reset Filter & Tampilkan Data -->
                <div class="mt-5 pt-4 border-t border-outline-variant/20 flex flex-wrap items-center justify-between gap-3">

                    <!-- Tombol Reset Filter -->
                    <a href="{{ route('pbi-apbd.ajuan.index') }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-error hover:bg-error/10 border border-transparent hover:border-error/20 transition-all">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        Reset Filter
                    </a>

                    <!-- Pengaturan Tampilkan X Data -->
                    <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                        Tampilkan
                        <select name="tampilkan" onchange="this.form.submit()" form="ajuan-pbi-tampilkan-form"
                            class="rounded-lg border border-outline-variant/50 px-2 py-1.5 text-xs bg-surface-container-lowest focus:outline-none">
                            <!-- Default otomatis ke 10 jika tidak ada request -->
                            @foreach ([10, 25, 50, 100] as $n)
                                <option value="{{ $n }}" @selected((int) request('tampilkan', 10) === $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                        data per halaman
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-xs table-fixed min-w-[900px]">
                    <colgroup>
                        <col style="width: 5%">
                        <col style="width: 10%">
                        <col style="width: 14%">
                        <col style="width: 16%">
                        <col style="width: 8%">
                        <col style="width: 18%">
                        <col style="width: 17%">
                        <col style="width: 12%">
                    </colgroup>
                    <thead class="bg-surface-container text-on-surface-variant uppercase text-[10px] tracking-wider font-semibold">
                        <tr>
                            <th class="text-center px-3 py-3 whitespace-nowrap">No</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Tgl Insert</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">No. Registrasi</th>
                            <th class="text-left px-3 py-3 whitespace-nowrap">Kepala Keluarga</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Desil</th>
                            <th class="text-left px-3 py-3 whitespace-nowrap">Alamat</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Tahap Saat Ini</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @forelse ($ajuan as $item)
                            <tr class="hover:bg-surface-container-low transition-colors group">
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-center text-on-surface-variant">{{ $ajuan->firstItem() + $loop->index }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap text-center text-on-surface-variant">{{ $item->created_at->format('d-m-Y') }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate font-semibold text-primary text-center" title="{{ $item->no_registrasi }}">{{ $item->no_registrasi }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate font-medium" title="{{ $item->nama_kepala_keluarga }}">{{ $item->nama_kepala_keluarga }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-center">
                                    @if($item->desil_nasional)
                                        <span class="px-2 py-1 rounded bg-outline-variant/10 text-on-surface-variant font-medium">{{ $item->desil_nasional }}</span>
                                    @else
                                        <span class="text-outline-variant/60">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-on-surface-variant" title="{{ $item->kecamatan }}, {{ $item->desa_kelurahan }}">{{ $item->kecamatan }}, {{ $item->desa_kelurahan }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap text-center" title="{{ $item->statusLabel() }}">
                                    <span class="inline-flex items-center gap-1.5 max-w-full px-2.5 py-1 rounded-full text-[11px] font-medium bg-secondary-fixed text-on-secondary-container border border-secondary-fixed/50">
                                        <span class="material-symbols-outlined text-[14px]">schedule</span>
                                        <span class="truncate">{{ $item->statusLabel() }}</span>
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 whitespace-nowrap text-center">
                                    <a href="{{ route('pbi-apbd.ajuan.show', $item) }}"
                                        class="inline-flex items-center justify-center gap-1.5 w-full max-w-[80px] px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-semibold transition-all duration-200">
                                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-on-surface-variant">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined text-[40px] mb-3 opacity-30">inbox</span>
                                        <p class="text-sm font-medium">Belum ada ajuan data</p>
                                        <p class="text-xs opacity-70 mt-1">Silakan sesuaikan filter pencarian Anda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls (Per 10 Data) -->
            @if($ajuan->hasPages())
                <div class="px-5 py-4 border-t border-outline-variant/20 bg-surface-container-lowest">
                    {{ $ajuan->links() }}
                </div>
            @endif
        </div>

        <form id="ajuan-pbi-tampilkan-form" method="GET" action="{{ route('pbi-apbd.ajuan.index') }}" class="hidden">
            <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
            <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
        </form>
    </div>

    @push('scripts')
        <script>
            (function () {
                const FOCUS_KEY = 'focus-search:' + location.pathname;

                document.querySelectorAll('form[data-auto-filter]').forEach(function (form) {
                    const searchInput = form.querySelector('input[name="search"]');
                    let timer = null;

                    function submitForm() {
                        if (searchInput && document.activeElement === searchInput) {
                            sessionStorage.setItem(FOCUS_KEY, '1');
                        }
                        form.submit();
                    }

                    form.querySelectorAll('select').forEach(function (el) {
                        if (el.form !== form) return;
                        el.addEventListener('change', submitForm);
                    });

                    form.querySelectorAll('input[type="date"]').forEach(function (el) {
                        el.addEventListener('change', function () {
                            const v = el.value;
                            if (v === '' || (v.length === 10 && parseInt(v.slice(0, 4), 10) >= 1900)) {
                                submitForm();
                            }
                        });
                    });

                    if (searchInput) {
                        searchInput.addEventListener('input', function () {
                            clearTimeout(timer);
                            timer = setTimeout(submitForm, 500);
                        });
                        searchInput.addEventListener('keydown', function (e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                clearTimeout(timer);
                                submitForm();
                            }
                        });

                        if (sessionStorage.getItem(FOCUS_KEY)) {
                            sessionStorage.removeItem(FOCUS_KEY);
                            searchInput.focus();
                            const len = searchInput.value.length;
                            searchInput.setSelectionRange(len, len);
                        }
                    }
                });
            })();
        </script>
    @endpush
@endsection
