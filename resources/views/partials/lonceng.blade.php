@php
    $extra = $extra ?? [];
    $tone = $tone ?? 'text-white/90 hover:text-white';
@endphp

<div class="relative shrink-0" x-data="lonceng(@js($extra))" x-init="muat(); setInterval(() => muat(), 20000)"
    @click.outside="open = false">
    <button type="button" @click="open = !open; if (open) muat()" title="Notifikasi"
        class="relative flex w-8 h-8 items-center justify-center {{ $tone }}">
        <span class="material-symbols-outlined text-[20px]">notifications</span>
        <span x-show="unread > 0 || extra.length > 0" x-cloak
            class="absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-red-500 ring-2 ring-brand"></span>
    </button>

    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        class="absolute right-0 mt-3 w-80 max-w-[90vw] bg-surface-container-lowest text-on-surface border border-outline-variant/40 rounded-xl shadow-lg overflow-hidden z-50">

        <div class="flex items-center justify-between px-4 py-2.5 border-b border-outline-variant/40">
            <p class="text-xs font-bold text-brand">Notifikasi</p>
            <button type="button" x-show="unread > 0" @click="bacaSemua()"
                class="text-[11px] text-brand hover:underline">Tandai semua dibaca</button>
        </div>

        <div class="max-h-96 overflow-y-auto">
            {{-- Item lama (badge permohonan staf) --}}
            <template x-for="e in extra" :key="e.url">
                <a :href="e.url"
                    class="flex items-start gap-3 px-4 py-3 hover:bg-surface-container border-b border-outline-variant/20">
                    <span class="mt-1.5 w-2 h-2 shrink-0 rounded-full bg-red-500"></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold" x-text="e.judul"></span>
                        <span class="block text-xs text-on-surface-variant mt-0.5"
                            x-text="e.jumlah + ' permohonan baru menunggu tindakan Anda'"></span>
                    </span>
                </a>
            </template>

            <template x-for="n in items" :key="n.id">
                <div class="px-4 py-3 border-b border-outline-variant/20" :class="n.dibaca ? '' : 'bg-brand/5'">
                    <div class="flex items-start gap-3">
                        <span class="mt-1.5 w-2 h-2 shrink-0 rounded-full"
                            :class="n.dibaca ? 'bg-transparent' : 'bg-red-500'"></span>
                        <div class="min-w-0 flex-1">
                            <button type="button" @click="baca(n)" class="block w-full text-left">
                                <span class="block text-sm font-semibold" x-text="n.judul"></span>
                                <span class="block text-xs text-on-surface-variant mt-0.5" x-text="n.pesan"></span>
                                <span class="block text-[10px] text-on-surface-variant/70 mt-1" x-text="n.waktu"></span>
                            </button>

                            <template x-if="n.izin">
                                <div class="mt-2">
                                    <template x-if="n.izin.status === 'pending'">
                                        <div class="flex gap-2">
                                            <button type="button" @click="aksi(n.izin.setujui_url)"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium bg-primary text-white">Setujui</button>
                                            <button type="button" @click="aksi(n.izin.tolak_url)"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium border border-outline-variant/60">Tolak</button>
                                        </div>
                                    </template>
                                    <template x-if="n.izin.status === 'approved'">
                                        <div class="rounded-lg bg-warning-container text-on-warning-container px-3 py-2">
                                            <p class="text-[11px]">Berikan kode ini ke Super Admin (berlaku 5 menit):</p>
                                            <p class="text-xl font-bold tracking-[0.3em] font-mono" x-text="n.izin.otp"></p>
                                        </div>
                                    </template>
                                    <template x-if="['rejected','expired','used'].includes(n.izin.status)">
                                        <p class="text-[11px] text-on-surface-variant italic"
                                            x-text="({rejected:'Anda menolak permintaan ini.', expired:'Permintaan sudah kedaluwarsa.', used:'Izin sudah dipakai.'})[n.izin.status]"></p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="items.length === 0 && extra.length === 0"
                class="px-4 py-6 text-center text-xs text-on-surface-variant">
                <span class="material-symbols-outlined text-[28px] opacity-40 block mb-1">notifications_off</span>
                Tidak ada notifikasi.
            </div>
        </div>
    </div>
</div>

<script>
    function lonceng(extra) {
        return {
            open: false, unread: 0, items: [], extra: extra,
            csrf: @js(csrf_token()),
            async muat() {
                try {
                    const r = await fetch(@js(route('notifikasi.index')), { headers: { Accept: 'application/json' } });
                    if (!r.ok) return;
                    const d = await r.json();
                    this.unread = d.unread;
                    this.items = d.items;
                } catch (e) {}
            },
            kirim(url) {
                return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' } });
            },
            async aksi(url) { await this.kirim(url); await this.muat(); },
            async baca(n) {
                if (!n.dibaca) await this.kirim(n.baca_url);
                if (n.url) { window.location = n.url; return; }
                this.muat();
            },
            async bacaSemua() { await this.kirim(@js(route('notifikasi.baca-semua'))); this.muat(); },
        };
    }
</script>
