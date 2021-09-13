<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'wp-import'], function () {
    Route::get('/', 'ImportController@index')->name('wp-import.index');
    Route::post('/upload', 'ImportController@upload')->name('wp-import.upload');
    Route::get('/summary', 'ImportController@summary')->name('wp-import.summary');
    Route::post('/import', 'ImportController@import')->name('wp-import.import');
});
