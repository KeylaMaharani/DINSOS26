<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KesanPilihan extends Model
{
    protected $table = 'kesan_pilihans';
    protected $fillable = [
        'kode', 'user_id', 'kesan_penerima_id', 'status', 'dipilih_at', 'verifikasi_at', 'selesai_at',
        'verifikator_id', 'catatan', 'laporan', 'bukti',
    ];
    protected $casts = ['dipilih_at' => 'datetime', 'verifikasi_at' => 'datetime', 'selesai_at' => 'datetime'];

    /** step = jumlah tahap pada TAHAP yang sudah selesai. */
    public const STATUS = [
        'keranjang'  => ['label' => 'Belum dikonfirmasi', 'chip' => 'bg-slate-100 text-slate-700', 'step' => 0, 'info' => 'Belum dilanjutkan ke penyaluran'],
        'dipilih'    => ['label' => 'Menunggu Verifikasi', 'chip' => 'bg-amber-100 text-amber-800', 'step' => 1, 'info' => 'Menunggu verifikasi petugas Dinas Sosial'],
        'penyaluran' => ['label' => 'Dalam Proses Penyaluran', 'chip' => 'bg-orange-100 text-orange-700', 'step' => 2, 'info' => 'Proses penyaluran bantuan'],
        'selesai'    => ['label' => 'Selesai', 'chip' => 'bg-green-100 text-green-800', 'step' => 4, 'info' => 'Bantuan sudah disalurkan'],
        'ditolak'    => ['label' => 'Ditolak', 'chip' => 'bg-red-100 text-red-700', 'step' => 0, 'info' => 'Pilihan tidak dapat dilanjutkan'],
        'batal'      => ['label' => 'Dibatalkan', 'chip' => 'bg-slate-100 text-slate-600', 'step' => 0, 'info' => 'Dibatalkan oleh Filantropi'],
    ];

    public const TAHAP = ['Dipilih', 'Verifikasi', 'Disalurkan', 'Selesai'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function penerima()
    {
        return $this->belongsTo(KesanPenerima::class, 'kesan_penerima_id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    public function scopeBerjalan($q)
    {
        return $q->whereIn('status', ['dipilih', 'penyaluran']);
    }

    public function cfg(): array
    {
        return self::STATUS[$this->status] ?? self::STATUS['keranjang'];
    }

    /** Berapa tahap yang sudah selesai (0-4). */
    public function langkah(): int
    {
        return $this->cfg()['step'];
    }

    /** Tahap yang sedang berjalan (1-4) atau null bila sudah selesai / berhenti. */
    public function tahapAktif(): ?int
    {
        return in_array($this->status, ['dipilih', 'penyaluran'], true) ? $this->langkah() + 1 : null;
    }

    public function buktiUrl(): ?string
    {
        return $this->bukti ? asset('storage/' . $this->bukti) : null;
    }
}
