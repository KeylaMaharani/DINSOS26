@extends('layouts.masyarakat')

@section('title', 'Lengkapi Data - SOLID Dinas Sosial Kota Bogor')

@section('content')
    <a href="{{ route('masyarakat.dashboard') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant mb-4">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali
    </a>

    <h1 class="text-xl font-bold text-primary mb-1">Lengkapi Data Pengajuan</h1>
    <p class="text-sm text-on-surface-variant mb-6">No. Registrasi: <strong>{{ $pbiApbn->no_registrasi }}</strong></p>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <p class="font-semibold mb-1">Periksa kembali data Anda:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('masyarakat.pbi-apbn.lengkapi.store', $pbiApbn) }}"
          enctype="multipart/form-data" class="space-y-6" id="form-lengkapi">
        @csrf

        {{-- ===== KOORDINAT ===== --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
            <h2 class="font-semibold text-sm mb-1 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px] text-secondary">location_on</span>
                Titik Lokasi Rumah
            </h2>
            <p class="text-xs text-on-surface-variant mb-3">
                Pastikan Anda berada di rumah/lokasi yang didaftarkan, lalu izinkan akses lokasi di browser.
            </p>

            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $pbiApbn->latitude) }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $pbiApbn->longitude) }}">

            <div id="geo-status" class="flex items-center gap-2 text-sm p-3 rounded-lg bg-surface-container">
                <span class="material-symbols-outlined text-[18px] animate-spin" id="geo-icon">progress_activity</span>
                <span id="geo-text">Mengambil lokasi Anda...</span>
            </div>

            <button type="button" id="btn-retry-geo"
                    class="hidden mt-3 text-xs font-semibold text-primary underline">
                Coba ambil ulang lokasi
            </button>
        </div>

        {{-- ===== LAMPIRAN ===== --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
            <h2 class="font-semibold text-sm mb-3 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px] text-secondary">attach_file</span>
                Lampiran Persyaratan
            </h2>

            <div class="grid sm:grid-cols-2 gap-4">
                @php
                    $lampiranFields = [
                        'scan_ktp' => 'Scan KTP',
                        'scan_kk' => 'Scan Kartu Keluarga',
                        'foto_rumah' => 'Foto Rumah Pemohon',
                        'foto_kamar_mandi' => 'Foto Kamar Mandi',
                        'foto_selfie_ktp' => 'Foto Selfie dengan KTP',
                        'surat_rawat_inap' => 'Surat Ket. Rawat Inap (jika ada)',
                        'screenshot_pembaharuan_desil' => 'Screenshot Pembaharuan Desil',
                        'screenshot_dtsen' => 'Screenshot DTSEN',
                    ];
                @endphp

                @foreach ($lampiranFields as $field => $label)
                    <div>
                        <label class="block text-xs font-semibold mb-1">{{ $label }}</label>

                        @if ($pbiApbn->{$field})
                            <div class="w-20 mb-2">
                                <x-lampiran-preview
                                    :src="asset('storage/' . $pbiApbn->{$field})"
                                    :label="$label" />
                            </div>
                            <div class="flex items-center gap-2 mb-1.5 text-xs text-success">
                                <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                Sudah diunggah — pilih file baru untuk mengganti.
                            </div>
                        @endif

                        <input type="file" name="{{ $field }}" accept="image/*,.pdf"
                               class="block w-full text-xs border border-outline-variant/40 rounded-lg file:mr-3 file:py-2 file:px-3 file:border-0 file:bg-surface-container file:text-xs file:font-semibold" />
                        @error($field) <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" id="btn-submit" disabled
                class="w-full flex items-center justify-center gap-2 py-3 rounded-full bg-primary text-white font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="material-symbols-outlined text-[18px]">save</span>
            Simpan Data
        </button>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');
    var statusText = document.getElementById('geo-text');
    var statusIcon = document.getElementById('geo-icon');
    var btnRetry = document.getElementById('btn-retry-geo');
    var btnSubmit = document.getElementById('btn-submit');

    function setStatus(text, icon, spin) {
        statusText.textContent = text;
        statusIcon.textContent = icon;
        statusIcon.classList.toggle('animate-spin', !!spin);
    }

    function ambilLokasi() {
        btnRetry.classList.add('hidden');

        if (!navigator.geolocation) {
            setStatus('Browser Anda tidak mendukung pengambilan lokasi otomatis.', 'error', false);
            btnRetry.classList.remove('hidden');
            return;
        }

        setStatus('Mengambil lokasi Anda...', 'progress_activity', true);

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                latInput.value = pos.coords.latitude;
                lngInput.value = pos.coords.longitude;
                setStatus('Lokasi berhasil diambil (' + pos.coords.latitude.toFixed(5) + ', ' + pos.coords.longitude.toFixed(5) + ')', 'check_circle', false);
                btnSubmit.disabled = false;
            },
            function (err) {
                setStatus('Gagal mengambil lokasi: izinkan akses lokasi lalu coba lagi.', 'error', false);
                btnRetry.classList.remove('hidden');
                // Kalau sebelumnya sudah pernah tersimpan, tetap izinkan submit tanpa update lokasi
                if (latInput.value && lngInput.value) {
                    btnSubmit.disabled = false;
                }
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    btnRetry.addEventListener('click', ambilLokasi);

    // Kalau sudah pernah ada koordinat tersimpan sebelumnya, submit boleh langsung aktif
    if (latInput.value && lngInput.value) {
        btnSubmit.disabled = false;
    }

    ambilLokasi();
})();
</script>
@endpush
