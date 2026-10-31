@extends('layouts.masyarakat')

@section('title', 'Lengkapi Data DTSEN - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Lengkapi Data')
@section('back', route('masyarakat.dashboard'))

@php
    // $dtsen     : DtsenPermohonan milik user yang login
    // $bisaEdit  : bool, masyarakat masih boleh mengisi / memperbaiki atau tidak
    $detail = $dtsen->detail;
    $lampiran = $dtsen->lampiran;

    // Pengajuan ditolak: tampilkan catatan petugas dari log penolakan terakhir
    $ditolak = $dtsen->status === \App\Models\DtsenPermohonan::STATUS_DITOLAK;
    $catatanTolak = $ditolak ? optional($dtsen->logs->where('taskname', 'Ditolak')->last())->catatan : null;

    $fields = [
        'nik' => ['label' => 'NIK', 'type' => 'text', 'numeric' => true],
        'nama' => ['label' => 'Nama Lengkap', 'type' => 'text'],
        'no_kk' => ['label' => 'No. KK', 'type' => 'text', 'numeric' => true],
        'tanggal_lahir' => ['label' => 'Tanggal Lahir', 'type' => 'date'],
        'alamat' => ['label' => 'Alamat', 'type' => 'textarea'],
    ];

    $lampiranFields = [
        'scan_ktp' => ['label' => 'Scan KTP', 'required' => false],
        'screenshot_dtsen' => ['label' => 'Screenshot DTSEN', 'required' => true],
    ];

    $inputClass = 'w-full rounded-xl border border-outline-variant/60 px-3.5 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand disabled:bg-surface-container disabled:text-on-surface-variant';
    $fileClass = 'mt-2 block w-full text-xs text-on-surface-variant file:mr-3 file:rounded-full file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-primary disabled:opacity-50';
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
                No. Permohonan: <strong>{{ $dtsen->no_permohonan ?? $dtsen->nik }}</strong>
            </p>
        </div>
    </div>

    @if (($dtsen->dikembalikan_ke_masyarakat ?? false) && $bisaEdit)
        <div class="mb-4 px-4 py-3 rounded-lg bg-warning-container text-on-warning-container text-sm">
            <p class="font-semibold">Petugas meminta Anda memperbaiki data.</p>
            @if (!empty($dtsen->catatan_kelurahan))<p class="mt-1 break-words">Catatan: {{ $dtsen->catatan_kelurahan }}</p>@endif
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
            <p>Pengajuan sudah selesai diproses, sehingga data tidak dapat diubah lagi.</p>
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

    <form method="POST" action="{{ route('masyarakat.dtsen.lengkapi.store', $dtsen) }}" enctype="multipart/form-data"
          class="space-y-4 sm:space-y-6 pb-44 sm:pb-0" id="form-lengkapi-dtsen">
        @csrf

        {{-- ===== DATA DIRI ===== --}}
        <div class="bg-white ring-1 ring-outline-variant/40 shadow-sm rounded-3xl p-5">
            <h2 class="font-extrabold text-sm text-brand mb-1 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">badge</span></span>
                Data Diri
            </h2>
            <p class="text-xs text-on-surface-variant mb-4">Isi sesuai KTP dan Kartu Keluarga. Petugas akan memeriksa kesesuaian datanya.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-5">
                @foreach ($fields as $field => $config)
                    @php
                        $nilai = old($field, $detail->$field ?? null);
                        if ($config['type'] === 'date' && $nilai && ! old($field)) {
                            $nilai = \Carbon\Carbon::parse($nilai)->format('Y-m-d');
                        }
                    @endphp
                    <div class="min-w-0 {{ $config['type'] === 'textarea' ? 'sm:col-span-2' : '' }}">
                        <label for="{{ $field }}" class="block text-xs font-semibold mb-1">
                            {{ $config['label'] }} <span class="text-error">*</span>
                        </label>

                        @if ($config['type'] === 'textarea')
                            <textarea id="{{ $field }}" name="{{ $field }}" rows="3" required @disabled(! $bisaEdit)
                                      class="{{ $inputClass }}">{{ $nilai }}</textarea>
                        @else
                            <input id="{{ $field }}" type="{{ $config['type'] }}" name="{{ $field }}" required
                                   value="{{ $nilai }}" @disabled(! $bisaEdit)
                                   @if (!empty($config['numeric'])) inputmode="numeric" pattern="[0-9]{16}" maxlength="16" @endif
                                   class="{{ $inputClass }}" />
                        @endif

                        @error($field)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== LAMPIRAN ===== --}}
        <div class="bg-white ring-1 ring-outline-variant/40 shadow-sm rounded-3xl p-5">
            <h2 class="font-extrabold text-sm text-brand mb-1 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-brand-soft text-brand flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">attach_file</span></span>
                Lampiran
            </h2>
            <p class="text-xs text-on-surface-variant mb-4">
                Format JPG, PNG, atau PDF, maksimal 2 MB per file.
                @if ($bisaEdit) Pilih file baru hanya jika ingin mengunggah atau mengganti. @endif
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-5">
                @foreach ($lampiranFields as $field => $config)
                    @php
                        $file = $lampiran->$field ?? null;
                        $isPdf = $file && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf';
                    @endphp
                    <div class="min-w-0">
                        <label for="{{ $field }}" class="block text-xs font-semibold mb-2">
                            {{ $config['label'] }}
                            @if ($config['required']) <span class="text-error">*</span> @endif
                        </label>

                        <div class="flex items-center gap-3">
                            <div class="w-20 h-20 shrink-0 rounded-lg border border-outline-variant/40 bg-surface-container overflow-hidden flex items-center justify-center">
                                @if ($file && ! $isPdf)
                                    <img src="{{ asset('storage/' . $file) }}" alt="{{ $config['label'] }}" class="w-full h-full object-cover">
                                @elseif ($file && $isPdf)
                                    <span class="material-symbols-outlined text-[32px] text-error/70">picture_as_pdf</span>
                                @else
                                    <span class="material-symbols-outlined text-[26px] text-on-surface-variant/50">image_not_supported</span>
                                @endif
                            </div>
                            <div class="min-w-0 text-xs">
                                @if ($file)
                                    <p class="text-success font-semibold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Sudah diunggah
                                    </p>
                                    <a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-primary underline">Lihat berkas</a>
                                @else
                                    <p class="text-on-surface-variant">Belum ada berkas</p>
                                @endif
                            </div>
                        </div>

                        @if ($bisaEdit)
                            <input id="{{ $field }}" type="file" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf"
                                   data-max-mb="2" @required($config['required'] && ! $file)
                                   class="{{ $fileClass }}" />
                            <p class="text-error text-xs mt-1 hidden" data-file-error="{{ $field }}"></p>
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
                    {{ $ditolak ? 'Simpan & Ajukan Ulang' : 'Simpan Data' }}
                </button>
            </div>
        @endif
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var inputs = document.querySelectorAll('input[type="file"][data-max-mb]');
    var btnSubmit = document.getElementById('btn-submit');
    if (!inputs.length) { return; }

    function refresh() {
        var ada = document.querySelector('[data-file-error]:not(.hidden)');
        if (btnSubmit) { btnSubmit.disabled = !!ada; }
    }

    inputs.forEach(function (input) {
        input.addEventListener('change', function () {
            var err = document.querySelector('[data-file-error="' + input.name + '"]');
            var file = input.files && input.files[0];
            var max = parseFloat(input.dataset.maxMb) * 1024 * 1024;

            if (file && file.size > max) {
                err.textContent = 'Ukuran file melebihi ' + input.dataset.maxMb + ' MB. Pilih file yang lebih kecil.';
                err.classList.remove('hidden');
                input.value = '';
            } else {
                err.classList.add('hidden');
            }
            refresh();
        });
    });
})();
</script>
@endpush
