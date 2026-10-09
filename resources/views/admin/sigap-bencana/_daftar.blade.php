{{-- Partial daftar laporan SIGAP Bencana.
     Variabel dari controller : $list, $statusLabels, $jenisBencana, $bolehMembuat
     Variabel dari halaman    : $rutaDaftar (nama rute), $tampilGiliran (bool), $prosesMode (bool) --}}
@php
    $badge = fn($s) => match ($s) {
        'selesai' => 'bg-success-container text-on-success-container',
        'ditolak' => 'bg-error-container text-on-error-container',
        'bast' => 'bg-warning-container text-on-warning-container',
        default => 'bg-secondary-fixed text-on-secondary-container',
    };
@endphp

<div class="space-y-6">

    @if ($errors->any())
        <div class="flex flex-col gap-1 px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700">
            @foreach ($errors->all() as $error)
                <span>{{ $error }}</span>
            @endforeach
        </div>
    @endif

    {{-- ===== Filter ===== --}}
    <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] p-5">
        <form method="GET" action="{{ route($rutaDaftar) }}" data-auto-filter>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Kejadian Dari</label>
                    <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}"
                        class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Kejadian Sampai</label>
                    <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                        class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Jenis Bencana</label>
                    <select name="jenis_bencana"
                        class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors">
                        <option value="">Semua Jenis</option>
                        @foreach ($jenisBencana as $jenis)
                            <option value="{{ $jenis }}" @selected(request('jenis_bencana') === $jenis)>{{ $jenis }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Tahap Saat Ini</label>
                    <select name="status"
                        class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors">
                        <option value="">Semua Tahap</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-on-surface-variant mb-1.5">Cari Data</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="No tiket, jenis, kelurahan, alamat..."
                            class="w-full pl-8 pr-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-xs focus:ring-2 focus:ring-brand/30 focus:border-brand focus:outline-none transition-colors" />
                        <span
                            class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-outline-variant/80">search</span>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-outline-variant/20 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route($rutaDaftar) }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-error hover:bg-error/10 border border-transparent hover:border-error/20 transition-all">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        Reset Filter
                    </a>

                    @if ($tampilGiliran)
                        <label class="flex items-center gap-2 text-xs text-on-surface-variant cursor-pointer select-none">
                            <input type="checkbox" name="giliran_saya" value="1" @checked(request()->boolean('giliran_saya'))
                                class="rounded border-outline-variant/60 text-brand focus:ring-brand/30" />
                            Hanya yang menunggu giliran saya
                        </label>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                        Tampilkan
                        <select name="tampilkan"
                            class="rounded-xl border border-outline-variant/40 bg-slate-50 px-2 py-1.5 text-xs focus:outline-none">
                            @foreach ([10, 25, 50, 100] as $n)
                                <option value="{{ $n }}" @selected((int) request('tampilkan', 10) === $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                        data
                    </div>

                    @if ($bolehMembuat)
                        <a href="{{ route('sigap.create') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand text-white hover:bg-primary text-xs font-bold shadow-sm transition-colors">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            Buat Laporan
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ===== Tabel ===== --}}
    <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs min-w-[1000px]">
                <thead class="bg-[#e6edf8] text-on-surface-variant uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="text-center px-3 py-3 whitespace-nowrap">No</th>
                        <th class="text-center px-3 py-3 whitespace-nowrap">Tgl Kejadian</th>
                        <th class="text-left px-3 py-3 whitespace-nowrap">No Tiket</th>
                        <th class="text-left px-3 py-3 whitespace-nowrap">Jenis Bencana</th>
                        <th class="text-left px-3 py-3 whitespace-nowrap">Lokasi</th>
                        <th class="text-center px-3 py-3 whitespace-nowrap">Korban</th>
                        <th class="text-center px-3 py-3 whitespace-nowrap">Tahap Saat Ini</th>
                        <th class="text-center px-3 py-3 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse ($list as $item)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-3 py-3.5 text-center text-on-surface-variant">{{ $list->firstItem() + $loop->index }}</td>
                            <td class="px-3 py-3.5 text-center whitespace-nowrap text-on-surface-variant">
                                {{ $item->tanggal_kejadian?->format('d-m-Y H:i') ?? '-' }}</td>
                            <td class="px-3 py-3.5 whitespace-nowrap font-semibold text-brand">{{ $item->no_tiket }}</td>
                            <td class="px-3 py-3.5 font-medium">{{ $item->jenis_bencana }}</td>
                            <td class="px-3 py-3.5 text-on-surface-variant">
                                <span class="block font-medium text-on-surface">Kel. {{ $item->kelurahan }}</span>
                                <span class="block text-[11px]">Kec. {{ $item->kecamatan }}</span>
                            </td>
                            <td class="px-3 py-3.5 text-center">{{ number_format($item->jumlah_korban) }}</td>
                            <td class="px-3 py-3.5 text-center">
                                <span
                                    class="inline-flex items-center gap-1.5 max-w-full px-2.5 py-1 rounded-full text-[11px] font-medium {{ $badge($item->status) }}">
                                    <span class="material-symbols-outlined text-[14px]">
                                        {{ $item->status === 'selesai' ? 'check_circle' : ($item->status === 'ditolak' ? 'cancel' : 'schedule') }}
                                    </span>
                                    <span class="truncate">{{ $statusLabels[$item->status] ?? $item->status }}</span>
                                </span>
                            </td>
                            <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                @if ($prosesMode && $item->status === 'bast')
                                    <a href="{{ route('sigap.show', $item) }}"
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-warning text-white hover:opacity-90 text-[11px] font-semibold transition-all">
                                        <span class="material-symbols-outlined text-[14px]">edit_document</span>
                                        Isi BAST
                                    </a>
                                @else
                                    <a href="{{ route('sigap.show', $item) }}"
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand/10 text-brand hover:bg-brand hover:text-white text-[11px] font-semibold transition-all duration-200">
                                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                                        Lihat
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-on-surface-variant">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-[40px] mb-3 opacity-30">inbox</span>
                                    <p class="text-sm font-medium">Belum ada laporan bencana</p>
                                    <p class="text-xs opacity-70 mt-1">Sesuaikan filter pencarian, atau buat laporan baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($list->hasPages())
            <div class="px-5 py-4 border-t border-outline-variant/20 bg-white">
                {{ $list->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <script>
        // Filter & pencarian otomatis tanpa tombol "Terapkan"
        (function() {
            const FOCUS_KEY = 'focus-search:' + location.pathname;

            document.querySelectorAll('form[data-auto-filter]').forEach(function(form) {
                const searchInput = form.querySelector('input[name="search"]');
                let timer = null;

                function submitForm() {
                    if (searchInput && document.activeElement === searchInput) {
                        sessionStorage.setItem(FOCUS_KEY, '1');
                    }
                    form.submit();
                }

                form.querySelectorAll('select, input[type="checkbox"]').forEach(function(el) {
                    el.addEventListener('change', submitForm);
                });

                form.querySelectorAll('input[type="date"]').forEach(function(el) {
                    el.addEventListener('change', function() {
                        const v = el.value;
                        if (v === '' || (v.length === 10 && parseInt(v.slice(0, 4), 10) >= 1900)) {
                            submitForm();
                        }
                    });
                });

                if (searchInput) {
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
