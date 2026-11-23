{{-- Satu pertanyaan Keterangan Anggota Keluarga (gaya kartu + chip). Variabel: $p, $nomor, $namaWarga, $nilai, $opsiLabels, $bisaEdit --}}
@php
    $Str = \Illuminate\Support\Str::class;
    $nm = $Str::lower(trim($p->nama));
    $multi = $p->tipe_input === 'pilihan_ganda';
    $adalahPilihan = in_array($p->tipe_input, ['pilihan', 'pilihan_ganda'], true);
    $prefix = $prefix ?? 'jawaban';                       // anggota: 'anggota[12]'
    $nmField = $multi ? "{$prefix}[{$p->id}][]" : "{$prefix}[{$p->id}]";
    $oldKey = str_replace(['[', ']'], ['.', ''], $prefix) . '.' . $p->id; 

    $judulMap = [
        'dimana keberadaan anggota keluarga sekarang?' => 'Dimana keberadaan {n} sekarang?',
        'no. telp' => 'Nomor Handphone {n}',
        'no telp' => 'Nomor Handphone {n}',
        'jenis kelamin' => 'Pilih Jenis Kelamin {n}',
        'status hubungan dengan kepala keluarga' => 'Apa status hubungan {n} dengan kepala keluarga?',
        'status perkawinan' => 'Apa status perkawinan {n}?',
        'apa status partisipasi sekolah?' => 'Apa Status Partisipasi Sekolah {n} ?',
        'apa ijazah / sttb tertinggi yang dimiliki?' => 'Apa Ijazah / STTB tertinggi yang dimiliki {n} ?',
        'apa profesi pekerjaan utama?' => 'Apa profesi pekerjaan utama {n}?',
        'apa status kedudukan dalam pekerjaan utama?' => 'Apa status kedudukan {n} dalam pekerjaan utama?',
        'kepemilikan rekening aktif/dompet digital' => 'Apakah {n} memiliki rekening aktif atau dompet digital?',
        'apakah memiliki keluhan kesehatan kronis/menahun?' => 'Apakah {n} memiliki keluhan kesehatan kronis/menahun ?',
    ];
    $judul = $judulMap[$nm] ?? ($Str::startsWith($nm, 'apakah menyandang ')
        ? 'Apakah {n} menyandang ' . $Str::after($p->nama, 'menyandang ')
        : $p->nama);
    $judulHtml = str_replace('{n}', '<span class="ga-nama">' . e($namaWarga) . '</span>', e($judul));

    $isTelp = in_array($nm, ['no. telp', 'no telp'], true);
    $isGender = $nm === 'jenis kelamin';

    // Opsi: dari $opsiLabels milik controller (sudah mencakup sumber master seperti penyakit kronis)
    $opsi = collect($opsiLabels[$p->id] ?? [])->values()->all();
    if (! $opsi && $isGender) { $opsi = ['Laki-laki', 'Perempuan']; }

    $raw = old($oldKey, $nilai[$p->id] ?? ($multi ? [] : ''));
    if ($multi) {
        if (! is_array($raw)) {
            $d = json_decode((string) $raw, true);
            $raw = is_array($d) ? $d : (filled($raw) ? [$raw] : []);
        }
        $dipilih = array_map('strval', $raw);
    } else {
        $dipilih = [is_array($raw) ? '' : (string) $raw];
    }

    $kompak = count($opsi) > 5;          // >5 pilihan: 5 tombol + "Lainnya ⌄" (daftar lengkap di pop-up)
    $yaTidak = count($opsi) === 2 && $Str::lower($opsi[0]) === 'ya';
@endphp

<div class="ga-card min-w-0" data-gc-id="{{ $p->id }}" @if ($p->wajib) data-gc-wajib @endif>
    <h3 class="ga-judul">{!! $judulHtml !!}</h3>

    @if ($isTelp)
        <p class="ga-note">Kosongkan bagian ini jika yang bersangkutan tidak memiliki nomor handphone</p>
        <input type="tel" inputmode="tel" name="{{ $nmField }}" value="{{ $dipilih[0] }}" placeholder="08xx-xxxx-xxxx"
               @disabled(! $bisaEdit) class="ga-input" autocomplete="off">
    @elseif (! $adalahPilihan)
        <input type="{{ $p->tipe_input === 'angka' ? 'number' : ($p->tipe_input === 'tanggal' ? 'date' : 'text') }}"
               name="{{ $nmField }}" value="{{ $dipilih[0] }}" @disabled(! $bisaEdit) class="ga-input">
    @elseif ($isGender)
        <div class="ga-gender">
            @foreach ($opsi as $label)
                @php $perempuan = $Str::startsWith($Str::lower($label), 'perempuan'); @endphp
                <label class="ga-gcard">
                    <input type="radio" class="ga-sr" name="{{ $nmField }}" value="{{ $label }}"
                           @checked(in_array((string) $label, $dipilih, true)) @disabled(! $bisaEdit)>
                    <span class="ga-gbox">
                        <span class="ga-avatar"><span class="material-symbols-outlined">{{ $perempuan ? 'face_3' : 'face' }}</span></span>
                        <span>{{ $label }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    @else
        @if ($multi)
            <p class="ga-note ga-warn">Pilihan tidak boleh kosong</p>
        @endif
        <div class="ga-box" data-ga-box @if ($multi) data-ga-multi @endif>
            <div class="ga-chips {{ $yaTidak ? 'is-yn' : '' }}">
                @foreach ($opsi as $i => $label)
                    @php $cek = in_array((string) $label, $dipilih, true); @endphp
                    @if ($kompak && $i >= 5)
                        <input type="{{ $multi ? 'checkbox' : 'radio' }}" class="ga-sr" data-ga-opt data-ga-lanjut
                               name="{{ $nmField }}" value="{{ $label }}" @checked($cek) @disabled(! $bisaEdit)>
                    @else
                        <label class="ga-chip">
                            <input type="{{ $multi ? 'checkbox' : 'radio' }}" class="ga-sr" data-ga-opt
                                   name="{{ $nmField }}" value="{{ $label }}" @checked($cek) @disabled(! $bisaEdit)>
                            <span>{{ $label }}</span>
                        </label>
                    @endif
                @endforeach
                @if ($kompak)
                    <button type="button" class="ga-chip ga-more" data-ga-buka @disabled(! $bisaEdit)>
                        <span class="ga-lbl">Lainnya</span>
                        <span class="material-symbols-outlined">expand_more</span>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>