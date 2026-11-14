@extends('layouts.masyarakat')

@section('title', 'Keluarga PBI APBD - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'PBI APBD')
@section('back', route('masyarakat.dashboard'))

@section('content')
    @unless (request()->attributes->get('is_mobile'))
        <a href="{{ route('masyarakat.dashboard') }}"
           class="inline-flex items-center gap-1 text-sm text-on-surface-variant mb-3 sm:mb-4 py-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali
        </a>
    @endunless

    <h1 class="text-xl sm:text-2xl font-extrabold text-brand mb-2">Daftar Keluarga</h1>
    <p class="mb-5 sm:mb-6 inline-flex max-w-full items-center gap-1.5 rounded-full bg-brand-soft ring-1 ring-brand/15 px-3.5 py-1.5 text-xs text-on-surface-variant">
        <span class="material-symbols-outlined text-[16px] text-brand shrink-0">confirmation_number</span>
        <span class="break-all">No. Registrasi: <strong class="text-on-surface">{{ $pbiApbd->no_registrasi }}</strong></span>
    </p>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-error-container text-on-error-container text-sm">
            <ul class="list-disc list-inside space-y-0.5 break-words">
                @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @unless ($bisaEdit)
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-success-container text-on-success-container text-sm flex items-start gap-2">
            <span class="material-symbols-outlined text-[18px] shrink-0 mt-px">lock</span>
            <p>Pengajuan sudah disetujui, sehingga pilihan peserta tidak dapat diubah lagi.</p>
        </div>
    @elseunless ($bisaPilih)
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-warning-container text-on-warning-container text-sm flex items-start gap-2">
            <span class="material-symbols-outlined text-[18px] shrink-0 mt-px">lock</span>
            <p>Pengajuan sedang diproses petugas ({{ $kepala['status_label'] }}), sehingga pilihan peserta tidak dapat diubah.</p>
        </div>
    @endunless

    {{-- Data kepala keluarga sudah terisi tetapi belum ada peserta: PBI APBD belum dikirim ke Kelurahan --}}
    @if ($kepala['sudah_mengisi'] && ! $kepala['diajukan_pbi'] && $bisaPilih)
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-warning-container text-on-warning-container text-sm flex items-start gap-2">
            <span class="material-symbols-outlined text-[18px] shrink-0 mt-px">info</span>
            <p>Data sudah masuk daftar Ground Check, tetapi <b>PBI APBD belum diajukan</b>. Centang minimal satu peserta lalu tekan <b>Simpan Pilihan Peserta</b> agar pengajuan diteruskan ke Kelurahan.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('masyarakat.pbi-apbd.keluarga.store', $pbiApbd) }}" class="space-y-4 pb-6">
        @csrf

        {{-- ===================== KARTU 1: KEPALA KELUARGA ===================== --}}
        <div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6">
            <h2 class="mb-3 flex items-center gap-3 text-base font-extrabold text-on-surface">
                <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">person</span>
                </span>
                Kepala Keluarga
            </h2>

            <div class="rounded-2xl border border-brand bg-brand-soft/60 p-3.5">
                <p class="text-sm font-bold text-on-surface break-words">{{ $kepala['nama'] }}</p>
                <p class="text-xs text-on-surface-variant tabular-nums break-all">{{ $kepala['nik'] }}</p>

                <div class="mt-1.5 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 rounded-full bg-surface-container text-[11px] font-medium text-on-surface-variant">{{ $kepala['hubungan'] }}</span>

                    @if (! $kepala['sudah_mengisi'])
                        <span class="px-2 py-0.5 rounded-full bg-warning-container text-on-warning-container text-[11px] font-semibold">
                            Data belum diisi
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-[11px] font-semibold">
                            Data terisi &middot; Masuk Ground Check
                        </span>

                        @if ($kepala['diajukan_pbi'])
                            <span class="px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-[11px] font-semibold">
                                PBI APBD diajukan &middot; {{ $kepala['status_label'] }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-warning-container text-on-warning-container text-[11px] font-semibold">
                                PBI APBD belum diajukan
                            </span>
                        @endif
                    @endif

                    @if ($kepala['peserta_pbi'])
                        <span class="px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-[11px] font-semibold">Peserta PBI APBD</span>
                    @endif

                    @if ($kepala['sudah_mengisi'] && $kepala['dikembalikan'])
                        <span class="px-2 py-0.5 rounded-full bg-error-container text-on-error-container text-[11px] font-semibold">
                            Perlu diperbaiki
                        </span>
                    @endif
                </div>
            </div>

            {{-- Kepala keluarga boleh tidak ikut sebagai peserta PBI APBD; datanya tetap diisi untuk Ground Check --}}
            @if ($kepala['ada_baris'])
                <input type="hidden" name="kepala_pbi_form" value="1">
                <label class="mt-3 flex items-start gap-3 rounded-2xl border border-outline-variant bg-white p-3.5 {{ $bisaPilih ? 'cursor-pointer' : 'cursor-not-allowed' }}">
                    <input type="checkbox" name="kepala_pbi" value="1"
                           class="mt-0.5 w-5 h-5 shrink-0 rounded accent-[#0a5ca8]"
                           @checked($kepala['peserta_pbi']) @disabled(! $bisaPilih)>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-on-surface">Kepala keluarga ikut didaftarkan sebagai peserta PBI APBD</span>
                        <span class="block text-xs text-on-surface-variant mt-0.5">
                            Biarkan kosong bila yang didaftarkan hanya anggota keluarga lain (anak, dll). Data kepala keluarga tetap diisi lengkap untuk Ground Check.
                        </span>
                    </span>
                </label>
            @endif

            @if ($kepala['sudah_mengisi'] && ! $kepala['bisa_isi'])
                <p class="mt-3 text-xs text-on-surface-variant">
                    Data kepala keluarga sudah dikirim dan tidak dapat diajukan lagi.
                </p>
            @elseif (! $kepala['sudah_mengisi'])
                <p class="mt-3 text-xs text-on-surface-variant">
                    Kepala keluarga wajib mengisi form lengkap terlebih dahulu untuk pendataan Ground Check.
                    Pengajuan PBI APBD baru dikirim ke Kelurahan setelah ada peserta yang dicentang.
                </p>
            @endif

            <div class="mt-3">
                @if ($kepala['bisa_isi'])
                    <a href="{{ route('masyarakat.pbi-apbd.lengkapi', $pbiApbd) }}"
                       class="w-full sm:w-auto sm:px-8 inline-flex items-center justify-center gap-2 py-3 rounded-full bg-primary shadow-md text-white font-semibold text-sm">
                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                        {{ $kepala['sudah_mengisi'] ? 'Perbaiki Data Kepala Keluarga' : 'Isi Data Kepala Keluarga' }}
                    </a>
                @else
                    <a href="{{ route('masyarakat.pbi-apbd.lengkapi', $pbiApbd) }}"
                       class="w-full sm:w-auto sm:px-8 inline-flex items-center justify-center gap-2 py-3 rounded-full border border-primary text-primary font-semibold text-sm">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                        Lihat Data Kepala Keluarga
                    </a>
                @endif
            </div>
        </div>

        {{-- ===================== KARTU 2: ANGGOTA KELUARGA ===================== --}}
        <div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6">
            <h2 class="mb-1.5 flex items-center gap-3 text-base font-extrabold text-on-surface">
                <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">family_restroom</span>
                </span>
                Anggota Keluarga
            </h2>
            <p class="text-xs text-on-surface-variant mb-4">
                <b>Centang</b> anggota yang didaftarkan sebagai peserta PBI APBD.
                Terpisah dari itu, tombol <b>Isi Data</b> dipakai mengisi data pribadi anggota (pendidikan, pekerjaan, kesehatan)
                untuk pendataan Ground Check. Anggota masuk daftar Ground Check setelah datanya disimpan, walau tidak didaftarkan ke PBI APBD.
                Belum ada anggota yang dicari? Tambahkan dulu di menu
                <a href="{{ route('masyarakat.keluarga') }}" class="font-semibold text-brand underline">Anggota Keluarga</a>.
            </p>

            @if ($baris->isEmpty())
                <p class="text-sm text-on-surface-variant">Belum ada anggota keluarga selain kepala keluarga.</p>
            @else
                @if ($bisaPilih)
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-xs text-on-surface-variant"><b id="jml-dipilih">0</b> dari {{ $baris->count() }} anggota dipilih sebagai peserta PBI APBD</span>
                        <button type="button" id="btn-pilih-semua"
                                class="px-3.5 py-2 rounded-full border border-brand text-brand text-xs font-semibold">
                            Pilih semua
                        </button>
                    </div>
                @endif

                <div class="space-y-2">
                    @foreach ($baris as $r)
                        <div data-row class="rounded-2xl border p-3.5 {{ $r['peserta_pbi'] ? 'border-brand bg-brand-soft/60' : 'border-outline-variant bg-white' }}">
                            <label class="flex items-start gap-3 {{ $bisaPilih ? 'cursor-pointer' : 'cursor-not-allowed' }}">
                                <input type="checkbox" name="anggota[]" value="{{ $r['id'] }}" data-anggota
                                       class="mt-1 w-5 h-5 shrink-0 rounded accent-[#0a5ca8]"
                                       @checked($r['peserta_pbi']) @disabled(! $bisaPilih)>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-on-surface break-words">{{ $r['nama'] }}</p>
                                    <p class="text-xs text-on-surface-variant tabular-nums break-all">{{ $r['nik'] }}</p>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        <span class="px-2 py-0.5 rounded-full bg-surface-container text-[11px] font-medium text-on-surface-variant">{{ $r['hubungan'] }}</span>
                                        @if ($r['peserta_pbi'])
                                            <span class="px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-[11px] font-semibold">Peserta PBI APBD</span>
                                        @endif
                                        @if ($r['terkunci'])
                                            <span class="px-2 py-0.5 rounded-full bg-warning-container text-on-warning-container text-[11px] font-semibold">Sudah dicek petugas</span>
                                        @elseif ($r['masuk_gc'])
                                            <span class="px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-[11px] font-semibold">Masuk Ground Check</span>
                                        @elseif ($r['terisi'])
                                            <span class="px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-[11px] font-semibold">Data terisi</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-warning-container text-on-warning-container text-[11px] font-semibold">Data belum diisi</span>
                                        @endif
                                    </div>
                                </div>
                            </label>

                            {{-- Menyimpan pilihan peserta (centang di atas) lalu membuka form data anggota --}}
                            <div class="mt-3 pl-8">
                                @if ($bisaEdit && ! $r['terkunci'])
                                    <button type="submit" name="isi" value="{{ $r['id'] }}"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-primary text-white text-xs font-semibold">
                                        <span class="material-symbols-outlined text-[16px]">edit_note</span>
                                        {{ $r['terisi'] ? 'Ubah Data' : 'Isi Data' }}
                                    </button>
                                @elseif ($r['masuk_gc'] || $r['terisi'])
                                    <a href="{{ $r['isi_url'] }}"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full border border-primary text-primary text-xs font-semibold">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span> Lihat Data
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($bisaPilih)
            <div class="pt-2 flex flex-col sm:flex-row gap-2">
                @if ($kepala['bisa_isi'])
                    <button type="submit" name="lanjut" value="1"
                            class="w-full sm:w-auto sm:px-8 flex items-center justify-center gap-2 py-3.5 rounded-full bg-primary shadow-md text-white font-semibold text-sm">
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        Simpan &amp; Lanjut Isi Data Kepala Keluarga
                    </button>
                @endif
                <button type="submit"
                        class="w-full sm:w-auto sm:px-8 flex items-center justify-center gap-2 py-3.5 rounded-full {{ $kepala['bisa_isi'] ? 'border border-primary text-primary' : 'bg-primary shadow-md text-white' }} font-semibold text-sm">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Simpan Pilihan Peserta
                </button>
            </div>
        @endif
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var semua = Array.prototype.slice.call(document.querySelectorAll('[data-anggota]'));
    var hitung = document.getElementById('jml-dipilih');
    var btn = document.getElementById('btn-pilih-semua');

    function aktif() { return semua.filter(function (c) { return !c.disabled; }); }

    function segarkan() {
        if (hitung) { hitung.textContent = semua.filter(function (c) { return c.checked; }).length; }
        if (btn) {
            var a = aktif();
            var penuh = a.length > 0 && a.every(function (c) { return c.checked; });
            btn.textContent = penuh ? 'Batal pilih semua' : 'Pilih semua';
        }
        semua.forEach(function (c) {
            var l = c.closest('[data-row]');
            if (!l) { return; }
            l.classList.toggle('border-brand', c.checked);
            l.classList.toggle('bg-brand-soft/60', c.checked);
            l.classList.toggle('border-outline-variant', !c.checked);
            l.classList.toggle('bg-white', !c.checked);
        });
    }

    semua.forEach(function (c) { c.addEventListener('change', segarkan); });

    if (btn) {
        btn.addEventListener('click', function () {
            var a = aktif();
            var penuh = a.length > 0 && a.every(function (c) { return c.checked; });
            a.forEach(function (c) { c.checked = !penuh; });
            segarkan();
        });
    }

    segarkan();
});
</script>
@endpush