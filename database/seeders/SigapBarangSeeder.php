<?php

namespace Database\Seeders;

use App\Models\SigapBarang;
use App\Models\SigapStokMutasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Contoh master barang bantuan bencana + stok awal.
 * Aman dijalankan berulang: barang yang sudah ada (berdasarkan kode)
 * tidak diubah dan stoknya tidak ditambah lagi.
 *
 * Jalankan: php artisan db:seed --class=SigapBarangSeeder
 *
 * Isinya HANYA CONTOH -- ganti nama, satuan, dan jumlah dengan data
 * gudang Dinsos yang sebenarnya.
 */
class SigapBarangSeeder extends Seeder
{
    public function run(): void
    {
        $barang = [
            // kode, nama, satuan, stok awal, batas menipis
            ['BRG-001', 'Beras 5 Kg', 'karung', 200, 50],
            ['BRG-002', 'Mie Instan', 'dus', 150, 30],
            ['BRG-003', 'Makanan Siap Saji', 'paket', 300, 60],
            ['BRG-004', 'Air Mineral Dus', 'dus', 250, 50],
            ['BRG-005', 'Selimut', 'lembar', 400, 80],
            ['BRG-006', 'Matras / Kasur Lipat', 'buah', 120, 30],
            ['BRG-007', 'Tenda Keluarga', 'unit', 40, 10],
            ['BRG-008', 'Terpal', 'lembar', 100, 20],
            ['BRG-009', 'Paket Kebersihan (Hygiene Kit)', 'paket', 200, 40],
            ['BRG-010', 'Paket Sembako', 'paket', 180, 40],
            ['BRG-011', 'Pakaian Layak Pakai', 'paket', 150, 30],
            ['BRG-012', 'Popok Bayi', 'bungkus', 90, 20],
        ];

        foreach ($barang as [$kode, $nama, $satuan, $stok, $minimum]) {
            DB::transaction(function () use ($kode, $nama, $satuan, $stok, $minimum) {
                $b = SigapBarang::firstOrCreate(
                    ['kode' => $kode],
                    ['nama' => $nama, 'satuan' => $satuan, 'stok' => $stok, 'stok_minimum' => $minimum, 'aktif' => true]
                );

                // Catat mutasi "stok awal" hanya saat barang baru dibuat
                if ($b->wasRecentlyCreated) {
                    SigapStokMutasi::create([
                        'barang_id' => $b->id,
                        'jenis' => 'masuk',
                        'jumlah' => $stok,
                        'saldo_setelah' => $stok,
                        'keterangan' => 'Stok awal (seeder contoh)',
                    ]);
                }
            });
        }
    }
}
