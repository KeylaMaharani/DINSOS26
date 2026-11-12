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
use App\Http\Controllers\Masyarakat\PbiKeluargaController;
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
use App\Http\Controllers\Admin\PfmDashboardController;
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

// Cek Desil di website sudah dialihkan ke Cek Bansos Kemensos.
// Nama route 'cek-densil' dipertahankan supaya route('cek-densil') / bookmark lama tidak error.
Route::redirect('/cek-densil', 'https://cekbansos.kemensos.go.id/')->name('cek-densil');
Route::get('/kejadian-bencana', [PageController::class, 'kejadianBencana'])->name('kejadian-bencana');
Route::get('/kesan', [PageController::class, 'kesan'])->name('kesan');
Route::get('/tutorial-pendaftaran', [PageController::class, 'tutorialPendaftaran'])->name('tutorial-pendaftaran');
Route::get('/ppid', [PageController::class, 'ppid'])->name('ppid');
Route::get('/kontak', [PageController::class, 'kontak'])->name('kontak');
Route::post('/kontak', [PageController::class, 'kontakStore'])->name('kontak.store')->middleware('throttle:5,1');
Route::get('/ppks-psks', [PageController::class, 'ppksPsks'])->name('ppks-psks');
Route::get('/lks', [PageController::class, 'lks'])->name('lks');
Route::get('/regulasi', [PageController::class, 'regulasi'])->name('regulasi');

// ==========================================
// NGOPI PAGI (publik, tanpa login) -- pilih spesialisasi -> pilih konselor -> isi data -> chat
//   Akses chat lewat token acak di URL.
// ==========================================
Route::prefix('ngopi-pagi')->name('ngopi-pagi.')->group(function () {
    $token = '[A-Za-z0-9]{40}';

    Route::get('/', [NgopiPagiPublikController::class, 'index'])->name('index');                                              // pilih spesialisasi
    Route::get('/spesialisasi/{spesialisasi:slug}', [NgopiPagiPublikController::class, 'spesialisasi'])->name('spesialisasi'); // daftar konselor
    Route::get('/mulai/{konselor?}', [NgopiPagiPublikController::class, 'mulai'])->whereNumber('konselor')->name('mulai');     // form data awal
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

// Logout dipakai SEMUA role (masyarakat & petugas), jadi hanya butuh login (auth),
// BUKAN middleware 'staff' -- kalau tidak, akun masyarakat kena 403 saat logout.
// Tujuan setelah logout diatur di LoginAdminController@destroy.
Route::post('/logout', [LoginAdminController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/ganti-tampilan/{mode}', function (string $mode) {
    session(['view_mode' => $mode]);   // mobile | desktop | auto
    return back();
})->whereIn('mode', ['mobile', 'desktop', 'auto'])->name('ganti-tampilan');

// ==========================================
// KEMBALI KE SUPER ADMIN (setelah "Masuk sebagai")
//   Sengaja HANYA middleware 'auth' -- keamanannya dijaga di controller:
//   butuh session 'impersonator_id' dan pemiliknya harus Super Admin.
// ==========================================
Route::post('/kembali-ke-admin', [ImpersonateController::class, 'stop'])
    ->middleware('auth')
    ->name('impersonate.stop');

// ==========================================
// NOTIFIKASI (lonceng) + PERSETUJUAN "MASUK SEBAGAI"
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::post('/notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');

    // Pemilik akun menyetujui / menolak permintaan masuk dari Super Admin
    Route::post('/izin-masuk/{izin}/setujui', [ImpersonateController::class, 'setujui'])->name('izin.setujui');
    Route::post('/izin-masuk/{izin}/tolak', [ImpersonateController::class, 'tolak'])->name('izin.tolak');
});


// ==========================================
// ADMIN / PROTECTED ROUTES
//
// HAK AKSES DETAIL: middleware 'module:' menerima kunci bertingkat (lihat config/akses.php):
//   module:modul                 -> boleh masuk modul
//   module:modul.submenu         -> boleh membuka halaman submenu
//   module:modul.submenu.aksi    -> boleh melakukan aksi (tambah / ubah / hapus / proses / unduh)
// Middleware modul pada group tetap dipertahankan; kunci submenu/aksi ditumpuk di tiap route.
// ==========================================
Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('module:beranda')->name('dashboard');

    // Edit profil akun sendiri (dipakai di dropdown pojok kanan atas, tampil sebagai pop-up)
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

    // Tanda tangan tersimpan (hanya role Lurah & Kadis; dicek di controller)
    Route::post('/profil/ttd', [ProfilTtdController::class, 'store'])->name('profil.ttd.store');
    Route::delete('/profil/ttd', [ProfilTtdController::class, 'destroy'])->name('profil.ttd.destroy');

    // Menu tunggal "Kelola User & Role" dengan tab: Pengguna | Hak Akses
    Route::prefix('kelola-role-user')->name('akun.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');

        // Super Admin: minta izin "Masuk sebagai" (butuh persetujuan pemilik akun + OTP).
        // Didaftarkan SEBELUM rute wildcard {akun:id} supaya tidak tertangkap olehnya.
        Route::get('/izin-masuk/{izin}', [ImpersonateController::class, 'show'])->name('izin.show');
        Route::get('/izin-masuk/{izin}/status', [ImpersonateController::class, 'status'])->name('izin.status');
        Route::post('/izin-masuk/{izin}/verifikasi', [ImpersonateController::class, 'verifikasi'])
            ->middleware('throttle:10,1')->name('izin.verifikasi');

        // Tab Pengguna
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{akun:id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{akun:id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{akun:id}', [UserController::class, 'destroy'])->name('destroy');

        // Super Admin: minta izin "Masuk sebagai" akun staf/masyarakat
        Route::post('/{akun:id}/masuk-sebagai', [ImpersonateController::class, 'minta'])->name('impersonate');

        // Tab Hak Akses (Role)
        Route::post('/role', [UserController::class, 'storeRole'])->name('role.store');
        Route::get('/role/{role}/edit', [UserController::class, 'editRole'])->name('role.edit');
        Route::put('/role/{role}', [UserController::class, 'updateRole'])->name('role.update');
        Route::delete('/role/{role}', [UserController::class, 'destroyRole'])->name('role.destroy');
    });

    // Menu "Kelola Akses Masyarakat" (Data Master)
    Route::prefix('kelola-akses-masyarakat')->name('akun-masyarakat.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [AkunMasyarakatController::class, 'index'])->name('index');
    });

    // ==========================================
    // Layanan Kelurahan — Ajukan & Riwayat Kartu KKS
    // ==========================================
    Route::prefix('layanan/kartu-kks')->name('layanan.kartu-kks.')->middleware('module:kartu-kks')->group(function () {
        Route::get('/ajukan', [KartuKksAjuanController::class, 'create'])->middleware('module:kartu-kks.ajuan.tambah')->name('ajukan');
        Route::post('/ajukan', [KartuKksAjuanController::class, 'store'])->middleware('module:kartu-kks.ajuan.tambah')->name('ajukan.store');
        Route::get('/riwayat', [KartuKksAjuanController::class, 'riwayat'])->middleware('module:kartu-kks.ajuan')->name('riwayat');
    });

    // ==========================================
    // Layanan Kelurahan — Ajukan & Riwayat DTSEN
    // ==========================================
    Route::prefix('layanan/dtsen')->name('layanan.dtsen.')->middleware('module:layanan-dtsen')->group(function () {
        Route::get('/ajukan', [DtsenAjuanController::class, 'create'])->middleware('module:layanan-dtsen.ajukan')->name('ajukan');
        Route::post('/ajukan', [DtsenAjuanController::class, 'store'])->middleware('module:layanan-dtsen.ajukan.tambah')->name('ajukan.store');
        Route::get('/riwayat', [DtsenAjuanController::class, 'riwayat'])->middleware('module:layanan-dtsen.riwayat')->name('riwayat');
    });

    // ==========================================
    // 3. PBI APBD — Ajuan / Arsip / Monitoring / Log (pintu masuk Super Admin, lintas tahap)
    // ==========================================
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

    // Halaman Kelurahan (PBI APBD)
    Route::prefix('pbi-kelurahan')->name('pbi-kelurahan.')->middleware('module:pbi-kelurahan.daftar')->group(function () {
        Route::get('/', [PbiKelurahanController::class, 'index'])->name('index');
        Route::get('/{pbiApbd}', [PbiKelurahanController::class, 'show'])->name('show');
        Route::post('/{pbiApbd}/aksi', [PbiKelurahanController::class, 'aksi'])->middleware('module:pbi-kelurahan.daftar.proses')->name('aksi');
    });

    // ==========================================
    // BAGIAN APPROVAL PBI APBD (halaman kerja tiap user)
    //   Operator Dinsos, Kabid, Kadis, Dinkes, BPJS -- masing-masing modul sendiri.
    //   URL berdiri sendiri per bagian, sama seperti pbi-kelurahan:
    //     /pbi-operator, /pbi-kabid, /pbi-kadis, /pbi-dinkes, /pbi-bpjs
    //   Nama route TETAP pbi-approval.{tahap}.* (dipakai sidebar & controller
    //   untuk membaca tahap), view-nya ada di admin/pbi-{tahap}/.
    //   Rute statis (/aksi) didaftarkan SEBELUM rute wildcard {pbiApbd}.
    //   PUT edit hanya ada untuk Operator (isi Desil Nasional); diagnosa hanya untuk Dinkes (ubah diagnosa).
    //   Kabid / Kadis / BPJS tidak punya rute edit sama sekali.
    //   Hak akses: {modul}.daftar = buka halaman, .daftar.proses = aksi, .daftar.ubah = ubah data.
    // ==========================================
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
                    // Mengubah diagnosa yang diisi masyarakat (tidak menambah baru)
                    Route::put('/{pbiApbd}/diagnosa', [PbiApprovalController::class, 'diagnosa'])
                        ->middleware("module:{$modulTahap}.daftar.ubah")->name('diagnosa');
                }
                if ($tahapCfg['edit']) {
                    Route::put('/{pbiApbd}', [PbiApprovalController::class, 'update'])
                        ->middleware("module:{$modulTahap}.daftar.ubah")->name('update');
                }
            });
    }

    // Master Kriteria Kemiskinan (hanya Super Admin)
    Route::prefix('master/kriteria')->name('master.kriteria.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [KriteriaController::class, 'index'])->name('index');
        Route::get('/tambah', [KriteriaController::class, 'create'])->name('create');
        Route::post('/', [KriteriaController::class, 'store'])->name('store');
        Route::get('/{parameter}/edit', [KriteriaController::class, 'edit'])->name('edit');
        Route::put('/{parameter}', [KriteriaController::class, 'update'])->name('update');
        Route::delete('/{parameter}', [KriteriaController::class, 'destroy'])->name('destroy');
    });

    // Master Kriteria Ground Check (hanya Super Admin)
    Route::prefix('master/kriteria-ground-check')->name('master.gc-kriteria.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [GcKriteriaController::class, 'index'])->name('index');
        Route::get('/tambah', [GcKriteriaController::class, 'create'])->name('create');
        Route::post('/', [GcKriteriaController::class, 'store'])->name('store');
        Route::get('/{parameter}/edit', [GcKriteriaController::class, 'edit'])->name('edit');
        Route::put('/{parameter}', [GcKriteriaController::class, 'update'])->name('update');
        Route::delete('/{parameter}', [GcKriteriaController::class, 'destroy'])->name('destroy');
    });

    // Master Lapangan Usaha + Pekerjaan (hanya Super Admin)
    Route::prefix('master/lapangan-usaha')->name('master.lapangan-usaha.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [GcLapanganUsahaController::class, 'index'])->name('index');
        Route::get('/tambah', [GcLapanganUsahaController::class, 'create'])->name('create');
        Route::post('/', [GcLapanganUsahaController::class, 'store'])->name('store');
        Route::get('/{lapanganUsaha}/edit', [GcLapanganUsahaController::class, 'edit'])->name('edit');
        Route::put('/{lapanganUsaha}', [GcLapanganUsahaController::class, 'update'])->name('update');
        Route::delete('/{lapanganUsaha}', [GcLapanganUsahaController::class, 'destroy'])->name('destroy');
    });

    // Master Keterampilan Khusus (hanya Super Admin)
    Route::prefix('master/keterampilan')->name('master.keterampilan.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [GcKeterampilanController::class, 'index'])->name('index');
        Route::post('/', [GcKeterampilanController::class, 'store'])->name('store');
        Route::put('/{keterampilan}', [GcKeterampilanController::class, 'update'])->name('update');
        Route::delete('/{keterampilan}', [GcKeterampilanController::class, 'destroy'])->name('destroy');
    });

    // Master Penyakit Kronis/Menahun (hanya Super Admin)
    Route::prefix('master/penyakit-kronis')->name('master.penyakit-kronis.')->middleware('module:kelola-akses')->group(function () {
        Route::get('/', [GcPenyakitKronisController::class, 'index'])->name('index');
        Route::post('/', [GcPenyakitKronisController::class, 'store'])->name('store');
        Route::put('/{penyakit}', [GcPenyakitKronisController::class, 'update'])->name('update');
        Route::delete('/{penyakit}', [GcPenyakitKronisController::class, 'destroy'])->name('destroy');
    });

    // ==========================================
    // GROUND CHECK 2026
    //   Rute statis (/keluarga/...) HARUS sebelum rute wildcard {sasaran}.
    //   Hak akses: ground-check.daftar = buka, .tambah = tambah sasaran, .ubah = isi form & ubah status.
    // ==========================================
    Route::prefix('ground-check')->name('ground-check.')->middleware('module:ground-check')->group(function () {
        Route::get('/', [GroundCheckController::class, 'index'])->middleware('module:ground-check.daftar')->name('index');
        Route::get('/keluarga/{noKk}', [GroundCheckController::class, 'keluarga'])->middleware('module:ground-check.daftar')->name('keluarga');
        Route::post('/{sasaran}/status', [GroundCheckController::class, 'statusStore'])->whereNumber('sasaran')->middleware('module:ground-check.daftar.ubah')->name('status');
        Route::get('/{sasaran}/form', [GroundCheckController::class, 'form'])->whereNumber('sasaran')->middleware('module:ground-check.daftar')->name('form');
        Route::post('/{sasaran}/form', [GroundCheckController::class, 'simpan'])->whereNumber('sasaran')->middleware('module:ground-check.daftar.ubah')->name('simpan');
        Route::post('/', [GroundCheckController::class, 'store'])->middleware('module:ground-check.daftar.tambah')->name('store');
    });

    // ==========================================
    // 4. Kartu KKS
    // PENTING: rute /ajuan, /arsip, /monitoring, /log HARUS sebelum wildcard /{permohonan}.
    // ==========================================
    Route::prefix('kartu-kks')->name('kartu-kks.')->middleware('module:kartu-kks')->group(function () {
        Route::get('/ajuan', [KartuKksController::class, 'ajuan'])->middleware('module:kartu-kks.ajuan')->name('ajuan');
        Route::get('/arsip', [KartuKksController::class, 'arsip'])->middleware('module:kartu-kks.arsip')->name('arsip');
        Route::get('/monitoring', [KartuKksController::class, 'monitoring'])->middleware('module:kartu-kks.monitoring')->name('monitoring');
        Route::get('/log', [KartuKksController::class, 'logIndex'])->middleware('module:kartu-kks.log')->name('log');
        
        Route::get('/{permohonan}', [KartuKksController::class, 'show'])->middleware('module:kartu-kks.ajuan')->name('show');
        Route::get('/{permohonan}/detail', [KartuKksController::class, 'detail'])->middleware('module:kartu-kks.ajuan')->name('detail');
        Route::put('/{permohonan}/detail', [KartuKksController::class, 'updateDetail'])->middleware('module:kartu-kks.ajuan.ubah')->name('detail.update');
        Route::post('/{permohonan}/proses', [KartuKksController::class, 'proses'])->middleware('module:kartu-kks.ajuan.proses')->name('proses');

        Route::post('/{permohonan}/surat', [KartuKksController::class, 'buatSurat'])->middleware('module:kartu-kks.ajuan.ubah')->name('surat.buat');
    });

    // ==========================================
    // 5. DTSEN
    // ==========================================
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

    // ==========================================
    // 2, 6. Modul lain — kerangka menu dulu (Asesmen SPMB, Kedaruratan Medis)
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
    // SIGAP BENCANA (Perlinsos)
    //   Hak akses detail hanya untuk halaman Dinsos (Laporan & Stock Barang).
    //   Buat/lihat/proses laporan dipakai bersama Kelurahan/OPD, jadi tetap di level modul.
    // ==========================================
    Route::prefix('sigap-bencana')->name('sigap.')->group(function () {

        // --- Staf Dinsos ---
        Route::middleware('module:sigap-bencana')->group(function () {
            Route::get('/laporan', [SigapBencanaController::class, 'index'])->middleware('module:sigap-bencana.laporan')->name('index');

            Route::get('/stok', [SigapBencanaController::class, 'stokIndex'])->middleware('module:sigap-bencana.stok')->name('stok.index');
            Route::post('/stok/barang', [SigapBencanaController::class, 'stokStoreBarang'])->middleware('module:sigap-bencana.stok.tambah')->name('stok.barang.store');
            Route::post('/stok/barang/{barang}/masuk', [SigapBencanaController::class, 'stokMasuk'])->middleware('module:sigap-bencana.stok.tambah')->name('stok.masuk');
        });

        // --- Kelurahan / OPD wilayah ---
        Route::middleware('module:sigap-proses')->group(function () {
            Route::get('/proses', [SigapBencanaController::class, 'prosesIndex'])->name('proses.index');
        });

        // --- Dipakai kedua kelompok ---
        Route::middleware('module:sigap-bencana,sigap-proses')->group(function () {
            Route::get('/buat', [SigapBencanaController::class, 'create'])->name('create');
            Route::post('/buat', [SigapBencanaController::class, 'store'])->name('store');

            Route::get('/{laporan}', [SigapBencanaController::class, 'show'])->name('show');
            Route::patch('/{laporan}/lokasi', [SigapBencanaController::class, 'updateLokasi'])->name('lokasi');
            Route::post('/{laporan}/proses', [SigapBencanaController::class, 'aksi'])->name('aksi');
        });
    });

    // ==========================================
    // PFM -- Beranda monitoring PFM (kerangka)
    // ==========================================
    Route::get('/pfm/beranda', [PfmDashboardController::class, 'index'])
        ->middleware('module:pbi-apbd,kartu-kks,dtsen')
        ->name('pfm.beranda');

    // ==========================================
    // PERLINSOS -- menu tambahan (kerangka)
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
    // NGOPI PAGI (admin) -- alur layanan LK3
    //   Rute statis (/konselor, /spesialisasi, /rekapitulasi, /kasus) HARUS sebelum
    //   rute tahap yang punya {konsultasi?}.
    //   Harus didaftarkan SEBELUM loop DAYASOS di bawah.
    //   Hak akses detail: dayasos-ngopi-pagi.{submenu}[.aksi] (lihat config/akses.php).
    // ==========================================
    Route::prefix('dayasos/ngopi-pagi')->name('ngopi-pagi.admin.')->middleware('module:dayasos-ngopi-pagi')->group(function () {
        $np = 'module:dayasos-ngopi-pagi.';

        Route::get('/rekapitulasi', [NgopiPagiAdminController::class, 'rekap'])->middleware($np . 'rekap')->name('rekap');
        Route::get('/rekapitulasi/export', [NgopiPagiAdminController::class, 'export'])->middleware($np . 'rekap.unduh')->name('rekap.export');

        // Data Konselor
        Route::get('/konselor', [NgopiPagiKonselorController::class, 'index'])->middleware($np . 'konselor')->name('konselor.index');
        Route::get('/konselor/tambah', [NgopiPagiKonselorController::class, 'create'])->middleware($np . 'konselor.tambah')->name('konselor.create');
        Route::post('/konselor', [NgopiPagiKonselorController::class, 'store'])->middleware($np . 'konselor.tambah')->name('konselor.store');
        Route::get('/konselor/{konselor}/edit', [NgopiPagiKonselorController::class, 'edit'])->whereNumber('konselor')->middleware($np . 'konselor.ubah')->name('konselor.edit');
        Route::put('/konselor/{konselor}', [NgopiPagiKonselorController::class, 'update'])->whereNumber('konselor')->middleware($np . 'konselor.ubah')->name('konselor.update');
        Route::delete('/konselor/{konselor}', [NgopiPagiKonselorController::class, 'destroy'])->whereNumber('konselor')->middleware($np . 'konselor.hapus')->name('konselor.destroy');

        // Spesialisasi
        Route::get('/spesialisasi', [NgopiPagiSpesialisasiController::class, 'index'])->middleware($np . 'spesialisasi')->name('spesialisasi.index');
        Route::post('/spesialisasi', [NgopiPagiSpesialisasiController::class, 'store'])->middleware($np . 'spesialisasi.tambah')->name('spesialisasi.store');
        Route::put('/spesialisasi/{spesialisasi}', [NgopiPagiSpesialisasiController::class, 'update'])->whereNumber('spesialisasi')->middleware($np . 'spesialisasi.ubah')->name('spesialisasi.update');
        Route::delete('/spesialisasi/{spesialisasi}', [NgopiPagiSpesialisasiController::class, 'destroy'])->whereNumber('spesialisasi')->middleware($np . 'spesialisasi.hapus')->name('spesialisasi.destroy');

        Route::get('/kasus/{konsultasi}', [NgopiPagiAdminController::class, 'kasus'])->whereNumber('konsultasi')->middleware($np . 'kasus')->name('kasus');
        Route::post('/kasus/{konsultasi}/aksi', [NgopiPagiAdminController::class, 'aksi'])->whereNumber('konsultasi')->middleware($np . 'kasus.proses')->name('kasus.aksi');
        Route::get('/kasus/{konsultasi}/pesan', [NgopiPagiAdminController::class, 'pesan'])->whereNumber('konsultasi')->middleware($np . 'kasus')->name('kasus.pesan');
        Route::post('/kasus/{konsultasi}/pesan', [NgopiPagiAdminController::class, 'kirimPesan'])->whereNumber('konsultasi')->middleware($np . 'kasus.pesan')->name('kasus.pesan.kirim');

        // Satu halaman per tahap: tahap.chat_masuk, tahap.screening, dst. (nama dipakai sidebar)
        foreach (NgopiPagiKonsultasi::TAHAP as $key => $cfg) {
            if (($cfg['menu'] ?? true) === false) {
                continue;
            }
            Route::get('/' . $cfg['url'] . '/{konsultasi?}', [NgopiPagiAdminController::class, 'tahap'])
                ->defaults('tahap', $key)
                ->whereNumber('konsultasi')
                ->middleware($np . $key)
                ->name('tahap.' . $key);
        }
    });

    // ==========================================
    // DAYASOS -- kerangka menu (dibaca dari config/dayasos.php)
    // ==========================================
    $daftarkanDayasos = function (array $nodes, $moduleInduk = null) use (&$daftarkanDayasos) {
        foreach ($nodes as $n) {
            if (! empty($n['custom'])) {
                continue; // rute Ngopi Pagi sudah dibuat di blok NGOPI PAGI di atas
            }

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
    // PORTAL (dulu "Menu Frontend") -- kerangka menu (config/portal.php)
    // ==========================================
    $daftarkanPortal = function (array $nodes, $moduleInduk) use (&$daftarkanPortal) {
        foreach ($nodes as $n) {
            $module = $n['module'] ?? $moduleInduk;

            if (isset($n['children'])) {
                $daftarkanPortal($n['children'], $module);
                continue;
            }

            Route::get('/' . $n['url'], [MenuPlaceholderController::class, 'show'])
                ->middleware('module:' . implode(',', (array) $module))
                ->defaults('judul', 'Portal')
                ->defaults('label', $n['label'])
                ->name($n['route']);
        }
    };
    $daftarkanPortal(config('portal', []), 'menu-frontend');

    // ==========================================
    // REHABSOS
    // ==========================================
    Route::prefix('rehabsos')->name('rehabsos.')->group(function () {
        Route::get('/dashboard', [RehabsosController::class, 'dashboard'])
            ->middleware('module:rehabsos-ppks,rehabsos-rumah-singgah,rehabsos-bantuan')
            ->name('dashboard');

        // Didaftarkan SEBELUM loop grup di bawah supaya tidak tertimpa.
        Route::get('/ground-check', [MenuPlaceholderController::class, 'show'])
            ->middleware('module:rehabsos-ground-check')
            ->defaults('judul', 'Rehabsos')
            ->defaults('label', 'Ground Check 2026')
            ->name('ground-check');

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
    // 7. Dokumen — template dokumen (lihat, preview & unduh)
    //   Hak akses: dokumen.daftar = lihat & preview, .daftar.unduh = unduh.
    // ==========================================
    Route::prefix('dokumen')->name('dokumen.')->middleware('module:dokumen')->group(function () {
        Route::get('/', [DokumenController::class, 'index'])->middleware('module:dokumen.daftar')->name('index');
        Route::get('/{dokumen}/preview', [DokumenController::class, 'preview'])->middleware('module:dokumen.daftar')->name('preview');
        Route::get('/{dokumen}/unduh', [DokumenController::class, 'unduh'])->middleware('module:dokumen.daftar.unduh')->name('unduh');

        Route::middleware('module:kelola-akses')->group(function () {
            Route::post('/', [DokumenController::class, 'store'])->name('store');
            Route::delete('/{dokumen}', [DokumenController::class, 'destroy'])->name('destroy');
        });
    });
}); // <-- GRUP ADMIN DITUTUP DI SINI



// ==========================================
// MASYARAKAT (akun warga)
// ==========================================
Route::middleware(['auth', 'role:masyarakat'])->prefix('akun-saya')->name('masyarakat.')->group(function () {
    Route::get('/', [MasyarakatController::class, 'dashboard'])->name('dashboard');

    Route::get('/permohonan', [MasyarakatController::class, 'permohonan'])->name('permohonan');
    Route::get('/tracking', [MasyarakatController::class, 'tracking'])->name('tracking');

    Route::get('/keluarga', [MasyarakatController::class, 'keluarga'])->name('keluarga');
    Route::post('/keluarga', [MasyarakatController::class, 'keluargaStore'])->name('keluarga.store');
    Route::put('/keluarga/{anggota}', [MasyarakatController::class, 'keluargaUpdate'])
        ->whereNumber('anggota')
        ->name('keluarga.update');

    Route::get('/profil', [MasyarakatController::class, 'profil'])->name('profil');
    Route::put('/profil', [MasyarakatController::class, 'profilUpdate'])->name('profil.update');

    Route::get('/password', [MasyarakatController::class, 'password'])->name('password');
    Route::put('/password', [MasyarakatController::class, 'akunUpdate'])->name('password.update');

    Route::prefix('pbi-apbd/{pbiApbd}')->name('pbi-apbd.')->group(function () {
        // Langkah pemilihan: anggota keluarga mana yang didaftarkan ke Ground Check
        Route::get('/keluarga', [PbiKeluargaController::class, 'index'])->name('keluarga');
        Route::post('/keluarga', [PbiKeluargaController::class, 'store'])->name('keluarga.store');

        Route::get('/lengkapi', [MasyarakatController::class, 'lengkapiData'])->name('lengkapi');
        Route::post('/lengkapi', [MasyarakatController::class, 'lengkapiDataStore'])->name('lengkapi.store');
    });

    Route::get('/dtsen', [MasyarakatController::class, 'dtsen'])->name('dtsen');
    // Form DTSEN pertama kali (warga belum punya pengajuan); pengajuan dibuat saat disimpan
    Route::get('/dtsen/baru', [MasyarakatController::class, 'dtsenBaru'])->name('dtsen.baru');
    Route::post('/dtsen/baru', [MasyarakatController::class, 'lengkapiDtsenStore'])->name('dtsen.baru.store');

    Route::prefix('dtsen/{permohonan}')->name('dtsen.')->group(function () {
        Route::get('/lengkapi', [MasyarakatController::class, 'lengkapiDtsen'])->name('lengkapi');
        Route::post('/lengkapi', [MasyarakatController::class, 'lengkapiDtsenStore'])->name('lengkapi.store');
    });

    Route::get('/dokumen', [MasyarakatDokumenController::class, 'index'])->name('dokumen');
    Route::post('/dokumen', [MasyarakatDokumenController::class, 'store'])->name('dokumen.store');
    Route::post('/dokumen/{dokumen}/pakai', [MasyarakatDokumenController::class, 'pakai'])->whereNumber('dokumen')->name('dokumen.pakai');
    Route::delete('/dokumen/{dokumen}', [MasyarakatDokumenController::class, 'destroy'])->whereNumber('dokumen')->name('dokumen.destroy');
});
