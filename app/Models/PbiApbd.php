<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Concerns\HasKriteriaSkor;

class PbiApbd extends Model
{
    use HasFactory, HasKriteriaSkor;

    protected $guarded = [];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'punya_kendaraan_roda_2' => 'boolean',
        'sudah_diverifikasi_kelurahan' => 'boolean',
    ];

    /**
     * Pakai `no_registrasi` sebagai "slug" di URL (route model binding).
     * $this->id tetap dipakai untuk foreign key dan relasi.
     */
    public function getRouteKeyName(): string
    {
        return 'no_registrasi';
    }

    /**
     * Notifikasi badge: saat status/pengembalian berubah, hapus catatan "sudah dilihat"
     * untuk tahap yang baru, supaya role di tahap itu melihatnya sebagai notifikasi baru.
     */
    protected static function booted(): void
    {
        static::updated(function (PbiApbd $m) {
            if ($m->wasChanged(['status', 'dikembalikan_ke_masyarakat', 'data_diisi_at'])) {
                DB::table('pbi_apbd_dilihat')
                    ->where('pbi_apbd_id', $m->id)
                    ->where('status', $m->status)
                    ->delete();
            }
        });
    }

    /**
     * Catat bahwa user ini sudah membuka / menindaklanjuti permohonan pada tahap saat ini.
     */
    public function tandaiDilihat(?User $user): void
    {
        if (! $user) {
            return;
        }

        DB::table('pbi_apbd_dilihat')->updateOrInsert(
            ['pbi_apbd_id' => $this->id, 'user_id' => $user->id, 'status' => $this->status],
            ['dilihat_at' => now()]
        );
    }

    // ==========================================================
    // ALUR STATUS
    // kelurahan -> operator_dinsos -> kabid -> kadis -> dinkes -> bpjs -> disetujui
    // "ditolak" adalah status akhir terpisah (bisa terjadi dari tahap manapun)
    // ==========================================================
    public const OPERATOR_ROLE = 'dayasos';

    public const STAGES = [
        'kelurahan' => [
            'label' => 'Kelurahan',
            'role' => 'kelurahan',
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
        'dinkes' => [
            'label' => 'Dinas Kesehatan',
            'role' => 'dinkes',
        ],
        'bpjs' => [
            'label' => 'BPJS Kesehatan',
            'role' => 'bpjs',
        ],
    ];

    public const STATUS_LABELS = [
        'kelurahan' => 'Di Kelurahan',
        'operator_dinsos' => 'Di Operator Dinsos',
        'kabid' => 'Di Kepala Bidang',
        'kadis' => 'Di Kepala Dinas',
        'dinkes' => 'Di Dinas Kesehatan',
        'bpjs' => 'Di BPJS Kesehatan',
        'disetujui' => 'Disetujui (Teraktivasi)',
        'ditolak' => 'Ditolak',
    ];

    /**
     * Halaman approval per tahap (dipakai PbiApprovalController, route, sidebar, badge).
     *  status   : nilai kolom status yang ditampilkan di halaman tahap itu
     *  module   : key di Role::MODULES
     *  edit     : boleh ubah data pemohon & unggah suket (hanya Operator)
     *  diagnosa : boleh menambah diagnosa
     */
    public const TAHAP = [
        'operator' => ['status' => 'operator_dinsos', 'label' => 'Operator Dinsos', 'module' => 'pbi-operator', 'edit' => true,  'diagnosa' => true],
        'kabid'    => ['status' => 'kabid',           'label' => 'Kepala Bidang',   'module' => 'pbi-kabid',    'edit' => false, 'diagnosa' => false],
        'kadis'    => ['status' => 'kadis',           'label' => 'Kepala Dinas',    'module' => 'pbi-kadis',    'edit' => false, 'diagnosa' => false],
        'dinkes'   => ['status' => 'dinkes',          'label' => 'Dinas Kesehatan', 'module' => 'pbi-dinkes',   'edit' => false, 'diagnosa' => true],
        'bpjs'     => ['status' => 'bpjs',            'label' => 'BPJS Kesehatan',  'module' => 'pbi-bpjs',     'edit' => false, 'diagnosa' => false],
    ];

    public function anggotaKeluarga()
    {
        return $this->hasMany(PbiApbdAnggotaKeluarga::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(PbiApbdLog::class)->latest();
    }

    /**
     * Riwayat diagnosa yang pernah diinput petugas (setiap entri baris baru).
     */
    public function diagnosaLogs()
    {
        return $this->hasMany(PbiApbdDiagnosaLog::class)->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['disetujui', 'ditolak']);
    }

    /**
     * Apakah masyarakat masih boleh mengisi / memperbaiki data.
     * Terkunci HANYA kalau sudah disetujui.
     */
    public function masyarakatBisaEdit(): bool
    {
        return $this->status !== 'disetujui';
    }

    /**
     * Masih berupa draft: warga belum pernah menekan "Simpan Data".
     */
    public function masyarakatMasihDraft(): bool
    {
        return $this->data_diisi_at === null && $this->status === 'kelurahan';
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
        // tahap terakhir (bpjs) -> disetujui
        return $keys[$idx + 1] ?? 'disetujui';
    }

    public function previousStageKey(): ?string
    {
        $idx = $this->currentStageIndex();
        if ($idx === null || $idx === 0) return null;
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
     * Urutan progress untuk stepper di halaman masyarakat.
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
