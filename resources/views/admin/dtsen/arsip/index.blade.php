@extends('layouts.admin')

@section('title', 'Arsip DTSEN - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'DTSEN - Arsip')

@section('content')
    <div class="space-y-6" x-data="arsipDtsenManager()">

        @if (session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-sm text-sm bg-green-50 border border-green-200 text-green-700 transition-all">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter Section -->
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] p-5">
            <form method="GET" action="{{ route('dtsen.arsip') }}" data-auto-filter>
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
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Status</label>
                        <select name="status" class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors">
                            <option value="">Semua Status</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Cari Data</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, Alamat..."
                                class="w-full pl-8 pr-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors" />
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-outline-variant/80">search</span>
                        </div>
                    </div>
                </div>

                <!-- Bagian Reset Filter & Tampilkan Data -->
                <div class="mt-5 pt-4 border-t border-outline-variant/20 flex flex-wrap items-center justify-between gap-3">

                    <!-- Tombol Reset Filter -->
                    <a href="{{ route('dtsen.arsip') }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-error hover:bg-error/10 border border-transparent hover:border-error/20 transition-all">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        Reset Filter
                    </a>

                    <!-- Pengaturan Tampilkan X Data -->
                    <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                        Tampilkan
                        <select name="tampilkan" onchange="this.form.submit()" form="arsip-dtsen-tampilkan-form"
                            class="rounded-xl border border-outline-variant/40 bg-slate-50 px-2 py-1.5 text-xs focus:outline-none">
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
                <table class="w-full text-xs table-fixed min-w-[1000px]">
                    <colgroup>
                        <col style="width: 5%">
                        <col style="width: 10%">
                        <col style="width: 13%">
                        <col style="width: 13%">
                        <col style="width: 7%">
                        <col style="width: 14%">
                        <col style="width: 13%">
                        <col style="width: 13%">
                        <col style="width: 12%">
                    </colgroup>
                    <thead class="bg-[#e6edf8] text-on-surface-variant uppercase text-[10px] tracking-wider font-semibold">
                        <tr>
                            <th class="text-center px-3 py-3 whitespace-nowrap">No</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Tgl Insert</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">NIK</th>
                            <th class="text-left px-3 py-3 whitespace-nowrap">Nama</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Desil</th>
                            <th class="text-left px-3 py-3 whitespace-nowrap">Alamat</th>
                            <th class="text-left px-3 py-3 whitespace-nowrap">Alasan Cetak</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Status</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @forelse ($arsipList as $item)
                            @php
                                $isSelesai = $item->status === 'selesai';
                                $desil = $item->bansos->peringkat_kesejahteraan_keluarga ?? null;
                            @endphp
                            <tr class="hover:bg-surface-container-low transition-colors group">
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-center text-on-surface-variant">{{ $arsipList->firstItem() + $loop->index }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap text-center text-on-surface-variant">{{ $item->tanggal_insert->format('d-m-Y') }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate font-semibold text-brand text-center" title="{{ $item->nik }}">{{ $item->nik }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate font-medium" title="{{ $item->nama_pemohon }}">{{ $item->nama_pemohon }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-center">
                                    @if ($desil)
                                        <span class="px-2.5 py-1 rounded-full bg-brand/10 text-brand font-semibold">{{ $desil }}</span>
                                    @else
                                        <span class="text-outline-variant/60">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-on-surface-variant" title="{{ $item->alamat }}">{{ $item->alamat }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate" title="{{ $item->alasan_cetak }}">{{ $item->alasan_cetak ?? '-' }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium border
                                        {{ $isSelesai ? 'bg-success-container text-on-success-container border-success-container/50' : 'bg-red-50 text-red-700 border-red-200' }}">
                                        <span class="material-symbols-outlined text-[14px]">
                                            {{ $isSelesai ? 'check_circle' : 'cancel' }}
                                        </span>
                                        {{ $statusLabels[$item->status] ?? $item->status }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 whitespace-nowrap text-center">
                                    <button @click="openDetail({{ $item->id }})"
                                        class="inline-flex items-center justify-center gap-1.5 w-full max-w-[80px] px-3 py-1.5 rounded-xl bg-brand/10 text-brand hover:bg-brand hover:text-white text-[11px] font-semibold transition-all duration-200">
                                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                                        Lihat
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-on-surface-variant">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined text-[40px] mb-3 opacity-30">inbox</span>
                                        <p class="text-sm font-medium">Belum ada arsip data</p>
                                        <p class="text-xs opacity-70 mt-1">Silakan sesuaikan filter pencarian Anda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($arsipList->hasPages())
                <div class="px-5 py-4 border-t border-outline-variant/20 bg-white">
                    {{ $arsipList->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Detail Arsip --}}
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-opacity">
            <div @click.outside="detailOpen = false"
                class="bg-white w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col rounded-3xl shadow-2xl transition-transform"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/30 bg-white shrink-0">
                    <h3 class="text-base font-semibold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand text-[20px]">assignment</span>
                        Detail Arsip DTSEN
                    </h3>
                    <button @click="detailOpen = false" class="text-on-surface-variant hover:bg-outline-variant/10 p-1.5 rounded-full transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto" x-show="!loading">
                    <div class="space-y-8 text-xs">

                        <!-- Keterangan Penerima BANSOS -->
                        <div>
                            <h4 class="text-[11px] font-bold mb-3 uppercase tracking-wider text-brand border-b border-outline-variant/20 pb-2">Keterangan Penerima BANSOS</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3">
                                <template x-for="f in bansosFields" :key="f.key">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-baseline gap-1 border-b border-outline-variant/10 pb-2">
                                        <span class="text-on-surface-variant font-medium" x-text="f.label"></span>
                                        <span class="font-semibold text-on-surface text-left sm:text-right" x-text="data.bansos ? val(data.bansos[f.key]) : '-'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Data Detail -->
                        <div>
                            <h4 class="text-[11px] font-bold mb-3 uppercase tracking-wider text-brand border-b border-outline-variant/20 pb-2">Data Detail</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3">
                                <template x-for="f in detailFields" :key="f.key">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-baseline gap-1 border-b border-outline-variant/10 pb-2">
                                        <span class="text-on-surface-variant font-medium" x-text="f.label"></span>
                                        <span class="font-semibold text-on-surface text-left sm:text-right" x-text="data.detail ? val(data.detail[f.key]) : '-'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Lampiran -->
                        <div>
                            <h4 class="text-[11px] font-bold mb-3 uppercase tracking-wider text-brand border-b border-outline-variant/20 pb-2">Dokumen Lampiran</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <template x-for="f in lampiranFields" :key="f.key">
                                    <a :href="lampiranUrl(f.key)" target="_blank"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-outline-variant/40 transition-all"
                                        :class="lampiranUrl(f.key) ? 'text-brand hover:bg-brand/5 hover:border-brand/30 font-medium' : 'text-on-surface-variant/50 pointer-events-none bg-white'">
                                        <span class="material-symbols-outlined text-[18px]" x-text="lampiranUrl(f.key) ? 'description' : 'draft'"></span>
                                        <span x-text="f.label" class="truncate"></span>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <!-- Surat Keterangan -->
                        <div>
                            <h4 class="text-[11px] font-bold mb-3 uppercase tracking-wider text-brand border-b border-outline-variant/20 pb-2">Surat Keterangan</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <a :href="suratUrl('surat_pengantar_dtsen_kelurahan_digital')" target="_blank"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-outline-variant/40 transition-all"
                                    :class="suratUrl('surat_pengantar_dtsen_kelurahan_digital') ? 'text-brand hover:bg-brand/5 hover:border-brand/30 font-medium' : 'text-on-surface-variant/50 pointer-events-none bg-white'">
                                    <span class="material-symbols-outlined text-[18px]">badge</span>
                                    <span class="truncate">Surat Pengantar DTSEN Kelurahan Digital</span>
                                </a>
                                <a :href="suratUrl('surat_keterangan_dtsen_digital')" target="_blank"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-outline-variant/40 transition-all"
                                    :class="suratUrl('surat_keterangan_dtsen_digital') ? 'text-brand hover:bg-brand/5 hover:border-brand/30 font-medium' : 'text-on-surface-variant/50 pointer-events-none bg-white'">
                                    <span class="material-symbols-outlined text-[18px]">verified</span>
                                    <span class="truncate">Surat Keterangan DTSEN Digital</span>
                                </a>
                            </div>
                        </div>

                        <!-- Log Proses -->
                        <div>
                            <h4 class="text-[11px] font-bold mb-3 uppercase tracking-wider text-brand border-b border-outline-variant/20 pb-2">Log Proses</h4>
                            <div class="overflow-x-auto rounded-xl border border-outline-variant/20">
                                <table class="w-full text-[11px]">
                                    <thead class="bg-[#e6edf8]">
                                        <tr class="text-left text-on-surface-variant uppercase text-[10px] tracking-wider">
                                            <th class="px-3 py-2 font-semibold w-8">No</th>
                                            <th class="px-3 py-2 font-semibold">Tanggal</th>
                                            <th class="px-3 py-2 font-semibold">Username</th>
                                            <th class="px-3 py-2 font-semibold">Task</th>
                                            <th class="px-3 py-2 font-semibold">Role</th>
                                            <th class="px-3 py-2 font-semibold">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline-variant/20">
                                        <template x-for="(log, idx) in data.logs || []" :key="log.id ?? idx">
                                            <tr class="hover:bg-surface-container-low transition-colors">
                                                <td class="px-3 py-2.5 text-on-surface-variant" x-text="idx + 1"></td>
                                                <td class="px-3 py-2.5" x-text="val(log.tanggal_proses)"></td>
                                                <td class="px-3 py-2.5 font-medium" x-text="val(log.username)"></td>
                                                <td class="px-3 py-2.5" x-text="val(log.taskname)"></td>
                                                <td class="px-3 py-2.5 text-on-surface-variant" x-text="val(log.rolename)"></td>
                                                <td class="px-3 py-2.5" x-text="val(log.catatan)"></td>
                                            </tr>
                                        </template>
                                        <tr x-show="!(data.logs || []).length">
                                            <td colspan="6" class="px-3 py-6 text-center text-on-surface-variant italic">Belum ada log proses.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State Modal -->
                <div class="p-12 flex flex-col items-center justify-center gap-3 text-brand/70" x-show="loading">
                    <span class="material-symbols-outlined text-[32px] animate-spin">progress_activity</span>
                    <span class="text-sm font-medium">Memuat Detail Arsip...</span>
                </div>
            </div>
        </div>

        <form id="arsip-dtsen-tampilkan-form" method="GET" action="{{ route('dtsen.arsip') }}" class="hidden">
            <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
            <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
        </form>
    </div>

    @push('scripts')
        <script>
            function arsipDtsenManager() {
                return {
                    detailOpen: false,
                    loading: false,
                    data: {},
                    bansosFields: [
                        { key: 'peringkat_kesejahteraan_keluarga', label: 'Desil' },
                        { key: 'bnpt', label: 'BNPT' },
                        { key: 'pkh', label: 'PKH' },
                        { key: 'pbi', label: 'PBI' },
                        { key: 'yatim_piatu', label: 'Yatim Piatu' },
                        { key: 'tanggal_cetak', label: 'Tanggal Cetak' },
                        { key: 'berlaku_sampai', label: 'Berlaku Sampai' },
                        { key: 'dicetak_oleh', label: 'Dicetak Oleh' },
                    ],
                    detailFields: [
                        { key: 'nama', label: 'Nama' },
                        { key: 'nik', label: 'NIK' },
                        { key: 'no_kk', label: 'No KK' },
                        { key: 'alamat', label: 'Alamat' },
                        { key: 'tanggal_lahir', label: 'Tanggal Lahir' },
                        { key: 'alasan_keputusan_cetak', label: 'Alasan Keputusan Cetak' },
                    ],
                    lampiranFields: [
                        { key: 'scan_ktp', label: 'Scan KTP' },
                        { key: 'screenshot_dtsen', label: 'Screenshot DTSEN' },
                    ],
                    async openDetail(id) {
                        this.detailOpen = true;
                        this.loading = true;
                        try {
                            const res = await fetch(`{{ url('dtsen') }}/${id}/detail`, {
                                headers: { 'Accept': 'application/json' }
                            });
                            this.data = await res.json();
                        } catch (e) {
                            console.error('Gagal memuat detail', e);
                        }
                        this.loading = false;
                    },
                    val(v) {
                        return (v === null || v === undefined || v === '') ? '-' : v;
                    },
                    lampiranUrl(key) {
                        const path = this.data.lampiran ? this.data.lampiran[key] : null;
                        return path ? `/storage/${path}` : null;
                    },
                    suratUrl(key) {
                        const path = this.data.surat ? this.data.surat[key] : null;
                        return path ? `/storage/${path}` : null;
                    },
                }
            }
        </script>

        <script>
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
