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
            <p>Pengajuan sudah disetujui, sehingga pilihan anggota tidak dapat diubah lagi.</p>
        </div>
    @endunless

    <form method="POST" action="{{ route('masyarakat.pbi-apbd.keluarga.store', $pbiApbd) }}" class="space-y-4 pb-6">
        @csrf

        <div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6">
            <h2 class="mb-1.5 flex items-center gap-3 text-base font-extrabold text-on-surface">
                <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">family_restroom</span>
                </span>
                Pilih anggota yang didaftarkan
            </h2>
            <p class="text-xs text-on-surface-variant mb-4">
                Centang anggota keluarga yang akan didaftarkan untuk pendataan Ground Check. Kepala keluarga otomatis ikut.
                Anggota keluarga lain (istri/anak, dst.) akan didata petugas di lapangan.
                Belum ada anggota yang dicari? Tambahkan dulu di menu
                <a href="{{ route('masyarakat.keluarga') }}" class="font-semibold text-brand underline">Anggota Keluarga</a>.
            </p>

            @if ($baris->isEmpty())
                <p class="text-sm text-on-surface-variant">Belum ada anggota keluarga.</p>
            @else
                @if ($bisaEdit)
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-xs text-on-surface-variant"><b id="jml-dipilih">0</b> dari {{ $baris->count() }} dipilih</span>
                        <button type="button" id="btn-pilih-semua"
                                class="px-3.5 py-2 rounded-full border border-brand text-brand text-xs font-semibold">
                            Pilih semua
                        </button>
                    </div>
                @endif

                <div class="space-y-2">
                    @foreach ($baris as $r)
                        @php $mati = $r['kepala'] || $r['terkunci'] || ! $bisaEdit; @endphp
                        <label class="flex items-start gap-3 rounded-2xl border p-3.5 {{ $r['terdaftar'] ? 'border-brand bg-brand-soft/60' : 'border-outline-variant bg-white' }} {{ $mati ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                            <input type="checkbox" name="anggota[]" value="{{ $r['id'] }}" data-anggota
                                   class="mt-1 w-5 h-5 shrink-0 rounded accent-[#0a5ca8]"
                                   @checked($r['terdaftar']) @disabled($mati)>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-on-surface break-words">{{ $r['nama'] }}</p>
                                <p class="text-xs text-on-surface-variant tabular-nums break-all">{{ $r['nik'] }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full bg-surface-container text-[11px] font-medium text-on-surface-variant">{{ $r['hubungan'] }}</span>
                                    @if ($r['kepala'])
                                        <span class="px-2 py-0.5 rounded-full bg-brand text-white text-[11px] font-semibold">Wajib · mengisi form lengkap</span>
                                    @elseif ($r['terkunci'])
                                        <span class="px-2 py-0.5 rounded-full bg-warning-container text-on-warning-container text-[11px] font-semibold">Sudah diproses petugas</span>
                                    @elseif ($r['terdaftar'])
                                        <span class="px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-[11px] font-semibold">Terdaftar</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="pt-2 flex flex-col sm:flex-row gap-2">
            @if ($bisaEdit)
                <button type="submit" name="lanjut" value="1"
                        class="w-full sm:w-auto sm:px-8 flex items-center justify-center gap-2 py-3.5 rounded-full bg-primary shadow-md text-white font-semibold text-sm">
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    Simpan &amp; Lanjut Isi Data
                </button>
                <button type="submit"
                        class="w-full sm:w-auto sm:px-8 flex items-center justify-center gap-2 py-3.5 rounded-full border border-primary text-primary font-semibold text-sm">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Simpan Pilihan
                </button>
            @else
                <a href="{{ route('masyarakat.pbi-apbd.lengkapi', $pbiApbd) }}"
                   class="w-full sm:w-auto sm:px-8 flex items-center justify-center gap-2 py-3.5 rounded-full bg-primary shadow-md text-white font-semibold text-sm">
                    Lihat Data Pengajuan
                </a>
            @endif
        </div>
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
            var l = c.closest('label');
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
