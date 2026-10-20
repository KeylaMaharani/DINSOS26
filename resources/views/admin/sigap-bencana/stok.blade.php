@extends('layouts.admin')

@section('title', 'Stok Barang - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'SIGAP Bencana — Stok Barang')

@php
    $card = 'bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden';
    $input = 'w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand';
@endphp

@section('content')
    {{-- tab : 'stok' (Daftar Stok Barang) | 'riwayat' (Riwayat Stok Terbaru)
         - Pilihan tab diingat di localStorage, jadi setelah halaman dimuat ulang tetap di tab terakhir
         - Jika ada error validasi (form tambah barang), otomatis kembali ke tab 'stok' --}}
    <div class="space-y-6"
        x-data="{
            modal: false,
            nama: '',
            satuan: '',
            action: '',
            tambahBarang: {{ $errors->any() && old('nama') ? 'true' : 'false' }},
            tab: 'stok',
            riwayatSearch: '',
            cocok(el) {
                const q = this.riwayatSearch.toLowerCase().trim();
                return q === '' || el.dataset.search.includes(q);
            },
            jumlahCocok() {
                return Array.from(this.$refs.riwayatBody.querySelectorAll('tr[data-search]'))
                    .filter(tr => this.cocok(tr)).length;
            },
            init() {
                const adaError = {{ $errors->any() ? 'true' : 'false' }};
                try {
                    if (!adaError && localStorage.getItem('stokTab') === 'riwayat') this.tab = 'riwayat';
                } catch (e) {}
                this.$watch('tab', v => { try { localStorage.setItem('stokTab', v); } catch (e) {} });
            }
        }">

        @if ($errors->any())
            <div class="flex flex-col gap-1 px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        {{-- ===== Tab ===== --}}
        <div role="tablist"
            class="inline-flex max-w-full gap-1 p-1.5 rounded-2xl bg-white shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-x-auto">
            <button type="button" role="tab" @click="tab = 'stok'" :aria-selected="tab === 'stok'"
                :class="tab === 'stok' ? 'bg-brand text-white shadow-sm' : 'text-on-surface-variant hover:bg-surface-container'"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                Daftar Stok Barang
                <span :class="tab === 'stok' ? 'bg-white/25 text-white' : 'bg-brand/10 text-brand'"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold">{{ number_format($barangList->total()) }}</span>
            </button>
            <button type="button" role="tab" @click="tab = 'riwayat'" :aria-selected="tab === 'riwayat'"
                :class="tab === 'riwayat' ? 'bg-brand text-white shadow-sm' : 'text-on-surface-variant hover:bg-surface-container'"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                <span class="material-symbols-outlined text-[18px]">swap_vert</span>
                Riwayat Stok Terbaru
                <span :class="tab === 'riwayat' ? 'bg-white/25 text-white' : 'bg-brand/10 text-brand'"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold">{{ number_format(count($mutasiTerbaru)) }}</span>
            </button>
        </div>

        {{-- ==================== TAB 1: DAFTAR STOK BARANG ==================== --}}
        <div x-show="tab === 'stok'" x-cloak class="space-y-6">

            {{-- ===== Cari + tombol tambah barang ===== --}}
            <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] p-5">
                <form method="GET" action="{{ route('sigap.stok.index') }}" data-auto-filter
                    class="flex flex-wrap items-end justify-between gap-4">
                    <div class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Cari Barang</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau kode barang..."
                                    class="w-64 pl-8 pr-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors" />
                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-outline-variant/80">search</span>
                            </div>
                        </div>
                        <a href="{{ route('sigap.stok.index') }}"
                            class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium text-error hover:bg-error/10 transition-all">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span> Reset
                        </a>
                    </div>

                    @if ($bolehKelola)
                        <button type="button" @click="tambahBarang = !tambahBarang"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand text-white hover:bg-primary text-xs font-bold shadow-sm transition-colors">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            Tambah Barang Baru
                        </button>
                    @endif
                </form>

                {{-- Form tambah barang --}}
                @if ($bolehKelola)
                    <form method="POST" action="{{ route('sigap.stok.barang.store') }}" x-show="tambahBarang" x-cloak
                        class="mt-5 pt-5 border-t border-outline-variant/20 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                        @csrf
                        <div class="lg:col-span-2">
                            <label class="block text-xs text-on-surface-variant mb-1">Nama Barang <span class="text-error">*</span></label>
                            <input type="text" name="nama" value="{{ old('nama') }}" class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">Kode</label>
                            <input type="text" name="kode" value="{{ old('kode') }}" class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">Satuan <span class="text-error">*</span></label>
                            <input type="text" name="satuan" value="{{ old('satuan') }}" placeholder="pcs, paket, lembar" class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">Stok Awal <span class="text-error">*</span></label>
                            <input type="number" min="0" name="stok_awal" value="{{ old('stok_awal', 0) }}" class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">Batas Stok Menipis</label>
                            <input type="number" min="0" name="stok_minimum" value="{{ old('stok_minimum', 0) }}" class="{{ $input }}" />
                        </div>
                        <div class="sm:col-span-2 lg:col-span-6 flex justify-end gap-2">
                            <button type="button" @click="tambahBarang = false"
                                class="px-4 py-2 rounded-xl border border-outline-variant/50 text-sm hover:bg-surface-container transition-colors">Batal</button>
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-brand text-white hover:bg-primary text-sm font-bold transition-colors">
                                <span class="material-symbols-outlined text-[18px]">save</span> Simpan Barang
                            </button>
                        </div>
                    </form>
                @else
                    <p class="mt-4 text-[11px] text-on-surface-variant">
                        Hanya Pengurus Barang yang dapat menambah barang dan stok. Anda dapat melihat stok terkini di sini.
                    </p>
                @endif
            </div>

            {{-- ===== Tabel stok ===== --}}
            <div class="{{ $card }}">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-brand">inventory_2</span>
                        Daftar Stok Barang
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left min-w-[760px]">
                        <thead class="bg-[#e6edf8] text-on-surface-variant text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 font-medium">Kode</th>
                                <th class="px-5 py-3 font-medium">Nama Barang</th>
                                <th class="px-5 py-3 font-medium text-center">Satuan</th>
                                <th class="px-5 py-3 font-medium text-center">Stok</th>
                                <th class="px-5 py-3 font-medium text-center">Batas Menipis</th>
                                @if ($bolehKelola)
                                    <th class="px-5 py-3 font-medium text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @forelse ($barangList as $b)
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-5 py-3 text-on-surface-variant">{{ $b->kode ?: '-' }}</td>
                                    <td class="px-5 py-3 font-medium">{{ $b->nama }}</td>
                                    <td class="px-5 py-3 text-center text-on-surface-variant">{{ $b->satuan }}</td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-block min-w-[3rem] px-2.5 py-1 rounded-full text-xs font-bold {{ $b->stokMenipis() ? 'bg-error-container text-on-error-container' : 'bg-success-container text-on-success-container' }}">
                                            {{ number_format($b->stok) }}
                                        </span>
                                        @if ($b->stokMenipis())
                                            <span class="block text-[10px] text-error mt-0.5">Stok menipis</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center text-on-surface-variant">{{ $b->stok_minimum }}</td>
                                    @if ($bolehKelola)
                                        <td class="px-5 py-3 text-center">
                                            <button type="button"
                                                @click="modal = true; nama = @js($b->nama); satuan = @js($b->satuan); action = @js(route('sigap.stok.masuk', $b))"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand/10 text-brand hover:bg-brand hover:text-white text-[11px] font-semibold transition-all">
                                                <span class="material-symbols-outlined text-[14px]">add</span> Tambah Stok
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $bolehKelola ? 6 : 5 }}" class="px-5 py-10 text-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-[36px] opacity-30 block mb-2">inventory_2</span>
                                        Belum ada barang. @if ($bolehKelola) Tambahkan barang baru di atas. @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($barangList->hasPages())
                    <div class="px-5 py-4 border-t border-outline-variant/20 bg-white">{{ $barangList->links() }}</div>
                @endif
            </div>
        </div>

        {{-- ==================== TAB 2: RIWAYAT STOK TERBARU ==================== --}}
        <div x-show="tab === 'riwayat'" x-cloak class="space-y-6">

            {{-- ===== Cari riwayat (sama seperti kartu cari di tab stok, tanpa tombol tambah).
                 Memfilter baris di halaman ini: barang, jenis, keterangan, petugas, waktu, no. tiket ===== --}}
            <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] p-5">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Cari Riwayat</label>
                        <div class="relative">
                            <input type="text" x-model="riwayatSearch" placeholder="Barang, keterangan, atau petugas..."
                                class="w-64 pl-8 pr-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors" />
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-outline-variant/80">search</span>
                        </div>
                    </div>
                    <button type="button" @click="riwayatSearch = ''"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium text-error hover:bg-error/10 transition-all">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span> Reset
                    </button>
                    <span x-show="riwayatSearch !== ''" x-cloak class="py-2 text-[11px] text-on-surface-variant whitespace-nowrap">
                        <span x-text="jumlahCocok()"></span> hasil
                    </span>
                </div>
            </div>

            <div class="{{ $card }} mb-10">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-brand">swap_vert</span>
                        Riwayat Stok Terbaru
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left min-w-[760px]">
                        <thead class="bg-[#e6edf8] text-on-surface-variant text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 font-medium">Waktu</th>
                                <th class="px-5 py-3 font-medium">Barang</th>
                                <th class="px-5 py-3 font-medium text-center">Jenis</th>
                                <th class="px-5 py-3 font-medium text-center">Jumlah</th>
                                <th class="px-5 py-3 font-medium text-center">Saldo</th>
                                <th class="px-5 py-3 font-medium">Keterangan</th>
                                <th class="px-5 py-3 font-medium">Petugas</th>
                            </tr>
                        </thead>
                        <tbody x-ref="riwayatBody" class="divide-y divide-outline-variant/30">
                            @forelse ($mutasiTerbaru as $m)
                                <tr x-show="cocok($el)"
                                    data-search="{{ \Illuminate\Support\Str::lower(implode(' ', [
                                        $m->created_at?->format('d-m-Y H:i'),
                                        $m->barang?->nama,
                                        $m->jenis === 'masuk' ? 'masuk' : 'keluar',
                                        $m->jumlah,
                                        $m->keterangan,
                                        $m->laporan?->no_tiket,
                                        $m->user?->name,
                                    ])) }}">
                                    <td class="px-5 py-3 whitespace-nowrap text-on-surface-variant">{{ $m->created_at?->format('d-m-Y H:i') }}</td>
                                    <td class="px-5 py-3 font-medium">{{ $m->barang?->nama ?? '-' }}</td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $m->jenis === 'masuk' ? 'bg-success-container text-on-success-container' : 'bg-warning-container text-on-warning-container' }}">
                                            {{ $m->jenis === 'masuk' ? 'Masuk' : 'Keluar' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-center font-semibold">{{ $m->jenis === 'masuk' ? '+' : '-' }}{{ number_format($m->jumlah) }}</td>
                                    <td class="px-5 py-3 text-center text-on-surface-variant">{{ number_format($m->saldo_setelah) }}</td>
                                    <td class="px-5 py-3 text-on-surface-variant">
                                        {{ $m->keterangan ?: '-' }}
                                        @if ($m->laporan)
                                            <a href="{{ route('sigap.show', $m->laporan) }}" class="text-brand hover:underline">({{ $m->laporan->no_tiket }})</a>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-on-surface-variant">{{ $m->user?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-on-surface-variant">Belum ada riwayat stok.</td>
                                </tr>
                            @endforelse

                            {{-- Muncul jika pencarian tidak cocok dengan baris mana pun --}}
                            @if (count($mutasiTerbaru) > 0)
                                <tr x-show="jumlahCocok() === 0" x-cloak>
                                    <td colspan="7" class="px-5 py-10 text-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-[36px] opacity-30 block mb-2">search_off</span>
                                        Tidak ada riwayat yang cocok dengan "<span x-text="riwayatSearch" class="font-semibold"></span>".
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===== Modal tambah stok ===== --}}
        @if ($bolehKelola)
            <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div @click.outside="modal = false" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="bg-white w-full max-w-md rounded-xl shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                        <h3 class="text-base font-semibold text-on-surface">Tambah Stok</h3>
                        <button type="button" @click="modal = false" class="text-on-surface-variant">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <form method="POST" :action="action" class="p-5 space-y-4">
                        @csrf
                        <p class="text-sm">Barang: <strong x-text="nama"></strong></p>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">
                                Jumlah Masuk (<span x-text="satuan"></span>) <span class="text-error">*</span>
                            </label>
                            <input type="number" min="1" name="jumlah" required class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">Keterangan</label>
                            <input type="text" name="keterangan" placeholder="Contoh: Pengadaan APBD 2026" class="{{ $input }}" />
                        </div>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" @click="modal = false"
                                class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Pencarian otomatis: kirim 0,5 detik setelah berhenti mengetik, tanpa tombol "Cari"
        (function() {
            const FOCUS_KEY = 'focus-search:' + location.pathname;

            document.querySelectorAll('form[data-auto-filter]').forEach(function(form) {
                const searchInput = form.querySelector('input[name="search"]');
                if (!searchInput) return;

                let timer = null;

                function submitForm() {
                    if (document.activeElement === searchInput) {
                        sessionStorage.setItem(FOCUS_KEY, '1');
                    }
                    form.submit();
                }

                searchInput.addEventListener('input', function() {
                    clearTimeout(timer);
                    timer = setTimeout(submitForm, 500);
                });

                searchInput.addEventListener('keydown', function(e) {
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
            });
        })();
    </script>
@endpush
