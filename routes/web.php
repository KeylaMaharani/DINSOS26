<?php

use App\Http\Controllers\Admin\AkunMasyarakatController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DokumenController;
use App\Http\Controllers\Admin\DtsenController;
use App\Http\Controllers\Admin\KartuKksController;
use App\Http\Controllers\Admin\PbiApbdController;
use App\Http\Controllers\Admin\PbiApprovalController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ImpersonateController;
use App\Http\Controllers\Auth\LoginAdminController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\ProfilTtdController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegistrasiController;
use App\Http\Controllers\Layanan\KartuKksAjuanController;
use App\Http\Controllers\Layanan\DtsenAjuanController;
use App\Http\Controllers\Masyarakat\MasyarakatController;
use App\Http\Controllers\Masyarakat\MasyarakatDokumenController;
use App\Http\Controllers\Admin\KriteriaController;
use App\Http\Controllers\Admin\GcKriteriaController;
use App\Http\Controllers\Admin\GroundCheckController;
use App\Http\Controllers\Admin\MenuPlaceholderController;
use App\Http\Controllers\Admin\PbiKelurahanController;
use App\Http\Controllers\Admin\RehabsosController;
use App\Http\Controllers\Admin\SigapBencanaController;
use App\Http\Controllers\Admin\GcLapanganUsahaController;
use App\Http\Controllers\Admin\GcKeterampilanController;
use App\Http\Controllers\Admin\GcPenyakitKronisController;
// --- Ngopi Pagi ---
use App\Http\Controllers\Admin\NgopiPagiController as NgopiPagiAdminController;
use App\Http\Controllers\Admin\NgopiPagiKonselorController;
use App\Http\Controllers\Admin\NgopiPagiSpesialisasiController;
use App\Http\Controllers\NgopiPagiPublikController;
use App\Models\NgopiPagiKonsultasi;
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

Route::redirect('/cek-densil', 'https://cekbansos.kemensos.go.id/')->name('cek-densil');
Route::get('/kejadian-bencana', [PageController::class, 'kejadianBencana'])->name('kejadian-bencana');
Route::get('/kesan', [PageController::class, 'kesan'])->name('kesan');
Route::get('/tutorial-pendaftaran', [PageController::class, 'tutorialPendaftaran'])->name('tutorial-pendaftaran');
Route::get('/ppid', [PageController::class, 'ppid'])->name('ppid');
Route::get('/kontak', [PageController::class, 'kontak'])->name('kontak');
Route::post('/kontak', [PageController::class, 'kontakStore'])->name('kontak.store')->middleware('throttle:5,1');
Route::get('/ppks-psks', [PageController::class, 'ppksPsks'])->name('ppks-psks');
Route::get('/regulasi', [PageController::class, 'regulasi'])->name('regulasi');

// ==========================================
// NGOPI PAGI (publik, tanpa login)
// ==========================================
Route::prefix('ngopi-pagi')->name('ngopi-pagi.')->group(function () {
    $token = '[A-Za-z0-9]{40}';

    Route::get('/', [NgopiPagiPublikController::class, 'index'])->name('index');                           
    Route::get('/spesialisasi/{spesialisasi:slug}', [NgopiPagiPublikController::class, 'spesialisasi'])->name('spesialisasi'); 
    Route::get('/mulai/{konselor?}', [NgopiPagiPublikController::class, 'mulai'])->whereNumber('konselor')->name('mulai');     
    Route::post('/mulai/{konselor?}', [NgopiPagiPublikController::class, 'store'])->whereNumber('konselor')->name('store')->middleware('throttle:5,10');

    Route::get('/chat/{token}', [NgopiPagiPublikController::class, 'chat'])->where('token', $token)->name('chat');
    Route::get('/chat/{token}/pesan', [NgopiPagiPublikController::class, 'pesan'])->where('token', $token)->middleware('throttle:90,1')->name('chat.pesan');
    Route::post('/chat/{token}/pesan', [NgopiPagiPublikController::class, 'kirim'])->where('token', $token)->middleware('throttle:30,1')->name('chat.kirim');
    Route::post('/chat/{token}/konfirmasi', [NgopiPagiPublikController::class, 'konfirmasi'])->where('token', $token)->middleware('throttle:10,1')->name('chat.konfirmasi');
});


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

Route::post('/logout', [LoginAdminController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/ganti-tampilan/{mode}', function (string $mode) {
    session(['view_mode' => $mode]);   
    return back();
})->whereIn('mode', ['mobile', 'desktop', 'auto'])->name('ganti-tampilan');

Route::post('/kembali-ke-admin', [ImpersonateController::class, 'stop'])
    ->middleware('auth')
    ->name('impersonate.stop');

// ==========================================
// NOTIFIKASI & PERSETUJUAN
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::post('/notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');

    Route::post('/izin-masuk/{izin}/setujui', [ImpersonateController::class, 'setujui'])->name('izin.setujui');
    Route::post('/izin-masuk/{izin}/tolak', [ImpersonateController::class, 'tolak'])->name('izin.tolak');
});


// ==========================================
// ADMIN / PROTECTED ROUTES
// ==========================================
Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('module:beranda')->name('dashboard');

    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

    Route::post('/profil/ttd', [ProfilTtdController::class, 'store'])->name('profil.ttd.store');
    Route::delete('/profil/ttd', [ProfilTtdController::class, 'destroy'])->name('profil.ttd.destroy');

    Route::prefix('kelola-role-user')->name('akun.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');

        Route::get('/izin-masuk/{izin}', [ImpersonateController::class, 'show'])->name('izin.show');
        Route::get('/izin-masuk/{izin}/status', [ImpersonateController::class, 'status'])->name('izin.status');
        Route::post('/izin-masuk/{izin}/verifikasi', [ImpersonateController::class, 'verifikasi'])
            ->middleware('throttle:10,1')->name('izin.verifikasi');

        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{akun:id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{akun:id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{akun:id}', [UserController::class, 'destroy'])->name('destroy');

        Route::post('/{akun:id}/masuk-sebagai', [ImpersonateController::class, 'minta'])->name('impersonate');

        Route::post('/role', [UserController::class, 'storeRole'])->name('role.store');
        Route::get('/role/{role}/edit', [UserController::class, 'editRole'])->name('role.edit');
        Route::put('/role/{role}', [UserController::class, 'updateRole'])->name('role.update');
        Route::delete('/role/{role}', [UserController::class, 'destroyRole'])->name('role.destroy');
    });

    Route::prefix('kelola-akses-masyarakat')->name('akun-masyarakat.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [AkunMasyarakatController::class, 'index'])->name('index');
    });

    Route::prefix('layanan/kartu-kks')->name('layanan.kartu-kks.')->middleware('module:layanan-kartu-kks')->group(function () {
        Route::get('/ajukan', [KartuKksAjuanController::class, 'create'])->middleware('module:layanan-kartu-kks.ajukan')->name('ajukan');
        Route::post('/ajukan', [KartuKksAjuanController::class, 'store'])->middleware('module:layanan-kartu-kks.ajukan.tambah')->name('ajukan.store');
        Route::get('/riwayat', [KartuKksAjuanController::class, 'riwayat'])->middleware('module:layanan-kartu-kks.riwayat')->name('riwayat');
    });

    Route::prefix('layanan/dtsen')->name('layanan.dtsen.')->middleware('module:layanan-dtsen')->group(function () {
        Route::get('/ajukan', [DtsenAjuanController::class, 'create'])->middleware('module:layanan-dtsen.ajukan')->name('ajukan');
        Route::post('/ajukan', [DtsenAjuanController::class, 'store'])->middleware('module:layanan-dtsen.ajukan.tambah')->name('ajukan.store');
        Route::get('/riwayat', [DtsenAjuanController::class, 'riwayat'])->middleware('module:layanan-dtsen.riwayat')->name('riwayat');
    });

    Route::prefix('pbi-apbd')->name('pbi-apbd.')->middleware('module:pbi-apbd')->group(function () {
        Route::get('/ajuan', [PbiApbdController::class, 'ajuanIndex'])->middleware('module:pbi-apbd.ajuan')->name('ajuan.index');
        Route::get('/ajuan/{pbiApbd}', [PbiApbdController::class, 'ajuanShow'])->middleware('module:pbi-apbd.ajuan')->name('ajuan.show');
        Route::post('/ajuan/{pbiApbd}/aksi', [PbiApbdController::class, 'ajuanAksi'])->middleware('module:pbi-apbd.ajuan.proses')->name('ajuan.aksi');
        Route::put('/ajuan/{pbiApbd}/tambahan', [PbiApbdController::class, 'ajuanUpdateTambahan'])->middleware('module:pbi-apbd.ajuan.ubah')->name('ajuan.updateTambahan');
        Route::post('/ajuan/{pbiApbd}/diagnosa', [PbiApbdController::class, 'ajuanTambahDiagnosa'])->middleware('module:pbi-apbd.ajuan.tambah')->name('ajuan.diagnosa.store');

        Route::get('/arsip', [PbiApbdController::class, 'arsipIndex'])->middleware('module:pbi-apbd.arsip')->name('arsip.index');
        Route::get('/arsip/{pbiApbd}/detail', [PbiApbdController::class, 'arsipDetail'])->middleware('module:pbi-apbd.arsip')->name('arsip.detail');
        Route::get('/monitoring', [PbiApbdController::class, 'monitoringIndex'])->middleware('module:pbi-apbd.monitoring')->name('monitoring.index');
        Route::get('/log', [PbiApbdController::class, 'logIndex'])->middleware('module:pbi-apbd.log')->name('log.index');
    });

    Route::prefix('pbi-kelurahan')->name('pbi-kelurahan.')->middleware('module:pbi-kelurahan.daftar')->group(function () {
        Route::get('/', [PbiKelurahanController::class, 'index'])->name('index');
        Route::get('/{pbiApbd}', [PbiKelurahanController::class, 'show'])->name('show');
        Route::post('/{pbiApbd}/aksi', [PbiKelurahanController::class, 'aksi'])->middleware('module:pbi-kelurahan.daftar.proses')->name('aksi');
    });

    foreach (\App\Models\PbiApbd::TAHAP as $tahapKey => $tahapCfg) {
        $modulTahap = $tahapCfg['module'];

        Route::prefix("pbi-{$tahapKey}")
            ->name("pbi-approval.{$tahapKey}.")
            ->middleware("module:{$modulTahap}.daftar")
            ->group(function () use ($tahapCfg, $modulTahap) {
                Route::get('/', [PbiApprovalController::class, 'index'])->name('index');
                Route::post('/aksi', [PbiApprovalController::class, 'aksi'])
                    ->middleware("module:{$modulTahap}.daftar.proses")->name('aksi');
                Route::get('/{pbiApbd}', [PbiApprovalController::class, 'show'])->name('show');

                if ($tahapCfg['diagnosa']) {
                    Route::put('/{pbiApbd}/diagnosa', [PbiApprovalController::class, 'diagnosa'])
                        ->middleware("module:{$modulTahap}.daftar.ubah")->name('diagnosa');
                }
                if ($tahapCfg['edit']) {
                    Route::put('/{pbiApbd}', [PbiApprovalController::class, 'update'])
                        ->middleware("module:{$modulTahap}.daftar.ubah")->name('update');
                }
            });
    }

    Route::prefix('master/kriteria')->name('master.kriteria.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [KriteriaController::class, 'index'])->name('index');
        Route::get('/tambah', [KriteriaController::class, 'create'])->name('create');
        Route::post('/', [KriteriaController::class, 'store'])->name('store');
        Route::get('/{parameter}/edit', [KriteriaController::class, 'edit'])->name('edit');
        Route::put('/{parameter}', [KriteriaController::class, 'update'])->name('update');
        Route::delete('/{parameter}', [KriteriaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('master/kriteria-ground-check')->name('master.gc-kriteria.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [GcKriteriaController::class, 'index'])->name('index');
        Route::get('/tambah', [GcKriteriaController::class, 'create'])->name('create');
        Route::post('/', [GcKriteriaController::class, 'store'])->name('store');
        Route::get('/{parameter}/edit', [GcKriteriaController::class, 'edit'])->name('edit');
        Route::put('/{parameter}', [GcKriteriaController::class, 'update'])->name('update');
        Route::delete('/{parameter}', [GcKriteriaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('master/lapangan-usaha')->name('master.lapangan-usaha.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [GcLapanganUsahaController::class, 'index'])->name('index');
        Route::get('/tambah', [GcLapanganUsahaController::class, 'create'])->name('create');
        Route::post('/', [GcLapanganUsahaController::class, 'store'])->name('store');
        Route::get('/{lapanganUsaha}/edit', [GcLapanganUsahaController::class, 'edit'])->name('edit');
        Route::put('/{lapanganUsaha}', [GcLapanganUsahaController::class, 'update'])->name('update');
        Route::delete('/{lapanganUsaha}', [GcLapanganUsahaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('master/keterampilan')->name('master.keterampilan.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [GcKeterampilanController::class, 'index'])->name('index');
        Route::post('/', [GcKeterampilanController::class, 'store'])->name('store');
        Route::put('/{keterampilan}', [GcKeterampilanController::class, 'update'])->name('update');
        Route::delete('/{keterampilan}', [GcKeterampilanController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('master/penyakit-kronis')->name('master.penyakit-kronis.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [GcPenyakitKronisController::class, 'index'])->name('index');
        Route::post('/', [GcPenyakitKronisController::class, 'store'])->name('store');
        Route::put('/{penyakit}', [GcPenyakitKronisController::class, 'update'])->name('update');
        Route::delete('/{penyakit}', [GcPenyakitKronisController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('ground-check')->name('ground-check.')->middleware('module:ground-check')->group(function () {
        Route::get('/', [GroundCheckController::class, 'index'])->middleware('module:ground-check.daftar')->name('index');
        Route::get('/keluarga/{noKk}', [GroundCheckController::class, 'keluarga'])->middleware('module:ground-check.daftar')->name('keluarga');
        Route::post('/{sasaran}/status', [GroundCheckController::class, 'statusStore'])->whereNumber('sasaran')->middleware('module:ground-check.daftar.ubah')->name('status');
        Route::get('/{sasaran}/form', [GroundCheckController::class, 'form'])->whereNumber('sasaran')->middleware('module:ground-check.daftar')->name('form');
        Route::post('/{sasaran}/form', [GroundCheckController::class, 'simpan'])->whereNumber('sasaran')->middleware('module:ground-check.daftar.ubah')->name('simpan');
        Route::post('/', [GroundCheckController::class, 'store'])->middleware('module:ground-check.daftar.tambah')->name('store');
    });

    Route::prefix('kartu-kks')->name('kartu-kks.')->middleware('module:kartu-kks')->group(function () {
        Route::get('/ajuan', [KartuKksController::class, 'ajuan'])->middleware('module:kartu-kks.ajuan')->name('ajuan');
        Route::get('/arsip', [KartuKksController::class, 'arsip'])->middleware('module:kartu-kks.arsip')->name('arsip');
        Route::get('/monitoring', [KartuKksController::class, 'monitoring'])->middleware('module:kartu-kks.monitoring')->name('monitoring');
        Route::get('/log', [KartuKksController::class, 'logIndex'])->middleware('module:kartu-kks.log')->name('log');

        Route::get('/{permohonan}', [KartuKksController::class, 'show'])->middleware('module:kartu-kks.ajuan')->name('show');
        Route::get('/{permohonan}/detail', [KartuKksController::class, 'detail'])->middleware('module:kartu-kks.ajuan')->name('detail');
        Route::put('/{permohonan}/detail', [KartuKksController::class, 'updateDetail'])->middleware('module:kartu-kks.ajuan.ubah')->name('detail.update');
        Route::post('/{permohonan}/proses', [KartuKksController::class, 'proses'])->middleware('module:kartu-kks.ajuan.proses')->name('proses');
    });

    Route::prefix('dtsen')->name('dtsen.')->middleware('module:dtsen')->group(function () {
        Route::get('/ajuan', [DtsenController::class, 'ajuan'])->middleware('module:dtsen.ajuan')->name('ajuan');
        Route::get('/arsip', [DtsenController::class, 'arsip'])->middleware('module:dtsen.arsip')->name('arsip');
        Route::get('/monitoring', [DtsenController::class, 'monitoring'])->middleware('module:dtsen.monitoring')->name('monitoring');

        Route::get('/{permohonan}', [DtsenController::class, 'show'])->middleware('module:dtsen.ajuan')->name('show');
        Route::get('/{permohonan}/detail', [DtsenController::class, 'detail'])->middleware('module:dtsen.ajuan')->name('detail');
        Route::put('/{permohonan}/detail', [DtsenController::class, 'updateDetail'])->middleware('module:dtsen.ajuan.ubah')->name('detail.update');
        Route::put('/{permohonan}/bansos', [DtsenController::class, 'updateBansos'])->middleware('module:dtsen.ajuan.ubah')->name('bansos.update');
        Route::put('/{permohonan}/lampiran', [DtsenController::class, 'updateLampiran'])->middleware('module:dtsen.ajuan.ubah')->name('lampiran.update');
        Route::post('/{permohonan}/proses', [DtsenController::class, 'proses'])->middleware('module:dtsen.ajuan.proses')->name('proses');
    });

    $placeholderModules = ['asesmen-spmb', 'kedaruratan-medis'];
    foreach ($placeholderModules as $module) {
        Route::prefix($module)->name(str_replace('-', '_', $module) . '.')->middleware("module:{$module}")->group(function () use ($module) {
            foreach (['ajuan', 'arsip', 'monitoring', 'log'] as $tab) {
                Route::get("/{$tab}", [PlaceholderController::class, 'tab'])
                    ->defaults('module', $module)
                    ->defaults('tab', $tab)
                    ->name($tab);
            }
        });
    }

    Route::prefix('sigap-bencana')->name('sigap.')->group(function () {
        Route::middleware('module:sigap-bencana')->group(function () {
            Route::get('/laporan', [SigapBencanaController::class, 'index'])->middleware('module:sigap-bencana.laporan')->name('index');
            Route::get('/stok', [SigapBencanaController::class, 'stokIndex'])->middleware('module:sigap-bencana.stok')->name('stok.index');
            Route::post('/stok/barang', [SigapBencanaController::class, 'stokStoreBarang'])->middleware('module:sigap-bencana.stok.tambah');
        });
    });
});