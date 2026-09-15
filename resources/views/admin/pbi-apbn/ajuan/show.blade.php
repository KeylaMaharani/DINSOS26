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

    <a href="{{ route('pbi-apbn.ajuan.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar Ajuan
    </a>

    @if (session('success'))
        <div class="px-4 py-3 rounded-lg bg-success-container text-on-success-container text-sm shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header ringkas --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-sm">
        <div>
            <p class="text-xs text-on-surface-variant uppercase tracking-wider font-medium">No. Registrasi</p>
            <h2 class="text-xl font-bold text-primary mt-0.5">{{ $data->no_registrasi }}</h2>
            <p class="text-sm text-on-surface-variant mt-1">{{ $data->nama_kepala_keluarga }} &mdash; NIK {{ $data->nik_kepala_keluarga }}</p>
        </div>
        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold {{ $statusColor }}">
            {{ $data->statusLabel() }}
        </span>
    </div>

    {{-- 1. Data Permohonan --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40 bg-surface-container-lowest">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">assignment</span>
                Data Permohonan
            </h3>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
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
                    <dt class="text-xs text-on-surface-variant mb-0.5">{{ $label }}</dt>
                    <dd class="font-semibold text-on-surface">{{ $value ?: '-' }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    {{-- Anggota Keluarga --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">group</span>
                Anggota Keluarga Yang Didaftarkan
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 font-medium">NIK</th>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Tempat/Tgl Lahir</th>
                        <th class="px-5 py-3 font-medium">Pekerjaan</th>
                        <th class="px-5 py-3 font-medium">Agama</th>
                        <th class="px-5 py-3 font-medium">JK</th>
                        <th class="px-5 py-3 font-medium">Hub. Keluarga</th>
                        <th class="px-5 py-3 font-medium">BPJS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($data->anggotaKeluarga as $anggota)
                        <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                            <td class="px-5 py-3">{{ $anggota->nik }}</td>
                            <td class="px-5 py-3 font-medium text-on-surface">{{ $anggota->nama }}</td>
                            <td class="px-5 py-3">{{ $anggota->tempat_lahir }}, {{ optional($anggota->tanggal_lahir)->format('d-m-Y') }}</td>
                            <td class="px-5 py-3">{{ $anggota->pekerjaan }}</td>
                            <td class="px-5 py-3">{{ $anggota->agama }}</td>
                            <td class="px-5 py-3">{{ $anggota->jenis_kelamin }}</td>
                            <td class="px-5 py-3">{{ $anggota->hubungan_keluarga }}</td>
                            <td class="px-5 py-3">{{ $anggota->keanggotaan_bpjs }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-6 text-center text-on-surface-variant">Belum ada data anggota keluarga.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Koordinat & Peta --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">location_on</span>
                Koordinat & Data Desil
            </h3>
        </div>
        @if ($data->latitude && $data->longitude)
            <div id="peta-detail" class="w-full h-[300px] bg-surface-container"></div>
        @else
            <div class="w-full h-32 flex items-center justify-center bg-surface-container/30">
                <p class="text-sm text-on-surface-variant">Koordinat lokasi belum tersedia.</p>
            </div>
        @endif
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
            <div>
                <dt class="text-xs text-on-surface-variant mb-0.5">Desil Nasional</dt>
                <dd class="font-semibold text-on-surface">{{ $data->desil_nasional ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-on-surface-variant mb-0.5">Kelengkapan/Kekurangan Berkas</dt>
                <dd class="font-semibold text-on-surface">{{ $data->kelengkapan_kekurangan_berkas ?: '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-on-surface-variant mb-0.5">Catatan Berkas</dt>
                <dd class="font-semibold text-on-surface">{{ $data->catatan_berkas ?: '-' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Lampiran + Diagnosa (editable) --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">attach_file</span>
                Lampiran Persyaratan
            </h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
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
                    class="group block border border-outline-variant/40 rounded-lg overflow-hidden hover:border-primary transition-all">
                    <div class="aspect-square bg-surface-container/50 flex items-center justify-center p-2">
                        @if ($data->$field)
                            <img src="{{ asset('storage/' . $data->$field) }}" class="w-full h-full object-contain mix-blend-multiply transition-transform group-hover:scale-105" alt="{{ $label }}">
                        @else
                            <span class="material-symbols-outlined text-[32px] text-on-surface-variant/40">image_not_supported</span>
                        @endif
                    </div>
                    <div class="bg-surface-container-lowest border-t border-outline-variant/40 p-2">
                        <p class="text-[11px] text-center text-on-surface-variant font-medium">{{ $label }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Diagnosa (editable) - BERSIH HANYA FORM --}}
        <div class="border-t border-outline-variant/40 px-5 py-6">
            @if ($canAct)
                <form method="POST" action="{{ route('pbi-apbn.ajuan.updateTambahan', $data) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Sisi Kiri: Label & Keterangan --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Diagnosa <span class="text-error">*</span>
                            </label>
                            <p class="text-xs text-on-surface-variant leading-relaxed">
                                Sesuaikan diagnosa dengan lampiran surat keterangan dari faskes.
                            </p>
                        </div>
                        
                        {{-- Sisi Kanan: Input, Tombol --}}
                        <div class="md:col-span-2 flex flex-col gap-4">
                            <div>
                                <textarea name="diagnosa" rows="3" placeholder="Tulis diagnosa..."
                                    class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-[#003B5C]/40 focus:border-[#003B5C] bg-white shadow-sm transition-shadow">{{ old('diagnosa', $data->diagnosa) }}</textarea>
                                @error('diagnosa')
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#003B5C] text-white hover:bg-[#002A42] text-sm font-medium transition-all shadow-sm">
                                    <span class="material-symbols-outlined text-[18px]">save</span>
                                    Simpan Diagnosa
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                {{-- Tampilan Read-only jika user tidak bisa edit --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <p class="text-sm font-bold text-on-surface mb-1">Diagnosa</p>
                    </div>
                    <div class="md:col-span-2">
                        <div class="px-4 py-3 bg-surface-container/30 rounded-lg border border-outline-variant/30">
                            <p class="text-sm font-medium text-on-surface">{{ $data->diagnosa ?: 'Belum ada diagnosa.' }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- 4/5. Verifikasi & Validasi Petugas Kelurahan — READ ONLY --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">fact_check</span>
                Verifikasi & Validasi Petugas Kelurahan
            </h3>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full
                {{ $data->sudah_diverifikasi_kelurahan ? 'bg-success-container text-on-success-container' : 'bg-warning-container text-on-warning-container' }}">
                <span class="material-symbols-outlined text-[14px]">lock</span>
                {{ $data->sudah_diverifikasi_kelurahan ? 'Sudah Diverifikasi' : 'Menunggu Verifikasi' }}
            </span>
        </div>
        <p class="px-5 pt-4 text-xs text-on-surface-variant italic leading-relaxed">
            *Bagian ini hanya bisa dilihat. Verifikasi dan validasi dilakukan oleh petugas Kelurahan, bukan oleh admin Dinsos.
        </p>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
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
                    <dt class="text-xs text-on-surface-variant mb-0.5">{{ $label }}</dt>
                    <dd class="font-semibold text-on-surface">{{ $value ?: '-' }}</dd>
                </div>
            @endforeach
            <div class="sm:col-span-2 mt-2 p-4 bg-surface-container/30 rounded-lg border border-outline-variant/30">
                <dt class="text-xs text-on-surface-variant mb-1">Kesimpulan/Rekomendasi Kelurahan</dt>
                <dd class="font-semibold text-on-surface">{{ $data->kesimpulan_rekomendasi ?: '-' }}</dd>
            </div>
        </dl>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 px-5 pb-5">
            @foreach ([
                'scan_surat_pengantar' => 'Scan Surat Pengantar',
                'surat_pengantar_digital' => 'Surat Pengantar Digital',
            ] as $field => $label)
                <a @if($data->$field) href="{{ asset('storage/' . $data->$field) }}" target="_blank" @endif
                    class="flex items-center justify-center gap-2 border border-outline-variant/60 rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-surface-container/50 hover:border-primary transition-all">
                    <span class="material-symbols-outlined text-[18px] text-primary">description</span>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- 6. Fasilitas Kesehatan (editable) --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">local_hospital</span>
                Fasilitas Kesehatan
            </h3>
        </div>
        @if ($canAct)
            <form method="POST" action="{{ route('pbi-apbn.ajuan.updateTambahan', $data) }}" class="p-5 grid grid-cols-1 md:grid-cols-3 gap-6">
                @csrf
                @method('PUT')
                <div class="md:col-span-1">
                     <label class="block text-sm font-bold text-on-surface mb-1">Pilih Faskes</label>
                     <p class="text-xs text-on-surface-variant">Tentukan faskes terdekat pemohon.</p>
                </div>
                <div class="md:col-span-2">
                    <select name="nama_faskes"
                        class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm focus:outline-none focus:ring-2 focus:ring-[#003B5C]/40 focus:border-[#003B5C] bg-white shadow-sm transition-shadow">
                        <option value="">- Pilih Faskes Terdekat -</option>
                        @foreach ($faskesOptions as $opt)
                            <option value="{{ $opt }}" @selected(old('nama_faskes', $data->nama_faskes) === $opt)>{{ $opt }}</option>
                        @endforeach
                        @if ($data->nama_faskes && !in_array($data->nama_faskes, $faskesOptions))
                            <option value="{{ $data->nama_faskes }}" selected>{{ $data->nama_faskes }} (tersimpan)</option>
                        @endif
                    </select>
                    @error('nama_faskes')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                    @if (empty($faskesOptions))
                        <p class="text-xs text-warning mt-2 bg-warning-container/20 p-2 rounded border border-warning/20">Belum ada data faskes untuk kecamatan "{{ $data->kecamatan ?: '-' }}". Data master faskes masih sementara — lengkapi di config/faskes.php.</p>
                    @endif
                    <div class="mt-4">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#003B5C] text-white hover:bg-[#002A42] text-sm font-medium transition-all shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Faskes
                        </button>
                    </div>
                </div>
            </form>
        @else
            <dl class="p-5 text-sm grid grid-cols-1 md:grid-cols-3 gap-6">
                <dt class="md:col-span-1 text-sm font-bold text-on-surface">Nama Faskes</dt>
                <dd class="md:col-span-2 font-semibold text-on-surface bg-surface-container/30 px-4 py-2.5 rounded-lg border border-outline-variant/30">{{ $data->nama_faskes ?: 'Belum ditentukan' }}</dd>
            </dl>
        @endif
    </div>

    {{-- Log Proses --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">history</span>
                Log Proses
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase tracking-wider">
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
                        <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                            <td class="px-5 py-3 whitespace-nowrap text-on-surface-variant">{{ $log->created_at->format('d M Y - H:i') }}</td>
                            <td class="px-5 py-3 font-medium text-on-surface">{{ $log->username }}</td>
                            <td class="px-5 py-3"><span class="bg-surface-container px-2 py-1 rounded text-xs">{{ $log->role_name }}</span></td>
                            <td class="px-5 py-3 font-medium">{{ $log->task_name }}</td>
                            <td class="px-5 py-3 text-on-surface-variant">{{ $log->catatan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-on-surface-variant">Belum ada log proses.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Aksi alur (Simpan / Next / Back / Reject) --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm mb-10">
        <div class="px-5 py-4 border-b border-outline-variant/40 bg-surface-container/20">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">alt_route</span>
                Proses Permohonan
            </h3>
        </div>

        @if ($data->isFinal())
            <div class="p-6 text-center bg-surface-container-lowest">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full {{ $data->status === 'disetujui' ? 'bg-success/20 text-success' : 'bg-error/20 text-error' }} mb-3">
                    <span class="material-symbols-outlined text-[24px]">{{ $data->status === 'disetujui' ? 'check_circle' : 'cancel' }}</span>
                </div>
                <h4 class="text-lg font-bold mb-1">Permohonan Final</h4>
                <p class="text-sm text-on-surface-variant">
                    Permohonan ini telah final dengan status <strong>{{ $data->statusLabel() }}</strong>. Tidak ada aksi lanjutan.
                </p>
            </div>
        @elseif (!$canAct)
            <div class="p-6 text-center bg-surface-container-lowest">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-warning/20 text-warning mb-3">
                    <span class="material-symbols-outlined text-[24px]">hourglass_empty</span>
                </div>
                <h4 class="text-lg font-bold mb-1">Menunggu Tahap Selanjutnya</h4>
                <p class="text-sm text-on-surface-variant">
                    Permohonan ini sedang berada di tahap <strong>{{ $data->currentStageLabel() }}</strong>.<br>
                    Anda tidak berwenang memproses pada tahap ini.
                </p>
            </div>
        @else
            <form method="POST" action="{{ route('pbi-apbn.ajuan.aksi', $data) }}" class="p-5" x-data="{ aksi: null }">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm font-bold text-on-surface mb-2">Catatan Proses <span class="text-error">*</span></label>
                    <textarea name="catatan" rows="3" required placeholder="Tulis catatan (wajib diisi, terutama jika ingin Reject)..."
                        class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary shadow-sm"></textarea>
                </div>
                <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3">
                    @if ($data->previousStageKey())
                        <button type="submit" name="aksi" value="back"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-outline-variant hover:bg-surface-container-highest text-on-surface text-sm font-bold transition-all">
                            <span class="material-symbols-outlined text-[18px]">undo</span>
                            Kembalikan ke {{ \App\Models\PbiApbn::STAGES[$data->previousStageKey()]['label'] }}
                        </button>
                    @endif

                    <button type="submit" name="aksi" value="simpan"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg bg-surface-container hover:bg-secondary/20 text-secondary text-sm font-bold transition-all">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan Catatan Saja
                    </button>

                    <button type="submit" name="aksi" value="next"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-bold shadow-md transition-all sm:ml-auto">
                        <span class="material-symbols-outlined text-[18px]">{{ $data->nextStageKey() === 'disetujui' ? 'check_circle' : 'redo' }}</span>
                        @if ($data->nextStageKey() === 'disetujui')
                            Setujui Final
                        @else
                            Teruskan ke {{ \App\Models\PbiApbn::STAGES[$data->nextStageKey()]['label'] }}
                        @endif
                    </button>

                    <button type="submit" name="aksi" value="reject"
                        onclick="return confirm('Yakin ingin menolak permohonan ini?');"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-sm font-bold transition-all">
                        <span class="material-symbols-outlined text-[18px]">cancel</span>
                        Tolak (Reject)
                    </button>
                </div>
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