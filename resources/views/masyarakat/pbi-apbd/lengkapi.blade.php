@extends('layouts.masyarakat')

@php
    use App\Models\GcKriteriaParameter as GcP;
    use App\Support\PbiGcForm;

    // Koordinat / lintang / bujur diisi otomatis dari lokasi rumah dan TIDAK ditampilkan ke warga.
    $tersembunyi = fn ($p) => $p->tipe_input === 'koordinat' || PbiGcForm::lintangBujur($p);
    $paramTersembunyi = $parameters->filter($tersembunyi)->values();
    $perKelompok = $parameters->reject($tersembunyi)->groupBy('kelompok');
@endphp

@section('title', 'Lengkapi Data - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Lengkapi Data')
@section('back', route('masyarakat.dashboard'))

@section('content')
    @unless (request()->attributes->get('is_mobile'))
        <a href="{{ route('masyarakat.dashboard') }}"
           class="inline-flex items-center gap-1 text-sm text-on-surface-variant mb-3 sm:mb-4 py-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali
        </a>
    @endunless

    <h1 class="text-xl sm:text-2xl font-extrabold text-brand mb-2">Lengkapi Data Pengajuan</h1>
    <p class="mb-5 sm:mb-6 inline-flex max-w-full items-center gap-1.5 rounded-full bg-brand-soft ring-1 ring-brand/15 px-3.5 py-1.5 text-xs text-on-surface-variant">
        <span class="material-symbols-outlined text-[16px] text-brand shrink-0">confirmation_number</span>
        <span class="break-all">No. Registrasi: <strong class="text-on-surface">{{ $pbiApbd->no_registrasi }}</strong></span>
    </p>

    @if ($pbiApbd->dikembalikan_ke_masyarakat)
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-warning-container text-on-warning-container text-sm">
            <p class="font-semibold">Petugas kelurahan meminta Anda memperbaiki data.</p>
            @if ($pbiApbd->catatan_kelurahan)<p class="mt-1 break-words">Catatan: {{ $pbiApbd->catatan_kelurahan }}</p>@endif
        </div>
    @endif

    @if ($pbiApbd->status === 'ditolak' && $bisaEdit)
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-warning-container text-on-warning-container text-sm">
            <p class="font-semibold">Pengajuan Anda ditolak.</p>
            <p class="mt-1">Anda dapat memperbaiki data lalu menyimpannya kembali untuk diajukan ulang.</p>
            @if ($pbiApbd->catatan_kelurahan)<p class="mt-1 break-words">Catatan: {{ $pbiApbd->catatan_kelurahan }}</p>@endif
        </div>
    @endif

    @unless ($bisaEdit)
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-success-container text-on-success-container text-sm flex items-start gap-2">
            <span class="material-symbols-outlined text-[18px] shrink-0 mt-px">lock</span>
            <p>Pengajuan sudah disetujui, sehingga data tidak dapat diubah lagi.</p>
        </div>
    @endunless

    @if ($errors->any())
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-error-container text-on-error-container text-sm">
            <p class="font-semibold mb-1">Periksa kembali data Anda:</p>
            <ul class="list-disc list-inside space-y-0.5 break-words">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- pb-28 di mobile: ruang kosong supaya konten terakhir tidak tertutup tombol Simpan yang fixed --}}
    <form method="POST" action="{{ route('masyarakat.pbi-apbd.lengkapi.store', $pbiApbd) }}" enctype="multipart/form-data"
          class="space-y-4 sm:space-y-6 pb-44 sm:pb-0" id="form-lengkapi"
          @if ($bisaDraft ?? false) data-draft-url="{{ route('masyarakat.pbi-apbd.lengkapi.store', $pbiApbd) }}" @endif>
        @csrf

        {{-- ===== LOKASI (otomatis, tidak ditampilkan ke warga) ===== --}}
        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $pbiApbd->latitude) }}">
        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $pbiApbd->longitude) }}">

        {{-- Parameter koordinat / lintang / bujur: terisi otomatis oleh skrip, tidak terlihat warga --}}
        @foreach ($paramTersembunyi as $p)
            @php $namaP = \Illuminate\Support\Str::lower($p->nama); @endphp
            <input type="hidden" name="jawaban[{{ $p->id }}]"
                   value="{{ old('jawaban.' . $p->id, $nilai[$p->id] ?? '') }}"
                   @disabled(! $bisaEdit)
                   @if ($p->tipe_input === 'koordinat') data-gc-koordinat @endif
                   @if (str_contains($namaP, 'lintang')) data-gc-lat @endif
                   @if (str_contains($namaP, 'bujur')) data-gc-lng @endif>
        @endforeach

        {{-- Kartu status lokasi: HANYA muncul bila pengambilan lokasi gagal (agar warga tahu kenapa tombol simpan terkunci) --}}
        @if ($bisaEdit)
            <div id="geo-card" class="hidden rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6">
                <h2 class="mb-1.5 flex items-center gap-3 text-base font-extrabold text-on-surface">
                    <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">location_on</span>
                    </span>
                    Izin Lokasi Diperlukan
                </h2>
                <p class="text-xs text-on-surface-variant mb-3">
                    Pastikan Anda berada di rumah/lokasi yang didaftarkan, lalu izinkan akses lokasi di browser.
                </p>

                <div id="geo-status" class="flex items-start gap-2 text-sm p-3.5 rounded-2xl bg-surface-container">
                    <span class="material-symbols-outlined text-[18px] shrink-0 mt-px animate-spin" id="geo-icon">progress_activity</span>
                    <span id="geo-text" class="min-w-0 break-words">Mengambil lokasi Anda...</span>
                </div>

                <button type="button" id="btn-retry-geo"
                        class="hidden mt-3 w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-full border border-primary text-primary text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">refresh</span>
                    Coba ambil ulang lokasi
                </button>
            </div>
        @endif

        {{-- ===== PERTANYAAN: semua parameter Master Kriteria Ground Check yang aktif ===== --}}
        @if ($parameters->isEmpty())
            <div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6">
                <p class="text-sm text-on-surface-variant">Pertanyaan belum tersedia. Silakan hubungi petugas.</p>
            </div>
        @endif

        @php $no = 0; $pertama = true; @endphp
        @foreach (GcP::KELOMPOK as $kel => $judul)
            @php $isi = $perKelompok->get($kel); @endphp
            @continue(! $isi || $isi->isEmpty())

            <div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6">
                <h2 class="mb-1.5 flex items-center gap-3 text-base font-extrabold text-on-surface">
                    <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">checklist</span>
                    </span>
                    {{ $judul }}
                </h2>
                @if ($pertama)
                    <p class="text-xs text-on-surface-variant mb-4">Jawab sesuai keadaan sebenarnya. Petugas kelurahan akan mengecek langsung ke lapangan. Isian bertanda <span class="text-error">*</span> wajib diisi.</p>
                    @php $pertama = false; @endphp
                @else
                    <div class="mb-4"></div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-x-5">
                    @foreach ($isi as $p)
                        @include('masyarakat.pbi-apbd._gc_field', ['p' => $p, 'nomor' => ++$no])
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Mobile: bar fixed selebar layar, latar solid + bayangan. Desktop: tombol biasa. --}}
        @if ($bisaEdit)
            <div style="bottom:var(--m-nav-h,0px)" class="fixed inset-x-0 z-30 px-4 pt-3 pb-3
                        bg-surface border-t border-outline-variant/40 shadow-[0_-4px_12px_rgba(0,0,0,0.08)]
                        sm:static sm:inset-auto sm:z-auto sm:p-0 sm:bg-transparent sm:border-0 sm:shadow-none">
                @if ($bisaDraft ?? false)
                    <p id="draft-status" aria-live="polite"
                       class="mb-2 text-center text-[11px] font-medium text-on-surface-variant sm:text-left empty:hidden"></p>
                @endif
                <button type="submit" id="btn-submit" disabled
                        class="w-full sm:w-auto sm:px-8 flex items-center justify-center gap-2 py-3.5 rounded-full bg-primary shadow-md text-white font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    {{ $pbiApbd->status === 'ditolak' ? 'Simpan & Ajukan Ulang' : 'Simpan Data' }}
                </button>
            </div>
        @endif
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');
    var geoCard = document.getElementById('geo-card');
    var statusText = document.getElementById('geo-text');
    var statusIcon = document.getElementById('geo-icon');
    var statusBox = document.getElementById('geo-status');
    var btnRetry = document.getElementById('btn-retry-geo');
    var btnSubmit = document.getElementById('btn-submit');

    // Mode baca saja (sudah disetujui): tidak ada tombol simpan / pengambilan lokasi
    if (!btnSubmit) { return; }

    function setStatus(text, icon, spin, state) {
        statusText.textContent = text;
        statusIcon.textContent = icon;
        statusIcon.classList.toggle('animate-spin', !!spin);

        // Kartu hanya terlihat saat error; saat memuat / berhasil, lokasi diam-diam tersimpan
        geoCard.classList.toggle('hidden', state !== 'error');

        statusBox.classList.remove('bg-surface-container', 'bg-error-container', 'text-on-error-container', 'bg-secondary-container');
        if (state === 'error') {
            statusBox.classList.add('bg-error-container', 'text-on-error-container');
        } else {
            statusBox.classList.add('bg-surface-container');
        }
    }

    // Isian koordinat/lintang/bujur di daftar pertanyaan (tersembunyi) ikut terisi dari titik lokasi rumah
    function isiKoordinat(lat, lng) {
        var a = lat.toFixed(6), b = lng.toFixed(6);
        var k = document.querySelector('[data-gc-koordinat]');
        var la = document.querySelector('[data-gc-lat]');
        var lo = document.querySelector('[data-gc-lng]');
        if (k && !k.disabled) { k.value = a + ', ' + b; }
        if (la && !la.disabled) { la.value = a; }
        if (lo && !lo.disabled) { lo.value = b; }
    }

    function ambilLokasi() {
        btnRetry.classList.add('hidden');

        if (!navigator.geolocation) {
            setStatus('Browser Anda tidak mendukung pengambilan lokasi otomatis.', 'error', false, 'error');
            btnRetry.classList.remove('hidden');
            return;
        }

        setStatus('Mengambil lokasi Anda...', 'progress_activity', true);

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                latInput.value = pos.coords.latitude;
                lngInput.value = pos.coords.longitude;
                isiKoordinat(pos.coords.latitude, pos.coords.longitude);
                // Sukses: koordinat tidak ditampilkan ke warga
                setStatus('Lokasi terdeteksi.', 'check_circle', false);
                btnSubmit.disabled = false;
            },
            function (err) {
                var msg = 'Gagal mengambil lokasi. Coba lagi.';
                if (!window.isSecureContext) {
                    msg = 'Lokasi hanya bisa diambil lewat koneksi aman (HTTPS). Hubungi petugas.';
                } else if (err.code === 1) {
                    msg = 'Izin lokasi ditolak. Aktifkan izin lokasi untuk browser ini di Pengaturan, lalu coba lagi.';
                } else if (err.code === 2) {
                    msg = 'Lokasi tidak tersedia. Pastikan GPS/Location Services aktif.';
                } else if (err.code === 3) {
                    msg = 'Waktu pengambilan lokasi habis. Coba lagi di tempat yang sinyalnya lebih baik.';
                }
                setStatus(msg, 'error', false, 'error');
                btnRetry.classList.remove('hidden');
                if (latInput.value && lngInput.value) { btnSubmit.disabled = false; }
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }

    btnRetry.addEventListener('click', ambilLokasi);
    if (latInput.value && lngInput.value) { btnSubmit.disabled = false; }
    ambilLokasi();
})();
</script>

{{-- Lapangan usaha -> pilihan pekerjaan menyesuaikan --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lap = document.querySelector('[data-gc-lapangan]');
    var pk = document.querySelector('[data-gc-pekerjaan]');
    if (!lap || !pk) { return; }

    var peta = {};
    try { peta = JSON.parse(pk.dataset.peta || '{}'); } catch (e) { peta = {}; }

    function isi(pertama) {
        var daftar = peta[lap.value] || [];
        var simpan = pertama ? pk.dataset.nilai : pk.value;
        pk.innerHTML = '';
        pk.add(new Option(lap.value ? '- Pilih -' : '- Pilih lapangan usaha dahulu -', ''));
        daftar.forEach(function (n) { pk.add(new Option(n, n)); });
        pk.value = daftar.indexOf(simpan) >= 0 ? simpan : '';
    }

    lap.addEventListener('change', function () { isi(false); });
    isi(true);
});
</script>

{{-- Ketenagakerjaan: bila "Apakah responden bekerja ...?" dijawab Tidak, field lainnya dinonaktifkan & dikosongkan. --}}
<script>
window.gcKerja = @json($kerja);

document.addEventListener('DOMContentLoaded', function () {
    var cfg = window.gcKerja;
    var form = document.getElementById('form-lengkapi');
    if (!cfg || !form) { return; }

    var bisaEdit = !!document.getElementById('btn-submit');

    function kontrol(id) {
        return Array.prototype.slice.call(form.querySelectorAll('[name="jawaban[' + id + ']"],[name="jawaban[' + id + '][]"]'));
    }

    function terapkan() {
        var gate = kontrol(cfg.gate)[0];
        var ya = gate && gate.value === 'Ya';

        cfg.dependen.forEach(function (id) {
            kontrol(id).forEach(function (el) {
                el.disabled = !bisaEdit || !ya;
                if (!ya) {
                    if (el.type === 'checkbox' || el.type === 'radio') { el.checked = false; el.dispatchEvent(new Event('change', { bubbles: true })); }
                    else if (el.type !== 'file') { el.value = ''; el.dispatchEvent(new Event('change', { bubbles: true })); }
                }
                var baris = el.closest('.min-w-0');
                if (baris) { baris.classList.toggle('opacity-50', !ya); }
            });
        });
    }

    form.addEventListener('change', function (e) {
        if (e.target.name === 'jawaban[' + cfg.gate + ']') { terapkan(); }
    });

    terapkan();
});
</script>

{{-- Draft otomatis: selama pengajuan belum pernah diajukan lewat "Simpan Data", isian yang sudah terisi
     disimpan sebagian ke server (tanpa mengubah status). Persentase di Beranda mengikuti isian ini. --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-lengkapi');
    var info = document.getElementById('draft-status');
    if (!form || !info || !form.dataset.draftUrl) { return; }

    var timer = null, jalan = false, ulang = false;

    function simpan(keepalive) {
        clearTimeout(timer);
        timer = null;
        if (jalan) { ulang = true; return; }

        var fd = new FormData(form);
        // Foto tidak ikut draft; foto baru dikirim saat "Simpan Data"
        form.querySelectorAll('input[type=file]').forEach(function (f) { fd.delete(f.name); });
        fd.set('aksi', 'draft');

        jalan = true;
        info.textContent = 'Menyimpan draft...';

        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: fd,
            keepalive: !!keepalive,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) {
            if (!r.ok) { throw new Error(r.status); }
            return r.json();
        }).then(function (d) {
            info.textContent = d.tersimpan ? 'Draft tersimpan otomatis (' + d.persen + '% terisi)' : '';
        }).catch(function () {
            info.textContent = 'Draft belum tersimpan. Periksa koneksi internet Anda.';
        }).then(function () {
            jalan = false;
            if (ulang) { ulang = false; jadwal(); }
        });
    }

    function jadwal() {
        clearTimeout(timer);
        timer = setTimeout(simpan, 2000);
    }

    // Hanya perubahan dari warga (isTrusted). Event buatan skrip (mis. mengosongkan field saat "bekerja" = Tidak)
    // tidak boleh membuat draft sendiri hanya karena halaman dibuka.
    ['change', 'input'].forEach(function (nama) {
        form.addEventListener(nama, function (e) {
            if (e.isTrusted && e.target.type !== 'search') { jadwal(); }
        });
    });

    // Ya/Tidak dan lembar pilihan mengubah nilai lewat skrip, jadi klik aslinya yang dipantau
    document.addEventListener('click', function (e) {
        if (e.isTrusted && e.target.closest && e.target.closest('[data-gc-yn-opt], .gcs-item')) { jadwal(); }
    });

    // Tutup tab / pindah aplikasi: simpan sekarang bila masih ada yang belum terkirim
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden' && timer) { simpan(true); }
    });
    window.addEventListener('pagehide', function () { if (timer) { simpan(true); } });

    // Saat menekan "Simpan Data", batalkan draft yang tertunda
    form.addEventListener('submit', function () { clearTimeout(timer); timer = null; });
});
</script>
@endpush
