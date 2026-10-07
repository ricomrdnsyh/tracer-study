<?php

use App\Http\Controllers\Admin\AdminFakultasController;
use App\Http\Controllers\Admin\AdminKategoriPertanyaanController;
use App\Http\Controllers\Admin\AdminKuesionerController;
use App\Http\Controllers\Admin\AdminMahasiswaController;

use App\Http\Controllers\Admin\AdminPertanyaanController;
use App\Http\Controllers\Admin\AdminProdiController;
use App\Http\Controllers\Admin\AdminResponController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminPerusahaanController;
use App\Http\Controllers\Admin\AdminTahunAkademikController;
use App\Http\Controllers\Admin\AdminStatistikController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Fakultas\DashboardController as FakultasDashboard;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboard;
use App\Http\Controllers\Mahasiswa\TracerController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;







Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('log-viewer', [LogViewerController::class, 'index'])->middleware(['auth', 'role:Admin']);

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin,Fakultas'])->group(function () {
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::get('/users/data', [AdminUserController::class, 'getUsers'])->name('users.data');
        Route::resource('users', AdminUserController::class);
    });

    Route::get('/tahun-akademik/data', [AdminTahunAkademikController::class, 'getTahunAkademik'])->name('tahun-akademik.data');
    Route::match(['get', 'post'], '/tahun-akademik/sync', [AdminTahunAkademikController::class, 'sync'])->name('tahun-akademik.sync');
    Route::resource('tahun-akademik', AdminTahunAkademikController::class)->only(['index', 'show']);

    Route::get('/fakultas/data', [AdminFakultasController::class, 'getFakultas'])->name('fakultas.data');
    Route::match(['get', 'post'], '/fakultas/sync', [AdminFakultasController::class, 'sync'])->name('fakultas.sync');
    Route::resource('fakultas', AdminFakultasController::class)->only(['index', 'show']);

    Route::get('/prodi/data', [AdminProdiController::class, 'getProdi'])->name('prodi.data');
    Route::match(['get', 'post'], '/prodi/sync', [AdminProdiController::class, 'sync'])->name('prodi.sync');
    Route::resource('prodi', AdminProdiController::class)->only(['index', 'show']);


    Route::get('/mahasiswa/data', [AdminMahasiswaController::class, 'getMahasiswa'])->name('mahasiswa.data');
    Route::match(['get', 'post'], '/mahasiswa/sync', [AdminMahasiswaController::class, 'sync'])->name('mahasiswa.sync');
    Route::resource('mahasiswa', AdminMahasiswaController::class)->only(['index', 'show']);


    Route::get('/perusahaan/data', [AdminPerusahaanController::class, 'getPerusahaan'])->name('perusahaan.data');
    Route::resource('perusahaan', AdminPerusahaanController::class)->only(['index', 'show']);

    Route::get('/kuesioner/data', [AdminKuesionerController::class, 'getKuesioner'])->name('kuesioner.data');
    Route::get('/kuesioner/{kuesioner}/export-json', [AdminKuesionerController::class, 'exportJson'])->name('kuesioner.export-json');
    Route::post('/kuesioner/{kuesioner}/import-json', [AdminKuesionerController::class, 'importJson'])->name('kuesioner.import-json');
    Route::resource('kuesioner', AdminKuesionerController::class);

    Route::get('/wilayah', [\App\Http\Controllers\Admin\AdminWilayahController::class, 'index'])->name('wilayah.index');
    Route::get('/wilayah/negara', [\App\Http\Controllers\Admin\AdminWilayahController::class, 'getNegara'])->name('wilayah.negara');
    Route::get('/wilayah/provinsi', [\App\Http\Controllers\Admin\AdminWilayahController::class, 'getProvinsi'])->name('wilayah.provinsi');
    Route::get('/wilayah/kabupaten', [\App\Http\Controllers\Admin\AdminWilayahController::class, 'getKabupaten'])->name('wilayah.kabupaten');
    Route::post('/wilayah/import', [\App\Http\Controllers\Admin\AdminWilayahController::class, 'import'])->name('wilayah.import');

    Route::get('/respon/template', [\App\Http\Controllers\Admin\ResponImportController::class, 'template'])->name('respon.template');
    Route::post('/respon/import', [\App\Http\Controllers\Admin\ResponImportController::class, 'import'])->name('respon.import');
    Route::get('/respon/export', [\App\Http\Controllers\Admin\ResponImportController::class, 'export'])->name('respon.export');
    Route::get('/respon/data', [AdminResponController::class, 'getRespon'])->name('respon.data');
    Route::resource('respon', AdminResponController::class)->only(['index', 'show']);

    Route::get('/statistik', [AdminStatistikController::class, 'index'])->name('statistik.index');
    Route::get('/statistik/data', [AdminStatistikController::class, 'getData'])->name('statistik.data');

    Route::resource('kategori', AdminKategoriPertanyaanController::class)->except(['index', 'show']);
    Route::resource('pertanyaan', AdminPertanyaanController::class)->except(['index', 'show']);
});

Route::prefix('fakultas')->name('fakultas.')->middleware(['auth', 'role:Fakultas'])->group(function () {
    Route::get('/dashboard', [FakultasDashboard::class, 'index'])->name('dashboard');
});

Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth:mahasiswa'])->group(function () {
    Route::get('/dashboard', [MahasiswaDashboard::class, 'index'])->name('dashboard');
    Route::get('/tracer/lookup', [TracerController::class, 'lookup'])->name('tracer.lookup');
    Route::get('/tracer', [TracerController::class, 'index'])->name('tracer.index');
    Route::post('/tracer', [TracerController::class, 'store'])->name('tracer.store');
});
