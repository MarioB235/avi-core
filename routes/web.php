<?php

use App\Actions\Empresa\EndSoporteEmpresaAction;
use App\Enums\UserRole;
use App\Http\Controllers\Admin\DescargarReporteHistoriaLoteController;
use App\Http\Controllers\Admin\DescargarReporteMovimientosExistenciasController;
use App\Http\Controllers\Admin\DescargarReporteMovimientosExistenciasPdfController;
use App\Http\Controllers\Admin\DescargarReporteProduccionDiariaController;
use App\Http\Controllers\Admin\DescargarReporteProduccionDiariaPdfController;
use App\Http\Controllers\Admin\DescargarReporteSanidadBasicaController;
use App\Http\Middleware\EnsureOperarioAccess;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsureRolePanelAccess;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Livewire\Admin\Auditoria\Index as AdminAuditoriaIndex;
use App\Livewire\Admin\Comercial\Index as AdminComercialIndex;
use App\Livewire\Admin\Empresas\Index as AdminEmpresasIndex;
use App\Livewire\Admin\Equipo\Index as AdminEquipoIndex;
use App\Livewire\Admin\Estructura\Index as AdminEstructuraIndex;
use App\Livewire\Admin\HistorialOperativo\Index as AdminHistorialOperativoIndex;
use App\Livewire\Admin\Movimientos\Index as AdminMovimientosIndex;
use App\Livewire\Admin\Resumen\Index as AdminResumenIndex;
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
use App\Services\SoporteEmpresaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(RedirectIfAuthenticated::class)->group(function () {
    Route::livewire('/login', Login::class)->name('login');
});

Route::post('/logout', function (EndSoporteEmpresaAction $endSoporte) {
    $user = Auth::user();

    if ($user !== null) {
        $endSoporte->execute($user, 'logout');
    }

    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', EnsurePasswordChanged::class])->group(function () {
    Route::livewire('/password/change', ChangePassword::class)->name('password.change');
    Route::livewire('/perfil', ProfileEdit::class)->name('profile.edit');

    Route::post('/avicore/soporte/finalizar', function (EndSoporteEmpresaAction $endSoporte) {
        $user = Auth::user();

        if ($user === null || ! $user->isAdminAvicore()) {
            abort(403);
        }

        $endSoporte->execute($user, 'manual');

        $destino = request()->string('destino')->toString();
        $routeName = app(SoporteEmpresaService::class)->resolveExitRoute(
            $destino !== '' ? $destino : null
        );

        return redirect()
            ->route($routeName)
            ->with('status', 'soporte-finalizado');
    })
        ->middleware(EnsureRolePanelAccess::class.':avicore')
        ->name('avicore.soporte.finalizar');

    foreach ([
        UserRole::Dueno,
        UserRole::Administrativo,
        UserRole::Encargado,
        UserRole::AdminAvicore,
    ] as $role) {
        $prefix = $role->routePrefix();

        Route::middleware(EnsureRolePanelAccess::class.':'.$prefix)
            ->prefix($prefix)
            ->name("{$prefix}.")
            ->group(function () {
                Route::view('/', 'pages.admin.home')->name('home');
                Route::livewire('/resumen', AdminResumenIndex::class)->name('resumen.index');
                Route::get('/reportes/produccion-diaria.xlsx', DescargarReporteProduccionDiariaController::class)
                    ->name('reportes.produccion-diaria');
                Route::get('/reportes/produccion-diaria.pdf', DescargarReporteProduccionDiariaPdfController::class)
                    ->name('reportes.produccion-diaria-pdf');
                Route::get('/reportes/movimientos-existencias.xlsx', DescargarReporteMovimientosExistenciasController::class)
                    ->name('reportes.movimientos-existencias');
                Route::get('/reportes/movimientos-existencias.pdf', DescargarReporteMovimientosExistenciasPdfController::class)
                    ->name('reportes.movimientos-existencias-pdf');
                Route::get('/reportes/historia-lote.xlsx', DescargarReporteHistoriaLoteController::class)
                    ->name('reportes.historia-lote');
                Route::get('/reportes/sanidad-basica.xlsx', DescargarReporteSanidadBasicaController::class)
                    ->name('reportes.sanidad-basica');
                Route::livewire('/historial-operativo', AdminHistorialOperativoIndex::class)->name('historial-operativo.index');
                Route::livewire('/movimientos', AdminMovimientosIndex::class)->name('movimientos.index');
                Route::livewire('/auditoria', AdminAuditoriaIndex::class)->name('auditoria.index');
                Route::livewire('/equipo', AdminEquipoIndex::class)->name('equipo.index');
                Route::livewire('/comercial', AdminComercialIndex::class)->name('comercial.index');
                Route::livewire('/usuarios', AdminUsuariosIndex::class)->name('usuarios.index');
                Route::livewire('/empresas', AdminEmpresasIndex::class)->name('empresas.index');
                Route::livewire('/estructura', AdminEstructuraIndex::class)->name('estructura.index');
            });
    }

    Route::middleware(EnsureRolePanelAccess::class.':reparto')
        ->prefix('reparto')
        ->name('reparto.')
        ->group(function () {
            Route::view('/', 'pages.reparto.home')->name('home');
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

Route::get('/admin/{path?}', function (?string $path = null) {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    if ($user->must_change_password) {
        return redirect()->route('password.change');
    }

    $prefix = $user->rol->routePrefix();
    $suffix = $path !== null && $path !== '' ? '/'.ltrim($path, '/') : '';

    return redirect("/{$prefix}{$suffix}");
})->where('path', '.*')->name('admin.legacy');

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
