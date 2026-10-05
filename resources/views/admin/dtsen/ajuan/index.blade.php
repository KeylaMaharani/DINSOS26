@extends('layouts.admin')

@section('title', 'Ajuan DTSEN')
@section('page_title', 'DTSEN — Ajuan')

@section('content')
    <div class="space-y-5">

        @if (session('success'))
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs bg-green-50 border border-green-200 text-green-700">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('dtsen.ajuan') }}" data-auto-filter
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
                <label class="block text-xs text-on-surface-variant mb-1">Tahap Saat Ini</label>
                <select name="status" class="px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs">
                    <option value="">Semua</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-on-surface-variant mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, Alamat, Tanggal (dd-mm-yyyy)..."
                    class="w-full px-3 py-1.5 rounded-lg border border-outline-variant/50 text-xs" />
            </div>

            <div class="flex items-center gap-2 text-xs text-on-surface-variant ml-auto">
                Tampilkan
                <select name="tampilkan" onchange="this.form.submit()" form="ajuan-dtsen-tampilkan-form"
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
                    <col style="width: 14%">
                    <col style="width: 16%">
                    <col style="width: 7%">
                    <col style="width: 18%">
                    <col style="width: 17%">
                    <col style="width: 14%">
                </colgroup>
                <thead class="bg-surface-container text-on-surface-variant uppercase">
                    <tr>
                        <th class="text-center px-2 py-2 whitespace-nowrap">No</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Tgl Insert</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">NIK</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Nama</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Desil</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Alamat</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Tahap Saat Ini</th>
                        <th class="text-center px-2 py-2 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($ajuanList as $item)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-2 py-2 whitespace-nowrap truncate text-center">{{ $ajuanList->firstItem() + $loop->index }}</td>
                            <td class="px-2 py-2 whitespace-nowrap text-center">{{ $item->tanggal_insert->format('d-m-Y') }}</td>
                            <td class="px-2 py-2 whitespace-nowrap truncate font-medium text-primary text-center" title="{{ $item->nik }}">{{ $item->nik }}</td>
                            <td class="px-2 py-2 whitespace-nowrap truncate" title="{{ $item->nama_pemohon }}">{{ $item->nama_pemohon }}</td>
                            <td class="px-2 py-2 whitespace-nowrap truncate text-center">{{ $item->bansos->peringkat_kesejahteraan_keluarga ?? '-' }}</td>
                            <td class="px-2 py-2 whitespace-nowrap truncate text-on-surface-variant" title="{{ $item->alamat }}">{{ $item->alamat }}</td>
                            <td class="px-2 py-2 whitespace-nowrap text-center" title="{{ $statusLabels[$item->status] ?? $item->status }}">
                                <span class="inline-flex items-center gap-1 max-w-full px-1.5 py-0.5 rounded-full text-[11px] font-medium bg-secondary-fixed text-on-secondary-container">
                                    <span class="material-symbols-outlined text-[12px]">schedule</span>
                                    <span class="truncate">{{ $statusLabels[$item->status] ?? $item->status }}</span>
                                </span>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-center">
                                <a href="{{ route('dtsen.show', $item) }}"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">visibility</span>
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[28px] block mb-2 opacity-40">inbox</span>
                                Belum ada ajuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-outline-variant/40">
                {{ $ajuanList->links() }}
            </div>
        </div>

        <form id="ajuan-dtsen-tampilkan-form" method="GET" action="{{ route('dtsen.ajuan') }}" class="hidden">
            <input type="hidden" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
            <input type="hidden" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
        </form>
    </div>

    @push('scripts')
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
