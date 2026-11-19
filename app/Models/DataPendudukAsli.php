<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Data penduduk asli (hasil impor, ±1,14 juta baris). Hanya dibaca, tidak diubah dari aplikasi.
 */
class DataPendudukAsli extends Model
{
    protected $table = 'data_penduduk_asli';

    public $timestamps = false;

    protected $guarded = ['*'];

    /** Seluruh kolom (kecuali id) beserta labelnya -- dipakai modal Detail & export CSV. */
    public const KOLOM = [
        'kode_kabupaten'           => 'Kode Kabupaten',
        'kode_wilayah'             => 'Kode Wilayah',
        'provinsi'                 => 'Provinsi',
        'kabupaten'                => 'Kabupaten/Kota',
        'kecamatan'                => 'Kecamatan',
        'desa_kelurahan'           => 'Desa/Kelurahan',
        'alamat'                   => 'Alamat',
        'dusun'                    => 'Dusun',
        'rw'                       => 'RW',
        'rt'                       => 'RT',
        'nomor_kartu_keluarga'     => 'No. KK',
        'nomor_induk_kependudukan' => 'NIK',
        'nama'                     => 'Nama',
        'jenis_kelamin'            => 'Jenis Kelamin',
        'tempat_lahir'             => 'Tempat Lahir',
        'tanggal_lahir'            => 'Tanggal Lahir (teks)',
        'tgl_lahir_2'              => 'Tanggal Lahir',
        'status_perkawinan'        => 'Status Perkawinan',
        'hubungan_keluarga'        => 'Hubungan Keluarga',
        'status_dtsen'             => 'Status DTSEN',
        'desil_nasional'           => 'Desil Nasional',
        'usia'                     => 'Usia',
        'nama_ibu_kandung'         => 'Nama Ibu Kandung',
        'pekerjaan'                => 'Pekerjaan',
        'disabilitas'              => 'Disabilitas',
        'keterangan_disabilitas'   => 'Keterangan Disabilitas',
        'tingkat_disabilitas'      => 'Tingkat Disabilitas',
        'lanjut_usia'              => 'Lanjut Usia',
        'BANSOS_PKH'               => 'Bansos PKH',
        'BANSOS_SEMBAKO'           => 'Bansos Sembako',
        'PBI_JK'                   => 'PBI-JK',
        'bpjs_tk'                  => 'BPJS TK',
        'p3ke'                     => 'P3KE',
        'rtlh'                     => 'RTLH',
        'st_disabilitas'           => 'Status Disabilitas',
        'lansia'                   => 'Lansia',
        'status_stunting'          => 'Status Stunting',
    ];

    /** key filter => [kolom, label, warna chip] */
    public const BANSOS = [
        'pbi'        => ['PBI_JK',         'PBI-JK',     'emerald'],
        'pkh'        => ['BANSOS_PKH',     'PKH',        'sky'],
        'sembako'    => ['BANSOS_SEMBAKO', 'Sembako',    'amber'],
        'bpjs_tk'    => ['bpjs_tk',        'BPJS TK',    'slate'],
        'p3ke'       => ['p3ke',           'P3KE',       'purple'],
        'rtlh'       => ['rtlh',           'RTLH',       'rose'],
        'disabilitas' => ['st_disabilitas', 'Disabilitas', 'indigo'],
        'lansia'     => ['lansia',         'Lansia',     'orange'],
        'stunting'   => ['status_stunting', 'Stunting',  'red'],
    ];

    public const DESIL_LABEL = [
        0  => 'Tidak Terdaftar Desil',
        1  => 'Sangat Miskin',
        2  => 'Miskin',
        3  => 'Hampir Miskin',
        4  => 'Rentan Miskin',
        5  => 'Menengah Bawah',
        6  => 'Menengah Bawah',
        7  => 'Menengah Atas',
        8  => 'Menengah Atas',
        9  => 'Mampu',
        10 => 'Mampu',
    ];

    /** Nilai kolom bansos dianggap "aktif" jika tidak kosong dan tidak diawali TIDAK / BUKAN. */
    public static function aktif(?string $nilai): bool
    {
        $v = strtoupper(trim(str_replace("\r", '', (string) $nilai)));

        return $v !== '' && ! str_starts_with($v, 'TIDAK') && ! str_starts_with($v, 'BUKAN');
    }

    /** Versi SQL dari aktif() untuk filter & agregat. $kolom hanya dari konstanta BANSOS. */
    public static function sqlAktif(string $kolom): string
    {
        return "(`{$kolom}` IS NOT NULL AND TRIM(REPLACE(`{$kolom}`, CHAR(13), '')) <> '' "
            . "AND `{$kolom}` NOT LIKE 'TIDAK%' AND `{$kolom}` NOT LIKE 'BUKAN%')";
    }

    /** Daftar key bansos yang aktif pada baris ini. */
    public function bansosAktif(): array
    {
        $hasil = [];
        foreach (self::BANSOS as $key => [$kolom, $label, $warna]) {
            if (self::aktif($this->{$kolom})) {
                $hasil[$key] = [$label, $warna];
            }
        }

        return $hasil;
    }
}
