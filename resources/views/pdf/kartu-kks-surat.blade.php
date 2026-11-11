<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 27pt 37pt 30pt 42pt; }

    body {
        margin: 0; padding: 0;
        font-family: "Times New Roman", Times, serif;
        font-size: 11pt; line-height: 15pt; color: #000;
    }
    p { margin: 0; padding: 0; }
    table { border-collapse: collapse; }
    td { padding: 0; vertical-align: top; }

    /* ---------- Kop ---------- */
    .judul  { font-size: 13pt; font-weight: bold; line-height: 15.5pt; }
    .alamat { font-size: 9.5pt; line-height: 12.4pt; }
    .garis  { margin-top: 15pt; height: 1pt; border-top: 3pt solid #000; border-bottom: 1pt solid #000; }

    /* ---------- Nomor / Kepada ---------- */
    table.meta td { line-height: 14.5pt; }

    /* ---------- Data pemohon ---------- */
    table.isi td { line-height: 12pt; padding: 5.1pt 0; }

    /* ---------- Paragraf ---------- */
    .p-awal  { margin-top: 17pt; text-indent: 30pt; text-align: justify; line-height: 15.5pt; }
    .p-biasa { margin-top: 15pt; text-align: justify; line-height: 15.5pt; }

    /* ---------- Kotak TTE ---------- */
    table.kotak { width: 100%; border: 1.5pt solid #000; }
    table.kotak td { line-height: 13.8pt; }
</style>
</head>
<body>

    {{-- ================= KOP SURAT ================= --}}
    <table style="width:100%">
        <tr>
            <td style="width:63pt; padding-left:5pt;">
                <img src="{{ $logo }}" style="width:53pt;">
            </td>
            <td style="text-align:center;">
                <div class="judul">PEMERINTAH DAERAH KOTA BOGOR</div>
                <div class="judul">DINAS SOSIAL</div>
                <div class="alamat">
                    Jl. Merdeka No. 142, Bogor Tengah, Ciwaringin, Bogor, Kota Bogor, Jawa Barat 16111<br>
                    Telp. (0251)-8332315, Faksimile (0251)-8378332<br>
                    Situs web : https://dinsos.kotabogor.go.id Email : dinsos@kotabogor.go.id
                </div>
            </td>
        </tr>
    </table>
    <div class="garis"></div>

    {{-- ================= TANGGAL ================= --}}
    <div style="margin-top:20pt; text-align:right;">
        Bogor, {{ now()->locale('id')->translatedFormat('d F Y') }}
    </div>

    {{-- ================= NOMOR / SIFAT / LAMPIRAN / HAL + KEPADA ================= --}}
    <table class="meta" style="width:100%; margin-top:15pt;">
        <tr>
            <td style="width:299pt;">
                <table style="margin-left:14pt;">
                    <tr><td style="width:58pt;">Nomor</td><td style="width:9pt;">:</td><td style="white-space:nowrap;">{{ $nomor }}</td></tr>
                    <tr><td>Sifat</td><td>:</td><td>Biasa</td></tr>
                    <tr><td>Lampiran</td><td>:</td><td>-</td></tr>
                    <tr><td>Hal</td><td>:</td><td>Surat Keterangan</td></tr>
                </table>
            </td>
            <td>
                Kepada<br>
                Yth. Kepala Bank Penyalur KKS<br>
                di Bogor
            </td>
        </tr>
    </table>

    {{-- ================= ISI SURAT (diinset kiri 41pt, kanan 30pt) ================= --}}
    <div style="margin: 17pt 30pt 0 41pt;">

        <p style="line-height:15.5pt;">Berdasarkan data Base Dinas Sosial Kota Bogor, dengan ini menerangkan bahwa :</p>

        <table class="isi" style="margin-left:27pt;">
            <tr><td style="width:124pt;">Nama</td><td style="width:13pt;">:</td><td>{{ $d->nama }}</td></tr>
            <tr><td>Jenis Kelamin</td><td>:</td><td>{{ $d->jenis_kelamin }}</td></tr>
            <tr><td>Tempat/Tanggal Lahir</td><td>:</td><td>{{ strtoupper($d->tempat_lahir) }}, {{ \Carbon\Carbon::parse($d->tanggal_lahir)->locale('id')->translatedFormat('d F Y') }}</td></tr>
            <tr><td>Nomor PKH</td><td>:</td><td>{{ $d->no_pkh ?: '-' }}</td></tr>
            <tr><td>Nomor NIK</td><td>:</td><td>{{ $d->nik }}</td></tr>
            <tr><td>Nomor KK</td><td>:</td><td>{{ $d->no_kk }}</td></tr>
            <tr><td>Nomor Kartu/KKS</td><td>:</td><td>{{ $d->no_kartu_kks }}</td></tr>
            <tr><td>Nomor Rekening</td><td>:</td><td>{{ $d->no_rekening }}</td></tr>
            <tr><td>Pekerjaan</td><td>:</td><td>{{ $d->pekerjaan }}</td></tr>
        </table>

        <p class="p-awal">
            Data diatas benar berdomisili {{ $d->alamat }}@if ($p->kelurahan) Kelurahan {{ strtoupper($p->kelurahan) }}@endif Kota Bogor,
            bermasalah dengan kartu KKS ({{ $d->masalah_kartu }}).
        </p>

        <p class="p-biasa">Sehubungan hal tersebut diatas kami mohon Bank bisa membuatkan kartu KKS yang baru.</p>

        <p class="p-biasa">Demikian surat keterangan ini kami buat untuk dipergunakan sebagaimana mestinya.</p>

        {{-- ================= TANDA TANGAN ELEKTRONIK ================= --}}
        <table style="margin-top:45pt; margin-left:84pt; width:361pt;">
            <tr>
                <td style="width:81pt; padding-top:7pt;">
                    <img src="{{ $qr }}" style="width:65pt;">
                </td>
                <td style="width:280pt;">
                    <table class="kotak">
                        <tr>
                            <td style="width:61pt; padding:5pt 9pt 3pt 6pt;">
                                <img src="{{ $logo }}" style="width:61pt;">
                            </td>
                            <td style="padding-top:2pt;">
                                Ditandatangani Secara Elektronik oleh :<br>
                                <b>{{ strtoupper($kadis->jabatan ?: 'KEPALA') }}</b><br><br>
                                <b><u>{{ $kadis->name }}</u></b><br>
                                @if ($kadis->pangkat) {{ $kadis->pangkat }}<br>@endif
                                @if ($kadis->nip) NIP. {{ $kadis->nip }} @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>