@extends('layouts.admin')

@section('title', 'Arsip DTSEN - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'DTSEN - Arsip')

@section('content')
    <div class="space-y-5" x-data="arsipDtsenManager()">

        @if (session('success'))
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs bg-green-50 border border-green-200 text-green-700">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('dtsen.arsip') }}"
            class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-on-surface-variant mb-1">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}"
                    class="px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
            </div>
            <div>
                <label class="block text-xs text-on-surface-variant mb-1">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                    class="px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
            </div>
            <div>
                <label class="block text-xs text-on-surface-variant mb-1">Alasan Cetak</label>
                <select name="alasan_cetak" class="px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs">
                    <option value="-Pilih-">-Pilih-</option>
                    @foreach ($alasanCetakOptions as $opt)
                        <option value="{{ $opt }}" @selected(request('alasan_cetak') === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, Alamat..."
                    class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
            </div>
            <button type="submit"
                class="px-4 py-1.5 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-medium">
                Terapkan
            </button>

            <div class="flex items-center gap-2 text-xs text-on-surface-variant ml-auto">
                Tampilkan
                <select name="tampilkan" onchange="this.form.submit()" form="arsip-dtsen-tampilkan-form"
                    class="rounded-lg border border-outline-variant/50 px-2 py-1 text-xs">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected((int) request('tampilkan', 10) === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                data
            </div>
        </form>

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
            <table class="w-full text-xs table-fixed">
                <colgroup>
                    <col style="width: 4%">
                    <col style="width: 10%">
                    <col style="width: 13%">
                    <col style="width: 13%">
                    <col style="width: 6%">
                    <col style="width: 14%">
                    <col style="width: 13%">
                    <col style="width: 12%">
                    <col style="width: 15%">
                </colgroup>
                <thead class="bg-surface-container text-on-surface-variant uppercase">
                    <tr>
                        <th class="text-center px-2 py-2 whitespace-nowrap">No</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Tgl Insert</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">NIK</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Nama</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Desil</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Alamat</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Alasan Cetak</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Status</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($arsipList as $item)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-2 py-2 whitespace-nowrap truncate text-center">{{ $arsipList->firstItem() + $loop->index }}</td>
                            <td class="px-2 py-2 whitespace-nowrap text-center">{{ $item->tanggal_insert->format('d-m-Y') }}</td>
                            <td class="px-2 py-2 whitespace-nowrap truncate font-medium text-primary text-center" title="{{ $item->nik }}">{{ $item->nik }}</td>
                            <td class="px-2 py-2 whitespace-nowrap truncate" title="{{ $item->nama_pemohon }}">{{ $item->nama_pemohon }}</td>
                            <td class="px-2 py-2 whitespace-nowrap truncate text-center">{{ $item->bansos->peringkat_kesejahteraan_keluarga ?? '-' }}</td>
                            <td class="px-2 py-2 whitespace-nowrap truncate text-on-surface-variant" title="{{ $item->alamat }}">{{ $item->alamat }}</td>
                            <td class="px-2 py-2 whitespace-nowrap truncate" title="{{ $item->alasan_cetak }}">{{ $item->alasan_cetak }}</td>
                            <td class="px-2 py-2 whitespace-nowrap truncate text-center">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[11px] font-medium
                                    {{ $item->status === 'selesai' ? 'bg-success-container text-on-success-container' : 'bg-secondary-fixed text-on-secondary-container' }}">
                                    <span class="material-symbols-outlined text-[12px]">
                                        {{ $item->status === 'selesai' ? 'check_circle' : 'schedule' }}
                                    </span>
                                    {{ $statusLabels[$item->status] ?? $item->status }}
                                </span>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-center">
                                <button @click="openDetail({{ $item->id }})"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">visibility</span>
                                    Lihat
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[28px] block mb-2 opacity-40">inbox</span>
                                Tidak ada data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-outline-variant/40">
                {{ $arsipList->links() }}
            </div>
        </div>

        {{-- Modal Detail Arsip --}}
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div @click.outside="detailOpen = false"
                class="bg-surface-container-lowest w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-xl shadow-lg">

                <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40 sticky top-0 bg-surface-container-lowest z-10">
                    <h3 class="text-sm font-semibold text-on-surface">Detail DTSEN</h3>
                    <button @click="detailOpen = false" class="text-on-surface-variant">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <div class="p-5 space-y-6 text-xs" x-show="!loading">
                    <div>
                        <h4 class="text-xs font-semibold mb-2 uppercase text-on-surface-variant">Keterangan Penerima BANSOS</h4>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                            <template x-for="f in bansosFields" :key="f.key">
                                <p><span class="text-on-surface-variant" x-text="f.label + ':'"></span> <span x-text="data.bansos ? data.bansos[f.key] : '-'"></span></p>
                            </template>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold mb-2 uppercase text-on-surface-variant">Data Detail</h4>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                            <template x-for="f in detailFields" :key="f.key">
                                <p><span class="text-on-surface-variant" x-text="f.label + ':'"></span> <span x-text="data.detail ? data.detail[f.key] : '-'"></span></p>
                            </template>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold mb-2 uppercase text-on-surface-variant">Lampiran</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="f in lampiranFields" :key="f.key">
                                <a :href="lampiranUrl(f.key)" target="_blank"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg border border-outline-variant/40"
                                    :class="lampiranUrl(f.key) ? 'text-primary hover:bg-surface-container' : 'text-on-surface-variant pointer-events-none opacity-50'">
                                    <span class="material-symbols-outlined text-[14px]">description</span>
                                    <span x-text="f.label"></span>
                                </a>
                            </template>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold mb-2 uppercase text-on-surface-variant">Surat Keterangan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <a :href="suratUrl('surat_pengantar_dtsen_kelurahan_digital')" target="_blank"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg border border-outline-variant/40"
                                :class="suratUrl('surat_pengantar_dtsen_kelurahan_digital') ? 'text-primary hover:bg-surface-container' : 'text-on-surface-variant pointer-events-none opacity-50'">
                                <span class="material-symbols-outlined text-[14px]">badge</span>
                                Surat Pengantar DTSEN Kelurahan Digital
                            </a>
                            <a :href="suratUrl('surat_keterangan_dtsen_digital')" target="_blank"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg border border-outline-variant/40"
                                :class="suratUrl('surat_keterangan_dtsen_digital') ? 'text-primary hover:bg-surface-container' : 'text-on-surface-variant pointer-events-none opacity-50'">
                                <span class="material-symbols-outlined text-[14px]">verified</span>
                                Surat Keterangan DTSEN Digital
                            </a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold mb-2 uppercase text-on-surface-variant">Log Proses</h4>
                        <table class="w-full text-[11px]">
                            <thead>
                                <tr class="text-left text-on-surface-variant border-b border-outline-variant/40">
                                    <th class="py-1.5 w-8">No</th>
                                    <th class="py-1.5">Tanggal</th>
                                    <th class="py-1.5">Username</th>
                                    <th class="py-1.5">Task</th>
                                    <th class="py-1.5">Role</th>
                                    <th class="py-1.5">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(log, idx) in data.logs || []" :key="log.id">
                                    <tr class="border-b border-outline-variant/20">
                                        <td class="py-1.5" x-text="idx + 1"></td>
                                        <td class="py-1.5" x-text="log.tanggal_proses"></td>
                                        <td class="py-1.5" x-text="log.username"></td>
                                        <td class="py-1.5" x-text="log.taskname"></td>
                                        <td class="py-1.5" x-text="log.rolename"></td>
                                        <td class="py-1.5" x-text="log.catatan ?? '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-10 text-center text-xs text-on-surface-variant" x-show="loading">Memuat data...</div>
            </div>
        </div>

        <form id="arsip-dtsen-tampilkan-form" method="GET" action="{{ route('dtsen.arsip') }}" class="hidden">
            <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
            <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            <input type="hidden" name="alasan_cetak" value="{{ request('alasan_cetak') }}">
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
    @endpush
@endsection
