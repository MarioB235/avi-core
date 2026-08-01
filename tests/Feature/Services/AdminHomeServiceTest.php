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
        $this->assertCount(3, $home->setupItems);
        $this->assertSame('Granjas', $home->setupItems[0]['label']);
    }

    public function test_setup_items_include_onboarding_steps(): void
    {
        $items = app(AdminHomeService::class)->setupItems();

        $labels = array_column($items, 'label');

        $this->assertSame(['Granjas', 'Galpones', 'Usuarios'], $labels);
        $this->assertSame('Disponible', $items[2]['status']);

        foreach ($items as $item) {
            $this->assertArrayHasKey('description', $item);
            $this->assertArrayHasKey('icon', $item);
            $this->assertNotSame('', $item['description']);
            $this->assertNotSame('', $item['icon']);
        }
    }
}
