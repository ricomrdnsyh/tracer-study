<?php

use App\Http\Controllers\Admin\AdminFakultasController;
use App\Http\Controllers\Admin\AdminMahasiswaController;
use App\Http\Controllers\Admin\AdminProdiController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Fakultas\DashboardController as FakultasDashboard;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboard;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('log-viewer', [LogViewerController::class, 'index'])->middleware(['auth', 'role:Admin']);

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::get('/fakultas/data', [AdminFakultasController::class, 'getFakultas'])->name('fakultas.data');
    Route::resource('fakultas', AdminFakultasController::class);

    Route::get('/prodi/data', [AdminProdiController::class, 'getProdi'])->name('prodi.data');
    Route::resource('prodi', AdminProdiController::class);

    Route::get('/users/data', [AdminUserController::class, 'getUsers'])->name('users.data');
    Route::resource('users', AdminUserController::class);

    Route::get('/mahasiswa/data', [AdminMahasiswaController::class, 'getMahasiswa'])->name('mahasiswa.data');
    Route::resource('mahasiswa', AdminMahasiswaController::class);
});

Route::prefix('fakultas')->name('fakultas.')->middleware(['auth', 'role:Fakultas'])->group(function () {
    Route::get('/dashboard', [FakultasDashboard::class, 'index'])->name('dashboard');
});

Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth:mahasiswa'])->group(function () {
    Route::get('/dashboard', [MahasiswaDashboard::class, 'index'])->name('dashboard');
});
