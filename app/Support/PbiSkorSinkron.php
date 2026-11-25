<?php

namespace App\Support;

use App\Models\GcKriteriaParameter;
use App\Models\GcPbiJawaban;
use App\Models\PbiApbd;
use App\Models\PbiApbdJawaban;
use App\Models\PbiKriteriaOpsi;
use App\Models\PbiKriteriaParameter;

/**
 * Menyalin jawaban Lengkapi Data (GC) ke tabel skor PBI (pbi_apbd_jawabans).
 *
 * Dasar penautan : gc_kriteria_parameters.pbi_parameter_id  (bukan nama pertanyaan)
 * Dasar penilaian: gc_kriteria_opsis.pbi_opsi_id            (opsi GC -> opsi PBI)
 * Parameter PBI dengan aktif = false tidak ikut skor (lihat Master Kriteria PBI).
 */
class PbiSkorSinkron
{
    /** $jawaban = peta id parameter GC => jawaban tersimpan. */
    public static function sinkron(PbiApbd $pbi, $parameters, array $jawaban): void
    {
        $skorParams = PbiKriteriaParameter::where('aktif', true)->get()->keyBy('id');
        $dipakai = [];

        foreach ($parameters as $p) {
            if (! in_array($p->target, ['pbi', 'pbi_gc'], true)
                || $p->tipe_input !== 'pilihan'
                || ! $p->pbi_parameter_id) {
                continue;
            }

            $pp = $skorParams->get($p->pbi_parameter_id);
            $label = $jawaban[$p->id] ?? null;

            if (! $pp || $label === null || $label === '') {
                continue;
            }

            $gcOpsi = $p->opsis->firstWhere('label', $label);
            $opsi = ($gcOpsi && $gcOpsi->pbi_opsi_id)
                ? PbiKriteriaOpsi::where('parameter_id', $pp->id)->find($gcOpsi->pbi_opsi_id)
                : null;

            if (! $opsi) {
                continue; // opsi belum dipetakan -> tidak dihitung
            }

            $dipakai[] = $pp->id;

            PbiApbdJawaban::updateOrCreate(
                ['pbi_apbd_id' => $pbi->id, 'parameter_id' => $pp->id],
                [
                    'opsi_id' => $opsi->id,
                    'urutan' => $pp->urutan,
                    'parameter_nama' => $pp->nama,
                    'jawaban_label' => $opsi->label,
                    'indeks' => $pp->indeks,
                    'bobot' => $pp->bobot,
                    'indeks_terintegrasi' => $opsi->indeks_terintegrasi,
                    'skor' => round((float) $pp->indeks * (float) $pp->bobot * (float) $opsi->indeks_terintegrasi, 6),
                ]
            );
        }

        // Akun lama (form 18 parameter): jawaban PBI lama jangan dihapus.
        if ($pbi->form_lama) {
            return;
        }

        // Baris skor yang tidak lagi dihasilkan jawaban GC (parameter dimatikan / opsi tidak dipetakan) dibuang.
        $pbi->jawabans()->whereNotNull('parameter_id')->whereNotIn('parameter_id', $dipakai)->delete();
    }

    /** Hitung ulang satu pengajuan dari jawaban GC yang tersimpan. */
    public static function hitungUlang(PbiApbd $pbi): void
    {
        $parameters = GcKriteriaParameter::aktif()->with('opsis')->get();

        $jawaban = GcPbiJawaban::where('pbi_apbd_id', $pbi->id)
            ->whereNotNull('jawaban')->pluck('jawaban', 'parameter_id')->all();

        self::sinkron($pbi, $parameters, $jawaban);
        $pbi->hitungUlangSkor();
    }
}
