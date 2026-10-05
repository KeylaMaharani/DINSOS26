{{-- Pengganti blok "4/5. Verifikasi & Validasi Petugas Kelurahan" di admin/pbi-apbn/ajuan/show.blade.php.
     Indeks/bobot/skor hanya tampil untuk petugas Dinsos (bukan role kelurahan/masyarakat). --}}
@php
    $lihatSkor = ! in_array(auth()->user()->role?->normalizedSlug(), ['kelurahan', 'masyarakat'], true);
    $jawabans = $data->jawabans()->orderBy('urutan')->get();
    $fmt = fn ($v, $d = 2) => number_format((float) $v, $d, ',', '.');
@endphp

<div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
    <div class="px-5 py-4 border-b border-outline-variant/40 flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">fact_check</span>
            Isian Parameter & Verifikasi Petugas Kelurahan
        </h3>
        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full
            {{ $data->sudah_diverifikasi_kelurahan ? 'bg-success-container text-on-success-container' : 'bg-warning-container text-on-warning-container' }}">
            <span class="material-symbols-outlined text-[14px]">lock</span>
            {{ $data->sudah_diverifikasi_kelurahan ? 'Sudah Diverifikasi Kelurahan' : 'Menunggu Verifikasi Kelurahan' }}
        </span>
    </div>

    @if ($jawabans->isEmpty())
        <p class="p-6 text-sm text-on-surface-variant text-center">Belum ada jawaban parameter dari masyarakat.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-container/50 text-on-surface-variant text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-center w-12">No</th>
                        <th class="px-5 py-3">Parameter</th>
                        <th class="px-5 py-3">Jawaban</th>
                        @if ($lihatSkor)
                            <th class="px-3 py-3 text-center">Indeks</th>
                            <th class="px-3 py-3 text-center">Bobot</th>
                            <th class="px-3 py-3 text-center">Indeks Terintegrasi</th>
                            <th class="px-3 py-3 text-center">Indeks Kumulatif</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @foreach ($jawabans as $j)
                        <tr>
                            <td class="px-5 py-3 text-center">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3">{{ $j->parameter_nama }}</td>
                            <td class="px-5 py-3 font-semibold">{{ $j->jawaban_label }}</td>
                            @if ($lihatSkor)
                                <td class="px-3 py-3 text-center">{{ $fmt($j->indeks) }}</td>
                                <td class="px-3 py-3 text-center">{{ $fmt($j->bobot) }}</td>
                                <td class="px-3 py-3 text-center">{{ $fmt($j->indeks_terintegrasi) }}</td>
                                <td class="px-3 py-3 text-center font-medium">{{ $fmt($j->skor, 3) }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                @if ($lihatSkor)
                    <tfoot>
                        <tr class="bg-surface-container/40 font-bold">
                            <td colspan="6" class="px-5 py-3 text-right">INDEKS KUMULATIF</td>
                            <td class="px-3 py-3 text-center text-primary">{{ $data->skor_kumulatif !== null ? $fmt($data->skor_kumulatif) : '-' }}</td>
                        </tr>
                        <tr class="bg-surface-container/40 font-bold">
                            <td colspan="6" class="px-5 py-3 text-right">KELAS TINGKAT KEMISKINAN</td>
                            <td class="px-3 py-3 text-center text-primary whitespace-nowrap">{{ $data->kelasLabel() }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 text-sm border-t border-outline-variant/30">
        <div class="sm:col-span-2 p-4 bg-surface-container/30 rounded-lg border border-outline-variant/30">
            <dt class="text-xs text-on-surface-variant mb-1">Kesimpulan/Rekomendasi Kelurahan</dt>
            <dd class="font-semibold">{{ $data->kesimpulan_rekomendasi ?: '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-on-surface-variant mb-0.5">Divalidasi pada</dt>
            <dd class="font-semibold">{{ $data->divalidasi_kelurahan_at?->format('d M Y - H:i') ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-on-surface-variant mb-0.5">Scan Surat Pengantar</dt>
            <dd class="font-semibold">
                @if ($data->scan_surat_pengantar)
                    <a class="text-primary underline" target="_blank" rel="noopener" href="{{ asset('storage/' . $data->scan_surat_pengantar) }}">Lihat</a>
                @else - @endif
            </dd>
        </div>
    </dl>
</div>
