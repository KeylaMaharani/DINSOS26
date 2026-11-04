<?php

namespace App\Observers;

use App\Models\DtsenPermohonan;
use App\Support\Notif;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class PermohonanMasyarakatObserver
{
    /** false = hanya notif penting (dikembalikan, ditolak, selesai/disetujui). */
    private const NOTIF_TAHAPAN = true;

    public function updated(Model $m): void
    {
        try {
            if (! $m->user_id) {
                return;
            }
            // Perubahan yang dilakukan warga sendiri tidak perlu diberi notif.
            if (auth()->id() === $m->user_id) {
                return;
            }

            $statusBerubah = $m->wasChanged('status');
            $dikembalikan = $m->wasChanged('dikembalikan_ke_masyarakat') && $m->dikembalikan_ke_masyarakat;

            if (! $statusBerubah && ! $dikembalikan) {
                return;
            }

            $dtsen = $m instanceof DtsenPermohonan;
            $jenis = $dtsen ? 'DTSEN' : 'PBI APBD';
            $no = $m->no_registrasi ?? $m->no_permohonan ?? '-';
            $url = $dtsen
                ? route('masyarakat.dtsen.lengkapi', $m)
                : route('masyarakat.pbi-apbd.lengkapi', $m);
            $status = strtolower((string) $m->status);

            if ($status === 'ditolak') {
                Notif::kirim($m->user_id, 'ditolak', "Permohonan {$jenis} ditolak",
                    "Permohonan {$no} ditolak. Silakan periksa dan perbaiki data Anda lalu ajukan ulang.", $url);
            } elseif ($dikembalikan) {
                Notif::kirim($m->user_id, 'perlu_dilengkapi', "Data {$jenis} perlu diperbaiki",
                    "Permohonan {$no} dikembalikan oleh petugas. Mohon lengkapi/perbaiki data Anda.", $url);
            } elseif (in_array($status, ['disetujui', 'selesai'], true)) {
                Notif::kirim($m->user_id, 'selesai', "Permohonan {$jenis} selesai",
                    "Permohonan {$no} telah disetujui/selesai diproses.", $url);
            } elseif ($statusBerubah && self::NOTIF_TAHAPAN) {
                Notif::kirim($m->user_id, 'proses', "Permohonan {$jenis} diproses",
                    "Permohonan {$no} sedang pada tahap: {$this->label($m)}.", $url);
            }
        } catch (Throwable $e) {
            // Notifikasi tidak boleh menggagalkan proses utama.
            report($e);
        }
    }

    private function label(Model $m): string
    {
        if (method_exists($m, 'statusLabel')) {
            return (string) $m->statusLabel();
        }
        if ($m instanceof DtsenPermohonan) {
            return DtsenPermohonan::statusLabels()[$m->status] ?? ucwords(str_replace('_', ' ', (string) $m->status));
        }

        return ucwords(str_replace('_', ' ', (string) $m->status));
    }
}
