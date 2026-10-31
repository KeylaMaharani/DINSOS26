{{-- Satu pertanyaan Master Ground Check pada form masyarakat. Variabel: $p, $nomor, $nilai, $opsiLabels, $master, $bisaEdit

     Perilaku tampilan (mobile-first):
     - Pilihan panjang (> 6 opsi, lapangan usaha, pekerjaan)  -> tombol yang membuka lembar pilihan + kolom cari
     - Pilihan hanya Ya / Tidak                                 -> kotak centang besar
     - Pilihan ganda panjang (> 8 opsi)                         -> kolom cari di dalam daftar centang
     - Pilihan pendek lainnya                                   -> dropdown bawaan browser --}}
@php
    use App\Support\PbiGcForm;

    $key = 'jawaban.' . $p->id;
    $val = old($key, $nilai[$p->id] ?? null);
    $wajib = ! PbiGcForm::opsional($p);
    $labels = collect($opsiLabels[$p->id] ?? [])->values()->all();
    $in = 'w-full rounded-xl border border-outline-variant px-3.5 py-3 text-base sm:text-sm bg-white focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 disabled:bg-surface-container disabled:text-on-surface-variant';

    // Deteksi pilihan Ya/Tidak (tanpa peduli besar-kecil huruf)
    $norm = fn ($t) => strtolower(trim((string) $t));
    $isYaTidak = $p->tipe_input === 'pilihan'
        && count($labels) === 2
        && collect($labels)->map($norm)->sort()->values()->all() === ['tidak', 'ya'];
    $yaLabel = $isYaTidak ? collect($labels)->first(fn ($l) => $norm($l) === 'ya') : null;
    $tidakLabel = $isYaTidak ? collect($labels)->first(fn ($l) => $norm($l) === 'tidak') : null;

    // Dropdown panjang / yang isinya dinamis dibuat bisa dicari
    $cari = $p->tipe_input === 'pilihan' && ! $isYaTidak
        && (count($labels) > 6 || in_array($p->sumber, ['lapangan_usaha', 'pekerjaan'], true));

    // Pertanyaan pendidikan: pilihan "kelas tertinggi" mengikuti "jenjang pendidikan" yang dipilih
    $namaLower = \Illuminate\Support\Str::lower($p->nama);
    $isJenjang = $p->kelompok === 'pendidikan' && str_contains($namaLower, 'jenjang') && str_contains($namaLower, 'pendidikan');
    $isKelas = $p->kelompok === 'pendidikan' && str_contains($namaLower, 'kelas tertinggi');

    $iconCek = '<svg viewBox="0 0 24 24" fill="none" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';
@endphp

<div class="min-w-0" @if ($wajib) data-gc-wajib @endif>
    <label for="jawaban-{{ $p->id }}" class="flex items-start gap-2 mb-2">
        <span class="mt-px shrink-0 inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 rounded-full bg-brand-soft text-brand text-[11px] font-bold">{{ $nomor }}</span>
        <span class="text-sm font-semibold text-on-surface leading-snug">
            <span data-gc-nama>{{ $p->nama }}</span> @if ($wajib)<span class="text-error">*</span>@endif
        </span>
    </label>

    @if ($isYaTidak)
        {{-- ===== Ya / Tidak: dua pilihan berdampingan (pilih salah satu). Nilai asli disimpan di input tersembunyi ===== --}}
        <input type="hidden" id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" value="{{ $val }}"
               data-gc-yn data-ya="{{ $yaLabel }}" data-tidak="{{ $tidakLabel }}" @disabled(! $bisaEdit)>
        <div class="gc-yn" role="radiogroup" aria-label="{{ $p->nama }}">
            @foreach ([$yaLabel, $tidakLabel] as $opsi)
                <button type="button" role="radio" class="gc-yn-opt" data-gc-yn-opt="{{ $opsi }}"
                        aria-checked="{{ (string) $val === (string) $opsi ? 'true' : 'false' }}" @disabled(! $bisaEdit)>
                    <span class="gc-box">{!! $iconCek !!}</span>
                    <span>{{ $opsi }}</span>
                </button>
            @endforeach
        </div>

    @elseif ($p->tipe_input === 'pilihan' && $labels)
        @php
            $atribut = '';
            if ($p->sumber === 'lapangan_usaha') {
                $atribut = 'data-gc-lapangan';
            } elseif ($p->sumber === 'pekerjaan') {
                $peta = $master['lapangan']->mapWithKeys(fn ($l) => [$l->nama => $l->pekerjaansAktif->pluck('nama')->values()->all()])->all();
            }
        @endphp
        {{-- Untuk pilihan yang bisa dicari: <select> asli tetap ada (dipakai form & skrip lain) tapi disembunyikan oleh skrip,
             lalu diganti tombol yang membuka lembar pilihan. --}}
        <select id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" @disabled(! $bisaEdit) class="{{ $in }}"
                {!! $atribut !!}
                @if ($isJenjang) data-gc-jenjang @endif
                @if ($isKelas) data-gc-kelas @endif
                @if ($cari) data-gc-cari data-judul="{{ $p->nama }}" @endif
                @if ($p->sumber === 'pekerjaan')
                    data-gc-pekerjaan data-nilai="{{ $val }}" data-peta="{{ json_encode($peta ?? [], JSON_UNESCAPED_UNICODE) }}"
                @endif>
            <option value="">- Pilih -</option>
            @foreach ($labels as $label)
                <option value="{{ $label }}" @selected((string) $val === (string) $label)>{{ $label }}</option>
            @endforeach
        </select>

    @elseif ($p->tipe_input === 'pilihan_ganda' && $labels)
        @php
            $dipilih = is_array($val) ? $val : (json_decode((string) $val, true) ?: []);
            $cariGanda = count($labels) > 8;
        @endphp
        <div class="gc-multi" data-gc-multi>
            @if ($cariGanda)
                <div class="gc-msearch">
                    <span class="material-symbols-outlined">search</span>
                    <input type="search" class="gc-sinput" placeholder="Cari pilihan..." autocomplete="off" data-gc-msearch>
                </div>
            @endif
            <div class="gc-mmeta">
                <span>Boleh pilih lebih dari satu</span>
                <b data-gc-mcount>{{ count($dipilih) ? count($dipilih) . ' dipilih' : '' }}</b>
            </div>
            <div class="gc-mlist">
                @foreach ($labels as $label)
                    <label class="gc-opt">
                        <input type="checkbox" class="sr-only" name="jawaban[{{ $p->id }}][]" value="{{ $label }}"
                               @checked(in_array($label, $dipilih, true)) @disabled(! $bisaEdit)>
                        <span class="gc-box">{!! $iconCek !!}</span>
                        <span class="gc-opt-text">{{ $label }}</span>
                    </label>
                @endforeach
                <p class="gcs-empty" data-gc-mempty hidden>Tidak ada pilihan yang cocok.</p>
            </div>
        </div>

    @elseif ($p->tipe_input === 'angka')
        @php $lb = PbiGcForm::lintangBujur($p); @endphp
        <input type="number" step="any" inputmode="decimal" @unless ($lb) min="0" @endunless id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]"
               value="{{ $val }}" @disabled(! $bisaEdit) class="{{ $in }}"
               @if (\Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($p->nama), 'lintang')) data-gc-lat @endif
               @if (\Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($p->nama), 'bujur')) data-gc-lng @endif>

    @elseif ($p->tipe_input === 'tanggal')
        <input type="date" id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" value="{{ $val }}" @disabled(! $bisaEdit) class="{{ $in }}">

    @elseif ($p->tipe_input === 'koordinat')
        <input type="text" id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" value="{{ $val }}" data-gc-koordinat
               placeholder="Terisi otomatis dari titik lokasi rumah" @disabled(! $bisaEdit) class="{{ $in }}">

    @elseif ($p->tipe_input === 'foto')
        @if ($val)
            <p class="flex items-center gap-1 text-xs text-success mb-1.5">
                <span class="material-symbols-outlined text-[15px]">check_circle</span> Sudah diunggah (ambil foto baru untuk mengganti)
            </p>
        @endif
        {{-- Teks "Choose file" bawaan browser tidak bisa diubah, jadi input disembunyikan dan diganti label bertombol "Ambil foto" --}}
        <input type="file" id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" accept="image/*" capture="environment"
               @if ($val) data-ada="1" @endif @disabled(! $bisaEdit) class="peer sr-only">
        <label for="jawaban-{{ $p->id }}"
               class="gc-file flex items-center gap-3 w-full rounded-xl border border-outline-variant bg-white p-1.5 cursor-pointer
                      peer-disabled:opacity-50 peer-disabled:cursor-not-allowed peer-focus-visible:ring-2 peer-focus-visible:ring-brand/40">
            <span class="shrink-0 inline-flex items-center gap-1.5 py-2.5 px-4 rounded-lg bg-primary text-white text-xs font-semibold">
                <span class="material-symbols-outlined text-[16px]">photo_camera</span> Ambil foto
            </span>
            <span class="min-w-0 truncate text-sm text-on-surface-variant" data-gc-filename>
                {{ $val ? 'Foto sudah ada' : 'Belum ada foto' }}
            </span>
        </label>

    @else
        {{-- teks, atau pilihan tanpa opsi di master --}}
        <input type="text" id="jawaban-{{ $p->id }}" name="jawaban[{{ $p->id }}]" value="{{ $val }}" maxlength="500"
               @disabled(! $bisaEdit) class="{{ $in }}">
    @endif

    @error($key)<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
    @error($key . '.*')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
</div>

{{-- ===== Gaya & skrip dipasang sekali saja, walau partial ini dipakai berkali-kali ===== --}}
@once
    @push('head')
        <style>
            /* Tombol pengganti dropdown panjang */
            .gc-trigger { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: .5rem; text-align: left;
                min-height: 48px; padding: .75rem .875rem; border: 1px solid #c2c7cf; border-radius: .75rem; background: #fff;
                font-size: 1rem; color: #121d26; cursor: pointer; }
            .gc-trigger .material-symbols-outlined { color: #136299; flex: none; }
            .gc-trigger.gc-placeholder .gc-trigger-text { color: #6b7280; }
            .gc-trigger:disabled { background: #e4effd; color: #42474e; cursor: not-allowed; }
            .gc-trigger:focus-visible { outline: 2px solid #0a5ca8; outline-offset: 1px; }
            .gc-trigger-text { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

            /* Kotak centang besar (Ya/Tidak & pilihan ganda) */
            .gc-opt { display: flex; align-items: center; gap: .75rem; min-height: 48px; padding: .625rem .875rem;
                border: 1.5px solid #d5dae1; border-radius: .75rem; background: #fff; cursor: pointer;
                transition: background-color .15s, border-color .15s; }
            .gc-opt[hidden] { display: none !important; }
            .gc-opt:has(input:checked) { border-color: #0a5ca8; background: #e8f4fd; }
            .gc-opt:has(input:disabled) { opacity: .6; cursor: not-allowed; }
            .gc-opt:has(input:focus-visible) { outline: 2px solid #0a5ca8; outline-offset: 2px; }
            .gc-box { flex: none; width: 24px; height: 24px; border: 2px solid #9aa3ad; border-radius: 7px; background: #fff;
                display: flex; align-items: center; justify-content: center; transition: background-color .15s, border-color .15s; }
            .gc-box svg { width: 15px; height: 15px; stroke: #fff; opacity: 0; }
            .gc-opt input:checked ~ .gc-box { background: #0a5ca8; border-color: #0a5ca8; }
            .gc-opt input:checked ~ .gc-box svg { opacity: 1; }
            .gc-opt-text { flex: 1; min-width: 0; font-size: .9375rem; line-height: 1.35; color: #121d26; }
            .gc-opt-text strong { font-weight: 700; }
            .gc-opt-text small { display: block; font-size: .75rem; color: #6b7280; margin-top: 2px; }


            /* Ya / Tidak: dua pilihan berdampingan */
            .gc-yn { display: grid; grid-template-columns: 1fr 1fr; gap: .625rem; }
            .gc-yn-opt { display: flex; align-items: center; justify-content: center; gap: .625rem; min-height: 54px; padding: .5rem .75rem;
                border: 1.5px solid #d5dae1; border-radius: .875rem; background: #fff; font-size: 1rem; font-weight: 600; color: #121d26;
                cursor: pointer; transition: background-color .15s, border-color .15s; }
            .gc-yn-opt:focus-visible { outline: 2px solid #0a5ca8; outline-offset: 2px; }
            .gc-yn-opt:disabled { opacity: .6; cursor: not-allowed; }
            .gc-yn-opt[aria-checked="true"] { border-color: #0a5ca8; background: #e8f4fd; color: #003b62; }
            .gc-yn-opt[aria-checked="true"] .gc-box { background: #0a5ca8; border-color: #0a5ca8; }
            .gc-yn-opt[aria-checked="true"] .gc-box svg { opacity: 1; }

            /* Pertanyaan wajib yang belum diisi */
            .gc-err-msg { margin: .375rem 0 0; font-size: .75rem; font-weight: 600; color: #ba1a1a; }
            .gc-err-on .gc-yn-opt, .gc-err-on .gc-trigger, .gc-err-on .gc-multi, .gc-err-on .gc-file, .gc-err-on input:not([type=checkbox]):not([type=hidden]):not([type=search]):not([type=file]) { border-color: #ba1a1a; }
            .gc-err-on .gc-yn-opt:not([aria-checked="true"]), .gc-err-on .gc-trigger, .gc-err-on .gc-file { background: #fff5f4; }

            /* Pilihan ganda */
            .gc-multi { border: 1px solid #c2c7cf; border-radius: .875rem; background: #f7f9ff; padding: .5rem; }
            .gc-mmeta { display: flex; justify-content: space-between; gap: .5rem; padding: .125rem .375rem .5rem; font-size: .75rem; color: #42474e; }
            .gc-mmeta b { color: #0a5ca8; }
            .gc-mlist { display: flex; flex-direction: column; gap: .375rem; max-height: 18rem; overflow-y: auto; overscroll-behavior: contain; }
            .gc-msearch { position: relative; margin-bottom: .25rem; }
            .gc-msearch .material-symbols-outlined { position: absolute; left: .625rem; top: 50%; transform: translateY(-50%); font-size: 20px; color: #6b7280; pointer-events: none; }

            /* Kolom cari */
            .gc-sinput { width: 100%; height: 46px; padding: 0 .875rem 0 2.5rem; border: 1px solid #c2c7cf; border-radius: .75rem;
                background: #fff; font-size: 1rem; color: #121d26; }
            .gc-sinput:focus { outline: none; border-color: #0a5ca8; box-shadow: 0 0 0 3px rgba(10, 92, 168, .18); }

            /* Lembar pilihan (bottom sheet di HP, kotak tengah di layar lebar) */
            html.gcs-lock { overflow: hidden; }
            .gcs { position: fixed; left: 0; right: 0; top: 0; height: 100%; z-index: 80; display: none; align-items: flex-end; justify-content: center; }
            .gcs.is-open { display: flex; }
            .gcs-backdrop { position: absolute; inset: 0; background: rgba(18, 29, 38, .5); }
            .gcs-panel { position: relative; width: 100%; max-height: 85vh; max-height: 85dvh; display: flex; flex-direction: column;
                background: #fff; border-radius: 1.5rem 1.5rem 0 0; box-shadow: 0 -10px 30px rgba(0, 0, 0, .2);
                padding-bottom: env(safe-area-inset-bottom, 0); animation: gcs-up .2s ease-out; }
            .gcs-handle { width: 40px; height: 4px; border-radius: 9999px; background: #c2c7cf; margin: .625rem auto .125rem; flex: none; }
            .gcs-head { display: flex; align-items: flex-start; gap: .75rem; padding: .5rem .5rem .25rem 1.25rem; flex: none; }
            .gcs-title { flex: 1; margin: 0; padding-top: .5rem; font-weight: 700; font-size: .95rem; line-height: 1.35; color: #003b62; }
            .gcs-close { flex: none; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 9999px; color: #42474e; }
            .gcs-close:active { background: #edf4ff; }
            .gcs-search { position: relative; margin: .5rem 1rem; flex: none; }
            .gcs-search .gcs-sicon { position: absolute; left: .625rem; top: 50%; transform: translateY(-50%); font-size: 20px; color: #6b7280; pointer-events: none; }
            .gcs-list { position: relative; list-style: none; margin: 0; padding: .25rem .5rem 1rem; flex: 1; min-height: 0; overflow-y: auto; overscroll-behavior: contain; }
            .gcs-item { width: 100%; display: flex; align-items: center; gap: .75rem; text-align: left; min-height: 48px;
                padding: .75rem; border-radius: .75rem; font-size: .95rem; line-height: 1.3; color: #121d26; }
            .gcs-item:active, .gcs-item:hover { background: #edf4ff; }
            .gcs-item.is-selected { background: #e8f4fd; color: #003b62; font-weight: 700; }
            .gcs-item .material-symbols-outlined { margin-left: auto; flex: none; color: #0a5ca8; }
            .gcs-item.is-clear { color: #ba1a1a; }
            .gcs-item.is-clear .material-symbols-outlined { color: #ba1a1a; margin-left: 0; }
            .gcs-empty { margin: 0; padding: 1.5rem 1rem; text-align: center; font-size: .875rem; color: #6b7280; }
            @@media (min-width: 640px) {
                .gcs { align-items: center; }
                .gcs-panel { max-width: 28rem; max-height: 70vh; border-radius: 1.25rem; padding-bottom: 0; box-shadow: 0 20px 50px rgba(0, 0, 0, .25); }
                .gcs-handle { display: none; }
            }
            @@keyframes gcs-up { from { transform: translateY(24px); opacity: 0; } to { transform: none; opacity: 1; } }
        </style>
    @endpush

    @push('scripts')
        <script>
        (function () {
            /* ---------- Ya / Tidak (pilih salah satu) ---------- */
            function ynRefresh(h) {
                var wrap = h.closest('.min-w-0');
                if (!wrap) { return; }
                wrap.querySelectorAll('[data-gc-yn-opt]').forEach(function (b) {
                    b.setAttribute('aria-checked', b.dataset.gcYnOpt === h.value ? 'true' : 'false');
                    b.disabled = h.disabled;
                });
            }

            /* ---------- Pertanyaan wajib: tidak boleh lanjut kalau masih kosong ---------- */
            function terisi(w) {
                var els = Array.prototype.filter.call(
                    w.querySelectorAll('input[name^="jawaban"], select[name^="jawaban"], textarea[name^="jawaban"]'),
                    function (e) { return !e.disabled; }
                );
                if (!els.length) { return true; } // dinonaktifkan oleh aturan lain (mis. tidak bekerja)
                var cb = els.filter(function (e) { return e.type === 'checkbox'; });
                if (cb.length) { return cb.some(function (c) { return c.checked; }); }
                var e = els[0];
                if (e.type === 'file') { return (e.files && e.files.length > 0) || e.dataset.ada === '1'; }
                return String(e.value).trim() !== '';
            }

            function tandaiKosong(w) {
                w.classList.add('gc-err-on');
                if (!w.querySelector('.gc-err-msg')) {
                    var m = document.createElement('p');
                    m.className = 'gc-err-msg';
                    m.textContent = 'Wajib diisi.';
                    w.appendChild(m);
                }
            }

            function hapusTanda(w) {
                w.classList.remove('gc-err-on');
                var m = w.querySelector('.gc-err-msg');
                if (m) { m.remove(); }
            }

            function bersihkanTanda(e) {
                var w = e.target && e.target.closest ? e.target.closest('[data-gc-wajib]') : null;
                if (w && w.classList.contains('gc-err-on') && terisi(w)) { hapusTanda(w); }
            }
            document.addEventListener('change', bersihkanTanda);
            document.addEventListener('input', bersihkanTanda);

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form || !form.querySelectorAll) { return; }
                var kosong = [];
                form.querySelectorAll('[data-gc-wajib]').forEach(function (w) {
                    if (terisi(w)) { hapusTanda(w); } else { tandaiKosong(w); kosong.push(w); }
                });
                if (kosong.length) {
                    e.preventDefault();
                    kosong[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    window.alert(kosong.length + ' pertanyaan wajib belum dijawab. Lengkapi dulu yang ditandai merah.');
                }
            }, true);

            document.addEventListener('click', function (e) {
                var b = e.target.closest ? e.target.closest('[data-gc-yn-opt]') : null;
                if (!b || b.disabled) { return; }
                var h = b.closest('.min-w-0').querySelector('[data-gc-yn]');
                if (h.value === b.dataset.gcYnOpt) { return; }
                h.value = b.dataset.gcYnOpt;
                ynRefresh(h);
                // Beri tahu form (mis. skrip "bekerja? Ya/Tidak" yang mengaktifkan field lain)
                h.dispatchEvent(new Event('change', { bubbles: true }));
            });

            /* ---------- Foto: tampilkan nama file setelah dipilih ---------- */
            document.addEventListener('change', function (e) {
                var t = e.target;
                if (!t || t.type !== 'file' || !t.matches('input[name^="jawaban"]')) { return; }
                var n = t.closest('.min-w-0').querySelector('[data-gc-filename]');
                if (!n) { return; }
                n.textContent = t.files && t.files.length
                    ? t.files[0].name
                    : (t.dataset.ada === '1' ? 'Foto sudah ada' : 'Belum ada foto');
            });

            /* ---------- Pilihan ganda: hitung yang dipilih ---------- */
            function multiCount(m) {
                var c = m.querySelectorAll('input[type=checkbox]:checked').length;
                var el = m.querySelector('[data-gc-mcount]');
                if (el) { el.textContent = c ? c + ' dipilih' : ''; }
            }

            document.addEventListener('change', function (e) {
                var t = e.target;
                if (!t || !t.matches) { return; }
                if (t.matches('[data-gc-yn]')) {
                    ynRefresh(t);
                } else if (t.type === 'checkbox') {
                    var m = t.closest('[data-gc-multi]');
                    if (m) { multiCount(m); }
                }
            });

            document.addEventListener('input', function (e) {
                var t = e.target;
                if (!t || !t.matches || !t.matches('[data-gc-msearch]')) { return; }
                var m = t.closest('[data-gc-multi]');
                var k = t.value.trim().toLowerCase();
                var ada = false;
                m.querySelectorAll('.gc-opt').forEach(function (o) {
                    var tampil = !k || o.textContent.toLowerCase().indexOf(k) >= 0;
                    o.hidden = !tampil;
                    if (tampil) { ada = true; }
                });
                var kosong = m.querySelector('[data-gc-mempty]');
                if (kosong) { kosong.hidden = ada; }
            });

            /* ---------- Lembar pilihan untuk dropdown panjang ---------- */
            var sheet, panel, titleEl, searchWrap, input, list, emptyEl, current = null;

            function fit() {
                if (!sheet) { return; }
                var vv = window.visualViewport;
                if (vv) {
                    // Ikuti area yang benar-benar terlihat supaya tidak tertutup keyboard HP
                    sheet.style.top = vv.offsetTop + 'px';
                    sheet.style.height = vv.height + 'px';
                    panel.style.maxHeight = Math.round(vv.height * 0.88) + 'px';
                }
            }

            function closeSheet() {
                if (!sheet || !current) { return; }
                sheet.classList.remove('is-open');
                document.documentElement.classList.remove('gcs-lock');
                var trg = current.trigger;
                current = null;
                if (trg) { trg.focus(); }
            }

            function choose(v) {
                var sel = current.sel;
                sel.value = v;
                sel.dispatchEvent(new Event('change', { bubbles: true }));
                closeSheet();
            }

            function render() {
                var sel = current.sel;
                var k = input.value.trim().toLowerCase();
                var val = sel.value;
                var total = 0;
                var tampil = 0;
                list.innerHTML = '';

                function item(label, selected, extraClass, icon, onClick) {
                    var li = document.createElement('li');
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'gcs-item' + (selected ? ' is-selected' : '') + (extraClass ? ' ' + extraClass : '');
                    b.setAttribute('role', 'option');
                    b.setAttribute('aria-selected', selected ? 'true' : 'false');
                    if (icon && extraClass) {
                        var ic0 = document.createElement('span');
                        ic0.className = 'material-symbols-outlined';
                        ic0.textContent = icon;
                        b.appendChild(ic0);
                    }
                    var s = document.createElement('span');
                    s.textContent = label;
                    b.appendChild(s);
                    if (icon && !extraClass) {
                        var ic = document.createElement('span');
                        ic.className = 'material-symbols-outlined';
                        ic.textContent = icon;
                        b.appendChild(ic);
                    }
                    b.addEventListener('click', onClick);
                    li.appendChild(b);
                    list.appendChild(li);
                }

                if (val !== '' && !k) {
                    item('Hapus pilihan', false, 'is-clear', 'close', function () { choose(''); });
                }

                Array.prototype.forEach.call(sel.options, function (o) {
                    if (o.value === '') { return; }
                    total++;
                    if (k && o.text.toLowerCase().indexOf(k) < 0) { return; }
                    tampil++;
                    item(o.text, o.value === val, '', o.value === val ? 'check' : '', function () { choose(o.value); });
                });

                searchWrap.hidden = total <= 6;
                emptyEl.hidden = tampil > 0;
                if (total === 0) {
                    emptyEl.textContent = sel.options.length ? sel.options[0].text : 'Belum ada pilihan.';
                } else {
                    emptyEl.textContent = 'Tidak ada hasil untuk "' + input.value.trim() + '".';
                }
            }

            function build() {
                sheet = document.createElement('div');
                sheet.className = 'gcs';
                sheet.innerHTML =
                    '<div class="gcs-backdrop" data-gcs-close></div>' +
                    '<div class="gcs-panel" role="dialog" aria-modal="true" aria-labelledby="gcs-title">' +
                        '<div class="gcs-handle"></div>' +
                        '<div class="gcs-head"><p class="gcs-title" id="gcs-title"></p>' +
                            '<button type="button" class="gcs-close" data-gcs-close aria-label="Tutup"><span class="material-symbols-outlined">close</span></button></div>' +
                        '<div class="gcs-search"><span class="material-symbols-outlined gcs-sicon">search</span>' +
                            '<input type="search" class="gc-sinput" placeholder="Cari..." autocomplete="off" enterkeyhint="search" aria-label="Cari pilihan"></div>' +
                        '<ul class="gcs-list" role="listbox"></ul>' +
                        '<p class="gcs-empty" hidden></p>' +
                    '</div>';
                document.body.appendChild(sheet);

                panel = sheet.querySelector('.gcs-panel');
                titleEl = sheet.querySelector('.gcs-title');
                searchWrap = sheet.querySelector('.gcs-search');
                input = sheet.querySelector('.gc-sinput');
                list = sheet.querySelector('.gcs-list');
                emptyEl = sheet.querySelector('.gcs-empty');

                sheet.addEventListener('click', function (e) {
                    if (e.target.closest('[data-gcs-close]')) { closeSheet(); }
                });
                input.addEventListener('input', function () { if (current) { render(); } });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && current) { closeSheet(); }
                });
                if (window.visualViewport) {
                    window.visualViewport.addEventListener('resize', fit);
                    window.visualViewport.addEventListener('scroll', fit);
                }
            }

            function openSheet(sel, trigger) {
                if (!sheet) { build(); }
                current = { sel: sel, trigger: trigger };
                titleEl.textContent = sel.dataset.judul || 'Pilih';
                input.value = '';
                render();
                sheet.classList.add('is-open');
                document.documentElement.classList.add('gcs-lock');
                fit();

                var terpilih = list.querySelector('.is-selected');
                if (terpilih) {
                    list.scrollTop = terpilih.parentNode.offsetTop - list.clientHeight / 2 + terpilih.offsetHeight / 2;
                }
                // Di HP jangan langsung munculkan keyboard (menutupi daftar); di desktop langsung fokus ke kolom cari
                if (!searchWrap.hidden && window.matchMedia('(hover: hover)').matches) { input.focus(); }
            }

            function initSelect(sel) {
                var trg = document.createElement('button');
                trg.type = 'button';
                trg.className = 'gc-trigger';
                trg.innerHTML = '<span class="gc-trigger-text"></span><span class="material-symbols-outlined">expand_more</span>';
                var txt = trg.firstChild;

                function sync() {
                    var o = sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex] : null;
                    txt.textContent = o ? o.text : '- Pilih -';
                    trg.classList.toggle('gc-placeholder', !o || o.value === '');
                    trg.disabled = sel.disabled;
                }

                sel.parentNode.insertBefore(trg, sel);
                sel.classList.add('hidden');
                sel.addEventListener('change', sync);
                // Opsi dibangun ulang (mis. pekerjaan mengikuti lapangan usaha) / field dinonaktifkan
                new MutationObserver(sync).observe(sel, { childList: true, attributes: true, attributeFilter: ['disabled'] });
                trg.addEventListener('click', function () { openSheet(sel, trg); });
                sync();
            }

            /* ---------- Pendidikan: "kelas tertinggi" mengikuti jenjang ----------
               SD maks 6 kelas, SMP/SMA maks 3 kelas, perguruan tinggi memakai semester (D1 2, D2 4, D3 6, D4/S1 8) */
            function aturanKelas(teks) {
                var t = ' ' + (teks || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim() + ' ';
                // Perguruan tinggi -> semester
                if (/\b(s ?[23]|pasca|magister|doktor|profesi|spesialis)\b/.test(t)) { return { maks: 0, semester: true }; }
                if (/\b(d ?(4|iv)|s ?1|sarjana)\b/.test(t)) { return { maks: 8, semester: true }; }
                if (/\bd ?(3|iii)\b/.test(t)) { return { maks: 6, semester: true }; }
                if (/\bd ?(2|ii)\b/.test(t)) { return { maks: 4, semester: true }; }
                if (/\bd ?(1|i)\b/.test(t)) { return { maks: 2, semester: true }; }
                if (/\b(perguruan tinggi|universitas|akademi|diploma|institut)\b/.test(t)) { return { maks: 0, semester: true }; }
                // Sekolah -> kelas
                if (/\b(smp|mts|smplb|sma|ma|mak|smk|smlb|paket b|paket c)\b/.test(t)) { return { maks: 3, semester: false }; }
                if (/\b(sd|mi|sdlb|paket a|sekolah dasar)\b/.test(t)) { return { maks: 6, semester: false }; }
                return { maks: 0, semester: false }; // jenjang lain: tidak dibatasi
            }

            function initKelas() {
                var j = document.querySelector('select[data-gc-jenjang]');
                var k = document.querySelector('select[data-gc-kelas]');
                if (!j || !k) { return; }

                var semua = Array.prototype.map.call(k.options, function (o) { return { v: o.value, t: o.text }; })
                    .filter(function (o) { return o.v !== ''; });
                var bisaEdit = !k.disabled;
                var wrap = k.closest('.min-w-0');
                var namaEl = wrap ? wrap.querySelector('[data-gc-nama]') : null;
                var namaAsli = namaEl ? namaEl.textContent : '';
                var judulAsli = k.dataset.judul || namaAsli;

                function terapkan(awal) {
                    var sebelum = k.value;
                    var jo = j.selectedIndex >= 0 ? j.options[j.selectedIndex] : null;
                    var jt = jo && jo.value !== '' ? jo.text : '';
                    var a = aturanKelas(jt);

                    // Judul pertanyaan: "Kelas ..." menjadi "Semester ..." untuk perguruan tinggi
                    var judul = a.semester ? namaAsli.replace(/^\s*kelas/i, 'Semester') : namaAsli;
                    if (namaEl) { namaEl.textContent = judul; }
                    k.dataset.judul = a.semester ? judulAsli.replace(/^\s*kelas/i, 'Semester') : judulAsli;

                    k.innerHTML = '';
                    k.add(new Option(jt ? '- Pilih -' : '- Pilih jenjang pendidikan dahulu -', ''));
                    if (a.semester && a.maks) {
                        // Semester dibuat penuh sesuai jenjang (D4/S1 = 8), tidak bergantung jumlah angka di master.
                        // Bila angkanya ada di master, nilai master dipakai; bila tidak, nilainya angka itu sendiri.
                        for (var n = 1; n <= a.maks; n++) {
                            var cocok = semua.filter(function (o) { var m = o.t.match(/\d+/); return m && parseInt(m[0], 10) === n; })[0];
                            k.add(new Option('Semester ' + n, cocok ? cocok.v : String(n)));
                        }
                        // Pilihan tanpa angka di master (mis. "Tamat") tetap disertakan
                        semua.forEach(function (o) { if (!/\d/.test(o.t)) { k.add(new Option(o.t, o.v)); } });
                    } else {
                        semua.forEach(function (o) {
                            var m = o.t.match(/\d+/);
                            if (a.maks && m && parseInt(m[0], 10) > a.maks) { return; }
                            k.add(new Option(a.semester && m ? 'Semester ' + m[0] : o.t, o.v));
                        });
                    }

                    var masihAda = sebelum !== '' && Array.prototype.some.call(k.options, function (o) { return o.value === sebelum; });
                    k.value = masihAda ? sebelum : '';
                    if (bisaEdit) { k.disabled = !jt; }
                    if (!awal && sebelum !== k.value) { k.dispatchEvent(new Event('change', { bubbles: true })); }
                }

                j.addEventListener('change', function () { terapkan(false); });
                terapkan(true);
            }

            function init() {
                document.querySelectorAll('select[data-gc-cari]').forEach(initSelect);
                document.querySelectorAll('[data-gc-yn]').forEach(function (h) {
                    ynRefresh(h);
                    new MutationObserver(function () { ynRefresh(h); }).observe(h, { attributes: true, attributeFilter: ['disabled'] });
                });
                document.querySelectorAll('[data-gc-multi]').forEach(multiCount);
                initKelas();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
        </script>
    @endpush
@endonce
