<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'pbi_apbns'                  => 'pbi_apbds',
        'pbi_apbn_anggota_keluargas' => 'pbi_apbd_anggota_keluargas',
        'pbi_apbn_logs'              => 'pbi_apbd_logs',
        'pbi_apbn_diagnosa_logs'     => 'pbi_apbd_diagnosa_logs',
        'pbi_apbn_jawabans'          => 'pbi_apbd_jawabans',
    ];

    public function up(): void
    {
        $this->renameAll($this->tables, 'apbn', 'apbd');

        // 'pbi-apbn' -> 'pbi-apbd' pada kolom permissions di tabel roles
        $this->rewritePermissions('pbi-apbn', 'pbi-apbd');
    }

    public function down(): void
    {
        $this->rewritePermissions('pbi-apbd', 'pbi-apbn');
        $this->renameAll(array_flip($this->tables), 'apbd', 'apbn');
    }

    private function renameAll(array $map, string $from, string $to): void
    {
        foreach ($map as $old => $new) {
            if (Schema::hasTable($old)) {
                Schema::rename($old, $new);
            }
        }

        // Ganti nama kolom yang mengandung "apbn" (mis. pbi_apbn_id) di semua tabel
        foreach (Schema::getTableListing() as $table) {
            $table = preg_replace('/^.*\./', '', $table); // buang prefix schema bila ada
            foreach (Schema::getColumnListing($table) as $column) {
                if (str_contains($column, $from)) {
                    Schema::table($table, function ($t) use ($column, $from, $to) {
                        $t->renameColumn($column, str_replace($from, $to, $column));
                    });
                }
            }
        }
    }

    private function rewritePermissions(string $from, string $to): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasColumn('roles', 'permissions')) {
            return;
        }

        foreach (DB::table('roles')->get(['id', 'permissions']) as $role) {
            if ($role->permissions !== null && str_contains($role->permissions, $from)) {
                DB::table('roles')->where('id', $role->id)
                    ->update(['permissions' => str_replace($from, $to, $role->permissions)]);
            }
        }
    }
};
