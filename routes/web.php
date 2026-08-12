<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/fakultas/data', [\App\Http\Controllers\Admin\AdminFakultasController::class, 'getFakultas'])->name('fakultas.data');
    Route::resource('fakultas', \App\Http\Controllers\Admin\AdminFakultasController::class);
});
