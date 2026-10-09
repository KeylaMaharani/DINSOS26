@extends('layouts.admin')

@section('title', 'Detail Laporan ' . $data->no_tiket . ' - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'SIGAP Bencana — Detail Laporan')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@php
    $S = \App\Models\SigapLaporan::class;
    $user = auth()->user();
    $isKelurahan = ($user->role?->normalizedSlug() ?? null) === 'kelurahan';
    $backRoute = $isKelurahan ? route('sigap.proses.index') : route('sigap.index');

    $card = 'bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden';
    $input = 'w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand';

    $statusColor = match ($data->status) {
        $S::STATUS_SELESAI => 'bg-success-container text-on-success-container',
        $S::STATUS_DITOLAK => 'bg-error-container text-on-error-container',
        $S::STATUS_BAST => 'bg-warning-container text-on-warning-container',
        default => 'bg-secondary-fixed text-on-secondary-container',
    };

    $lat = $data->latitude !== null ? (float) $data->latitude : null;
    $lng = $data->longitude !== null ? (float) $data->longitude : null;

    // Teks petunjuk tugas pada tiap tahap
    $petunjuk = [
        $S::STATUS_DILAPORKAN => 'Lakukan assessment di lokasi, isi hasilnya, lalu tentukan barang bantuan yang dibutuhkan. Data ini menjadi isi Nota Dinas.',
        $S::STATUS_VERIFIKASI_PERLINSOS => 'Verifikasi hasil assessment, lalu tetapkan jumlah barang yang disetujui. Data ini menjadi isi Form Permohonan Barang.',
        $S::STATUS_PROSES_BARANG => 'Periksa ketersediaan stok untuk barang yang disetujui. Jika cukup, teruskan agar Kepala Dinas dapat menandatangani Surat Pengeluaran Barang.',
        $S::STATUS_TTD_KADIS => 'Dengan menekan "Setujui & Keluarkan Barang", stok barang akan berkurang otomatis dan tidak dapat dibatalkan.',
        $S::STATUS_PENGIRIMAN => 'Pastikan bantuan sudah dikirim ke lokasi, lalu teruskan agar Kelurahan/OPD wilayah dapat membuat BAST.',
        $S::STATUS_BAST => 'Isi Berita Acara Serah Terima dan unggah foto dokumentasi penyerahan bantuan.',
        $S::STATUS_PENYELESAIAN => 'Periksa BAST dan dokumentasi. Jika sudah lengkap, tutup tiket laporan ini.',
    ];

    $labelLanjut = match ($data->status) {
        $S::STATUS_DILAPORKAN => 'Simpan Assessment & Teruskan',
        $S::STATUS_VERIFIKASI_PERLINSOS => 'Verifikasi & Teruskan ke Pengurus Barang',
        $S::STATUS_PROSES_BARANG => 'Stok Cukup, Teruskan ke Kadis',
        $S::STATUS_TTD_KADIS => 'Setujui & Keluarkan Barang',
        $S::STATUS_PENGIRIMAN => 'Bantuan Dikirim, Teruskan ke BAST',
        $S::STATUS_BAST => 'Kirim BAST & Dokumentasi',
        $S::STATUS_PENYELESAIAN => 'Tutup Tiket (Selesai)',
        default => 'Teruskan',
    };
@endphp

@section('content')
    <div class="space-y-5 max-w-5xl">

        <a href="{{ $backRoute }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-brand">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar laporan
        </a>

        @if ($errors->any())
            <div class="flex flex-col gap-1 px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        {{-- ===== Header ringkas ===== --}}
        <div class="{{ $card }} p-5 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <p class="text-xs text-on-surface-variant">No Tiket</p>
                <h2 class="text-lg font-extrabold text-brand">{{ $data->no_tiket }}</h2>
                <p class="text-sm text-on-surface-variant mt-1">
                    {{ $data->jenis_bencana }} — Kel. {{ $data->kelurahan }}, Kec. {{ $data->kecamatan }}
                </p>
                @if ($data->nomor_surat_laporan)
                    <p class="text-[11px] text-on-surface-variant mt-0.5">No. Surat Laporan: {{ $data->nomor_surat_laporan }}</p>
                @endif
            </div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $statusColor }}">
                {{ $statusLabels[$data->status] ?? $data->status }}
            </span>
        </div>

        {{-- ===== 1. Data laporan ===== --}}
        <div class="{{ $card }}">
            <div class="px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-brand">crisis_alert</span>
                    Laporan Kejadian Bencana
                </h3>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 p-5 text-sm">
                <div>
                    <dt class="text-xs text-on-surface-variant">Jenis Bencana</dt>
                    <dd class="font-medium">{{ $data->jenis_bencana }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-on-surface-variant">Tanggal & Waktu Kejadian</dt>
                    <dd class="font-medium">{{ $data->tanggal_kejadian?->format('d-m-Y H:i') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-on-surface-variant">Jumlah Korban</dt>
                    <dd class="font-medium">{{ number_format($data->jumlah_korban) }} jiwa</dd>
                </div>
                <div>
                    <dt class="text-xs text-on-surface-variant">No. Kartu Keluarga</dt>
                    <dd class="font-medium">{{ $data->no_kk ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-on-surface-variant">Kecamatan / Kelurahan</dt>
                    <dd class="font-medium">{{ $data->kecamatan }} / {{ $data->kelurahan }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-on-surface-variant">RT / RW</dt>
                    <dd class="font-medium">{{ $data->rt ?: '-' }} / {{ $data->rw ?: '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-on-surface-variant">Alamat</dt>
                    <dd class="font-medium">{{ $data->alamat }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-on-surface-variant">Kerusakan</dt>
                    <dd class="font-medium whitespace-pre-line">{{ $data->kerusakan ?: '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-on-surface-variant">Kerugian</dt>
                    <dd class="font-medium whitespace-pre-line">{{ $data->kerugian ?: '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-on-surface-variant">Bantuan yang Dibutuhkan</dt>
                    <dd class="font-medium whitespace-pre-line">{{ $data->bantuan_dibutuhkan }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-on-surface-variant">Pelapor</dt>
                    <dd class="font-medium">{{ $data->nama_pelapor ?: '-' }}
                        <span class="text-on-surface-variant font-normal">({{ ucfirst($data->peran_pelapor ?? '-') }})</span>
                    </dd>
                </div>
            </dl>

            @if ($lat !== null && $lng !== null)
                <div class="px-5 pb-5">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <p class="text-xs text-on-surface-variant">
                            Koordinat: {{ number_format($lat, 6) }}, {{ number_format($lng, 6) }}
                            @if ($data->lokasi_diperbarui_at)
                                <span class="text-success font-medium">· dikonfirmasi Tagana di lokasi
                                    {{ $data->lokasi_diperbarui_at->format('d-m-Y H:i') }}</span>
                            @endif
                        </p>
                        @if ($bolehEditLokasi)
                            <button type="button" id="btn-tiba"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand text-white hover:bg-primary text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[16px]">my_location</span>
                                Saya Sudah Tiba di Lokasi (Ambil GPS)
                            </button>
                        @endif
                    </div>
                    <div id="map-detail" class="w-full h-72 rounded-2xl border border-outline-variant/40 z-0"></div>
                    <p id="gps-status" class="text-[11px] text-on-surface-variant mt-2"></p>
                </div>
            @endif
        </div>

        {{-- ===== 2. Barang bantuan ===== --}}
        @if ($data->items->isNotEmpty())
            <div class="{{ $card }}">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-brand">inventory_2</span>
                        Barang Bantuan
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-[#e6edf8] text-on-surface-variant text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 font-medium">Barang</th>
                                <th class="px-5 py-3 font-medium text-center">Dibutuhkan</th>
                                <th class="px-5 py-3 font-medium text-center">Disetujui</th>
                                <th class="px-5 py-3 font-medium text-center">Keluar</th>
                                <th class="px-5 py-3 font-medium text-center">Stok Saat Ini</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @foreach ($data->items as $item)
                                <tr>
                                    <td class="px-5 py-3 font-medium">{{ $item->nama_barang }}</td>
                                    <td class="px-5 py-3 text-center">{{ $item->jumlah_dibutuhkan }} {{ $item->satuan }}</td>
                                    <td class="px-5 py-3 text-center">
                                        {{ $item->jumlah_disetujui !== null ? $item->jumlah_disetujui . ' ' . $item->satuan : '-' }}</td>
                                    <td class="px-5 py-3 text-center">
                                        {{ $item->jumlah_keluar !== null ? $item->jumlah_keluar . ' ' . $item->satuan : '-' }}</td>
                                    <td class="px-5 py-3 text-center text-on-surface-variant">
                                        {{ $stokMap[$item->barang_id] ?? 0 }} {{ $item->satuan }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ===== 3. Dokumen hasil tiap tahap ===== --}}
        @if ($data->assessment || $data->permohonanBarang || $data->pengeluaran || $data->bast)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                @if ($data->assessment)
                    @php $a = $data->assessment; @endphp
                    <div class="{{ $card }}">
                        <div class="px-5 py-3 border-b border-outline-variant/40 bg-[#eef4fd]">
                            <h3 class="text-sm font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-brand">description</span>
                                Nota Dinas (Assessment Tagana)
                            </h3>
                        </div>
                        <dl class="p-5 space-y-2 text-sm">
                            <div><dt class="text-xs text-on-surface-variant">Nomor</dt><dd class="font-medium">{{ $a->nomor_nota_dinas ?? '-' }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Petugas Assessment</dt><dd class="font-medium">{{ $a->nama_petugas }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Tanggal Assessment</dt><dd class="font-medium">{{ $a->tanggal_assessment?->format('d-m-Y') }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Hasil Assessment</dt><dd class="font-medium whitespace-pre-line">{{ $a->hasil_assessment }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Tembusan</dt><dd class="font-medium">{{ $a->tembusan }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Disetujui oleh Tagana</dt><dd class="font-medium">{{ $a->ttd_tagana_at?->format('d-m-Y H:i') ?? '-' }}</dd></div>
                        </dl>
                    </div>
                @endif

                @if ($data->permohonanBarang)
                    @php $p = $data->permohonanBarang; @endphp
                    <div class="{{ $card }}">
                        <div class="px-5 py-3 border-b border-outline-variant/40 bg-[#eef4fd]">
                            <h3 class="text-sm font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-brand">fact_check</span>
                                Permohonan Barang (Verifikasi Perlinsos)
                            </h3>
                        </div>
                        <dl class="p-5 space-y-2 text-sm">
                            <div><dt class="text-xs text-on-surface-variant">Nomor</dt><dd class="font-medium">{{ $p->nomor_surat ?? '-' }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Tanggal</dt><dd class="font-medium">{{ $p->tanggal?->format('d-m-Y') }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Verifikator</dt><dd class="font-medium">{{ $p->nama_verifikator }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Hasil Verifikasi</dt><dd class="font-medium whitespace-pre-line">{{ $p->hasil_verifikasi }}</dd></div>
                        </dl>
                    </div>
                @endif

                @if ($data->pengeluaran)
                    @php $k = $data->pengeluaran; @endphp
                    <div class="{{ $card }}">
                        <div class="px-5 py-3 border-b border-outline-variant/40 bg-[#eef4fd]">
                            <h3 class="text-sm font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-brand">local_shipping</span>
                                Surat Pengeluaran Barang
                            </h3>
                        </div>
                        <dl class="p-5 space-y-2 text-sm">
                            <div><dt class="text-xs text-on-surface-variant">Nomor</dt><dd class="font-medium">{{ $k->nomor_surat ?? '-' }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Pengurus Barang</dt><dd class="font-medium">{{ $k->nama_pengurus_barang }}
                                <span class="text-on-surface-variant font-normal">· {{ $k->ttd_pengurus_at?->format('d-m-Y H:i') ?? '-' }}</span></dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Kepala Dinas</dt><dd class="font-medium">
                                @if ($k->ttd_kadis_at)
                                    {{ $k->nama_kadis }} <span class="text-on-surface-variant font-normal">· {{ $k->ttd_kadis_at->format('d-m-Y H:i') }}</span>
                                @else
                                    <span class="text-on-surface-variant font-normal">Belum menandatangani</span>
                                @endif
                            </dd></div>
                        </dl>
                    </div>
                @endif

                @if ($data->bast)
                    @php $b = $data->bast; @endphp
                    <div class="{{ $card }}">
                        <div class="px-5 py-3 border-b border-outline-variant/40 bg-[#eef4fd]">
                            <h3 class="text-sm font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-brand">handshake</span>
                                Berita Acara Serah Terima (BAST)
                            </h3>
                        </div>
                        <dl class="p-5 space-y-2 text-sm">
                            <div><dt class="text-xs text-on-surface-variant">Nomor</dt><dd class="font-medium">{{ $b->nomor_bast ?? '-' }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Tanggal Diterima</dt><dd class="font-medium">{{ $b->tanggal_terima?->format('d-m-Y') }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Penerima</dt><dd class="font-medium">{{ $b->nama_penerima }} — {{ $b->jabatan_penerima }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Instansi</dt><dd class="font-medium">{{ $b->instansi_penerima ?: '-' }}</dd></div>
                            <div><dt class="text-xs text-on-surface-variant">Catatan</dt><dd class="font-medium whitespace-pre-line">{{ $b->catatan ?: '-' }}</dd></div>
                        </dl>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===== 4. Dokumentasi foto ===== --}}
        @if ($data->dokumentasi->isNotEmpty())
            <div class="{{ $card }}">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-brand">photo_library</span>
                        Dokumentasi Penerimaan
                    </h3>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
                    @foreach ($data->dokumentasi as $foto)
                        <a href="{{ asset('storage/' . $foto->path) }}" target="_blank"
                            class="block border border-outline-variant/40 rounded-lg overflow-hidden hover:border-brand transition-all">
                            <div class="aspect-square bg-surface-container/50">
                                <img src="{{ asset('storage/' . $foto->path) }}" alt="Dokumentasi"
                                    class="w-full h-full object-cover" />
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===== 5. Log proses ===== --}}
        <div class="{{ $card }}">
            <div class="px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-brand">history</span>
                    Log Proses
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#e6edf8] text-on-surface-variant text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 font-medium">Tanggal Proses</th>
                            <th class="px-5 py-3 font-medium">Username</th>
                            <th class="px-5 py-3 font-medium">Role</th>
                            <th class="px-5 py-3 font-medium">Aktivitas</th>
                            <th class="px-5 py-3 font-medium">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        @forelse ($data->logs as $log)
                            <tr class="hover:bg-white/50 transition-colors">
                                <td class="px-5 py-3 whitespace-nowrap text-on-surface-variant">
                                    {{ $log->tanggal_proses?->format('d M Y - H:i') }}</td>
                                <td class="px-5 py-3 font-medium text-on-surface">{{ $log->username }}</td>
                                <td class="px-5 py-3"><span class="bg-surface-container px-2 py-1 rounded text-xs">{{ $log->rolename }}</span></td>
                                <td class="px-5 py-3 font-medium">{{ $log->taskname }}</td>
                                <td class="px-5 py-3 text-on-surface-variant">{{ $log->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-on-surface-variant">Belum ada log proses.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== 6. Proses tahap ===== --}}
        <div class="{{ $card }} mb-10">
            <div class="px-5 py-4 border-b border-outline-variant/40 bg-[#eef4fd]">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-brand">alt_route</span>
                    Proses Laporan
                </h3>
            </div>

            @if ($isFinal)
                <div class="p-6 text-center bg-white">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full {{ $data->status === $S::STATUS_SELESAI ? 'bg-success/20 text-success' : 'bg-error/20 text-error' }} mb-3">
                        <span class="material-symbols-outlined text-[24px]">{{ $data->status === $S::STATUS_SELESAI ? 'check_circle' : 'cancel' }}</span>
                    </div>
                    <h4 class="text-lg font-bold mb-1">Laporan Final</h4>
                    <p class="text-sm text-on-surface-variant">
                        Laporan ini sudah final dengan status <strong>{{ $statusLabels[$data->status] ?? $data->status }}</strong>.
                        Tidak ada aksi lanjutan.
                    </p>
                </div>
            @elseif (!$canAct)
                <div class="p-6 text-center bg-white">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-warning/20 text-warning mb-3">
                        <span class="material-symbols-outlined text-[24px]">hourglass_empty</span>
                    </div>
                    <h4 class="text-lg font-bold mb-1">Menunggu Tahap Selanjutnya</h4>
                    <p class="text-sm text-on-surface-variant">
                        Laporan ini sedang berada di tahap <strong>{{ $statusLabels[$data->status] ?? $data->status }}</strong>
                        @if ($data->currentRole)
                            (giliran <strong>{{ $data->currentRole->name }}</strong>)
                        @endif.<br>
                        Anda tidak berwenang memproses pada tahap ini.
                    </p>
                </div>
            @else
                <form method="POST" action="{{ route('sigap.aksi', $data) }}" enctype="multipart/form-data"
                    class="p-5 space-y-5">
                    @csrf

                    @if (isset($petunjuk[$data->status]))
                        <div class="flex items-start gap-2 px-4 py-3 rounded-xl bg-brand-soft text-sm text-primary">
                            <span class="material-symbols-outlined text-[20px] shrink-0">info</span>
                            <span>{{ $petunjuk[$data->status] }}</span>
                        </div>
                    @endif

                    {{-- ======== Tahap: Dilaporkan -> Assessment Tagana ======== --}}
                    @if ($data->status === $S::STATUS_DILAPORKAN)
                        @php
                            $oldItems = old('items');
                            if (is_array($oldItems) && count($oldItems)) {
                                $rowsAwal = array_values(array_map(fn($r) => [
                                    'barang_id' => (string) ($r['barang_id'] ?? ''),
                                    'jumlah' => (int) ($r['jumlah'] ?? 1),
                                ], $oldItems));
                            } elseif ($data->items->count()) {
                                $rowsAwal = $data->items->map(fn($i) => [
                                    'barang_id' => (string) $i->barang_id,
                                    'jumlah' => $i->jumlah_dibutuhkan,
                                ])->values()->all();
                            } else {
                                $rowsAwal = [['barang_id' => '', 'jumlah' => 1]];
                            }
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <label class="block text-xs text-on-surface-variant mb-1">Nama Petugas Assessment <span class="text-error">*</span></label>
                                <input type="text" name="nama_petugas"
                                    value="{{ old('nama_petugas', $data->assessment->nama_petugas ?? $user->name) }}"
                                    placeholder="Boleh lebih dari satu nama" class="{{ $input }}" />
                            </div>
                            <div>
                                <label class="block text-xs text-on-surface-variant mb-1">Tanggal Assessment <span class="text-error">*</span></label>
                                <input type="date" name="tanggal_assessment" max="{{ now()->toDateString() }}"
                                    value="{{ old('tanggal_assessment', optional($data->assessment?->tanggal_assessment)->format('Y-m-d') ?? now()->toDateString()) }}"
                                    class="{{ $input }}" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-on-surface-variant mb-1">Hasil Assessment <span class="text-error">*</span></label>
                                <textarea name="hasil_assessment" rows="4" class="{{ $input }}"
                                    placeholder="Kondisi di lokasi berdasarkan hasil assessment">{{ old('hasil_assessment', $data->assessment->hasil_assessment ?? '') }}</textarea>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-on-surface mb-2">Barang yang Dibutuhkan <span class="text-error">*</span></p>

                            @if ($barangList->isEmpty())
                                <div class="px-4 py-3 rounded-xl bg-warning-container text-on-warning-container text-sm">
                                    Master barang masih kosong. Minta Pengurus Barang menambahkan barang di menu <strong>Stok Barang</strong>.
                                </div>
                            @else
                                <div x-data="{
                                    rows: @js($rowsAwal),
                                    tambah() { this.rows.push({ barang_id: '', jumlah: 1 }); },
                                    hapus(i) { if (this.rows.length > 1) this.rows.splice(i, 1); }
                                }" class="space-y-2">
                                    <template x-for="(row, i) in rows" :key="i">
                                        <div class="grid grid-cols-12 gap-2 items-center">
                                            <select :name="'items[' + i + '][barang_id]'" x-model="row.barang_id"
                                                class="col-span-12 sm:col-span-7 {{ $input }}">
                                                <option value="">-Pilih barang-</option>
                                                @foreach ($barangList as $brg)
                                                    <option value="{{ $brg->id }}">{{ $brg->nama }} ({{ $brg->satuan }}) — stok {{ $brg->stok }}</option>
                                                @endforeach
                                            </select>
                                            <input type="number" min="1" :name="'items[' + i + '][jumlah]'"
                                                x-model="row.jumlah" placeholder="Jumlah"
                                                class="col-span-8 sm:col-span-3 {{ $input }}" />
                                            <button type="button" @click="hapus(i)" title="Hapus baris"
                                                class="col-span-4 sm:col-span-2 inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl border border-error/40 text-error hover:bg-error/10 text-xs font-medium transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">delete</span> Hapus
                                            </button>
                                        </div>
                                    </template>
                                    <button type="button" @click="tambah()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand/10 text-brand hover:bg-brand hover:text-white text-xs font-semibold transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">add</span> Tambah Barang
                                    </button>
                                </div>
                            @endif
                        </div>

                        <p class="text-[11px] text-on-surface-variant">Tembusan Nota Dinas: Kepala Bidang Perlinsos.</p>
                    @endif

                    {{-- ======== Tahap: Verifikasi Perlinsos ======== --}}
                    @if ($data->status === $S::STATUS_VERIFIKASI_PERLINSOS)
                        <div class="overflow-x-auto border border-outline-variant/30 rounded-xl">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-[#e6edf8] text-on-surface-variant text-xs uppercase tracking-wider">
                                    <tr>
                                        <th class="px-4 py-2.5 font-medium">Barang</th>
                                        <th class="px-4 py-2.5 font-medium text-center">Dibutuhkan</th>
                                        <th class="px-4 py-2.5 font-medium text-center">Stok Saat Ini</th>
                                        <th class="px-4 py-2.5 font-medium text-center w-40">Disetujui <span class="text-error">*</span></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant/30">
                                    @foreach ($data->items as $item)
                                        <tr>
                                            <td class="px-4 py-2.5 font-medium">{{ $item->nama_barang }}</td>
                                            <td class="px-4 py-2.5 text-center">{{ $item->jumlah_dibutuhkan }} {{ $item->satuan }}</td>
                                            <td class="px-4 py-2.5 text-center text-on-surface-variant">{{ $stokMap[$item->barang_id] ?? 0 }} {{ $item->satuan }}</td>
                                            <td class="px-4 py-2.5">
                                                <input type="number" min="0" name="jumlah_disetujui[{{ $item->id }}]"
                                                    value="{{ old('jumlah_disetujui.' . $item->id, $item->jumlah_disetujui ?? $item->jumlah_dibutuhkan) }}"
                                                    class="{{ $input }} text-center" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">Hasil Verifikasi <span class="text-error">*</span></label>
                            <textarea name="hasil_verifikasi" rows="3" class="{{ $input }}"
                                placeholder="Hasil verifikasi terhadap laporan dan assessment Tagana">{{ old('hasil_verifikasi', $data->permohonanBarang->hasil_verifikasi ?? '') }}</textarea>
                        </div>
                    @endif

                    {{-- ======== Tahap: Proses Pengurus Barang & TTD Kadis: tabel cek stok ======== --}}
                    @if (in_array($data->status, [$S::STATUS_PROSES_BARANG, $S::STATUS_TTD_KADIS], true))
                        @php $adaKurang = false; @endphp
                        <div class="overflow-x-auto border border-outline-variant/30 rounded-xl">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-[#e6edf8] text-on-surface-variant text-xs uppercase tracking-wider">
                                    <tr>
                                        <th class="px-4 py-2.5 font-medium">Barang</th>
                                        <th class="px-4 py-2.5 font-medium text-center">Disetujui</th>
                                        <th class="px-4 py-2.5 font-medium text-center">Stok Saat Ini</th>
                                        <th class="px-4 py-2.5 font-medium text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant/30">
                                    @foreach ($data->items->where('jumlah_disetujui', '>', 0) as $item)
                                        @php
                                            $stok = $stokMap[$item->barang_id] ?? 0;
                                            $cukup = $stok >= $item->jumlah_disetujui;
                                            $adaKurang = $adaKurang || !$cukup;
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2.5 font-medium">{{ $item->nama_barang }}</td>
                                            <td class="px-4 py-2.5 text-center">{{ $item->jumlah_disetujui }} {{ $item->satuan }}</td>
                                            <td class="px-4 py-2.5 text-center">{{ $stok }} {{ $item->satuan }}</td>
                                            <td class="px-4 py-2.5 text-center">
                                                <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $cukup ? 'bg-success-container text-on-success-container' : 'bg-error-container text-on-error-container' }}">
                                                    {{ $cukup ? 'Stok Cukup' : 'Stok Kurang' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($adaKurang)
                            <div class="px-4 py-3 rounded-xl bg-error-container text-on-error-container text-sm">
                                Ada barang yang stoknya kurang. Tambahkan stok di menu <strong>Stok Barang</strong>, atau
                                kembalikan ke Perlinsos untuk menyesuaikan jumlah yang disetujui.
                            </div>
                        @endif
                    @endif

                    {{-- ======== Tahap: BAST ======== --}}
                    @if ($data->status === $S::STATUS_BAST)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <label class="block text-xs text-on-surface-variant mb-1">Tanggal Bantuan Diterima <span class="text-error">*</span></label>
                                <input type="date" name="tanggal_terima" max="{{ now()->toDateString() }}"
                                    value="{{ old('tanggal_terima', optional($data->bast?->tanggal_terima)->format('Y-m-d') ?? now()->toDateString()) }}"
                                    class="{{ $input }}" />
                            </div>
                            <div>
                                <label class="block text-xs text-on-surface-variant mb-1">Nama Penerima <span class="text-error">*</span></label>
                                <input type="text" name="nama_penerima" value="{{ old('nama_penerima', $data->bast->nama_penerima ?? $user->name) }}"
                                    class="{{ $input }}" />
                            </div>
                            <div>
                                <label class="block text-xs text-on-surface-variant mb-1">Jabatan Penerima <span class="text-error">*</span></label>
                                <input type="text" name="jabatan_penerima" value="{{ old('jabatan_penerima', $data->bast->jabatan_penerima ?? '') }}"
                                    placeholder="Contoh: Lurah / Sekretaris Kelurahan" class="{{ $input }}" />
                            </div>
                            <div>
                                <label class="block text-xs text-on-surface-variant mb-1">Catatan</label>
                                <input type="text" name="catatan_bast" value="{{ old('catatan_bast', $data->bast->catatan ?? '') }}"
                                    class="{{ $input }}" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-on-surface-variant mb-1">
                                    Foto Dokumentasi Penerimaan
                                    @if ($data->dokumentasi->isEmpty())
                                        <span class="text-error">*</span>
                                    @endif
                                    <span class="opacity-70">(JPG/PNG, maks. 4 MB per foto, hingga 10 foto)</span>
                                </label>
                                <input type="file" name="dokumentasi[]" multiple accept=".jpg,.jpeg,.png"
                                    class="block w-full text-xs text-on-surface-variant file:mr-3 file:rounded-lg file:border-0 file:bg-brand/10 file:px-3 file:py-2 file:text-brand file:font-semibold" />
                            </div>
                        </div>
                    @endif

                    {{-- ======== Catatan & tombol aksi (semua tahap) ======== --}}
                    <div>
                        <label class="block text-sm font-bold text-on-surface mb-2">Catatan Proses <span class="text-error">*</span></label>
                        <textarea name="catatan" rows="3" required placeholder="Tulis catatan (wajib diisi untuk semua aksi)..."
                            class="w-full px-4 py-3 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand shadow-sm">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3">
                        @if ($returnLabel)
                            <button type="submit" name="action" value="kembalikan" data-confirm="{{ $returnLabel }}?"
                                data-confirm-variant="primary"
                                class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-outline-variant hover:bg-surface-container-highest text-on-surface text-sm font-bold transition-all">
                                <span class="material-symbols-outlined text-[18px]">undo</span>
                                {{ $returnLabel }}
                            </button>
                        @endif

                        <button type="submit" name="action" value="simpan_catatan"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg bg-surface-container hover:bg-secondary/20 text-brand text-sm font-bold transition-all">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Catatan Saja
                        </button>

                        <button type="submit" name="action" value="lanjut"
                            data-confirm="{{ $labelLanjut }}? Pastikan isian sudah benar."
                            data-confirm-variant="primary"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-xl bg-brand text-white hover:bg-primary text-sm font-bold shadow-md transition-all sm:ml-auto">
                            <span class="material-symbols-outlined text-[18px]">redo</span>
                            {{ $labelLanjut }}
                        </button>

                        @if ($canTolak)
                            <button type="submit" name="action" value="tolak"
                                data-confirm="Yakin ingin menolak laporan ini? Catatan alasan wajib diisi."
                                data-confirm-variant="error"
                                class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-sm font-bold transition-all">
                                <span class="material-symbols-outlined text-[18px]">cancel</span>
                                Tolak (Reject)
                            </button>
                        @endif
                    </div>

                    <p class="text-[11px] text-on-surface-variant italic">
                        Catatan wajib diisi untuk semua aksi (Teruskan, Kembalikan, Simpan, maupun Tolak).
                    </p>
                </form>
            @endif
        </div>
    </div>
@endsection

@if ($lat !== null && $lng !== null)
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            (function() {
                const lat = @json($lat);
                const lng = @json($lng);
                const bolehEdit = @json((bool) $bolehEditLokasi);
                const urlLokasi = @json(route('sigap.lokasi', $data));
                const csrf = @json(csrf_token());
                const statusEl = document.getElementById('gps-status');

                const map = L.map('map-detail').setView([lat, lng], 17);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                const marker = L.marker([lat, lng]).addTo(map);

                setTimeout(function() {
                    map.invalidateSize();
                }, 250);

                if (!bolehEdit) return;

                // Tagana: ambil koordinat GPS saat tiba di lokasi, lalu simpan otomatis
                document.getElementById('btn-tiba').addEventListener('click', function() {
                    if (!window.isSecureContext || !navigator.geolocation) {
                        statusEl.textContent = 'GPS hanya tersedia di alamat HTTPS pada perangkat yang mendukung.';
                        return;
                    }

                    statusEl.textContent = 'Mengambil lokasi perangkat...';

                    navigator.geolocation.getCurrentPosition(function(pos) {
                        const la = pos.coords.latitude;
                        const lo = pos.coords.longitude;

                        fetch(urlLokasi, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: JSON.stringify({
                                    latitude: la,
                                    longitude: lo
                                })
                            })
                            .then(function(r) {
                                if (!r.ok) throw new Error('gagal');
                                marker.setLatLng([la, lo]);
                                map.setView([la, lo], 17);
                                statusEl.textContent = 'Lokasi tersimpan. Memuat ulang...';
                                setTimeout(function() {
                                    location.reload();
                                }, 700);
                            })
                            .catch(function() {
                                statusEl.textContent = 'Gagal menyimpan lokasi. Coba lagi.';
                            });
                    }, function(err) {
                        statusEl.textContent = err.code === 1 ?
                            'Izin lokasi ditolak pada perangkat ini.' :
                            'Lokasi tidak dapat diambil.';
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000
                    });
                });
            })();
        </script>
    @endpush
@endif
