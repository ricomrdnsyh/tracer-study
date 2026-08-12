<?php

use App\Http\Controllers\Admin\AdminFakultasController;
use App\Http\Controllers\Admin\AdminProdiController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/fakultas/data', [AdminFakultasController::class, 'getFakultas'])->name('fakultas.data');
    Route::resource('fakultas', AdminFakultasController::class);

    Route::get('/prodi/data', [AdminProdiController::class, 'getProdi'])->name('prodi.data');
    Route::resource('prodi', AdminProdiController::class);
});
