@extends('layouts.admin')

@php
    $statusColor = match ($data->status) {
        'disetujui' => 'bg-success-container text-on-success-container',
        'ditolak' => 'bg-error-container text-on-error-container',
        default => 'bg-secondary-fixed text-on-secondary-container',
    };
    $me = auth()->user();
    $punyaTtd = filled($me->ttd_path);
    // Isian masyarakat = jawaban Ground Check (sama dengan halaman Kelurahan); jawaban foto tampil sebagai kartu lampiran
    $jawabans = ($gcJawabans ?? collect())->sortBy('urutan')->values();
    $fotos = $jawabans->filter(fn ($j) => $j->tipe_input === 'foto' && filled($j->jawaban))->values();
    // Berkas lama dari masyarakat hanya ditampilkan kalau memang ada file-nya
    $lampiranMasyarakat = ['scan_ktp', 'scan_kk', 'surat_rawat_inap', 'screenshot_pembaharuan_desil'];
    $lampiranDaftar = [
        'scan_ktp' => 'Scan KTP', 'scan_kk' => 'Scan Kartu Keluarga',
        'surat_rawat_inap' => 'Surat Ket. Rawat Inap', 'screenshot_pembaharuan_desil' => 'Screenshot Pembaharuan Desil',
        'foto_selfie_ktp' => 'Foto Selfie dengan KTP', 'foto_rumah' => 'Foto Rumah Pemohon',
        'foto_kamar_mandi' => 'Foto Kamar Mandi', 'screenshot_dtsen' => 'Screenshot DTSEN',
    ];
@endphp

@section('title', 'Detail Ajuan PBI APBD - Lurah')
@section('page_title', 'PBI APBD — Lurah')

@section('content')
<div class="space-y-5 max-w-5xl">

    <a href="{{ route('pbi-approval.lurah.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
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
                'No KK' => $data->no_kk, 'Nama Kepala Keluarga' => $data->nama_kepala_keluarga,
                'NIK Kepala Keluarga' => $data->nik_kepala_keluarga, 'Alamat' => $data->alamat,
                'RT/RW' => ($data->rt ?: '-') . ' / ' . ($data->rw ?: '-'), 'Kecamatan' => $data->kecamatan,
                'Desa/Kelurahan' => $data->desa_kelurahan, 'No Telp' => $data->no_telp, 'Email' => $data->email,
            ] as $label => $value)
                <div><dt class="text-xs text-on-surface-variant mb-0.5">{{ $label }}</dt><dd class="font-semibold">{{ $value ?: '-' }}</dd></div>
            @endforeach
            <div>
                <dt class="text-xs text-on-surface-variant mb-0.5">Titik Lokasi (dari masyarakat)</dt>
                <dd class="font-semibold">
                    @if ($data->latitude && $data->longitude)
                        <a class="text-primary underline" target="_blank" rel="noopener"
                           href="https://www.google.com/maps?q={{ $data->latitude }},{{ $data->longitude }}">Buka di Google Maps</a>
                    @else - @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs text-on-surface-variant mb-0.5">Verifikasi Data oleh Kelurahan</dt>
                <dd class="font-semibold">
                    @if ($data->verifikasi_berkas['data_pemohon'] ?? null)
                        <span class="text-success">Sudah diverifikasi ({{ \Illuminate\Support\Carbon::parse($data->verifikasi_berkas['data_pemohon'])->format('d M Y - H:i') }})</span>
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
                    <tr><th class="px-5 py-3">NIK</th><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Hub. Keluarga</th><th class="px-5 py-3">BPJS</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($data->daftarPeserta() as $a)
                        <tr><td class="px-5 py-3">{{ $a->nik }}</td><td class="px-5 py-3 font-medium">{{ $a->nama }}</td>
                            <td class="px-5 py-3">{{ $a->hubungan_keluarga }}</td><td class="px-5 py-3">{{ $a->keanggotaan_bpjs ?: '-' }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-on-surface-variant">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Jawaban masyarakat: HANYA pertanyaan & jawaban (tanpa indeks/bobot/skor) --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40 flex items-center justify-between gap-3 flex-wrap">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">checklist</span> Isian Masyarakat (untuk dicek di lapangan)
            </h3>
        </div>
        @if ($jawabans->isEmpty())
            <p class="p-6 text-sm text-on-surface-variant text-center">Masyarakat belum mengisi parameter.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase">
                        <tr><th class="px-5 py-3 w-14 text-center">No</th><th class="px-5 py-3">Parameter</th><th class="px-5 py-3">Jawaban Masyarakat</th></tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        @foreach ($jawabans as $j)
                            @continue($j->tipe_input === 'foto')
                            @php
                                $nilai = $j->jawaban;
                                $pilihan = null;
                                if ($j->tipe_input === 'pilihan_ganda' && filled($nilai)) {
                                    $dec = json_decode($nilai, true);
                                    $pilihan = is_array($dec) ? $dec : [$nilai];
                                }
                            @endphp
                            <tr>
                                <td class="px-5 py-3 text-center">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3">
                                    {{ $j->nama_parameter }}
                                </td>
                                <td class="px-5 py-3 font-semibold">
                                    @if (blank($nilai))
                                        <span class="font-normal text-on-surface-variant">Belum diisi</span>
                                    @elseif ($pilihan)
                                        {{ implode(', ', $pilihan) }}
                                    @else
                                        {{ $nilai }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Lampiran Persyaratan: foto jawaban masyarakat. Klik kartu -> pilihan Lihat / Unduh --}}
    @if ($fotos->isNotEmpty())
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl shadow-sm">
            <div class="px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-secondary">attach_file</span> Lampiran Persyaratan
                </h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
                @foreach ($fotos as $f)
                    @php
                        $url = asset('storage/' . $f->jawaban);
                        $ext = pathinfo((string) $f->jawaban, PATHINFO_EXTENSION) ?: 'jpg';
                        $namaUnduh = \Illuminate\Support\Str::slug($f->nama_parameter) . '.' . $ext;
                    @endphp
                    <div class="relative min-w-0" x-data="{ buka: false, gagal: false }"
                         @click.outside="buka = false" @keydown.escape.window="buka = false">
                        <button type="button" @click="buka = !buka"
                                class="w-full text-left rounded-xl border border-outline-variant/40 overflow-hidden bg-surface-container-lowest hover:shadow-md transition"
                                :aria-expanded="buka" aria-haspopup="true">
                            <div class="aspect-square bg-[#eef5ff] flex items-center justify-center">
                                <img src="{{ $url }}" alt="{{ $f->nama_parameter }}" loading="lazy"
                                     class="w-full h-full object-cover" x-show="!gagal" x-on:error="gagal = true">
                                <span x-show="gagal" x-cloak class="material-symbols-outlined text-[44px] text-outline">image_not_supported</span>
                            </div>
                            <div class="px-3 py-3 text-center text-sm text-on-surface border-t border-outline-variant/40">
                                {{ $f->nama_parameter }}
                            </div>
                        </button>

                        {{-- Pilihan: Lihat / Unduh --}}
                        <div x-show="buka" x-cloak x-transition.opacity
                             class="absolute z-20 left-3 right-3 top-1/2 -translate-y-1/2 rounded-lg border border-outline-variant/40 bg-white shadow-lg overflow-hidden text-sm">
                            <a href="{{ $url }}" target="_blank" rel="noopener" @click="buka = false"
                               class="flex items-center gap-2 px-3 py-2.5 hover:bg-surface-container">
                                <span class="material-symbols-outlined text-[18px] text-secondary">visibility</span> Lihat
                            </a>
                            <a href="{{ $url }}" download="{{ $namaUnduh }}" @click="buka = false"
                               class="flex items-center gap-2 px-3 py-2.5 hover:bg-surface-container border-t border-outline-variant/30">
                                <span class="material-symbols-outlined text-[18px] text-secondary">download</span> Unduh
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Lampiran + hasil verifikasi Kelurahan --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">attach_file</span> Lampiran & Hasil Verifikasi Kelurahan</h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
            @foreach ($lampiranDaftar as $field => $label)
                @continue(in_array($field, $lampiranMasyarakat, true) && ! $data->$field)
                <div>
                    <x-lampiran-preview :src="$data->$field ? asset('storage/' . $data->$field) : null" :label="$label" />
                    @php $cek = $data->verifikasi_berkas[$field] ?? null; @endphp
                    @if ($cek)
                        <p class="mt-1 text-[11px] font-semibold {{ $cek === 'sesuai' ? 'text-success' : 'text-error' }}">{{ $cek === 'sesuai' ? 'Sesuai' : 'Tidak sesuai' }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Surat pengantar (otomatis dari Kelurahan) --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">description</span> Surat Pengantar Kelurahan</h3>
            <p class="text-xs text-on-surface-variant mt-1">Dibuat otomatis saat Kelurahan meneruskan berkas. Tanda tangan Anda disisipkan saat berkas diteruskan.</p>
        </div>
        <div class="p-5">
            @if ($data->scan_surat_pengantar)
                <div class="w-28"><x-lampiran-preview :src="asset('storage/' . $data->scan_surat_pengantar)" label="Surat Pengantar Kelurahan" /></div>
            @else
                <p class="text-sm text-on-surface-variant">Surat pengantar belum tersedia.</p>
            @endif
            @if ($data->ttd_kelurahan && ! $canAct)
                <div class="w-28 mt-4"><x-lampiran-preview :src="asset('storage/' . $data->ttd_kelurahan)" label="Tanda Tangan Lurah" /></div>
            @endif
            @if ($data->keterangan_lurah && ! $canAct)
                <p class="text-sm mt-4"><span class="text-on-surface-variant">Keterangan Lurah:</span> {{ $data->keterangan_lurah }}</p>
            @endif
        </div>
    </div>

    {{-- Tanda tangan tersimpan milik Lurah (diunggah sekali, dipakai di semua ajuan) --}}
    @if ($canAct)
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">draw</span> Tanda Tangan Saya</h3>
                <p class="text-xs text-on-surface-variant mt-1">Unggah sekali (PNG/JPG, latar putih/transparan, maks. 2 MB). Dipakai otomatis pada setiap ajuan yang Anda tandatangani.</p>
            </div>
            <div class="p-5 space-y-3">
                @if ($punyaTtd)
                    <div class="flex items-center gap-3">
                        <div class="w-28 shrink-0"><x-lampiran-preview :src="asset('storage/' . $me->ttd_path)" label="Tanda Tangan Saya" /></div>
                        <span class="flex items-center gap-1 text-xs text-success"><span class="material-symbols-outlined text-[15px]">check_circle</span> Tersimpan. Unggah baru untuk mengganti.</span>
                    </div>
                @else
                    <div class="px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm">Anda belum mengunggah tanda tangan. Unggah dulu sebelum meneruskan berkas.</div>
                @endif

                <form method="POST" action="{{ route('profil.ttd.store') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="ttd" accept="image/png,image/jpeg" required
                           class="block w-full text-xs border border-outline-variant/40 rounded-lg file:mr-3 file:py-2.5 file:px-3 file:border-0 file:bg-surface-container file:text-xs file:font-semibold">
                    @error('ttd')<p class="text-error text-xs">{{ $message }}</p>@enderror
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-sm font-medium">
                        <span class="material-symbols-outlined text-[18px]">upload</span> {{ $punyaTtd ? 'Ganti Tanda Tangan' : 'Simpan Tanda Tangan' }}
                    </button>
                </form>
            </div>
        </div>
    @endif

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

    {{-- Aksi: hanya tanda tangan + keterangan + catatan --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm mb-10">
        <div class="px-5 py-4 border-b border-outline-variant/40 bg-surface-container/20">
            <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">alt_route</span> Proses Permohonan</h3>
        </div>

        @if ($canAct)
            <form method="POST" action="{{ route('pbi-approval.lurah.aksi') }}" class="p-5 space-y-5">
                @csrf
                <input type="hidden" name="ids[]" value="{{ $data->id }}">
                <div>
                    <label class="block text-sm font-bold mb-2">Keterangan Lurah</label>
                    <textarea name="keterangan_lurah" rows="3" class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm">{{ old('keterangan_lurah', $data->keterangan_lurah) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Catatan <span class="text-error">*</span></label>
                    <textarea name="catatan" rows="3" required class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm">{{ old('catatan') }}</textarea>
                </div>
                <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3">
                    <button type="submit" name="aksi" value="back"
                        onclick="return confirm('Kembalikan berkas ke Kelurahan?');"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-sm font-bold">
                        <span class="material-symbols-outlined text-[18px]">undo</span> Tolak / Kembalikan ke Kelurahan
                    </button>
                    <button type="submit" name="aksi" value="next" @disabled(! $punyaTtd)
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-bold shadow-md sm:ml-auto disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-[18px]">draw</span> Tanda Tangan & Teruskan ke Operator Dinsos
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