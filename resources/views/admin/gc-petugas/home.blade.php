@extends('layouts.admin')
@section('title', 'Home Petugas Ground Check')
@section('page_title', 'Home')

@push('styles')
    <style>
        @keyframes gc-rise { from { opacity: 0; transform: translateY(16px) } to { opacity: 1; transform: none } }
        @keyframes gc-ring { 0% { box-shadow: 0 0 0 0 rgba(10,92,168,.45) } 70% { box-shadow: 0 0 0 14px rgba(10,92,168,0) } 100% { box-shadow: 0 0 0 0 rgba(10,92,168,0) } }
        @keyframes gc-pop { 0% { transform: scale(.6); opacity: 0 } 70% { transform: scale(1.12) } 100% { transform: scale(1); opacity: 1 } }
        .gc-rise { animation: gc-rise .55s cubic-bezier(.2, .8, .2, 1) both }
        .gc-ring { animation: gc-ring 1.8s infinite }
        .gc-pop { animation: gc-pop .45s ease-out both }
        @media (prefers-reduced-motion: reduce) { .gc-rise, .gc-ring, .gc-pop { animation: none !important } }
    </style>
@endpush

@section('content')
    @php
        $lengkap = filled($user->gc_profil_at);
        $fields = [
            ['nik', 'NIK', 'badge', 'text', '16 digit angka', 'numeric'],
            ['name', 'Nama Lengkap', 'person', 'text', 'Nama sesuai KTP', 'text'],
            ['no_telp', 'No Telp', 'call', 'tel', '08xxxxxxxxxx', 'tel'],
            ['email', 'Email', 'mail', 'email', 'nama@email.com', 'email'],
            ['pekerjaan', 'Pekerjaan', 'work', 'text', 'Pekerjaan saat ini', 'text'],
            ['no_rekening', 'Nomor Rekening', 'account_balance', 'text', 'Hanya angka', 'numeric'],
        ];
        $nilai = [];
        foreach (['nik', 'name', 'no_telp', 'email', 'alamat', 'pekerjaan', 'no_rekening'] as $k) {
            $nilai[$k] = (string) old($k, $user->{$k} ?? '');
        }
    @endphp

    <div class="max-w-4xl mx-auto space-y-5" x-data="{
        v: @js($nilai),
        submitting: false,
        get filled() { return Object.values(this.v).filter(x => String(x).trim() !== '').length },
        total: 7,
        get pct() { return Math.round(this.filled / this.total * 100) }
    }">

        {{-- Sambutan --}}
        <div class="gc-rise rounded-2xl bg-brand text-white p-6 shadow-[0_10px_30px_rgba(10,92,168,0.25)] relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-44 h-44 rounded-full bg-white/10"></div>
            <div class="absolute right-16 -bottom-14 w-32 h-32 rounded-full bg-white/10"></div>
            <p class="text-sm text-white/80">Selamat datang,</p>
            <h2 class="text-2xl font-extrabold tracking-tight">{{ $user->name ?: $user->username }}</h2>
            <span class="inline-flex mt-3 px-3 py-1 rounded-full bg-white/20 text-xs font-semibold">
                {{ now()->locale('id')->translatedFormat('d F Y') }}
            </span>
        </div>

        {{-- Langkah --}}
        <div class="gc-rise rounded-2xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/30" style="animation-delay:.08s">
            <div class="flex items-center">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors duration-500 {{ $lengkap ? 'bg-success text-white gc-pop' : 'bg-brand text-white gc-ring' }}">
                        @if ($lengkap)
                            <span class="material-symbols-outlined text-[22px]">check</span>
                        @else
                            1
                        @endif
                    </span>
                    <div class="leading-tight">
                        <p class="text-sm font-bold">Lengkapi Data Petugas</p>
                        <p class="text-xs text-on-surface-variant">{{ $lengkap ? 'Sudah lengkap' : 'Wajib sebelum Ground Check' }}</p>
                    </div>
                </div>
                <div class="flex-1 mx-4 h-1 rounded-full bg-surface-container-high overflow-hidden">
                    <div class="h-full bg-success transition-all duration-700" style="width: {{ $lengkap ? '100%' : '0%' }}"></div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors {{ $lengkap ? 'bg-brand text-white gc-ring' : 'bg-surface-container-high text-on-surface-variant' }}">2</span>
                    <div class="leading-tight">
                        <p class="text-sm font-bold">Ground Check</p>
                        <p class="text-xs text-on-surface-variant">{{ $lengkap ? 'Siap dimulai' : 'Terkunci' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form data petugas --}}
        <form method="POST" action="{{ route('gc.profil.update') }}" @submit="submitting = true"
            class="gc-rise rounded-2xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/30" style="animation-delay:.16s">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between gap-4 mb-1">
                <h3 class="text-base font-bold text-on-surface">Data Petugas</h3>
                <span class="text-xs font-semibold text-brand tabular-nums" x-text="filled + '/' + total + ' terisi'"></span>
            </div>
            <div class="h-1.5 rounded-full bg-surface-container-high overflow-hidden mb-5">
                <div class="h-full rounded-full bg-gradient-to-r from-brand to-secondary-container transition-all duration-500 ease-out"
                    :style="`width:${pct}%`"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Username (hanya baca, dibuat Super Admin) --}}
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Username</label>
                    <div class="flex items-center gap-2 rounded-lg bg-surface-container px-3 py-2.5 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-[18px]">lock</span>
                        {{ $user->username }}
                    </div>
                </div>

                @foreach ($fields as [$name, $label, $icon, $type, $ph, $mode])
                    <div>
                        <label for="f-{{ $name }}" class="block text-xs font-semibold text-on-surface-variant mb-1">{{ $label }}</label>
                        <div class="group flex items-center gap-2 rounded-lg border px-3 py-2.5 bg-white transition-all duration-200
                            focus-within:border-brand focus-within:ring-4 focus-within:ring-brand/10
                            {{ $errors->has($name) ? 'border-error' : 'border-outline-variant/60' }}">
                            <span class="material-symbols-outlined text-[18px] text-outline group-focus-within:text-brand transition-colors">{{ $icon }}</span>
                            <input id="f-{{ $name }}" name="{{ $name }}" type="{{ $type }}" inputmode="{{ $mode }}"
                                x-model="v.{{ $name }}" placeholder="{{ $ph }}" required
                                @if ($name === 'nik') maxlength="16" @endif
                                class="w-full bg-transparent border-0 p-0 text-sm outline-none focus:ring-0 placeholder:text-on-surface-variant/50" />
                            <span x-show="String(v.{{ $name }}).trim() !== ''" x-cloak
                                x-transition.scale.origin.center
                                class="material-symbols-outlined text-[18px] text-success">check_circle</span>
                        </div>
                        @error($name)
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                <div class="sm:col-span-2">
                    <label for="f-alamat" class="block text-xs font-semibold text-on-surface-variant mb-1">Alamat</label>
                    <div class="group flex items-start gap-2 rounded-lg border px-3 py-2.5 bg-white transition-all duration-200
                        focus-within:border-brand focus-within:ring-4 focus-within:ring-brand/10
                        {{ $errors->has('alamat') ? 'border-error' : 'border-outline-variant/60' }}">
                        <span class="material-symbols-outlined text-[18px] text-outline group-focus-within:text-brand transition-colors mt-0.5">home</span>
                        <textarea id="f-alamat" name="alamat" rows="2" x-model="v.alamat" required placeholder="Alamat lengkap"
                            class="w-full bg-transparent border-0 p-0 text-sm outline-none focus:ring-0 resize-none placeholder:text-on-surface-variant/50"></textarea>
                    </div>
                    @error('alamat')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-semibold
                           hover:bg-secondary hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 transition-all disabled:opacity-60">
                    <span class="material-symbols-outlined text-[18px]" :class="submitting ? 'animate-spin' : ''"
                        x-text="submitting ? 'progress_activity' : 'save'"></span>
                    {{ $lengkap ? 'Perbarui Data' : 'Simpan Data' }}
                </button>
            </div>
        </form>

        {{-- Dokumen petugas --}}
        <div class="gc-rise rounded-2xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/30" style="animation-delay:.22s">
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-[20px] text-brand">description</span>
                <h3 class="text-base font-bold text-on-surface">Dokumen Petugas Ground Check</h3>
            </div>
            <p class="text-xs text-on-surface-variant mb-4">
                {{ $lengkap ? 'Dokumen terisi otomatis dari data petugas. Silakan unduh.' : 'Lengkapi dan simpan data petugas, lalu dokumen berikut akan tersedia untuk diunduh.' }}
            </p>

            <ul class="space-y-2">
                @foreach ($daftarDokumen as $kode => $nama)
                    @php $ada = isset($dokumenAda[$kode]); @endphp
                    <li class="gc-rise flex items-center justify-between gap-3 rounded-xl px-4 py-3 ring-1 transition-all
                        {{ $lengkap && $ada ? 'bg-brand-soft ring-brand/15 hover:ring-brand/40 hover:-translate-y-0.5 hover:shadow-md' : 'bg-surface-container-low ring-outline-variant/30' }}"
                        style="animation-delay: {{ 0.28 + $loop->index * 0.07 }}s">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="material-symbols-outlined text-[22px] {{ $lengkap && $ada ? 'text-success' : 'text-outline' }}">
                                {{ $lengkap && $ada ? 'task_alt' : 'lock' }}
                            </span>
                            <span class="text-sm font-semibold truncate">{{ $nama }}</span>
                        </div>

                        @if ($lengkap && $ada)
                            <a href="{{ route('gc.dokumen', $kode) }}"
                                class="group inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-success text-white text-xs font-semibold hover:brightness-110 transition-all">
                                <span class="material-symbols-outlined text-[16px] transition-transform group-hover:translate-y-0.5">download</span>
                                Download
                            </a>
                        @elseif ($lengkap && ! $ada)
                            <span class="text-[11px] text-on-surface-variant">Template belum diunggah</span>
                        @else
                            <span class="text-[11px] text-on-surface-variant">Terkunci</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Mulai Ground Check --}}
        <div class="gc-rise rounded-2xl p-5 flex flex-wrap items-center justify-between gap-4 ring-1
            {{ $lengkap ? 'bg-brand-soft ring-brand/20' : 'bg-surface-container-low ring-outline-variant/30' }}"
            style="animation-delay:.5s">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[28px] {{ $lengkap ? 'text-brand' : 'text-outline' }}">
                    {{ $lengkap ? 'task_alt' : 'lock' }}
                </span>
                <div class="leading-tight">
                    <p class="text-sm font-bold">Ground Check 2026</p>
                    <p class="text-xs text-on-surface-variant">
                        {{ $lengkap ? 'Data petugas lengkap. Pilih keluarga dan mulai verifikasi.' : 'Simpan data petugas dulu untuk membuka menu ini.' }}
                    </p>
                </div>
            </div>

            @if ($lengkap)
                <a href="{{ route('ground-check.index') }}"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold gc-ring
                           hover:bg-primary hover:-translate-y-0.5 hover:shadow-lg transition-all">
                    <span class="material-symbols-outlined text-[18px]">edit_note</span>
                    Mulai Ground Check
                    <span class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</span>
                </a>
            @else
                <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-surface-container-high text-on-surface-variant text-sm font-semibold cursor-not-allowed">
                    <span class="material-symbols-outlined text-[18px]">lock</span> Terkunci
                </span>
            @endif
        </div>
    </div>
@endsection