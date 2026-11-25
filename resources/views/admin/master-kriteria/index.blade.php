@extends('layouts.admin')

@section('title', 'Master Kriteria PBI')
@section('page_title', 'Master Kriteria PBI')

@section('content')
<div class="space-y-5 w-full">

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Keterangan --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 flex flex-wrap items-start justify-between gap-3">
        <div class="max-w-3xl">
            <h2 class="text-sm font-bold text-on-surface">Pertanyaan Lengkapi Data untuk PBI APBD</h2>
            <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">
                Daftar pertanyaan form Lengkapi Data yang berlaku untuk PBI APBD.
                <strong>Tampil di verifikasi</strong> menentukan baris yang muncul di kartu
                "Isian Parameter &amp; Verifikasi Petugas Kelurahan" dan "Isian Masyarakat".
                <strong>Ikut skor</strong> menentukan apakah parameter skor PBI padanannya dihitung
                (Indeks Kumulatif = Indeks × Bobot × Indeks Terintegrasi).
                Data identitas selalu tampil dan tidak diatur di sini.
            </p>
            <p class="text-xs mt-2 {{ $skorAktif < $skorTotal ? 'text-on-warning-container' : 'text-on-surface-variant' }}">
                {{ $skorAktif }} dari {{ $skorTotal }} parameter skor ikut dihitung.
                @if ($skorAktif < $skorTotal) Skor bersifat parsial, jangan dipakai sebagai penentu kelas. @endif
            </p>
        </div>
        <form method="POST" action="{{ route('master.kriteria.hitung-ulang') }}">
            @csrf
            <button type="submit"
                data-confirm="Hitung ulang skor semua pengajuan yang masih berjalan (bukan yang sudah disetujui/ditolak)?"
                data-confirm-title="Hitung Ulang Skor"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary hover:bg-secondary text-on-primary text-xs font-semibold">
                <span class="material-symbols-outlined text-[16px]">calculate</span> Hitung Ulang Skor
            </button>
        </form>
    </div>

    {{-- Tab kelompok + pencarian --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $t)
                <a href="{{ route('master.kriteria.index', ['tab' => $key]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold border transition-colors
                          {{ $tab === $key ? 'bg-primary text-on-primary border-primary' : 'bg-white border-outline-variant/60 text-on-surface-variant hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-[16px]">{{ $t['icon'] }}</span> {{ $t['label'] }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('master.kriteria.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant">search</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari pertanyaan..."
                       class="w-60 pl-9 pr-3 py-2 rounded-lg border border-outline-variant/60 bg-white text-sm">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">Cari</button>
        </form>
    </div>

    {{-- Daftar pertanyaan --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-surface-container text-on-surface-variant uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3 text-left">Pertanyaan</th>
                        <th class="px-4 py-3 text-left">Parameter Skor PBI</th>
                        <th class="px-4 py-3 text-center">Tampil di Verifikasi</th>
                        <th class="px-4 py-3 text-center">Ikut Skor</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($parameters as $p)
                        @php
                            $pp = $p->pbiParameter;
                            $belumPeta = ($pp && $p->tipe_input === 'pilihan') ? $p->opsis->whereNull('pbi_opsi_id')->count() : 0;
                        @endphp
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-sm">{{ $p->nama }}</div>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    <span class="px-2 py-0.5 rounded bg-surface-container text-[10px] text-on-surface-variant">
                                        {{ \App\Models\GcKriteriaParameter::KELOMPOK[$p->kelompok] ?? $p->kelompok }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-brand-soft text-[10px] text-brand">
                                        {{ ['pbi' => 'Khusus PBI', 'pbi_gc' => 'PBI & Ground Check', 'gc' => 'Khusus Ground Check'][$p->target] ?? $p->target }}
                                    </span>
                                    {{-- @unless ($p->aktif)
                                        <span class="px-2 py-0.5 rounded bg-warning-container text-[10px] text-on-warning-container">
                                            Tidak tampil di form warga (aktifkan di Kriteria Ground Check)
                                        </span>
                                    @endunless --}}
                                </div>
                            </td>

                            {{-- Parameter Skor PBI --}}
                            <td class="px-4 py-3">
                                @if ($pp)
                                    <div class="font-medium">{{ $pp->nama }}</div>
                                    <div class="text-[11px] text-on-surface-variant">
                                        Indeks {{ number_format((float) $pp->indeks, 2, ',', '.') }} ·
                                        Bobot {{ number_format((float) $pp->bobot, 2, ',', '.') }}
                                    </div>
                                    @if ($belumPeta > 0)
                                        <div class="text-[11px] text-on-warning-container mt-0.5">{{ $belumPeta }} jawaban belum dipetakan (tidak dihitung)</div>
                                    @endif
                                @else
                                    <span class="text-on-surface-variant text-[11px]">Tidak dinilai (hanya ditampilkan)</span>
                                @endif
                            </td>

                            {{-- Tampil di Verifikasi --}}
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('master.kriteria.toggle', $p) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="kolom" value="tampil_verifikasi">
                                    <button type="submit" role="switch" aria-checked="{{ $p->tampil_verifikasi ? 'true' : 'false' }}"
                                        title="{{ $p->tampil_verifikasi ? 'Tampil. Klik untuk menyembunyikan' : 'Disembunyikan. Klik untuk menampilkan' }}"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $p->tampil_verifikasi ? 'bg-primary' : 'bg-outline-variant' }}">
                                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform {{ $p->tampil_verifikasi ? 'translate-x-5' : 'translate-x-0.5' }}"></span>
                                    </button>
                                </form>
                            </td>

                            {{-- Ikut Skor --}}
                            <td class="px-4 py-3 text-center">
                                @if ($pp)
                                    <form method="POST" action="{{ route('master.kriteria.toggle', $p) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="kolom" value="ikut_skor">
                                        <button type="submit" role="switch" aria-checked="{{ $pp->aktif ? 'true' : 'false' }}"
                                            title="{{ $pp->aktif ? 'Ikut skor. Klik untuk menonaktifkan' : 'Tidak ikut skor. Klik untuk mengaktifkan' }}"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $pp->aktif ? 'bg-primary' : 'bg-outline-variant' }}">
                                            <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform {{ $pp->aktif ? 'translate-x-5' : 'translate-x-0.5' }}"></span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" disabled role="switch" aria-checked="false"
                                        title="Tidak punya parameter skor PBI, jadi tidak bisa ikut skor"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full bg-outline-variant opacity-50 cursor-not-allowed">
                                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow translate-x-0.5"></span>
                                    </button>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @if ($pp && $p->tipe_input === 'pilihan')
                                        <a href="{{ route('master.kriteria.peta', $p) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">swap_horiz</span> Pemetaan
                                        </a>
                                    @else
                                        <span title="{{ $pp ? 'Pertanyaan ini bukan pilihan, tidak perlu dipetakan' : 'Tidak punya parameter skor PBI' }}"
                                              class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container text-on-surface-variant opacity-50 cursor-not-allowed text-[11px] font-medium">
                                            <span class="material-symbols-outlined text-[14px]">swap_horiz</span> Pemetaan
                                        </span>
                                    @endif

                                    @if ($pp)
                                        <a href="{{ route('master.kriteria.edit', $pp) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container text-on-surface-variant hover:bg-surface-container-high text-[11px] font-medium transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">edit</span> Skor
                                        </a>
                                    @else
                                        <span title="Tidak punya parameter skor PBI"
                                              class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container text-on-surface-variant opacity-50 cursor-not-allowed text-[11px] font-medium">
                                            <span class="material-symbols-outlined text-[14px]">edit</span> Skor
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">
                            {{ filled($q) ? 'Tidak ada pertanyaan yang cocok.' : 'Belum ada pertanyaan pada kelompok ini.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Parameter skor PBI --}}
    <details class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <summary class="px-5 py-4 cursor-pointer flex items-center justify-between gap-3 text-sm font-bold">
            <span>Parameter Skor PBI (bobot &amp; indeks terintegrasi)</span>
            <a href="{{ route('master.kriteria.create') }}"
               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium">
                <span class="material-symbols-outlined text-[14px]">add</span> Tambah Parameter
            </a>
        </summary>
        <div class="overflow-x-auto border-t border-outline-variant/30">
            <table class="w-full text-xs">
                <thead class="bg-surface-container text-on-surface-variant uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3 text-left">Parameter</th>
                        <th class="px-4 py-3 text-center">Indeks</th>
                        <th class="px-4 py-3 text-center">Bobot</th>
                        <th class="px-4 py-3 text-center">Opsi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @foreach ($skorParams as $s)
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-4 py-3 text-center">{{ $s->urutan }}</td>
                            <td class="px-4 py-3 font-medium">{{ $s->nama }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format((float) $s->indeks, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format((float) $s->bobot, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">{{ $s->opsis->count() }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-medium {{ $s->aktif ? 'bg-success-container text-on-success-container' : 'bg-surface-container-highest text-on-surface-variant' }}">
                                    {{ $s->aktif ? 'Ikut skor' : 'Tidak ikut skor' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('master.kriteria.edit', $s) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary text-[11px] font-medium">
                                    <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</div>
@endsection