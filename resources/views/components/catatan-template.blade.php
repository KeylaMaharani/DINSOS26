{{--
    Pilihan template catatan. Klik kalimat -> masuk ke kolom catatan (id = $target), masih bisa diedit.
    Pemakaian:  <x-catatan-template tahap="kabid" :aksi="['next','back','reject']" />
    Textarea catatan harus punya id="catatan" (atau ganti lewat target="...").
    Daftar kalimat ada di config/catatan.php.
--}}
@props(['tahap', 'target' => 'catatan', 'aksi' => ['next', 'back', 'reject']])

@php
    $semua = config("catatan.{$tahap}", []);
    $judul = ['next' => 'Meneruskan / verifikasi', 'back' => 'Mengembalikan', 'reject' => 'Menolak'];
    $warna = [
        'next' => 'border-primary/40 text-primary hover:bg-primary hover:text-on-primary',
        'back' => 'border-outline-variant text-on-surface hover:bg-surface-container-highest',
        'reject' => 'border-error/50 text-error hover:bg-error hover:text-on-error',
    ];
@endphp

<div class="mb-3 space-y-2" x-data="{ pakai(t) { var el = document.getElementById('{{ $target }}'); if (!el) return; el.value = t; el.dispatchEvent(new Event('input', { bubbles: true })); el.focus(); } }">
    <p class="text-xs text-on-surface-variant">Pilih template catatan (bisa diedit setelah dipilih):</p>
    @foreach ($aksi as $a)
        @continue(empty($semua[$a]))
        <div>
            <p class="text-[11px] font-semibold text-on-surface-variant mb-1">{{ $judul[$a] }}</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach ($semua[$a] as $t)
                    <button type="button" data-t="{{ $t }}" @click="pakai($el.dataset.t)"
                            class="px-3 py-1.5 rounded-full border text-xs text-left transition-colors {{ $warna[$a] }}">{{ $t }}</button>
                @endforeach
            </div>
        </div>
    @endforeach
</div>