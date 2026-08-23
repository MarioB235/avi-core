<?php

namespace Tests\Feature\Services;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
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
            ->assertSee('Tu gente en AviCore')
            ->assertSee('Usuarios activos')
            ->assertSee('Operarios en campo');
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
            ->assertDontSee('avicore-operario-carga-grid', false);
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
            ->assertSee('Última venta')
            ->assertSee('Próximamente');
    }

    public function test_comercial_preview_items_use_plain_language(): void
    {
        $items = app(AdminHomeService::class)->comercialPreviewItems();

        $labels = array_column($items, 'label');

        $this->assertSame(
            ['Clientes', 'Última venta', 'Pedido de mañana', 'Huevos reservados'],
            $labels
        );

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
