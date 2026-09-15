    @extends('layouts.admin')

    @section('title', 'Arsip Kartu KKS - SOLID Dinas Sosial Kota Bogor')
    @section('page_title', 'Kartu KKS - Arsip')

    @section('content')
        <div x-data="arsipManager()">
            @if (session('success'))
                <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-700">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
                <h2 class="text-base font-semibold text-on-surface mb-4">Arsip Kartu KKS</h2>

                <form method="GET" action="{{ route('kartu-kks.arsip') }}" class="mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-medium mb-1 text-on-surface-variant">Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}"
                                class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1 text-on-surface-variant">Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                                class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1 text-on-surface-variant">Masalah Kartu</label>
                            <select name="masalah_kartu" class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">
                                <option value="-Pilih-">-Pilih-</option>
                                @foreach ($masalahKartuOptions as $opt)
                                    <option value="{{ $opt }}" @selected(request('masalah_kartu') === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-medium mb-1 text-on-surface-variant">Pencarian (NIK / Nama / Alamat)</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" placeholder="Cari..." />
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <button type="submit"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-primary text-on-primary">
                            <span class="material-symbols-outlined text-[18px]">search</span>
                            Proses
                        </button>
                        <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                            Tampilkan
                            <select name="tampilkan" onchange="this.form.submit()" form="arsip-tampilkan-form"
                                class="rounded-lg border border-outline-variant/50 px-2 py-1 text-sm">
                                @foreach ([10, 25, 50, 100] as $n)
                                    <option value="{{ $n }}" @selected((int) request('tampilkan', 10) === $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                            Data Perhalaman
                        </div>
                    </div>
                </form>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-on-surface-variant border-b border-outline-variant/40">
                            <th class="py-2 w-12">No</th>
                            <th class="py-2">Tgl Insert</th>
                            <th class="py-2">NIK</th>
                            <th class="py-2">Nama</th>
                            <th class="py-2">Alamat</th>
                            <th class="py-2">Masalah KKS</th>
                            <th class="py-2 text-center">Arsip</th>
                            <th class="py-2 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($arsipList as $item)
                            <tr class="border-b border-outline-variant/20">
                                <td class="py-2">{{ $arsipList->firstItem() + $loop->index }}</td>
                                <td class="py-2">{{ $item->tanggal_insert->format('d-m-Y') }}</td>
                                <td class="py-2">{{ $item->nik }}</td>
                                <td class="py-2">{{ $item->nama_pemohon }}</td>
                                <td class="py-2 max-w-xs truncate">{{ $item->alamat }}</td>
                                <td class="py-2">{{ $item->masalah_kartu }}</td>
                                <td class="py-2 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                        {{ $item->status === 'selesai' ? 'bg-success-container text-on-success-container' : 'bg-secondary-fixed text-on-secondary-container' }}">
                                        {{ $statusLabels[$item->status] ?? $item->status }}
                                    </span>
                                </td>
                                <td class="py-2 text-center">
                                    <button @click="openDetail({{ $item->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-100 text-blue-700 hover:bg-blue-200 transition">
                                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-on-surface-variant">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">{{ $arsipList->links() }}</div>

                {{-- Modal Detail Arsip --}}
                <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div @click.outside="detailOpen = false"
                        class="bg-surface-container-lowest w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-xl shadow-lg">

                        <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40 sticky top-0 bg-surface-container-lowest z-10">
                            <h3 class="text-base font-semibold text-on-surface">Detail Kartu KKS</h3>
                            <button @click="detailOpen = false" class="text-on-surface-variant">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <div class="p-5 space-y-6" x-show="!loading">
                            <div>
                                <h4 class="text-sm font-semibold mb-2">Data Permohonan</h4>
                                <p class="text-sm">NIK: <span x-text="data.nik"></span></p>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold mb-2">Data Detail</h4>
                                <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-sm">
                                    <template x-for="f in detailFields" :key="f.key">
                                        <p><span class="text-on-surface-variant" x-text="f.label + ':'"></span> <span x-text="data.detail ? data.detail[f.key] : '-'"></span></p>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold mb-2">Lampiran</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                                    <template x-for="f in lampiranFields" :key="f.key">
                                        <a :href="lampiranUrl(f.key)" target="_blank"
                                            class="flex items-center gap-2 px-3 py-2 rounded-lg border border-outline-variant/40"
                                            :class="lampiranUrl(f.key) ? 'text-primary hover:bg-surface-container' : 'text-on-surface-variant pointer-events-none opacity-50'">
                                            <span class="material-symbols-outlined text-[16px]">description</span>
                                            <span x-text="f.label"></span>
                                        </a>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold mb-2">Surat Keterangan</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <a :href="suratUrl('surat_pengantar_kelurahan')" target="_blank"
                                        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-outline-variant/40"
                                        :class="suratUrl('surat_pengantar_kelurahan') ? 'text-primary hover:bg-surface-container' : 'text-on-surface-variant pointer-events-none opacity-50'">
                                        <span class="material-symbols-outlined text-[16px]">badge</span>
                                        Surat Pengantar KKS Kelurahan Digital
                                    </a>
                                    <a :href="suratUrl('surat_keterangan_dinsos')" target="_blank"
                                        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-outline-variant/40"
                                        :class="suratUrl('surat_keterangan_dinsos') ? 'text-primary hover:bg-surface-container' : 'text-on-surface-variant pointer-events-none opacity-50'">
                                        <span class="material-symbols-outlined text-[16px]">verified</span>
                                        Surat Keterangan KKS Digital
                                    </a>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold mb-2">Log Proses</h4>
                                <table class="w-full text-xs">
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

                        <div class="p-10 text-center text-on-surface-variant" x-show="loading">Memuat data...</div>
                    </div>
                </div>
            </div>

            <form id="arsip-tampilkan-form" method="GET" action="{{ route('kartu-kks.arsip') }}" class="hidden">
                <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
                <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
                <input type="hidden" name="masalah_kartu" value="{{ request('masalah_kartu') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
            </form>
        </div>

        @push('scripts')
            <script>
                function arsipManager() {
                    return {
                        detailOpen: false,
                        loading: false,
                        data: {},
                        detailFields: [
                            { key: 'nama', label: 'Nama' },
                            { key: 'jenis_kelamin', label: 'Jenis Kelamin' },
                            { key: 'tempat_lahir', label: 'Tempat Lahir' },
                            { key: 'tanggal_lahir', label: 'Tanggal Lahir' },
                            { key: 'agama', label: 'Agama' },
                            { key: 'status_perkawinan', label: 'Status Perkawinan' },
                            { key: 'no_pkh', label: 'No PKH' },
                            { key: 'no_kk', label: 'No KK' },
                            { key: 'no_kartu_kks', label: 'No Kartu/KKS' },
                            { key: 'no_rekening', label: 'No Rekening' },
                            { key: 'pekerjaan', label: 'Pekerjaan' },
                            { key: 'alamat', label: 'Alamat' },
                            { key: 'masalah_kartu', label: 'Masalah Kartu' },
                            { key: 'nomor_kehilangan_polisi', label: 'No. Kehilangan Polisi' },
                        ],
                        lampiranFields: [
                            { key: 'scan_ktp', label: 'Scan KTP' },
                            { key: 'scan_kartu_keluarga', label: 'Scan KK' },
                            { key: 'surat_kehilangan_polisi', label: 'Surat Kehilangan Polisi' },
                            { key: 'scan_kartu_kks', label: 'Scan KKS' },
                            { key: 'screenshot_dtsen', label: 'Screenshot DTsen' },
                        ],
                        async openDetail(id) {
                            this.detailOpen = true;
                            this.loading = true;
                            try {
                                const res = await fetch(`{{ url('kartu-kks') }}/${id}/detail`, {
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
