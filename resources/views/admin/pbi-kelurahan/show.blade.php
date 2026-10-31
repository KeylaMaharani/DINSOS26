@extends('layouts.admin')

@section('title', 'Verifikasi Kelurahan')
@section('page_title', 'PBI APBD — Verifikasi Kelurahan')

@php
    $bisaProses = $data->status === 'kelurahan';
    // Isian masyarakat = jawaban Ground Check (sama dengan form warga & hitungan kelengkapan di controller)
    $jawabans = $gcJawabans->sortBy('urutan')->values();
    $dataLengkap = $totalParameter > 0 && $terjawab >= $totalParameter;
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
                {{ $terjawab }}/{{ $totalParameter }} terjawab
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
                                    @if ($j->parameter_id && ! in_array($j->parameter_id, $idAktif, true))
                                        <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-surface-container text-on-surface-variant">Disembunyikan dari warga</span>
                                    @endif
                                </td>                                <td class="px-5 py-3 font-semibold">
                                    @if (blank($nilai))
                                        <span class="font-normal text-on-surface-variant">Belum diisi</span>
                                    @elseif ($j->tipe_input === 'foto')
                                        <div class="w-20"><x-lampiran-preview :src="asset('storage/' . $nilai)" :label="$j->nama_parameter" /></div>
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
                        {{-- TTD punya kartu sendiri di bawah (pilih: digambar di layar / unggah foto) --}}
                        @continue($field === 'ttd_kelurahan')
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

            {{-- Tanda tangan Kelurahan: digambar langsung di layar ATAU unggah foto --}}
            <script>
                // Dipanggil lewat x-data="ttdPad()". Hasil gambar dikirim sebagai data URL PNG
                // lewat input tersembunyi "ttd_kelurahan_data" (diproses di PbiKelurahanController@aksi).
                window.ttdPad = function () {
                    return {
                        mode: 'layar',
                        ada: false,
                        menggambar: false,
                        ctx: null,
                        init() {
                            this.$nextTick(() => this.siap());
                            this.$watch('mode', (v) => { if (v === 'layar') this.$nextTick(() => this.siap()); });
                        },
                        siap() {
                            const c = this.$refs.kanvas;
                            if (!c || !c.clientWidth) return;
                            const rasio = window.devicePixelRatio || 1;
                            // Ukuran kanvas hanya disesuaikan saat masih kosong (mengubah ukuran akan menghapus gambar)
                            if (!this.ada) {
                                c.width = c.clientWidth * rasio;
                                c.height = c.clientHeight * rasio;
                            }
                            this.ctx = c.getContext('2d');
                            this.ctx.setTransform(c.width / c.clientWidth, 0, 0, c.height / c.clientHeight, 0, 0);
                            this.ctx.lineWidth = 2.4;
                            this.ctx.lineCap = 'round';
                            this.ctx.lineJoin = 'round';
                            this.ctx.strokeStyle = '#121d26';
                        },
                        titik(e) {
                            const r = this.$refs.kanvas.getBoundingClientRect();
                            return { x: e.clientX - r.left, y: e.clientY - r.top };
                        },
                        mulai(e) {
                            if (!this.ctx) this.siap();
                            if (!this.ctx) return;
                            e.preventDefault();
                            this.$refs.kanvas.setPointerCapture(e.pointerId);
                            const p = this.titik(e);
                            this.menggambar = true;
                            this.ctx.beginPath();
                            this.ctx.moveTo(p.x, p.y);
                            this.ctx.lineTo(p.x + 0.01, p.y + 0.01);
                            this.ctx.stroke();
                            this.ada = true;
                        },
                        gerak(e) {
                            if (!this.menggambar) return;
                            const p = this.titik(e);
                            this.ctx.lineTo(p.x, p.y);
                            this.ctx.stroke();
                            this.ctx.beginPath();
                            this.ctx.moveTo(p.x, p.y);
                        },
                        selesai() {
                            if (!this.menggambar) return;
                            this.menggambar = false;
                            this.$refs.data.value = this.$refs.kanvas.toDataURL('image/png');
                        },
                        bersihkan() {
                            const c = this.$refs.kanvas;
                            this.ctx.save();
                            this.ctx.setTransform(1, 0, 0, 1, 0, 0);
                            this.ctx.clearRect(0, 0, c.width, c.height);
                            this.ctx.restore();
                            this.ada = false;
                            this.$refs.data.value = '';
                        },
                    };
                };
            </script>

            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm" x-data="ttdPad()" @resize.window="siap()">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-secondary">draw</span> Tanda Tangan Kelurahan</h3>
                    <p class="text-xs text-on-surface-variant mt-1">Pilih salah satu: tanda tangan langsung di layar, atau unggah foto tanda tangan. Tersimpan saat menekan Simpan Draft atau Validasi.</p>
                </div>

                <div class="p-5 space-y-4">
                    @if ($data->ttd_kelurahan)
                        <div class="flex items-center gap-3">
                            <div class="w-24 shrink-0"><x-lampiran-preview :src="asset('storage/' . $data->ttd_kelurahan)" label="Tanda Tangan Kelurahan" /></div>
                            <span class="flex items-center gap-1 text-xs text-success"><span class="material-symbols-outlined text-[15px]">check_circle</span> Sudah ada. Buat atau unggah yang baru untuk menggantinya.</span>
                        </div>
                    @endif

                    <div class="inline-flex rounded-lg border border-outline-variant/50 overflow-hidden text-xs font-semibold">
                        <button type="button" @click="mode = 'layar'"
                            :class="mode === 'layar' ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container'"
                            class="px-4 py-2 inline-flex items-center gap-1.5 transition-colors">
                            <span class="material-symbols-outlined text-[16px]">gesture</span> Tanda tangan di layar
                        </button>
                        <button type="button" @click="mode = 'upload'"
                            :class="mode === 'upload' ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container'"
                            class="px-4 py-2 inline-flex items-center gap-1.5 transition-colors">
                            <span class="material-symbols-outlined text-[16px]">upload_file</span> Unggah foto
                        </button>
                    </div>

                    {{-- Mode 1: digambar di layar --}}
                    <div x-show="mode === 'layar'">
                        <div class="relative rounded-lg border-2 border-dashed border-outline-variant bg-white">
                            <canvas x-ref="kanvas" class="block w-full h-48 rounded-lg cursor-crosshair" style="touch-action: none;"
                                @pointerdown="mulai($event)" @pointermove="gerak($event)"
                                @pointerup="selesai()" @pointercancel="selesai()" @pointerleave="selesai()"></canvas>
                            <p x-show="!ada" class="absolute inset-0 flex items-center justify-center text-xs text-on-surface-variant/60 pointer-events-none select-none">Tanda tangan di sini</p>
                        </div>
                        <div class="flex items-center justify-between gap-3 mt-2">
                            <p class="text-[11px] text-on-surface-variant">Gunakan mouse, stylus, atau jari.</p>
                            <button type="button" @click="bersihkan()" x-show="ada" x-cloak
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant text-xs font-semibold hover:bg-surface-container">
                                <span class="material-symbols-outlined text-[16px]">ink_eraser</span> Hapus
                            </button>
                        </div>
                        <input type="hidden" name="ttd_kelurahan_data" x-ref="data" :disabled="mode !== 'layar'">
                        @error('ttd_kelurahan_data')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Mode 2: unggah foto --}}
                    <div x-show="mode === 'upload'" x-cloak>
                        <input type="file" name="ttd_kelurahan" accept="image/jpeg,image/png" :disabled="mode !== 'upload'"
                            class="block w-full text-xs border border-outline-variant/40 rounded-lg file:mr-3 file:py-2.5 file:px-3 file:border-0 file:bg-surface-container file:text-xs file:font-semibold" />
                        <p class="text-[11px] text-on-surface-variant mt-1">Format JPG atau PNG. Maks. 4 MB.</p>
                        @error('ttd_kelurahan')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
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
