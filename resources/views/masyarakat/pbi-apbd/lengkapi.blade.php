@extends('layouts.masyarakat')

@php
    use App\Models\GcKriteriaParameter as GcP;
    use App\Support\PbiGcForm;

    // Koordinat / lintang / bujur diisi otomatis dari lokasi rumah dan TIDAK ditampilkan ke warga.
    // Foto kartu keluarga juga disembunyikan: memakai kartu "Scan KK" di bagian lampiran.
    $tersembunyi = fn ($p) => $p->tipe_input === 'koordinat' || PbiGcForm::lintangBujur($p);
    $paramTersembunyi = $parameters->filter($tersembunyi)->values();
    $perKelompok = $parameters
        ->reject($tersembunyi)
        ->reject(fn ($p) => PbiGcForm::isFotoKk($p))
        ->groupBy('kelompok');
@endphp

@section('title', 'Lengkapi Data - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Lengkapi Data')
@section('back', route('masyarakat.dashboard'))

@section('content')
<div class="gc-lebar">
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
            <p>
                {{ $pbiApbd->status === 'disetujui'
                    ? 'Pengajuan sudah disetujui, sehingga data tidak dapat diubah lagi.'
                    : 'Pengajuan sudah dikirim dan sedang diproses, sehingga tidak dapat diajukan lagi.' }}
            </p>
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

    {{-- Tombol Simpan berada di akhir form (tidak mengambang), jadi padding bawah form dibuat kecil. --}}
    <form method="POST" action="{{ route('masyarakat.pbi-apbd.lengkapi.store', $pbiApbd) }}" enctype="multipart/form-data"
          class="space-y-4 sm:space-y-6 pb-6" id="form-lengkapi"
          @if ($bisaDraft ?? false) data-draft-url="{{ route('masyarakat.pbi-apbd.lengkapi.store', $pbiApbd) }}" @endif>
        @csrf

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

        {{-- ===== PERTANYAAN: semua parameter Master Kriteria Ground Check yang aktif ===== --}}
        @if ($parameters->isEmpty())
            <div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6">
                <p class="text-sm text-on-surface-variant">Pertanyaan belum tersedia. Silakan hubungi petugas.</p>
            </div>
        @endif

        <div id="gc-wiz-head" class="rounded-2xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 overflow-hidden" hidden>
            <div id="gc-tabs" class="flex overflow-x-auto" role="tablist"></div>
            <div class="h-1.5 bg-brand-soft"><div id="gc-wiz-bar" class="h-full transition-all duration-300" style="width:0;background:#0a5ca8"></div></div>
            <p id="gc-wiz-persen" class="px-4 py-1.5 text-[11px] font-semibold text-on-surface-variant">0% terisi</p>
        </div>

        @php
            $lihat = $parameters->reject($tersembunyi)->reject(fn ($p) => PbiGcForm::isFotoKk($p))->values();
            $ambil = fn (array $kel) => $lihat
                ->filter(fn ($p) => in_array($p->kelompok, $kel, true))
                ->values();

            // Langkah 2 dibagi A-D mengikuti blok urutan (100/200/300/400/500) dari GcSosialEkonomiSeeder.
            $sosek = $ambil(['keluarga']);

            // Struktur mengikuti formulir Pendataan Keluarga: bagian 1-4 + lampiran
            $langkah = [
                ['judul' => 'Identitas Keluarga', 'ikon' => 'badge', 'lokasi' => true,
                    'bagian' => [[null, $ambil(['identitas_keluarga'])]]],
                ['judul' => 'Keterangan Sosial Ekonomi', 'ikon' => 'home',
                    'bagian' => [
                        ['A. Jenis Bangunan & Jumlah Penghuni', $sosek->where('urutan', '<', 200)],
                        ['B. Kepemilikan, Bukti Tanah, Nilai & Luas Lantai', $sosek->whereBetween('urutan', [200, 299])],
                        ['C. Kondisi Fisik & Sanitasi / Air / Listrik', $sosek->whereBetween('urutan', [300, 399])],
                        ['D. Pengeluaran & Pendapatan Keluarga', $sosek->whereBetween('urutan', [400, 499])],
                        ['E. Keterangan Tambahan', $sosek->where('urutan', '>=', 500)],
                    ]],
                ['judul' => 'Kepemilikan Aset', 'ikon' => 'inventory_2',
                    'bagian' => [
                        ['Aset bergerak', $ambil(['aset_bergerak'])],
                        ['Aset tidak bergerak', $ambil(['aset_tidak_bergerak'])],
                    ]],
                ['judul' => 'Keterangan Anggota Keluarga', 'ikon' => 'groups',
                    'bagian' => [
                        ['Data pribadi', $ambil(['kependudukan'])],
                        ['Pendidikan', $ambil(['pendidikan'])],
                        ['Ketenagakerjaan', $ambil(['ketenagakerjaan'])],
                        ['Kesehatan', $ambil(['kesehatan'])],
                    ]],
            ];
            $dokumentasi = $ambil(['dokumentasi']);
            $no = 0;
            $pertama = true;
        @endphp

        @foreach ($langkah as $l)
            @php $ada = collect($l['bagian'])->contains(fn ($b) => $b[1]->isNotEmpty()); @endphp
            @continue(! $ada && empty($l['lokasi']))

            <div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6"
                data-gc-step data-gc-judul="{{ $l['judul'] }}" data-gc-ikon="{{ $l['ikon'] }}">
                <h2 class="mb-1.5 flex items-center gap-3 text-base font-extrabold text-on-surface">
                    <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">{{ $l['ikon'] }}</span>
                    </span>
                    {{ $l['judul'] }}
                </h2>
                @if ($pertama)
                    <p class="text-xs text-on-surface-variant mb-4">Jawab sesuai keadaan sebenarnya. Petugas kelurahan akan mengecek langsung ke lapangan. Isian bertanda <span class="text-error">*</span> wajib diisi.</p>
                    @php $pertama = false; @endphp
                @else
                    <div class="mb-4"></div>
                @endif

                @foreach ($l['bagian'] as [$sub, $isi])
                    @continue($isi->isEmpty())
                    @if ($sub)
                        <h3 class="mt-6 mb-3 pb-1.5 border-b border-outline-variant/40 text-sm font-extrabold text-brand">{{ $sub }}</h3>
                    @endif
                    <div class="grid grid-cols-1 gap-5 max-w-xl">
                        @foreach ($isi as $p)
                            @include('masyarakat.pbi-apbd._gc_field', ['p' => $p, 'nomor' => ++$no])
                        @endforeach
                    </div>
                @endforeach

                {{-- Titik lokasi = bagian dari Identitas Keluarga (nomor 15-16 di formulir) --}}
                @if (! empty($l['lokasi']))
                    @include('masyarakat.pbi-apbd._lokasi', ['noMulai' => $no + 1])
                    @php $no += 2; @endphp
                @endif
            </div>
        @endforeach

        {{-- Langkah terakhir: foto rumah + lampiran PBI (Scan KTP, Scan KK, Surat rawat inap, diagnosa & faskes) --}}
        <div data-gc-step data-gc-judul="Lampiran & Foto" data-gc-ikon="photo_camera" class="space-y-4 sm:space-y-6">
            @if ($dokumentasi->isNotEmpty())
                <div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6">
                    <h2 class="mb-4 flex items-center gap-3 text-base font-extrabold text-on-surface">
                        <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                            <span class="material-symbols-outlined text-[22px]">photo_camera</span>
                        </span>
                        Foto Rumah
                    </h2>
                    <div class="grid grid-cols-1 gap-5 max-w-xl">
                        @foreach ($dokumentasi as $p)
                            @include('masyarakat.pbi-apbd._gc_field', ['p' => $p, 'nomor' => ++$no])
                        @endforeach
                    </div>
                </div>
            @endif

            @include('masyarakat.pbi-apbd._layanan_lampiran')
        </div>

        <div id="gc-nav" class="flex items-center gap-3 pt-1" hidden>
            <button type="button" id="gc-prev"
                    class="flex-1 sm:flex-none sm:px-6 inline-flex items-center justify-center gap-1 py-3 rounded-full border border-outline-variant text-sm font-semibold text-on-surface">
                <span class="material-symbols-outlined text-[18px]">chevron_left</span> Sebelumnya
            </button>
            <button type="button" id="gc-next"
                    class="flex-1 sm:flex-none sm:px-8 inline-flex items-center justify-center gap-1 py-3 rounded-full bg-primary text-white text-sm font-semibold">
                Lanjut <span class="material-symbols-outlined text-[18px]">chevron_right</span>
            </button>
        </div>
        <p id="gc-step-error" class="text-xs font-semibold text-error" hidden></p>

        {{-- Tombol simpan: blok biasa di paling bawah form (mobile & desktop), tidak mengambang. --}}
        @if ($bisaEdit)
            <div class="pt-2" id="gc-submit-wrap">
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

    {{-- ===== Pop-up konfirmasi sebelum data dikirim ===== --}}
    @if ($bisaEdit)
        <div id="modal-konfirmasi" class="fixed inset-0 z-[90] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="konfirmasi-judul">
            <div class="absolute inset-0 bg-black/50" data-konfirmasi-batal></div>
            <div class="relative w-full max-w-sm rounded-3xl bg-white p-6 shadow-xl">
                <div class="w-12 h-12 rounded-2xl bg-brand-soft text-brand flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-[26px]">fact_check</span>
                </div>
                <h2 id="konfirmasi-judul" class="text-base font-extrabold text-on-surface">Apakah data sudah sesuai?</h2>
                <p class="mt-1.5 text-sm text-on-surface-variant">
                    Pastikan semua jawaban sudah benar dan sesuai keadaan sebenarnya. Data akan dikirim ke petugas kelurahan untuk diverifikasi.
                </p>
                <div class="mt-5 flex flex-col-reverse sm:flex-row gap-2 sm:justify-end">
                    <button type="button" data-konfirmasi-batal
                            class="px-5 py-3 rounded-full border border-outline-variant text-sm font-semibold text-on-surface">Periksa lagi</button>
                    <button type="button" id="btn-konfirmasi-ya"
                            class="px-5 py-3 rounded-full bg-primary text-white text-sm font-semibold">Ya, sudah sesuai</button>
                </div>
            </div>
        </div>
    @endif

</div>
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

    // Mode baca saja (sudah disetujui / sudah dikirim): tidak ada tombol simpan / pengambilan lokasi
    if (!btnSubmit) { return; }

    // Kartu status lokasi lama sudah digantikan _lokasi.blade.php (peta). Bila elemennya tidak ada, lewati.
    if (!geoCard || !statusText || !statusIcon || !statusBox || !btnRetry) { return; }

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

{{-- Isian teks yang terkunci dari registrasi (No. telp, Email): tampil terkunci. Dropdown terkunci sudah ditangani _gc_field. --}}
<script>
window.gcTerkunci = @json(array_keys($terkunci ?? []));

document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-lengkapi');
    if (!form) { return; }

    (window.gcTerkunci || []).forEach(function (id) {
        var el = form.querySelector('[name="jawaban[' + id + ']"]');
        if (!el || el.tagName !== 'INPUT' || el.type === 'hidden') { return; }
        el.readOnly = true;
        el.classList.add('bg-surface-container', 'opacity-70');
        if (el.type === 'date') {
            // Kotak tanggal: matikan juga pemilih kalendernya
            el.style.pointerEvents = 'none';
            el.tabIndex = -1;
        }
    });

    // Isian angka yang selalu 0 (Perahu, Perahu motor, ternak babi): tampil terkunci juga
    form.querySelectorAll('input[data-gc-nol]').forEach(function (el) {
        el.readOnly = true;
        el.tabIndex = -1;
        el.classList.add('bg-surface-container', 'opacity-70', 'cursor-not-allowed');
    });
});
</script>

{{-- Lapangan usaha: penjelasannya tampil di bawah pilihan (sama seperti kolom Pekerjaan di registrasi) --}}
<script>
window.gcPenjelasanLap = @json($master['lapangan']->mapWithKeys(fn ($l) => [$l->nama => $l->penjelasan])->all());

document.addEventListener('DOMContentLoaded', function () {
    var lap = document.querySelector('[data-gc-lapangan]');
    var wrap = lap ? lap.closest('.min-w-0') : null;
    if (!lap || !wrap) { return; }

    var peta = window.gcPenjelasanLap || {};
    var info = document.createElement('p');
    info.hidden = true;
    info.style.cssText = 'margin:.5rem 0 0;padding:.5rem .75rem;border-radius:.75rem;background:#eef4fb;border:1px solid #d9e3f1;color:#42474e;font-size:.75rem;line-height:1.45';
    wrap.appendChild(info);

    function tampil() {
        var nama = lap.value;
        info.textContent = '';
        if (!nama) { info.hidden = true; return; }

        info.appendChild(document.createTextNode('Lapangan usaha: '));
        var b = document.createElement('strong');
        b.textContent = nama;
        info.appendChild(b);
        if (peta[nama]) { info.appendChild(document.createTextNode(' — ' + peta[nama])); }
        info.hidden = false;
    }

    lap.addEventListener('change', tampil);
    tampil();
});
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

{{-- Alamat domisili: SELALU terisi dari alamat KK & terkunci (tidak bergantung jawaban "Apakah alamat sesuai KK?").
     Bila wilayah berbeda, warga mengisi field "Kabupaten/Kecamatan/Kelurahan baru" (disimpan terpisah).
     Server tetap memaksa nilai KK saat disimpan. ("Alamat sesuai KK" tampil sebagai teks oleh server.) --}}
<script>
window.gcAlamat = @json(['ada' => ! empty($petaAlamat), 'kk' => $kk, 'wilayah' => $wilayah]);

document.addEventListener('DOMContentLoaded', function () {
    var cfg = window.gcAlamat;
    var form = document.getElementById('form-lengkapi');
    if (!cfg || !cfg.ada || !form) { return; }

    var f = {};
    form.querySelectorAll('[data-gc-alamat]').forEach(function (el) { f[el.dataset.gcAlamat] = el; });

    function isiKel(kec, pilih) {
        var el = f.kelurahan;
        if (!el) { return; }
        var daftar = cfg.wilayah[kec] || [];
        el.innerHTML = '';
        el.add(new Option(kec ? '- Pilih -' : '- Pilih kecamatan dahulu -', ''));
        daftar.forEach(function (n) { el.add(new Option(n, n)); });
        el.value = daftar.indexOf(pilih) >= 0 ? pilih : '';
    }

    function kunci(el) {
        if (el.tagName === 'SELECT') { el.style.pointerEvents = 'none'; el.tabIndex = -1; }
        else { el.readOnly = true; }
        el.classList.add('bg-surface-container', 'opacity-70');
    }

    Object.keys(f).forEach(function (k) { if (k !== 'kelurahan') { f[k].value = cfg.kk[k] || ''; } });
    isiKel(f.kecamatan ? f.kecamatan.value : cfg.kk.kecamatan, cfg.kk.kelurahan);
    Object.keys(f).forEach(function (k) { kunci(f[k]); });
});
</script>

{{-- "Apakah No KK sama?" dijawab Ya -> "Apakah nama sama?" otomatis Ya. Hanya aktif bila pasangan pertanyaannya ada di master. --}}
<script>
window.gcSama = @json($sama ?? null);

document.addEventListener('DOMContentLoaded', function () {
    var cfg = window.gcSama;
    var form = document.getElementById('form-lengkapi');
    if (!cfg || !form) { return; }

    function ambil(id) { return form.querySelector('[name="jawaban[' + id + ']"]'); }

    form.addEventListener('change', function (e) {
        var src = ambil(cfg.sumber);
        if (!src || e.target !== src) { return; }

        var tgt = ambil(cfg.target);
        if (!tgt || tgt.disabled) { return; }

        if (src.value !== (src.dataset.ya || 'Ya')) { return; }

        var ya = tgt.dataset.ya || 'Ya';
        if (tgt.value === ya) { return; }

        tgt.value = ya;
        tgt.dispatchEvent(new Event('change', { bubbles: true }));
    });
});
</script>

{{-- Ketenagakerjaan: bila "Apakah responden bekerja ...?" dijawab Tidak, field lainnya dinonaktifkan & dikosongkan.
     Field yang terkunci dari registrasi (lapangan usaha & pekerjaan) dipulihkan lagi saat dijawab Ya. --}}
<script>
window.gcKerja = @json($kerja);
// Nilai awal dari registrasi (Lapangan usaha & Pekerjaan): dipasang kembali saat "bekerja" = Ya bila field masih kosong
window.gcIsiAwal = @json($isiAwal ?? new \stdClass);

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
        var pulih = [];

        cfg.dependen.forEach(function (id) {
            kontrol(id).forEach(function (el) {
                el.disabled = !bisaEdit || !ya;
                if (!ya) {
                    if (el.type === 'checkbox' || el.type === 'radio') { el.checked = false; el.dispatchEvent(new Event('change', { bubbles: true })); }
                    else if (el.type !== 'file') { el.value = ''; el.dispatchEvent(new Event('change', { bubbles: true })); }
                } else {
                    // Terkunci -> selalu kembali ke nilai kunci. Terisi awal dari registrasi -> hanya bila masih kosong.
                    var tujuan = el.hasAttribute('data-gc-kunci')
                        ? el.dataset.kunciNilai
                        : (el.value === '' ? ((window.gcIsiAwal || {})[id] || '') : '');
                    if (tujuan && el.value !== tujuan) { pulih.push({ el: el, nilai: tujuan }); }
                }
                var baris = el.closest('.min-w-0');
                if (baris) { baris.classList.toggle('opacity-50', !ya); }
            });
        });

        // Lapangan usaha dulu, baru pekerjaan (opsi pekerjaan dibangun dari lapangan usaha)
        pulih.sort(function (a, b) {
            return (b.el.hasAttribute('data-gc-lapangan') ? 1 : 0) - (a.el.hasAttribute('data-gc-lapangan') ? 1 : 0);
        });
        pulih.forEach(function (p) {
            p.el.value = p.nilai;
            p.el.dispatchEvent(new Event('change', { bubbles: true }));
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
        // Foto tidak ikut draft; file baru dikirim saat "Simpan Data"
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
            if (e.isTrusted && e.target.type !== 'search' && e.target.type !== 'file') { jadwal(); }
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

{{-- Konfirmasi sebelum data dikirim --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-lengkapi');
    var modal = document.getElementById('modal-konfirmasi');
    var btnSubmit = document.getElementById('btn-submit');
    if (!form || !modal || !btnSubmit) { return; }

    var sudahKonfirmasi = false;

    function buka() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.documentElement.style.overflow = 'hidden';
        document.getElementById('btn-konfirmasi-ya').focus();
    }

    function tutup() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.documentElement.style.overflow = '';
    }

    // Listener ini berjalan SETELAH validasi "wajib diisi" di _gc_field (fase capture di document).
    // Jadi pop-up hanya muncul kalau semua pertanyaan wajib sudah terisi.
    form.addEventListener('submit', function (e) {
        if (e.defaultPrevented || sudahKonfirmasi) { return; }
        e.preventDefault();
        buka();
    });

    modal.addEventListener('click', function (e) {
        if (e.target.closest('[data-konfirmasi-batal]')) { tutup(); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) { tutup(); }
    });

    document.getElementById('btn-konfirmasi-ya').addEventListener('click', function () {
        sudahKonfirmasi = true;
        tutup();
        form.requestSubmit(btnSubmit);   // submit ulang, kali ini lolos
        btnSubmit.disabled = true;       // cegah klik ganda
    });
});
</script>

{{-- Wizard tab + progress bar (persen isian wajib yang sudah terisi) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-lengkapi');
    if (!form) { return; }
    var steps = Array.prototype.slice.call(form.querySelectorAll('[data-gc-step]'));
    if (steps.length < 2) { return; }

    var bisaEdit = !!document.getElementById('btn-submit');
    var head = document.getElementById('gc-wiz-head'), nav = document.getElementById('gc-nav');
    var prev = document.getElementById('gc-prev'), next = document.getElementById('gc-next');
    var err = document.getElementById('gc-step-error'), submitWrap = document.getElementById('gc-submit-wrap');
    var bar = document.getElementById('gc-wiz-bar'), tabsEl = document.getElementById('gc-tabs');
    var persenEl = document.getElementById('gc-wiz-persen');
    var now = -1, terjauh = 0, last = steps.length - 1, tabs = [], timer = null;

    // ---- Tab: ikon + judul + badge status ----
    steps.forEach(function (s, i) {
        var t = document.createElement('button');
        t.type = 'button';
        t.className = 'gc-tab';
        t.setAttribute('role', 'tab');
        t.innerHTML = '<span class="material-symbols-outlined"></span><span class="gc-tab-judul"></span><span class="gc-badge" hidden></span>';
        t.children[0].textContent = s.dataset.gcIkon || 'checklist';
        t.children[1].textContent = s.dataset.gcJudul || ('Langkah ' + (i + 1));
        t.addEventListener('click', function () { if (!bisaEdit || i <= terjauh) { tampil(i); } });
        tabsEl.appendChild(t);
        tabs.push(t);
    });

    // ---- Status tiap langkah dari isian yang tampil ----
    function hitung(step) {
        var W = window.gcWajib, total = 0, isi = 0;
        step.querySelectorAll('[data-gc-id]').forEach(function (w) {
            if (w.hidden) { return; }
            var ada = Array.prototype.some.call(
                w.querySelectorAll('input[name^="jawaban"], select[name^="jawaban"], textarea[name^="jawaban"]'),
                function (e) { return !e.disabled; });
            if (!ada) { return; }
            total++;
            if (W && W.terisi(w)) { isi++; }
        });
        if (step.querySelector('[data-gc-lokasi]') && window.gcLokasi) {
            total++;
            if (window.gcLokasi.cek()) { isi++; }
        }
        return { total: total, isi: isi };
    }

    function segarkan() {
        var T = 0, I = 0;
        steps.forEach(function (s, i) {
            var b = tabs[i].children[2], h = hitung(s);
            T += h.total; I += h.isi;
            if (!h.total) { b.hidden = true; return; }
            b.hidden = false;
            b.classList.remove('is-lengkap', 'is-draft', 'is-kosong');
            if (h.isi >= h.total) { b.className = 'gc-badge is-lengkap'; b.textContent = 'Lengkap'; }
            else if (h.isi === 0) { b.className = 'gc-badge is-kosong'; b.textContent = 'Kosong'; }
            else { b.className = 'gc-badge is-draft'; b.textContent = 'Draft ' + h.isi + '/' + h.total; }
        });
        var p = T ? Math.round(I / T * 100) : 0;
        bar.style.width = p + '%';
        if (persenEl) { persenEl.textContent = p + '% terisi'; }
    }

    // Dipanggil dari _lokasi.blade.php saat titik peta berubah
    window.gcSegarkanProgres = segarkan;

    function jadwalSegarkan() { clearTimeout(timer); timer = setTimeout(segarkan, 80); }
    ['change', 'input', 'click'].forEach(function (ev) { form.addEventListener(ev, jadwalSegarkan); });

    function keAtas() {
        var y = head.getBoundingClientRect().top + window.pageYOffset - 80;
        window.scrollTo({ top: Math.max(y, 0), behavior: 'smooth' });
    }

    function tampil(i, awal) {
        if (i === now) { return; }
        var arah = i > now ? 'next' : 'prev';
        steps.forEach(function (s, j) {
            s.hidden = j !== i;
            s.classList.remove('is-in-next', 'is-in-prev');
        });
        if (!awal) { steps[i].classList.add('is-in-' + arah); }
        now = i;
        if (i > terjauh) { terjauh = i; }

        tabs.forEach(function (t, j) {
            t.classList.toggle('is-active', j === i);
            t.setAttribute('aria-selected', j === i ? 'true' : 'false');
            t.disabled = bisaEdit && j > terjauh;
        });
        var aktif = tabs[i];
        if (aktif.scrollIntoView) { tabsEl.scrollLeft = aktif.offsetLeft - (tabsEl.clientWidth - aktif.offsetWidth) / 2; }

        prev.disabled = i === 0;
        next.hidden = i === last;
        if (submitWrap) { submitWrap.hidden = i !== last; }
        err.hidden = true;
        segarkan();
        if (!awal) { keAtas(); }
    }

    function validasi(step) {
        var lok = step.querySelector('[data-gc-lokasi]');
        if (bisaEdit && lok && window.gcLokasi && !window.gcLokasi.cek()) {
            err.textContent = window.gcLokasi.pesan();
            err.hidden = false;
            lok.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        var pasang = window.gcPasangMeteran ? window.gcPasangMeteran() : null;
        if (pasang && step.contains(pasang.pertama)) {
            pasang.pertama.scrollIntoView({ behavior: 'smooth', block: 'center' });
            err.textContent = pasang.pesan;
            err.hidden = false;
            return false;
        }
        var W = window.gcWajib;
        if (!W) { return true; }
        var kosong = [];
        step.querySelectorAll('[data-gc-wajib]').forEach(function (w) {
            if (w.hidden) { return; }
            if (W.terisi(w)) { W.hapus(w); } else { W.tandai(w); kosong.push(w); }
        });
        if (kosong.length) {
            kosong[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            err.textContent = kosong.length + ' pertanyaan wajib di bagian ini belum dijawab.';
            err.hidden = false;
            return false;
        }
        err.hidden = true;
        return true;
    }

    next.addEventListener('click', function () {
        if (bisaEdit && !validasi(steps[now])) { return; }
        tampil(now + 1);
    });
    prev.addEventListener('click', function () { tampil(now - 1); });

    window.gcBukaLangkah = function (el) {
        for (var i = 0; i < steps.length; i++) {
            if (steps[i].contains(el)) { tampil(i); return; }
        }
    };

    var mulai = 0;
    var salah = form.querySelector('p.text-error');
    if (salah) {
        for (var k = 0; k < steps.length; k++) { if (steps[k].contains(salah)) { mulai = k; break; } }
    }
    terjauh = bisaEdit ? mulai : last;
    head.hidden = false;
    nav.hidden = false;
    tampil(mulai, true);
    setTimeout(segarkan, 400); // setelah skrip lain (syarat, kerja, lokasi) selesai menyiapkan isian
});
</script>

{{-- Syarat tampil (induk_id / induk_nilai): sembunyikan pertanyaan yang syaratnya belum terpenuhi --}}
@include('partials.gc-syarat')

{{-- Nomor pertanyaan mengikuti yang tampil: pertanyaan yang disembunyikan syarat tidak menyisakan nomor loncat --}}
<script>
(function () {
    var form = document.getElementById('form-lengkapi');
    if (!form) { return; }

    var daftar = Array.prototype.slice.call(form.querySelectorAll('[data-gc-no]')).map(function (el) {
        return { el: el, asli: parseInt(el.textContent, 10) };
    }).filter(function (x) { return !isNaN(x.asli); });

    function tersembunyi(el) {
        for (var n = el.closest('[data-gc-id]'); n && n !== form; n = n.parentElement) {
            if (n.hasAttribute('data-gc-step')) { break; }
            if (n.hidden || window.getComputedStyle(n).display === 'none') { return true; }
        }
        return false;
    }

    var jalan = false;
    function susun() {
        jalan = false;
        var hitung = 0, sebelumnya = 0;
        daftar.forEach(function (x) {
            // selisih nomor asli (mis. nomor titik lokasi) tetap dipertahankan
            hitung += Math.max(0, x.asli - sebelumnya - 1);
            sebelumnya = x.asli;
            if (tersembunyi(x.el)) { return; }
            hitung++;
            if (x.el.textContent !== String(hitung)) { x.el.textContent = hitung; }
        });
    }
    function jadwalkan() {
        if (jalan) { return; }
        jalan = true;
        window.requestAnimationFrame(susun);
    }

    // ID Pelanggan PLN / No Meteran Listrik: boleh salah satu, tidak boleh dua-duanya kosong (bila tampil)
    window.gcPasangMeteran = function () {
        var a = null, b = null;
        form.querySelectorAll('[data-gc-id]').forEach(function (w) {
            var n = w.querySelector('[data-gc-nama]');
            var t = n ? n.textContent.trim().toLowerCase() : '';
            if (t === 'id pelanggan pln') { a = w; }
            if (t === 'no meteran listrik') { b = w; }
        });
        if (!a || !b || a.hidden || b.hidden || a.style.display === 'none' || b.style.display === 'none') { return null; }
        function isi(w) {
            return Array.prototype.some.call(w.querySelectorAll('input[name^="jawaban"]'), function (e) {
                return !e.disabled && e.value.trim() !== '';
            });
        }
        if (isi(a) || isi(b)) { return null; }
        return { pertama: a, pesan: 'Isi minimal salah satu: ID Pelanggan PLN atau No Meteran Listrik.' };
    };

    form.addEventListener('submit', function (e) {
        var p = window.gcPasangMeteran();
        if (!p || !document.getElementById('btn-submit') || e.submitter && e.submitter.name === 'aksi') { return; }
        e.preventDefault();
        if (window.gcBukaLangkah) { window.gcBukaLangkah(p.pertama); }
        var er = document.getElementById('gc-step-error');
        if (er) { er.textContent = p.pesan; er.hidden = false; }
        p.pertama.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, true);

    new MutationObserver(jadwalkan).observe(form, { attributes: true, subtree: true, attributeFilter: ['style', 'hidden', 'class'] });
    ['change', 'input', 'click'].forEach(function (e) { form.addEventListener(e, function () { setTimeout(jadwalkan, 0); }); });
    document.addEventListener('DOMContentLoaded', function () { setTimeout(jadwalkan, 100); setTimeout(jadwalkan, 600); });
    jadwalkan();
})();
</script>
@endpush

@push('head')
<style>
    .gc-step.is-in-next { animation: gc-in-next .28s ease-out; }
    .gc-step.is-in-prev { animation: gc-in-prev .28s ease-out; }
    @@keyframes gc-in-next { from { opacity: 0; transform: translateX(28px); } to { opacity: 1; transform: none; } }
    @@keyframes gc-in-prev { from { opacity: 0; transform: translateX(-28px); } to { opacity: 1; transform: none; } }
    @@media (prefers-reduced-motion: reduce) { .gc-step { animation: none !important; } }
    #gc-tabs { scrollbar-width: none; }
    #gc-tabs::-webkit-scrollbar { display: none; }
    .gc-tab { flex: 1 1 0; min-width: 132px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: .35rem;
    padding: .9rem .6rem .8rem; background: #fff; color: #42474e; font-size: .8125rem; font-weight: 500; text-align: center; line-height: 1.25;
    border-bottom: 3px solid transparent; cursor: pointer; transition: background-color .15s; }
    .gc-tab-judul { display: block; white-space: normal; }
    .gc-tab .material-symbols-outlined { font-size: 26px; }
    .gc-tab.is-active { background: #e8f4fd; color: #0a5ca8; border-bottom-color: #0a5ca8; }
    .gc-tab:disabled { opacity: .55; cursor: not-allowed; }
    .gc-badge { padding: .1rem .6rem; border-radius: 9999px; font-size: .6875rem; font-weight: 500; }
    .gc-badge.is-lengkap { background: #dcf5e3; color: #146c2e; }
    .gc-badge.is-draft { background: #ffefc2; color: #9a5a00; }
    .gc-badge.is-kosong { background: #eceff3; color: #6b7280; }
    .gc-dot.is-done { background: #e8f4fd; border-color: #0a5ca8; color: #0a5ca8; }
    .gc-dot.is-active { background: #0a5ca8; border-color: #0a5ca8; color: #fff; }
    #gc-prev:disabled { opacity: .4; cursor: not-allowed; }
    .m-content:has(.gc-lebar) { max-width: 1000px; }
    .gc-lebar { width: 100%; }
</style>
@endpush
