<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DtsenController;
use App\Http\Controllers\Admin\KartuKksController;
use App\Http\Controllers\Admin\PbiApbnController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginAdminController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegistrasiController;
use App\Http\Controllers\Layanan\KartuKksAjuanController;
use App\Http\Controllers\Layanan\DtsenAjuanController;
use App\Http\Controllers\Masyarakat\MasyarakatController;
use Illuminate\Support\Facades\Route;


// ==========================================
// FRONTEND / PUBLIC ROUTES
// ==========================================
Route::get('/', [PageController::class, 'home'])->name('home');

Route::prefix('registrasi')->name('registrasi.')->group(function () {
    Route::get('/step-1', [RegistrasiController::class, 'step1'])->name('step1');
    Route::post('/step-1', [RegistrasiController::class, 'step1Store'])->name('step1.store');
    Route::get('/step-2', [RegistrasiController::class, 'step2'])->name('step2');
    Route::post('/step-2', [RegistrasiController::class, 'step2Store'])->name('step2.store');
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
Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginAdminController::class, 'destroy'])->name('logout');

    // Edit profil akun sendiri (dipakai di dropdown pojok kanan atas, tampil sebagai pop-up)
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

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

    Route::prefix('layanan/kartu-kks')->name('layanan.kartu-kks.')->group(function () {
        Route::get('/ajukan', [KartuKksAjuanController::class, 'create'])->name('ajukan');
        Route::post('/ajukan', [KartuKksAjuanController::class, 'store'])->name('ajukan.store');
        Route::get('/riwayat', [KartuKksAjuanController::class, 'riwayat'])->name('riwayat');
    });

    Route::prefix('layanan/dtsen')->name('layanan.dtsen.')->group(function () {
        Route::get('/ajukan', [DtsenAjuanController::class, 'create'])->name('ajukan');
        Route::post('/ajukan', [DtsenAjuanController::class, 'store'])->name('ajukan.store');
        Route::get('/riwayat', [DtsenAjuanController::class, 'riwayat'])->name('riwayat');
    });

    // ==========================================
    // 3. PBI APBN — sudah dibangun penuh
    // ==========================================
    Route::prefix('pbi-apbn')->name('pbi-apbn.')->group(function () {
        Route::get('/ajuan', [PbiApbnController::class, 'ajuanIndex'])->name('ajuan.index');
        Route::get('/ajuan/{pbiApbn}', [PbiApbnController::class, 'ajuanShow'])->name('ajuan.show');
        Route::post('/ajuan/{pbiApbn}/aksi', [PbiApbnController::class, 'ajuanAksi'])->name('ajuan.aksi');
        Route::put('/ajuan/{pbiApbn}/tambahan', [PbiApbnController::class, 'ajuanUpdateTambahan'])->name('ajuan.updateTambahan');
        Route::post('/ajuan/{pbiApbn}/diagnosa', [PbiApbnController::class, 'ajuanTambahDiagnosa'])->name('ajuan.diagnosa.store');

        Route::get('/arsip', [PbiApbnController::class, 'arsipIndex'])->name('arsip.index');
        Route::get('/monitoring', [PbiApbnController::class, 'monitoringIndex'])->name('monitoring.index');
        Route::get('/log', [PbiApbnController::class, 'logIndex'])->name('log.index');
    });

    // ==========================================
    // 4. Kartu KKS — sudah dibangun penuh (punya Tika)
    // PENTING: rute /ajuan, /arsip, /monitoring, /log HARUS ada
    // sebelum rute wildcard /{permohonan}, agar tidak "ketangkap"
    // oleh wildcard tersebut.
    // ==========================================
    Route::prefix('kartu-kks')->name('kartu-kks.')->group(function () {
        Route::get('/ajuan', [KartuKksController::class, 'ajuan'])->name('ajuan');
        Route::get('/arsip', [KartuKksController::class, 'arsip'])->name('arsip');
        Route::get('/monitoring', [KartuKksController::class, 'monitoring'])->name('monitoring');
        Route::get('/log', [KartuKksController::class, 'logIndex'])->name('log');

        Route::get('/{permohonan}', [KartuKksController::class, 'show'])->name('show');
        Route::get('/{permohonan}/detail', [KartuKksController::class, 'detail'])->name('detail');
        Route::put('/{permohonan}/detail', [KartuKksController::class, 'updateDetail'])->name('detail.update');
        Route::post('/{permohonan}/proses', [KartuKksController::class, 'proses'])->name('proses');
    });

    // ==========================================
    // 5. DTSEN — sudah dibangun penuh
    // ==========================================
    Route::prefix('dtsen')->name('dtsen.')->group(function () {
        Route::get('/ajuan', [DtsenController::class, 'ajuan'])->name('ajuan');
        Route::get('/arsip', [DtsenController::class, 'arsip'])->name('arsip');
        Route::get('/monitoring', [DtsenController::class, 'monitoring'])->name('monitoring');

        Route::get('/{permohonan}', [DtsenController::class, 'show'])->name('show');
        Route::get('/{permohonan}/detail', [DtsenController::class, 'detail'])->name('detail');
        Route::put('/{permohonan}/detail', [DtsenController::class, 'updateDetail'])->name('detail.update');
        Route::put('/{permohonan}/bansos', [DtsenController::class, 'updateBansos'])->name('bansos.update');
        Route::post('/{permohonan}/proses', [DtsenController::class, 'proses'])->name('proses');
    });

    // ==========================================
    // 2, 6. Modul lain — kerangka menu dulu, fitur menyusul
    // (Asesmen SPMB, Kedaruratan Medis)
    // 'kartu-kks' dan 'dtsen' SUDAH DIHAPUS dari daftar placeholder ini
    // karena sudah dibangun penuh di atas.
    // ==========================================
    $placeholderModules = ['asesmen-spmb', 'kedaruratan-medis'];
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
}); // <-- GRUP ADMIN DITUTUP DI SINI


// ==========================================
// AREA MASYARAKAT (setelah login) — DIPINDAH KE LUAR grup admin,
// supaya TIDAK ikut kena middleware 'staff'.
// ==========================================
Route::middleware(['auth', 'role:masyarakat'])->prefix('akun-saya')->name('masyarakat.')->group(function () {
    Route::get('/', [MasyarakatController::class, 'dashboard'])->name('dashboard');

    Route::prefix('pbi-apbn/{pbiApbn}')->name('pbi-apbn.')->group(function () {
        Route::get('/lengkapi', [MasyarakatController::class, 'lengkapiData'])->name('lengkapi');
        Route::post('/lengkapi', [MasyarakatController::class, 'lengkapiDataStore'])->name('lengkapi.store');
    });
});
