@extends('layouts.admin')

@section('title', 'Ajuan Kartu KKS - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Kartu KKS — Ajuan')

@section('content')
    <div class="space-y-6">

        @if (session('success'))
            <div id="alert-success" class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-sm text-sm bg-green-50 border border-green-200 text-green-700 transition-all">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-sm text-sm bg-red-50 border border-red-200 text-red-700 transition-all">
                <span class="material-symbols-outlined text-[20px]">error</span>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filter Section -->
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] p-5">
            <form method="GET" action="{{ route('kartu-kks.ajuan') }}" data-auto-filter>
                <!-- Grid layout untuk input filter -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Tanggal Awal</label>
                        <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}"
                            class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                            class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Tahap Saat Ini</label>
                        <select name="status" class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors">
                            <option value="">Semua Tahap</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Cari Data</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, Alamat, Tanggal (dd-mm-yyyy)..."
                                class="w-full pl-8 pr-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors" />
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-outline-variant/80">search</span>
                        </div>
                    </div>
                </div>

                <!-- Bagian Reset Filter & Tampilkan Data -->
                <div class="mt-5 pt-4 border-t border-outline-variant/20 flex flex-wrap items-center justify-between gap-3">

                    <!-- Tombol Reset Filter -->
                    <a href="{{ route('kartu-kks.ajuan') }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-error hover:bg-error/10 border border-transparent hover:border-error/20 transition-all">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        Reset Filter
                    </a>

                    <!-- Pengaturan Tampilkan X Data -->
                    <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                        Tampilkan
                        <select name="tampilkan" onchange="this.form.submit()" form="ajuan-kks-tampilkan-form"
                            class="rounded-xl border border-outline-variant/40 bg-slate-50 px-2 py-1.5 text-xs focus:outline-none">
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
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs table-fixed min-w-[900px]">
                    <colgroup>
                        <col style="width: 5%">
                        <col style="width: 11%">
                        <col style="width: 15%">
                        <col style="width: 17%">
                        <col style="width: 22%">
                        <col style="width: 18%">
                        <col style="width: 12%">
                    </colgroup>
                    <thead class="bg-[#e6edf8] text-on-surface-variant uppercase text-[10px] tracking-wider font-semibold">
                        <tr>
                            <th class="text-center px-3 py-3 whitespace-nowrap">No</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Tgl Insert</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">NIK</th>
                            <th class="text-left px-3 py-3 whitespace-nowrap">Nama</th>
                            <th class="text-left px-3 py-3 whitespace-nowrap">Alamat</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Tahap Saat Ini</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @forelse ($ajuanList as $item)
                            <tr class="hover:bg-surface-container-low transition-colors group">
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-center text-on-surface-variant">{{ $ajuanList->firstItem() + $loop->index }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap text-center text-on-surface-variant">{{ $item->tanggal_insert ? \Carbon\Carbon::parse($item->tanggal_insert)->format('d-m-Y') : '-' }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate font-semibold text-brand text-center" title="{{ $item->nik }}">{{ $item->nik }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate font-medium" title="{{ $item->nama_pemohon }}">{{ $item->nama_pemohon }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-on-surface-variant" title="{{ $item->alamat }}">{{ $item->alamat }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap text-center" title="{{ $statusLabels[$item->status] ?? $item->status }}">
                                    <span class="inline-flex items-center gap-1.5 max-w-full px-2.5 py-1 rounded-full text-[11px] font-medium bg-secondary-fixed text-on-secondary-container border border-secondary-fixed/50">
                                        <span class="material-symbols-outlined text-[14px]">schedule</span>
                                        <span class="truncate">{{ $statusLabels[$item->status] ?? $item->status }}</span>
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 whitespace-nowrap text-center">
                                    <a href="{{ route('kartu-kks.show', $item) }}"
                                        class="inline-flex items-center justify-center gap-1.5 w-full max-w-[80px] px-3 py-1.5 rounded-xl bg-brand/10 text-brand hover:bg-brand hover:text-white text-[11px] font-semibold transition-all duration-200">
                                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-on-surface-variant">
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

            <!-- Pagination Controls -->
            @if ($ajuanList->hasPages())
                <div class="px-5 py-4 border-t border-outline-variant/20 bg-white">
                    {{ $ajuanList->links() }}
                </div>
            @endif
        </div>

        {{-- Form tersembunyi untuk fitur Tampilkan data --}}
        <form id="ajuan-kks-tampilkan-form" method="GET" action="{{ route('kartu-kks.ajuan') }}" class="hidden">
            <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
            <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
        </form>
    </div>

    @push('scripts')
        <script>
            // Notifikasi sukses hilang otomatis
            (function () {
                const alertBox = document.getElementById('alert-success');
                if (alertBox) {
                    setTimeout(function () {
                        alertBox.style.transition = 'opacity .5s';
                        alertBox.style.opacity = '0';
                        setTimeout(function () { alertBox.remove(); }, 500);
                    }, 3000);
                }
            })();

            // Filter & pencarian otomatis tanpa tombol "Terapkan"
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

                    // Dropdown: langsung submit saat berubah
                    // (lewati dropdown "Tampilkan" yang punya form sendiri)
                    form.querySelectorAll('select').forEach(function (el) {
                        if (el.form !== form) return;
                        el.addEventListener('change', submitForm);
                    });

                    // Tanggal: submit hanya jika kosong atau sudah lengkap & valid
                    form.querySelectorAll('input[type="date"]').forEach(function (el) {
                        el.addEventListener('change', function () {
                            const v = el.value;
                            if (v === '' || (v.length === 10 && parseInt(v.slice(0, 4), 10) >= 1900)) {
                                submitForm();
                            }
                        });
                    });

                    // Pencarian: tunggu 500ms setelah berhenti mengetik
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

                        // Kembalikan fokus & kursor ke kolom cari setelah halaman dimuat ulang
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
