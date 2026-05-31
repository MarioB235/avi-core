<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');

Route::prefix('dev')->name('dev.')->group(function () {
    Route::view('/admin-layout', 'pages.dev.admin-layout')->name('admin-layout');
    Route::view('/operario-layout', 'pages.dev.operario-layout')->name('operario-layout');
});
