<?php

namespace Tests\Feature\Support;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use App\Support\OperarioNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class OperarioNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_nested_carga_huevos_route_marks_cargar_tab_active(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => $galpon->id,
        ]);

        $request = Request::create(route('operario.carga.huevos'), 'GET');
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

        $this->actingAs($operario);
        $this->app->instance('request', $request);

        $cargarTab = collect(OperarioNav::tabs())->firstWhere('route', 'operario.cargar');

        $this->assertNotNull($cargarTab);
        $this->assertTrue(OperarioNav::tabIsActive($cargarTab));
        $this->assertSame('Cargar', OperarioNav::headerTitle());
    }

    public function test_home_route_marks_inicio_tab_active(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $request = Request::create(route('operario.home'), 'GET');
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

        $this->actingAs($operario);
        $this->app->instance('request', $request);

        $homeTab = collect(OperarioNav::tabs())->firstWhere('route', 'operario.home');

        $this->assertNotNull($homeTab);
        $this->assertTrue(OperarioNav::tabIsActive($homeTab));
        $this->assertSame('Inicio', OperarioNav::headerTitle());
    }

    public function test_historial_route_marks_historial_tab_active(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $request = Request::create(route('operario.historial'), 'GET');
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

        $this->actingAs($operario);
        $this->app->instance('request', $request);

        $historialTab = collect(OperarioNav::tabs())->firstWhere('route', 'operario.historial');

        $this->assertNotNull($historialTab);
        $this->assertSame('calendar', $historialTab['icon']);
        $this->assertTrue(OperarioNav::tabIsActive($historialTab));
        $this->assertSame('Historial', OperarioNav::headerTitle());
    }
}
