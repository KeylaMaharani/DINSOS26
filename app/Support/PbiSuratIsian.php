<?php

namespace App\Support;

use App\Models\PbiApbd;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Menyiapkan nilai placeholder ${...} untuk
 *   - Surat Pengantar Kelurahan  (diunggah petugas Kelurahan)
 *   - Surat Keterangan / Suket   (diunggah Operator Dinsos)
 * lalu mengisikannya ke file .docx yang diunggah (lihat DocxTemplate).
 *
 * Semua placeholder ada di satu tempat (DAFTAR + nilai()), jadi kalau mau
 * menambah/ubah tinggal edit file ini.
 */
class PbiSuratIsian
{
    /** placeholder => keterangan (juga ditampilkan di halaman sebagai contekan) */
    public const DAFTAR = [
        'nama_pemohon' => 'Nama kepala keluarga',
        'nik' => 'NIK kepala keluarga',
        'no_kk' => 'Nomor KK',
        'ttl' => 'Tempat, tanggal lahir (dari data anggota keluarga)',
        'jenis_kelamin' => 'Jenis kelamin (dari data anggota keluarga)',
        'pekerjaan' => 'Pekerjaan (dari data anggota keluarga)',
        'alamat_pemohon' => 'Alamat + RT/RW (alias: alamat)',
        'desil' => 'Desil nasional',
        'kel' => 'Nama kelurahan (alias: kelurahan)',
        'kec' => 'Nama kecamatan (alias: kecamatan)',
        'kel_up' => 'KELURAHAN huruf kapital',
        'kec_up' => 'KECAMATAN huruf kapital',
        'alamat_kelurahan' => 'Alamat kantor kelurahan',
        'no_sk' => 'Nomor surat (isi di kolom Nomor Surat; kosong = No. Registrasi)',
        'tgl_sk' => 'Tanggal hari ini, contoh 4 November 2026',
        'ditujukan_kepada' => 'Tujuan surat (alias: ditujukan_kpada)',
        'nama_faskes' => 'Nama faskes',
        'no_registrasi' => 'Nomor registrasi pengajuan',
        'no_telp' => 'Nomor telepon pemohon',
        'kel_ttd' => 'LURAH + nama kelurahan (blok tanda tangan)',
        'ttd_nama_lurah' => 'Nama lengkap petugas yang login/menyimpan',
        'jabatan_lurah' => 'Jabatan petugas (dari akunnya; kosong = Lurah)',
        'no_kks' => 'No. KKS (isi di form Operator)',
        'no_rek' => 'No. rekening (isi di form Operator)',
        'no_sktm' => 'Nomor surat pengantar Kelurahan (isi di form Operator)',
        'masalah_kartu' => 'Masalah kartu, mis. hilang/rusak (isi di form Operator)',
        'redaksi' => 'Redaksi permohonan ke Bank (isi di form Operator)',
        'nama_lengkap_kadin' => 'Nama Kepala Dinas (dari akun user Kepala Dinas)',
        'pangkat_kadin' => 'Pangkat Kepala Dinas (dari akun user Kepala Dinas)',
        'nip_kadin' => 'NIP Kepala Dinas (dari akun user Kepala Dinas)',
        'pangkat_lurah' => 'Pangkat petugas kelurahan yang menyimpan (dari akunnya)',
        'nip_lurah' => 'NIP petugas kelurahan yang menyimpan (dari akunnya)',
    ];

    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public static function tanggalId(mixed $tgl): string
    {
        if (! $tgl instanceof CarbonInterface) {
            return '';
        }

        return $tgl->day . ' ' . self::BULAN[$tgl->month] . ' ' . $tgl->year;
    }

    /** @return array<string, string> */
    public static function nilai(PbiApbd $m, $user, ?string $nomor = null, array $extra = []): array
    {
        $m->loadMissing('anggotaKeluarga');

        // Kepala keluarga di daftar anggota: cocokkan NIK, cadangan: hubungan "kepala"
        $kepala = $m->anggotaKeluarga->first(fn ($a) => (string) $a->nik === (string) $m->nik_kepala_keluarga)
            ?? $m->anggotaKeluarga->first(fn ($a) => Str::contains(Str::lower((string) $a->hubungan_keluarga), 'kepala'));

        $strip = fn ($v) => filled($v) ? trim((string) $v) : '-';

        $kel = trim((string) ($m->desa_kelurahan ?: data_get($user, 'kelurahan')));
        $kec = trim((string) ($m->kecamatan ?: data_get($user, 'kecamatan')));

        $tempat = trim((string) data_get($kepala, 'tempat_lahir'));
        $tglLahir = self::tanggalId(data_get($kepala, 'tanggal_lahir'));
        $ttl = trim($tempat . ($tempat !== '' && $tglLahir !== '' ? ', ' : '') . $tglLahir);

        $jk = strtoupper(trim((string) data_get($kepala, 'jenis_kelamin')));
        $jk = match (true) {
            in_array($jk, ['L', 'LAKI-LAKI', 'LAKI LAKI', 'LAKI'], true) => 'Laki-laki',
            in_array($jk, ['P', 'PEREMPUAN', 'WANITA'], true) => 'Perempuan',
            default => $jk !== '' ? Str::title(Str::lower($jk)) : '',
        };

        $alamat = trim((string) $m->alamat);
        if (filled($m->rt) || filled($m->rw)) {
            $alamat .= ' RT ' . ($m->rt ?: '-') . ' / RW ' . ($m->rw ?: '-');
        }

        // Isian manual dari form (Operator) menang atas data bawaan
        $ex = fn (string $k, $default = null) => filled($extra[$k] ?? null) ? trim((string) $extra[$k]) : $default;

        $kadis = self::penandatanganKadis();

        $nomorSurat = filled($nomor) ? trim($nomor) : (string) $m->no_registrasi;

        return [
            'nama_pemohon' => $strip($m->nama_kepala_keluarga),
            'nik' => $strip($m->nik_kepala_keluarga),
            'no_kk' => $strip($m->no_kk),
            'ttl' => $strip($ttl),
            'jenis_kelamin' => $strip($jk),
            'pekerjaan' => $strip(data_get($kepala, 'pekerjaan')),
            'alamat_pemohon' => $strip($alamat),
            'alamat' => $strip($alamat),
            'desil' => $strip($m->desil_nasional),
            'kel' => $kel,
            'kelurahan' => $kel,
            'kec' => $kec,
            'kecamatan' => $kec,
            'kel_up' => Str::upper($kel),
            'kec_up' => Str::upper($kec),
            // Alamat kantor kelurahan: ambil dari akun petugas bila ada kolomnya, kalau tidak dikosongkan
            'alamat_kelurahan' => (string) (config('pbi_surat.alamat_kelurahan.' . Str::upper($kel)) ?: data_get($user, 'alamat_kelurahan') ?: ''),
            'no_sk' => $nomorSurat,
            'tgl_sk' => self::tanggalId(now()),
            'ditujukan_kepada' => $strip($ex('ditujukan_kepada')),
            'ditujukan_kpada' => $strip($ex('ditujukan_kepada')), // salah ketik di template SK_KKS.docx
            'nama_faskes' => $strip($m->nama_faskes),
            'no_registrasi' => $strip($m->no_registrasi),
            'no_telp' => $strip($m->no_telp),
            'kel_ttd' => 'LURAH ' . Str::upper($kel),
            'ttd_nama_lurah' => (string) data_get($user, 'name', ''),
            'jabatan_lurah' => (string) (data_get($user, 'jabatan') ?: 'Lurah'),
            'pangkat_lurah' => $strip(data_get($user, 'pangkat')),
            'nip_lurah' => $strip(data_get($user, 'nip')),
            'no_kks' => $strip($ex('no_kks', data_get($m, 'no_kks'))),
            'no_rek' => $strip($ex('no_rek', data_get($m, 'no_rek'))),
            'no_sktm' => $strip($ex('no_sktm')),
            'masalah_kartu' => $strip($ex('masalah_kartu')),
            'redaksi' => $strip($ex('redaksi')),
            'nama_lengkap_kadin' => $strip(data_get($kadis, 'name')),
            'pangkat_kadin' => $strip(data_get($kadis, 'pangkat')),
            'nip_kadin' => $strip(data_get($kadis, 'nip')),
        ];
    }

    /**
     * Akun Kepala Dinas = user yang role-nya punya modul "pbi-kadis" (Approval PBI APBD: Kepala Dinas).
     * Kalau ada beberapa (mis. Plt), dipakai yang NIP-nya terisi dan akunnya paling baru dibuat.
     * Data (nama lengkap, pangkat, NIP) diisi di menu Kelola User & Role.
     */
    public static function penandatanganKadis(): ?User
    {
        $roleIds = Role::query()->get()
            ->filter(fn (Role $r) => ! $r->isSuperAdmin() && in_array('pbi-kadis', $r->permissions ?? [], true))
            ->pluck('id');

        if ($roleIds->isEmpty()) {
            return null;
        }

        return User::whereIn('role_id', $roleIds)
            ->orderByRaw("(nip IS NULL OR nip = '') asc")
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Isi placeholder pada file yang BARU disimpan (path relatif disk "public").
     * Hanya .docx yang diproses; PDF/gambar dibiarkan. Tidak pernah melempar error
     * (kegagalan hanya dicatat), supaya unggahan tetap tersimpan.
     *
     * @return string pesan singkat untuk ditampilkan ke petugas ('' bila tidak perlu)
     */
    public static function isiBerkas(PbiApbd $m, ?string $pathRelatif, $user, ?string $nomor = null, array $extra = []): string
    {
        if (blank($pathRelatif)) {
            return '';
        }

        $ext = strtolower(pathinfo($pathRelatif, PATHINFO_EXTENSION));

        if ($ext === 'doc') {
            return 'Catatan: format .doc lama tidak bisa diisi otomatis. Simpan ulang sebagai .docx agar placeholder terisi.';
        }
        if ($ext !== 'docx') {
            return '';
        }

        try {
            $hasil = DocxTemplate::isi(Storage::disk('public')->path($pathRelatif), self::nilai($m, $user, $nomor, $extra));
        } catch (Throwable $e) {
            Log::warning('Gagal mengisi placeholder surat: ' . $e->getMessage(), ['path' => $pathRelatif]);

            return 'Peringatan: file tersimpan, tetapi placeholder gagal diisi otomatis (' . $e->getMessage() . ').';
        }

        if ($hasil['terisi'] === 0 && ! $hasil['tidak_dikenal']) {
            return 'Catatan: tidak ditemukan placeholder ${...} pada file Word.';
        }

        $pesan = "{$hasil['terisi']} isian surat terisi otomatis.";
        if ($hasil['tidak_dikenal']) {
            $pesan .= ' Placeholder tidak dikenal (dibiarkan): ' . implode(', ', array_map(fn ($k) => '${' . $k . '}', $hasil['tidak_dikenal'])) . '.';
        }

        return $pesan;
    }
}