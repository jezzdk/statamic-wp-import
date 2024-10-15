<?php

use Illuminate\Support\Facades\Route;
use RadPack\StatamicWpImport\Http\Controllers\ImportController;

Route::group(['prefix' => 'wp-import'], function () {
    Route::get('/', [ImportController::class, 'index'])->name('wp-import.index');
    Route::post('/upload', [ImportController::class, 'upload'])->name('wp-import.upload');
    Route::get('/summary', [ImportController::class, 'summary'])->name('wp-import.summary');
    Route::post('/import', [ImportController::class, 'import'])->name('wp-import.import');
});
