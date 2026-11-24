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

    {{-- ===================== KARTU 1: KEPALA KELUARGA ===================== --}}
    <div class="rounded-3xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/30 p-4 sm:p-6 mb-4">
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
                    <span class="px-2 py-0.5 rounded-full bg-warning-container text-on-warning-container text-[11px] font-semibold">Data belum diisi</span>
                @else
                    <span class="px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-[11px] font-semibold">Data terisi &middot; Masuk Ground Check</span>
                    @if ($kepala['diajukan_pbi'])
                        <span class="px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-[11px] font-semibold">PBI APBD diajukan &middot; {{ $kepala['status_label'] }}</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-warning-container text-on-warning-container text-[11px] font-semibold">PBI APBD belum diajukan</span>
                    @endif
                @endif

                @if ($kepala['sudah_mengisi'] && $kepala['dikembalikan'])
                    <span class="px-2 py-0.5 rounded-full bg-error-container text-on-error-container text-[11px] font-semibold">Perlu diperbaiki</span>
                @endif
            </div>
        </div>

        @if ($kepala['sudah_mengisi'] && ! $kepala['bisa_isi'])
            <p class="mt-3 text-xs text-on-surface-variant">
                Pengajuan PBI APBD kepala keluarga sudah dikirim dan tidak dapat diajukan lagi.
            </p>
        @elseif (! $kepala['sudah_mengisi'])
            <p class="mt-3 text-xs text-on-surface-variant">
                Kepala keluarga wajib mengisi form pengajuan lengkap terlebih dahulu.
            </p>
        @endif

        <div class="mt-3">
            @if ($kepala['bisa_isi'])
                <a href="{{ route('masyarakat.pbi-apbd.lengkapi', $pbiApbd) }}"
                   class="w-full sm:w-auto sm:px-8 inline-flex items-center justify-center gap-2 py-3 rounded-full bg-primary shadow-md text-white font-semibold text-sm">
                    <span class="material-symbols-outlined text-[18px]">edit_note</span>
                    {{ $kepala['sudah_mengisi'] ? 'Perbaiki Data Pengajuan' : 'Isi Data Pengajuan' }}
                </a>
            @else
                <a href="{{ route('masyarakat.pbi-apbd.lengkapi', $pbiApbd) }}"
                   class="w-full sm:w-auto sm:px-8 inline-flex items-center justify-center gap-2 py-3 rounded-full border border-primary text-primary font-semibold text-sm">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                    Lihat Data Pengajuan
                </a>
            @endif
        </div>
    </div>
@endsection

