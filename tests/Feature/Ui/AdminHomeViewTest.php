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

        $dueno = User::factory()->create([
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

        $response = $this->actingAs($dueno)->get(route('admin.home'));

        $response->assertOk();
        $response->assertSee('Inicio');
        $response->assertSee('Granja Santa Elena · Encargado');
        $response->assertSee('María González');
        $response->assertSee('¡Bienvenido de nuevo!');
        $response->assertSee('Aquí tienes un resumen general de la operación.');
        $response->assertSee('avicore-admin-header--toolbar');
        $response->assertSee('avicore-home-hero');
        $response->assertSee('avicore-home-hero__card');
        $response->assertSee('avicore-home-hero__figure');
        $response->assertSee('avicore-admin-main__content');
        $response->assertDontSee('avicore-home-hero__stack');
        $response->assertDontSee('avicore-admin-main__scroll');
        $response->assertDontSee('avicore-admin-header--masthead');
        $response->assertSee('images/brand/admin-home-hero.jpg');
        $response->assertSee('Producción de hoy');
        $response->assertSee('Sin datos');
        $response->assertSee('Galpones activos');
        $response->assertSee('Aún no configurado');
        $response->assertSee('Usuarios activos');
        $response->assertSee('2');
        $response->assertSee('Estado inicial');
        $response->assertSee('Granjas');
        $response->assertSee('Galpones');
        $response->assertSee('Usuarios');
        $response->assertSee('Pendiente');
        $response->assertSee('Configurar estructura');
        $response->assertSee('Actividad reciente');
        $response->assertSee('Aún no hay actividad registrada');
        $response->assertSee('Notificaciones');
        $response->assertSee('Próximamente');
        $response->assertSee('Navegación');
        $response->assertSee('Cuenta');
        $response->assertSee('avicore-admin-sidebar');
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
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee('Empresa A · Encargado')
            ->assertSee('Usuarios activos')
            ->assertSee('2')
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
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee('AviCore · Admin AviCore')
            ->assertSee('Usuarios activos')
            ->assertSee('2');
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
            ->get(route('admin.home'))
            ->assertRedirect(route('operario.home'));
    }
}
