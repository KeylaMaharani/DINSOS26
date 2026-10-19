@extends('layouts.admin')

@php
    use App\Models\GcKriteriaParameter as GcP;

    $isEdit = $parameter->exists;

    $opsiRows = old('opsi');
    if ($opsiRows === null) {
        $opsiRows = $parameter->opsis->map(fn ($o) => [
            'id' => $o->id,
            'label' => $o->label,
            'nilai' => $o->nilai === null ? '' : (string) $o->nilai,
        ])->values()->all();
    }
    if (empty($opsiRows)) {
        $opsiRows = [
            ['id' => null, 'label' => '', 'nilai' => ''],
            ['id' => null, 'label' => '', 'nilai' => ''],
        ];
    }
    $opsiRows = collect($opsiRows)->values()->map(fn ($r) => [
        'id' => $r['id'] ?? '',
        'label' => $r['label'] ?? '',
        'nilai' => $r['nilai'] ?? '',
    ])->all();

    // Parameter baru default ke Pilihan ganda.
    $tipeAktif = old('tipe_input', $isEdit ? $parameter->tipe_input : 'pilihan_ganda');
    if (! $isEdit && old('tipe_input') === null) {
        $tipeAktif = 'pilihan_ganda';
    }

    // Hanya dua jenis yang ditawarkan. Parameter lama dengan jenis lain (angka, foto, dll)
    // tetap menampilkan jenisnya sendiri sebagai kartu tambahan, supaya tidak berubah
    // diam-diam saat disimpan dan form entry Ground Check tidak rusak.
    $tipeInfo = [
        'pilihan_ganda' => ['judul' => 'Pilihan ganda', 'icon' => 'checklist', 'desc' => 'Petugas memilih dari daftar jawaban. Cocok untuk Ya/Tidak dan pilihan lainnya.'],
        'teks'          => ['judul' => 'Isian',         'icon' => 'notes',     'desc' => 'Petugas mengetik jawaban sendiri (teks bebas).'],
    ];
    $tipeTampil = ['pilihan_ganda', 'teks'];
    if (! in_array($tipeAktif, $tipeTampil, true)) {
        $tipeTampil[] = $tipeAktif;
        $tipeInfo[$tipeAktif] = [
            'judul' => (GcP::TIPE_INPUT[$tipeAktif] ?? $tipeAktif) . ' (jenis lama)',
            'icon'  => 'history',
            'desc'  => 'Jenis bawaan data lama. Tetap dipertahankan agar form entry tidak berubah.',
        ];
    }

    $init = ['rows' => $opsiRows];
@endphp

@section('title', $isEdit ? 'Edit Parameter Ground Check' : 'Tambah Parameter Ground Check')
@section('page_title', 'Master Kriteria Ground Check — ' . ($isEdit ? 'Edit Parameter' : 'Tambah Parameter'))

@section('content')
<div class="max-w-4xl space-y-5">
    <a href="{{ route('master.gc-kriteria.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
    </a>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if ($isEdit && $parameter->pbiParameter)
        <div class="px-4 py-3 rounded-lg bg-brand-soft text-brand text-xs leading-relaxed">
            <strong>Padanan PBI:</strong> {{ $parameter->pbiParameter->nama }}.
            Opsi di sini adalah salinan; mengubahnya tidak mengubah master kriteria PBI.
        </div>
    @endif

    <form id="gc-form" method="POST"
          action="{{ $isEdit ? route('master.gc-kriteria.update', $parameter) : route('master.gc-kriteria.store') }}"
          class="space-y-5">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        {{-- 1. Jenis isian (dipilih paling awal) --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
            <h3 class="text-sm font-bold mb-1">Jenis isian</h3>
            <p class="text-xs text-on-surface-variant mb-3">Pilih bagaimana petugas akan menjawab pertanyaan ini di form entry.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach ($tipeTampil as $key)
                    @php $info = $tipeInfo[$key]; $label = $info['judul']; @endphp
                    <label class="cursor-pointer block">
                        <input type="radio" name="tipe_input" value="{{ $key }}" class="peer sr-only"
                               data-desc="{{ $info['desc'] }}" @checked($tipeAktif === $key) required>
                        <div class="h-full rounded-lg border border-outline-variant/50 p-3 transition-colors
                                    peer-checked:border-primary peer-checked:bg-primary/10 hover:bg-surface-container-low">
                            <div class="flex items-center gap-2 text-sm font-semibold">
                                <span class="material-symbols-outlined text-[18px]">{{ $info['icon'] }}</span>
                                {{ $label }}
                            </div>
                            <p class="text-[11px] text-on-surface-variant mt-1 leading-snug">{{ $info['desc'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 2. Data parameter --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold mb-1">Pertanyaan (yang dilihat petugas)</label>
                <input type="text" name="nama" value="{{ old('nama', $parameter->nama) }}" required
                       class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Kelompok (tab pada form)</label>
                <select name="kelompok" required class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-white">
                    @foreach (GcP::KELOMPOK as $key => $label)
                        <option value="{{ $key }}" @selected(old('kelompok', $parameter->kelompok) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Bobot</label>
                <input type="number" step="0.01" min="0" name="bobot" value="{{ old('bobot', $parameter->bobot) }}" required
                       class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Nomor urut</label>
                <input type="number" min="1" name="urutan" value="{{ old('urutan', $parameter->urutan) }}" required
                       class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold mb-1">Keterangan (opsional, hanya terlihat di master)</label>
                <textarea name="keterangan" rows="2"
                          class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">{{ old('keterangan', $parameter->keterangan) }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $isEdit ? $parameter->aktif : true)) class="rounded" />
                    Tampilkan isian ini di form entry Ground Check
                </label>
            </div>
        </div>

        {{-- 3. Pilihan jawaban: hanya untuk jenis Pilihan / Pilihan ganda (dikelola JS biasa, tanpa Alpine) --}}
        <div id="gc-opsi-panel" class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-bold">Pilihan Jawaban</h3>
                <button type="button" id="gc-tambah"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-xs font-medium">
                    <span class="material-symbols-outlined text-[16px]">add</span> Tambah Pilihan
                </button>
            </div>
            <p class="text-xs text-on-surface-variant mb-3">
                Pilihan tampil sesuai urutan di bawah. "Nilai" dipakai untuk skor; kosongkan jika belum ditentukan
                (akan ditandai <em>belum bernilai</em> di daftar master). Minimal 2 pilihan.
            </p>

            <div class="grid grid-cols-12 gap-2 text-[11px] font-semibold text-on-surface-variant uppercase mb-1">
                <span class="col-span-1 text-center">No</span>
                <span class="col-span-7">Pilihan jawaban</span>
                <span class="col-span-3">Nilai</span>
                <span class="col-span-1"></span>
            </div>

            <div id="gc-opsi-list" class="space-y-2"></div>
        </div>

        <div id="gc-opsi-info" class="hidden bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 text-xs text-on-surface-variant">
            <span id="gc-opsi-info-text"></span>
            Jenis ini tidak memakai daftar pilihan jawaban.
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('master.gc-kriteria.index') }}" class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</a>
            <button type="submit" class="px-5 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">Simpan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function boot() {
        var form = document.getElementById('gc-form');
        // Cegah inisialisasi ganda kalau script ini ikut termuat dua kali.
        if (!form || form.dataset.gcReady === '1') return;
        form.dataset.gcReady = '1';

        var init = {{ \Illuminate\Support\Js::from($init) }};
        var panel = document.getElementById('gc-opsi-panel');
        var info = document.getElementById('gc-opsi-info');
        var infoText = document.getElementById('gc-opsi-info-text');
        var list = document.getElementById('gc-opsi-list');
        var btnTambah = document.getElementById('gc-tambah');
        var radios = form.querySelectorAll('input[name="tipe_input"]');

        var inputCls = 'rounded-lg border border-outline-variant/50 px-3 py-2 text-sm';

        function el(tag, cls, attrs) {
            var e = document.createElement(tag);
            if (cls) e.className = cls;
            if (attrs) for (var k in attrs) e.setAttribute(k, attrs[k]);
            return e;
        }

        function buatBaris(data) {
            var row = el('div', 'grid grid-cols-12 gap-2 items-center');
            row.setAttribute('data-row', '1');

            var hid = el('input', '', { type: 'hidden' });
            hid.setAttribute('data-f', 'id');
            hid.value = data.id || '';

            var no = el('span', 'col-span-1 text-xs text-center text-on-surface-variant');
            no.setAttribute('data-f', 'no');

            var label = el('input', 'col-span-7 ' + inputCls, { type: 'text', placeholder: 'Contoh: Milik sendiri' });
            label.setAttribute('data-f', 'label');
            label.value = data.label || '';

            var nilai = el('input', 'col-span-3 ' + inputCls, { type: 'number', step: '0.01', min: '0', placeholder: 'Opsional' });
            nilai.setAttribute('data-f', 'nilai');
            nilai.value = data.nilai === null || data.nilai === undefined ? '' : data.nilai;

            var hapus = el('button',
                'col-span-1 w-8 h-8 justify-self-center rounded-full text-error hover:bg-error-container/50 disabled:opacity-30 flex items-center justify-center',
                { type: 'button', title: 'Hapus pilihan ini' });
            hapus.setAttribute('data-f', 'hapus');
            var ic = el('span', 'material-symbols-outlined text-[18px]');
            ic.textContent = 'delete';
            hapus.appendChild(ic);
            hapus.addEventListener('click', function () {
                if (list.children.length > 2) { list.removeChild(row); rapikan(); }
            });

            row.appendChild(hid); row.appendChild(no); row.appendChild(label);
            row.appendChild(nilai); row.appendChild(hapus);
            return row;
        }

        // Beri nomor & nama field ulang (opsi[i][...]) setiap ada perubahan baris.
        function rapikan() {
            var rows = list.children;
            for (var i = 0; i < rows.length; i++) {
                var r = rows[i];
                r.querySelector('[data-f="no"]').textContent = i + 1;
                r.querySelector('[data-f="id"]').name = 'opsi[' + i + '][id]';
                r.querySelector('[data-f="label"]').name = 'opsi[' + i + '][label]';
                r.querySelector('[data-f="nilai"]').name = 'opsi[' + i + '][nilai]';
                r.querySelector('[data-f="hapus"]').disabled = rows.length <= 2;
            }
        }

        function tipeSekarang() {
            for (var i = 0; i < radios.length; i++) if (radios[i].checked) return radios[i];
            return null;
        }

        // Tampilkan / sembunyikan panel pilihan sesuai jenis isian.
        function terapkanTipe() {
            var r = tipeSekarang();
            var tipe = r ? r.value : '';
            var butuh = tipe === 'pilihan' || tipe === 'pilihan_ganda';

            panel.classList.toggle('hidden', !butuh);
            info.classList.toggle('hidden', butuh);
            if (r) infoText.textContent = r.getAttribute('data-desc') || '';

            // Input yang disabled tidak ikut terkirim & tidak divalidasi browser.
            var inputs = list.querySelectorAll('input, button[data-f="hapus"]');
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].tagName === 'BUTTON') continue;
                inputs[i].disabled = !butuh;
                if (inputs[i].getAttribute('data-f') === 'label') inputs[i].required = butuh;
            }
            btnTambah.disabled = !butuh;
        }

        btnTambah.addEventListener('click', function () {
            list.appendChild(buatBaris({}));
            rapikan();
            terapkanTipe();
        });

        for (var i = 0; i < radios.length; i++) radios[i].addEventListener('change', terapkanTipe);

        // Isi awal
        list.innerHTML = '';
        init.rows.forEach(function (d) { list.appendChild(buatBaris(d)); });
        rapikan();
        terapkanTipe();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
</script>
@endpush
