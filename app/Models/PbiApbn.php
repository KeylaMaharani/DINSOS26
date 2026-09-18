<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbiApbn extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'punya_kendaraan_roda_2' => 'boolean',
        'sudah_diverifikasi_kelurahan' => 'boolean',
    ];

    // ==========================================================
    // ALUR STATUS INTERNAL DINSOS
    // Urutan linear: kelurahan -> operator_dinsos -> kabid -> kadis -> disetujui
    // "ditolak" adalah status akhir terpisah (bisa terjadi dari tahap manapun)
    // ==========================================================
    public const OPERATOR_ROLE = 'dayasos';

    public const STAGES = [
        'kelurahan' => [
            'label' => 'Kelurahan',
            'role' => 'kelurahan', // role yang bertanggung jawab
        ],
        'operator_dinsos' => [
            'label' => 'Operator Dinsos',
            'role' => self::OPERATOR_ROLE,
        ],
        'kabid' => [
            'label' => 'Kepala Bidang',
            'role' => 'kabin',
        ],
        'kadis' => [
            'label' => 'Kepala Dinas',
            'role' => 'kadis',
        ],
    ];

    public const STATUS_LABELS = [
        'kelurahan' => 'Di Kelurahan',
        'operator_dinsos' => 'Di Operator Dinsos',
        'kabid' => 'Di Kepala Bidang',
        'kadis' => 'Di Kepala Dinas',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];

    public function anggotaKeluarga()
    {
        return $this->hasMany(PbiApbnAnggotaKeluarga::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(PbiApbnLog::class)->latest();
    }

    /**
     * Riwayat diagnosa yang pernah diinput petugas (bisa berulang kali,
     * setiap entri tersimpan sebagai baris baru, tidak menimpa entri lama).
     */
    public function diagnosaLogs()
    {
        return $this->hasMany(PbiApbnDiagnosaLog::class)->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['disetujui', 'ditolak']);
    }

    /** Urutan key tahap, dipakai untuk hitung next/back */
    public static function stageKeys(): array
    {
        return array_keys(self::STAGES);
    }

    public function currentStageIndex(): ?int
    {
        $keys = self::stageKeys();
        $idx = array_search($this->status, $keys, true);
        return $idx === false ? null : $idx;
    }

    public function nextStageKey(): ?string
    {
        $idx = $this->currentStageIndex();
        if ($idx === null) return null;
        $keys = self::stageKeys();
        // tahap terakhir (kadis) -> disetujui
        return $keys[$idx + 1] ?? 'disetujui';
    }

    public function previousStageKey(): ?string
    {
        $idx = $this->currentStageIndex();
        if ($idx === null || $idx === 0) return null; // sudah paling awal, tidak bisa back
        $keys = self::stageKeys();
        return $keys[$idx - 1];
    }

    /** Role yang berhak bertindak di status saat ini */
    public function currentStageRole(): ?string
    {
        return self::STAGES[$this->status]['role'] ?? null;
    }

    public function currentStageLabel(): ?string
    {
        return self::STAGES[$this->status]['label'] ?? null;
    }

    /**
     * Bangun urutan progress untuk ditampilkan sebagai stepper di halaman masyarakat.
     * Tidak dipakai untuk status 'ditolak' — status itu ditampilkan sebagai banner terpisah.
     */
    public function progressSteps(): array
    {
        $allKeys = array_merge(self::stageKeys(), ['disetujui']);
        $currentIdx = array_search($this->status, $allKeys, true);

        $steps = [];
        foreach ($allKeys as $i => $key) {
            $label = $key === 'disetujui'
                ? 'Disetujui'
                : (self::STAGES[$key]['label'] ?? $key);

            if ($currentIdx === false) {
                $state = 'pending';
            } elseif ($i < $currentIdx) {
                $state = 'done';
            } elseif ($i === $currentIdx) {
                $state = 'current';
            } else {
                $state = 'pending';
            }

            $steps[] = ['key' => $key, 'label' => $label, 'state' => $state];
        }

        return $steps;
    }
}
