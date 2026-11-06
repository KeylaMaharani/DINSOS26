@props(['konselor', 'size' => 'w-16 h-16', 'rounded' => 'rounded-2xl'])
@php $warna = ['bg-sky-600', 'bg-emerald-600', 'bg-violet-600', 'bg-rose-600', 'bg-amber-600', 'bg-teal-600']; @endphp
@if ($konselor->foto_url)
    <img src="{{ $konselor->foto_url }}" alt="{{ $konselor->nama_tampilan }}" loading="lazy" class="{{ $size }} {{ $rounded }} object-cover shrink-0 bg-brand-soft">
@else
    <span aria-hidden="true" class="{{ $size }} {{ $rounded }} {{ $warna[$konselor->id % 6] }} shrink-0 text-white font-extrabold text-xl flex items-center justify-center">{{ $konselor->inisial() }}</span>
@endif
