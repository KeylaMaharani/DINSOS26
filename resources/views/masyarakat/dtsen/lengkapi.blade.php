@extends('layouts.masyarakat')

@section('title', 'Lengkapi Data DTSEN - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Lengkapi Data')
@section('back', route('masyarakat.dashboard'))

@php
    // $dtsen     : DtsenPermohonan milik user yang login
    // $bisaEdit  : bool, masyarakat masih boleh mengisi / memperbaiki atau tidak
    // $prefill   : [field => nilai awal] dari pengajuan ini / DTSEN sebelumnya / PBI APBD / akun
    // $asal      : [field => label sumber] untuk keterangan "terisi otomatis"
    $detail = $dtsen->detail;
    $lampiran = $dtsen->lampiran;
    $prefill = $prefill ?? [];
    $asal = $asal ?? [];

    // Pengajuan ditolak: tampilkan catatan petugas dari log penolakan terakhir
    $ditolak = $dtsen->status === \App\Models\DtsenPermohonan::STATUS_DITOLAK;
    $catatanTolak = $ditolak ? optional($dtsen->logs->where('taskname', 'Ditolak')->last())->catatan : null;

    // Petugas meminta perbaikan (ditolak / dikembalikan): semua field dibuka supaya bisa dikoreksi
    $perluPerbaikan = $ditolak || ($dtsen->dikembalikan_ke_masyarakat ?? false);

    // 'lock' => false : field isian bebas yang diketik warga, tidak pernah dikunci
    $fields = [
        'nik' => ['label' => 'NIK', 'type' => 'text', 'numeric' => true],
        'nama' => ['label' => 'Nama Lengkap', 'type' => 'text'],
        'no_kk' => ['label' => 'No. KK', 'type' => 'text', 'numeric' => true],
        'tanggal_lahir' => ['label' => 'Tanggal Lahir', 'type' => 'date'],
        'alamat' => ['label' => 'Alamat', 'type' => 'textarea'],
        'alasan_keputusan_cetak' => ['label' => 'Alasan Keputusan Cetak', 'type' => 'textarea', 'lock' => false],
    ];

    // Lampiran: dipakai dari menu Dokumen bila sudah ada; kalau belum ada, diunggah lewat form ini
    // (lalu otomatis tersimpan juga di menu Dokumen). jenis = jenis dokumen di menu Dokumen.
    $lampiranFields = [
        'scan_ktp' => ['label' => 'Scan KTP', 'required' => false, 'jenis' => 'ktp'],
        'screenshot_dtsen' => ['label' => 'Screenshot DTSEN', 'required' => true, 'jenis' => 'screenshot_dtsen'],
    ];

    $inputClass = 'w-full rounded-xl border border-outline-variant/60 px-3.5 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand disabled:bg-surface-container disabled:text-on-surface-variant';
    $kunciClass = 'bg-surface-container text-on-surface-variant cursor-not-allowed';
    $inputFile = 'block w-full min-w-0 text-xs text-on-surface-variant file:mr-3 file:rounded-full file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-primary';
@endphp

@section('content')
    @unless (request()->attributes->get('is_mobile'))
        <a href="{{ route('masyarakat.dashboard') }}"
           class="inline-flex items-center gap-1 text-sm text-on-surface-variant mb-3 sm:mb-4 py-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali
        </a>
    @endunless

    <div class="flex items-center gap-3 mb-5">
        <span class="w-11 h-11 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
            <span class="material-symbols-outlined text-[24px]">groups</span>
        </span>
        <div class="min-w-0">
            <h1 class="text-lg font-extrabold text-brand leading-tight">Lengkapi Data DTSEN</h1>
            <p class="text-xs text-on-surface-variant break-words">
                @if ($dtsen->exists)
                    No. Permohonan: <strong>{{ $dtsen->no_permohonan ?? $dtsen->nik }}</strong>
                @else
                    Pengajuan baru &mdash; lengkapi data di bawah lalu kirim.
                @endif
            </p>
        </div>
    </div>

    @if (($dtsen->dikembalikan_ke_masyarakat ?? false) && $bisaEdit)
        <div class="mb-4 px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm">
            <p class="font-semibold">Petugas meminta Anda memperbaiki data.</p>
        </div>
    @endif

    @if ($ditolak && $bisaEdit)
        <div class="mb-4 px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm">
            <p class="font-semibold">Pengajuan Anda ditolak.</p>
            <p class="mt-1">Anda dapat memperbaiki data lalu menyimpannya kembali untuk diajukan ulang.</p>
            @if ($catatanTolak)<p class="mt-1 break-words">Catatan petugas: {{ $catatanTolak }}</p>@endif
        </div>
    @endif

    @unless ($bisaEdit)
        <div class="mb-4 px-4 py-3 rounded-lg bg-success-container text-on-success-container text-sm flex items-start gap-2">
            <span class="material-symbols-outlined text-[18px] shrink-0 mt-px">lock</span>
            <p>Pengajuan sedang diproses petugas atau sudah selesai, sehingga data tidak dapat diubah lagi.</p>
        </div>
    @endunless

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <p class="font-semibold mb-1">Periksa kembali data Anda:</p>
            <ul class="list-disc list-inside space-y-0.5 break-words">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $dtsen->exists ? route('masyarakat.dtsen.lengkapi.store', $dtsen) : route('masyarakat.dtsen.baru.store') }}"
          class="space-y-4 sm:space-y-6 pb-44 sm:pb-0" id="form-lengkapi-dtsen">
        @csrf

        {{-- ===== DATA DIRI ===== --}}
        <div class="bg-white ring-1 ring-outline-variant/40 shadow-sm rounded-3xl p-5">
            <h2 class="font-extrabold text-sm text-brand mb-1 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">badge</span></span>
                Data Diri
            </h2>
            <p class="text-xs text-on-surface-variant mb-4">
                Isi sesuai KTP dan Kartu Keluarga. Data yang sudah kami miliki terkunci otomatis.
            </p>

            <div class="grid grid-cols-1 gap-4">
                @foreach ($fields as $field => $config)
                    @php
                        // Prioritas: isian sebelumnya (validasi gagal) -> nilai awal otomatis -> data detail tersimpan
                        $nilai = old($field, $prefill[$field] ?? ($detail->$field ?? null));
                        if ($config['type'] === 'date' && $nilai && ! old($field)) {
                            $nilai = \Carbon\Carbon::parse($nilai)->format('Y-m-d');
                        }
                        $otomatis = ! old($field) && isset($asal[$field]) && filled($nilai);

                        // Nilai angka 16 digit harus valid; kalau tidak, field dibiarkan terbuka supaya warga bisa memperbaiki
                        $valid = empty($config['numeric']) || preg_match('/^\d{16}$/', (string) $nilai);

                        // Terkunci: sudah ada isinya & valid, bukan field bebas, dan tidak sedang diminta perbaikan
                        $kunci = $bisaEdit && ! $perluPerbaikan && ($config['lock'] ?? true)
                            && ! old($field) && filled($nilai) && $valid;
                    @endphp
                    <div class="min-w-0">
                        <label for="{{ $field }}" class="block text-xs font-semibold mb-1">
                            {{ $config['label'] }} <span class="text-error">*</span>
                        </label>

                        @if ($config['type'] === 'textarea')
                            <textarea id="{{ $field }}" name="{{ $field }}" rows="3" required
                                      @disabled(! $bisaEdit) @readonly($kunci)
                                      class="{{ $inputClass }} {{ $kunci ? $kunciClass : '' }}">{{ $nilai }}</textarea>
                        @else
                            <input id="{{ $field }}" type="{{ $config['type'] }}" name="{{ $field }}" required
                                   value="{{ $nilai }}" @disabled(! $bisaEdit) @readonly($kunci)
                                   @if (!empty($config['numeric'])) inputmode="numeric" pattern="[0-9]{16}" maxlength="16" @endif
                                   class="{{ $inputClass }} {{ $kunci ? $kunciClass : '' }}" />
                        @endif

                        @if ($otomatis && $bisaEdit && ! $kunci)
                            <p class="mt-1 text-[11px] text-on-surface-variant flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px] text-brand">auto_fix_high</span>
                                Terisi otomatis dari {{ $asal[$field] }}
                            </p>
                        @endif

                        @error($field)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== LAMPIRAN (dari menu Dokumen bila sudah ada; kalau belum, diunggah di sini) ===== --}}
        <div class="bg-white ring-1 ring-outline-variant/40 shadow-sm rounded-3xl p-5">
            <h2 class="font-extrabold text-sm text-brand mb-1 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">attach_file</span></span>
                Lampiran
            </h2>
            <p class="text-xs text-on-surface-variant mb-4">
                Berkas yang sudah ada di menu
                <a href="{{ route('masyarakat.dokumen') }}" class="text-primary font-semibold underline">Dokumen</a>
                dipakai otomatis. Kalau belum ada, unggah di sini, berkas akan tersimpan juga di menu Dokumen.
            </p>

            <div class="grid grid-cols-1 gap-4">
                @foreach ($lampiranFields as $field => $config)
                    @php
                        $file = $lampiran->$field ?? null;
                        $isPdf = $file && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf';
                        // Belum terpasang di pengajuan: pakai dokumen terbaru dari menu Dokumen sebagai pratinjau
                        $dok = $file ? null : \App\Support\DokumenMasyarakat::terbaru(auth()->id(), $config['jenis']);

                        // Input unggah muncul bila belum ada berkas sama sekali, atau saat petugas meminta perbaikan (ganti berkas)
                        $bisaUnggah = $bisaEdit && ((! $file && ! $dok) || $perluPerbaikan);
                        $wajibUnggah = $config['required'] && ! $file && ! $dok;
                    @endphp
                    <div class="min-w-0">
                        <p class="block text-xs font-semibold mb-2">
                            {{ $config['label'] }}
                            @if ($config['required']) <span class="text-error">*</span> @endif
                        </p>

                        <div class="flex items-center gap-3">
                            <div id="thumb-{{ $field }}" class="w-20 h-20 shrink-0 rounded-lg border border-outline-variant/40 bg-surface-container overflow-hidden flex items-center justify-center">
                                @if ($file && ! $isPdf)
                                    <img src="{{ asset('storage/' . $file) }}" alt="{{ $config['label'] }}" class="w-full h-full object-cover">
                                @elseif ($file && $isPdf)
                                    <span class="material-symbols-outlined text-[32px] text-error/70">picture_as_pdf</span>
                                @elseif ($dok && $dok->isGambar())
                                    <img src="{{ $dok->url() }}" alt="{{ $config['label'] }}" class="w-full h-full object-cover">
                                @elseif ($dok && $dok->isPdf())
                                    <span class="material-symbols-outlined text-[32px] text-error/70">picture_as_pdf</span>
                                @else
                                    <span class="material-symbols-outlined text-[26px] text-on-surface-variant/50">image_not_supported</span>
                                @endif
                            </div>
                            <div class="min-w-0 text-xs">
                                @if ($file)
                                    <p class="text-success font-semibold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Terpasang di pengajuan
                                    </p>
                                    <a href="{{ asset('storage/' . $file) }}" target="_blank" rel="noopener" data-lihat="{{ $isPdf ? 'pdf' : 'gambar' }}" data-judul="{{ $config['label'] }}" class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3.5 py-2 text-xs font-semibold text-primary active:bg-primary/20">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span> Lihat berkas
                                    </a>
                                @elseif ($dok)
                                    <p class="text-success font-semibold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Terpasang otomatis dari Dokumen
                                    </p>
                                    <a href="{{ $dok->url() }}" target="_blank" rel="noopener" data-lihat="{{ $dok->isPdf() ? 'pdf' : 'gambar' }}" data-judul="{{ $config['label'] }}" class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3.5 py-2 text-xs font-semibold text-primary active:bg-primary/20">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span> Lihat berkas
                                    </a>
                                @else
                                    <p id="info-{{ $field }}" class="{{ $config['required'] ? 'text-error font-semibold' : 'text-on-surface-variant' }}">
                                        Belum ada dokumen.
                                    </p>
                                    @if ($bisaUnggah)
                                        <p id="petunjuk-{{ $field }}" class="text-on-surface-variant">Unggah berkas di bawah ini.</p>
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if ($bisaUnggah)
                            <div class="mt-2.5">
                                @if ($file || $dok)
                                    <p class="text-[11px] text-on-surface-variant mb-1">Ganti berkas (opsional):</p>
                                @endif
                                <input type="file" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf"
                                       @required($wajibUnggah) class="{{ $inputFile }}">
                                <p class="mt-1 text-[11px] text-on-surface-variant">JPG, PNG, atau PDF, maksimal 2 MB.</p>
                            </div>
                        @endif

                        @error($field)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Mobile: bar fixed selebar layar, latar solid + bayangan. Desktop: tombol biasa. --}}
        @if ($bisaEdit)
            <div style="bottom:var(--m-nav-h,0px)" class="fixed inset-x-0 z-30 px-4 pt-3 pb-3
                        bg-surface border-t border-outline-variant/40 shadow-[0_-4px_12px_rgba(0,0,0,0.08)]
                        sm:static sm:inset-auto sm:z-auto sm:p-0 sm:bg-transparent sm:border-0 sm:shadow-none">
                <button type="submit" id="btn-submit"
                        class="w-full sm:w-auto sm:px-8 flex items-center justify-center gap-2 py-3.5 rounded-full bg-primary shadow-md text-white font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    {{ $ditolak ? 'Simpan & Ajukan Ulang' : ($dtsen->exists ? 'Simpan Data' : 'Kirim Pengajuan') }}
                </button>
            </div>
        @endif
    </form>

    <script>
        // Pratinjau langsung saat berkas dipilih (sebelum tersimpan); server tetap memeriksa ulang saat dikirim
        (function () {
            var form = document.getElementById('form-lengkapi-dtsen');
            if (!form) return;

            form.querySelectorAll('input[type="file"]').forEach(function (input) {
                var thumb = document.getElementById('thumb-' + input.name);
                if (!thumb) return;

                var info = document.getElementById('info-' + input.name);
                var petunjuk = document.getElementById('petunjuk-' + input.name);
                var thumbAwal = thumb.innerHTML;
                var infoAwal = info ? info.textContent : '';
                var infoKelas = info ? info.className : '';
                var petunjukAwal = petunjuk ? petunjuk.textContent : '';
                var urlLama = null;

                function pulihkan() {
                    thumb.innerHTML = thumbAwal;
                    if (info) { info.textContent = infoAwal; info.className = infoKelas; }
                    if (petunjuk) { petunjuk.textContent = petunjukAwal; }
                }

                input.addEventListener('change', function () {
                    var f = input.files && input.files[0];

                    if (urlLama) { URL.revokeObjectURL(urlLama); urlLama = null; }

                    if (!f) { pulihkan(); return; }

                    if (f.size > 2 * 1024 * 1024) {
                        alert('Ukuran berkas melebihi 2 MB. Pilih berkas yang lebih kecil.');
                        input.value = '';
                        pulihkan();
                        return;
                    }

                    if (f.type.indexOf('image/') === 0) {
                        urlLama = URL.createObjectURL(f);
                        thumb.innerHTML = '<img src="' + urlLama + '" alt="Pratinjau" class="w-full h-full object-cover">';
                    } else {
                        thumb.innerHTML = '<span class="material-symbols-outlined text-[32px] text-error/70">picture_as_pdf</span>';
                    }

                    if (info) { info.className = 'text-success font-semibold'; info.textContent = 'Berkas dipilih: ' + f.name; }
                    if (petunjuk) { petunjuk.textContent = 'Tersimpan saat Anda menekan tombol kirim.'; }
                });
            });
        })();
    </script>

    @include('masyarakat._lihat-berkas')
@endsection
