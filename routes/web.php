<?php

use App\Http\Middleware\EnsureAdminPanelAccess;
use App\Http\Middleware\EnsureOperarioAccess;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Livewire\Admin\Usuarios\Index as AdminUsuariosIndex;
use App\Livewire\Auth\ChangePassword;
use App\Livewire\Auth\Login;
use App\Livewire\Operario\CargaAlimento;
use App\Livewire\Operario\CargaDescarte;
use App\Livewire\Operario\CargaHuevos;
use App\Livewire\Operario\CargaLote;
use App\Livewire\Operario\CargaMuertes;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\CargaVacunacion;
use App\Livewire\Operario\Historial;
use App\Livewire\Operario\Home as OperarioHome;
use App\Livewire\Profile\Edit as ProfileEdit;
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
    Route::livewire('/perfil', ProfileEdit::class)->name('profile.edit');

    Route::view('/admin', 'pages.admin.home')
        ->middleware(EnsureAdminPanelAccess::class)
        ->name('admin.home');

    Route::middleware(EnsureAdminPanelAccess::class)->prefix('admin')->name('admin.')->group(function () {
        Route::livewire('/usuarios', AdminUsuariosIndex::class)->name('usuarios.index');
    });
    Route::middleware(EnsureOperarioAccess::class)->prefix('operario')->name('operario.')->group(function () {
        Route::livewire('/', OperarioHome::class)->name('home');
        Route::livewire('/cargar', CargarHub::class)->name('cargar');
        Route::livewire('/historial', Historial::class)->name('historial');
        Route::livewire('/carga/huevos', CargaHuevos::class)->name('carga.huevos');
        Route::livewire('/carga/muertes', CargaMuertes::class)->name('carga.muertes');
        Route::livewire('/carga/descarte', CargaDescarte::class)->name('carga.descarte');
        Route::livewire('/carga/vacunacion', CargaVacunacion::class)->name('carga.vacunacion');
        Route::livewire('/carga/alimento', CargaAlimento::class)->name('carga.alimento');
        Route::livewire('/carga/lote', CargaLote::class)->name('carga.lote');
        Route::livewire('/perfil', ProfileEdit::class)->name('perfil');
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
