@extends('layouts.admin')

@section('page_title', 'Ngopi Pagi')

@section('content')
<div class="space-y-4">
    @include('admin.ngopi-pagi._nav', ['aktif' => $aktif])

    <a href="{{ $aktif === 'rekap' ? route('ngopi-pagi.admin.rekap') : route('ngopi-pagi.admin.tahap.' . $aktif) }}"
        class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:underline">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        {{ $aktif === 'rekap' ? 'Kembali ke Rekapitulasi' : 'Kembali ke daftar' }}
    </a>

    @include('admin.ngopi-pagi._workspace', ['k' => $k])
</div>
@endsection
