<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\LoginAdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PbiApbnController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\KartuKksController;


// ==========================================
// FRONTEND / PUBLIC ROUTES
// ==========================================
Route::get('/', [PageController::class, 'home'])->name('home');

Route::prefix('registrasi')->name('registrasi.')->group(function () {
    Route::get('/step-1', [PageController::class, 'registrasiStep1'])->name('step1');
    Route::get('/step-2', [PageController::class, 'registrasiStep2'])->name('step2');
});

Route::get('/cek-densil', [PageController::class, 'cekDensil'])->name('cek-densil');
Route::get('/kejadian-bencana', [PageController::class, 'kejadianBencana'])->name('kejadian-bencana');
Route::get('/kesan', [PageController::class, 'kesan'])->name('kesan');
Route::get('/tutorial-pendaftaran', [PageController::class, 'tutorialPendaftaran'])->name('tutorial-pendaftaran');


// ==========================================
// AUTHENTICATION ROUTES
// ==========================================
Route::get('/login', [PageController::class, 'login'])->name('login');
Route::post('/login', [PageController::class, 'loginStore'])->name('login.store');
Route::get('/admin-login', [LoginAdminController::class, 'create'])->name('admin.login');
Route::post('/admin-login', [LoginAdminController::class, 'store'])->name('admin.login.store');

Route::get('/lupa-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/lupa-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');


// ==========================================
// ADMIN / PROTECTED ROUTES
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginAdminController::class, 'destroy'])->name('logout');

    // Menu tunggal "Kelola User & Role" dengan tab: Pengguna | Hak Akses
    Route::prefix('kelola-role-user')->name('akun.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');

        // Tab Pengguna
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{akun}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{akun}', [UserController::class, 'update'])->name('update');
        Route::delete('/{akun}', [UserController::class, 'destroy'])->name('destroy');

        // Tab Hak Akses (Role)
        Route::post('/role', [UserController::class, 'storeRole'])->name('role.store');
        Route::get('/role/{role}/edit', [UserController::class, 'editRole'])->name('role.edit');
        Route::put('/role/{role}', [UserController::class, 'updateRole'])->name('role.update');
        Route::delete('/role/{role}', [UserController::class, 'destroyRole'])->name('role.destroy');
    });

    // ==========================================
    // 3. PBI APBN — sudah dibangun penuh
    // ==========================================
    Route::prefix('pbi-apbn')->name('pbi-apbn.')->group(function () {
        Route::get('/ajuan', [PbiApbnController::class, 'ajuanIndex'])->name('ajuan.index');
        Route::get('/ajuan/{pbiApbn}', [PbiApbnController::class, 'ajuanShow'])->name('ajuan.show');
        Route::post('/ajuan/{pbiApbn}/aksi', [PbiApbnController::class, 'ajuanAksi'])->name('ajuan.aksi');

        Route::get('/arsip', [PbiApbnController::class, 'arsipIndex'])->name('arsip.index');
        Route::get('/monitoring', [PbiApbnController::class, 'monitoringIndex'])->name('monitoring.index');
        Route::get('/log', [PbiApbnController::class, 'logIndex'])->name('log.index');
    });

    // ==========================================
    // 4. Kartu KKS — sudah dibangun penuh (punya Tika)
    // PENTING: rute /ajuan, /arsip, /monitoring HARUS ada
    // sebelum rute wildcard /{permohonan}, agar tidak "ketangkap"
    // oleh wildcard tersebut.
    // ==========================================
    Route::prefix('kartu-kks')->name('kartu-kks.')->group(function () {
        Route::get('/ajuan', [KartuKksController::class, 'ajuan'])->name('ajuan');
        Route::get('/arsip', [KartuKksController::class, 'arsip'])->name('arsip');
        Route::get('/monitoring', [KartuKksController::class, 'monitoring'])->name('monitoring');

        Route::get('/{permohonan}', [KartuKksController::class, 'show'])->name('show');
        Route::get('/{permohonan}/detail', [KartuKksController::class, 'detail'])->name('detail');
        Route::put('/{permohonan}/detail', [KartuKksController::class, 'updateDetail'])->name('detail.update');
        Route::post('/{permohonan}/proses', [KartuKksController::class, 'proses'])->name('proses');
    });

    // ==========================================
    // 2, 5, 6. Modul lain — kerangka menu dulu, fitur menyusul
    // (Asesmen SPMB, Kedaruratan Medis, DTSEN)
    // 'kartu-kks' SUDAH DIHAPUS dari daftar placeholder ini
    // karena sudah dibangun penuh di atas.
    // ==========================================
    $placeholderModules = ['asesmen-spmb', 'kedaruratan-medis', 'dtsen'];
    foreach ($placeholderModules as $module) {
        Route::prefix($module)->name(str_replace('-', '_', $module) . '.')->group(function () use ($module) {
            foreach (['ajuan', 'arsip', 'monitoring', 'log'] as $tab) {
                Route::get("/{$tab}", [PlaceholderController::class, 'tab'])
                    ->defaults('module', $module)
                    ->defaults('tab', $tab)
                    ->name($tab);
            }
        });
    }

    // ==========================================
    // 7. Dokumen — template dokumen
    // ==========================================
    Route::get('/dokumen', [PlaceholderController::class, 'dokumen'])->name('dokumen.index');
});