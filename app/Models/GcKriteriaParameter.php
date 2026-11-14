<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * GANTI isi app/Models/GcKriteriaParameter.php dengan file ini.
 * Perubahan: kolom `target`, konstanta TARGET, targetBawaan(), scope untukPbi(), dan untukPetugas() kini hanya gc + pbi_gc.
 */
class GcKriteriaParameter extends Model
{
    protected $table = 'gc_kriteria_parameters';

    protected $fillable = [
        'kelompok', 'target', 'urutan', 'nama', 'bobot', 'tipe_input', 'sumber',
        'pbi_parameter_id', 'keterangan', 'aktif', 'wajib',
    ];

    protected $casts = [
        'bobot' => 'float',
        'aktif' => 'boolean',
        'wajib' => 'boolean',
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
        'jenis dinding terluas',
        'jenis atap terluas',
        'sumber air minum utama',
        'sumber penerangan utama',
        'bahan bakar/energi utama untuk memasak',
        'kepemilikan fasilitas bab',
        'pendapatan sebulan terakhir',
        'foto kartu keluarga',
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
        'kependudukan'        => 'A. Kependudukan',
        'pendidikan'          => 'B. Pendidikan',
        'ketenagakerjaan'     => 'C. Ketenagakerjaan',
        'kesehatan'           => 'D. Kesehatan',
        'keluarga'            => 'E. Keluarga',
        'aset_bergerak'       => 'F1. Aset Bergerak',
        'aset_tidak_bergerak' => 'F2. Aset Tidak Bergerak',
        'dokumentasi'         => 'G. Dokumentasi',
    ];

    /** Tab pada form entry Ground Check (sama seperti di aplikasi SOLID). */
    public const TAB = [
        'kependudukan'    => ['label' => 'A. Kependudukan', 'icon' => 'person', 'kelompok' => ['kependudukan']],
        'pendidikan'      => ['label' => 'B. Pendidikan', 'icon' => 'school', 'kelompok' => ['pendidikan']],
        'ketenagakerjaan' => ['label' => 'C. Ketenagakerjaan', 'icon' => 'work', 'kelompok' => ['ketenagakerjaan']],
        'kesehatan'       => ['label' => 'D. Kesehatan', 'icon' => 'medical_services', 'kelompok' => ['kesehatan']],
        'keluarga'        => ['label' => 'E. Keluarga', 'icon' => 'home', 'kelompok' => ['keluarga']],
        'aset'            => ['label' => 'F. Kepemilikan Aset', 'icon' => 'inventory_2', 'kelompok' => ['aset_bergerak', 'aset_tidak_bergerak']],
        'dokumentasi'     => ['label' => 'G. Dokumentasi', 'icon' => 'photo_camera', 'kelompok' => ['dokumentasi']],
    ];

    /** Sub-judul di dalam tab yang memuat lebih dari satu kelompok. */
    public const SUBJUDUL = [
        'aset_bergerak'       => 'Keluarga memiliki aset bergerak sebagai berikut:',
        'aset_tidak_bergerak' => 'Kepemilikan aset tidak bergerak (lahan sawah/kebun):',
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
}