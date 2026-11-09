@extends('layouts.masyarakat')

@section('title', 'Dokumen - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Dokumen')
@section('container', 'max-w-3xl')

@php
    $card = 'rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30';
    $inputFile = 'block w-full min-w-0 text-xs text-on-surface-variant file:mr-3 file:rounded-full file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-primary';
@endphp

@section('content')
    <div class="space-y-4 sm:space-y-5">

        <div>
            <h1 class="text-xl font-extrabold text-brand">Dokumen Saya</h1>
            <p class="mt-1 text-xs text-on-surface-variant">
                Unggah dokumen sekali saja, lalu dipakai otomatis di pengajuan PBI APBD dan DTSEN tanpa perlu mengunggah ulang.
                Setiap jenis menyimpan maksimal {{ $maksVersi }} versi terbaru (dengan tanggal unggah berbeda).
                Format JPG, PNG, atau PDF, maksimal {{ $maksMb }} MB.
            </p>
        </div>

        @if ($errors->any())
            <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
                <ul class="list-disc list-inside space-y-0.5 break-words">
                    @foreach (array_unique($errors->all()) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @foreach ($jenisList as $jenis => [$label, $keterangan, $ikon])
            @php
                $items = $dokumens->get($jenis, collect());
                $t = $target[$jenis] ?? [];
                // Tombol "Pakai di pengajuan" muncul bila ada pengajuan tujuan yang masih bisa diubah
                $bisaDipakai = (! empty($t['pbi']) && $bisaPakai['pbi']) || (! empty($t['dtsen']) && $bisaPakai['dtsen']);
            @endphp

            <section class="{{ $card }} p-4 sm:p-5">
                <div class="flex items-start gap-3 mb-3">
                    <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">{{ $ikon }}</span>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm font-extrabold text-on-surface">{{ $label }}</h2>
                        <p class="text-xs text-on-surface-variant">{{ $keterangan }}</p>
                    </div>
                </div>

                {{-- Unggah (versi baru; bila sudah {{ $maksVersi }} versi, yang paling lama otomatis diganti) --}}
                <form method="POST" action="{{ route('masyarakat.dokumen.store') }}" enctype="multipart/form-data"
                      class="flex flex-col sm:flex-row sm:items-center gap-2">
                    @csrf
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                    <input type="file" name="berkas" accept=".jpg,.jpeg,.png,.pdf" required class="{{ $inputFile }}"
                           data-max-mb="{{ $maksMb }}">
                    <button type="submit"
                            class="shrink-0 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-full bg-primary text-white text-xs font-semibold">
                        <span class="material-symbols-outlined text-[16px]">upload</span>
                        {{ $items->count() >= $maksVersi ? 'Unggah & ganti yang terlama' : 'Unggah' }}
                    </button>
                </form>
                @if (old('jenis') === $jenis)
                    @error('berkas')<p class="mt-1 text-error text-xs">{{ $message }}</p>@enderror
                @endif

                {{-- Daftar versi (terbaru di atas) --}}
                <div class="mt-4 space-y-2.5">
                    @forelse ($items as $i => $d)
                        <div class="flex items-center gap-3 rounded-2xl border border-outline-variant/40 p-2.5">
                            <div class="w-16 h-16 shrink-0 rounded-xl border border-outline-variant/40 bg-surface-container overflow-hidden flex items-center justify-center">
                                @if ($d->isGambar())
                                    <img src="{{ $d->url() }}" alt="{{ $label }}" class="w-full h-full object-cover">
                                @elseif ($d->isPdf())
                                    <span class="material-symbols-outlined text-[28px] text-error/70">picture_as_pdf</span>
                                @else
                                    <span class="material-symbols-outlined text-[26px] text-on-surface-variant/50">description</span>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-on-surface truncate" title="{{ $d->nama_asli }}">{{ $d->nama_asli ?: basename($d->path) }}</p>
                                <p class="text-[11px] text-on-surface-variant">
                                    Diunggah {{ $d->created_at->format('d M Y, H:i') }}
                                    @if ($i === 0)
                                        <span class="ml-1 inline-flex px-1.5 py-0.5 rounded-full bg-success-container text-on-success-container text-[10px] font-bold">Terbaru</span>
                                    @endif
                                </p>

                                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <a href="{{ $d->url() }}" target="_blank" rel="noopener" class="text-xs font-semibold text-primary underline">Lihat</a>
                                    <a href="{{ $d->url() }}" download="{{ $d->nama_asli ?: basename($d->path) }}" class="text-xs font-semibold text-primary underline">Unduh</a>

                                    @if ($bisaDipakai)
                                        <form method="POST" action="{{ route('masyarakat.dokumen.pakai', $d->id) }}"
                                              onsubmit="return confirm('Pasang dokumen ini di pengajuan Anda? Berkas yang sekarang terpasang akan diganti.');">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-secondary underline">Pakai di pengajuan</button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('masyarakat.dokumen.destroy', $d->id) }}"
                                          onsubmit="return confirm('Hapus dokumen ini? Berkas yang sudah melekat di pengajuan tidak ikut terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-error underline">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-on-surface-variant italic">Belum ada dokumen.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
@endsection

@push('scripts')
<script>
    // Peringatan ukuran sebelum dikirim (server tetap memeriksa ulang)
    document.querySelectorAll('input[type="file"][data-max-mb]').forEach(function (input) {
        input.addEventListener('change', function () {
            var f = input.files && input.files[0];
            var max = parseFloat(input.dataset.maxMb) * 1024 * 1024;
            if (f && f.size > max) {
                alert('Ukuran berkas melebihi ' + input.dataset.maxMb + ' MB. Pilih berkas yang lebih kecil.');
                input.value = '';
            }
        });
    });
</script>
@endpush