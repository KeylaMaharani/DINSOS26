@extends('layouts.admin')

@section('title', 'API Desil')
@section('page_title', 'API Desil')
@section('tanpa_alert_global', '1')

@section('content')
    @php
        $tabs = ['klien' => 'Klien', 'log' => 'Log Akses', 'uji' => 'Uji Coba'];
        $clientsJs = $clients->map(fn ($c) => [
            'id' => $c->id,
            'nama' => $c->nama,
            'scopes' => $c->scopes ?? [],
            'kecamatan_text' => implode("\n", $c->kecamatan ?? []),
            'ips_text' => implode("\n", $c->allowed_ips ?? []),
            'per_page_max' => $c->per_page_max,
            'rate_per_menit' => $c->rate_per_menit,
            'kuota_harian' => $c->kuota_harian,
            'expires_at' => $c->expires_at?->format('Y-m-d'),
        ])->values();
    @endphp

    <div x-data="apiKlien()">

        {{-- ===== Notifikasi ===== --}}
        @if (session('success'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-success-container text-on-success-container text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg bg-error-container text-on-error-container text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Key baru: tampil SEKALI saja --}}
        @if (session('api_key_baru'))
            @php $kb = session('api_key_baru'); @endphp
            <div class="mb-4 p-4 rounded-xl border-2 border-warning bg-warning-container text-on-warning-container"
                x-data="{ copied: false }">
                <p class="text-sm font-bold">API key untuk {{ $kb['nama'] }}</p>
                <p class="text-xs mb-2">Salin sekarang. Key ini tidak akan ditampilkan lagi (di database hanya tersimpan hash-nya).</p>
                <div class="flex flex-wrap items-center gap-2">
                    <code class="px-3 py-2 rounded-lg bg-white text-sm font-mono break-all select-all">{{ $kb['key'] }}</code>
                    <button type="button"
                        @click="navigator.clipboard.writeText('{{ $kb['key'] }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="px-3 py-2 rounded-lg bg-primary text-on-primary text-xs font-medium">
                        <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
                    </button>
                </div>
            </div>
        @endif

        {{-- ===== Tab ===== --}}
        <div class="flex gap-1 mb-4 border-b border-outline-variant/40">
            @foreach ($tabs as $k => $label)
                <a href="{{ route('api-klien.index', ['tab' => $k]) }}"
                    class="px-4 py-2 text-sm font-semibold -mb-px border-b-2 {{ $tab === $k ? 'border-brand text-brand' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- =========================================================
             TAB: KLIEN
             ========================================================= --}}
        @if ($tab === 'klien')
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-on-surface-variant">Satu baris = satu instansi = satu API key.</p>
                <button type="button" @click="baru()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-medium hover:bg-secondary">
                    <span class="material-symbols-outlined text-[18px]">add</span> Tambah Klien
                </button>
            </div>

            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-container-low text-left text-xs uppercase tracking-wide text-on-surface-variant">
                        <tr>
                            <th class="px-4 py-3">Instansi</th>
                            <th class="px-4 py-3">Hak akses</th>
                            <th class="px-4 py-3">Wilayah / IP</th>
                            <th class="px-4 py-3">Batas</th>
                            <th class="px-4 py-3">Masa berlaku</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        @forelse ($clients as $c)
                            @php
                                $dicabut = $c->revoked_at !== null;
                                $kedaluwarsa = ! $dicabut && $c->expires_at && $c->expires_at->isPast();
                                $terpakai = (int) ($pakaiHariIni[$c->id] ?? 0);
                            @endphp
                            <tr class="align-top">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $c->nama }}</p>
                                    <p class="text-[11px] text-on-surface-variant">
                                        Terakhir dipakai:
                                        {{ $c->last_used_at ? $c->last_used_at->diffForHumans() : 'belum pernah' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1 max-w-[16rem]">
                                        @foreach ($c->scopes ?? [] as $s)
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $s === 'detail' ? 'bg-error-container text-on-error-container' : 'bg-secondary-fixed text-on-secondary-container' }}">{{ $s }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs space-y-1">
                                    <p><span class="text-on-surface-variant">Wilayah:</span>
                                        {{ $c->kecamatan ? implode(', ', $c->kecamatan) : 'Semua' }}</p>
                                    <p><span class="text-on-surface-variant">IP:</span>
                                        {{ $c->allowed_ips ? implode(', ', $c->allowed_ips) : 'Bebas (tidak dibatasi)' }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs space-y-1">
                                    <p>{{ $c->rate_per_menit }} req/menit</p>
                                    <p>Hari ini: <b>{{ number_format($terpakai) }}</b> / {{ number_format($c->kuota_harian) }}</p>
                                    <p>Maks. {{ $c->per_page_max }} baris/halaman</p>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $c->expires_at ? $c->expires_at->translatedFormat('d M Y') : 'Tanpa batas' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($dicabut)
                                        <span class="px-2 py-1 rounded-full text-[11px] font-bold bg-error-container text-on-error-container">Dicabut</span>
                                    @elseif ($kedaluwarsa)
                                        <span class="px-2 py-1 rounded-full text-[11px] font-bold bg-warning-container text-on-warning-container">Kedaluwarsa</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-[11px] font-bold bg-success-container text-on-success-container">Aktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        <button type="button" @click="ubah({{ $c->id }})"
                                            class="px-2.5 py-1.5 rounded-lg border border-outline-variant/60 text-xs font-medium hover:bg-surface-container">Edit</button>

                                        <form method="POST" action="{{ route('api-klien.key', $c) }}">
                                            @csrf
                                            <button type="submit"
                                                data-confirm="Buat key baru untuk {{ $c->nama }}? Key lama langsung tidak berlaku dan klien harus memakai key baru."
                                                data-confirm-variant="error"
                                                class="px-2.5 py-1.5 rounded-lg border border-outline-variant/60 text-xs font-medium hover:bg-surface-container">Key baru</button>
                                        </form>

                                        <form method="POST" action="{{ route('api-klien.toggle', $c) }}">
                                            @csrf
                                            <button type="submit"
                                                data-confirm="{{ $dicabut ? 'Aktifkan kembali akses ' : 'Cabut akses ' }}{{ $c->nama }}?"
                                                data-confirm-variant="{{ $dicabut ? 'primary' : 'error' }}"
                                                class="px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $dicabut ? 'bg-success text-white' : 'bg-error text-on-error' }}">
                                                {{ $dicabut ? 'Aktifkan' : 'Cabut' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('api-klien.destroy', $c) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                data-confirm="Hapus klien {{ $c->nama }} permanen? Riwayat log tetap tersimpan."
                                                data-confirm-variant="error"
                                                class="px-2.5 py-1.5 rounded-lg border border-error/50 text-error text-xs font-medium hover:bg-error-container/40">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-on-surface-variant">Belum ada klien API.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- =========================================================
             TAB: LOG AKSES
             ========================================================= --}}
        @if ($tab === 'log')
            @if ($ringkasan)
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                    @foreach ([['Request 24 jam', $ringkasan->total, 'text-brand'], ['Berhasil', $ringkasan->berhasil, 'text-success'], ['Ditolak', $ringkasan->ditolak, 'text-error'], ['Kena rate limit (429)', $ringkasan->dibatasi, 'text-warning']] as [$lbl, $val, $warna])
                        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-wide text-on-surface-variant">{{ $lbl }}</p>
                            <p class="text-2xl font-extrabold {{ $warna }}">{{ number_format($val) }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="GET" class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-3 mb-3 grid grid-cols-2 lg:grid-cols-7 gap-2 items-end">
                <input type="hidden" name="tab" value="log">
                <label class="text-xs font-medium">Klien
                    <select name="klien" class="mt-1 w-full rounded-lg border border-outline-variant/50 px-2 py-1.5 text-sm">
                        <option value="">Semua</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}" @selected(request('klien') == $c->id)>{{ $c->nama }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium">Kejadian
                    <select name="event" class="mt-1 w-full rounded-lg border border-outline-variant/50 px-2 py-1.5 text-sm">
                        <option value="">Semua</option>
                        @foreach (['akses' => 'Akses', 'auth_gagal' => 'Key salah', 'ip_ditolak' => 'IP ditolak', 'scope_ditolak' => 'Scope ditolak'] as $v => $l)
                            <option value="{{ $v }}" @selected(request('event') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium">Status
                    <select name="status" class="mt-1 w-full rounded-lg border border-outline-variant/50 px-2 py-1.5 text-sm">
                        <option value="">Semua</option>
                        <option value="2" @selected(request('status') === '2')>2xx</option>
                        <option value="4" @selected(request('status') === '4')>4xx</option>
                        <option value="5" @selected(request('status') === '5')>5xx</option>
                    </select>
                </label>
                <label class="text-xs font-medium">IP
                    <input type="text" name="ip" value="{{ request('ip') }}" placeholder="192.168."
                        class="mt-1 w-full rounded-lg border border-outline-variant/50 px-2 py-1.5 text-sm">
                </label>
                <label class="text-xs font-medium">Dari
                    <input type="date" name="dari" value="{{ request('dari') }}"
                        class="mt-1 w-full rounded-lg border border-outline-variant/50 px-2 py-1.5 text-sm">
                </label>
                <label class="text-xs font-medium">Sampai
                    <input type="date" name="sampai" value="{{ request('sampai') }}"
                        class="mt-1 w-full rounded-lg border border-outline-variant/50 px-2 py-1.5 text-sm">
                </label>
                <div class="flex gap-2">
                    <button class="px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-medium">Filter</button>
                    <a href="{{ route('api-klien.index', ['tab' => 'log']) }}"
                        class="px-3 py-2 rounded-lg border border-outline-variant/60 text-sm">Reset</a>
                </div>
            </form>

            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-surface-container-low text-left uppercase tracking-wide text-on-surface-variant">
                        <tr>
                            <th class="px-3 py-2.5">Waktu</th>
                            <th class="px-3 py-2.5">Klien</th>
                            <th class="px-3 py-2.5">Kejadian</th>
                            <th class="px-3 py-2.5">Request</th>
                            <th class="px-3 py-2.5">Parameter</th>
                            <th class="px-3 py-2.5">IP</th>
                            <th class="px-3 py-2.5">Status</th>
                            <th class="px-3 py-2.5">Req ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        @forelse ($logs as $l)
                            @php
                                $ev = [
                                    'akses' => ['Akses', 'bg-secondary-fixed text-on-secondary-container'],
                                    'auth_gagal' => ['Key salah', 'bg-error-container text-on-error-container'],
                                    'ip_ditolak' => ['IP ditolak', 'bg-warning-container text-on-warning-container'],
                                    'scope_ditolak' => ['Scope ditolak', 'bg-warning-container text-on-warning-container'],
                                ][$l->event] ?? [$l->event, 'bg-surface-container'];
                                $warnaStatus = $l->status < 300 ? 'text-success' : ($l->status < 500 ? 'text-error' : 'text-warning');
                            @endphp
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $l->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-3 py-2">{{ $l->klien ?? '—' }}</td>
                                <td class="px-3 py-2"><span class="px-2 py-0.5 rounded-full font-semibold {{ $ev[1] }}">{{ $ev[0] }}</span></td>
                                <td class="px-3 py-2 font-mono"><b>{{ $l->method }}</b> /{{ $l->path }}</td>
                                <td class="px-3 py-2 text-on-surface-variant">{{ $l->query_keys ? implode(', ', $l->query_keys) : '—' }}</td>
                                <td class="px-3 py-2 font-mono">{{ $l->ip }}</td>
                                <td class="px-3 py-2 font-bold {{ $warnaStatus }}">{{ $l->status }}</td>
                                <td class="px-3 py-2 font-mono text-on-surface-variant" title="{{ $l->req_id }}">{{ Str::limit($l->req_id, 8, '') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-on-surface-variant">Belum ada log yang cocok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $logs->links() }}</div>
            <p class="mt-2 text-[11px] text-on-surface-variant">Nilai parameter (nama, NIK) tidak dicatat, hanya nama parameternya. NIK/No. KK di path disamarkan. Log dihapus otomatis setelah 90 hari.</p>
        @endif

        {{-- =========================================================
             TAB: UJI COBA
             ========================================================= --}}
        @if ($tab === 'uji')
            <div class="grid lg:grid-cols-5 gap-4">
                <div class="lg:col-span-2 bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-4 space-y-3">
                    <h3 class="text-sm font-bold">Kirim request uji</h3>
                    <p class="text-xs text-on-surface-variant">Request dikirim dari browser Anda ke API yang sebenarnya, jadi ikut tercatat di Log Akses dan memakai kuota klien. IP yang terbaca adalah IP komputer Anda.</p>

                    <label class="block text-xs font-medium">API key
                        <input type="password" x-model="uji.key" autocomplete="off" placeholder="dsl_..."
                            class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm font-mono">
                    </label>

                    <label class="block text-xs font-medium">Endpoint
                        <select x-model="uji.endpoint" class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">
                            <option value="index">GET /desil (daftar / cari)</option>
                            <option value="nik">GET /desil/nik/{nik}</option>
                            <option value="kk">GET /desil/kk/{noKk}</option>
                            <option value="statistik">GET /desil/statistik</option>
                            <option value="wilayah">GET /desil/wilayah</option>
                        </select>
                    </label>

                    <label class="block text-xs font-medium" x-show="['nik','kk'].includes(uji.endpoint)" x-cloak>
                        <span x-text="uji.endpoint === 'nik' ? 'NIK (16 digit)' : 'No. KK (16 digit)'"></span>
                        <input type="text" x-model="uji.nilai" maxlength="16" inputmode="numeric"
                            class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm font-mono">
                    </label>

                    <label class="block text-xs font-medium" x-show="uji.endpoint === 'index'" x-cloak>
                        Query string
                        <input type="text" x-model="uji.query" placeholder="q=budi&per_page=5"
                            class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm font-mono">
                    </label>

                    <button type="button" @click="kirim()" :disabled="uji.loading || !uji.key"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-sm font-medium disabled:opacity-50">
                        <span class="material-symbols-outlined text-[18px]" :class="uji.loading ? 'animate-spin' : ''"
                            x-text="uji.loading ? 'progress_activity' : 'send'"></span>
                        <span x-text="uji.loading ? 'Mengirim...' : 'Kirim'"></span>
                    </button>

                    {{-- Riwayat uji (hanya di halaman ini, hilang saat di-refresh) --}}
                    <div x-show="riwayat.length" x-cloak>
                        <p class="text-xs font-bold mt-2 mb-1">Riwayat uji sesi ini</p>
                        <table class="w-full text-xs">
                            <template x-for="r in riwayat" :key="r.id">
                                <tr class="border-t border-outline-variant/30">
                                    <td class="py-1 text-on-surface-variant" x-text="r.waktu"></td>
                                    <td class="py-1 font-mono" x-text="r.ep"></td>
                                    <td class="py-1 font-bold" :class="r.status >= 200 && r.status < 300 ? 'text-success' : 'text-error'" x-text="r.status || 'ERR'"></td>
                                    <td class="py-1 text-right text-on-surface-variant" x-text="r.ms + ' ms'"></td>
                                </tr>
                            </template>
                        </table>
                    </div>
                </div>

                <div class="lg:col-span-3 space-y-4">
                    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-bold">Hasil</h3>
                            <template x-if="hasil">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="px-2 py-1 rounded-full font-bold"
                                        :class="hasil.ok ? 'bg-success-container text-on-success-container' : 'bg-error-container text-on-error-container'"
                                        x-text="hasil.status || 'Gagal'"></span>
                                    <span class="text-on-surface-variant" x-text="hasil.ms + ' ms'"></span>
                                    <span x-show="hasil.retry" class="text-warning font-semibold" x-text="'Retry-After: ' + hasil.retry + 's'"></span>
                                </div>
                            </template>
                        </div>
                        <p class="text-[11px] font-mono text-on-surface-variant break-all mb-2" x-show="hasil" x-text="hasil ? 'GET ' + hasil.url : ''"></p>
                        <pre class="text-xs bg-surface-container-low rounded-lg p-3 overflow-auto max-h-[28rem] whitespace-pre-wrap"
                            x-text="hasil ? hasil.body : 'Belum ada request.'"></pre>
                    </div>

                    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-4">
                        <h3 class="text-sm font-bold mb-2">Skenario uji sebelum diserahkan ke instansi</h3>
                        <table class="w-full text-xs">
                            <thead class="text-left text-on-surface-variant">
                                <tr><th class="py-1">Skenario</th><th class="py-1">Harapan</th></tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/30">
                                <tr><td class="py-1.5">Key kosong / salah</td><td><b>401</b>, tercatat "Key salah"</td></tr>
                                <tr><td class="py-1.5">10x key salah dalam 15 menit</td><td><b>429</b> + header Retry-After</td></tr>
                                <tr><td class="py-1.5">Endpoint di luar hak akses klien</td><td><b>403</b>, tercatat "Scope ditolak"</td></tr>
                                <tr><td class="py-1.5">Dari IP yang tidak ada di allowlist</td><td><b>403</b>, tercatat "IP ditolak"</td></tr>
                                <tr><td class="py-1.5">GET /desil tanpa q / kecamatan / kelurahan</td><td><b>422</b></td></tr>
                                <tr><td class="py-1.5">per_page=100 (batas klien 25)</td><td>Hanya 25 baris dikembalikan</td></tr>
                                <tr><td class="py-1.5">NIK di luar wilayah klien</td><td><b>404</b> (bukan 403)</td></tr>
                                <tr><td class="py-1.5">Klien tanpa scope detail</td><td>Tidak ada alamat, disabilitas, stunting</td></tr>
                                <tr><td class="py-1.5">Setelah klien dicabut</td><td><b>401</b> langsung</td></tr>
                                <tr><td class="py-1.5">Request normal</td><td><b>200</b>, tercatat "Akses"</td></tr>
                            </tbody>
                        </table>
                        <a href="{{ route('api-klien.index', ['tab' => 'log']) }}" class="inline-block mt-3 text-xs font-semibold text-brand hover:underline">Lihat hasilnya di Log Akses →</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- =========================================================
             MODAL TAMBAH / EDIT KLIEN
             ========================================================= --}}
        <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div @click.outside="modal = false" class="bg-surface-container-lowest w-full max-w-2xl max-h-[92vh] overflow-y-auto rounded-xl shadow-lg">
                <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                    <h3 class="text-base font-semibold" x-text="form.id ? 'Edit Klien' : 'Tambah Klien'"></h3>
                    <button type="button" @click="modal = false" class="text-on-surface-variant">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form method="POST" :action="action" class="p-5 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" :disabled="!form.id">

                    <label class="block text-sm font-medium">Nama instansi
                        <input type="text" name="nama" x-model="form.nama" required maxlength="255"
                            class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">
                    </label>

                    <div>
                        <p class="text-sm font-medium mb-1">Hak akses endpoint</p>
                        <div class="grid sm:grid-cols-2 gap-1.5">
                            @foreach ($scopes as $key => $label)
                                <label class="flex items-start gap-2 text-xs p-2 rounded-lg border {{ $key === 'detail' ? 'border-error/40 bg-error-container/20' : 'border-outline-variant/40' }}">
                                    <input type="checkbox" name="scopes[]" value="{{ $key }}" x-model="form.scopes" class="mt-0.5">
                                    <span><b>{{ $key }}</b><br><span class="text-on-surface-variant">{{ $label }}</span></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3">
                        <label class="block text-sm font-medium">Batasi kecamatan
                            <textarea name="kecamatan_text" x-model="form.kecamatan_text" rows="3" placeholder="Satu per baris. Kosongkan = semua wilayah"
                                class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm"></textarea>
                        </label>
                        <label class="block text-sm font-medium">IP yang diizinkan
                            <textarea name="ips_text" x-model="form.ips_text" rows="3" placeholder="Satu per baris, boleh CIDR (203.0.113.0/24). Kosongkan = bebas"
                                class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm font-mono"></textarea>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label class="block text-xs font-medium">Maks. baris/halaman
                            <input type="number" name="per_page_max" x-model="form.per_page_max" min="1" max="100" required
                                class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">
                        </label>
                        <label class="block text-xs font-medium">Request/menit
                            <input type="number" name="rate_per_menit" x-model="form.rate_per_menit" min="1" max="600" required
                                class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">
                        </label>
                        <label class="block text-xs font-medium">Kuota harian
                            <input type="number" name="kuota_harian" x-model="form.kuota_harian" min="1" required
                                class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">
                        </label>
                        <label class="block text-xs font-medium">Kedaluwarsa
                            <input type="date" name="expires_at" x-model="form.expires_at"
                                class="mt-1 w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">
                        </label>
                    </div>

                    <p class="text-xs text-on-surface-variant" x-show="!form.id">API key dibuat otomatis dan ditampilkan sekali setelah disimpan.</p>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modal = false" class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary" x-text="form.id ? 'Simpan' : 'Buat Klien'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function apiKlien() {
            const clients = @js($clientsJs);
            const base = @js(url('/api/v1/desil'));
            const storeUrl = @js(route('api-klien.store'));
            const updateUrl = @js(route('api-klien.update', ':id'));
            let seq = 0;

            return {
                modal: false,
                form: {},
                riwayat: [],
                hasil: null,
                uji: { key: '', endpoint: 'index', nilai: '', query: 'q=', loading: false },

                get action() {
                    return this.form.id ? updateUrl.replace(':id', this.form.id) : storeUrl;
                },

                baru() {
                    this.form = {
                        id: null, nama: '', scopes: ['nik'], kecamatan_text: '', ips_text: '',
                        per_page_max: 25, rate_per_menit: 60, kuota_harian: 1000, expires_at: ''
                    };
                    this.modal = true;
                },

                ubah(id) {
                    this.form = JSON.parse(JSON.stringify(clients.find(c => c.id === id)));
                    this.form.expires_at = this.form.expires_at || '';
                    this.modal = true;
                },

                async kirim() {
                    const e = this.uji.endpoint;
                    let path = '';
                    if (e === 'nik' || e === 'kk') path = '/' + e + '/' + encodeURIComponent(this.uji.nilai.trim());
                    else if (e === 'statistik' || e === 'wilayah') path = '/' + e;

                    const q = e === 'index' ? this.uji.query.trim().replace(/^\?/, '') : '';
                    const url = base + path + (q ? '?' + q : '');

                    this.uji.loading = true;
                    const t = performance.now();
                    let status = 0;

                    try {
                        const res = await fetch(url, {
                            headers: { 'X-API-Key': this.uji.key, 'Accept': 'application/json' }
                        });
                        status = res.status;
                        const text = await res.text();
                        let body = text;
                        try { body = JSON.stringify(JSON.parse(text), null, 2); } catch (_) {}
                        this.hasil = {
                            status, ok: res.ok, url, body,
                            ms: Math.round(performance.now() - t),
                            retry: res.headers.get('Retry-After')
                        };
                    } catch (err) {
                        this.hasil = {
                            status: 0, ok: false, url,
                            body: 'Gagal terhubung: ' + err.message,
                            ms: Math.round(performance.now() - t), retry: null
                        };
                    }

                    this.riwayat.unshift({
                        id: ++seq,
                        waktu: new Date().toLocaleTimeString('id-ID'),
                        ep: e, status, ms: this.hasil.ms
                    });
                    this.riwayat = this.riwayat.slice(0, 15);
                    this.uji.loading = false;
                }
            };
        }
    </script>
@endpush