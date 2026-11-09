{{--
    Checklist "Hak Akses Menu" di modal Tambah/Edit Hak Akses (dipakai di kelola-akses/index.blade.php).
    Harus berada di dalam scope roleManager() (memakai form.permissions & isSuperAdminEdit).

    - Modul berkatalog (config/akses.php): submenu (= boleh membuka halaman) + aksi per submenu.
      Aksi baru bisa dicentang setelah submenunya dicentang. Mencabut submenu ikut mencabut aksinya.
    - Modul lain (belum ada halamannya): satu centang per modul seperti sebelumnya.
--}}
@php
    $katalog = config('akses', []);
    $labelModul = \App\Models\Role::MODULES;

    // Modul tanpa katalog: dikelompokkan per prefix label ("Rehabsos - PPKS" => grup Rehabsos)
    $legacyGroups = [];
    foreach (collect($labelModul)->except(array_keys($katalog)) as $key => $label) {
        if (str_contains($label, ' - ')) {
            [$g, $item] = explode(' - ', $label, 2);
        } else {
            $g = 'Umum';
            $item = $label;
        }
        $legacyGroups[$g][$key] = $item;
    }
    if (isset($legacyGroups['Umum'])) {
        $legacyGroups = ['Umum' => $legacyGroups['Umum']] + $legacyGroups;
    }
@endphp

<div x-data="{
    has(k) { return form.permissions.includes(k); },
    setMany(keys, on) {
        const s = new Set(form.permissions);
        keys.forEach(k => on ? s.add(k) : s.delete(k));
        form.permissions = [...s];
    },
    toggleSub(sub, aksi, on) { on ? this.setMany([sub], true) : this.setMany([sub, ...aksi], false); },
    allOn(keys) { return keys.length > 0 && keys.every(k => this.has(k)); },
    toggleAll(keys) { this.setMany(keys, !this.allOn(keys)); },
}">
    <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-medium">Hak Akses Menu</label>
        <div x-show="!isSuperAdminEdit" class="flex items-center gap-3 text-xs">
            <span class="text-on-surface-variant"><span x-text="form.permissions.length"></span> dipilih</span>
            <button type="button" @click="form.permissions = []" class="text-red-600 hover:underline">Kosongkan</button>
        </div>
    </div>

    <template x-if="isSuperAdminEdit">
        <p class="text-xs text-on-surface-variant italic bg-surface-container rounded-lg px-3 py-2">
            Super Admin otomatis memiliki akses ke seluruh menu dan tidak bisa diubah.
        </p>
    </template>

    <div x-show="!isSuperAdminEdit" class="space-y-3">
        <p class="text-xs text-on-surface-variant">
            Centang submenu agar halamannya bisa dibuka. Aksi (tambah, ubah, hapus, proses, dst) baru bisa dipilih
            setelah submenunya dicentang.
        </p>

        {{-- ===== Modul dengan hak akses detail ===== --}}
        @foreach ($katalog as $modul => $submenus)
            @php
                $semuaKunci = [];
                foreach ($submenus as $s => $c) {
                    $semuaKunci[] = "{$modul}.{$s}";
                    foreach (array_keys($c['aksi'] ?? []) as $a) {
                        $semuaKunci[] = "{$modul}.{$s}.{$a}";
                    }
                }
            @endphp
            <div class="rounded-lg border border-outline-variant/40 overflow-hidden">
                <div class="flex items-center justify-between px-3 py-2 bg-surface-container-low border-b border-outline-variant/40">
                    <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $labelModul[$modul] ?? $modul }}</span>
                    <button type="button" @click="toggleAll(@js($semuaKunci))"
                        class="text-xs font-medium text-primary hover:underline"
                        x-text="allOn(@js($semuaKunci)) ? 'Batalkan semua' : 'Pilih semua'"></button>
                </div>

                <div class="divide-y divide-outline-variant/20">
                    @foreach ($submenus as $s => $c)
                        @php
                            $kunciSub = "{$modul}.{$s}";
                            $kunciAksi = array_map(fn ($a) => "{$kunciSub}.{$a}", array_keys($c['aksi'] ?? []));
                        @endphp
                        <div class="px-3 py-2">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-on-surface cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $kunciSub }}"
                                    :checked="has('{{ $kunciSub }}')"
                                    @change="toggleSub('{{ $kunciSub }}', @js($kunciAksi), $event.target.checked)"
                                    class="shrink-0 rounded border-outline-variant/50 text-primary focus:ring-primary" />
                                <span>{{ $c['label'] }}</span>
                            </label>

                            @if (! empty($c['aksi']))
                                <div class="ml-7 mt-1.5 flex flex-wrap gap-x-5 gap-y-1">
                                    @foreach ($c['aksi'] as $a => $labelAksi)
                                        <label class="flex items-center gap-2 text-xs text-on-surface-variant cursor-pointer"
                                            :class="has('{{ $kunciSub }}') ? '' : 'opacity-50 cursor-not-allowed'">
                                            <input type="checkbox" name="permissions[]" value="{{ $kunciSub }}.{{ $a }}"
                                                :checked="has('{{ $kunciSub }}.{{ $a }}')"
                                                :disabled="!has('{{ $kunciSub }}')"
                                                @change="setMany(['{{ $kunciSub }}.{{ $a }}'], $event.target.checked)"
                                                class="shrink-0 rounded border-outline-variant/50 text-primary focus:ring-primary" />
                                            <span>{{ $labelAksi }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- ===== Modul tanpa katalog (satu centang per modul) ===== --}}
        @foreach ($legacyGroups as $groupName => $items)
            <div class="rounded-lg border border-outline-variant/40 overflow-hidden">
                <div class="flex items-center justify-between px-3 py-2 bg-surface-container-low border-b border-outline-variant/40">
                    <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $groupName }}</span>
                    <button type="button" @click="toggleAll(@js(array_map('strval', array_keys($items))))"
                        class="text-xs font-medium text-primary hover:underline"
                        x-text="allOn(@js(array_map('strval', array_keys($items)))) ? 'Batalkan semua' : 'Pilih semua'"></button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-0.5 p-2">
                    @foreach ($items as $key => $itemLabel)
                        <label class="flex items-start gap-2.5 px-2 py-1.5 rounded-md text-sm text-on-surface leading-snug cursor-pointer hover:bg-surface-container-low">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                :checked="has('{{ $key }}')"
                                @change="setMany(['{{ $key }}'], $event.target.checked)"
                                class="mt-0.5 shrink-0 rounded border-outline-variant/50 text-primary focus:ring-primary" />
                            <span>{{ $itemLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
