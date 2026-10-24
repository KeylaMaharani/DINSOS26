{{-- Dropdown multi-pilih bergaya "tag" (pengganti checkbox, sama seperti di SOLID lama).
     Pilihan terpilih tampil sebagai kotak kecil (klik × untuk menghapus); daftar hanya berisi yang belum dipilih
     dan bisa dicari dengan mengetik. Opsi bertanda eksklusif (mis. "Belum Ada" / "Tidak Ada")
     menggantikan pilihan lain, dan sebaliknya.

     Variabel: $nm (name input, tanpa []), $opsiMulti ([['nama' => ..., 'eksl' => bool], ...]), $dipilih (array nama terpilih) --}}
<div x-data="{
        opsi: @js($opsiMulti),
        terpilih: @js($dipilih),
        q: '',
        buka: false,
        get sisa() {
            const k = this.q.trim().toLowerCase();
            return this.opsi.filter(o => !this.terpilih.includes(o.nama) && (k === '' || o.nama.toLowerCase().includes(k)));
        },
        eksklusif(n) { const o = this.opsi.find(x => x.nama === n); return !!(o && o.eksl); },
        pilih(o) {
            this.terpilih = o.eksl
                ? [o.nama]
                : [...this.terpilih.filter(n => !this.eksklusif(n)), o.nama];
            this.q = '';
            this.buka = false;
            this.ubah();
        },
        hapus(n) { this.terpilih = this.terpilih.filter(x => x !== n); this.ubah(); },
        ubah() { this.$nextTick(() => this.$root.dispatchEvent(new Event('change', { bubbles: true }))); }
     }"
     @click.outside="buka = false">

    {{-- Penanda terisi/kosong untuk validasi 'wajib' di wizard (tanpa name, tidak ikut terkirim) --}}
    <input type="hidden" :value="terpilih.join('|')">
    {{-- Nilai yang benar-benar dikirim --}}
    <template x-for="n in terpilih" :key="n">
        <input type="hidden" name="{{ $nm }}[]" :value="n">
    </template>

    <div @click="buka = true; $refs.cari.focus()"
         :class="buka ? 'border-primary ring-2 ring-primary/20' : 'border-outline-variant/50'"
         class="min-h-[42px] flex flex-wrap items-center gap-1.5 px-2 py-1.5 rounded-lg border bg-white cursor-text transition-all">
        <template x-for="n in terpilih" :key="n">
            <span class="inline-flex items-center gap-1 pl-2 pr-1 py-0.5 rounded border border-outline-variant/60 bg-surface-container text-[12px] text-on-surface">
                <span x-text="n"></span>
                <button type="button" @click.stop="hapus(n)" title="Hapus"
                        class="w-4 h-4 rounded flex items-center justify-center text-on-surface-variant hover:bg-error hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[13px]">close</span>
                </button>
            </span>
        </template>
        <input x-ref="cari" type="text" x-model="q" autocomplete="off"
               @focus="buka = true"
               @keydown.escape="buka = false"
               @keydown.enter.prevent="if (sisa.length) pilih(sisa[0])"
               @keydown.backspace="if (q === '' && terpilih.length) hapus(terpilih[terpilih.length - 1])"
               :placeholder="terpilih.length ? '' : '-- Pilih --'"
               class="flex-1 min-w-[90px] border-0 outline-none bg-transparent p-0 text-sm focus:ring-0" />
        <span class="material-symbols-outlined text-[18px] text-on-surface-variant transition-transform" :class="buka ? 'rotate-180' : ''">expand_more</span>
    </div>

    {{-- Daftar dibuat mengalir (bukan melayang) supaya tidak terpotong card form --}}
    <div x-show="buka" x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mt-1 max-h-56 overflow-y-auto rounded-lg border border-outline-variant/50 bg-white shadow-md py-1">
        <template x-for="o in sisa" :key="o.nama">
            <button type="button" @click="pilih(o)"
                    class="block w-full text-left px-3 py-1.5 text-sm text-on-surface hover:bg-brand hover:text-white transition-colors"
                    x-text="o.nama"></button>
        </template>
        <p x-show="sisa.length === 0" class="px-3 py-2 text-xs text-on-surface-variant">Tidak ada pilihan yang cocok.</p>
    </div>
</div>
