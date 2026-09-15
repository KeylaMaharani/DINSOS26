@extends('layouts.admin')

@section('title', 'Detail Ajuan PBI APBN')
@section('page_title', 'PBI APBN — Detail Ajuan')

@section('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@php
    $statusColor = match($data->status) {
        'disetujui' => 'bg-success-container text-on-success-container',
        'ditolak' => 'bg-error-container text-on-error-container',
        default => 'bg-secondary-fixed text-on-secondary-container',
    };
@endphp

@section('content')
<div class="space-y-5 max-w-5xl">

    <a href="{{ route('pbi-apbn.ajuan.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar Ajuan
    </a>

    {{-- Header ringkas --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <p class="text-xs text-on-surface-variant">No. Registrasi</p>
            <h2 class="text-lg font-bold text-primary">{{ $data->no_registrasi }}</h2>
            <p class="text-sm text-on-surface-variant mt-1">{{ $data->nama_kepala_keluarga }} — NIK {{ $data->nik_kepala_keluarga }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $statusColor }}">
            {{ $data->statusLabel() }}
        </span>
    </div>

    {{-- 1. Data Permohonan --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40">
            <h3 class="text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-secondary">assignment</span>
                Data Permohonan
            </h3>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 p-4 text-sm">
            @foreach ([
                'Nama Layanan' => $data->nama_layanan,
                'No KK' => $data->no_kk,
                'Nama Kepala Keluarga' => $data->nama_kepala_keluarga,
                'NIK Kepala Keluarga' => $data->nik_kepala_keluarga,
                'Alamat' => $data->alamat,
                'RT/RW' => $data->rt . ' / ' . $data->rw,
                'Provinsi' => $data->provinsi,
                'Kab/Kota' => $data->kabupaten_kota,
                'Kecamatan' => $data->kecamatan,
                'Desa/Kelurahan' => $data->desa_kelurahan,
                'No Telp' => $data->no_telp,
                'Email' => $data->email,
            ] as $label => $value)
                <div>
                    <dt class="text-xs text-on-surface-variant">{{ $label }}</dt>
                    <dd class="font-medium">{{ $value ?: '-' }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    {{-- Anggota Keluarga --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40">
            <h3 class="text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-secondary">group</span>
                Anggota Keluarga Yang Didaftarkan
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-2">NIK</th>
                        <th class="text-left px-4 py-2">Nama</th>
                        <th class="text-left px-4 py-2">Tempat/Tgl Lahir</th>
                        <th class="text-left px-4 py-2">Pekerjaan</th>
                        <th class="text-left px-4 py-2">Agama</th>
                        <th class="text-left px-4 py-2">JK</th>
                        <th class="text-left px-4 py-2">Hub. Keluarga</th>
                        <th class="text-left px-4 py-2">BPJS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($data->anggotaKeluarga as $anggota)
                        <tr>
                            <td class="px-4 py-2">{{ $anggota->nik }}</td>
                            <td class="px-4 py-2">{{ $anggota->nama }}</td>
                            <td class="px-4 py-2">{{ $anggota->tempat_lahir }}, {{ optional($anggota->tanggal_lahir)->format('d-m-Y') }}</td>
                            <td class="px-4 py-2">{{ $anggota->pekerjaan }}</td>
                            <td class="px-4 py-2">{{ $anggota->agama }}</td>
                            <td class="px-4 py-2">{{ $anggota->jenis_kelamin }}</td>
                            <td class="px-4 py-2">{{ $anggota->hubungan_keluarga }}</td>
                            <td class="px-4 py-2">{{ $anggota->keanggotaan_bpjs }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-4 text-center text-on-surface-variant">Belum ada data anggota keluarga.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Koordinat & Peta --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40">
            <h3 class="text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-secondary">location_on</span>
                Koordinat & Data Desil
            </h3>
        </div>
        @if ($data->latitude && $data->longitude)
            <div id="peta-detail" class="w-full h-64"></div>
        @else
            <p class="px-4 py-6 text-sm text-on-surface-variant">Koordinat lokasi belum tersedia.</p>
        @endif
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 p-4 text-sm">
            <div>
                <dt class="text-xs text-on-surface-variant">Desil Nasional</dt>
                <dd class="font-medium">{{ $data->desil_nasional ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-on-surface-variant">Kelengkapan/Kekurangan Berkas</dt>
                <dd class="font-medium">{{ $data->kelengkapan_kekurangan_berkas ?: '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-on-surface-variant">Catatan Berkas</dt>
                <dd class="font-medium">{{ $data->catatan_berkas ?: '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-on-surface-variant">Diagnosa</dt>
                <dd class="font-medium">{{ $data->diagnosa ?: '-' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Lampiran --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40">
            <h3 class="text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-secondary">attach_file</span>
                Lampiran Persyaratan
            </h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 p-4">
            @foreach ([
                'scan_ktp' => 'Scan KTP',
                'scan_kk' => 'Scan Kartu Keluarga',
                'foto_rumah' => 'Foto Rumah Pemohon',
                'foto_kamar_mandi' => 'Foto Kamar Mandi',
                'foto_selfie_ktp' => 'Foto Selfie dengan KTP',
                'surat_rawat_inap' => 'Surat Ket. Rawat Inap',
                'screenshot_pembaharuan_desil' => 'Screenshot Pembaharuan Desil',
                'screenshot_dtsen' => 'Screenshot DTSEN',
            ] as $field => $label)
                <a @if($data->$field) href="{{ asset('storage/' . $data->$field) }}" target="_blank" @endif
                    class="block border border-outline-variant/40 rounded-lg overflow-hidden hover:border-primary transition-colors">
                    <div class="aspect-square bg-surface-container flex items-center justify-center">
                        @if ($data->$field)
                            <img src="{{ asset('storage/' . $data->$field) }}" class="w-full h-full object-cover" alt="{{ $label }}">
                        @else
                            <span class="material-symbols-outlined text-[28px] text-on-surface-variant/50">image_not_supported</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-center px-1 py-1.5 text-on-surface-variant">{{ $label }}</p>
                </a>
            @endforeach
        </div>
    </div>

    {{-- 4/5. Verifikasi & Validasi Petugas Kelurahan — READ ONLY --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40 flex items-center justify-between">
            <h3 class="text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-secondary">fact_check</span>
                Verifikasi & Validasi Petugas Kelurahan
            </h3>
            <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full
                {{ $data->sudah_diverifikasi_kelurahan ? 'bg-success-container text-on-success-container' : 'bg-warning-container text-on-warning-container' }}">
                <span class="material-symbols-outlined text-[14px]">lock</span>
                {{ $data->sudah_diverifikasi_kelurahan ? 'Sudah Diverifikasi' : 'Menunggu Verifikasi' }}
            </span>
        </div>
        <p class="px-4 pt-3 text-xs text-on-surface-variant italic">
            Bagian ini hanya bisa dilihat. Verifikasi dan validasi dilakukan oleh petugas Kelurahan, bukan oleh admin Dinsos.
        </p>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 p-4 text-sm">
            @foreach ([
                'Status Penguasaan Bangunan' => $data->status_penguasaan_bangunan,
                'Status Lahan' => $data->status_lahan,
                'Penghasilan Rata-Rata/Bulan' => $data->penghasilan_rata_rata,
                'Memiliki Kendaraan Roda 2' => $data->punya_kendaraan_roda_2 === null ? '-' : ($data->punya_kendaraan_roda_2 ? 'Ya' : 'Tidak'),
                'Jumlah Tanggungan Keluarga' => $data->jumlah_tanggungan_keluarga,
                'Jenis Lantai' => $data->jenis_lantai,
                'Jenis Dinding' => $data->jenis_dinding,
                'Kondisi Dinding' => $data->kondisi_dinding,
                'Jenis Atap' => $data->jenis_atap,
                'Kondisi Atap' => $data->kondisi_atap,
                'Sumber Air Minum' => $data->sumber_air_minum,
                'Cara Memperoleh Air Minum' => $data->cara_memperoleh_air_minum,
                'Sumber Penerangan Utama' => $data->sumber_penerangan_utama,
                'Daya Terpasang' => $data->daya_terpasang,
                'Bahan Bakar/Energi Memasak' => $data->bahan_bakar_energi_memasak,
                'Penggunaan Fasilitas BAB' => $data->penggunaan_fasilitas_bab,
                'Jenis Kloset' => $data->jenis_kloset,
                'Tempat Pembuangan Akhir Tinja' => $data->tempat_pembuangan_akhir_tinja,
            ] as $label => $value)
                <div>
                    <dt class="text-xs text-on-surface-variant">{{ $label }}</dt>
                    <dd class="font-medium">{{ $value ?: '-' }}</dd>
                </div>
            @endforeach
            <div class="sm:col-span-2">
                <dt class="text-xs text-on-surface-variant">Kesimpulan/Rekomendasi</dt>
                <dd class="font-medium">{{ $data->kesimpulan_rekomendasi ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-on-surface-variant">Nama Faskes</dt>
                <dd class="font-medium">{{ $data->nama_faskes ?: '-' }}</dd>
            </div>
        </dl>
        <div class="grid grid-cols-2 gap-3 px-4 pb-4">
            @foreach ([
                'scan_surat_pengantar' => 'Scan Surat Pengantar',
                'surat_pengantar_digital' => 'Surat Pengantar Digital',
            ] as $field => $label)
                <a @if($data->$field) href="{{ asset('storage/' . $data->$field) }}" target="_blank" @endif
                    class="flex items-center gap-2 border border-outline-variant/40 rounded-lg px-3 py-2 text-xs hover:border-primary transition-colors">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">description</span>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Log Proses --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40">
            <h3 class="text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-secondary">history</span>
                Log Proses
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-2">Tanggal Proses</th>
                        <th class="text-left px-4 py-2">Username</th>
                        <th class="text-left px-4 py-2">Role</th>
                        <th class="text-left px-4 py-2">Aktivitas</th>
                        <th class="text-left px-4 py-2">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($data->logs as $log)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $log->created_at->format('d-m-Y H:i') }}</td>
                            <td class="px-4 py-2">{{ $log->username }}</td>
                            <td class="px-4 py-2">{{ $log->role_name }}</td>
                            <td class="px-4 py-2">{{ $log->task_name }}</td>
                            <td class="px-4 py-2 text-on-surface-variant">{{ $log->catatan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-4 text-center text-on-surface-variant">Belum ada log proses.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Aksi alur (Simpan / Next / Back / Reject) --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-outline-variant/40">
            <h3 class="text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-secondary">alt_route</span>
                Proses Permohonan
            </h3>
        </div>

        @if ($data->isFinal())
            <p class="px-4 py-6 text-sm text-on-surface-variant">
                Permohonan ini sudah final dengan status <strong>{{ $data->statusLabel() }}</strong>. Tidak ada aksi lanjutan.
            </p>
        @elseif (!$canAct)
            <p class="px-4 py-6 text-sm text-on-surface-variant">
                Permohonan ini sedang berada di tahap <strong>{{ $data->currentStageLabel() }}</strong>.
                Anda tidak berwenang memproses pada tahap ini — hanya bisa melihat.
            </p>
        @else
            <form method="POST" action="{{ route('pbi-apbn.ajuan.aksi', $data) }}" class="p-4 space-y-3" x-data="{ aksi: null }">
                @csrf
                <div>
                    <label class="block text-xs text-on-surface-variant mb-1">Catatan</label>
                    <textarea name="catatan" rows="3" required placeholder="Tulis catatan untuk tahap ini..."
                        class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"></textarea>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($data->previousStageKey())
                        <button type="submit" name="aksi" value="back"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high text-sm font-medium transition-colors">
                            <span class="material-symbols-outlined text-[18px]">undo</span>
                            Kembalikan ke {{ \App\Models\PbiApbn::STAGES[$data->previousStageKey()]['label'] }}
                        </button>
                    @endif

                    <button type="submit" name="aksi" value="simpan"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary/10 text-secondary hover:bg-secondary hover:text-on-secondary text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan Catatan
                    </button>

                    <button type="submit" name="aksi" value="next"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-[18px]">redo</span>
                        @if ($data->nextStageKey() === 'disetujui')
                            Setujui Permohonan
                        @else
                            Teruskan ke {{ \App\Models\PbiApbn::STAGES[$data->nextStageKey()]['label'] }}
                        @endif
                    </button>

                    <button type="submit" name="aksi" value="reject"
                        onclick="return confirm('Yakin ingin menolak permohonan ini? Catatan alasan wajib diisi.');"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-error/10 text-error hover:bg-error hover:text-on-error text-sm font-medium transition-colors ml-auto">
                        <span class="material-symbols-outlined text-[18px]">cancel</span>
                        Reject
                    </button>
                </div>
                <p class="text-[11px] text-on-surface-variant italic">
                    Catatan wajib diisi untuk aksi Reject. Alur Reject masih menunggu konfirmasi lebih lanjut dari pihak terkait.
                </p>
            </form>
        @endif
    </div>


</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@if ($data->latitude && $data->longitude)
<script>
    const lat = {{ $data->latitude }};
    const lng = {{ $data->longitude }};
    const map = L.map('peta-detail').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup(@json($data->alamat)).openPopup();
</script>
@endif
@endpush
