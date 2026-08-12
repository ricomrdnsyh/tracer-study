<?php

use App\Http\Controllers\Admin\AdminFakultasController;
use App\Http\Controllers\Admin\AdminProdiController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminMahasiswaController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/fakultas/data', [AdminFakultasController::class, 'getFakultas'])->name('fakultas.data');
    Route::resource('fakultas', AdminFakultasController::class);

    Route::get('/prodi/data', [AdminProdiController::class, 'getProdi'])->name('prodi.data');
    Route::resource('prodi', AdminProdiController::class);

    Route::get('/users/data', [AdminUserController::class, 'getUsers'])->name('users.data');
    Route::resource('users', AdminUserController::class);

    Route::get('/mahasiswa/data', [AdminMahasiswaController::class, 'getMahasiswa'])->name('mahasiswa.data');
    Route::resource('mahasiswa', AdminMahasiswaController::class);
});
