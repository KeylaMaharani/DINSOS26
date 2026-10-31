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

    @if (session('success'))
        <div class="px-4 py-3 rounded-lg bg-green-100 text-green-800 text-sm">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <p class="font-semibold mb-1">Data belum dapat disimpan:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form id="gc-form" method="POST" action="{{ route('ground-check.simpan', $sasaran) }}" enctype="multipart/form-data" x-ref="form" novalidate>
        @csrf
        {{-- Bawaan "draft" supaya tidak pernah menembus validasi JS secara tidak sengaja.
             Tombol "Submit" mengubahnya menjadi "submit" lewat kirim(). --}}
        <input type="hidden" name="aksi" value="draft" x-ref="aksi">
        <input type="hidden" name="tab_aktif" :value="tab">

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">

            {{-- Header tab (dengan badge progres: Belum diisi / Draft x/y / Lengkap) --}}
            <div class="flex overflow-x-auto border-b border-outline-variant/40">
                @foreach ($tabs as $i => $t)
                    @php
                        $st = $statusTab[$i] ?? ['status' => 'kosong', 'isi' => 0, 'total' => 0];
                        $badge = [
                            'lengkap' => ['Lengkap', 'bg-green-100 text-green-700'],
                            'draft'   => ['Draft ' . $st['isi'] . '/' . $st['total'], 'bg-amber-100 text-amber-700'],
                            'kosong'  => ['Belum diisi', 'bg-surface-container text-on-surface-variant'],
                        ][$st['status']];
                    @endphp
                    <button type="button" @click="keTab({{ $i }})"
                        :class="tab === {{ $i }} ? 'text-brand border-b-2 border-brand bg-brand-soft/60' : 'text-on-surface-variant hover:bg-surface-container-low'"
                        class="flex-1 min-w-[120px] px-3 py-3 flex flex-col items-center gap-1 text-[11px] font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[22px]">{{ $t['icon'] }}</span>
                        {{ $t['label'] }}
                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full {{ $badge[1] }}">{{ $badge[0] }}</span>
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
                    <div id="gc-panel-{{ $i }}" x-show="tab === {{ $i }}" @if ($i !== $mulai) x-cloak @endif class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                        @foreach ($t['kelompok'] as $kel)
                            @php $isi = $t['params']->where('kelompok', $kel); @endphp
                            @if ($isi->isNotEmpty())
                                @if (count($t['kelompok']) > 1 && isset(GcP::SUBJUDUL[$kel]))
                                    <h4 class="text-xs font-bold text-on-surface mt-4 mb-1 first:mt-0">{{ GcP::SUBJUDUL[$kel] }}</h4>
                                @endif
                                @foreach ($isi as $p)
                                    {{-- Parameter bersumber master memakai partial sendiri --}}
                                    @include(
                                        $p->sumber === 'penyakit_kronis'
                                            ? 'admin.ground-check._field_penyakit'
                                            : ($p->sumber ? 'admin.ground-check._field_master' : 'admin.ground-check._field'),
                                        ['p' => $p, 'nilai' => $nilai, 'master' => $master]
                                    )
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
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="draft()"
                        class="inline-flex items-center gap-1 px-4 py-2 rounded-lg text-sm border border-primary text-primary bg-white hover:bg-surface-container-low font-semibold">
                        <span class="material-symbols-outlined text-[16px]">draft</span> Simpan Draft
                    </button>
                    <button type="button" x-show="tab < total - 1" @click="lanjut()"
                        class="inline-flex items-center gap-1 px-5 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">
                        Lanjut <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </button>
                    <button type="button" x-show="tab === total - 1" x-cloak @click="kirim()"
                        class="inline-flex items-center gap-1 px-5 py-2 rounded-lg text-sm bg-success text-white hover:opacity-90 font-semibold">
                        <span class="material-symbols-outlined text-[16px]">send</span> Submit
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

            // true jika semua kontrol pada baris w nonaktif (mis. Ketenagakerjaan saat jawabannya "Tidak")
            nonaktif(w) {
                const els = w.querySelectorAll('input,select,textarea');
                return els.length > 0 && [...els].every((e) => e.disabled);
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
                    // Baris yang dinonaktifkan (tidak berlaku) tidak diwajibkan
                    if (this.nonaktif(w)) {
                        w.classList.remove('ring-1', 'ring-error');
                        return;
                    }
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

            // Navigasi antar tab bebas (tanpa validasi) supaya petugas bisa pindah tab
            // saat berkas belum ada. Kewajiban semua field dicek saat Submit.
            lanjut() { this.pesan = ''; this.tab++; this.keAtas(); },

            kembali() { this.pesan = ''; this.tab--; this.keAtas(); },

            keTab(i) { this.pesan = ''; this.tab = i; this.keAtas(); },

            // Simpan sebagai draf: tanpa validasi, semua tab ikut tersimpan
            draft() {
                this.$refs.aksi.value = 'draft';
                this.$refs.form.submit();
            },

            // Submit akhir: semua tab wajib lengkap + pernyataan dicentang
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

{{-- Lapangan usaha -> panel info + pilihan pekerjaan yang menyesuaikan --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lap = document.querySelector('[data-gc-lapangan]');
    if (!lap) return;
    const pk = document.querySelector('[data-gc-pekerjaan]');
    const panel = document.querySelector('[data-gc-info]');
    const peta = pk ? JSON.parse(pk.dataset.peta || '{}') : {};

    function info() {
        if (!panel) return;
        const opt = lap.options[lap.selectedIndex];
        if (!lap.value || !opt || !opt.dataset.info) { panel.hidden = true; return; }
        const d = JSON.parse(opt.dataset.info);
        panel.querySelector('[data-i="ikon"]').textContent = d.ikon || '';
        panel.querySelector('[data-i="nama"]').textContent = lap.value;
        panel.querySelector('[data-i="penjelasan"]').textContent = d.penjelasan || '-';
        ['sendiri', 'buruh'].forEach((k) => {
            panel.querySelector('[data-row="' + k + '"]').hidden = !d[k];
            panel.querySelector('[data-i="' + k + '"]').textContent = d[k] || '';
        });
        panel.hidden = false;
    }

    function pekerjaan(pertama) {
        if (!pk) return;
        const daftar = peta[lap.value] || [];
        const simpan = pertama ? pk.dataset.nilai : pk.value;
        pk.innerHTML = '';
        pk.add(new Option(lap.value ? '-Pilih-' : '-Pilih lapangan usaha dahulu-', ''));
        daftar.forEach((n) => pk.add(new Option(n, n)));
        pk.value = daftar.includes(simpan) ? simpan : '';
        pk.disabled = !lap.value;
    }

    lap.addEventListener('change', () => { info(); pekerjaan(false); });
    info(); pekerjaan(true);
});
</script>

{{-- Ketenagakerjaan: jika "Apakah responden bekerja ...?" dijawab Tidak, field lain dinonaktifkan & dikosongkan.
     Dijawab Ya -> field terbuka dan wajib diisi. Script ini HARUS setelah script lapangan usaha di atas. --}}
<script>
window.gcKerja = @json($kerja);

document.addEventListener('DOMContentLoaded', function () {
    const cfg = window.gcKerja;
    const form = document.getElementById('gc-form');
    if (!cfg || !form) return;

    const kontrol = (id) => [...form.querySelectorAll(`[name="jawaban[${id}]"],[name="jawaban[${id}][]"]`)];

    function jawabanGate() {
        const els = kontrol(cfg.gate);
        const radio = els.find((e) => e.type === 'radio' && e.checked);
        if (radio) return radio.value;
        const lain = els.find((e) => e.type !== 'radio');
        return lain ? lain.value : '';
    }

    function terapkan() {
        const ya = jawabanGate() === 'Ya';

        cfg.dependen.forEach((id) => {
            kontrol(id).forEach((el) => {
                el.disabled = !ya;

                if (!ya) {
                    if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
                    else if (el.type !== 'file') el.value = '';
                    // beri tahu skrip lain (panel info lapangan usaha, daftar pekerjaan)
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }

                const baris = el.closest('[data-wajib]') || el.parentElement;
                if (baris) {
                    baris.classList.toggle('opacity-50', !ya);
                    baris.classList.toggle('pointer-events-none', !ya);
                    if (!ya) baris.classList.remove('ring-1', 'ring-error');
                }
            });
        });

        // Pekerjaan baru aktif bila responden bekerja DAN lapangan usaha sudah dipilih
        const lap = form.querySelector('[data-gc-lapangan]');
        const pk = form.querySelector('[data-gc-pekerjaan]');
        if (ya && lap && pk) pk.disabled = !lap.value;
    }

    form.addEventListener('change', (e) => {
        if (e.target.name && e.target.name.startsWith(`jawaban[${cfg.gate}]`)) terapkan();
    });

    terapkan();
});
</script>
@endpush
