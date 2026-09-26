<?php

namespace Tests\Feature\Admin;

use App\Actions\Empresa\CreateEmpresaAction;
use App\Actions\Empresa\UpdateEmpresaConfiguracionAction;
use App\Actions\Empresa\UpdateEmpresaEstadoAction;
use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Admin\Empresas\Index as EmpresasIndex;
use App\Livewire\Auth\Login;
use App\Models\Empresa;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AdminEmpresasTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_avicore_can_list_and_create_empresa_with_dueno_inicial(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('avicore.empresas.index'))
            ->assertOk()
            ->assertSee('Nueva empresa');

        Livewire::actingAs($admin)
            ->test(EmpresasIndex::class)
            ->call('abrirCrear')
            ->set('nombre', 'Avícola Real')
            ->set('codigo', 'real01')
            ->set('estado', EmpresaEstado::Activa->value)
            ->set('admin_name', 'María Dueña')
            ->set('admin_documento', '12345678')
            ->set('admin_email', 'duena@real.test')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('dialogPasswordAbierto', true)
            ->assertDispatched('snackbar-show');

        $empresa = Empresa::query()->where('codigo', 'REAL01')->first();
        $this->assertNotNull($empresa);
        $this->assertSame('Avícola Real', $empresa->nombre);
        $this->assertSame(EmpresaEstado::Activa, $empresa->estado);

        $dueno = User::query()
            ->where('empresa_id', $empresa->id)
            ->where('documento', '12345678')
            ->first();

        $this->assertNotNull($dueno);
        $this->assertSame(UserRole::Dueno, $dueno->rol);
        $this->assertTrue($dueno->must_change_password);
        $this->assertTrue($dueno->activo);
        $this->assertSame(0, Granja::query()->where('empresa_id', $empresa->id)->count());
    }

    public function test_dueno_cannot_access_empresas_panel(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.empresas.index'))
            ->assertForbidden();
    }

    public function test_create_empresa_action_rejects_duplicate_codigo(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        Empresa::factory()->create([
            'codigo' => 'EXISTE',
            'estado' => EmpresaEstado::Activa,
        ]);

        $this->expectException(ValidationException::class);

        app(CreateEmpresaAction::class)->execute($admin, [
            'nombre' => 'Otra empresa',
            'codigo' => 'EXISTE',
            'admin_name' => 'Dueño Nuevo',
            'admin_documento' => '87654321',
        ]);
    }

    public function test_non_admin_cannot_create_empresa(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        $this->expectException(AuthorizationException::class);

        app(CreateEmpresaAction::class)->execute($administrativo, [
            'nombre' => 'Empresa Ajena',
            'codigo' => 'AJENA',
            'admin_name' => 'Dueño Ajeno',
            'admin_documento' => '11112222',
        ]);
    }

    public function test_admin_avicore_can_suspend_and_reactivate_empresa_with_motivo(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'name' => 'Admin Plataforma',
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create([
            'codigo' => 'CLIENTE',
            'estado' => EmpresaEstado::Activa,
        ]);

        Livewire::actingAs($admin)
            ->test(EmpresasIndex::class)
            ->call('abrirCambioEstado', $empresa->id)
            ->set('estadoNuevo', EmpresaEstado::Suspendida->value)
            ->set('motivoEstado', 'Falta de pago del servicio')
            ->call('guardarEstado')
            ->assertHasNoErrors()
            ->assertDispatched('snackbar-show');

        $empresa->refresh();
        $this->assertSame(EmpresaEstado::Suspendida, $empresa->estado);

        $historial = $empresa->estadoHistorial();
        $this->assertCount(1, $historial);
        $this->assertSame('activa', $historial[0]['estado_anterior']);
        $this->assertSame('suspendida', $historial[0]['estado_nuevo']);
        $this->assertSame('Falta de pago del servicio', $historial[0]['motivo']);
        $this->assertSame($admin->id, $historial[0]['actor_id']);

        Livewire::actingAs($admin)
            ->test(EmpresasIndex::class)
            ->call('abrirCambioEstado', $empresa->id)
            ->set('estadoNuevo', EmpresaEstado::Activa->value)
            ->set('motivoEstado', 'Regularizó el pago')
            ->call('guardarEstado')
            ->assertHasNoErrors();

        $this->assertSame(EmpresaEstado::Activa, $empresa->fresh()->estado);
        $this->assertCount(2, $empresa->fresh()->estadoHistorial());
    }

    public function test_suspend_empresa_blocks_existing_user_session(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create([
            'codigo' => 'BLOQUEO',
            'estado' => EmpresaEstado::Activa,
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.home'))
            ->assertOk();

        app(UpdateEmpresaEstadoAction::class)->execute($admin, $empresa, [
            'estado' => EmpresaEstado::Suspendida->value,
            'motivo' => 'Revisión de contrato pendiente',
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.home'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertSame(0, Granja::query()->where('empresa_id', $empresa->id)->count());
    }

    public function test_admin_avicore_can_update_empresa_configuration(): void
    {
        Storage::fake('public');

        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create([
            'codigo' => 'CONF01',
            'nombre' => 'Empresa Original',
            'configuracion' => ['estado_historial' => [['motivo' => 'test']]],
        ]);

        Livewire::actingAs($admin)
            ->test(EmpresasIndex::class)
            ->call('abrirConfigurar', $empresa->id)
            ->set('configNombre', 'Empresa Renombrada')
            ->set('configZonaHoraria', 'America/Montevideo')
            ->set('configHuevosPorMaple', '25')
            ->set('configMaplesPorCajon', '10')
            ->set('configLogo', UploadedFile::fake()->create('logo.png', 100, 'image/png'))
            ->call('guardarConfiguracion')
            ->assertHasNoErrors()
            ->assertDispatched('snackbar-show');

        $empresa->refresh();

        $this->assertSame('Empresa Renombrada', $empresa->nombre);
        $this->assertSame('America/Montevideo', $empresa->configuracionOperativa()->zonaHoraria);
        $this->assertSame(25, $empresa->configuracionOperativa()->huevosPorMaple);
        $this->assertSame(10, $empresa->configuracionOperativa()->maplesPorCajon);
        $this->assertNotNull($empresa->logo_path);
        $this->assertStringStartsWith('empresas/logos/conf01/', $empresa->logo_path);
        $this->assertArrayHasKey('estado_historial', $empresa->configuracion);
        Storage::disk('public')->assertExists($empresa->logo_path);
    }

    public function test_update_configuration_rejects_invalid_timezone(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create(['codigo' => 'TZFAIL']);

        $this->expectException(ValidationException::class);

        app(UpdateEmpresaConfiguracionAction::class)->execute($admin, $empresa, [
            'nombre' => $empresa->nombre,
            'zona_horaria' => 'Invalid/Zone',
            'huevos_por_maple' => 30,
            'maples_por_cajon' => 12,
        ]);
    }

    public function test_cannot_change_demo_empresa_estado(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create([
            'codigo' => 'DEMO',
            'estado' => EmpresaEstado::Activa,
        ]);

        $this->expectException(ValidationException::class);

        app(UpdateEmpresaEstadoAction::class)->execute($admin, $empresa, [
            'estado' => EmpresaEstado::Suspendida->value,
            'motivo' => 'Intento sobre demo',
        ]);
    }

    public function test_new_dueno_can_login_and_reach_empty_company_panel(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $result = app(CreateEmpresaAction::class)->execute($admin, [
            'nombre' => 'Empresa Vacía',
            'codigo' => 'VACIA',
            'admin_name' => 'Dueño Vacío',
            'admin_documento' => '55556666',
        ]);

        $dueno = $result['admin'];
        $dueno->update(['must_change_password' => false, 'password' => 'Avicore2026!']);

        Livewire::test(Login::class)
            ->set('documento', '55556666')
            ->set('password', 'Avicore2026!')
            ->call('login')
            ->assertRedirect(route('dueno.home'));

        $this->actingAs($dueno->fresh())
            ->get(route('dueno.home'))
            ->assertOk()
            ->assertSee('Empresa Vacía', false);
    }
}
