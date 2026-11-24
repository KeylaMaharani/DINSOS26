<?php

use App\Http\Controllers\Api\V1\DesilController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/desil')->name('api.desil.')->group(function () {
    Route::get('/', [DesilController::class, 'index'])->middleware('api.desil:index')->name('index');
    Route::get('/statistik', [DesilController::class, 'statistik'])->middleware('api.desil:statistik')->name('statistik');
    Route::get('/wilayah', [DesilController::class, 'wilayah'])->middleware('api.desil:wilayah')->name('wilayah');
    Route::get('/nik/{nik}', [DesilController::class, 'nik'])->where('nik', '[0-9]{16}')->middleware('api.desil:nik')->name('nik');
    Route::get('/kk/{noKk}', [DesilController::class, 'kk'])->where('noKk', '[0-9]{16}')->middleware('api.desil:kk')->name('kk');
});