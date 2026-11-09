<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hak akses detail (submenu + aksi): role yang sudah punya sebuah modul diberi SEMUA submenu
 * dan aksi di bawah modul itu, jadi perilakunya sama persis seperti sebelumnya.
 * Setelah ini barulah Super Admin bisa mempersempit per submenu/aksi di Kelola Akses.
 */
return new class extends Migration
{
    public function up(): void
    {
        $katalog = config('akses', []);

        foreach (DB::table('roles')->get() as $role) {
            $lama = json_decode($role->permissions ?? '[]', true) ?: [];
            $baru = $lama;

            foreach ($katalog as $modul => $submenus) {
                if (! in_array($modul, $lama, true)) {
                    continue;
                }

                foreach ($submenus as $sub => $cfg) {
                    $baru[] = "{$modul}.{$sub}";
                    foreach (array_keys($cfg['aksi'] ?? []) as $aksi) {
                        $baru[] = "{$modul}.{$sub}.{$aksi}";
                    }
                }
            }

            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode(array_values(array_unique($baru))),
            ]);
        }
    }

    public function down(): void
    {
        $modul = array_keys(config('akses', []));

        foreach (DB::table('roles')->get() as $role) {
            $lama = json_decode($role->permissions ?? '[]', true) ?: [];

            // kunci detail berbentuk "modul.submenu[.aksi]" dengan modul dari katalog
            $baru = array_values(array_filter($lama, function ($k) use ($modul) {
                return ! (str_contains($k, '.') && in_array(explode('.', $k)[0], $modul, true));
            }));

            DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode($baru)]);
        }
    }
};
