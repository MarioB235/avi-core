<?php

use App\Http\Middleware\EnsureAdminPanelAccess;
use App\Http\Middleware\EnsureOperarioAccess;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Livewire\Auth\ChangePassword;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(RedirectIfAuthenticated::class)->group(function () {
    Route::livewire('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', EnsurePasswordChanged::class])->group(function () {
    Route::livewire('/password/change', ChangePassword::class)->name('password.change');

    Route::view('/admin', 'pages.admin.home')
        ->middleware(EnsureAdminPanelAccess::class)
        ->name('admin.home');
    Route::view('/operario', 'pages.operario.home')
        ->middleware(EnsureOperarioAccess::class)
        ->name('operario.home');
});

Route::view('/', 'pages.home')->name('home');

if (app()->environment('local')) {
    Route::prefix('dev')->name('dev.')->middleware(['auth', EnsurePasswordChanged::class])->group(function () {
        Route::view('/admin-layout', 'pages.dev.admin-layout')->name('admin-layout');
        Route::view('/operario-layout', 'pages.dev.operario-layout')->name('operario-layout');
    });
}
