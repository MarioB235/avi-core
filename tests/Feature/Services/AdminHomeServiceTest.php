<?php

namespace Tests\Feature\Services;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use App\Services\AdminHomeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHomeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_context_label_uses_company_name_and_role(): void
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Granja Norte',
            'estado' => EmpresaEstado::Activa,
        ]);

        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $service = app(AdminHomeService::class);

        $this->assertSame('Granja Norte · Encargado', $service->contextLabel($user));
    }

    public function test_context_label_falls_back_to_avicore_for_admin_without_company(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $this->assertSame(
            'AviCore · Admin AviCore',
            app(AdminHomeService::class)->contextLabel($admin)
        );
    }

    public function test_active_users_count_scopes_by_company(): void
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $encargadoA = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Encargado,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaB->id,
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaB->id,
            'rol' => UserRole::Operario,
            'activo' => false,
            'must_change_password' => false,
        ]);

        $service = app(AdminHomeService::class);

        $this->assertSame(2, $service->activeUsersCount($encargadoA));
    }

    public function test_active_users_count_includes_all_companies_for_admin_avicore(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'activo' => false,
            'must_change_password' => false,
        ]);

        $this->assertSame(2, app(AdminHomeService::class)->activeUsersCount($admin));
    }

    public function test_for_composes_view_data_object(): void
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Avícola Demo',
            'estado' => EmpresaEstado::Activa,
        ]);

        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'activo' => true,
            'must_change_password' => false,
        ]);

        $home = app(AdminHomeService::class)->for($user);

        $this->assertSame($user->id, $home->user->id);
        $this->assertSame('Avícola Demo · Encargado', $home->contextLabel);
        $this->assertSame(1, $home->activeUsersCount);
        $this->assertArrayHasKey('huevos_hoy', $home->operativoTeaser);
        $this->assertArrayHasKey('muertes_hoy', $home->operativoTeaser);
        $this->assertArrayHasKey('granjas', $home->inicio);
        $this->assertArrayHasKey('galpones', $home->inicio);
        $this->assertTrue($home->inicio['show_estructura']);
        $this->assertArrayHasKey('show', $home->pulso);
        $this->assertArrayHasKey('show', $home->stockPreview);
    }

    public function test_pulso_panel_shows_when_galpones_exist(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->create();

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $pulso = app(AdminHomeService::class)->pulsoPanel($dueno);

        $this->assertTrue($pulso['show']);
        $this->assertSame('dueno.resumen.index', $pulso['resumen_route']);
        $this->assertSame('0 huevos', $pulso['unidades_hoy']);
    }

    public function test_stock_preview_returns_four_items_for_dueno(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $preview = app(AdminHomeService::class)->stockPreviewFor($dueno);

        $this->assertFalse($preview['show']);
    }

    public function test_stock_preview_shows_when_galpones_exist(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->create();

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $preview = app(AdminHomeService::class)->stockPreviewFor($dueno);

        $this->assertTrue($preview['show']);
        $this->assertTrue($preview['preview']);
        $this->assertCount(4, $preview['items']);
        $this->assertSame('En reserva (cámara)', $preview['items'][0]['label']);
        $this->assertSame('12 cajas', $preview['items'][0]['value']);
    }

    public function test_team_preview_items_for_dueno_equipo_module(): void
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Avícola Demo',
            'estado' => EmpresaEstado::Activa,
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'activo' => true,
            'must_change_password' => false,
        ]);

        $items = app(AdminHomeService::class)->teamPreviewItems($dueno);

        $this->assertCount(3, $items);
        $this->assertSame('3', $items[0]['value']);
        $this->assertSame('1', $items[1]['value']);
        $this->assertSame('1', $items[2]['value']);

        $this->actingAs($dueno)
            ->get(route('dueno.equipo.index'))
            ->assertOk()
            ->assertSee('Tu equipo')
            ->assertSee('personas activas')
            ->assertSee('Campo')
            ->assertSee('avicore-team-list', false)
            ->assertSee('avicore-team-list__item', false)
            ->assertDontSee('avicore-table', false);
    }

    public function test_team_list_returns_flat_items_with_segments(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'activo' => true,
            'must_change_password' => false,
            'name' => 'Dueño Demo',
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
            'name' => 'Operario Campo',
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'activo' => true,
            'must_change_password' => false,
            'name' => 'Encargado Super',
        ]);

        $list = app(AdminHomeService::class)->teamList($dueno);

        $this->assertSame(3, $list['summary']['total']);
        $this->assertCount(4, $list['filters']);
        $this->assertCount(3, $list['items']);

        $operario = collect($list['items'])->firstWhere(fn (array $item) => $item['user']->name === 'Operario Campo');

        $this->assertNotNull($operario);
        $this->assertSame('campo', $operario['segment']);
    }

    public function test_team_members_returns_active_company_users(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'activo' => true,
            'must_change_password' => false,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'activo' => false,
            'must_change_password' => false,
        ]);

        $members = app(AdminHomeService::class)->teamMembers($dueno);

        $this->assertCount(2, $members);
        $this->assertTrue($members->contains('id', $operario->id));
    }

    public function test_dueno_home_has_no_module_shortcuts(): void
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.home'))
            ->assertOk()
            ->assertDontSee('Accesos rápidos')
            ->assertDontSee('avicore-operario-carga-grid', false)
            ->assertSee('Tu empresa')
            ->assertSee('Sin estructura cargada')
            ->assertDontSee('Tu equipo')
            ->assertDontSee('Producción de hoy')
            ->assertDontSee('Huevos de los últimos 7 días')
            ->assertDontSee('avicore-line-chart', false)
            ->assertDontSee('Ver análisis completo')
            ->assertDontSee('Ver módulo Comercial')
            ->assertDontSee('avicore-pulse-status', false);
    }

    public function test_inicio_panel_returns_structural_counts(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->create();

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $panel = app(AdminHomeService::class)->inicioPanel($dueno);

        $this->assertTrue($panel['show_estructura']);
        $this->assertSame(1, $panel['granjas']);
        $this->assertSame(1, $panel['galpones']);
    }

    public function test_comercial_module_for_dueno(): void
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.comercial.index'))
            ->assertOk()
            ->assertSee('Clientes y entregas')
            ->assertSee('Tus clientes en el mapa')
            ->assertSee('Tocá un pin en el mapa')
            ->assertSee('avicore-client-map-detail', false)
            ->assertSee('data-avicore-client-map-canvas', false)
            ->assertDontSee('avicore-comercial-client-list', false)
            ->assertSee('Próximamente');
    }

    public function test_comercial_client_map_includes_last_purchase_summary(): void
    {
        $map = app(AdminHomeService::class)->comercialClientMap();

        $this->assertCount(6, $map['clients']);
        $this->assertSame('21 ago 2026 · 600 huevos', $map['clients'][0]['ultima_compra_resumen']);
        $this->assertSame('Almacén El Progreso', $map['clients'][0]['name']);
    }

    public function test_comercial_preview_items_use_plain_language(): void
    {
        $items = app(AdminHomeService::class)->comercialPreviewItems();

        $labels = array_column($items, 'label');

        $this->assertSame(
            ['Clientes', 'Última venta', 'Pedido de mañana', 'Huevos reservados'],
            $labels
        );

        $this->assertSame('6', $items[0]['value']);
        $this->assertSame('$ 48.500', $items[1]['value']);
        $this->assertSame('1.200 huevos', $items[2]['value']);
        $this->assertSame('operario-huevo', $items[2]['illustration']);

        foreach ($items as $item) {
            $this->assertArrayHasKey('value', $item);
            $this->assertArrayHasKey('hint', $item);
            $this->assertNotSame('', $item['value']);
            $this->assertNotSame('', $item['hint']);
            $this->assertTrue(
                isset($item['icon']) || isset($item['illustration']),
                'Each commercial item needs icon or illustration'
            );
        }
    }
}
