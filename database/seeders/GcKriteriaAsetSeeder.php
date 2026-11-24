<?php

namespace Database\Seeders;

use App\Models\GcKriteriaParameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Kepemilikan Aset disamakan dengan rekap formulir (bagian 3):
 *  A. Aset bergerak  : Tabung gas 3 kg (+ jumlah bila Ya), Tabung gas 5,5 kg+, Emas/Perhiasan,
 *                      Komputer/Laptop/Tablet, Sepeda motor, Mobil  -> semua Ya/Tidak
 *  B. Aset tidak bergerak : Rumah/Bangunan (selain yang ditempati), Lahan lainnya -> Ya/Tidak
 * Pertanyaan aset lain dinonaktifkan (bukan dihapus), aman dijalankan berulang.
 *
 * Jalankan: php artisan db:seed --class=GcKriteriaAsetSeeder
 */
class GcKriteriaAsetSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // [kelompok, nama baru, nama pertanyaan lama (sumber bobot & nilai opsi)]
            $daftar = [
                ['aset_bergerak', 'Tabung gas 3 kg', 'Jumlah tabung gas 3 kg'],
                ['aset_bergerak', 'Tabung gas 5,5 kg atau lebih', 'Jumlah tabung gas 5,5 kg atau lebih'],
                ['aset_bergerak', 'Emas/Perhiasan', 'Jumlah emas/perhiasan (gram)'],
                ['aset_bergerak', 'Komputer/Laptop/Tablet', 'Jumlah komputer/laptop/tablet'],
                ['aset_bergerak', 'Sepeda motor', 'Jumlah sepeda motor'],
                ['aset_bergerak', 'Mobil', 'Jumlah mobil'],
                ['aset_tidak_bergerak', 'Rumah/Bangunan (selain yang ditempati)', 'Memiliki rumah/bangunan di tempat lain'],
                ['aset_tidak_bergerak', 'Lahan lainnya', 'Memiliki lahan (selain yang ditempati)'],
            ];

            $pakai = ['aset_bergerak' => [], 'aset_tidak_bergerak' => []];
            $induk3kg = null;

            foreach ($daftar as [$kel, $nama, $lama]) {
                $p = $this->pastikan($kel, $nama, $lama);
                $pakai[$kel][$nama] = $p;

                if ($nama === 'Tabung gas 3 kg') {
                    $induk3kg = $p;
                }
            }

            // Jumlah (Unit) tabung gas 3 kg: pakai pertanyaan angka yang sudah ada, bila tidak ada dibuat baru.
            $jumlah = GcKriteriaParameter::whereRaw('LOWER(TRIM(nama)) = ?', ['jumlah tabung gas 3 kg'])->first()
                ?? GcKriteriaParameter::create([
                    'kelompok' => 'aset_bergerak', 'urutan' => ((int) GcKriteriaParameter::max('urutan')) + 1,
                    'nama' => 'Jumlah tabung gas 3 kg', 'bobot' => 0, 'tipe_input' => 'angka', 'sumber' => null,
                    'target' => GcKriteriaParameter::targetBawaan('aset_bergerak', 'Jumlah tabung gas 3 kg'),
                    'pbi_parameter_id' => null, 'keterangan' => null, 'aktif' => true, 'wajib' => false,
                ]);

            $jumlah->update([
                'kelompok' => 'aset_bergerak',
                'aktif' => true,
                'wajib' => false,           // hanya berlaku bila Ya
                'induk_id' => $induk3kg->id,
                'induk_nilai' => ['Ya'],
            ]);

            // Urutan tampil: 3 kg, jumlahnya, lalu sisanya sesuai tabel
            $urutBergerak = array_merge(
                [$pakai['aset_bergerak']['Tabung gas 3 kg'], $jumlah],
                collect($pakai['aset_bergerak'])->except('Tabung gas 3 kg')->values()->all()
            );
            $urutTidak = array_values($pakai['aset_tidak_bergerak']);

            foreach ([['aset_bergerak', $urutBergerak], ['aset_tidak_bergerak', $urutTidak]] as [$kel, $urut]) {
                $this->susun($kel, $urut);

                // Selain yang ada di tabel: nonaktifkan
                $idAktif = collect($urut)->pluck('id');
                $mati = GcKriteriaParameter::where('kelompok', $kel)->whereNotIn('id', $idAktif)->where('aktif', true)->count();
                GcKriteriaParameter::where('kelompok', $kel)->whereNotIn('id', $idAktif)->update(['aktif' => false]);
                $this->command?->info("{$kel}: " . count($urut) . " pertanyaan aktif, {$mati} dinonaktifkan.");
            }
        });
    }

    /** Ambil (atau buat) pertanyaan Ya/Tidak; bobot & nilai opsi disalin dari pertanyaan lama bila ada. */
    private function pastikan(string $kel, string $nama, string $lama): GcKriteriaParameter
    {
        $p = GcKriteriaParameter::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($nama)])->where('kelompok', $kel)->first();
        $asal = GcKriteriaParameter::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($lama)])->with('opsis')->first();

        if (! $p) {
            $p = GcKriteriaParameter::create([
                'kelompok' => $kel,
                'urutan' => ((int) GcKriteriaParameter::max('urutan')) + 1,
                'nama' => $nama,
                'bobot' => $asal->bobot ?? 0,
                'tipe_input' => 'pilihan',
                'sumber' => null,
                'target' => GcKriteriaParameter::targetBawaan($kel, $nama),
                'pbi_parameter_id' => null,
                'keterangan' => 'Dari rekap formulir Kepemilikan Aset; opsi Ya/Tidak.',
                'aktif' => true,
                'wajib' => true,
            ]);
            $this->command?->info("Ditambahkan: {$nama}");
        } else {
            $p->update(['aktif' => true, 'tipe_input' => 'pilihan']);
        }

        $nilaiAsal = $asal && $asal->tipe_input === 'pilihan'
            ? $asal->opsis->mapWithKeys(fn ($o) => [mb_strtolower(trim($o->label)) => $o->nilai])
            : collect();

        foreach (['Ya', 'Tidak'] as $i => $label) {
            $p->opsis()->updateOrCreate(
                ['label' => $label],
                ['urutan' => $i + 1] + ($p->opsis()->where('label', $label)->exists() ? [] : ['nilai' => $nilaiAsal->get(mb_strtolower($label))])
            );
        }
        $p->opsis()->whereNotIn('label', ['Ya', 'Tidak'])->delete();

        return $p;
    }

    /** Pakai ulang nomor urut milik kelompok ini supaya urutan kelompok lain tidak terganggu. */
    private function susun(string $kel, array $urut): void
    {
        $semua = GcKriteriaParameter::where('kelompok', $kel)->orderBy('urutan')->orderBy('id')->get();
        $idUrut = collect($urut)->pluck('id');
        $lain = $semua->reject(fn ($p) => $idUrut->contains($p->id))->values();

        $slot = $semua->pluck('urutan')->map(fn ($u) => (int) $u)->sort()->values()->all();
        foreach (array_merge($urut, $lain->all()) as $i => $p) {
            GcKriteriaParameter::whereKey($p->id)->update(['urutan' => $slot[$i]]);
        }
    }
}