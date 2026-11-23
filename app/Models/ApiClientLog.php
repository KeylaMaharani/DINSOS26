<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\MassPrunable;

class ApiClientLog extends Model
{
    use MassPrunable;

    const UPDATED_AT = null;

    protected $guarded = [];
    protected $casts = ['query_keys' => 'array', 'created_at' => 'datetime'];

    /** Log otomatis dihapus setelah 90 hhari (butuh `model:prune` di scheduler). */
    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(90));
    }
}