<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property string|null $keterangan
 * @property string $file_path
 * @property string $file_name
 * @property string|null $mime
 * @property int $file_size
 * @property int|null $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $uploader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dokumen whereUploadedBy($value)
 */
	class Dokumen extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $permohonan_id
 * @property string|null $peringkat_kesejahteraan_keluarga
 * @property bool|null $bnpt
 * @property bool|null $pkh
 * @property bool|null $pbi
 * @property bool|null $yatim_piatu
 * @property \Illuminate\Support\Carbon|null $tanggal_cetak
 * @property \Illuminate\Support\Carbon|null $berlaku_sampai
 * @property string|null $dicetak_oleh
 * @property string|null $upload_lainnya
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DtsenPermohonan $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos whereBerlakuSampai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos whereBnpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos whereDicetakOleh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos wherePbi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos wherePeringkatKesejahteraanKeluarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos wherePermohonanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos wherePkh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos whereTanggalCetak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos whereUploadLainnya($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenBansos whereYatimPiatu($value)
 */
	class DtsenBansos extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $permohonan_id
 * @property string|null $nik
 * @property string|null $nama
 * @property string|null $no_kk
 * @property string|null $alamat
 * @property \Illuminate\Support\Carbon|null $tanggal_lahir
 * @property string|null $alasan_keputusan_cetak
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DtsenPermohonan $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail whereAlasanKeputusanCetak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail whereNoKk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail wherePermohonanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail whereTanggalLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenDetail whereUpdatedAt($value)
 */
	class DtsenDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $permohonan_id
 * @property string|null $screenshot_dtsen
 * @property string|null $scan_ktp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DtsenPermohonan $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLampiran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLampiran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLampiran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLampiran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLampiran whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLampiran wherePermohonanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLampiran whereScanKtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLampiran whereScreenshotDtsen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLampiran whereUpdatedAt($value)
 */
	class DtsenLampiran extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $permohonan_id
 * @property \Illuminate\Support\Carbon $tanggal_proses
 * @property int|null $user_id
 * @property string|null $username
 * @property string $taskname
 * @property string|null $rolename
 * @property string|null $catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DtsenPermohonan $permohonan
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog wherePermohonanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog whereRolename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog whereTanggalProses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog whereTaskname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenLog whereUsername($value)
 */
	class DtsenLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $no_permohonan
 * @property int|null $user_id
 * @property string $nik
 * @property string $nama_pemohon
 * @property string|null $alamat
 * @property string|null $alasan_cetak
 * @property string $status
 * @property int|null $current_role_id
 * @property string|null $kelurahan
 * @property \Illuminate\Support\Carbon $tanggal_insert
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DtsenBansos|null $bansos
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Role|null $currentRole
 * @property-read \App\Models\DtsenDetail|null $detail
 * @property-read \App\Models\DtsenLampiran|null $lampiran
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DtsenLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\DtsenSurat|null $surat
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereAlasanCetak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereCurrentRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereNamaPemohon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereNoPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereTanggalInsert($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenPermohonan whereUserId($value)
 */
	class DtsenPermohonan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $permohonan_id
 * @property string|null $surat_pengantar_dtsen_kelurahan_digital
 * @property \Illuminate\Support\Carbon|null $surat_pengantar_dtsen_kelurahan_digital_at
 * @property string|null $surat_keterangan_dtsen_digital
 * @property \Illuminate\Support\Carbon|null $surat_keterangan_dtsen_digital_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DtsenPermohonan $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat wherePermohonanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat whereSuratKeteranganDtsenDigital($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat whereSuratKeteranganDtsenDigitalAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat whereSuratPengantarDtsenKelurahanDigital($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat whereSuratPengantarDtsenKelurahanDigitalAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DtsenSurat whereUpdatedAt($value)
 */
	class DtsenSurat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $verifikasi_id
 * @property int|null $parameter_id
 * @property string $nama_parameter
 * @property float $bobot
 * @property string|null $jawaban
 * @property float|null $skor
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GcKriteriaParameter|null $parameter
 * @property-read \App\Models\GcVerifikasi $verifikasi
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban whereBobot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban whereJawaban($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban whereNamaParameter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban whereParameterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban whereSkor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcJawaban whereVerifikasiId($value)
 */
	class GcJawaban extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $urutan
 * @property string $nama
 * @property bool $eksklusif
 * @property bool $aktif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan aktif()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan whereEksklusif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKeterampilan whereUrutan($value)
 */
	class GcKeterampilan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $parameter_id
 * @property string $label
 * @property float|null $nilai
 * @property int $urutan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GcKriteriaParameter $parameter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaOpsi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaOpsi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaOpsi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaOpsi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaOpsi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaOpsi whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaOpsi whereNilai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaOpsi whereParameterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaOpsi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaOpsi whereUrutan($value)
 */
	class GcKriteriaOpsi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $kelompok
 * @property int $urutan
 * @property string $nama
 * @property float $bobot
 * @property string $tipe_input
 * @property string|null $sumber
 * @property int|null $pbi_parameter_id
 * @property string|null $keterangan
 * @property bool $aktif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GcKriteriaOpsi> $opsis
 * @property-read int|null $opsis_count
 * @property-read \App\Models\PbiKriteriaParameter|null $pbiParameter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter aktif()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter untukMasyarakat()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereBobot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereKelompok($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter wherePbiParameterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereSumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereTipeInput($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcKriteriaParameter whereUrutan($value)
 */
	class GcKriteriaParameter extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $urutan
 * @property string $nama
 * @property string|null $ikon
 * @property string|null $penjelasan
 * @property string|null $contoh_berusaha_sendiri
 * @property string|null $contoh_buruh
 * @property bool $aktif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GcPekerjaan> $pekerjaans
 * @property-read int|null $pekerjaans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GcPekerjaan> $pekerjaansAktif
 * @property-read int|null $pekerjaans_aktif_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha aktif()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha whereContohBerusahaSendiri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha whereContohBuruh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha whereIkon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha wherePenjelasan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcLapanganUsaha whereUrutan($value)
 */
	class GcLapanganUsaha extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $lapangan_usaha_id
 * @property string $nama
 * @property int $urutan
 * @property bool $aktif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GcLapanganUsaha $lapanganUsaha
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPekerjaan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPekerjaan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPekerjaan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPekerjaan whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPekerjaan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPekerjaan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPekerjaan whereLapanganUsahaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPekerjaan whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPekerjaan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPekerjaan whereUrutan($value)
 */
	class GcPekerjaan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $urutan
 * @property string $nama
 * @property bool $aktif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPenyakitKronis aktif()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPenyakitKronis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPenyakitKronis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPenyakitKronis query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPenyakitKronis whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPenyakitKronis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPenyakitKronis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPenyakitKronis whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPenyakitKronis whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcPenyakitKronis whereUrutan($value)
 */
	class GcPenyakitKronis extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nik
 * @property string $nama
 * @property string $no_kk
 * @property \Illuminate\Support\Carbon|null $tanggal_lahir
 * @property string|null $kecamatan
 * @property string|null $kelurahan
 * @property string|null $alamat
 * @property string|null $rt
 * @property string|null $rw
 * @property int|null $desil
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GcVerifikasi> $verifikasis
 * @property-read int|null $verifikasis_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereDesil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereKecamatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereNoKk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereRt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereRw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereTanggalLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcSasaran whereUpdatedAt($value)
 */
	class GcSasaran extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sasaran_id
 * @property int $tahun
 * @property int|null $user_id
 * @property string $status_temuan
 * @property string $status_entry
 * @property bool $pernyataan
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GcJawaban> $jawabans
 * @property-read int|null $jawabans_count
 * @property-read \App\Models\User|null $petugas
 * @property-read \App\Models\GcSasaran $sasaran
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi wherePernyataan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi whereSasaranId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi whereStatusEntry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi whereStatusTemuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GcVerifikasi whereUserId($value)
 */
	class GcVerifikasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $permohonan_id
 * @property string $nik
 * @property string $nama
 * @property string $jenis_kelamin
 * @property string|null $tempat_lahir
 * @property \Illuminate\Support\Carbon|null $tanggal_lahir
 * @property string|null $agama
 * @property string|null $status_perkawinan
 * @property string|null $no_pkh
 * @property string|null $no_kk
 * @property string|null $no_kartu_kks
 * @property string|null $no_rekening
 * @property string|null $pekerjaan
 * @property string|null $alamat
 * @property string|null $masalah_kartu
 * @property string|null $nomor_kehilangan_polisi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KartuKksPermohonan $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereAgama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereJenisKelamin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereMasalahKartu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereNoKartuKks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereNoKk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereNoPkh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereNoRekening($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereNomorKehilanganPolisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail wherePekerjaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail wherePermohonanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereStatusPerkawinan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereTanggalLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereTempatLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksDetail whereUpdatedAt($value)
 */
	class KartuKksDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $permohonan_id
 * @property string|null $scan_ktp
 * @property string|null $scan_kartu_keluarga
 * @property string|null $surat_kehilangan_polisi
 * @property string|null $scan_kartu_kks
 * @property string|null $screenshot_dtsen
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KartuKksPermohonan $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran wherePermohonanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran whereScanKartuKeluarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran whereScanKartuKks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran whereScanKtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran whereScreenshotDtsen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran whereSuratKehilanganPolisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLampiran whereUpdatedAt($value)
 */
	class KartuKksLampiran extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $permohonan_id
 * @property \Illuminate\Support\Carbon $tanggal_proses
 * @property int|null $user_id
 * @property string|null $username
 * @property string $taskname
 * @property string|null $rolename
 * @property string|null $catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KartuKksPermohonan $permohonan
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog wherePermohonanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog whereRolename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog whereTanggalProses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog whereTaskname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksLog whereUsername($value)
 */
	class KartuKksLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $no_permohonan
 * @property int|null $user_id
 * @property string $nik
 * @property string $nama_pemohon
 * @property string|null $alamat
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $masalah_kartu
 * @property string|null $nomor_kehilangan_polisi
 * @property string $status
 * @property int|null $current_role_id
 * @property string|null $kelurahan
 * @property \Illuminate\Support\Carbon $tanggal_insert
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Role|null $currentRole
 * @property-read \App\Models\KartuKksDetail|null $detail
 * @property-read \App\Models\KartuKksLampiran|null $lampiran
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\KartuKksLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\KartuKksSurat|null $surat
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereCurrentRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereMasalahKartu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereNamaPemohon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereNoPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereNomorKehilanganPolisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereTanggalInsert($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksPermohonan whereUserId($value)
 */
	class KartuKksPermohonan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $permohonan_id
 * @property string|null $surat_pengantar_kelurahan
 * @property \Illuminate\Support\Carbon|null $surat_pengantar_kelurahan_at
 * @property string|null $surat_keterangan_dinsos
 * @property \Illuminate\Support\Carbon|null $surat_keterangan_dinsos_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KartuKksPermohonan $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat wherePermohonanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat whereSuratKeteranganDinsos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat whereSuratKeteranganDinsosAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat whereSuratPengantarKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat whereSuratPengantarKelurahanAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KartuKksSurat whereUpdatedAt($value)
 */
	class KartuKksSurat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $nama_layanan
 * @property string $no_registrasi
 * @property string $no_kk
 * @property string $nama_kepala_keluarga
 * @property string $nik_kepala_keluarga
 * @property string $alamat
 * @property string|null $rt
 * @property string|null $rw
 * @property string|null $provinsi
 * @property string|null $kabupaten_kota
 * @property string|null $kecamatan
 * @property string|null $desa_kelurahan
 * @property string|null $no_telp
 * @property string|null $email
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $desil_nasional
 * @property string|null $screenshot_pembaharuan_desil
 * @property string|null $kelengkapan_kekurangan_berkas
 * @property string|null $catatan_berkas
 * @property string|null $screenshot_dtsen
 * @property string|null $diagnosa
 * @property string|null $scan_ktp
 * @property string|null $scan_kk
 * @property string|null $foto_rumah
 * @property string|null $foto_kamar_mandi
 * @property string|null $foto_selfie_ktp
 * @property string|null $surat_rawat_inap
 * @property string|null $status_penguasaan_bangunan
 * @property string|null $status_lahan
 * @property string|null $penghasilan_rata_rata
 * @property bool|null $punya_kendaraan_roda_2
 * @property int|null $jumlah_tanggungan_keluarga
 * @property string|null $jenis_lantai
 * @property string|null $jenis_dinding
 * @property string|null $kondisi_dinding
 * @property string|null $jenis_atap
 * @property string|null $kondisi_atap
 * @property string|null $sumber_air_minum
 * @property string|null $cara_memperoleh_air_minum
 * @property string|null $sumber_penerangan_utama
 * @property string|null $daya_terpasang
 * @property string|null $bahan_bakar_energi_memasak
 * @property string|null $penggunaan_fasilitas_bab
 * @property string|null $jenis_kloset
 * @property string|null $tempat_pembuangan_akhir_tinja
 * @property string|null $kesimpulan_rekomendasi
 * @property string|null $scan_surat_pengantar
 * @property string|null $surat_pengantar_digital
 * @property bool $sudah_diverifikasi_kelurahan
 * @property string|null $nama_faskes
 * @property string $status
 * @property string|null $catatan_internal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property numeric|null $skor_kumulatif
 * @property int|null $kelas_kemiskinan
 * @property \Illuminate\Support\Carbon|null $data_diisi_at
 * @property bool $dikembalikan_ke_masyarakat
 * @property string|null $catatan_kelurahan
 * @property \Illuminate\Support\Carbon|null $divalidasi_kelurahan_at
 * @property int|null $divalidasi_kelurahan_oleh
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PbiApbdAnggotaKeluarga> $anggotaKeluarga
 * @property-read int|null $anggota_keluarga_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PbiApbdDiagnosaLog> $diagnosaLogs
 * @property-read int|null $diagnosa_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PbiApbdJawaban> $jawabans
 * @property-read int|null $jawabans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PbiApbdLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd untukKelurahan(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereBahanBakarEnergiMemasak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereCaraMemperolehAirMinum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereCatatanBerkas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereCatatanInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereCatatanKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereDataDiisiAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereDayaTerpasang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereDesaKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereDesilNasional($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereDiagnosa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereDikembalikanKeMasyarakat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereDivalidasiKelurahanAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereDivalidasiKelurahanOleh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereFotoKamarMandi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereFotoRumah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereFotoSelfieKtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereJenisAtap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereJenisDinding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereJenisKloset($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereJenisLantai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereJumlahTanggunganKeluarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereKabupatenKota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereKecamatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereKelasKemiskinan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereKelengkapanKekuranganBerkas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereKesimpulanRekomendasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereKondisiAtap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereKondisiDinding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereNamaFaskes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereNamaKepalaKeluarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereNamaLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereNikKepalaKeluarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereNoKk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereNoRegistrasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereNoTelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd wherePenggunaanFasilitasBab($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd wherePenghasilanRataRata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereProvinsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd wherePunyaKendaraanRoda2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereRt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereRw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereScanKk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereScanKtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereScanSuratPengantar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereScreenshotDtsen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereScreenshotPembaharuanDesil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereSkorKumulatif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereStatusLahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereStatusPenguasaanBangunan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereSudahDiverifikasiKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereSumberAirMinum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereSumberPeneranganUtama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereSuratPengantarDigital($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereSuratRawatInap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereTempatPembuanganAkhirTinja($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbd whereUserId($value)
 */
	class PbiApbd extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $pbi_apbd_id
 * @property string $nik
 * @property string $nama
 * @property string|null $tempat_lahir
 * @property \Illuminate\Support\Carbon|null $tanggal_lahir
 * @property string|null $pekerjaan
 * @property string|null $agama
 * @property string|null $jenis_kelamin
 * @property string|null $hubungan_keluarga
 * @property string|null $keanggotaan_bpjs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PbiApbd $pbiApbd
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereAgama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereHubunganKeluarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereJenisKelamin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereKeanggotaanBpjs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga wherePbiApbdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga wherePekerjaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereTanggalLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereTempatLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdAnggotaKeluarga whereUpdatedAt($value)
 */
	class PbiApbdAnggotaKeluarga extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $pbi_apbd_id
 * @property int|null $user_id
 * @property string|null $username
 * @property string|null $role_name
 * @property string $diagnosa
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PbiApbd $pbiApbd
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog whereDiagnosa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog wherePbiApbdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog whereRoleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdDiagnosaLog whereUsername($value)
 */
	class PbiApbdDiagnosaLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $pbi_apbd_id
 * @property int|null $parameter_id
 * @property int|null $opsi_id
 * @property int $urutan
 * @property string $parameter_nama
 * @property string $jawaban_label
 * @property numeric $indeks
 * @property numeric $bobot
 * @property numeric $indeks_terintegrasi
 * @property numeric $skor
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PbiKriteriaOpsi|null $opsi
 * @property-read \App\Models\PbiKriteriaParameter|null $parameter
 * @property-read \App\Models\PbiApbd $pbiApbd
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereBobot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereIndeks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereIndeksTerintegrasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereJawabanLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereOpsiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereParameterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereParameterNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban wherePbiApbdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereSkor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdJawaban whereUrutan($value)
 */
	class PbiApbdJawaban extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $pbi_apbd_id
 * @property int|null $user_id
 * @property string|null $username
 * @property string|null $role_name
 * @property string $task_name
 * @property string|null $catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PbiApbd $pbiApbd
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog wherePbiApbdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog whereRoleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog whereTaskName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiApbdLog whereUsername($value)
 */
	class PbiApbdLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $parameter_id
 * @property string $label
 * @property numeric $indeks_terintegrasi
 * @property int $urutan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PbiKriteriaParameter $parameter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaOpsi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaOpsi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaOpsi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaOpsi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaOpsi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaOpsi whereIndeksTerintegrasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaOpsi whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaOpsi whereParameterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaOpsi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaOpsi whereUrutan($value)
 */
	class PbiKriteriaOpsi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $urutan
 * @property string $nama
 * @property numeric $bobot
 * @property numeric $indeks
 * @property bool $aktif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PbiKriteriaOpsi> $opsis
 * @property-read int|null $opsis_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter aktif()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter whereBobot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter whereIndeks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PbiKriteriaParameter whereUrutan($value)
 */
	class PbiKriteriaParameter extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property array<array-key, mixed>|null $permissions
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $laporan_id
 * @property int|null $petugas_id
 * @property string $nama_petugas
 * @property string|null $nomor_nota_dinas
 * @property \Illuminate\Support\Carbon $tanggal_assessment
 * @property string $hasil_assessment
 * @property string $tembusan
 * @property \Illuminate\Support\Carbon|null $ttd_tagana_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SigapLaporan $laporan
 * @property-read \App\Models\User|null $petugas
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment whereHasilAssessment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment whereLaporanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment whereNamaPetugas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment whereNomorNotaDinas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment wherePetugasId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment whereTanggalAssessment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment whereTembusan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment whereTtdTaganaAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapAssessment whereUpdatedAt($value)
 */
	class SigapAssessment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $kode
 * @property string $nama
 * @property string $satuan
 * @property int $stok
 * @property int $stok_minimum
 * @property bool $aktif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SigapStokMutasi> $mutasi
 * @property-read int|null $mutasi_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang aktif()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang whereSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang whereStok($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang whereStokMinimum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBarang whereUpdatedAt($value)
 */
	class SigapBarang extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $laporan_id
 * @property string|null $nomor_bast
 * @property \Illuminate\Support\Carbon $tanggal_terima
 * @property int|null $penerima_id
 * @property string $nama_penerima
 * @property string $jabatan_penerima
 * @property string|null $instansi_penerima
 * @property string|null $catatan
 * @property \Illuminate\Support\Carbon|null $ttd_penerima_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SigapLaporan $laporan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereInstansiPenerima($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereJabatanPenerima($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereLaporanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereNamaPenerima($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereNomorBast($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast wherePenerimaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereTanggalTerima($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereTtdPenerimaAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapBast whereUpdatedAt($value)
 */
	class SigapBast extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $laporan_id
 * @property string $path
 * @property string|null $keterangan
 * @property int|null $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SigapLaporan $laporan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapDokumentasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapDokumentasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapDokumentasi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapDokumentasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapDokumentasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapDokumentasi whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapDokumentasi whereLaporanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapDokumentasi wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapDokumentasi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapDokumentasi whereUploadedBy($value)
 */
	class SigapDokumentasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $laporan_id
 * @property int $barang_id
 * @property string $nama_barang
 * @property string $satuan
 * @property int $jumlah_dibutuhkan
 * @property int|null $jumlah_disetujui
 * @property int|null $jumlah_keluar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SigapBarang $barang
 * @property-read \App\Models\SigapLaporan $laporan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem whereJumlahDibutuhkan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem whereJumlahDisetujui($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem whereJumlahKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem whereLaporanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem whereNamaBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem whereSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapItem whereUpdatedAt($value)
 */
	class SigapItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $no_tiket
 * @property string|null $nomor_surat_laporan
 * @property int|null $user_id
 * @property string|null $nama_pelapor
 * @property string|null $peran_pelapor
 * @property string $jenis_bencana
 * @property \Illuminate\Support\Carbon $tanggal_kejadian
 * @property int $jumlah_korban
 * @property string|null $no_kk
 * @property string|null $kerusakan
 * @property string|null $kerugian
 * @property string $bantuan_dibutuhkan
 * @property string $kecamatan
 * @property string $kelurahan
 * @property string|null $rt
 * @property string|null $rw
 * @property string $alamat
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property \Illuminate\Support\Carbon|null $lokasi_diperbarui_at
 * @property string $status
 * @property int|null $current_role_id
 * @property \Illuminate\Support\Carbon|null $ditutup_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SigapAssessment|null $assessment
 * @property-read \App\Models\SigapBast|null $bast
 * @property-read \App\Models\Role|null $currentRole
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SigapDokumentasi> $dokumentasi
 * @property-read int|null $dokumentasi_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SigapItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SigapLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\User|null $pelapor
 * @property-read \App\Models\SigapPengeluaran|null $pengeluaran
 * @property-read \App\Models\SigapPermohonanBarang|null $permohonanBarang
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereBantuanDibutuhkan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereCurrentRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereDitutupAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereJenisBencana($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereJumlahKorban($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereKecamatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereKerugian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereKerusakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereLokasiDiperbaruiAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereNamaPelapor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereNoKk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereNoTiket($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereNomorSuratLaporan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan wherePeranPelapor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereRt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereRw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereTanggalKejadian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLaporan whereUserId($value)
 */
	class SigapLaporan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $laporan_id
 * @property \Illuminate\Support\Carbon $tanggal_proses
 * @property int|null $user_id
 * @property string|null $username
 * @property string $taskname
 * @property string|null $rolename
 * @property string|null $catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SigapLaporan $laporan
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog whereLaporanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog whereRolename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog whereTanggalProses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog whereTaskname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapLog whereUsername($value)
 */
	class SigapLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $laporan_id
 * @property string|null $nomor_surat
 * @property \Illuminate\Support\Carbon $tanggal
 * @property int|null $pengurus_barang_id
 * @property string $nama_pengurus_barang
 * @property \Illuminate\Support\Carbon|null $ttd_pengurus_at
 * @property int|null $kadis_id
 * @property string|null $nama_kadis
 * @property \Illuminate\Support\Carbon|null $ttd_kadis_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SigapLaporan $laporan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereKadisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereLaporanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereNamaKadis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereNamaPengurusBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereNomorSurat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran wherePengurusBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereTtdKadisAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereTtdPengurusAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPengeluaran whereUpdatedAt($value)
 */
	class SigapPengeluaran extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $laporan_id
 * @property string|null $nomor_surat
 * @property \Illuminate\Support\Carbon $tanggal
 * @property int|null $verifikator_id
 * @property string $nama_verifikator
 * @property string $hasil_verifikasi
 * @property \Illuminate\Support\Carbon|null $ttd_perlinsos_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SigapLaporan $laporan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang whereHasilVerifikasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang whereLaporanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang whereNamaVerifikator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang whereNomorSurat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang whereTtdPerlinsosAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapPermohonanBarang whereVerifikatorId($value)
 */
	class SigapPermohonanBarang extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $barang_id
 * @property string $jenis
 * @property int $jumlah
 * @property int $saldo_setelah
 * @property int|null $laporan_id
 * @property string|null $keterangan
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SigapBarang $barang
 * @property-read \App\Models\SigapLaporan|null $laporan
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi whereLaporanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi whereSaldoSetelah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SigapStokMutasi whereUserId($value)
 */
	class SigapStokMutasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property int|null $role_id
 * @property string|null $kecamatan
 * @property string|null $kelurahan
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $default_password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DtsenPermohonan> $pengajuanDtsen
 * @property-read int|null $pengajuan_dtsen_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\KartuKksPermohonan> $pengajuanKartuKks
 * @property-read int|null $pengajuan_kartu_kks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PbiApbd> $pengajuanPbiApbd
 * @property-read int|null $pengajuan_pbi_apbd_count
 * @property-read \App\Models\Role|null $role
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDefaultPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereKecamatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 */
	class User extends \Eloquent {}
}

