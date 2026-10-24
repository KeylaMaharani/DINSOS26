<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GcKriteriaParameter extends Model
{
    protected $table = 'gc_kriteria_parameters';

    protected $fillable = [
        'kelompok', 'urutan', 'nama', 'bobot', 'tipe_input', 'sumber',
        'pbi_parameter_id', 'keterangan', 'aktif',
    ];

    protected $casts = [
        'bobot' => 'float',
        'aktif' => 'boolean',
    ];

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

    public function butuhOpsi(): bool
    {
        // Parameter bersumber master tidak memakai gc_kriteria_opsis.
        return ! $this->sumber && in_array($this->tipe_input, ['pilihan', 'pilihan_ganda'], true);
    }
}
