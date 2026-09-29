<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\AvicoreAuthSeeder;
use Database\Seeders\AvicoreDuenoDemoSeeder;
use Database\Seeders\AvicoreEstructuraAvicolaSeeder;
use Database\Seeders\AvicoreOperarioDemoSeeder;
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
        $response->assertSee('Granja Santa Elena · Encargado.');
        $response->assertSee('Tu empresa');
        $response->assertSee('Sin estructura cargada');
        $response->assertDontSee('Tu empresa hoy');
        $response->assertDontSee('Tu equipo');
        $response->assertDontSee('Huevos juntados');
        $response->assertDontSee('Stock y demanda');
        $response->assertDontSee('avicore-pulse-status', false);
        $response->assertDontSee('Huevos de los últimos 7 días');
        $response->assertDontSee('avicore-line-chart', false);
        $response->assertDontSee('Clientes y entregas');
        $response->assertDontSee('Tu gente en AviCore');
        $response->assertDontSee('Negocios que te compran seguido');
        $response->assertDontSee('$ 48.500');
        $response->assertDontSee('avicore-ui-illustration', false);
        $response->assertDontSee('Ejemplo');
        $response->assertDontSee('Próximamente');
        $response->assertSee('Primeros pasos');
        $response->assertSee('Granja');
        $response->assertSee('Pendiente');
        $response->assertSee('avicore-setup-item', false);
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
            ->assertSee('Sin estructura cargada')
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
            ->assertSee('Sin indicadores operativos')
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

    public function test_dueno_with_demo_seed_sees_pulso_without_stock_preview(): void
    {
        $this->seed([
            AvicoreAuthSeeder::class,
            AvicoreEstructuraAvicolaSeeder::class,
            AvicoreOperarioDemoSeeder::class,
            AvicoreDuenoDemoSeeder::class,
        ]);

        $dueno = User::query()->where('documento', '000000000')->firstOrFail();
        $dueno->update(['rol' => UserRole::Dueno]);

        $this->actingAs($dueno)
            ->get(route('dueno.home'))
            ->assertOk()
            ->assertSee('Tu empresa hoy')
            ->assertSee('Huevos juntados hoy')
            ->assertDontSee('Stock y demanda')
            ->assertDontSee('En reserva (cámara)')
            ->assertDontSee('vista previa', false)
            ->assertSee('avicore-pulse-status', false)
            ->assertSee('Ver análisis completo en Resumen');
    }
}
