@extends('layouts.admin')

@section('title', 'Izin Masuk Akun - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Izin Masuk Akun')

@section('content')
    <div class="max-w-md mx-auto bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-6"
        x-data="izinMasuk()">

        <p class="text-sm text-on-surface-variant mb-1">Meminta izin masuk ke akun</p>
        <p class="text-base font-semibold text-on-surface mb-5">
            {{ $izin->target->name }}
            <span class="text-xs font-normal text-on-surface-variant">({{ $izin->target->role->name ?? '-' }})</span>
        </p>

        {{-- Menunggu persetujuan --}}
        <div x-show="status === 'pending'" class="text-center py-6">
            <span class="material-symbols-outlined text-[32px] animate-spin text-primary">progress_activity</span>
            <p class="text-sm mt-3">Menunggu pemilik akun menyetujui permintaan di lonceng notifikasinya...</p>
            <p class="text-xs text-on-surface-variant mt-1">Permintaan berlaku {{ \App\Models\ImpersonateRequest::MENIT_MENUNGGU_IZIN }} menit.</p>
        </div>

        {{-- Disetujui: input OTP --}}
        <form x-show="status === 'approved'" x-cloak method="POST" action="{{ route('akun.izin.verifikasi', $izin) }}"
            class="space-y-3">
            @csrf
            <p class="text-sm text-success font-medium">Izin disetujui. Minta kode OTP 6 digit dari pemilik akun.</p>
            <input type="text" name="otp" inputmode="numeric" maxlength="6" autocomplete="off" autofocus
                placeholder="------"
                class="w-full text-center text-2xl tracking-[0.5em] font-mono rounded-lg border border-outline-variant/50 px-3 py-2" />
            @error('otp')
                <p class="text-xs text-error">{{ $message }}</p>
            @enderror
            <button type="submit" class="w-full px-4 py-2 rounded-lg text-sm bg-primary text-on-primary">Masuk</button>
        </form>

        <div x-show="status === 'rejected'" x-cloak class="text-sm text-error">Pemilik akun menolak permintaan ini.</div>
        <div x-show="status === 'expired'" x-cloak class="text-sm text-error">Permintaan sudah kedaluwarsa. Silakan ajukan lagi.</div>
        <div x-show="status === 'used'" x-cloak class="text-sm text-on-surface-variant">Izin ini sudah dipakai.</div>

        <a href="{{ url()->previous() === url()->current() ? route('akun.index') : route('akun.index') }}"
            class="inline-block mt-5 text-xs text-brand hover:underline">&larr; Kembali ke Kelola Akses</a>
    </div>

    @push('scripts')
        <script>
            function izinMasuk() {
                return {
                    status: @js($izin->statusEfektif()),
                    timer: null,
                    init() { this.timer = setInterval(() => this.cek(), 3000); },
                    async cek() {
                        if (!['pending', 'approved'].includes(this.status)) { clearInterval(this.timer); return; }
                        try {
                            const r = await fetch(@js(route('akun.izin.status', $izin)), { headers: { Accept: 'application/json' } });
                            if (r.ok) this.status = (await r.json()).status;
                        } catch (e) {}
                    },
                };
            }
        </script>
    @endpush
@endsection
