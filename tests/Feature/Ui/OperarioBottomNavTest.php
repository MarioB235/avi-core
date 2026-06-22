<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
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
            ->assertSee('avicore-operario-body', false)
            ->assertSee('avicore-operario-shell--home', false)
            ->assertSee('avicore-operario-home-hero', false)
            ->assertSee('avicore-operario-home-cargas', false)
            ->assertSee('aria-label="Navegación operario"', false)
            ->assertSee('avicore-operario-tab-bar__inner', false)
            ->assertSee('avicore-operario-tab-bar__icon-wrap', false)
            ->assertSee('avicore-operario-tab-bar__item--active', false)
            ->assertSee('Resumen de hoy', false)
            ->assertSee('Maples producidos', false)
            ->assertSee('Cargas realizadas', false)
            ->assertSee('Operario', false)
            ->assertSee('Inicio', false)
            ->assertSee('Galpón', false)
            ->assertSee('Cargar', false)
            ->assertSee('Historial', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('Acá tenés el resumen de tu granja.', false)
            ->assertSee('images/brand/operario-home-hero.jpg', false)
            ->assertSee(route('operario.galpon'), false)
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
            ->assertSee('avicore-operario-tab-bar__icon-wrap', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee(route('operario.cargar'), false)
            ->assertSee('Cantidad de huevos');
    }

    public function test_home_shows_empty_galpon_chip_when_no_galpon_selected(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        $this->actingAs($operario)
            ->get(route('operario.home'))
            ->assertOk()
            ->assertSee('avicore-operario-home-hero__galpon--empty', false)
            ->assertSee('Sin seleccionar', false);
    }

    public function test_home_shows_maples_kpi_from_today_loads(): void
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

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 1500,
            ]);

        $this->actingAs($operario)
            ->get(route('operario.home'))
            ->assertOk()
            ->assertSee('>50</p>', false)
            ->assertSee($galpon->displayName(), false)
            ->assertDontSee('avicore-operario-home-hero__galpon--empty', false);
    }
}
