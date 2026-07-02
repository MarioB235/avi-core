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
            ->assertSee('avicore-operario-home-summary', false)
            ->assertDontSee('avicore-operario-primary-action', false)
            ->assertSee('Seleccioná un galpón para ver el estado.', false)
            ->assertSee('aria-label="Navegación operario"', false)
            ->assertSee('avicore-operario-tab-bar__inner', false)
            ->assertSee('avicore-operario-tab-bar__item--active', false)
            ->assertSee('Operario', false)
            ->assertSee('Inicio', false)
            ->assertSee('Cargar', false)
            ->assertSee('Historial', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('Estado de hoy del galpón.', false)
            ->assertSee('avicore-operario-galpon-selector', false)
            ->assertSee('wire:transition="operario-page"', false)
            ->assertSee('wire:navigate.hover', false)
            ->assertSee('avicore-snackbar-host', false)
            ->assertSee('avicore-operario-home-hero__media', false)
            ->assertSee(route('operario.cargar'), false)
            ->assertSee(route('operario.historial'), false);
    }

    public function test_historial_renders_hero_navbar_and_sheet_without_layout_chrome(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        $this->actingAs($operario)
            ->get(route('operario.historial'))
            ->assertOk()
            ->assertSee('avicore-operario-shell--home', false)
            ->assertSee('avicore-operario-historial-hero', false)
            ->assertSee('avicore-home-nav', false)
            ->assertSee('avicore-operario-header--home', false)
            ->assertSee('Huevos, muertes y más · consultá todo tu historial.', false)
            ->assertSee('Todos los registros', false)
            ->assertSee('Filtrar por fecha', false)
            ->assertDontSee('avicore-operario-historial-account', false)
            ->assertSee('avicore-operario-tab-bar__item--active', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('avicore-operario-home-hero__galpon--empty', false)
            ->assertSee('Sin seleccionar · Elegí en Inicio', false)
            ->assertSee('abrir_galpon=1', false)
            ->assertSee('Cuando registres huevos, muertes u otros datos, aparecerán acá.', false)
            ->assertDontSee('wire:transition="operario-chrome"', false);
    }

    public function test_historial_highlights_historial_tab_via_http(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($operario)->get(route('operario.historial'));

        $response
            ->assertOk()
            ->assertSee(route('operario.historial'), false)
            ->assertSee('avicore-operario-tab-bar__item--active', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('M8 2v4', false)
            ->assertSee('M3 10h18', false);

        $this->assertSame(
            1,
            substr_count($response->getContent(), 'aria-current="page"'),
            'Solo la pestaña activa debe exponer aria-current="page".'
        );
    }

    public function test_historial_shows_today_loads_via_http_route(): void
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
            ->get(route('operario.historial'))
            ->assertOk()
            ->assertSee('1.500 huevos', false)
            ->assertDontSee('Huevos ·', false)
            ->assertDontSee('Cuando registres huevos, muertes u otros datos, aparecerán acá.', false)
            ->assertSee('avicore-operario-historial-list__item', false);
    }

    public function test_carga_huevos_highlights_cargar_tab_and_opens_dialog_from_deep_link(): void
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
            ->get(route('operario.cargar', ['form' => 'huevos']))
            ->assertOk()
            ->assertSee('avicore-operario-tab-bar__item--active', false)
            ->assertSee('avicore-dialog', false)
            ->assertSee('Cantidad de huevos', false)
            ->assertSee('wire:click="abrirFormularioHuevos"', false)
            ->assertDontSee('wire:transition="operario-chrome"', false);
    }

    public function test_cargar_hub_shows_layout_subtitle_when_no_galpon_selected(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        $this->actingAs($operario)
            ->get(route('operario.cargar'))
            ->assertOk()
            ->assertSee('avicore-operario-cargar-hero', false)
            ->assertSee('avicore-operario-cargar-alert', false)
            ->assertSee('Elegí un galpón en Inicio', false)
            ->assertSee('Sin seleccionar · Elegí en Inicio', false)
            ->assertSee('avicore-operario-home-hero__media', false)
            ->assertDontSee('wire:transition="operario-chrome"', false);
    }

    public function test_cargar_hub_renders_hero_sheet_and_huevos_tile_with_galpon(): void
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
            ->get(route('operario.cargar'))
            ->assertOk()
            ->assertSee('avicore-operario-cargar-hero', false)
            ->assertSee('avicore-operario-home-sheet', false)
            ->assertSee('avicore-operario-carga-tile', false)
            ->assertSee('avicore-operario-carga-tile--action', false)
            ->assertSee('Producción del día', false)
            ->assertDontSee('avicore-operario-carga-tile--featured', false)
            ->assertSee('Nueva carga', false)
            ->assertSee('Tipo de carga', false)
            ->assertSee('wire:click="abrirFormularioHuevos"', false)
            ->assertSee($galpon->displayName(), false)
            ->assertDontSee(route('operario.carga.huevos'), false);
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
            ->assertSee('1.500', false)
            ->assertSee('Huevos hoy', false)
            ->assertSee('50 maples', false)
            ->assertSee($galpon->displayName(), false)
            ->assertDontSee('avicore-operario-home-hero__galpon--empty', false);
    }
}
