@extends('layouts.admin')

@section('title', 'Beranda Rehabsos - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Rehabsos — Beranda')

@php $card = 'bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)]'; @endphp

@section('content')
    <div class="space-y-6">
        @foreach ($groups as $key => $g)
            <section class="{{ $card }} p-4 sm:p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-[22px] text-brand">{{ $g['icon'] }}</span>
                    <h2 class="text-sm font-bold text-on-surface">{{ $g['label'] }}</h2>
                </div>
                <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach ($stat[$key] as $s)
                        <div class="rounded-2xl bg-slate-50 p-4 shadow-[0_4px_12px_rgba(0,0,0,0.08)]">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center text-brand bg-brand-soft">
                                <span class="material-symbols-outlined text-[20px]">{{ $s['icon'] }}</span>
                            </span>
                            <p class="text-[11px] text-on-surface-variant mt-4">{{ $s['label'] }}</p>
                            <p class="text-xl font-extrabold text-on-surface mt-0.5">{{ number_format($s['value']) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        @if ($groups->isEmpty())
            <div class="{{ $card }} p-8 text-center text-sm text-on-surface-variant">
                Role Anda belum memiliki akses ke sub-modul Rehabsos.
            </div>
        @endif
    </div>
@endsection