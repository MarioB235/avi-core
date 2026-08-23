<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHomeViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dueno_sees_admin_home_with_company_context_and_empty_states(): void
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Granja Santa Elena',
            'estado' => EmpresaEstado::Activa,
        ]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'name' => 'María González',
            'documento' => '20111222',
            'password' => 'Secret123!',
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '30111222',
            'password' => 'Secret123!',
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($encargado)->get(route('encargado.home'));

        $response->assertOk();
        $response->assertSee('Inicio');
        $response->assertSee('Granja Santa Elena · Encargado');
        $response->assertSee('María González');
        $response->assertSee('¡Buen');
        $response->assertSee('Resumen de Granja Santa Elena · Encargado.');
        $response->assertSee('avicore-operario-shell', false);
        $response->assertSee('avicore-operario-home-hero', false);
        $response->assertSee('avicore-operario-home-sheet', false);
        $response->assertSee('avicore-home-nav', false);
        $response->assertSee('avicore-operario-kpi-grid--stat', false);
        $response->assertSee('avicore-operario-kpi-panel--stat', false);
        $response->assertDontSee('avicore-admin-context', false);
        $response->assertDontSee('avicore-operario-carga-grid', false);
        $response->assertDontSee('¿Qué querés gestionar?');
        $response->assertDontSee('avicore-operario-galpon-chip', false);
        $response->assertDontSee('avicore-admin-header--toolbar', false);
        $response->assertDontSee('avicore-admin-header--masthead', false);
        $response->assertSee('Producción de hoy');
        $response->assertSee('Huevos juntados');
        $response->assertSee('Aves que murieron');
        $response->assertSee('Galpones en alerta');
        $response->assertSee('Galpones con producción');
        $response->assertDontSee('Clientes y entregas');
        $response->assertDontSee('Tu gente en AviCore');
        $response->assertDontSee('Negocios que te compran seguido');
        $response->assertDontSee('$ 48.500');
        $response->assertSee('avicore-ui-illustration', false);
        $response->assertDontSee('Ejemplo');
        $response->assertDontSee('Próximamente');
        $response->assertDontSee('Estado inicial');
        $response->assertDontSee('Onboarding');
        $response->assertDontSee('Disponible');
        $response->assertSee('Resumen');
        $response->assertDontSee('Cargar en galpón');
        $response->assertDontSee(route('operario.home'));
        $response->assertDontSee('>Campo<', false);
        $response->assertSee('Abrir menú de cuenta');
        $response->assertSee('avicore-user-menu--sidebar', false);
        $response->assertSee('Navegación');
        $response->assertSee('Cuenta');
        $response->assertSee('avicore-operario-sidebar', false);
        $response->assertSee('avicore-operario-tab-bar', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertDontSee('chevron-down');
        $response->assertDontSee('>3<');
    }

    public function test_active_users_count_excludes_other_companies(): void
    {
        $empresaA = Empresa::factory()->create([
            'nombre' => 'Empresa A',
            'estado' => EmpresaEstado::Activa,
        ]);
        $empresaB = Empresa::factory()->create([
            'nombre' => 'Empresa B',
            'estado' => EmpresaEstado::Activa,
        ]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'documento' => '50111222',
            'password' => 'Secret123!',
            'rol' => UserRole::Encargado,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaA->id,
            'documento' => '50211222',
            'password' => 'Secret123!',
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->count(3)->create([
            'empresa_id' => $empresaB->id,
            'password' => 'Secret123!',
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        $this->actingAs($encargado)
            ->get(route('encargado.home'))
            ->assertOk()
            ->assertSee('Empresa A · Encargado')
            ->assertSee('Huevos juntados')
            ->assertDontSee('>5<');
    }

    public function test_admin_avicore_sees_total_active_users_count(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $admin = User::factory()->adminAvicore()->create([
            'documento' => '900000004',
            'password' => 'Avicore2026!',
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '60111222',
            'password' => 'Secret123!',
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '60211222',
            'password' => 'Secret123!',
            'rol' => UserRole::Encargado,
            'activo' => false,
            'must_change_password' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('avicore.home'))
            ->assertOk()
            ->assertSee('AviCore · Admin AviCore')
            ->assertSee('Huevos juntados')
            ->assertDontSee('>Campo<', false)
            ->assertDontSee(route('operario.home'), false);
    }

    public function test_operario_is_redirected_from_admin_home(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '40111222',
            'password' => 'Secret123!',
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('dueno.home'))
            ->assertRedirect(route('operario.home'));
    }
}
