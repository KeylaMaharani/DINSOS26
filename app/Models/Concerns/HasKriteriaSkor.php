<?php

namespace App\Models\Concerns;

use App\Models\PbiApbnJawaban;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Dipasang di App\Models\PbiApbn:  use HasKriteriaSkor;
 * Berisi logika baru: jawaban parameter, perhitungan skor (SK Wali Kota
 * 460/Kep.196-Dinsos/2021), dan pembatasan wilayah kelurahan.
 */
trait HasKriteriaSkor
{
    public function initializeHasKriteriaSkor(): void
    {
        $this->mergeCasts([
            'skor_kumulatif' => 'decimal:2',
            'kelas_kemiskinan' => 'integer',
            'data_diisi_at' => 'datetime',
            'divalidasi_kelurahan_at' => 'datetime',
            'dikembalikan_ke_masyarakat' => 'boolean',
        ]);
    }

    public function jawabans(): HasMany
    {
        return $this->hasMany(PbiApbnJawaban::class);
    }

    /** Masyarakat boleh mengisi/mengubah data selama berkas masih di Kelurahan. */
    public function masyarakatBisaEdit(): bool
    {
        return $this->status === 'kelurahan';
    }

    /**
     * Indeks Kumulatif = Σ (Indeks x Bobot x Indeks Terintegrasi), dibulatkan 2 desimal.
     * Dihitung dengan integer (mikro-poin) supaya pembulatan .xx5 tidak meleset akibat float.
     */
    public function hitungUlangSkor(): void
    {
        $jawabans = $this->jawabans()->get();

        if ($jawabans->isEmpty()) {
            $this->forceFill(['skor_kumulatif' => null, 'kelas_kemiskinan' => null])->save();
            return;
        }

        $micro = $jawabans->sum(fn ($j) => (int) round(((float) $j->skor) * 1000000));
        $senti = intdiv($micro + 5000, 10000); // pembulatan half-up ke 0,01

        $this->forceFill([
            'skor_kumulatif' => $senti / 100,
            'kelas_kemiskinan' => self::kelasDariSenti($senti),
        ])->save();
    }

    /** Kelas 1: 0,50-0,99 | Kelas 2: 1,00-1,99 | Kelas 3: 2,00-3,00 | selain itu: null */
    public static function kelasDariSenti(int $senti): ?int
    {
        return match (true) {
            $senti >= 50 && $senti <= 99 => 1,
            $senti >= 100 && $senti <= 199 => 2,
            $senti >= 200 && $senti <= 300 => 3,
            default => null,
        };
    }

    public static function kelasLabels(): array
    {
        return [
            1 => 'Kelas 1 (Sangat Miskin)',
            2 => 'Kelas 2 (Miskin)',
            3 => 'Kelas 3 (Rentan Miskin)',
        ];
    }

    public function kelasLabel(): string
    {
        if ($this->skor_kumulatif === null) {
            return 'Belum dihitung';
        }

        return self::kelasLabels()[$this->kelas_kemiskinan] ?? 'Di luar kriteria (> 3,00)';
    }

    /**
     * Batasi data ke wilayah petugas kelurahan. Super Admin melihat semua.
     * Akun kelurahan yang belum diisi wilayahnya tidak melihat apa pun.
     */
    public function scopeUntukKelurahan(Builder $query, User $user): Builder
    {
        if ($user->role?->isSuperAdmin()) {
            return $query;
        }

        $kel = mb_strtolower(trim((string) $user->kelurahan));
        if ($kel === '') {
            return $query->whereRaw('1 = 0');
        }

        $query->whereRaw('LOWER(TRIM(desa_kelurahan)) = ?', [$kel]);

        if (filled($user->kecamatan)) {
            $query->whereRaw('LOWER(TRIM(kecamatan)) = ?', [mb_strtolower(trim($user->kecamatan))]);
        }

        return $query;
    }
}
