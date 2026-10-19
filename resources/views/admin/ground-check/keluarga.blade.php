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

    <a href="{{ route('ground-check.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
    </a>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-outline-variant/30">
            <h2 class="text-sm font-bold text-on-surface">Data Keluarga dengan No KK <span class="text-brand">{{ $noKk }}</span></h2>
            <p class="text-xs text-on-surface-variant mt-0.5">
                Klik ikon pensil pada anggota keluarga untuk memverifikasi status, lalu isi form jika ditemukan.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-surface-container text-on-surface-variant uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center w-24">Act</th>
                        <th class="px-4 py-3 text-left">NIK</th>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Tgl Lahir</th>
                        <th class="px-4 py-3 text-left">Alamat</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @foreach ($anggota as $a)
                        @php
                            $v = $a->verifikasis->first();
                            [$label, $kelas] = $badge($v);
                            $alamatLengkap = collect([
                                $a->alamat,
                                $a->rt ? 'RT ' . $a->rt : null,
                                $a->rw ? 'RW ' . $a->rw : null,
                                $a->kelurahan ? 'Kel. ' . $a->kelurahan : null,
                                $a->kecamatan ? 'Kec. ' . $a->kecamatan : null,
                            ])->filter()->implode(' ');
                        @endphp
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-4 py-2.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" title="Verifikasi status"
                                        @click="buka({ id: {{ $a->id }}, nik: @js($a->nik), nama: @js($a->nama), alamat: @js($alamatLengkap) }, @js($v->status_temuan ?? ''))"
                                        class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-success text-white hover:opacity-90 transition-opacity">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                    @if ($v && $v->status_temuan === 'ditemukan')
                                        <a href="{{ route('ground-check.form', $a) }}" title="Buka form isian"
                                           class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">edit_note</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2.5 whitespace-nowrap">{{ $a->nik }}</td>
                            <td class="px-4 py-2.5 font-medium whitespace-nowrap">{{ $a->nama }}</td>
                            <td class="px-4 py-2.5 whitespace-nowrap">{{ optional($a->tanggal_lahir)->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-4 py-2.5">{{ $alamatLengkap ?: '—' }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-medium whitespace-nowrap {{ $kelas }}">{{ $label }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal: Form Verifikasi Data --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div @click.outside="open = false" @keydown.escape.window="open = false"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-surface-container-lowest w-full max-w-md rounded-xl shadow-lg overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-base font-semibold text-on-surface">Form Verifikasi Data</h3>
                <button type="button" @click="open = false" class="text-on-surface-variant">
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
                    <select name="status_temuan" x-model="row.status" required class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm bg-white">
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
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Close</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
