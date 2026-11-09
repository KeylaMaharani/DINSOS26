@extends('layouts.admin')

@php
    $statusColor = match ($data->status) {
        'disetujui' => 'bg-success-container text-on-success-container',
        'ditolak' => 'bg-error-container text-on-error-container',
        default => 'bg-secondary-fixed text-on-secondary-container',
    };
@endphp

@section('title', 'Detail Ajuan PBI APBD - Operator Dinsos')
@section('page_title', 'PBI APBD — Operator Dinsos')

@section('content')
{{-- panel = lampiran KK yang sedang dibuka di samping Data Pemohon (null = tertutup) --}}
<div class="space-y-5 max-w-7xl" x-data="{ panel: null }" @lampiran-panel.window="panel = $event.detail">

    <a href="{{ route('pbi-approval.operator.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar
    </a>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <ul class="list-disc list-inside">@foreach (array_unique($errors->all()) as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-sm">
        <div>
            <p class="text-xs text-on-surface-variant uppercase tracking-wider font-medium">No. Registrasi</p>
            <h2 class="text-xl font-bold text-primary mt-0.5">{{ $data->no_registrasi }}</h2>
            <p class="text-sm text-on-surface-variant mt-1">{{ $data->nama_kepala_keluarga }} — NIK {{ $data->nik_kepala_keluarga }}</p>
        </div>
        <span class="inline-flex px-4 py-1.5 rounded-full text-sm font-semibold {{ $statusColor }}">{{ $data->statusLabel() }}</span>
    </div>

    {{-- Lampiran (di atas Data Pemohon). Scan KK dibuka di panel samping; lampiran lain di tab baru. --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">attach_file</span> Lampiran</h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 p-5 pb-16">
            @foreach ([
                'scan_ktp' => 'Scan KTP', 'scan_kk' => 'Scan Kartu Keluarga',
                'foto_rumah' => 'Foto Rumah', 'foto_kamar_mandi' => 'Foto Kamar Mandi',
                'foto_selfie_ktp' => 'Foto Selfie dengan KTP', 'surat_rawat_inap' => 'Surat Ket. Rawat Inap',
                'screenshot_pembaharuan_desil' => 'Screenshot Pembaharuan Desil', 'screenshot_dtsen' => 'Screenshot DTSEN',
                'scan_surat_pengantar' => 'Surat Pengantar Kelurahan',
                'ttd_kelurahan' => 'Tanda Tangan Lurah',
            ] as $field => $label)
                <x-lampiran-preview :src="$data->$field ? asset('storage/' . $data->$field) : null" :label="$label" :panel="$field === 'scan_kk'" />
            @endforeach
        </div>
    </div>

    {{-- Data Pemohon (kiri) + Scan KK (kanan, muncul saat "Lihat" pada Scan Kartu Keluarga diklik) --}}
    <div class="grid grid-cols-1 gap-5 items-start" :class="panel ? 'lg:grid-cols-2' : ''">

        <form method="POST" action="{{ route('pbi-approval.operator.update', $data) }}" class="min-w-0">
            @csrf @method('PUT')

            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">assignment</span> Data Pemohon</h3>
                    <p class="text-xs text-on-surface-variant mt-1">Data diisi masyarakat/kelurahan. Operator hanya menambahkan Desil Nasional.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 text-sm">
                    @foreach ([
                        'nama_kepala_keluarga' => 'Nama Kepala Keluarga', 'nik_kepala_keluarga' => 'NIK Kepala Keluarga',
                        'no_kk' => 'No KK', 'no_telp' => 'No Telp',
                        'alamat' => 'Alamat', 'rt' => 'RT', 'rw' => 'RW',
                        'kecamatan' => 'Kecamatan', 'desa_kelurahan' => 'Desa/Kelurahan',
                        'email' => 'Email', 'nama_faskes' => 'Nama Faskes',
                    ] as $f => $label)
                        <div class="{{ $f === 'alamat' ? 'sm:col-span-2' : '' }}">
                            <label class="block text-xs text-on-surface-variant mb-1">{{ $label }}</label>
                            <input type="text" value="{{ $data->$f }}" disabled
                                   class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-surface-container text-on-surface-variant cursor-not-allowed">
                        </div>
                    @endforeach

                    {{-- Satu-satunya isian yang bisa diubah Operator --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold mb-1">Desil Nasional</label>
                        <input type="text" name="desil_nasional" value="{{ old('desil_nasional', $data->desil_nasional) }}" maxlength="50"
                               @disabled(! $canEdit)
                               class="w-full rounded-lg border border-primary/60 px-3 py-2 text-sm bg-white disabled:bg-surface-container disabled:text-on-surface-variant disabled:cursor-not-allowed">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs text-on-surface-variant mb-1">Kelengkapan/Kekurangan Berkas</label>
                        <textarea rows="2" disabled class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-surface-container text-on-surface-variant cursor-not-allowed">{{ $data->kelengkapan_kekurangan_berkas }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-on-surface-variant mb-1">Catatan Berkas</label>
                        <textarea rows="2" disabled class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-surface-container text-on-surface-variant cursor-not-allowed">{{ $data->catatan_berkas }}</textarea>
                    </div>

                    @if ($canEdit)
                        <div class="sm:col-span-2">
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-sm font-medium">
                                <span class="material-symbols-outlined text-[18px]">save</span> Simpan Desil Nasional
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        {{-- Card Scan KK: tersendiri, bisa ditutup --}}
        <div x-show="panel" x-cloak x-transition
             class="min-w-0 bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm lg:sticky lg:top-4">
            <div class="px-5 py-3 border-b border-outline-variant/40 flex items-center justify-between gap-3">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-secondary">family_restroom</span>
                    <span x-text="panel ? panel.label : ''"></span>
                </h3>
                <div class="flex items-center gap-1">
                    <a :href="panel ? panel.view : '#'" target="_blank" rel="noopener" title="Buka di tab baru"
                       class="p-1.5 rounded-full hover:bg-surface-container text-on-surface-variant">
                        <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                    </a>
                    <button type="button" @click="panel = null" title="Tutup" aria-label="Tutup"
                            class="p-1.5 rounded-full hover:bg-surface-container text-on-surface-variant">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            </div>
            <div class="bg-surface-container/40 p-2">
                <template x-if="panel && panel.img">
                    <img :src="panel.src" :alt="panel.label" class="w-full max-h-[75vh] object-contain rounded-lg bg-white">
                </template>
                <template x-if="panel && !panel.img">
                    <iframe :src="panel.view" class="w-full h-[75vh] rounded-lg bg-white" title="Scan Kartu Keluarga"></iframe>
                </template>
            </div>
        </div>
    </div>

    {{-- Anggota keluarga --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">group</span> Anggota Keluarga Yang Didaftarkan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase">
                    <tr><th class="px-5 py-3">NIK</th><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Tempat/Tgl Lahir</th><th class="px-5 py-3">Pekerjaan</th><th class="px-5 py-3">Hub. Keluarga</th><th class="px-5 py-3">BPJS</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($data->anggotaKeluarga as $a)
                        <tr>
                            <td class="px-5 py-3">{{ $a->nik }}</td>
                            <td class="px-5 py-3 font-medium">{{ $a->nama }}</td>
                            <td class="px-5 py-3">{{ $a->tempat_lahir }}, {{ optional($a->tanggal_lahir)->format('d-m-Y') }}</td>
                            <td class="px-5 py-3">{{ $a->pekerjaan }}</td>
                            <td class="px-5 py-3">{{ $a->hubungan_keluarga }}</td>
                            <td class="px-5 py-3">{{ $a->keanggotaan_bpjs ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-6 text-center text-on-surface-variant">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.pbi-apbd.partials.hasil-verifikasi')

    {{-- Log --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">history</span> Log Proses</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase">
                    <tr><th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Username</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Aktivitas</th><th class="px-5 py-3">Catatan</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($data->logs as $log)
                        <tr>
                            <td class="px-5 py-3 whitespace-nowrap text-on-surface-variant">{{ $log->created_at->format('d M Y - H:i') }}</td>
                            <td class="px-5 py-3 font-medium">{{ $log->username }}</td>
                            <td class="px-5 py-3"><span class="bg-surface-container px-2 py-1 rounded text-xs">{{ $log->role_name }}</span></td>
                            <td class="px-5 py-3 font-medium">{{ $log->task_name }}</td>
                            <td class="px-5 py-3 text-on-surface-variant">{{ $log->catatan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-on-surface-variant">Belum ada log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Aksi: Operator hanya memverifikasi & meneruskan ke Kepala Bidang (tanpa kembalikan / tolak) --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm mb-10">
        <div class="px-5 py-4 border-b border-outline-variant/40 bg-surface-container/20">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">alt_route</span> Verifikasi Permohonan</h3>
        </div>

        @if ($canAct)
            <form method="POST" action="{{ route('pbi-approval.operator.aksi') }}" class="p-5">
                @csrf
                <input type="hidden" name="ids[]" value="{{ $data->id }}">
                <div class="mb-5">
                    <label class="block text-sm font-bold mb-2">Catatan <span class="text-error">*</span></label>
                    <x-catatan-template tahap="operator" :aksi="['next']" />
                    <textarea id="catatan" name="catatan" rows="3" required class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm">{{ old('catatan') }}</textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="aksi" value="next" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-bold shadow-md">
                        <span class="material-symbols-outlined text-[18px]">redo</span> Verifikasi & Teruskan ke Kepala Bidang
                    </button>
                </div>
            </form>
        @else
            <div class="p-6 text-center">
                <h4 class="text-base font-bold mb-1">Tidak ada aksi pada tahap ini</h4>
                <p class="text-sm text-on-surface-variant">Status permohonan saat ini: <strong>{{ $data->statusLabel() }}</strong>.</p>
            </div>
        @endif
    </div>
</div>
@endsection