{{-- Langkah slider: konfirmasi titik lokasi (geotagging) = nomor 15-16 formulir. Variabel: $pbiApbd, $bisaEdit, $noMulai (opsional, default 15) --}}
<div data-gc-lokasi class="mt-8 border-t border-outline-variant/40 pt-6">
    <div class="mb-1 flex items-center justify-center gap-2">
        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 rounded-full bg-brand-soft text-brand text-[11px] font-bold">{{ $noMulai ?? 15 }}</span>
        <h2 class="text-base sm:text-lg font-extrabold text-on-surface">Titik lokasi (geotagging) tempat tinggal saat ini</h2>
    </div>
    <p class="mb-4 text-center text-xs text-on-surface-variant">
        @if ($bisaEdit)
            Tekan tombol otomatis, isi manual, atau geser marker di peta untuk titik yang lebih tepat.
        @else
            Titik lokasi yang tersimpan pada pengajuan ini.
        @endif
    </p>

    @if ($bisaEdit)
        <div class="flex justify-center mb-4">
            <button type="button" id="btn-lokasi-auto"
                    class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-primary text-white text-xs font-semibold">
                <span class="material-symbols-outlined text-[16px]">location_on</span>
                Ambil Titik Lokasi Otomatis
            </button>
        </div>
    @endif

    <p id="lokasi-status" aria-live="polite" class="mb-3 text-center text-xs text-on-surface-variant empty:hidden"></p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
        <label class="flex items-center gap-3 text-sm font-semibold text-on-surface">
            <span class="w-20 shrink-0">Latitude</span>
            <input type="text" inputmode="decimal" id="latitude" name="latitude" autocomplete="off"
                   value="{{ old('latitude', $pbiApbd->latitude) }}" @readonly(! $bisaEdit)
                   class="w-full rounded-xl border border-outline-variant px-3.5 py-2.5 text-base sm:text-sm bg-white focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 read-only:bg-surface-container">
        </label>
        <label class="flex items-center gap-3 text-sm font-semibold text-on-surface">
            <span class="w-20 shrink-0">Longitude</span>
            <input type="text" inputmode="decimal" id="longitude" name="longitude" autocomplete="off"
                   value="{{ old('longitude', $pbiApbd->longitude) }}" @readonly(! $bisaEdit)
                   class="w-full rounded-xl border border-outline-variant px-3.5 py-2.5 text-base sm:text-sm bg-white focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 read-only:bg-surface-container">
        </label>
    </div>

    <div id="gc-peta" class="w-full rounded-2xl overflow-hidden border border-outline-variant"
         style="height:300px;background:#e4effd" role="application" aria-label="Peta titik lokasi rumah"></div>

    <p class="mt-5 mb-1.5 flex items-center gap-2 text-sm font-semibold text-on-surface">
        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 rounded-full bg-brand-soft text-brand text-[11px] font-bold">{{ ($noMulai ?? 15) + 1 }}</span>
        Pernyataan kesesuaian lokasi
    </p>
    <label class="flex items-start gap-3 rounded-2xl border border-outline-variant p-3.5 cursor-pointer">
        <input type="checkbox" id="konfirmasi-lokasi" name="konfirmasi_lokasi" value="1"
               class="mt-0.5 h-5 w-5 shrink-0 accent-[#0a5ca8]" @checked(! $bisaEdit) @disabled(! $bisaEdit)>
        <span class="text-sm text-on-surface">Saya menyatakan bahwa titik lokasi di peta sudah sesuai dengan rumah saya.</span>
    </label>

    <p id="lokasi-error" class="mt-2 text-xs font-semibold text-error" hidden></p>
</div>

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <style>
        #gc-peta { position: relative; z-index: 0; } /* peta tidak menimpa pop-up / header */
        #gc-peta .leaflet-control-attribution { font-size: 10px; }
        .gc-pin { background: none; border: 0; filter: drop-shadow(0 2px 3px rgba(0,0,0,.35)); }
    </style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var latEl = document.getElementById('latitude');
    var lngEl = document.getElementById('longitude');
    var chk = document.getElementById('konfirmasi-lokasi');
    var btnAuto = document.getElementById('btn-lokasi-auto');
    var statusEl = document.getElementById('lokasi-status');
    var errEl = document.getElementById('lokasi-error');
    var mapEl = document.getElementById('gc-peta');
    var submit = document.getElementById('btn-submit');
    var form = document.getElementById('form-lengkapi');
    var card = document.querySelector('[data-gc-lokasi]');
    if (!latEl || !mapEl || !window.L) { return; }

    var bisaEdit = !!submit;
    var PUSAT = [-6.5971, 106.8060]; // Kota Bogor

    function num(v) { var n = parseFloat(String(v == null ? '' : v).replace(',', '.')); return isFinite(n) ? n : null; }
    function valid() {
        var a = num(latEl.value), b = num(lngEl.value);
        return a !== null && b !== null && Math.abs(a) <= 90 && Math.abs(b) <= 180 && !(a === 0 && b === 0);
    }
    function ok() { return !bisaEdit || (valid() && chk.checked); }
    function pesan() {
        if (!valid()) { return 'Titik lokasi belum diisi. Ambil otomatis, isi manual, atau pilih titik di peta.'; }
        return 'Centang pernyataan kesesuaian titik lokasi terlebih dahulu.';
    }
    function status(t) { statusEl.textContent = t || ''; }

    // Parameter koordinat/lintang/bujur di daftar pertanyaan (tersembunyi) ikut terisi
    function isiKoordinat(a, b) {
        var k = document.querySelector('[data-gc-koordinat]');
        var la = document.querySelector('[data-gc-lat]');
        var lo = document.querySelector('[data-gc-lng]');
        if (k && !k.disabled) { k.value = a.toFixed(6) + ', ' + b.toFixed(6); }
        if (la && !la.disabled) { la.value = a.toFixed(6); }
        if (lo && !lo.disabled) { lo.value = b.toFixed(6); }
    }

    function segarkan() {
        if (submit) { submit.disabled = !ok(); }
        if (ok()) { errEl.hidden = true; }
    }

    // ---- Peta ----
    var ikonRumah = L.divIcon({
        className: 'gc-pin',
        html: '<svg viewBox="0 0 24 24" width="38" height="38" aria-hidden="true">' +
            '<path d="M12 2C7.6 2 4 5.5 4 9.9c0 5.6 6.4 11.4 7.2 12.1.5.4 1.1.4 1.6 0C13.6 21.3 20 15.5 20 9.9 20 5.5 16.4 2 12 2z" fill="#ba1a1a" stroke="#fff" stroke-width="1.5"/>' +
            '<circle cx="12" cy="10" r="3.2" fill="#fff"/></svg>',
        iconSize: [38, 38],
        iconAnchor: [19, 37]   // ujung bawah pin tepat di titik koordinat
    });

    var map = L.map(mapEl, { scrollWheelZoom: false }).setView(PUSAT, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var marker = null;

    function taruhMarker(a, b) {
        if (!marker) {
            marker = L.marker([a, b], { draggable: bisaEdit, icon: ikonRumah }).addTo(map);
            if (bisaEdit) {
                marker.on('dragend', function () {
                    var p = marker.getLatLng();
                    setTitik(p.lat, p.lng, { dariPeta: true });
                });
            }
        } else {
            marker.setLatLng([a, b]);
        }
    }

    // opsi.dariPeta: tidak perlu memindahkan marker lagi; opsi.awal: pemuatan awal (tidak mereset centang)
    function setTitik(a, b, opsi) {
        opsi = opsi || {};
        latEl.value = a.toFixed(8);
        lngEl.value = b.toFixed(8);
        taruhMarker(a, b);
        if (!opsi.dariPeta) { map.setView([a, b], Math.max(map.getZoom(), 18)); }
        isiKoordinat(a, b);
        if (!opsi.awal && bisaEdit) { chk.checked = false; } // titik berubah -> konfirmasi ulang
        status('');
        segarkan();
        if (window.gcSegarkanProgres) { window.gcSegarkanProgres(); }
    }

    if (bisaEdit) {
        map.on('click', function (e) { setTitik(e.latlng.lat, e.latlng.lng, { dariPeta: true }); });
    }

    // Peta berada di langkah slider yang tersembunyi: hitung ulang ukuran saat langkah tampil
    if (window.ResizeObserver) {
        new ResizeObserver(function () { map.invalidateSize(); }).observe(mapEl);
    }

    // Ketik manual -> marker ikut pindah
    ['change', 'blur'].forEach(function (ev) {
        [latEl, lngEl].forEach(function (el) {
            el.addEventListener(ev, function () {
                if (valid()) { setTitik(num(latEl.value), num(lngEl.value)); } else { segarkan(); }
            });
        });
    });

    // ---- Ambil otomatis ----
    function ambilOtomatis(senyap) {
        if (!navigator.geolocation) {
            status('Browser Anda tidak mendukung pengambilan lokasi otomatis. Isi manual atau pilih titik di peta.');
            return;
        }
        status('Mengambil lokasi Anda...');
        if (btnAuto) { btnAuto.disabled = true; }

        navigator.geolocation.getCurrentPosition(function (pos) {
            if (btnAuto) { btnAuto.disabled = false; }
            setTitik(pos.coords.latitude, pos.coords.longitude);
            status('Lokasi terdeteksi. Geser marker bila kurang tepat.');
        }, function (err) {
            if (btnAuto) { btnAuto.disabled = false; }
            var msg = 'Gagal mengambil lokasi. Isi manual atau pilih titik di peta.';
            if (!window.isSecureContext) { msg = 'Lokasi otomatis hanya bisa lewat koneksi aman (HTTPS). Pilih titik di peta.'; }
            else if (err.code === 1) { msg = 'Izin lokasi ditolak. Aktifkan izin lokasi di browser, atau pilih titik di peta.'; }
            else if (err.code === 2) { msg = 'Lokasi tidak tersedia. Pastikan GPS aktif, atau pilih titik di peta.'; }
            else if (err.code === 3) { msg = 'Waktu pengambilan lokasi habis. Coba lagi atau pilih titik di peta.'; }
            status(senyap ? '' : msg);
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
    }

    if (btnAuto) { btnAuto.addEventListener('click', function () { ambilOtomatis(false); }); }
    if (chk) { chk.addEventListener('change', segarkan); }

    // ---- Nilai awal ----
    if (valid()) {
        setTitik(num(latEl.value), num(lngEl.value), { awal: true });
        map.setView([num(latEl.value), num(lngEl.value)], 18);
    } else if (bisaEdit) {
        segarkan();
        ambilOtomatis(true); // coba diam-diam, seperti sebelumnya
    }

    // ---- Dipakai slider & submit ----
    window.gcLokasi = { cek: ok, pesan: pesan };

    if (form && bisaEdit) {
        form.addEventListener('submit', function (e) {
            if (ok()) { return; }
            e.preventDefault();
            e.stopImmediatePropagation();
            if (window.gcBukaLangkah) { window.gcBukaLangkah(card); }
            errEl.textContent = pesan();
            errEl.hidden = false;
        }, true);
    }
});
</script>
@endpush
