<?php

use App\Http\Middleware\EnsureAdminPanelAccess;
use App\Http\Middleware\EnsureOperarioAccess;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Livewire\Auth\ChangePassword;
use App\Livewire\Auth\Login;
use App\Livewire\Operario\CargaHuevos;
use App\Livewire\Operario\CargaMuertes;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\CargaVacunacion;
use App\Livewire\Operario\Historial;
use App\Livewire\Operario\Home as OperarioHome;
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
    Route::middleware(EnsureOperarioAccess::class)->prefix('operario')->name('operario.')->group(function () {
        Route::livewire('/', OperarioHome::class)->name('home');
        Route::livewire('/cargar', CargarHub::class)->name('cargar');
        Route::livewire('/historial', Historial::class)->name('historial');
        Route::livewire('/carga/huevos', CargaHuevos::class)->name('carga.huevos');
        Route::livewire('/carga/muertes', CargaMuertes::class)->name('carga.muertes');
        Route::livewire('/carga/vacunacion', CargaVacunacion::class)->name('carga.vacunacion');
    });
});

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        if ($user->must_change_password) {
            return redirect()->route('password.change');
        }

        return redirect()->route($user->homeRouteName());
    }

    return redirect()->route('login');
})->name('home');

if (app()->environment('local')) {
    Route::prefix('dev')->name('dev.')->middleware(['auth', EnsurePasswordChanged::class])->group(function () {
        Route::view('/admin-layout', 'pages.dev.admin-layout')->name('admin-layout');
        Route::view('/operario-layout', 'pages.dev.operario-layout')->name('operario-layout');
    });
}
