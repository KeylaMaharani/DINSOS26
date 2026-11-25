@extends('layouts.admin')

@section('title', 'Pemetaan Jawaban ke Skor PBI')
@section('page_title', 'Master Kriteria PBI — Pemetaan Jawaban')

@section('content')
<div class="max-w-4xl space-y-5">
    <a href="{{ route('master.kriteria.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
    </a>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach (array_unique($errors->all()) as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 space-y-1">
        <p class="text-xs text-on-surface-variant">Pertanyaan di form warga</p>
        <h2 class="text-sm font-bold">{{ $p->nama }}</h2>
        <p class="text-xs text-on-surface-variant pt-2">Parameter skor PBI</p>
        <h3 class="text-sm font-bold">{{ $p->pbiParameter->nama }}
            <span class="font-normal text-on-surface-variant text-xs">
                (Indeks {{ number_format((float) $p->pbiParameter->indeks, 2, ',', '.') }} ·
                Bobot {{ number_format((float) $p->pbiParameter->bobot, 2, ',', '.') }})
            </span>
        </h3>
        <p class="text-xs text-on-surface-variant pt-2 leading-relaxed">
            Tentukan tiap jawaban warga dinilai sebagai pilihan PBI yang mana. Jawaban yang dibiarkan
            "Tidak dihitung" tidak menambah skor.
        </p>
    </div>

    <form method="POST" action="{{ route('master.kriteria.peta.update', $p) }}" class="space-y-5">
        @csrf @method('PUT')

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3 text-left">Jawaban warga</th>
                        <th class="px-4 py-3 text-left">Dinilai sebagai (PBI)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @foreach ($p->opsis as $o)
                        @php $pilih = old("peta.{$o->id}", $o->pbi_opsi_id); @endphp
                        <tr>
                            <td class="px-4 py-3 text-center text-xs">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium">{{ $o->label }}</td>
                            <td class="px-4 py-3">
                                <select name="peta[{{ $o->id }}]"
                                    class="w-full rounded-lg border px-3 py-2 text-sm {{ blank($pilih) ? 'border-warning bg-warning-container/30' : 'border-outline-variant/50' }}">
                                    <option value="">— Tidak dihitung —</option>
                                    @foreach ($p->pbiParameter->opsis as $po)
                                        <option value="{{ $po->id }}" @selected((string) $pilih === (string) $po->id)>
                                            {{ $po->label }} ({{ number_format((float) $po->indeks_terintegrasi, 2, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('master.kriteria.index') }}" class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</a>
            <button type="submit" class="px-5 py-2 rounded-lg text-sm bg-primary text-on-primary hover:bg-secondary font-semibold">Simpan</button>
        </div>
    </form>
</div>
@endsection
