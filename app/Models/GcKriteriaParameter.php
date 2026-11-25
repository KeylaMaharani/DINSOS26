<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GcKriteriaParameter extends Model
{
    protected $table = 'gc_kriteria_parameters';

    protected $fillable = [
        'kelompok',
        'target',
        'urutan',
        'nama',
        'bobot',
        'tipe_input',
        'sumber',
        'pbi_parameter_id',
        'keterangan',
        'aktif',
        'wajib',
        'induk_id',
        'induk_nilai',
        'placeholder',
        'tampil_verifikasi',
    ];

    protected $casts = [
        'bobot' => 'float',
        'aktif' => 'boolean',
        'wajib' => 'boolean',
        'tampil_verifikasi' => 'boolean',
        'induk_nilai' => 'array',
    ];
    
    /**
     * Ke mana jawaban sebuah pertanyaan dipakai.
     *  pbi_gc = pertanyaan sama, dijawab sekali, dipakai PBI APBD dan Ground Check
     *  pbi    = hanya PBI APBD (tidak muncul di form petugas Ground Check)
     *  gc     = hanya Ground Check (tidak muncul di halaman PBI / kelurahan / approval)
     * Form masyarakat menampilkan SEMUA target.
     */
    public const TARGET = [
        'pbi_gc' => 'PBI APBD & Ground Check',
        'pbi'    => 'Khusus PBI APBD',
        'gc'     => 'Khusus Ground Check',
    ];

    /** Nama (huruf kecil) pertanyaan yang HANYA milik PBI APBD. */
    private const NAMA_KHUSUS_PBI = [
        'status lahan tempat tinggal yang ditempati',
        'kondisi dinding',
        'bagaimana kondisi dinding di tempat tinggal anda?',
        'kondisi atap',
        'cara memperoleh air minum',
        'daya listrik terpasang',
        'jenis kloset',
        'tempat pembuangan akhir tinja',
        'jumlah tanggungan keluarga',
        'kepemilikan kendaraan roda 2',
    ];

    /** Nama (huruf kecil) pertanyaan yang dijawab sekali tetapi dipakai PBI dan Ground Check. */
    private const NAMA_BERSAMA = [
        'no kk',
        'nama',
        'alamat sesuai kk',
        'no telp',
        'no. telp',
        'email',
        'status kepemilikan bangunan tempat tinggal yang ditempati',
        'jenis lantai terluas',
        'apa jenis lantai terluas di tempat tinggal anda?',
        'jenis dinding terluas',
        'jenis atap terluas',
        'apa jenis atap terluas di tempat tinggal anda?',
        'sumber air minum utama',
        'apa sumber air minum utama di tempat tinggal anda?',
        'sumber penerangan utama',
        'apa sumber penerangan utama di tempat tinggal anda?',
        'bahan bakar/energi utama untuk memasak',
        'kepemilikan fasilitas bab',
        'di tempat tinggal anda, apakah memiliki fasilitas tempat buang air besar (bab) dan siapa saja yang menggunakan?',
        'pendapatan sebulan terakhir',
        'foto kartu keluarga',
        // Identitas keluarga (formulir bagian 1)
        'nik kepala keluarga',
        'jumlah anggota keluarga',
        'provinsi',
        'kabupaten/kota',
        'kecamatan',
        'desa/kelurahan',
        'kode pos',
        'satuan lingkungan setempat (rt/rw/dusun)',
        'alamat lengkap',
        'nama jalan',
        'nomor rumah',
        'apakah alamat tempat tinggal saat ini sesuai dengan kartu keluarga?',
        // Nama baru identitas keluarga
        'siapa nama kepala keluarga di kartu keluarga (kk) anda?',
        'berapa nomor induk kependudukan (nik) kepala keluarga anda?',
        'berapa nomor kartu keluarga (kk) anda?',
        'berapa jumlah anggota keluarga anda?',
        'provinsi tempat tinggal keluarga saat ini',
        'kabupaten/kota tempat tinggal keluarga saat ini',
        'kecamatan tempat tinggal keluarga saat ini',
        'desa/kelurahan tempat tinggal keluarga saat ini',
        'kode pos tempat tinggal saat ini',
        'satuan lingkungan setempat (rt/rw/dusun dll)',
        'alamat lengkap rumah/tempat tinggal anda?',
        'nama jalan rumah/tempat tinggal anda?',
        'nomor rumah/tempat tinggal anda?',
    ];

    /** Target bawaan sebuah pertanyaan berdasarkan namanya (dipakai migrasi & seeder). */
    public static function targetBawaan(string $kelompok, string $nama): string
    {
        $n = preg_replace('/\s+/', ' ', mb_strtolower(trim($nama)));

        if (in_array($n, self::NAMA_KHUSUS_PBI, true)) {
            return 'pbi';
        }

        if (in_array($n, self::NAMA_BERSAMA, true)) {
            return 'pbi_gc';
        }

        return 'gc';
    }

    /** Kelompok di master (dipakai filter & form master). */
    public const KELOMPOK = [
        'identitas_keluarga'  => '1. Identitas Keluarga',
        'kependudukan'        => 'A. Kependudukan (data pribadi anggota)',
        'pendidikan'          => 'B. Pendidikan',
        'ketenagakerjaan'     => 'C. Ketenagakerjaan',
        'kesehatan'           => 'D. Kesehatan',
        'keluarga'            => 'E. Keluarga',
        'aset_bergerak'       => 'F1. Aset Bergerak',
        'aset_tidak_bergerak' => 'F2. Aset Tidak Bergerak',
        'dokumentasi'         => 'G. Dokumentasi',
    ];

    /** Tab pada form entry Ground Check: sama dengan 5 langkah form masyarakat. */
    public const TAB = [
        'identitas' => ['label' => '1. Identitas Keluarga', 'icon' => 'badge', 'kelompok' => ['identitas_keluarga']],
        'keluarga'  => ['label' => '2. Sosial Ekonomi', 'icon' => 'home', 'kelompok' => ['keluarga']],
        'aset'      => ['label' => '3. Kepemilikan Aset', 'icon' => 'inventory_2', 'kelompok' => ['aset_bergerak', 'aset_tidak_bergerak']],
        'anggota'   => ['label' => '4. Anggota Keluarga', 'icon' => 'groups', 'kelompok' => ['kependudukan', 'pendidikan', 'ketenagakerjaan', 'kesehatan']],
        'dokumentasi' => ['label' => '5. Lampiran & Foto', 'icon' => 'photo_camera', 'kelompok' => ['dokumentasi']],
    ];

    /** Sub-judul di dalam tab yang memuat lebih dari satu kelompok. */
    public const SUBJUDUL = [
        'aset_bergerak'       => 'Aset bergerak',
        'aset_tidak_bergerak' => 'Aset tidak bergerak',
        'kependudukan'        => 'Data pribadi',
        'pendidikan'          => 'Pendidikan',
        'ketenagakerjaan'     => 'Ketenagakerjaan',
        'kesehatan'           => 'Kesehatan',
    ];

    public const TIPE_INPUT = [
        'pilihan'       => 'Pilihan (satu jawaban)',
        'pilihan_ganda' => 'Pilihan ganda',
        'teks'          => 'Teks (isi manual)',
        'angka'         => 'Angka',
        'tanggal'       => 'Tanggal',
        'koordinat'     => 'Koordinat peta',
        'foto'          => 'Foto / unggah berkas',
    ];

    /** Sumber pilihan dari master (null = opsi di gc_kriteria_opsis). */
    public const SUMBER = [
        'lapangan_usaha'  => 'Master Lapangan Usaha',
        'pekerjaan'       => 'Master Pekerjaan (per Lapangan Usaha)',
        'keterampilan'    => 'Master Keterampilan Khusus',
        'penyakit_kronis' => 'Master Penyakit Kronis/Menahun',
    ];

    public function opsis(): HasMany
    {
        return $this->hasMany(GcKriteriaOpsi::class, 'parameter_id')->orderBy('urutan')->orderBy('id');
    }

    public function pbiParameter(): BelongsTo
    {
        return $this->belongsTo(PbiKriteriaParameter::class, 'pbi_parameter_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true)->orderBy('urutan')->orderBy('id');
    }

    /**
     * Parameter yang boleh tampil di form masyarakat AKUN LAMA (form 18 parameter PBI):
     * aktif, punya padanan PBI, berjenis pilihan satu jawaban, bukan bersumber
     * master terpisah, dan sudah punya opsi.
     */
    public function scopeUntukMasyarakat($query)
    {
        return $query->aktif()
            ->whereNotNull('pbi_parameter_id')
            ->where('tipe_input', 'pilihan')
            ->whereNull('sumber')
            ->whereHas('opsis');
    }

    public static function daftarMasyarakat()
    {
        return static::untukMasyarakat()->with('opsis')->get()->unique('pbi_parameter_id')->values();
    }

    public function butuhOpsi(): bool
    {
        // Parameter bersumber master tidak memakai gc_kriteria_opsis.
        return ! $this->sumber && in_array($this->tipe_input, ['pilihan', 'pilihan_ganda'], true);
    }

    /** Form petugas Ground Check: hanya pertanyaan target gc dan pbi_gc. */
    public function scopeUntukPetugas($query)
    {
        return $query->whereIn('target', ['gc', 'pbi_gc'])->orderBy('urutan')->orderBy('id');
    }

    /** Sisi PBI APBD (skor, kelurahan, approval): hanya target pbi dan pbi_gc. */
    public function scopeUntukPbi($query)
    {
        return $query->whereIn('target', ['pbi', 'pbi_gc'])->orderBy('urutan')->orderBy('id');
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'induk_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(self::class, 'induk_id');
    }

    /**
     * ID parameter yang TIDAK berlaku karena jawaban induknya tidak memenuhi syarat
     * (berantai: anak dari parameter yang tersembunyi ikut tersembunyi).
     * $jawaban = [parameter_id => jawaban]. Induk yang tidak ada di $params dianggap tidak membatasi.
     */
    public static function idTersembunyi($params, array $jawaban): array
    {
        $norm = fn($t) => mb_strtolower(trim((string) $t));
        $byId = $params->keyBy('id');
        $sembunyi = [];

        for ($i = 0; $i < 5; $i++) {
            $berubah = false;

            foreach ($params as $p) {
                if (! $p->induk_id || ! $byId->has($p->induk_id)) {
                    continue;
                }

                $mentah = $jawaban[$p->induk_id] ?? null;
                if (is_string($mentah) && str_starts_with(trim($mentah), '[')) {
                    $mentah = json_decode($mentah, true) ?? $mentah;
                }
                $dipilih = array_map($norm, array_filter((array) $mentah, fn($x) => $x !== null && $x !== ''));
                $syarat = array_map($norm, (array) $p->induk_nilai);

                $tampil = ! isset($sembunyi[$p->induk_id]) && array_intersect($syarat, $dipilih);

                if (! $tampil && ! isset($sembunyi[$p->id])) {
                    $sembunyi[$p->id] = true;
                    $berubah = true;
                } elseif ($tampil && isset($sembunyi[$p->id])) {
                    unset($sembunyi[$p->id]);
                    $berubah = true;
                }
            }

            if (! $berubah) {
                break;
            }
        }

        return array_keys($sembunyi);
    }
}
