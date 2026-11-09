@extends('layouts.admin')

@section('page_title', 'Ngopi Pagi')

@section('content')
{{-- xl:-mb-24 membatalkan pb-24 milik <main> agar halaman tidak ikut ter-scroll. --}}
<div class="space-y-3 xl:-mb-24">
    <a href="{{ $aktif === 'rekap' ? route('ngopi-pagi.admin.rekap') : route('ngopi-pagi.admin.tahap.' . $aktif) }}"
        class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:underline">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        {{ $aktif === 'rekap' ? 'Kembali ke Rekapitulasi' : 'Kembali ke daftar' }}
    </a>

    <div class="xl:h-[calc(100vh-8.5rem)] xl:min-h-[540px]">
        @include('admin.ngopi-pagi._workspace', ['k' => $k])
    </div>
</div>
@endsection
