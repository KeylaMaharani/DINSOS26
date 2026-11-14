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

                {{-- Satu kolom: setiap pertanyaan satu baris, lebar dibatasi supaya input tidak terlalu panjang --}}
                <div class="grid grid-cols-1 gap-5 max-w-xl">
                    @foreach ($isi as $p)
                        @include('masyarakat.pbi-apbd._gc_field', ['p' => $p, 'nomor' => ++$no])
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Kartu lampiran PBI: Scan KTP, Scan KK, Surat rawat inap, diagnosa & faskes --}}
        @include('masyarakat.pbi-apbd._layanan_lampiran')

        {{-- Tombol simpan: blok biasa di paling bawah form (mobile & desktop), tidak mengambang. --}}
        @if ($bisaEdit)
            <div class="pt-2">
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

{{-- Alamat domisili: SELALU terisi dari alamat KK & terkunci (tidak bergantung jawaban "Apakah wilayah sama?").
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

{{-- "Apakah No KK sama?" dijawab Ya -> "Apakah nama sama?" otomatis Ya (wilayah tidak ikut berubah). Tetap bisa diubah warga. --}}
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
@endpush