@extends('layouts.admin')

@php
    $statusColor = match ($data->status) {
        'disetujui' => 'bg-success-container text-on-success-container',
        'ditolak' => 'bg-error-container text-on-error-container',
        default => 'bg-secondary-fixed text-on-secondary-container',
    };
@endphp

@section('title', 'Detail Ajuan PBI APBD - Kepala Dinas')
@section('page_title', 'PBI APBD — Kepala Dinas')

@section('content')
<div class="space-y-5 max-w-5xl">

    <a href="{{ route('pbi-approval.kadis.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
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

    {{-- Data pemohon --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">assignment</span> Data Pemohon</h3>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
            @foreach ([
                'Nama Layanan' => $data->nama_layanan, 'No KK' => $data->no_kk,
                'Nama Kepala Keluarga' => $data->nama_kepala_keluarga, 'NIK Kepala Keluarga' => $data->nik_kepala_keluarga,
                'Alamat' => $data->alamat, 'RT/RW' => ($data->rt ?: '-') . ' / ' . ($data->rw ?: '-'),
                'Kecamatan' => $data->kecamatan, 'Desa/Kelurahan' => $data->desa_kelurahan,
                'No Telp' => $data->no_telp, 'Email' => $data->email,
                'Desil Nasional' => $data->desil_nasional, 'Nama Faskes' => $data->nama_faskes,
                'Catatan Kelengkapan/Kekurangan Berkas' => $data->catatan_berkas,
            ] as $label => $value)
                <div><dt class="text-xs text-on-surface-variant mb-0.5">{{ $label }}</dt><dd class="font-semibold">{{ $value ?: '-' }}</dd></div>
            @endforeach
            <div>
                <dt class="text-xs text-on-surface-variant mb-0.5">Titik Lokasi</dt>
                <dd class="font-semibold">
                    @if ($data->latitude && $data->longitude)
                        <a class="text-primary underline" target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $data->latitude }},{{ $data->longitude }}">Buka di Google Maps</a>
                    @else - @endif
                </dd>
            </div>
        </dl>
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
                    @forelse ($data->daftarPeserta() as $a)
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

    {{-- Lampiran + Suket --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">attach_file</span> Lampiran & Surat Keterangan</h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-5 pb-16">
            @foreach ([
                'surat_keterangan' => 'Surat Keterangan (Suket)',
                'scan_surat_pengantar' => 'Surat Pengantar Kelurahan',
                'scan_ktp' => 'Scan KTP', 'scan_kk' => 'Scan Kartu Keluarga',
                'foto_rumah' => 'Foto Rumah', 'foto_kamar_mandi' => 'Foto Kamar Mandi',
                'foto_selfie_ktp' => 'Foto Selfie dengan KTP', 'surat_rawat_inap' => 'Surat Ket. Rawat Inap',
                'screenshot_pembaharuan_desil' => 'Screenshot Pembaharuan Desil', 'screenshot_dtsen' => 'Screenshot DTSEN',
                'ttd_kelurahan' => 'Tanda Tangan Kelurahan',
            ] as $field => $label)
                <x-lampiran-preview :src="$data->$field ? asset('storage/' . $data->$field) : null" :label="$label" />
            @endforeach
        </div>
    </div>

    <!--@include('admin.pbi-apbd.partials.diagnosa-edit', ['canDiagnosa' => false])-->

    {{-- Diagnosa (riwayat, baca saja) --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">medical_information</span> Diagnosa</h3>
        </div>
        <div class="p-5 space-y-2 max-h-64 overflow-y-auto">
            @forelse ($data->diagnosaLogs as $log)
                <div class="p-3 bg-surface-container/30 rounded-lg border border-outline-variant/30">
                    <div class="flex justify-between gap-2 flex-wrap mb-1">
                        <span class="text-xs font-semibold">{{ $log->username }} <span class="font-normal text-on-surface-variant">({{ $log->role_name }})</span></span>
                        <span class="text-[11px] text-on-surface-variant">{{ $log->created_at->format('d M Y - H:i') }}</span>
                    </div>
                    <p class="text-sm">{{ $log->diagnosa }}</p>
                </div>
            @empty
                <p class="text-sm text-on-surface-variant italic">Belum ada diagnosa yang tercatat.</p>
            @endforelse
        </div>
    </div>

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

    {{-- Aksi --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm mb-10">
        <div class="px-5 py-4 border-b border-outline-variant/40 bg-surface-container/20">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">alt_route</span> Proses Permohonan</h3>
        </div>

        @if ($canAct)
            <form method="POST" action="{{ route('pbi-approval.kadis.aksi') }}" class="p-5">
                @csrf
                <input type="hidden" name="ids[]" value="{{ $data->id }}">
                <div class="mb-5">
                    <label class="block text-sm font-bold mb-2">Catatan <span class="text-error">*</span></label>
                    <textarea name="catatan" rows="3" required class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm">{{ old('catatan') }}</textarea>
                </div>
                <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3">
                    <button type="submit" name="aksi" value="back" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-outline-variant hover:bg-surface-container-highest text-sm font-bold">
                        <span class="material-symbols-outlined text-[18px]">undo</span> Kembalikan ke Kepala Bidang
                    </button>
                    <button type="submit" name="aksi" value="next" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-bold shadow-md sm:ml-auto">
                        <span class="material-symbols-outlined text-[18px]">redo</span> Teruskan ke Dinas Kesehatan
                    </button>
                    <button type="submit" name="aksi" value="reject" onclick="return confirm('Yakin menolak permohonan ini?');"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-sm font-bold">
                        <span class="material-symbols-outlined text-[18px]">cancel</span> Reject
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