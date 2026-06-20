<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperarioBottomNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_shell_renders_bottom_navigation_with_active_home_tab(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($operario)->get(route('operario.home'));

        $response
            ->assertOk()
            ->assertSee('aria-label="Navegación operario"', false)
            ->assertSee('avicore-operario-tab-bar__item--active', false)
            ->assertSee('avicore-operario-header__title', false)
            ->assertSee('Inicio', false)
            ->assertSee('Galpón', false)
            ->assertSee('Cargar', false)
            ->assertSee('Historial', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('Elegí un galpón en la pestaña Galpón', false)
            ->assertSee(route('operario.cargar'), false)
            ->assertSee(route('operario.historial'), false);
    }

    public function test_carga_huevos_highlights_cargar_tab_in_bottom_navigation(): void
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

        $this->actingAs($operario)
            ->get(route('operario.carga.huevos'))
            ->assertOk()
            ->assertSee('avicore-operario-tab-bar__item--active', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee(route('operario.cargar'), false)
            ->assertSee('Cantidad de huevos');
    }
}
