<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\LoginAdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Admin\DashboardController;


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
    // Satu index untuk kedua tab, dibedakan lewat query string ?tab=
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
});
