@extends('layouts.admin')

@section('title', 'Arsip PBI APBN')
@section('page_title', 'PBI APBN — Arsip')

@section('content')
    <div class="space-y-6" x-data="arsipPbiManager()">

        @if (session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-sm text-sm bg-green-50 border border-green-200 text-green-700 transition-all">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter Section -->
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 shadow-sm">
            <form method="GET" action="{{ route('pbi-apbn.arsip.index') }}" data-auto-filter>
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
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Status</label>
                        <select name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-xs focus:ring-1 focus:outline-none transition-colors">
                            <option value="">Semua Status</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
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
                    <a href="{{ route('pbi-apbn.arsip.index') }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-error hover:bg-error/10 border border-transparent hover:border-error/20 transition-all">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        Reset Filter
                    </a>

                    <!-- Pengaturan Tampilkan X Data -->
                    <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                        Tampilkan
                        <select name="tampilkan" onchange="this.form.submit()" form="arsip-pbi-tampilkan-form"
                            class="rounded-lg border border-outline-variant/50 px-2 py-1.5 text-xs bg-surface-container-lowest focus:outline-none">
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
                <table class="w-full text-xs table-fixed min-w-[950px]">
                    <colgroup>
                        <col style="width: 5%">
                        <col style="width: 10%">
                        <col style="width: 14%">
                        <col style="width: 16%">
                        <col style="width: 8%">
                        <col style="width: 20%">
                        <col style="width: 15%">
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
                            <th class="text-center px-3 py-3 whitespace-nowrap">Status</th>
                            <th class="text-center px-3 py-3 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @forelse ($arsip as $item)
                            @php $isSelesai = $item->status === 'disetujui'; @endphp
                            <tr class="hover:bg-surface-container-low transition-colors group">
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-center text-on-surface-variant">{{ $arsip->firstItem() + $loop->index }}</td>
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
                                <td class="px-3 py-3.5 whitespace-nowrap truncate text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium border
                                        {{ $isSelesai ? 'bg-success-container text-on-success-container border-success-container/50' : 'bg-red-50 text-red-700 border-red-200' }}">
                                        <span class="material-symbols-outlined text-[14px]">
                                            {{ $isSelesai ? 'check_circle' : 'cancel' }}
                                        </span>
                                        {{ $item->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 whitespace-nowrap text-center">
                                    <button @click="openDetail({{ $item->id }})"
                                        class="inline-flex items-center justify-center gap-1.5 w-full max-w-[80px] px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-semibold transition-all duration-200">
                                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                                        Lihat
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-on-surface-variant">
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

            @if($arsip->hasPages())
                <div class="px-5 py-4 border-t border-outline-variant/20 bg-surface-container-lowest">
                    {{ $arsip->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Detail Arsip --}}
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-opacity">
            <div @click.outside="detailOpen = false"
                class="bg-surface-container-lowest w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col rounded-2xl shadow-2xl transition-transform"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/30 bg-surface-container-lowest shrink-0">
                    <h3 class="text-base font-semibold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">assignment</span>
                        Detail Arsip PBI APBN
                    </h3>
                    <button @click="detailOpen = false" class="text-on-surface-variant hover:bg-outline-variant/10 p-1.5 rounded-full transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto" x-show="!loading">
                    <div class="space-y-8 text-xs">

                        <!-- Data Permohonan -->
                        <div>
                            <h4 class="text-[11px] font-bold mb-3 uppercase tracking-wider text-primary border-b border-outline-variant/20 pb-2">Data Permohonan</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3">
                                <template x-for="f in dataFields" :key="f.key">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-baseline gap-1 border-b border-outline-variant/10 pb-2">
                                        <span class="text-on-surface-variant font-medium" x-text="f.label"></span>
                                        <span class="font-semibold text-on-surface text-left sm:text-right" x-text="val(data[f.key])"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Anggota Keluarga -->
                        <div>
                            <h4 class="text-[11px] font-bold mb-3 uppercase tracking-wider text-primary border-b border-outline-variant/20 pb-2">Anggota Keluarga</h4>
                            <div class="overflow-x-auto rounded-lg border border-outline-variant/20">
                                <table class="w-full text-[11px]">
                                    <thead class="bg-surface-container/50">
                                        <tr class="text-left text-on-surface-variant uppercase text-[10px] tracking-wider">
                                            <th class="px-3 py-2 font-semibold w-8">No</th>
                                            <th class="px-3 py-2 font-semibold">Nama</th>
                                            <th class="px-3 py-2 font-semibold">NIK</th>
                                            <th class="px-3 py-2 font-semibold">Hubungan</th>
                                            <th class="px-3 py-2 font-semibold">JK</th>
                                            <th class="px-3 py-2 font-semibold">Pekerjaan</th>
                                            <th class="px-3 py-2 font-semibold">BPJS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline-variant/20">
                                        <template x-for="(a, idx) in data.anggota_keluarga || []" :key="a.id ?? idx">
                                            <tr class="hover:bg-surface-container-low transition-colors">
                                                <td class="px-3 py-2.5 text-on-surface-variant" x-text="idx + 1"></td>
                                                <td class="px-3 py-2.5 font-medium" x-text="val(a.nama)"></td>
                                                <td class="px-3 py-2.5 text-on-surface-variant" x-text="val(a.nik)"></td>
                                                <td class="px-3 py-2.5" x-text="val(a.hubungan_keluarga)"></td>
                                                <td class="px-3 py-2.5" x-text="val(a.jenis_kelamin)"></td>
                                                <td class="px-3 py-2.5" x-text="val(a.pekerjaan)"></td>
                                                <td class="px-3 py-2.5" x-text="val(a.keanggotaan_bpjs)"></td>
                                            </tr>
                                        </template>
                                        <tr x-show="!(data.anggota_keluarga || []).length">
                                            <td colspan="7" class="px-3 py-6 text-center text-on-surface-variant italic">Tidak ada data anggota keluarga.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Lampiran -->
                        <div>
                            <h4 class="text-[11px] font-bold mb-3 uppercase tracking-wider text-primary border-b border-outline-variant/20 pb-2">Dokumen Lampiran</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <template x-for="f in lampiranFields" :key="f.key">
                                    <a :href="lampiranUrl(f.key)" target="_blank"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-outline-variant/40 transition-all"
                                        :class="lampiranUrl(f.key) ? 'text-primary hover:bg-primary/5 hover:border-primary/30 font-medium' : 'text-on-surface-variant/50 pointer-events-none bg-surface-container-lowest'">
                                        <span class="material-symbols-outlined text-[18px]">
                                            <span x-show="lampiranUrl(f.key)">description</span>
                                            <span x-show="!lampiranUrl(f.key)">draft</span>
                                        </span>
                                        <span x-text="f.label" class="truncate"></span>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <!-- Riwayat Diagnosa -->
                        <div>
                            <h4 class="text-[11px] font-bold mb-3 uppercase tracking-wider text-primary border-b border-outline-variant/20 pb-2">Riwayat Diagnosa</h4>
                            <div class="overflow-x-auto rounded-lg border border-outline-variant/20">
                                <table class="w-full text-[11px]">
                                    <thead class="bg-surface-container/50">
                                        <tr class="text-left text-on-surface-variant uppercase text-[10px] tracking-wider">
                                            <th class="px-3 py-2 font-semibold w-8">No</th>
                                            <th class="px-3 py-2 font-semibold">Tanggal</th>
                                            <th class="px-3 py-2 font-semibold">Username</th>
                                            <th class="px-3 py-2 font-semibold">Role</th>
                                            <th class="px-3 py-2 font-semibold">Diagnosa</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline-variant/20">
                                        <template x-for="(d, idx) in data.diagnosa_logs || []" :key="d.id ?? idx">
                                            <tr class="hover:bg-surface-container-low transition-colors">
                                                <td class="px-3 py-2.5 text-on-surface-variant" x-text="idx + 1"></td>
                                                <td class="px-3 py-2.5" x-text="formatTanggal(d.created_at)"></td>
                                                <td class="px-3 py-2.5 font-medium" x-text="val(d.username)"></td>
                                                <td class="px-3 py-2.5 text-on-surface-variant" x-text="val(d.role_name)"></td>
                                                <td class="px-3 py-2.5" x-text="val(d.diagnosa)"></td>
                                            </tr>
                                        </template>
                                        <tr x-show="!(data.diagnosa_logs || []).length">
                                            <td colspan="5" class="px-3 py-6 text-center text-on-surface-variant italic">Belum ada riwayat diagnosa.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Log Proses -->
                        <div>
                            <h4 class="text-[11px] font-bold mb-3 uppercase tracking-wider text-primary border-b border-outline-variant/20 pb-2">Log Proses</h4>
                            <div class="overflow-x-auto rounded-lg border border-outline-variant/20">
                                <table class="w-full text-[11px]">
                                    <thead class="bg-surface-container/50">
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
                                                <td class="px-3 py-2.5" x-text="formatTanggal(log.created_at)"></td>
                                                <td class="px-3 py-2.5 font-medium" x-text="val(log.username)"></td>
                                                <td class="px-3 py-2.5" x-text="val(log.task_name)"></td>
                                                <td class="px-3 py-2.5 text-on-surface-variant" x-text="val(log.role_name)"></td>
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
                <div class="p-12 flex flex-col items-center justify-center gap-3 text-primary/70" x-show="loading">
                    <span class="material-symbols-outlined text-[32px] animate-spin">progress_activity</span>
                    <span class="text-sm font-medium">Memuat Detail Arsip...</span>
                </div>
            </div>
        </div>

        <form id="arsip-pbi-tampilkan-form" method="GET" action="{{ route('pbi-apbn.arsip.index') }}" class="hidden">
            <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
            <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
        </form>
    </div>

    @push('scripts')
        <script>
            function arsipPbiManager() {
                return {
                    detailOpen: false,
                    loading: false,
                    data: {},
                    detailUrlTemplate: @js(route('pbi-apbn.arsip.detail', ['pbiApbn' => '__ID__'])),
                    dataFields: [
                        { key: 'no_registrasi', label: 'No. Registrasi' },
                        { key: 'status', label: 'Status' },
                        { key: 'nama_kepala_keluarga', label: 'Kepala Keluarga' },
                        { key: 'nik_kepala_keluarga', label: 'NIK Kepala Keluarga' },
                        { key: 'no_kk', label: 'No. KK' },
                        { key: 'no_telp', label: 'No. Telp' },
                        { key: 'alamat', label: 'Alamat' },
                        { key: 'rt', label: 'RT' },
                        { key: 'rw', label: 'RW' },
                        { key: 'desa_kelurahan', label: 'Kelurahan' },
                        { key: 'kecamatan', label: 'Kecamatan' },
                        { key: 'kabupaten_kota', label: 'Kota/Kabupaten' },
                        { key: 'desil_nasional', label: 'Desil' },
                        { key: 'penghasilan_rata_rata', label: 'Penghasilan Rata-rata' },
                        { key: 'jumlah_tanggungan_keluarga', label: 'Jumlah Tanggungan' },
                        { key: 'nama_faskes', label: 'Nama Faskes' },
                        { key: 'diagnosa', label: 'Diagnosa Terakhir' },
                        { key: 'kesimpulan_rekomendasi', label: 'Kesimpulan Rekomendasi' },
                        { key: 'catatan_internal', label: 'Catatan Internal' },
                    ],
                    lampiranFields: [
                        { key: 'screenshot_dtsen', label: 'Screenshot DTKS' }, // Perbaikan label jika typ0
                        { key: 'surat_rawat_inap', label: 'Surat Rawat Inap' },
                        { key: 'scan_ktp', label: 'Scan KTP' },
                        { key: 'scan_kk', label: 'Scan KK' },
                        { key: 'foto_rumah', label: 'Foto Rumah' },
                        { key: 'foto_kamar_mandi', label: 'Foto Kamar Mandi' },
                        { key: 'foto_selfie_ktp', label: 'Foto Selfie KTP' },
                        { key: 'screenshot_pembaharuan_desil', label: 'SS Update Desil' },
                    ],
                    async openDetail(id) {
                        this.detailOpen = true;
                        this.loading = true;
                        this.data = {};
                        try {
                            const res = await fetch(this.detailUrlTemplate.replace('__ID__', id), {
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
                        const path = this.data ? this.data[key] : null;
                        return path ? `/storage/${path}` : null;
                    },
                    formatTanggal(v) {
                        if (!v) return '-';
                        const d = new Date(v);
                        if (isNaN(d)) return v;
                        const p = n => String(n).padStart(2, '0');
                        return `${p(d.getDate())}-${p(d.getMonth() + 1)}-${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}`;
                    },
                }
            }

            // Script Pencarian Otomatis
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
