@extends('layouts.admin')

@section('title', 'Verifikasi Kelurahan')
@section('page_title', 'PBI APBD — Verifikasi Kelurahan')

@php
    $bisaProses = $data->status === 'kelurahan';
    $jawabans = $data->jawabans->sortBy('urutan');
    $dataLengkap = $totalParameter > 0 && $jawabans->count() >= $totalParameter;
@endphp

@section('content')
<div class="space-y-5 max-w-5xl">

    <a href="{{ route('pbi-kelurahan.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar
    </a>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-sm">
        <div>
            <p class="text-xs text-on-surface-variant uppercase tracking-wider font-medium">No. Registrasi</p>
            <h2 class="text-xl font-bold text-primary mt-0.5">{{ $data->no_registrasi }}</h2>
            <p class="text-sm text-on-surface-variant mt-1">{{ $data->nama_kepala_keluarga }} — NIK {{ $data->nik_kepala_keluarga }}</p>
        </div>
        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-secondary-fixed text-on-secondary-container">{{ $data->statusLabel() }}</span>
    </div>

    @if ($bisaProses && $data->dikembalikan_ke_masyarakat)
        <div class="px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm">
            <strong>Sudah dikembalikan ke masyarakat</strong> dan menunggu perbaikan data.
            @if ($data->catatan_kelurahan)<div class="mt-1">Catatan: {{ $data->catatan_kelurahan }}</div>@endif
        </div>
    @endif

    {{-- Data permohonan --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40"><h3 class="text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">assignment</span> Data Pemohon</h3></div>
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
        </dl>
    </div>

    {{-- Anggota keluarga --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/40"><h3 class="text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">group</span> Anggota Keluarga Yang Didaftarkan</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase">
                    <tr><th class="px-5 py-3">NIK</th><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Hubungan</th><th class="px-5 py-3">BPJS</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($data->anggotaKeluarga as $a)
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
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $dataLengkap ? 'bg-success-container text-on-success-container' : 'bg-warning-container text-on-warning-container' }}">
                {{ $jawabans->count() }}/{{ $totalParameter }} terjawab
            </span>
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
                            <tr><td class="px-5 py-3 text-center">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3">{{ $j->parameter_nama }}</td>
                                <td class="px-5 py-3 font-semibold">{{ $j->jawaban_label }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($bisaProses)
        <form method="POST" action="{{ route('pbi-kelurahan.aksi', $data) }}" enctype="multipart/form-data" class="space-y-5 mb-10">
            @csrf

            @if ($errors->any())
                <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
                    <p class="font-semibold mb-1">Periksa kembali:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Lampiran hasil turun lapangan --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">attach_file</span> Lampiran Hasil Verifikasi Lapangan</h3>
                    <p class="text-xs text-on-surface-variant mt-1">Bertanda <span class="text-error">*</span> wajib ada sebelum divalidasi. Pilih file baru untuk mengganti. Maks. 4 MB.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5">
                    @foreach ($lampiran as $field => [$label, $wajib, $mimes])
                        <div class="min-w-0">
                            <label for="{{ $field }}" class="block text-xs font-semibold mb-1">{{ $label }} @if($wajib)<span class="text-error">*</span>@endif</label>
                            @if ($data->{$field})
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-20 shrink-0"><x-lampiran-preview :src="asset('storage/' . $data->{$field})" :label="$label" /></div>
                                    <span class="flex items-center gap-1 text-xs text-success"><span class="material-symbols-outlined text-[15px]">check_circle</span> Sudah diunggah</span>
                                </div>
                            @endif
                            <input type="file" id="{{ $field }}" name="{{ $field }}" accept="{{ collect(explode(',', $mimes))->map(fn($m) => $m === 'pdf' ? '.pdf' : 'image/' . ($m === 'jpg' ? 'jpeg' : $m))->unique()->implode(',') }}"
                                   class="block w-full text-xs border border-outline-variant/40 rounded-lg file:mr-3 file:py-2.5 file:px-3 file:border-0 file:bg-surface-container file:text-xs file:font-semibold" />
                            @error($field)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kesimpulan & aksi --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
                <div class="px-5 py-4 border-b border-outline-variant/40"><h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-secondary">fact_check</span> Kesimpulan & Proses</h3></div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Kesimpulan / Rekomendasi Kelurahan <span class="text-error">*</span> <span class="font-normal text-on-surface-variant">(wajib untuk Validasi)</span></label>
                        <textarea name="kesimpulan_rekomendasi" rows="3" class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm">{{ old('kesimpulan_rekomendasi', $data->kesimpulan_rekomendasi) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Catatan <span class="font-normal text-on-surface-variant">(wajib untuk Kembalikan / Tolak)</span></label>
                        <textarea name="catatan" rows="2" class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3">
                        <button type="submit" name="aksi" value="kembalikan"
                            data-confirm="Kembalikan berkas ke masyarakat untuk memperbaiki data?" data-confirm-title="Kembalikan ke Masyarakat"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-outline-variant hover:bg-surface-container-highest text-sm font-bold">
                            <span class="material-symbols-outlined text-[18px]">undo</span> Kembalikan ke Masyarakat
                        </button>
                        <button type="submit" name="aksi" value="simpan"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg bg-surface-container hover:bg-secondary/20 text-secondary text-sm font-bold">
                            <span class="material-symbols-outlined text-[18px]">save</span> Simpan Draft
                        </button>
                        <button type="submit" name="aksi" value="validasi"
                            data-confirm="Validasi berkas ini dan teruskan ke Operator Dinsos? Setelah diteruskan, Kelurahan tidak bisa mengubahnya lagi." data-confirm-title="Validasi & Teruskan"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-secondary text-sm font-bold shadow-md sm:ml-auto">
                            <span class="material-symbols-outlined text-[18px]">redo</span> Validasi & Teruskan ke Operator Dinsos
                        </button>
                        <button type="submit" name="aksi" value="tolak"
                            data-confirm="Yakin menolak permohonan ini?" data-confirm-variant="error" data-confirm-title="Tolak Permohonan"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-sm font-bold">
                            <span class="material-symbols-outlined text-[18px]">cancel</span> Tolak
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-6 text-center shadow-sm">
            <span class="material-symbols-outlined text-[32px] text-success">task_alt</span>
            <h4 class="text-base font-bold mt-1">Sudah diproses Kelurahan</h4>
            <p class="text-sm text-on-surface-variant">Status saat ini: <strong>{{ $data->statusLabel() }}</strong>.</p>
            @if ($data->kesimpulan_rekomendasi)<p class="text-sm mt-3"><span class="text-on-surface-variant">Kesimpulan:</span> {{ $data->kesimpulan_rekomendasi }}</p>@endif
        </div>
    @endif

    {{-- Log --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm mb-10">
        <div class="px-5 py-4 border-b border-outline-variant/40"><h3 class="text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">history</span> Log Proses</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase">
                    <tr><th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Username</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Aktivitas</th><th class="px-5 py-3">Catatan</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($data->logs as $log)
                        <tr><td class="px-5 py-3 whitespace-nowrap text-on-surface-variant">{{ $log->created_at->format('d M Y - H:i') }}</td>
                            <td class="px-5 py-3 font-medium">{{ $log->username }}</td>
                            <td class="px-5 py-3"><span class="bg-surface-container px-2 py-1 rounded text-xs">{{ $log->role_name }}</span></td>
                            <td class="px-5 py-3 font-medium">{{ $log->task_name }}</td>
                            <td class="px-5 py-3 text-on-surface-variant">{{ $log->catatan ?: '-' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-on-surface-variant">Belum ada log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
