<?php

namespace App\Http\Resources;

use App\Models\DataPendudukAsli as P;
use Illuminate\Http\Resources\Json\JsonResource;

class DesilResource extends JsonResource
{
    public function toArray($request): array
    {
        $detail = (bool) $request->attributes->get('api_client')?->bisa('detail');
        $c = function ($v) {
            $v = trim(str_replace("\r", '', (string) $v));

            return $v === '' ? null : $v;
        };

        $desil = (int) $this->desil_nasional;

        return [
            'nik' => $c($this->nomor_induk_kependudukan),
            'no_kk' => $c($this->nomor_kartu_keluarga),
            'nama' => $c($this->nama),
            'jenis_kelamin' => $c($this->jenis_kelamin),
            'tempat_lahir' => $this->when($detail, $c($this->tempat_lahir)),
            'tanggal_lahir' => $this->when($detail, $c($this->tgl_lahir_2)),
            'usia' => $this->usia !== null ? (int) $this->usia : null,
            'hubungan_keluarga' => $c($this->hubungan_keluarga),
            'status_perkawinan' => $c($this->status_perkawinan),
            'pekerjaan' => $c($this->pekerjaan),
            'alamat' => $this->when($detail, [
                'provinsi' => $c($this->provinsi),
                'kabupaten_kota' => $c($this->kabupaten),
                'kecamatan' => $c($this->kecamatan),
                'kelurahan' => $c($this->desa_kelurahan),
                'rw' => $c($this->rw),
                'rt' => $c($this->rt),
                'alamat' => $c($this->alamat),
            ]),
            'desil' => [
                'nilai' => $desil,
                'label' => P::DESIL_LABEL[$desil] ?? null,
                'kategori' => $desil === 0 ? 'tidak_terdaftar' : ($desil <= 4 ? 'prioritas' : 'mampu'),
                'status_dtsen' => $c($this->status_dtsen),
            ],
            'bansos' => collect(P::BANSOS)->map(fn ($cfg) => P::aktif($this->{$cfg[0]}))->all(),
            'disabilitas' => $this->when($detail, [
                'status' => $c($this->st_disabilitas),
                'keterangan' => $c($this->keterangan_disabilitas),
                'tingkat' => $c($this->tingkat_disabilitas),
            ]),
            'status_stunting' => $this->when($detail, $c($this->status_stunting)),
        ];
    }
}