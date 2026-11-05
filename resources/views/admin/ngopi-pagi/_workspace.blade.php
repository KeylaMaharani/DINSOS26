{{-- Ruang kerja satu kasus: identitas, stepper alur, obrolan, dan panel aksi sesuai tahap. Variabel: $k --}}
@php
    $K = \App\Models\NgopiPagiKonsultasi::class;
    $urut = array_keys($K::TAHAP);
    $idx = array_search($k->tahap, $urut, true);
    $kat = $k->kategori ? $K::KATEGORI[$k->kategori] : null;
    $selesai = $k->tahap === 'selesai';
    $awal = $k->pesans->map(fn ($p) => $p->ringkas($k->konselor_id))->values();
    $aksiUrl = route('ngopi-pagi.admin.kasus.aksi', $k);
    $inp = 'w-full rounded-xl border border-outline-variant/60 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20';
    $lbl = 'block text-xs font-semibold text-on-surface-variant mb-1';
    $btn = 'inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold shadow-sm hover:bg-primary transition-colors';
@endphp

<div class="space-y-4">
    {{-- Identitas kasus --}}
    <div class="rounded-2xl bg-white border border-outline-variant/40 border-l-4 {{ $kat['edge'] ?? 'border-l-brand' }} p-4 flex flex-wrap items-center gap-4">
        <div class="w-12 h-12 shrink-0 rounded-full bg-brand text-white text-lg font-extrabold flex items-center justify-center">{{ $k->inisial() }}</div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-base font-extrabold">{{ $k->nama }}</h2>
                @if ($k->butuh_segera)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-bold"><span class="material-symbols-outlined text-[12px]">emergency</span>Butuh segera</span>
                @endif
                @if ($kat)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $kat['chip'] }}">Kategori {{ $k->kategori }} · {{ $kat['nama'] }}</span>
                @endif
                @if ($k->identitas_terverifikasi)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold"><span class="material-symbols-outlined text-[12px]">verified</span>Terverifikasi</span>
                @endif
            </div>
            <p class="text-xs text-on-surface-variant mt-0.5">{{ $k->no_konsultasi }} · {{ $k->kelurahan }}, {{ $k->kecamatan }} · masuk {{ $k->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="tel:{{ preg_replace('/[^\d+]/', '', $k->kontak) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-outline-variant/60 text-xs font-semibold hover:bg-surface-container">
                <span class="material-symbols-outlined text-[16px]">call</span>{{ $k->kontak }}
            </a>
            <a href="{{ $k->waLink() }}" target="_blank" rel="noopener" title="Hubungi lewat WhatsApp (mis. untuk memberi tahu ada balasan)" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-emerald-300 text-emerald-700 text-xs font-semibold hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[16px]">chat</span>WhatsApp
            </a>
        </div>
    </div>

    {{-- Stepper alur --}}
    <ol class="flex items-center gap-1 overflow-x-auto py-1">
        @foreach ($urut as $i => $key)
            @php $c = $K::TAHAP[$key]; $done = $i < $idx; $now = $i === $idx; @endphp
            <li class="flex items-center gap-1 shrink-0">
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold
                    {{ $now ? 'bg-brand text-white' : ($done ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-container text-on-surface-variant') }}">
                    <span class="material-symbols-outlined text-[14px]">{{ $done ? 'check_circle' : $c['icon'] }}</span>{{ $c['label'] }}
                </div>
                @unless ($loop->last)<span class="w-3 h-px bg-outline-variant"></span>@endunless
            </li>
        @endforeach
    </ol>

    <div class="grid xl:grid-cols-5 gap-4 items-start">
        {{-- ================= OBROLAN ================= --}}
        <section id="np-chat" class="xl:col-span-3 rounded-2xl bg-white border border-outline-variant/40 overflow-hidden flex flex-col h-[68vh] min-h-[440px]"
            data-poll="{{ route('ngopi-pagi.admin.kasus.pesan', $k) }}" data-kirim="{{ route('ngopi-pagi.admin.kasus.pesan.kirim', $k) }}">
            <div class="px-4 py-2.5 border-b border-outline-variant/30 flex items-center gap-2 text-xs font-semibold">
                <span class="material-symbols-outlined text-[18px] text-brand">forum</span>
                Obrolan dengan {{ $k->nama }}
                <span class="ml-auto text-[10px] font-medium text-on-surface-variant">diperbarui otomatis</span>
            </div>

            <div data-list class="flex-1 overflow-y-auto px-4 py-4 space-y-2 bg-[#f1f6fb]"></div>
            <script type="application/json" id="np-initial">{!! json_encode($awal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>

            @unless ($selesai)
                @php
                    $cepat = match ($k->tahap) {
                        'chat_masuk' => [
                            'Salam' => 'Selamat pagi, Bapak/Ibu ' . $k->nama . '. Saya admin Ngopi Pagi – LK3 Dinas Sosial Kota Bogor. Kami siap membantu, silakan ceritakan lebih lanjut.',
                            'Cek data' => 'Untuk memastikan data kami sudah benar: apakah Anda tinggal di Kelurahan ' . $k->kelurahan . ', Kecamatan ' . $k->kecamatan . ', dan nomor ' . $k->kontak . ' bisa dihubungi?',
                            'Perlu segera?' => 'Apakah permasalahan ini membutuhkan penanganan segera? Mohon jelaskan kondisi Bapak/Ibu saat ini.',
                        ],
                        'konsultasi' => [
                            'Perkenalan konselor' => 'Selamat pagi Bapak/Ibu ' . $k->nama . ', saya ' . auth()->user()->name . ' dari LK3 Dinas Sosial Kota Bogor. Mulai sekarang saya yang mendampingi Anda. Silakan ceritakan lebih lanjut kondisi yang dialami.',
                            'Gali kronologi' => 'Boleh diceritakan kronologinya dari awal: kapan mulai terjadi dan siapa saja yang terlibat?',
                            'Tanya kebutuhan' => 'Menurut Bapak/Ibu, bantuan apa yang paling dibutuhkan saat ini?',
                        ],
                        default => [],
                    };
                @endphp
                @if ($cepat)
                    <div class="px-3 pt-2 flex gap-1.5 overflow-x-auto border-t border-outline-variant/20">
                        @foreach ($cepat as $nama => $teks)
                            <button type="button" data-quick="{{ $teks }}" class="shrink-0 px-3 py-1 rounded-full border border-outline-variant/60 text-[11px] font-semibold text-brand hover:bg-brand-soft">{{ $nama }}</button>
                        @endforeach
                    </div>
                @endif
                <form class="p-3 flex items-end gap-2" autocomplete="off">
                    @csrf
                    <textarea name="isi" rows="1" maxlength="2000" placeholder="Tulis balasan… (Enter kirim, Shift+Enter baris baru)"
                        class="flex-1 resize-none max-h-32 rounded-2xl border border-outline-variant/60 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"></textarea>
                    <button type="submit" class="w-11 h-11 shrink-0 rounded-full bg-brand text-white flex items-center justify-center hover:bg-primary disabled:opacity-50" aria-label="Kirim">
                        <span class="material-symbols-outlined text-[20px]">send</span>
                    </button>
                </form>
            @else
                <div class="px-4 py-3 border-t border-outline-variant/30 bg-surface-container-low text-xs text-on-surface-variant text-center">Layanan sudah selesai. Obrolan hanya bisa dibaca.</div>
            @endunless
        </section>

        {{-- ================= PANEL TAHAP ================= --}}
        <section class="xl:col-span-2 space-y-4">
            @if ($errors->any())
                <div class="rounded-xl bg-error-container text-on-error-container text-xs px-4 py-3 space-y-0.5">
                    @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif

            <div class="rounded-2xl bg-white border border-outline-variant/40 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-[20px] text-amber-700">{{ $K::TAHAP[$k->tahap]['icon'] }}</span>
                    <div>
                        <p class="text-sm font-extrabold leading-tight">{{ $K::TAHAP[$k->tahap]['label'] }}</p>
                        <p class="text-[11px] text-on-surface-variant">Langkah {{ $K::TAHAP[$k->tahap]['langkah'] }} · Output: {{ $K::TAHAP[$k->tahap]['output'] }}</p>
                    </div>
                </div>

                @switch($k->tahap)
                    {{-- ---------- 1-3: verifikasi ---------- --}}
                    @case('chat_masuk')
                        <form method="POST" action="{{ $aksiUrl }}" class="space-y-3">
                            @csrf <input type="hidden" name="aksi" value="verifikasi">
                            <div><label class="{{ $lbl }}">Nama / inisial</label><input name="nama" value="{{ old('nama', $k->nama) }}" class="{{ $inp }}" required></div>
                            <div><label class="{{ $lbl }}">Nomor yang dapat dihubungi</label><input name="kontak" value="{{ old('kontak', $k->kontak) }}" class="{{ $inp }}" required></div>
                            <div class="grid grid-cols-2 gap-2">
                                <div><label class="{{ $lbl }}">Kecamatan</label>
                                    <select name="kecamatan" class="{{ $inp }}" required>
                                        @foreach ($K::KECAMATAN as $kc)<option @selected(old('kecamatan', $k->kecamatan) === $kc)>{{ $kc }}</option>@endforeach
                                    </select></div>
                                <div><label class="{{ $lbl }}">Kelurahan</label><input name="kelurahan" value="{{ old('kelurahan', $k->kelurahan) }}" class="{{ $inp }}" required></div>
                            </div>
                            <div class="rounded-xl bg-surface-container-low px-3 py-2.5">
                                <p class="text-[11px] font-semibold text-on-surface-variant mb-0.5">Kebutuhan awal (dari pengguna)</p>
                                <p class="text-[13px] whitespace-pre-line">{{ $k->kebutuhan }}</p>
                            </div>
                            <div><label class="{{ $lbl }}">Catatan verifikasi (opsional)</label><textarea name="verifikasi_catatan" rows="2" class="{{ $inp }}">{{ old('verifikasi_catatan', $k->verifikasi_catatan) }}</textarea></div>
                            <label class="flex items-start gap-2 text-xs">
                                <input type="checkbox" name="identitas_terverifikasi" value="1" class="mt-0.5 rounded border-outline-variant text-brand focus:ring-brand" @checked(old('identitas_terverifikasi'))>
                                <span>Identitas dan kebutuhan awal sudah saya verifikasi bersama pengguna.</span>
                            </label>
                            <button class="{{ $btn }} w-full">Verifikasi &amp; teruskan ke Screening</button>
                        </form>
                        @break

                    {{-- ---------- 4: screening ---------- --}}
                    @case('screening')
                        <form method="POST" action="{{ $aksiUrl }}" class="space-y-3">
                            @csrf <input type="hidden" name="aksi" value="screening">
                            <div class="space-y-2">
                                @foreach ($K::KATEGORI as $kk => $kv)
                                    <label class="block cursor-pointer">
                                        <input type="radio" name="kategori" value="{{ $kk }}" class="peer sr-only" @checked(old('kategori', $k->kategori) === $kk)>
                                        <div class="rounded-xl border-2 border-outline-variant/50 p-3 peer-checked:border-brand peer-checked:bg-brand-soft peer-focus-visible:ring-2 peer-focus-visible:ring-brand/40">
                                            <div class="flex items-center gap-2">
                                                <span class="w-7 h-7 rounded-lg font-extrabold text-sm flex items-center justify-center {{ $kv['chip'] }}">{{ $kk }}</span>
                                                <b class="text-sm">{{ $kv['nama'] }}</b>
                                            </div>
                                            <p class="text-[11px] text-on-surface-variant mt-1.5">Contoh: {{ $kv['contoh'] }}.</p>
                                            <p class="text-[11px] font-semibold text-on-surface mt-1">Tindakan: {{ $kv['tindakan'] }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div><label class="{{ $lbl }}">Jenis masalah</label>
                                <select name="jenis_masalah" class="{{ $inp }}">
                                    <option value="">— pilih —</option>
                                    @foreach ($K::JENIS_MASALAH as $jk => $jl)<option value="{{ $jk }}" @selected(old('jenis_masalah', $k->jenis_masalah) === $jk)>{{ $jl }}</option>@endforeach
                                </select></div>
                            <div><label class="{{ $lbl }}">Catatan klasifikasi (opsional)</label><textarea name="klasifikasi_catatan" rows="2" class="{{ $inp }}">{{ old('klasifikasi_catatan', $k->klasifikasi_catatan) }}</textarea></div>
                            <p class="text-[11px] text-on-surface-variant">Kategori A langsung ke Tindak Lanjut. Kategori B dan C diteruskan ke Petugas LK3; Kategori C juga mengirim notifikasi prioritas.</p>
                            <button class="{{ $btn }} w-full">Simpan klasifikasi &amp; teruskan</button>
                        </form>
                        @break

                    {{-- ---------- 5: asesmen LK3 ---------- --}}
                    @case('konsultasi')
                        @if (! $k->konselor_id)
                            <form method="POST" action="{{ $aksiUrl }}" class="mb-4 rounded-xl bg-amber-50 border border-amber-200 p-3">
                                @csrf <input type="hidden" name="aksi" value="ambil">
                                <p class="text-xs text-amber-900 mb-2">Kasus ini belum punya konselor. Setelah diambil, pengguna melihat nama Anda sebagai pendamping di obrolan mereka.</p>
                                <button class="{{ $btn }} w-full">Ambil kasus ini sebagai konselor</button>
                            </form>
                        @else
                            <p class="mb-3 text-xs rounded-xl bg-emerald-50 text-emerald-800 px-3 py-2 flex items-center gap-2"><span class="material-symbols-outlined text-[16px]">support_agent</span>Didampingi: <b>{{ $k->konselor->name }}</b></p>
                        @endif
                        <form method="POST" action="{{ $aksiUrl }}" class="space-y-3">
                            @csrf <input type="hidden" name="aksi" value="asesmen">
                            <div><label class="{{ $lbl }}">Kondisi saat ini</label><textarea name="kondisi" rows="2" class="{{ $inp }}">{{ old('kondisi', $k->kondisi) }}</textarea></div>
                            <div><label class="{{ $lbl }}">Kronologi</label><textarea name="kronologi" rows="3" class="{{ $inp }}">{{ old('kronologi', $k->kronologi) }}</textarea></div>
                            <div><label class="{{ $lbl }}">Kebutuhan</label><textarea name="kebutuhan_asesmen" rows="2" class="{{ $inp }}">{{ old('kebutuhan_asesmen', $k->kebutuhan_asesmen) }}</textarea></div>
                            <div><label class="{{ $lbl }}">Tingkat urgensi</label>
                                <select name="tingkat_urgensi" class="{{ $inp }}">
                                    <option value="">— pilih —</option>
                                    @foreach ($K::URGENSI as $uk => $ul)<option value="{{ $uk }}" @selected(old('tingkat_urgensi', $k->tingkat_urgensi) === $uk)>{{ $ul }}</option>@endforeach
                                </select></div>
                            <div><label class="{{ $lbl }}">Hasil asesmen awal</label><textarea name="hasil_asesmen" rows="3" class="{{ $inp }}">{{ old('hasil_asesmen', $k->hasil_asesmen) }}</textarea></div>
                            <button class="{{ $btn }} w-full">Simpan asesmen &amp; lanjut ke Tindak Lanjut</button>
                        </form>
                        @break

                    {{-- ---------- 6-7: tindak lanjut ---------- --}}
                    @case('tindak_lanjut')
                        <form method="POST" action="{{ $aksiUrl }}" class="space-y-3" x-data="{ jenis: @js(old('jenis_tindak_lanjut', $k->jenis_tindak_lanjut ?? ($k->kategori === 'A' ? 'informasi' : 'konseling'))) }">
                            @csrf <input type="hidden" name="aksi" value="tindak_lanjut">
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($K::TINDAK_LANJUT as $tk => $tv)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_tindak_lanjut" value="{{ $tk }}" x-model="jenis" class="peer sr-only">
                                        <div class="h-full rounded-xl border-2 border-outline-variant/50 p-2.5 text-center peer-checked:border-brand peer-checked:bg-brand-soft peer-focus-visible:ring-2 peer-focus-visible:ring-brand/40">
                                            <span class="material-symbols-outlined text-[22px] text-brand">{{ $tv['icon'] }}</span>
                                            <p class="text-xs font-bold">{{ $tv['label'] }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @foreach ($K::TINDAK_LANJUT as $tk => $tv)
                                <p x-show="jenis === '{{ $tk }}'" x-cloak class="text-[11px] text-on-surface-variant">{{ $tv['desc'] }}</p>
                            @endforeach
                            <div><label class="{{ $lbl }}">Rencana intervensi</label><textarea name="rencana_intervensi" rows="3" class="{{ $inp }}">{{ old('rencana_intervensi', $k->rencana_intervensi) }}</textarea></div>
                            <div x-show="jenis === 'rujukan'" x-cloak><label class="{{ $lbl }}">Tujuan rujukan</label>
                                <input name="tujuan_rujukan" value="{{ old('tujuan_rujukan', $k->tujuan_rujukan) }}" placeholder="mis. Puskesmas Tanah Sareal, P2TP2A, UPT..." class="{{ $inp }}"></div>
                            <div><label class="{{ $lbl }}">Output layanan (opsional)</label>
                                <input name="output_layanan" value="{{ old('output_layanan', $k->output_layanan) }}" placeholder="Kosongkan untuk memakai output standar" class="{{ $inp }}"></div>
                            <button class="{{ $btn }} w-full">Simpan rencana &amp; mulai Monitoring</button>
                        </form>
                        @break

                    {{-- ---------- 8: monitoring ---------- --}}
                    @case('monitoring')
                        <form method="POST" action="{{ $aksiUrl }}" class="space-y-3">
                            @csrf <input type="hidden" name="aksi" value="monitor">
                            <div><label class="{{ $lbl }}">Status perkembangan</label>
                                <select name="status_perkembangan" class="{{ $inp }}">
                                    @foreach ($K::STATUS_PERKEMBANGAN as $sk => $sl)<option value="{{ $sk }}" @selected(old('status_perkembangan') === $sk)>{{ $sl }}</option>@endforeach
                                </select></div>
                            <div><label class="{{ $lbl }}">Catatan pemantauan</label><textarea name="catatan" rows="3" class="{{ $inp }}">{{ old('catatan') }}</textarea></div>
                            <button class="{{ $btn }} w-full">Tambah catatan</button>
                        </form>
                        <form method="POST" action="{{ $aksiUrl }}" class="mt-3 pt-3 border-t border-outline-variant/30">
                            @csrf <input type="hidden" name="aksi" value="ke_konfirmasi">
                            <button class="w-full px-4 py-2.5 rounded-xl border-2 border-brand text-brand text-sm font-semibold hover:bg-brand-soft"
                                data-confirm="Kirim permintaan konfirmasi ke pengguna? Kasus berpindah ke tahap Konfirmasi & Penutupan.">Minta konfirmasi pengguna</button>
                        </form>
                        @break

                    {{-- ---------- 9-10: konfirmasi & penutupan ---------- --}}
                    @case('konfirmasi')
                        @if ($k->dikonfirmasi_at)
                            <div class="rounded-xl bg-emerald-50 text-emerald-800 text-xs px-3 py-2.5 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                Pengguna mengonfirmasi kebutuhan terpenuhi pada {{ $k->dikonfirmasi_at->format('d/m/Y H:i') }}.
                            </div>
                        @else
                            <div class="rounded-xl bg-amber-50 text-amber-800 text-xs px-3 py-2.5 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">hourglass_top</span>
                                Menunggu jawaban pengguna di ruang obrolan mereka.
                            </div>
                        @endif
                        <form method="POST" action="{{ $aksiUrl }}" class="space-y-3">
                            @csrf <input type="hidden" name="aksi" value="tutup">
                            <div><label class="{{ $lbl }}">Catatan penutupan (opsional)</label><textarea name="catatan_penutupan" rows="2" class="{{ $inp }}">{{ old('catatan_penutupan') }}</textarea></div>
                            @unless ($k->dikonfirmasi_at)
                                <label class="flex items-start gap-2 text-xs">
                                    <input type="checkbox" name="tanpa_konfirmasi" value="1" class="mt-0.5 rounded border-outline-variant text-brand focus:ring-brand" @checked(old('tanpa_konfirmasi'))>
                                    <span>Tutup tanpa konfirmasi pengguna</span>
                                </label>
                            @endunless
                            <button class="{{ $btn }} w-full" data-confirm="Nyatakan layanan selesai? Obrolan akan dikunci dan kasus masuk Rekapitulasi.">Nyatakan layanan selesai</button>
                        </form>
                        @break

                    @default
                        <div class="rounded-xl bg-emerald-50 text-emerald-800 text-xs px-3 py-2.5 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">task_alt</span>
                            Selesai {{ $k->closed_at?->format('d/m/Y H:i') }}@if ($k->catatan_penutupan) — {{ $k->catatan_penutupan }}@endif
                        </div>
                @endswitch

                @if (! in_array($k->tahap, ['chat_masuk', 'selesai'], true))
                    <form method="POST" action="{{ $aksiUrl }}" class="mt-3 text-right">
                        @csrf <input type="hidden" name="aksi" value="mundur">
                        <button class="text-[11px] font-semibold text-on-surface-variant hover:text-error" data-confirm="Kembalikan kasus ke tahap sebelumnya?">↩ Kembalikan ke tahap sebelumnya</button>
                    </form>
                @endif
            </div>

            {{-- Ringkasan yang sudah terisi --}}
            @php
                $ringkas = array_filter([
                    'Kebutuhan awal' => $k->kebutuhan,
                    'Jenis masalah' => $K::JENIS_MASALAH[$k->jenis_masalah] ?? null,
                    'Catatan klasifikasi' => $k->klasifikasi_catatan,
                    'Kondisi' => $k->kondisi, 'Kronologi' => $k->kronologi, 'Kebutuhan (asesmen)' => $k->kebutuhan_asesmen,
                    'Urgensi' => $K::URGENSI[$k->tingkat_urgensi] ?? null, 'Hasil asesmen' => $k->hasil_asesmen,
                    'Tindak lanjut' => $K::TINDAK_LANJUT[$k->jenis_tindak_lanjut]['label'] ?? null,
                    'Tujuan rujukan' => $k->tujuan_rujukan, 'Rencana intervensi' => $k->rencana_intervensi, 'Output layanan' => $k->output_layanan,
                ]);
            @endphp
            <details class="rounded-2xl bg-white border border-outline-variant/40" @if ($selesai) open @endif>
                <summary class="px-4 py-3 text-sm font-bold cursor-pointer flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-brand">description</span>Ringkasan kasus</summary>
                <dl class="px-4 pb-4 space-y-2.5">
                    @foreach ($ringkas as $label => $nilai)
                        <div><dt class="text-[11px] font-semibold text-on-surface-variant">{{ $label }}</dt><dd class="text-[13px] whitespace-pre-line">{{ $nilai }}</dd></div>
                    @endforeach
                </dl>
            </details>

            {{-- Catatan monitoring --}}
            @if ($k->monitorings->isNotEmpty())
                <details class="rounded-2xl bg-white border border-outline-variant/40" open>
                    <summary class="px-4 py-3 text-sm font-bold cursor-pointer flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-brand">monitor_heart</span>Catatan monitoring ({{ $k->monitorings->count() }})</summary>
                    <ul class="px-4 pb-4 space-y-3">
                        @foreach ($k->monitorings as $m)
                            <li class="text-[13px]">
                                <span class="inline-block px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">{{ $K::STATUS_PERKEMBANGAN[$m->status_perkembangan] ?? $m->status_perkembangan }}</span>
                                <p class="mt-1 whitespace-pre-line">{{ $m->catatan }}</p>
                                <p class="text-[10px] text-on-surface-variant">{{ $m->user->name ?? '-' }} · {{ $m->created_at->format('d/m/Y H:i') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif

            {{-- Riwayat proses --}}
            <details class="rounded-2xl bg-white border border-outline-variant/40">
                <summary class="px-4 py-3 text-sm font-bold cursor-pointer flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-brand">history</span>Riwayat proses</summary>
                <ol class="px-4 pb-4 ml-2 border-l border-outline-variant/50 space-y-3">
                    @foreach ($k->logs as $lg)
                        <li class="pl-4 relative">
                            <span class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-brand"></span>
                            <p class="text-xs font-semibold">{{ str_replace('_', ' ', ucfirst($lg->aksi)) }}@if ($lg->ke_tahap && $lg->ke_tahap !== $lg->dari_tahap) → {{ $K::TAHAP[$lg->ke_tahap]['label'] ?? $lg->ke_tahap }}@endif</p>
                            @if ($lg->catatan)<p class="text-[11px] text-on-surface-variant whitespace-pre-line">{{ \Illuminate\Support\Str::limit($lg->catatan, 160) }}</p>@endif
                            <p class="text-[10px] text-on-surface-variant">{{ $lg->user->name ?? 'Pengguna/Sistem' }} · {{ $lg->created_at->format('d/m/Y H:i') }}</p>
                        </li>
                    @endforeach
                </ol>
            </details>
        </section>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var box = document.getElementById('np-chat');
    if (!box) return;
    var list = box.querySelector('[data-list]');
    var form = box.querySelector('form');
    var seen = new Set(), last = 0;
    var AKU = 'petugas'; // sisi admin: pesan petugas di kanan

    function bubble(p, paksaScroll) {
        if (seen.has(p.id)) return;
        seen.add(p.id); last = Math.max(last, p.id);
        var dekat = list.scrollHeight - list.scrollTop - list.clientHeight < 120;
        var row = document.createElement('div'), b = document.createElement('div'), t = document.createElement('div');
        b.className = 'whitespace-pre-wrap break-words text-[13px] leading-relaxed';
        b.textContent = p.isi;
        t.className = 'text-[10px] mt-1 opacity-70 text-right';
        t.textContent = p.waktu;
        if (p.pengirim === 'sistem') {
            row.className = 'flex justify-center';
            var s = document.createElement('div');
            s.className = 'max-w-[90%] rounded-xl bg-white/80 border border-outline-variant/40 text-on-surface-variant px-3 py-2';
            b.className += ' text-[12px]'; s.appendChild(b); s.appendChild(t); row.appendChild(s);
        } else {
            var mine = p.pengirim === AKU;
            row.className = 'flex ' + (mine ? 'justify-end' : 'justify-start');
            var w = document.createElement('div');
            w.className = 'max-w-[82%] px-3.5 py-2 shadow-sm ' + (mine ? 'bg-brand text-white rounded-2xl rounded-br-md' : 'bg-white border border-outline-variant/40 rounded-2xl rounded-bl-md');
            w.appendChild(b); w.appendChild(t); row.appendChild(w);
        }
        list.appendChild(row);
        if (dekat || paksaScroll) list.scrollTop = list.scrollHeight;
    }

    JSON.parse(document.getElementById('np-initial').textContent).forEach(function (p) { bubble(p, true); });

    function poll() {
        if (document.hidden) return;
        fetch(box.dataset.poll + '?after=' + last, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) { if (d) d.pesan.forEach(function (p) { bubble(p); }); })
            .catch(function () {});
    }
    setInterval(poll, 4000);

    if (!form) return;
    var ta = form.querySelector('textarea'), btn = form.querySelector('button[type=submit]');
    box.querySelectorAll('[data-quick]').forEach(function (q) {
        q.addEventListener('click', function () { ta.value = q.dataset.quick; ta.focus(); });
    });
    ta.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var isi = ta.value.trim(); if (!isi) return;
        btn.disabled = true;
        fetch(box.dataset.kirim, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value, 'Content-Type': 'application/json' },
            body: JSON.stringify({ isi: isi })
        }).then(function (r) { return r.json(); })
          .then(function (d) { if (d.pesan) { bubble(d.pesan, true); ta.value = ''; } else if (d.message) { alert(d.message); } })
          .catch(function () { alert('Pesan gagal terkirim. Coba lagi.'); })
          .finally(function () { btn.disabled = false; ta.focus(); });
    });
})();
</script>
@endpush
