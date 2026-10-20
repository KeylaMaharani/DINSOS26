@extends('layouts.admin')

@section('title', 'Data Keluarga Ground Check')
@section('page_title', 'Ground Check 2026 — Data Keluarga')

@php
    use App\Models\GcVerifikasi;

    $badge = function ($v) {
        if (! $v) {
            return ['Belum diisi', 'bg-surface-container-highest text-on-surface-variant'];
        }
        $label = GcVerifikasi::STATUS_TEMUAN[$v->status_temuan] ?? $v->status_temuan;
        if ($v->status_temuan === 'ditemukan') {
            return $v->status_entry === 'submit'
                ? [$label . ' · Selesai', 'bg-success-container text-on-success-container']
                : [$label . ' · Draf', 'bg-warning-container text-on-warning-container'];
        }

        return [$label, 'bg-surface-container-highest text-on-surface-variant'];
    };

    $statusUrl = route('ground-check.status', ['sasaran' => '__ID__']);
@endphp

@section('content')
<div class="space-y-5 max-w-6xl"
     x-data="{
        open: false,
        row: { id: null, nik: '', nama: '', alamat: '', status: '' },
        url: '',
        buka(r, status) {
            this.row = r;
            this.row.status = status || '';
            this.url = '{{ $statusUrl }}'.replace('__ID__', r.id);
            this.open = true;
        }
     }">

    <a href="{{ route('ground-check.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
    </a>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-xl bg-error-container text-on-error-container text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-outline-variant/30 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">family_restroom</span>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-on-surface">
                        Data Keluarga &middot; No KK <span class="text-brand tabular-nums">{{ $noKk }}</span>
                    </h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">
                        Klik <span class="font-semibold">Verifikasi</span> pada anggota keluarga untuk menentukan status, lalu isi form jika ditemukan.
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full bg-surface-container text-xs font-semibold text-on-surface-variant">
                {{ $anggota->count() }} anggota
            </span>
        </div>

        {{-- Tabel tanpa scroll horizontal --}}
        <table class="w-full text-xs">
            <thead class="bg-surface-container text-on-surface-variant uppercase text-[10.5px] tracking-wider">
                <tr>
                    <th class="pl-5 pr-2 py-3 text-center w-10">No</th>
                    <th class="px-3 py-3 text-left">Nama / NIK</th>
                    <th class="px-3 py-3 text-left">Tgl Lahir</th>
                    <th class="px-3 py-3 text-left">Alamat</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="pl-3 pr-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
                @foreach ($anggota as $a)
                    @php
                        $v = $a->verifikasis->first();
                        [$label, $kelas] = $badge($v);
                        $inisial = mb_strtoupper(mb_substr($a->nama ?? '?', 0, 1));
                        $alamatLengkap = collect([
                            $a->alamat,
                            $a->rt ? 'RT ' . $a->rt : null,
                            $a->rw ? 'RW ' . $a->rw : null,
                            $a->kelurahan ? 'Kel. ' . $a->kelurahan : null,
                            $a->kecamatan ? 'Kec. ' . $a->kecamatan : null,
                        ])->filter()->implode(' ');
                    @endphp
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="pl-5 pr-2 py-3 text-center text-on-surface-variant">{{ $loop->iteration }}</td>

                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2.5">
                                <span class="shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary text-[12px] font-bold flex items-center justify-center">{{ $inisial }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-on-surface leading-tight">{{ $a->nama }}</p>
                                    <p class="text-[11px] text-on-surface-variant mt-0.5 tabular-nums whitespace-nowrap">{{ $a->nik }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-3 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px] text-on-surface-variant">cake</span>
                                {{ optional($a->tanggal_lahir)->format('d-m-Y') ?? '—' }}
                            </span>
                        </td>

                        <td class="px-3 py-3 leading-snug">{{ $alamatLengkap ?: '—' }}</td>

                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium whitespace-nowrap {{ $kelas }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $label }}
                            </span>
                        </td>

                        <td class="pl-3 pr-5 py-3">
                            <div class="flex items-center justify-center">
                                @php $keForm = $v && $v->status_temuan === 'ditemukan'; @endphp
                                <div class="relative group">
                                    @if ($keForm)
                                        {{-- Sudah diverifikasi "Ditemukan": langsung ke form isian, tanpa pop up --}}
                                        <a href="{{ route('ground-check.form', $a) }}" aria-label="Buka form isian"
                                           class="inline-flex w-9 h-9 items-center justify-center rounded-lg bg-success text-white shadow-sm hover:opacity-90 transition-opacity">
                                            <span class="material-symbols-outlined text-[19px]">edit_note</span>
                                        </a>
                                    @else
                                        <button type="button" aria-label="{{ $v ? 'Ubah verifikasi' : 'Verifikasi' }}"
                                            @click="buka({ id: {{ $a->id }}, nik: @js($a->nik), nama: @js($a->nama), alamat: @js($alamatLengkap) }, @js($v->status_temuan ?? ''))"
                                            class="inline-flex w-9 h-9 items-center justify-center rounded-lg bg-primary text-on-primary shadow-sm hover:bg-secondary transition-colors">
                                            <span class="material-symbols-outlined text-[19px]">fact_check</span>
                                        </button>
                                    @endif
                                    {{-- Keterangan (tooltip) --}}
                                    <span class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 px-2 py-1 rounded-md bg-on-surface text-surface-container-lowest text-[10.5px] font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity z-10">
                                        {{ $keForm ? 'Buka form isian' : ($v ? 'Ubah verifikasi' : 'Verifikasi') }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Modal: Form Verifikasi Data --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div @click.outside="open = false" @keydown.escape.window="open = false"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-lg overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-base font-semibold text-on-surface">Form Verifikasi Data</h3>
                <button type="button" @click="open = false" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" :action="url" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1">NIK</label>
                    <input type="text" x-model="row.nik" readonly class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-surface-container-low" />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Nama</label>
                    <input type="text" x-model="row.nama" readonly class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-surface-container-low" />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Alamat</label>
                    <textarea x-model="row.alamat" readonly rows="2" class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-surface-container-low"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Status</label>
                    <select name="status_temuan" x-model="row.status" required class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">-Pilih-</option>
                        @foreach (GcVerifikasi::STATUS_TEMUAN as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-on-surface-variant mt-1">
                        Jika <strong>Ditemukan</strong>, kamu akan diarahkan ke form isian. Status lain langsung dianggap selesai.
                    </p>
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50 hover:bg-surface-container transition-colors">Close</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
