@extends('layouts.admin')

@php
    use App\Models\GcKriteriaParameter as GcP;

    // Tab awal: tab pertama yang berisi galat validasi (jika ada), atau tab terakhir untuk galat pernyataan,
    // atau tab yang terakhir dibuka sebelumnya.
    $mulai = $tabAwal ?? 0;
    if ($errors->any()) {
        $mulai = null;
        foreach ($tabs as $i => $t) {
            foreach ($t['params'] as $pp) {
                $kena = collect($errors->keys())->contains(fn ($k) => $k === "jawaban.{$pp->id}" || str_starts_with($k, "jawaban.{$pp->id}."));
                if ($kena) { $mulai = $i; break 2; }
            }
        }
        if ($mulai === null) {
            $mulai = $errors->has('pernyataan') ? count($tabs) - 1 : 0;
        }
    }
    $mulai = max(0, min((int) $mulai, count($tabs) - 1));
@endphp

@section('title', 'Form Ground Check')
@section('page_title', 'Ground Check 2026 — Form Isian')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="max-w-5xl space-y-5" x-data="gcWizard({{ count($tabs) }}, {{ $mulai }})">

    <a href="{{ route('ground-check.keluarga', $sasaran->no_kk) }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar keluarga
    </a>

    {{-- Identitas --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
        <div><p class="text-on-surface-variant">Nama</p><p class="font-semibold text-sm">{{ $sasaran->nama }}</p></div>
        <div><p class="text-on-surface-variant">NIK</p><p class="font-semibold text-sm">{{ $sasaran->nik }}</p></div>
        <div><p class="text-on-surface-variant">No KK</p><p class="font-semibold text-sm">{{ $sasaran->no_kk }}</p></div>
    </div>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <p class="font-semibold mb-1">Data belum dapat disimpan:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('ground-check.simpan', $sasaran) }}" enctype="multipart/form-data" x-ref="form" novalidate>
        @csrf
        {{-- Simpan langsung (tanpa draf) --}}
        <input type="hidden" name="aksi" value="submit" x-ref="aksi">
        <input type="hidden" name="tab_aktif" :value="tab">

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">

            {{-- Header tab --}}
            <div class="flex overflow-x-auto border-b border-outline-variant/40">
                @foreach ($tabs as $i => $t)
                    <button type="button" @click="keTab({{ $i }})"
                        :class="tab === {{ $i }} ? 'text-brand border-b-2 border-brand bg-brand-soft/60' : 'text-on-surface-variant hover:bg-surface-container-low'"
                        class="flex-1 min-w-[120px] px-3 py-3 flex flex-col items-center gap-1 text-[11px] font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[22px]">{{ $t['icon'] }}</span>
                        {{ $t['label'] }}
                    </button>
                @endforeach
            </div>
            <div class="h-1.5 bg-surface-container">
                <div class="h-full bg-brand transition-all duration-300" :style="`width: ${((tab + 1) / total) * 100}%`"></div>
            </div>

            {{-- Isi tab --}}
            <div class="p-5">
                <p x-show="pesan" x-cloak x-text="pesan" class="mb-3 px-4 py-2.5 rounded-lg bg-error-container text-on-error-container text-xs font-medium"></p>

                @foreach ($tabs as $i => $t)
                    <div id="gc-panel-{{ $i }}" x-show="tab === {{ $i }}" @if ($i !== $mulai) x-cloak @endif>
                        @foreach ($t['kelompok'] as $kel)
                            @php $isi = $t['params']->where('kelompok', $kel); @endphp
                            @if ($isi->isNotEmpty())
                                @if (count($t['kelompok']) > 1 && isset(GcP::SUBJUDUL[$kel]))
                                    <h4 class="text-xs font-bold text-on-surface mt-4 mb-1 first:mt-0">{{ GcP::SUBJUDUL[$kel] }}</h4>
                                @endif
                                @foreach ($isi as $p)
                                    @include('admin.ground-check._field', ['p' => $p, 'nilai' => $nilai])
                                @endforeach
                            @endif
                        @endforeach

                        @if ($loop->last)
                            <label class="mt-5 flex items-start gap-2 px-4 py-3 rounded-lg bg-error-container/60 text-on-error-container text-xs leading-relaxed cursor-pointer" id="gc-pernyataan"
                                   @change="$el.classList.remove('ring-2', 'ring-error')">
                                <input type="checkbox" name="pernyataan" value="1" x-ref="pernyataan" @checked(old('pernyataan', $v->pernyataan)) class="mt-0.5 rounded" />
                                <span>Dengan ini saya menyatakan bahwa data yang saya kirim adalah benar dan valid.</span>
                            </label>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Tombol --}}
            <div class="px-5 py-4 bg-surface-container-low border-t border-outline-variant/40 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <a x-show="tab === 0" href="{{ route('ground-check.keluarga', $sasaran->no_kk) }}"
                       class="inline-flex px-4 py-2 rounded-lg text-sm border border-outline-variant/50 bg-white">Batal</a>
                    <button type="button" x-show="tab > 0" x-cloak @click="kembali()"
                       class="inline-flex px-4 py-2 rounded-lg text-sm border border-outline-variant/50 bg-white">Kembali</button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" x-show="tab < total - 1" @click="lanjut()"
                        class="inline-flex items-center gap-1 px-5 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">
                        Lanjut <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </button>
                    <button type="button" x-show="tab === total - 1" x-cloak @click="kirim()"
                        class="inline-flex items-center gap-1 px-5 py-2 rounded-lg text-sm bg-success text-white hover:opacity-90 font-semibold">
                        <span class="material-symbols-outlined text-[16px]">save</span> Simpan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    /* ---------- Wizard tab A-G ---------- */
    function gcWizard(total, start) {
        return {
            tab: start,
            total: total,
            pesan: '',

            init() {
                this.$watch('tab', () => {
                    setTimeout(() => { if (window.gcMap) window.gcMap.invalidateSize(); }, 150);
                });

                // Hilangkan border merah begitu isian diisi
                const bersihkan = (e) => {
                    const w = e.target.closest('[data-wajib]');
                    if (w && !this.kosong(w)) {
                        w.classList.remove('ring-1', 'ring-error');
                    }
                };
                this.$el.addEventListener('input', bersihkan);
                this.$el.addEventListener('change', bersihkan);
            },

            // true jika isian wajib pada baris w masih kosong
            kosong(w) {
                const t = w.dataset.wajib;
                if (t === 'ganda') {
                    return !w.querySelector('input[type=checkbox]:checked');
                }
                if (t === 'foto') {
                    const f = w.querySelector('input[type=file]');
                    return !(f.files && f.files.length) && !w.dataset.ada;
                }
                const el = w.querySelector('input,select,textarea');
                return !el || String(el.value).trim() === '';
            },

            // true jika semua isian wajib (bertanda *) pada tab ke-i sudah terisi
            cekTab(i) {
                const panel = document.getElementById('gc-panel-' + i);
                let ok = true, pertama = null;
                panel.querySelectorAll('[data-wajib]').forEach((w) => {
                    const kosong = this.kosong(w);
                    w.classList.toggle('ring-1', kosong);
                    w.classList.toggle('ring-error', kosong);
                    if (kosong && !pertama) pertama = w;
                    ok = ok && !kosong;
                });
                if (!ok) {
                    this.pesan = 'Lengkapi semua isian bertanda * pada tab ini.';
                    pertama.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    this.pesan = '';
                }
                return ok;
            },

            keAtas() {
                const m = document.querySelector('main');
                if (m) m.scrollTo({ top: 0, behavior: 'smooth' });
            },

            lanjut() {
                if (this.cekTab(this.tab)) { this.tab++; this.keAtas(); }
            },

            kembali() {
                this.pesan = '';
                this.tab--;
                this.keAtas();
            },

            keTab(i) {
                if (i <= this.tab) { this.pesan = ''; this.tab = i; return; }
                for (let k = this.tab; k < i; k++) {
                    if (!this.cekTab(k)) { this.tab = k; return; }
                }
                this.tab = i;
            },

            // Simpan langsung (status submit)
            kirim() {
                for (let k = 0; k < this.total; k++) {
                    if (!this.cekTab(k)) { this.tab = k; return; }
                }
                if (!this.$refs.pernyataan.checked) {
                    this.pesan = 'Centang pernyataan kebenaran data terlebih dahulu.';
                    document.getElementById('gc-pernyataan').classList.add('ring-2', 'ring-error');
                    return;
                }
                this.$refs.aksi.value = 'submit';
                this.$refs.form.submit();
            },
        };
    }

    /* ---------- Pratinjau foto ---------- */
    function gcPreview(input) {
        const img = input.parentElement.querySelector('.gc-preview');
        if (!img || !input.files || !input.files[0]) return;
        img.src = URL.createObjectURL(input.files[0]);
        img.hidden = false;
    }

    /* ---------- Peta koordinat lokasi ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('gc-map');
        if (!el || typeof L === 'undefined') return;

        const koor = document.querySelector('[data-gc-koordinat]');
        const lat = document.querySelector('[data-gc-lat]');
        const lng = document.querySelector('[data-gc-lng]');

        let pos = [-6.595, 106.816]; // Kota Bogor
        const mentah = koor && koor.value ? koor.value.split(',').map((x) => parseFloat(x)) : null;
        const ada = mentah && mentah.length === 2 && !isNaN(mentah[0]) && !isNaN(mentah[1]);
        if (ada) pos = mentah;

        const map = L.map(el).setView(pos, ada ? 17 : 13);
        window.gcMap = map;
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        let marker = ada ? L.marker(pos, { draggable: true }).addTo(map) : null;

        function set(latlng, geser) {
            const a = latlng.lat.toFixed(6), b = latlng.lng.toFixed(6);
            if (!marker) {
                marker = L.marker(latlng, { draggable: true }).addTo(map);
                marker.on('dragend', () => set(marker.getLatLng(), false));
            } else if (geser !== false) {
                marker.setLatLng(latlng);
            }
            if (koor) koor.value = a + ', ' + b;
            if (lat) lat.value = a;
            if (lng) lng.value = b;

            // Isian koordinat terisi lewat skrip (bukan ketikan), jadi bersihkan border merahnya manual
            [koor, lat, lng].forEach((x) => {
                const w = x && x.closest('[data-wajib]');
                if (w) w.classList.remove('ring-1', 'ring-error');
            });
        }

        if (marker) marker.on('dragend', () => set(marker.getLatLng(), false));
        map.on('click', (e) => set(e.latlng));

        const btn = document.getElementById('gc-lokasi-saya');
        if (btn) {
            btn.addEventListener('click', function () {
                if (!navigator.geolocation) { alert('Peramban tidak mendukung geolokasi.'); return; }
                navigator.geolocation.getCurrentPosition(
                    (p) => {
                        const ll = L.latLng(p.coords.latitude, p.coords.longitude);
                        set(ll);
                        map.setView(ll, 18);
                    },
                    () => alert('Lokasi tidak dapat diambil. Izinkan akses lokasi atau tandai manual di peta.')
                );
            });
        }
    });
</script>
@endpush
