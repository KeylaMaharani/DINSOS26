@extends('layouts.admin')

@section('title', 'Buat Laporan Bencana - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'SIGAP Bencana — Buat Laporan')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@php
    $isKelurahan = ($user->role?->normalizedSlug() ?? null) === 'kelurahan';
    $backRoute = $isKelurahan ? route('sigap.proses.index') : route('sigap.index');
    $input = 'w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand';
    $card = 'bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden';
@endphp

@section('content')
    <div class="space-y-5 max-w-5xl">

        <a href="{{ $backRoute }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-brand">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar laporan
        </a>

        @if ($errors->any())
            <div class="flex flex-col gap-1 px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('sigap.store') }}" class="space-y-5">
            @csrf

            {{-- 1. Data kejadian --}}
            <div class="{{ $card }}">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-brand">crisis_alert</span>
                        Laporan Kejadian Bencana
                    </h3>
                    <p class="text-[11px] text-on-surface-variant mt-1">
                        Nomor tiket dan nomor surat laporan dibuat otomatis berurutan saat laporan disimpan.
                    </p>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">Jenis Bencana <span class="text-error">*</span></label>
                        <select name="jenis_bencana" class="{{ $input }}">
                            <option value="">-Pilih-</option>
                            @foreach ($jenisBencana as $jenis)
                                <option value="{{ $jenis }}" @selected(old('jenis_bencana') === $jenis)>{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">Tanggal & Waktu Kejadian <span class="text-error">*</span></label>
                        <input type="datetime-local" name="tanggal_kejadian"
                            value="{{ old('tanggal_kejadian', now()->format('Y-m-d\TH:i')) }}"
                            max="{{ now()->format('Y-m-d\TH:i') }}" class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">Jumlah Korban Bencana (jiwa) <span class="text-error">*</span></label>
                        <input type="number" min="0" name="jumlah_korban" value="{{ old('jumlah_korban', 0) }}"
                            class="{{ $input }}" />
                    </div>
                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">No. Kartu Keluarga</label>
                        <input type="text" name="no_kk" value="{{ old('no_kk') }}"
                            placeholder="Jika lebih dari satu KK, pisahkan dengan koma" class="{{ $input }}" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-on-surface-variant mb-1">Kerusakan</label>
                        <textarea name="kerusakan" rows="2" class="{{ $input }}"
                            placeholder="Contoh: 3 rumah rusak berat, 1 jembatan putus">{{ old('kerusakan') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-on-surface-variant mb-1">Kerugian</label>
                        <textarea name="kerugian" rows="2" class="{{ $input }}">{{ old('kerugian') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-on-surface-variant mb-1">Bantuan yang Dibutuhkan <span class="text-error">*</span></label>
                        <textarea name="bantuan_dibutuhkan" rows="2" class="{{ $input }}"
                            placeholder="Contoh: makanan siap saji, selimut, tenda">{{ old('bantuan_dibutuhkan') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- 2. Lokasi --}}
            <div class="{{ $card }}">
                <div class="px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-brand">location_on</span>
                        Lokasi Kejadian
                    </h3>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">Kecamatan <span class="text-error">*</span></label>
                        @if ($isKelurahan)
                            <input type="text" value="{{ $user->kecamatan }}" readonly class="{{ $input }} bg-slate-100 text-on-surface-variant" />
                        @else
                            <input type="text" name="kecamatan" list="daftar-kecamatan" value="{{ old('kecamatan') }}"
                                class="{{ $input }}" />
                            <datalist id="daftar-kecamatan">
                                @foreach (['Bogor Barat', 'Bogor Selatan', 'Bogor Tengah', 'Bogor Timur', 'Bogor Utara', 'Tanah Sareal'] as $kec)
                                    <option value="{{ $kec }}"></option>
                                @endforeach
                            </datalist>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">Kelurahan <span class="text-error">*</span></label>
                        @if ($isKelurahan)
                            <input type="text" value="{{ $user->kelurahan }}" readonly class="{{ $input }} bg-slate-100 text-on-surface-variant" />
                        @else
                            <input type="text" name="kelurahan" value="{{ old('kelurahan') }}" class="{{ $input }}" />
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">RT</label>
                            <input type="text" name="rt" maxlength="5" value="{{ old('rt') }}" class="{{ $input }}" />
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">RW</label>
                            <input type="text" name="rw" maxlength="5" value="{{ old('rw') }}" class="{{ $input }}" />
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-on-surface-variant mb-1">Alamat Lengkap <span class="text-error">*</span></label>
                        <textarea name="alamat" rows="2" class="{{ $input }}">{{ old('alamat') }}</textarea>
                    </div>

                    {{-- Peta tepat di bawah kolom alamat --}}
                    <div class="sm:col-span-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <label class="block text-xs text-on-surface-variant">
                                Titik Lokasi <span class="text-error">*</span>
                                <span class="opacity-70">— klik peta atau geser pin untuk menentukan titik</span>
                            </label>
                            <button type="button" id="btn-gps"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand/10 text-brand hover:bg-brand hover:text-white text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[16px]">my_location</span>
                                Gunakan Lokasi Saya
                            </button>
                        </div>
                        <div id="map-lokasi" class="w-full h-80 rounded-2xl border border-outline-variant/40 z-0"></div>
                        <p id="gps-status" class="text-[11px] text-on-surface-variant mt-2">
                            Titik belum dipilih.
                        </p>
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}" />
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pb-6">
                <a href="{{ $backRoute }}"
                    class="px-4 py-2 rounded-xl border border-outline-variant/50 text-sm hover:bg-surface-container transition-colors">Batal</a>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-brand text-white hover:bg-primary text-sm font-bold shadow-md transition-colors">
                    <span class="material-symbols-outlined text-[18px]">send</span>
                    Kirim Laporan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function() {
            const latEl = document.getElementById('latitude');
            const lngEl = document.getElementById('longitude');
            const statusEl = document.getElementById('gps-status');

            const startLat = parseFloat(latEl.value);
            const startLng = parseFloat(lngEl.value);
            const hasPoint = !isNaN(startLat) && !isNaN(startLng);

            // Pusat awal: Kota Bogor
            const map = L.map('map-lokasi').setView(hasPoint ? [startLat, startLng] : [-6.5971, 106.8060], hasPoint ? 17 : 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            let marker = null;

            function setPoint(lat, lng, keterangan) {
                latEl.value = lat.toFixed(7);
                lngEl.value = lng.toFixed(7);

                if (!marker) {
                    marker = L.marker([lat, lng], {
                        draggable: true
                    }).addTo(map);
                    marker.on('dragend', function() {
                        const p = marker.getLatLng();
                        setPoint(p.lat, p.lng, 'Titik digeser manual');
                    });
                } else {
                    marker.setLatLng([lat, lng]);
                }

                statusEl.textContent = (keterangan ? keterangan + ' — ' : '') +
                    'Koordinat: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
            }

            map.on('click', function(e) {
                setPoint(e.latlng.lat, e.latlng.lng, 'Titik dipilih di peta');
            });

            function ambilGps() {
                if (!window.isSecureContext) {
                    statusEl.textContent = 'GPS otomatis hanya aktif di alamat HTTPS. Silakan klik peta untuk menentukan titik.';
                    return;
                }
                if (!navigator.geolocation) {
                    statusEl.textContent = 'Perangkat tidak mendukung GPS. Silakan klik peta untuk menentukan titik.';
                    return;
                }

                statusEl.textContent = 'Mengambil lokasi perangkat...';

                navigator.geolocation.getCurrentPosition(function(pos) {
                    setPoint(pos.coords.latitude, pos.coords.longitude, 'Lokasi perangkat');
                    map.setView([pos.coords.latitude, pos.coords.longitude], 17);
                }, function(err) {
                    statusEl.textContent = err.code === 1 ?
                        'Izin lokasi ditolak. Silakan klik peta untuk menentukan titik.' :
                        'Lokasi tidak dapat diambil. Silakan klik peta untuk menentukan titik.';
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                });
            }

            document.getElementById('btn-gps').addEventListener('click', ambilGps);

            if (hasPoint) {
                setPoint(startLat, startLng, 'Titik tersimpan');
            } else {
                ambilGps();
            }

            // Peta di dalam kartu perlu dihitung ulang ukurannya setelah halaman tampil penuh
            setTimeout(function() {
                map.invalidateSize();
            }, 250);
        })();
    </script>
@endpush
