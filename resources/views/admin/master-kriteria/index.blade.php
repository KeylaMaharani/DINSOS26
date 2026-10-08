@extends('layouts.admin')

@section('title', 'Master Kriteria Kemiskinan')
@section('page_title', 'Master Kriteria Kemiskinan')

@section('content')
<div class="space-y-5 max-w-6xl">

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 flex flex-wrap items-start justify-between gap-3">
        <div class="max-w-3xl">
            <h2 class="text-sm font-bold text-on-surface">Parameter & Opsi Jawaban PBI APBD</h2>
            <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">
                Dasar: SK Wali Kota Bogor No. 460/Kep.196-Dinsos/2021. Parameter aktif menjadi pertanyaan (dropdown) di halaman masyarakat.
                Rumus: <strong>Indeks Kumulatif = Indeks × Bobot × Indeks Terintegrasi</strong> (dijumlahkan semua parameter).
                Perubahan hanya berlaku saat masyarakat mengisi/menyimpan ulang; jawaban yang sudah tersimpan tidak berubah.
            </p>
        </div>
        <a href="{{ route('master.kriteria.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-semibold">
            <span class="material-symbols-outlined text-[16px]">add</span> Tambah Parameter
        </a>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-surface-container text-on-surface-variant uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center w-14">No</th>
                        <th class="px-4 py-3 text-left">Parameter</th>
                        <th class="px-4 py-3 text-center">Indeks</th>
                        <th class="px-4 py-3 text-center">Bobot</th>
                        <th class="px-4 py-3 text-center">Opsi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($parameters as $p)
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-4 py-3 text-center">{{ $p->urutan }}</td>
                            <td class="px-4 py-3 font-medium">
                                {{ $p->nama }}
                                <div class="text-[11px] text-on-surface-variant font-normal mt-0.5">
                                    {{ $p->opsis->pluck('label')->take(3)->implode(' · ') }}{{ $p->opsis->count() > 3 ? ' …' : '' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">{{ number_format((float) $p->indeks, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format((float) $p->bobot, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">{{ $p->opsis->count() }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-medium {{ $p->aktif ? 'bg-success-container text-on-success-container' : 'bg-surface-container-highest text-on-surface-variant' }}">
                                    {{ $p->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('master.kriteria.edit', $p) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium transition-colors">
                                        <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                                    </a>
                                    <form method="POST" action="{{ route('master.kriteria.destroy', $p) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" data-confirm="Hapus parameter &quot;{{ $p->nama }}&quot; beserta opsinya?" data-confirm-variant="error"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-[11px] font-medium transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">delete</span> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-on-surface-variant">
                            Belum ada parameter. Jalankan <code>php artisan db:seed --class=PbiKriteriaSeeder</code> atau tambah manual.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
