@extends('layouts.admin')

@section('title', $moduleTitle . ' — ' . $tabLabel)
@section('page_title', $moduleTitle . ' — ' . $tabLabel)

@section('content')
<div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-10 flex flex-col items-center justify-center text-center gap-3 min-h-[50vh]">
    <span class="material-symbols-outlined text-[48px] text-secondary">construction</span>
    <h2 class="text-lg font-semibold">{{ $moduleTitle }} — {{ $tabLabel }}</h2>
    <p class="text-sm text-on-surface-variant max-w-md">
        Halaman ini sudah tersedia menu &amp; route-nya, tapi fiturnya belum dibangun.
        Saat ini pengembangan difokuskan ke modul <strong>PBI APBN</strong> terlebih dahulu.
    </p>
</div>
@endsection
