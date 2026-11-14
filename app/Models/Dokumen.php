<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Dokumen extends Model
{
    protected $table = 'dokumens';

    protected $fillable = [
        'nama',
        'kode',
        'keterangan',
        'file_path',
        'file_name',
        'mime',
        'file_size',
        'uploaded_by',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Ambil dokumen template berdasarkan kode (null jika belum diunggah). */
    public static function templateByKode(string $kode): ?self
    {
        return static::where('kode', $kode)->first();
    }

    /** Path absolut file template, atau null jika dokumen/filenya tidak ada. */
    public static function pathTemplate(string $kode): ?string
    {
        $dok = static::templateByKode($kode);
        $disk = Storage::disk('local');

        return ($dok && $disk->exists($dok->file_path)) ? $disk->path($dok->file_path) : null;
    }
}