<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NgopiPagiKonsultasi extends Model
{
    protected $table = 'ngopi_pagi_konsultasis';

    protected $guarded = ['id'];

    protected $casts = [
        'butuh_segera' => 'boolean',
        'identitas_terverifikasi' => 'boolean',
        'terakhir_pesan_at' => 'datetime',
        'dikonfirmasi_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Satu sumber untuk route, view, dan controller (config/dayasos.php menyalin label & url-nya).
     *   publik = posisi di bilah progres yang dilihat masyarakat (1-5)
     *   menu   = false -> tidak punya halaman daftar sendiri (dilihat lewat Rekapitulasi)
     */
    public const TAHAP = [
        'chat_masuk' => ['label' => 'Chat Masuk', 'langkah' => '1–3', 'url' => 'chat-masuk', 'icon' => 'forum', 'publik' => 1,
            'desc' => 'Admin menyapa, memperkenalkan layanan, lalu memverifikasi identitas dan kebutuhan awal.', 'output' => 'Data awal pengguna'],
        'screening' => ['label' => 'Screening', 'langkah' => '4', 'url' => 'screening', 'icon' => 'fact_check', 'publik' => 2,
            'desc' => 'Identifikasi jenis masalah, lalu tentukan Kategori A, B, atau C.', 'output' => 'Klasifikasi masalah'],
        'konsultasi' => ['label' => 'Konsultasi LK3', 'langkah' => '5', 'url' => 'konsultasi-lk3', 'icon' => 'psychology', 'publik' => 3,
            'desc' => 'Petugas LK3 menggali kondisi, kronologi, kebutuhan, dan tingkat urgensi.', 'output' => 'Hasil asesmen awal'],
        'tindak_lanjut' => ['label' => 'Tindak Lanjut', 'langkah' => '6–7', 'url' => 'tindak-lanjut', 'icon' => 'alt_route', 'publik' => 3,
            'desc' => 'Tentukan jenis layanan (Informasi, Konseling, Rujukan) dan rencana intervensinya.', 'output' => 'Rencana intervensi'],
        'monitoring' => ['label' => 'Monitoring', 'langkah' => '8', 'url' => 'monitoring', 'icon' => 'monitor_heart', 'publik' => 3,
            'desc' => 'Pantau perkembangan kasus yang membutuhkan tindak lanjut.', 'output' => 'Status perkembangan kasus'],
        'konfirmasi' => ['label' => 'Konfirmasi & Penutupan', 'langkah' => '9–10', 'url' => 'konfirmasi', 'icon' => 'handshake', 'publik' => 4,
            'desc' => 'Pastikan kebutuhan terpenuhi, lalu nyatakan layanan selesai.', 'output' => 'Layanan selesai'],
        'selesai' => ['label' => 'Selesai', 'langkah' => '11', 'url' => 'selesai', 'icon' => 'task_alt', 'publik' => 5, 'menu' => false,
            'desc' => 'Layanan ditutup dan tercatat di rekapitulasi.', 'output' => 'Rekapitulasi layanan'],
    ];

    public const PUBLIK = ['Diterima', 'Diidentifikasi', 'Pendampingan', 'Konfirmasi', 'Selesai'];

    public const KATEGORI = [
        'A' => ['nama' => 'Informasi', 'chip' => 'bg-emerald-100 text-emerald-800', 'edge' => 'border-l-emerald-500',
            'tindakan' => 'Admin memberikan informasi yang tersedia.',
            'contoh' => 'Pertanyaan program, bantuan sosial, layanan Dinas Sosial, lembaga/layanan sosial'],
        'B' => ['nama' => 'Konsultasi', 'chip' => 'bg-amber-100 text-amber-800', 'edge' => 'border-l-amber-500',
            'tindakan' => 'Dialihkan kepada Petugas/Konselor LK3 untuk konsultasi lebih lanjut.',
            'contoh' => 'Konflik keluarga, pengasuhan, psikososial, perselisihan anggota keluarga'],
        'C' => ['nama' => 'Prioritas / Risiko Tinggi', 'chip' => 'bg-rose-100 text-rose-800', 'edge' => 'border-l-rose-500',
            'tindakan' => 'Segera dilakukan asesmen dan koordinasi/rujukan kepada layanan yang berwenang.',
            'contoh' => 'Kekerasan, penelantaran anak/lansia, keselamatan terancam, orang terlantar'],
    ];

    public const JENIS_MASALAH = [
        'keluarga' => 'Keluarga', 'sosial' => 'Sosial', 'anak' => 'Anak', 'lansia' => 'Lansia',
        'disabilitas' => 'Disabilitas', 'kemiskinan' => 'Kemiskinan', 'kekerasan' => 'Kekerasan',
        'konflik_keluarga' => 'Konflik keluarga', 'lainnya' => 'Lainnya',
    ];

    public const URGENSI = ['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi', 'darurat' => 'Darurat'];

    public const TINDAK_LANJUT = [
        'informasi' => ['label' => 'Informasi', 'icon' => 'info', 'output' => 'Informasi selesai',
            'desc' => 'Memberikan informasi program/layanan, rekayasa/kebijakan sosial, dll.'],
        'konseling' => ['label' => 'Konseling', 'icon' => 'forum', 'output' => 'Pendampingan LK3',
            'desc' => 'Konseling oleh petugas LK3.'],
        'rujukan' => ['label' => 'Rujukan', 'icon' => 'share', 'output' => 'Rujuk layanan terkait',
            'desc' => 'Rujuk ke Dinsos, Puskesmas, Rumah Sakit, Polisi, UPT, LKS, P2TP2A, atau lembaga lain.'],
    ];

    public const STATUS_PERKEMBANGAN = [
        'berjalan' => 'Sedang berjalan',
        'membaik' => 'Ada perkembangan',
        'butuh_tindak_lanjut' => 'Perlu tindak lanjut lanjutan',
        'terpenuhi' => 'Kebutuhan terpenuhi',
    ];

    public const KECAMATAN = ['Bogor Barat', 'Bogor Selatan', 'Bogor Tengah', 'Bogor Timur', 'Bogor Utara', 'Tanah Sareal'];

    // ---------- Relasi ----------
    public function pesans(): HasMany { return $this->hasMany(NgopiPagiPesan::class, 'konsultasi_id')->orderBy('id'); }
    public function monitorings(): HasMany { return $this->hasMany(NgopiPagiMonitoring::class, 'konsultasi_id')->latest('id'); }
    public function logs(): HasMany { return $this->hasMany(NgopiPagiLog::class, 'konsultasi_id')->latest('id'); }
    public function petugas(): BelongsTo { return $this->belongsTo(User::class, 'petugas_id'); }
    public function konselor(): BelongsTo { return $this->belongsTo(User::class, 'konselor_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    // ---------- Scope ----------
    /** Urutan antrian: butuh segera, lalu Kategori C > B > A > belum diklasifikasi, lalu pesan terbaru. */
    public function scopePrioritas(Builder $q): Builder
    {
        return $q->orderByDesc('butuh_segera')
            ->orderByRaw("CASE kategori WHEN 'C' THEN 0 WHEN 'B' THEN 1 WHEN 'A' THEN 2 ELSE 3 END")
            ->orderByDesc('terakhir_pesan_at');
    }

    public function scopeCari(Builder $q, ?string $kata): Builder
    {
        if (! $kata) {
            return $q;
        }

        return $q->where(function ($w) use ($kata) {
            $w->where('nama', 'like', "%{$kata}%")
                ->orWhere('no_konsultasi', 'like', "%{$kata}%")
                ->orWhere('kontak', 'like', "%{$kata}%")
                ->orWhere('kelurahan', 'like', "%{$kata}%");
        });
    }

    // ---------- Helper ----------
    public static function buatNomor(): string
    {
        do {
            $no = 'NP-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (static::where('no_konsultasi', $no)->exists());

        return $no;
    }

    public function inisial(): string
    {
        return strtoupper(mb_substr($this->nama, 0, 1));
    }

    public function waLink(): string
    {
        $hp = preg_replace('/\D/', '', $this->kontak);
        if (str_starts_with($hp, '0')) {
            $hp = '62' . substr($hp, 1);
        }

        return 'https://wa.me/' . $hp;
    }

    public function tahapSebelumnya(): ?string
    {
        $urut = array_keys(self::TAHAP);
        $i = array_search($this->tahap, $urut, true);

        if (! $i) {
            return null;
        }
        // Kategori A melompati Konsultasi LK3, jadi mundurnya ke Screening.
        if ($this->tahap === 'tindak_lanjut' && $this->kategori === 'A') {
            return 'screening';
        }

        return $urut[$i - 1];
    }

    /** URL halaman admin untuk kasus ini (menyesuaikan tahap saat ini). Relatif, aman disimpan di notifikasi. */
    public function urlAdmin(): string
    {
        if (self::TAHAP[$this->tahap]['menu'] ?? true) {
            return route('ngopi-pagi.admin.tahap.' . $this->tahap, ['konsultasi' => $this->id], false);
        }

        return route('ngopi-pagi.admin.kasus', ['konsultasi' => $this->id], false);
    }

    /** Teks "sedang berbicara dengan siapa" untuk header chat masyarakat. */
    public function pendampingLabel(): string
    {
        if ($this->konselor) {
            return $this->konselor->name . ' · Konselor LK3';
        }
        if ($this->petugas) {
            return $this->petugas->name . ' · Admin Ngopi Pagi';
        }

        return 'Menunggu petugas menyapa Anda';
    }

    public function kirimSistem(string $isi): NgopiPagiPesan
    {
        $this->forceFill(['terakhir_pesan_at' => now()])->save();

        return $this->pesans()->create(['pengirim' => 'sistem', 'isi' => $isi]);
    }

    public function catat(string $aksi, ?string $dari = null, ?string $ke = null, ?string $catatan = null, ?int $userId = null): void
    {
        NgopiPagiLog::create([
            'konsultasi_id' => $this->id, 'user_id' => $userId, 'aksi' => $aksi,
            'dari_tahap' => $dari, 'ke_tahap' => $ke, 'catatan' => $catatan,
        ]);
    }

    public function pindahTahap(string $ke, string $aksi, ?string $catatan = null, ?int $userId = null): void
    {
        $dari = $this->tahap;
        $this->update(['tahap' => $ke]);
        $this->catat($aksi, $dari, $ke, $catatan, $userId);
    }
}
