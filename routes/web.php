<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DtsenController;
use App\Http\Controllers\Admin\KartuKksController;
use App\Http\Controllers\Admin\PbiApbdController;
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
use App\Http\Controllers\Admin\KriteriaController;
use App\Http\Controllers\Admin\MenuPlaceholderController;
use App\Http\Controllers\Admin\PbiKelurahanController;
use App\Http\Controllers\Admin\RehabsosController;
use App\Http\Controllers\Admin\SigapBencanaController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('module:beranda')->name('dashboard');
    Route::post('/logout', [LoginAdminController::class, 'destroy'])->name('logout');

    // Edit profil akun sendiri (dipakai di dropdown pojok kanan atas, tampil sebagai pop-up)
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

    // Menu tunggal "Kelola User & Role" dengan tab: Pengguna | Hak Akses
    // 'kelola-akses' TIDAK ada di daftar checkbox Role::MODULES, jadi middleware
    // ini otomatis HANYA meloloskan Super Admin (role lain tidak mungkin punya
    // string 'kelola-akses' di kolom permissions-nya karena tidak pernah ditawarkan).
    Route::prefix('kelola-role-user')->name('akun.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');

        // Tab Pengguna
        Route::post('/', [UserController::class, 'store'])->name('store');
        // ':id' dipasang eksplisit supaya binding SELALU lewat kolom id,
        // apa pun pengaturan getRouteKeyName() di model User -- ini yang
        // dipakai $user->id di JS (openEdit, form update, tombol hapus),
        // jadi harus konsisten dengan itu.
        Route::get('/{akun:id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{akun:id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{akun:id}', [UserController::class, 'destroy'])->name('destroy');

        // Tab Hak Akses (Role)
        Route::post('/role', [UserController::class, 'storeRole'])->name('role.store');
        Route::get('/role/{role}/edit', [UserController::class, 'editRole'])->name('role.edit');
        Route::put('/role/{role}', [UserController::class, 'updateRole'])->name('role.update');
        Route::delete('/role/{role}', [UserController::class, 'destroyRole'])->name('role.destroy');
    });

    // ==========================================
    // Layanan Kelurahan — Ajukan & Riwayat Kartu KKS
    // Dibatasi modul 'layanan-kartu-kks' (centang di Kelola Hak Akses).
    // ==========================================
    Route::prefix('layanan/kartu-kks')->name('layanan.kartu-kks.')->middleware('module:layanan-kartu-kks')->group(function () {
        Route::get('/ajukan', [KartuKksAjuanController::class, 'create'])->name('ajukan');
        Route::post('/ajukan', [KartuKksAjuanController::class, 'store'])->name('ajukan.store');
        Route::get('/riwayat', [KartuKksAjuanController::class, 'riwayat'])->name('riwayat');
    });

    // ==========================================
    // Layanan Kelurahan — Ajukan & Riwayat DTSEN
    // Dibatasi modul 'layanan-dtsen' (centang di Kelola Hak Akses).
    // ==========================================
    Route::prefix('layanan/dtsen')->name('layanan.dtsen.')->middleware('module:layanan-dtsen')->group(function () {
        Route::get('/ajukan', [DtsenAjuanController::class, 'create'])->name('ajukan');
        Route::post('/ajukan', [DtsenAjuanController::class, 'store'])->name('ajukan.store');
        Route::get('/riwayat', [DtsenAjuanController::class, 'riwayat'])->name('riwayat');
    });

    // ==========================================
    // 3. PBI APBD — sudah dibangun penuh
    // ==========================================
    Route::prefix('pbi-apbd')->name('pbi-apbd.')->middleware('module:pbi-apbd')->group(function () {
        Route::get('/ajuan', [PbiApbdController::class, 'ajuanIndex'])->name('ajuan.index');
        Route::get('/ajuan/{pbiApbd}', [PbiApbdController::class, 'ajuanShow'])->name('ajuan.show');
        Route::post('/ajuan/{pbiApbd}/aksi', [PbiApbdController::class, 'ajuanAksi'])->name('ajuan.aksi');
        Route::put('/ajuan/{pbiApbd}/tambahan', [PbiApbdController::class, 'ajuanUpdateTambahan'])->name('ajuan.updateTambahan');
        Route::post('/ajuan/{pbiApbd}/diagnosa', [PbiApbdController::class, 'ajuanTambahDiagnosa'])->name('ajuan.diagnosa.store');

        Route::get('/arsip', [PbiApbdController::class, 'arsipIndex'])->name('arsip.index');
        Route::get('/arsip/{pbiApbd}/detail', [PbiApbdController::class, 'arsipDetail'])->name('arsip.detail');
        Route::get('/monitoring', [PbiApbdController::class, 'monitoringIndex'])->name('monitoring.index');
        Route::get('/log', [PbiApbdController::class, 'logIndex'])->name('log.index');
    });

    // Halaman Kelurahan (PBI APBD)
    Route::prefix('pbi-kelurahan')->name('pbi-kelurahan.')->middleware('module:pbi-kelurahan')->group(function () {
        Route::get('/', [PbiKelurahanController::class, 'index'])->name('index');
        Route::get('/{pbiApbd}', [PbiKelurahanController::class, 'show'])->name('show');
        Route::post('/{pbiApbd}/aksi', [PbiKelurahanController::class, 'aksi'])->name('aksi');
    });

    // Master Kriteria Kemiskinan (hanya Super Admin, sama seperti Kelola Akses)
    Route::prefix('master/kriteria')->name('master.kriteria.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [KriteriaController::class, 'index'])->name('index');
        Route::get('/tambah', [KriteriaController::class, 'create'])->name('create');
        Route::post('/', [KriteriaController::class, 'store'])->name('store');
        Route::get('/{parameter}/edit', [KriteriaController::class, 'edit'])->name('edit');
        Route::put('/{parameter}', [KriteriaController::class, 'update'])->name('update');
        Route::delete('/{parameter}', [KriteriaController::class, 'destroy'])->name('destroy');
    });

    // ==========================================
    // 4. Kartu KKS — sudah dibangun penuh (punya Tika)
    // PENTING: rute /ajuan, /arsip, /monitoring, /log HARUS ada
    // sebelum rute wildcard /{permohonan}, agar tidak "ketangkap"
    // oleh wildcard tersebut.
    // ==========================================
    Route::prefix('kartu-kks')->name('kartu-kks.')->middleware('module:kartu-kks')->group(function () {
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
    Route::prefix('dtsen')->name('dtsen.')->middleware('module:dtsen')->group(function () {
        Route::get('/ajuan', [DtsenController::class, 'ajuan'])->name('ajuan');
        Route::get('/arsip', [DtsenController::class, 'arsip'])->name('arsip');
        Route::get('/monitoring', [DtsenController::class, 'monitoring'])->name('monitoring');

        Route::get('/{permohonan}', [DtsenController::class, 'show'])->name('show');
        Route::get('/{permohonan}/detail', [DtsenController::class, 'detail'])->name('detail');
        Route::put('/{permohonan}/detail', [DtsenController::class, 'updateDetail'])->name('detail.update');
        Route::put('/{permohonan}/bansos', [DtsenController::class, 'updateBansos'])->name('bansos.update');
        Route::put('/{permohonan}/lampiran', [DtsenController::class, 'updateLampiran'])->name('lampiran.update');
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
        Route::prefix($module)->name(str_replace('-', '_', $module) . '.')->middleware("module:{$module}")->group(function () use ($module) {
            foreach (['ajuan', 'arsip', 'monitoring', 'log'] as $tab) {
                Route::get("/{$tab}", [PlaceholderController::class, 'tab'])
                    ->defaults('module', $module)
                    ->defaults('tab', $tab)
                    ->name($tab);
            }
        });
    }

    // ==========================================
    // SIGAP BENCANA (Perlinsos) -- Laporan Bantuan Bencana
    //   module sigap-bencana : staf Dinsos (Tagana, Perlinsos, Pengurus Barang, Kadis)
    //   module sigap-proses  : Kelurahan / OPD wilayah (hanya menu Proses Bantuan Kebencanaan)
    // ==========================================
    Route::prefix('sigap-bencana')->name('sigap.')->group(function () {

        // --- Staf Dinsos ---
        Route::middleware('module:sigap-bencana')->group(function () {
            Route::get('/laporan', [SigapBencanaController::class, 'index'])->name('index');

            Route::get('/stok', [SigapBencanaController::class, 'stokIndex'])->name('stok.index');
            Route::post('/stok/barang', [SigapBencanaController::class, 'stokStoreBarang'])->name('stok.barang.store');
            Route::post('/stok/barang/{barang}/masuk', [SigapBencanaController::class, 'stokMasuk'])->name('stok.masuk');
        });

        // --- Kelurahan / OPD wilayah ---
        Route::middleware('module:sigap-proses')->group(function () {
            Route::get('/proses', [SigapBencanaController::class, 'prosesIndex'])->name('proses.index');
        });

        // --- Dipakai kedua kelompok (dicek lagi per role & wilayah di controller) ---
        Route::middleware('module:sigap-bencana,sigap-proses')->group(function () {
            Route::get('/buat', [SigapBencanaController::class, 'create'])->name('create');
            Route::post('/buat', [SigapBencanaController::class, 'store'])->name('store');

            Route::get('/{laporan}', [SigapBencanaController::class, 'show'])->name('show');
            Route::patch('/{laporan}/lokasi', [SigapBencanaController::class, 'updateLokasi'])->name('lokasi');
            Route::post('/{laporan}/proses', [SigapBencanaController::class, 'aksi'])->name('aksi');
        });
    });

    // ==========================================
    // PFM -- Beranda monitoring PFM (kerangka; tampilan menyusul)
    // ==========================================
    Route::get('/pfm/beranda', [MenuPlaceholderController::class, 'show'])
        ->middleware('module:pbi-apbd,kartu-kks,dtsen')
        ->defaults('judul', 'PFM')->defaults('label', 'Beranda')
        ->name('pfm.beranda');

    // ==========================================
    // PERLINSOS -- menu tambahan (kerangka): Beranda & Laporan SIGAP
    //   (Laporan Bantuan Bencana & Stock Barang sudah dibangun penuh di atas)
    // ==========================================
    Route::get('/perlinsos/dashboard', [MenuPlaceholderController::class, 'show'])
        ->middleware('module:sigap-bencana')
        ->defaults('judul', 'Perlinsos')->defaults('label', 'Beranda')
        ->name('perlinsos.dashboard');

    Route::get('/perlinsos/laporan', [MenuPlaceholderController::class, 'show'])
        ->middleware('module:sigap-bencana')
        ->defaults('judul', 'Perlinsos - SIGAP Bencana')->defaults('label', 'Laporan')
        ->name('perlinsos.laporan');

    // ==========================================
    // DAYASOS -- kerangka menu saja (Dashboard, Ngopi Pagi, Kesan)
    //   Struktur dibaca dari config/dayasos.php; setiap menu sementara
    //   menampilkan halaman placeholder. Anak menu mengikuti module induknya.
    // ==========================================
    $daftarkanDayasos = function (array $nodes, $moduleInduk = null) use (&$daftarkanDayasos) {
        foreach ($nodes as $n) {
            $module = $n['module'] ?? $moduleInduk;

            if (isset($n['children'])) {
                $daftarkanDayasos($n['children'], $module);
                continue;
            }

            Route::get('/' . $n['url'], [MenuPlaceholderController::class, 'show'])
                ->middleware('module:' . implode(',', (array) $module))
                ->defaults('judul', 'Dayasos')
                ->defaults('label', $n['label'])
                ->name($n['route']);
        }
    };
    $daftarkanDayasos(config('dayasos', []));

    // ==========================================
    // MENU FRONTEND -- kerangka menu saja
    //   Struktur dibaca dari config/frontend.php; setiap menu sementara
    //   menampilkan halaman placeholder. Semua mengikuti module 'menu-frontend'.
    // ==========================================
    $daftarkanFrontend = function (array $nodes, $moduleInduk) use (&$daftarkanFrontend) {
        foreach ($nodes as $n) {
            $module = $n['module'] ?? $moduleInduk;

            if (isset($n['children'])) {
                $daftarkanFrontend($n['children'], $module);
                continue;
            }

            Route::get('/' . $n['url'], [MenuPlaceholderController::class, 'show'])
                ->middleware('module:' . implode(',', (array) $module))
                ->defaults('judul', 'Menu Frontend')
                ->defaults('label', $n['label'])
                ->name($n['route']);
        }
    };
    $daftarkanFrontend(config('frontend', []), 'menu-frontend');

    // ==========================================
    // REHABSOS -- Dashboard + PPKS + Rumah Singgah + Data Permohonan Bantuan
    //   Struktur menu dibaca dari config/rehabsos.php
    //   module rehabsos-ppks / rehabsos-rumah-singgah / rehabsos-bantuan
    //   Dashboard terbuka untuk role yang punya salah satu dari ketiga modul.
    // ==========================================
    Route::prefix('rehabsos')->name('rehabsos.')->group(function () {
        Route::get('/dashboard', [RehabsosController::class, 'dashboard'])
            ->middleware('module:rehabsos-ppks,rehabsos-rumah-singgah,rehabsos-bantuan')
            ->name('dashboard');

        foreach (config('rehabsos.groups', []) as $gKey => $g) {
            Route::prefix($gKey)->name($gKey . '.')->middleware('module:' . $g['module'])->group(function () use ($gKey, $g) {
                foreach ($g['items'] as $iKey => $label) {
                    Route::get("/{$iKey}", [RehabsosController::class, 'tab'])
                        ->defaults('group', $gKey)
                        ->defaults('item', $iKey)
                        ->name($iKey);
                }
            });
        }
    });

    // ==========================================
    // 7. Dokumen — template dokumen
    // ==========================================
    Route::get('/dokumen', [PlaceholderController::class, 'dokumen'])->middleware('module:dokumen')->name('dokumen.index');
}); // <-- GRUP ADMIN DITUTUP DI SINI



Route::middleware(['auth', 'role:masyarakat'])->prefix('akun-saya')->name('masyarakat.')->group(function () {
    Route::get('/', [MasyarakatController::class, 'dashboard'])->name('dashboard');

    Route::prefix('pbi-apbd/{pbiApbd}')->name('pbi-apbd.')->group(function () {
        Route::get('/lengkapi', [MasyarakatController::class, 'lengkapiData'])->name('lengkapi');
        Route::post('/lengkapi', [MasyarakatController::class, 'lengkapiDataStore'])->name('lengkapi.store');
    });
});
