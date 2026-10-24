@extends('layouts.masyarakat')

@section('title', 'Anggota Keluarga - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Anggota Keluarga')
@section('container', 'max-w-6xl')

@section('content')
    @php
        $kosong = [
            'nik' => '', 'nama' => '', 'hubungan_keluarga' => '', 'tempat_lahir' => '',
            'tanggal_lahir' => '', 'jenis_kelamin' => '', 'agama' => '', 'pekerjaan' => '',
        ];
        $lama = [
            'nik' => old('nik', ''), 'nama' => old('nama', ''),
            'hubungan_keluarga' => old('hubungan_keluarga', ''), 'tempat_lahir' => old('tempat_lahir', ''),
            'tanggal_lahir' => old('tanggal_lahir', ''), 'jenis_kelamin' => old('jenis_kelamin', ''),
            'agama' => old('agama', ''), 'pekerjaan' => old('pekerjaan', ''),
        ];
        $inputCls = 'w-full h-11 px-3 rounded-lg border border-outline-variant bg-white text-sm text-on-surface
                     focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand';
        $labelCls = 'text-sm text-on-surface-variant sm:pt-3';
        $rowCls = 'grid grid-cols-1 sm:grid-cols-[11rem_minmax(0,1fr)] gap-x-4 gap-y-1';
        $jkLabel = ['L' => 'LAKI-LAKI', 'P' => 'PEREMPUAN'];
    @endphp

    <div x-data="{
        open: {{ $errors->any() ? 'true' : 'false' }},
        mode: @js(old('_mode', 'tambah')),
        id: @js(old('_id')),
        q: '',
        rows: @js($anggota->keyBy('id')),
        hub: @js(collect($hubunganOpsi)->map(fn ($l, $v) => ['v' => $v, 'l' => $l])->values()),
        f: @js($lama),
        storeUrl: @js(route('masyarakat.keluarga.store')),
        get action() { return this.mode === 'edit' ? this.storeUrl + '/' + this.id : this.storeUrl; },
        get hubOpts() {
            const v = this.f.hubungan_keluarga;
            return v && !this.hub.some(h => h.v === v) ? [...this.hub, { v: v, l: v }] : this.hub;
        },
        openTambah() { this.mode = 'tambah'; this.id = null; this.f = @js($kosong); this.open = true; },
        openEdit(id) {
            const r = this.rows[id];
            if (!r) return;
            this.mode = 'edit';
            this.id = id;
            this.f = {
                nik: r.nik || '', nama: r.nama || '', hubungan_keluarga: r.hubungan || '',
                tempat_lahir: r.tempat_lahir || '', tanggal_lahir: r.tanggal_lahir || '',
                jenis_kelamin: r.jenis_kelamin || '', agama: r.agama || '', pekerjaan: r.pekerjaan || '',
            };
            this.open = true;
        }
    }" @keydown.escape.window="open = false">

        <section class="rounded-2xl bg-white ring-1 ring-outline-variant/40 shadow-sm overflow-hidden">

            {{-- Judul + tombol tambah + pencarian --}}
            <header class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-outline-variant/40">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-10 h-10 shrink-0 rounded-xl bg-brand-soft text-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">group</span>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-base font-extrabold text-brand leading-tight">Daftar Anggota Keluarga</h2>
                        <p class="text-xs text-on-surface-variant">
                            {{ $anggota->count() }} anggota
                            @if ($pbi)
                                &middot; No. KK {{ $pbi->no_kk }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    @if ($anggota->isNotEmpty())
                        <div class="relative flex-1 sm:flex-none sm:w-60">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant">search</span>
                            <input type="search" x-model="q" placeholder="Cari NIK, nama..."
                                class="w-full h-10 pl-10 pr-3 rounded-xl border border-outline-variant bg-surface text-sm
                                       focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" />
                        </div>
                    @endif

                    @if ($pbi && $bisaEdit)
                        <button type="button" @click="openTambah()"
                            class="h-10 inline-flex items-center gap-1.5 rounded-xl bg-brand px-4 text-sm font-semibold text-white hover:bg-primary transition-colors">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Tambah Data
                        </button>
                    @endif
                </div>
            </header>

            {{-- Pengajuan sudah dikunci --}}
            @if ($pbi && ! $bisaEdit)
                <div class="mx-5 mt-4 flex items-start gap-2 rounded-xl bg-warning-container px-4 py-3 text-sm text-on-warning-container">
                    <span class="material-symbols-outlined text-[20px] shrink-0">lock</span>
                    <span>Pengajuan sudah disetujui, data anggota keluarga hanya bisa dilihat dan tidak dapat diubah lagi.</span>
                </div>
            @endif

            @if (! $pbi)
                {{-- Belum punya pengajuan PBI APBD --}}
                <div class="px-5 py-12 text-center">
                    <span class="material-symbols-outlined text-[40px] text-outline-variant">family_restroom</span>
                    <p class="mt-2 text-sm font-semibold text-on-surface">Belum ada data keluarga</p>
                    <p class="mt-1 text-sm text-on-surface-variant">
                        Anggota keluarga tercatat di pengajuan PBI APBD. Daftar PBI APBD terlebih dahulu.
                    </p>
                </div>
            @elseif ($anggota->isEmpty())
                <div class="px-5 py-12 text-center">
                    <span class="material-symbols-outlined text-[40px] text-outline-variant">group_off</span>
                    <p class="mt-2 text-sm text-on-surface-variant">Belum ada anggota keluarga yang tercatat.</p>
                </div>
            @else
                {{-- Tabel (layar >= xl): lebar tetap sesuai kartu, tanpa scroll horizontal --}}
                <div class="hidden xl:block mt-4">
                    <table class="w-full table-fixed text-[13px]">
                        <colgroup>
                            <col class="w-[8%]">   {{-- Aksi --}}
                            <col class="w-[15%]">  {{-- NIK --}}
                            <col class="w-[12%]">  {{-- Nama --}}
                            <col class="w-[12%]">  {{-- Tempat/Tgl Lahir --}}
                            <col class="w-[9%]">   {{-- Jenis Kelamin --}}
                            <col class="w-[6%]">   {{-- Agama --}}
                            <col class="w-[9%]">   {{-- Pekerjaan --}}
                            <col class="w-[7%]">   {{-- Hub Keluarga --}}
                            <col class="w-[9%]">   {{-- Keanggotaan BPJS --}}
                            <col class="w-[6%]">   {{-- No BPJS --}}
                            <col class="w-[7%]">   {{-- Status Peserta --}}
                        </colgroup>
                        <thead>
                            <tr class="bg-surface-container-low text-left text-[10px] font-bold uppercase tracking-wide text-on-surface-variant">
                                <th class="pl-4 pr-2 py-3">Aksi</th>
                                <th class="px-2 py-3">NIK</th>
                                <th class="px-2 py-3">Nama</th>
                                <th class="px-2 py-3">Tempat/Tgl Lahir</th>
                                <th class="px-2 py-3">Jenis Kelamin</th>
                                <th class="px-2 py-3">Agama</th>
                                <th class="px-2 py-3">Pekerjaan</th>
                                <th class="px-2 py-3">Hub Keluarga</th>
                                <th class="px-2 py-3">Keanggotaan BPJS</th>
                                <th class="px-2 py-3">No BPJS</th>
                                <th class="pl-2 pr-4 py-3">Status Peserta</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @foreach ($anggota as $a)
                                <tr x-show="!q || $el.textContent.toLowerCase().includes(q.toLowerCase())"
                                    class="hover:bg-surface transition-colors align-top">
                                    <td class="pl-4 pr-2 py-3">
                                        @if ($bisaEdit)
                                            <button type="button" @click="openEdit({{ $a['id'] }})"
                                                class="inline-flex items-center gap-1 rounded-lg border border-brand/30 px-2 py-1 text-xs font-semibold text-brand hover:bg-brand-soft transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                                Edit
                                            </button>
                                        @else
                                            <span class="text-on-surface-variant">-</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-3 font-semibold text-on-surface whitespace-nowrap">{{ $a['nik'] ?: '-' }}</td>
                                    <td class="px-2 py-3 text-on-surface break-words">{{ strtoupper($a['nama'] ?? '-') }}</td>
                                    <td class="px-2 py-3 text-on-surface-variant break-words">
                                        {{ strtoupper($a['tempat_lahir'] ?? '-') }}{{ $a['tanggal_lahir'] ? ', ' . $a['tanggal_lahir'] : '' }}
                                    </td>
                                    <td class="px-2 py-3 text-on-surface-variant break-words">{{ $jkLabel[$a['jenis_kelamin']] ?? '-' }}</td>
                                    <td class="px-2 py-3 text-on-surface-variant break-words">{{ strtoupper($a['agama'] ?? '-') }}</td>
                                    <td class="px-2 py-3 text-on-surface-variant break-words">{{ strtoupper($a['pekerjaan'] ?? '-') }}</td>
                                    <td class="px-2 py-3 text-on-surface-variant break-words">{{ $a['hubungan_label'] ?: '-' }}</td>
                                    <td class="px-2 py-3 text-on-surface-variant break-words">{{ $a['bpjs'] ?: '-' }}</td>
                                    <td class="px-2 py-3 text-on-surface-variant break-words">{{ $a['no_bpjs'] ?: '-' }}</td>
                                    <td class="pl-2 pr-4 py-3 text-on-surface-variant break-words">{{ $a['status_peserta'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Kartu (layar < xl) --}}
                <div class="xl:hidden divide-y divide-outline-variant/30 mt-4">
                    @foreach ($anggota as $a)
                        <article x-show="!q || $el.textContent.toLowerCase().includes(q.toLowerCase())" class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-on-surface break-words">{{ strtoupper($a['nama'] ?? '-') }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ $a['nik'] ?: '-' }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-brand-soft px-3 py-1 text-xs font-bold text-brand">
                                    {{ $a['hubungan_label'] ?: '-' }}
                                </span>
                            </div>

                            <dl class="text-sm space-y-1">
                                <div class="flex gap-2">
                                    <dt class="w-28 shrink-0 text-on-surface-variant">Tempat/Tgl Lahir</dt>
                                    <dd class="text-on-surface break-words min-w-0">
                                        {{ strtoupper($a['tempat_lahir'] ?? '-') }}{{ $a['tanggal_lahir'] ? ', ' . $a['tanggal_lahir'] : '' }}
                                    </dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="w-28 shrink-0 text-on-surface-variant">Jenis Kelamin</dt>
                                    <dd class="text-on-surface">{{ $jkLabel[$a['jenis_kelamin']] ?? '-' }}</dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="w-28 shrink-0 text-on-surface-variant">Agama</dt>
                                    <dd class="text-on-surface">{{ strtoupper($a['agama'] ?? '-') }}</dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="w-28 shrink-0 text-on-surface-variant">Pekerjaan</dt>
                                    <dd class="text-on-surface break-words min-w-0">{{ strtoupper($a['pekerjaan'] ?? '-') }}</dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="w-28 shrink-0 text-on-surface-variant">BPJS</dt>
                                    <dd class="text-on-surface break-words min-w-0">
                                        {{ $a['bpjs'] ?: '-' }}
                                        @if ($a['no_bpjs'])
                                            &middot; {{ $a['no_bpjs'] }}
                                        @endif
                                        @if ($a['status_peserta'])
                                            &middot; {{ $a['status_peserta'] }}
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            @if ($bisaEdit)
                                <button type="button" @click="openEdit({{ $a['id'] }})"
                                    class="w-full inline-flex items-center justify-center gap-1 rounded-lg border border-brand/30 px-3 py-2 text-xs font-semibold text-brand">
                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                    Edit
                                </button>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="h-4"></div>
        </section>

        {{-- Modal tambah / edit --}}
        @if ($pbi && $bisaEdit)
            <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4">
                <div class="absolute inset-0 bg-black/50" @click="open = false"></div>

                <div class="relative w-full sm:max-w-3xl max-h-[92dvh] overflow-y-auto rounded-t-3xl sm:rounded-2xl bg-white shadow-xl"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0">

                    {{-- Judul + tombol tutup (x) --}}
                    <div class="flex items-start justify-between gap-3 px-6 pt-6 pb-2">
                        <h3 class="text-2xl font-bold text-primary"
                            x-text="mode === 'edit' ? 'Ubah Anggota Keluarga' : 'Tambah Anggota Keluarga'"></h3>
                        <button type="button" @click="open = false" title="Tutup"
                            class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center text-on-surface-variant hover:bg-surface-container">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form method="POST" :action="action">
                        @csrf
                        <input type="hidden" name="_method" value="PUT" :disabled="mode !== 'edit'">
                        <input type="hidden" name="_mode" :value="mode">
                        <input type="hidden" name="_id" :value="id">

                        <div class="px-6 py-4 space-y-4">

                            {{-- 1. NIK --}}
                            <div class="{{ $rowCls }}">
                                <label for="f_nik" class="{{ $labelCls }}">NIK</label>
                                <div class="sm:max-w-md">
                                    <input id="f_nik" type="text" name="nik" x-model="f.nik" maxlength="16"
                                        inputmode="numeric" class="{{ $inputCls }}" required>
                                    @error('nik') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- 2. Nama Pemohon --}}
                            <div class="{{ $rowCls }}">
                                <label for="f_nama" class="{{ $labelCls }}">Nama Pemohon</label>
                                <div class="sm:max-w-md">
                                    <input id="f_nama" type="text" name="nama" x-model="f.nama" maxlength="150"
                                        class="{{ $inputCls }}" required>
                                    @error('nama') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- 3. Status Hubungan Keluarga --}}
                            <div class="{{ $rowCls }}">
                                <label for="f_hub" class="{{ $labelCls }}">Status Hubungan Keluarga</label>
                                <div class="sm:max-w-md">
                                    <select id="f_hub" name="hubungan_keluarga" x-model="f.hubungan_keluarga"
                                        class="{{ $inputCls }}" required>
                                        <option value="">-Pilih-</option>
                                        <template x-for="h in hubOpts" :key="h.v">
                                            <option :value="h.v" x-text="h.l"></option>
                                        </template>
                                    </select>
                                    @error('hubungan_keluarga') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- 4. Tempat Lahir --}}
                            <div class="{{ $rowCls }}">
                                <label for="f_tempat" class="{{ $labelCls }}">Tempat Lahir</label>
                                <div class="sm:max-w-md">
                                    <input id="f_tempat" type="text" name="tempat_lahir" x-model="f.tempat_lahir"
                                        maxlength="100" class="{{ $inputCls }}" required>
                                    @error('tempat_lahir') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- 5. Tgl Lahir --}}
                            <div class="{{ $rowCls }}">
                                <label for="f_tgl" class="{{ $labelCls }}">Tgl Lahir</label>
                                <div class="sm:max-w-md">
                                    <input id="f_tgl" type="date" name="tanggal_lahir" x-model="f.tanggal_lahir"
                                        max="{{ now()->subDay()->toDateString() }}" class="{{ $inputCls }}" required>
                                    @error('tanggal_lahir') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- 6. Jenis Kelamin --}}
                            <div class="{{ $rowCls }}">
                                <label for="f_jk" class="{{ $labelCls }}">Jenis Kelamin</label>
                                <div class="sm:max-w-md">
                                    <select id="f_jk" name="jenis_kelamin" x-model="f.jenis_kelamin"
                                        class="{{ $inputCls }}" required>
                                        <option value="">-Pilih-</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- 7. Agama --}}
                            <div class="{{ $rowCls }}">
                                <label for="f_agama" class="{{ $labelCls }}">Agama</label>
                                <div class="sm:max-w-md">
                                    <select id="f_agama" name="agama" x-model="f.agama" class="{{ $inputCls }}" required>
                                        <option value="">-Pilih-</option>
                                        @foreach ($agamaOpsi as $ag)
                                            <option value="{{ $ag }}">{{ $ag }}</option>
                                        @endforeach
                                    </select>
                                    @error('agama') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- 8. Pekerjaan --}}
                            <div class="{{ $rowCls }}">
                                <label for="f_kerja" class="{{ $labelCls }}">Pekerjaan</label>
                                <div class="sm:max-w-md">
                                    <input id="f_kerja" type="text" name="pekerjaan" x-model="f.pekerjaan"
                                        maxlength="100" class="{{ $inputCls }}" required>
                                    @error('pekerjaan') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Tombol --}}
                        <div class="flex items-center justify-end gap-3 border-t border-outline-variant/40 px-6 py-4">
                            <button type="submit"
                                class="h-10 px-6 rounded-lg bg-primary text-sm font-semibold text-white hover:bg-brand transition-colors">
                                Simpan
                            </button>
                            <button type="button" @click="open = false"
                                class="h-10 px-4 rounded-lg text-sm text-on-surface-variant hover:bg-surface-container transition-colors">
                                Close
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection