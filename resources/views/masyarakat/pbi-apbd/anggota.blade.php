{{-- Taruh di resources/views/masyarakat/pbi-apbd/anggota.blade.php --}}
@extends('layouts.masyarakat')

@php use App\Models\GcKriteriaParameter as GcP; @endphp

@section('title', 'Data Anggota Keluarga - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Data Anggota')
@section('back', route('masyarakat.pbi-apbd.keluarga', $pbiApbd))

@section('content')
    @unless (request()->attributes->get('is_mobile'))
        <a href="{{ route('masyarakat.pbi-apbd.keluarga', $pbiApbd) }}"
           class="inline-flex items-center gap-1 text-sm text-on-surface-variant mb-3 sm:mb-4 py-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali ke Daftar Keluarga
        </a>
    @endunless

    <h1 class="text-xl sm:text-2xl font-extrabold text-brand mb-2">Data Anggota Keluarga</h1>
    <p class="mb-5 sm:mb-6 inline-flex max-w-full items-center gap-1.5 rounded-full bg-brand-soft ring-1 ring-brand/15 px-3.5 py-1.5 text-xs text-on-surface-variant">
        <span class="material-symbols-outlined text-[16px] text-brand shrink-0">person</span>
        <span class="break-all"><strong class="text-on-surface">{{ $a->nama }}</strong> &middot; NIK {{ $a->nik }}</span>
    </p>

    @if ($terkunci)
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-warning-container text-on-warning-container text-sm flex items-start gap-2">
            <span class="material-symbols-outlined text-[18px] shrink-0 mt-px">lock</span>
            <p>Data anggota ini sudah diproses petugas Ground Check sehingga tidak dapat diubah lagi.</p>
        </div>
    @elseif (! $bisaEdit)
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-success-container text-on-success-container text-sm flex items-start gap-2">
            <span class="material-symbols-outlined text-[18px] shrink-0 mt-px">lock</span>
            <p>Pengajuan sudah disetujui, sehingga data tidak dapat diubah lagi.</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 px-4 py-3.5 rounded-2xl bg-error-container text-on-error-container text-sm">
            <p class="font-semibold mb-1">Periksa kembali data Anda:</p>
            <ul class="list-disc list-inside space-y-0.5 break-words">
                @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('masyarakat.pbi-apbd.anggota.store', [$pbiApbd, $a->id]) }}"
          class="space-y-4 sm:space-y-6 pb-6" id="form-lengkapi">
        @csrf

        @if ($parameters->isEmpty())
            <div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6">
                <p class="text-sm text-on-surface-variant">Pertanyaan belum tersedia.</p>
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
                    <p class="text-xs text-on-surface-variant mb-4">Jawab sesuai keadaan sebenarnya. Petugas akan mengecek langsung ke lapangan. Isian bertanda <span class="text-error">*</span> wajib diisi.</p>
                    @php $pertama = false; @endphp
                @else
                    <div class="mb-4"></div>
                @endif

                <div class="grid grid-cols-1 gap-5 max-w-xl">
                    @foreach ($isi as $p)
                        @include('masyarakat.pbi-apbd._gc_field', ['p' => $p, 'nomor' => ++$no])
                    @endforeach
                </div>
            </div>
        @endforeach

        @if ($bisaEdit && $parameters->isNotEmpty())
            <div class="pt-2">
                <button type="submit" id="btn-submit"
                        class="w-full sm:w-auto sm:px-8 flex items-center justify-center gap-2 py-3.5 rounded-full bg-primary shadow-md text-white font-semibold text-sm">
                    <span class="material-symbols-outlined text-[18px]">save</span> Simpan Data Anggota
                </button>
            </div>
        @endif
    </form>
@endsection

@push('scripts')
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

{{-- Ketenagakerjaan: "Apakah responden bekerja ...?" Tidak -> field lain dinonaktifkan & dikosongkan --}}
<script>
window.gcKerja = @json($kerja);
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
                    var tujuan = el.value === '' ? ((window.gcIsiAwal || {})[id] || '') : '';
                    if (tujuan && el.value !== tujuan) { pulih.push({ el: el, nilai: tujuan }); }
                }
                var baris = el.closest('.min-w-0');
                if (baris) { baris.classList.toggle('opacity-50', !ya); }
            });
        });

        // Lapangan usaha dulu, baru pekerjaan
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
@endpush