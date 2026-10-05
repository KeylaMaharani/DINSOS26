@extends('layouts.admin')

@section('title', 'Detail Ajuan DTSEN')
@section('page_title', 'DTSEN — Detail Ajuan')

@php
    $statusColor = match ($data->status) {
        \App\Models\DtsenPermohonan::STATUS_SELESAI => 'bg-success-container text-on-success-container',
        \App\Models\DtsenPermohonan::STATUS_DITOLAK => 'bg-error-container text-on-error-container',
        default => 'bg-secondary-fixed text-on-secondary-container',
    };

    $detail = $data->detail;
    $bansos = $data->bansos;

    $inputFileClass =
        'mt-2 block w-full text-[10px] text-on-surface-variant file:mr-2 file:rounded file:border-0 file:bg-brand/10 file:px-2 file:py-1 file:text-brand';
@endphp

@section('content')
    <div class="space-y-5 max-w-5xl">

        <a href="{{ route('dtsen.ajuan') }}"
            class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-brand">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar Ajuan
        </a>

        @if ($errors->any())
            <div class="flex flex-col gap-1 px-4 py-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        {{-- Header ringkas --}}
        <div
            class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] p-5 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <p class="text-xs text-on-surface-variant">NIK</p>
                <h2 class="text-lg font-extrabold text-brand">{{ $data->nik }}</h2>
                <p class="text-sm text-on-surface-variant mt-1">{{ $data->nama_pemohon }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $statusColor }}">
                {{ $statusLabels[$data->status] ?? $data->status }}
            </span>
        </div>

        {{-- 1. Keterangan Penerima BANSOS (editable) --}}
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
            <div class="px-4 py-3 border-b border-outline-variant/40 flex items-center justify-between">
                <h3 class="text-sm font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-brand">volunteer_activism</span>
                    Keterangan Penerima BANSOS
                </h3>
                <div class="flex items-center gap-3">
                    @if ($canAct)
                        <span class="text-[11px] text-on-surface-variant"><span class="text-error">*</span> wajib diisi</span>
                        <span class="text-xs text-brand">Dapat diedit</span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('dtsen.bansos.update', $data) }}" class="p-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">Peringkat Kesejahteraan Keluarga (Desil) <span class="text-error">*</span></label>
                        @if (!$canAct)
                            <p class="font-medium text-sm px-1 py-1.5">{{ $bansos->peringkat_kesejahteraan_keluarga ?? '-' }}</p>
                        @else
                            <input type="text" name="peringkat_kesejahteraan_keluarga" required
                                value="{{ old('peringkat_kesejahteraan_keluarga', $bansos->peringkat_kesejahteraan_keluarga ?? '') }}"
                                class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30" />
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">Dicetak Oleh <span class="text-error">*</span></label>
                        @if (!$canAct)
                            <p class="font-medium text-sm px-1 py-1.5">{{ $bansos->dicetak_oleh ?? '-' }}</p>
                        @else
                            <input type="text" name="dicetak_oleh" required
                                value="{{ old('dicetak_oleh', $bansos->dicetak_oleh ?? '') }}"
                                class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30" />
                        @endif
                    </div>

                    @foreach (['bnpt' => 'BNPT', 'pkh' => 'PKH', 'pbi' => 'PBI', 'yatim_piatu' => 'Yatim Piatu'] as $field => $label)
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">{{ $label }} <span class="text-error">*</span></label>
                            @if (!$canAct)
                                <p class="font-medium text-sm px-1 py-1.5">{{ ($bansos->$field ?? false) ? 'Ya' : 'Tidak' }}</p>
                            @else
                                <select name="{{ $field }}" required
                                    class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                                    <option value="1" @selected(old($field, $bansos->$field ?? false))>Ya</option>
                                    <option value="0" @selected(!old($field, $bansos->$field ?? false))>Tidak</option>
                                </select>
                            @endif
                        </div>
                    @endforeach

                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">Tanggal Cetak <span class="text-error">*</span></label>
                        @if (!$canAct)
                            <p class="font-medium text-sm px-1 py-1.5">
                                {{ $bansos->tanggal_cetak ? $bansos->tanggal_cetak->format('d-m-Y') : '-' }}</p>
                        @else
                            <input type="date" name="tanggal_cetak" required
                                value="{{ old('tanggal_cetak', optional($bansos->tanggal_cetak ?? null)->format('Y-m-d')) }}"
                                class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30" />
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs text-on-surface-variant mb-1">Berlaku Sampai <span class="text-error">*</span></label>
                        @if (!$canAct)
                            <p class="font-medium text-sm px-1 py-1.5">
                                {{ $bansos->berlaku_sampai ? $bansos->berlaku_sampai->format('d-m-Y') : '-' }}
                                @if ($bansos && $bansos->isExpired())
                                    <span class="ml-1 text-xs text-error font-semibold">(Kedaluwarsa)</span>
                                @endif
                            </p>
                        @else
                            <input type="date" name="berlaku_sampai" required
                                value="{{ old('berlaku_sampai', optional($bansos->berlaku_sampai ?? null)->format('Y-m-d')) }}"
                                class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30" />
                        @endif
                    </div>
                </div>

                {{-- Upload Lainnya --}}
                <div class="pt-4">
                    <label class="block text-xs text-on-surface-variant mb-1">Upload Lainnya</label>
                    @php $uploadLainnya = $bansos->upload_lainnya ?? null; @endphp
                    <a @if ($uploadLainnya) href="{{ asset('storage/' . $uploadLainnya) }}" target="_blank" @endif
                        class="inline-flex items-center gap-2 border border-outline-variant/40 rounded-lg px-3 py-2 text-sm hover:border-brand transition-colors w-fit">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">upload_file</span>
                        Dokumen Tambahan
                        @unless ($uploadLainnya)
                            <span class="text-xs text-on-surface-variant">(belum ada)</span>
                        @endunless
                    </a>
                </div>

                @if ($canAct)
                    <div class="pt-4">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand text-white hover:bg-primary text-sm font-medium transition-colors">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Keterangan BANSOS
                        </button>
                    </div>
                @endif
            </form>
        </div>

        {{-- 2. Data Detail (editable) --}}
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
            <div class="px-4 py-3 border-b border-outline-variant/40 flex items-center justify-between">
                <h3 class="text-sm font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-brand">badge</span>
                    Data Detail
                </h3>
                <div class="flex items-center gap-3">
                    @if ($canAct)
                        <span class="text-[11px] text-on-surface-variant"><span class="text-error">*</span> wajib diisi</span>
                        <span class="text-xs text-brand">Dapat diedit</span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('dtsen.detail.update', $data) }}" class="p-4">
                @csrf
                @method('PUT')

                @php
                    $fields = [
                        'nik' => ['label' => 'NIK', 'type' => 'text', 'required' => true],
                        'nama' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                        'no_kk' => ['label' => 'No KK', 'type' => 'text', 'required' => true],
                        'tanggal_lahir' => ['label' => 'Tanggal Lahir', 'type' => 'date', 'required' => true],
                        'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'required' => true],
                        'alasan_keputusan_cetak' => ['label' => 'Alasan Keputusan Cetak', 'type' => 'textarea', 'required' => true],
                    ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    @foreach ($fields as $field => $config)
                        <div class="{{ $config['type'] === 'textarea' ? 'sm:col-span-2' : '' }}">
                            <label class="block text-xs text-on-surface-variant mb-1">
                                {{ $config['label'] }}
                                @if ($config['required'] ?? false)
                                    <span class="text-error">*</span>
                                @endif
                            </label>

                            @if (!$canAct)
                                <p class="font-medium text-sm px-1 py-1.5">
                                    {{ $detail->$field ?? null
                                        ? ($config['type'] === 'date'
                                            ? \Carbon\Carbon::parse($detail->$field)->format('d-m-Y')
                                            : $detail->$field)
                                        : '-' }}
                                </p>
                            @elseif ($config['type'] === 'textarea')
                                <textarea name="{{ $field }}" rows="2" @required($config['required'] ?? false)
                                    class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">{{ old($field, $detail->$field ?? '') }}</textarea>
                            @else
                                <input type="{{ $config['type'] }}" name="{{ $field }}" @required($config['required'] ?? false)
                                    value="{{ old($field, $config['type'] === 'date' ? optional($detail->$field ?? null)->format('Y-m-d') : $detail->$field ?? '') }}"
                                    class="w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30" />
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($canAct)
                    <div class="pt-4">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand text-white hover:bg-primary text-sm font-medium transition-colors">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Data Detail
                        </button>
                    </div>
                @endif
            </form>
        </div>

        {{-- 3. Lampiran (form upload terpisah) --}}
        <form method="POST" action="{{ route('dtsen.lampiran.update', $data) }}" enctype="multipart/form-data"
            class="space-y-5">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
                <div class="px-4 py-3 border-b border-outline-variant/40 flex items-center justify-between">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-brand">attach_file</span>
                        Lampiran
                    </h3>
                    @if ($canUploadLampiran)
                        <span class="text-[11px] text-on-surface-variant">Pilih file untuk mengunggah / mengganti
                            (JPG, PNG, PDF, maks. 2 MB)</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 p-4">
                    @foreach ([
                        'scan_ktp' => ['label' => 'Scan KTP', 'required' => false],
                        'screenshot_dtsen' => ['label' => 'Screenshot DTSEN', 'required' => true],
                    ] as $field => $config)
                        @php
                            $file = $data->lampiran->$field ?? null;
                            $isPdf = $file && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf';
                        @endphp
                        <div class="border border-outline-variant/40 rounded-lg overflow-hidden hover:border-brand transition-colors">
                            <a @if ($file) href="{{ asset('storage/' . $file) }}" target="_blank" @endif
                                class="block">
                                <div class="aspect-square bg-surface-container flex items-center justify-center">
                                    @if ($file && !$isPdf)
                                        <img src="{{ asset('storage/' . $file) }}" class="w-full h-full object-cover"
                                            alt="{{ $config['label'] }}">
                                    @elseif ($file && $isPdf)
                                        <span class="material-symbols-outlined text-[40px] text-error/70">picture_as_pdf</span>
                                    @else
                                        <span
                                            class="material-symbols-outlined text-[28px] text-on-surface-variant/50">image_not_supported</span>
                                    @endif
                                </div>
                            </a>
                            <div class="px-1 py-1.5">
                                <p class="text-[11px] text-center text-on-surface-variant">
                                    {{ $config['label'] }}
                                    @if ($config['required'])
                                        <span class="text-error">*</span>
                                    @endif
                                </p>
                                @if ($canUploadLampiran)
                                    <input type="file" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf"
                                        class="{{ $inputFileClass }}" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($canUploadLampiran)
                <div>
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand text-white hover:bg-primary text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan Lampiran
                    </button>
                </div>
            @endif
        </form>

        {{-- 4. Surat Keterangan DTSEN Dinas Sosial --}}
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
            <div class="px-4 py-3 border-b border-outline-variant/40">
                <h3 class="text-sm font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-brand">verified</span>
                    Surat Keterangan DTSEN Dinas Sosial
                </h3>
            </div>
            <div class="p-4">
                @php $suratDinsos = $data->surat->surat_keterangan_dtsen_digital ?? null; @endphp
                <a @if ($suratDinsos) href="{{ asset('storage/' . $suratDinsos) }}" target="_blank" @endif
                    class="flex items-center gap-2 border border-outline-variant/40 rounded-lg px-3 py-2 text-sm hover:border-brand transition-colors w-fit">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">description</span>
                    Surat Keterangan DTSEN Digital
                    @unless ($suratDinsos)
                        <span class="text-xs text-on-surface-variant">(belum ada)</span>
                    @endunless
                </a>
            </div>
        </div>

        {{-- 5. Log Proses --}}
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant/40">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-brand">history</span>
                    Log Proses
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#e6edf8] text-on-surface-variant text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 font-medium">Tanggal Proses</th>
                            <th class="px-5 py-3 font-medium">Username</th>
                            <th class="px-5 py-3 font-medium">Role</th>
                            <th class="px-5 py-3 font-medium">Aktivitas</th>
                            <th class="px-5 py-3 font-medium">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        @forelse ($data->logs as $log)
                            <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                                <td class="px-5 py-3 whitespace-nowrap text-on-surface-variant">
                                    {{ \Carbon\Carbon::parse($log->tanggal_proses)->format('d M Y - H:i') }}</td>
                                <td class="px-5 py-3 font-medium text-on-surface">{{ $log->username }}</td>
                                <td class="px-5 py-3"><span class="bg-surface-container px-2 py-1 rounded text-xs">{{ $log->rolename }}</span></td>
                                <td class="px-5 py-3 font-medium">{{ $log->taskname }}</td>
                                <td class="px-5 py-3 text-on-surface-variant">{{ $log->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-on-surface-variant">Belum ada log proses.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 6. Aksi & Catatan --}}
        <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden mb-10">
            <div class="px-5 py-4 border-b border-outline-variant/40 bg-[#eef4fd]">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-brand">alt_route</span>
                    Proses Permohonan
                </h3>
            </div>

            @if ($isFinal)
                <div class="p-6 text-center bg-surface-container-lowest">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full {{ $data->status === \App\Models\DtsenPermohonan::STATUS_SELESAI ? 'bg-success/20 text-success' : 'bg-error/20 text-error' }} mb-3">
                        <span class="material-symbols-outlined text-[24px]">{{ $data->status === \App\Models\DtsenPermohonan::STATUS_SELESAI ? 'check_circle' : 'cancel' }}</span>
                    </div>
                    <h4 class="text-lg font-bold mb-1">Permohonan Final</h4>
                    <p class="text-sm text-on-surface-variant">
                        Permohonan ini sudah final dengan status
                        <strong>{{ $statusLabels[$data->status] ?? $data->status }}</strong>. Tidak ada aksi lanjutan.
                    </p>
                </div>
            @elseif (!$canAct)
                <div class="p-6 text-center bg-surface-container-lowest">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-warning/20 text-warning mb-3">
                        <span class="material-symbols-outlined text-[24px]">hourglass_empty</span>
                    </div>
                    <h4 class="text-lg font-bold mb-1">Menunggu Tahap Selanjutnya</h4>
                    <p class="text-sm text-on-surface-variant">
                        Permohonan ini sedang berada di tahap
                        <strong>{{ $statusLabels[$data->status] ?? $data->status }}</strong>.<br>
                        Anda tidak berwenang memproses pada tahap ini.
                    </p>
                </div>
            @else
                <form method="POST" action="{{ route('dtsen.proses', $data) }}" class="p-5">
                    @csrf

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-on-surface mb-2">Catatan Proses <span class="text-error">*</span></label>
                        <textarea name="catatan" rows="3" required placeholder="Tulis catatan (wajib diisi untuk semua aksi)..."
                            class="w-full px-4 py-3 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand shadow-sm">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3">

                        @if ($returnLabel)
                            <button type="submit" name="action" value="kembalikan"
                                data-confirm="{{ $returnLabel }}?"
                                data-confirm-variant="primary"
                                class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-outline-variant hover:bg-surface-container-highest text-on-surface text-sm font-bold transition-all">
                                <span class="material-symbols-outlined text-[18px]">undo</span>
                                {{ $returnLabel }}
                            </button>
                        @endif

                        <button type="submit" name="action" value="simpan_catatan"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg bg-surface-container hover:bg-secondary/20 text-brand text-sm font-bold transition-all">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Catatan Saja
                        </button>

                        <button type="submit" name="action" value="lanjut"
                            data-confirm="Teruskan permohonan ini ke tahap berikutnya{{ $nextTaskLabel ? ' (' . $nextTaskLabel . ')' : '' }}?"
                            data-confirm-variant="primary"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-xl bg-brand text-white hover:bg-primary text-sm font-bold shadow-md transition-all sm:ml-auto">
                            <span class="material-symbols-outlined text-[18px]">redo</span>
                            @if ($nextTaskLabel)
                                Teruskan ke {{ $nextTaskLabel }}
                            @else
                                Teruskan
                            @endif
                        </button>

                        <button type="submit" name="action" value="tolak"
                            data-confirm="Yakin ingin menolak permohonan ini? Catatan alasan wajib diisi."
                            data-confirm-variant="error"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg border border-error text-error hover:bg-error hover:text-on-error text-sm font-bold transition-all">
                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                            Tolak (Reject)
                        </button>
                    </div>

                    <p class="text-[11px] text-on-surface-variant italic mt-3">
                        Catatan wajib diisi untuk semua aksi (Lanjut, Kembalikan, Simpan, maupun Reject).
                    </p>
                </form>
            @endif
        </div>

    </div>
@endsection
