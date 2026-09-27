<?php

namespace Tests\Feature\Admin;

use App\Actions\Empresa\StartSoporteEmpresaAction;
use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Admin\Empresas\Index as EmpresasIndex;
use App\Livewire\Admin\Resumen\Index as ResumenIndex;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\SoporteSesion;
use App\Models\User;
use App\Policies\LotePolicy;
use App\Services\AdminResumenService;
use App\Services\EmpresaContextService;
use App\Services\EmpresaScopeService;
use App\Services\SoporteEmpresaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSoporteEmpresaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_support_cannot_access_resumen(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('avicore.resumen.index'))
            ->assertForbidden();
    }

    public function test_admin_without_support_cannot_scope_operational_data(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        Granja::factory()->create(['empresa_id' => $empresaA->id, 'activa' => true]);
        Granja::factory()->create(['empresa_id' => $empresaB->id, 'activa' => true]);

        $this->actingAs($admin);

        $scope = app(EmpresaScopeService::class);

        $this->assertNull(app(EmpresaContextService::class)->empresaId());
        $this->assertSame(0, $scope->constrainQuery(Granja::query(), $admin)->count());
    }

    public function test_admin_enters_support_with_motivo_and_sees_banner_and_resumen(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create([
            'nombre' => 'Cliente Soporte',
            'estado' => EmpresaEstado::Activa,
        ]);

        $granja = Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'activa' => true,
        ]);

        Galpon::factory()->forGranja($granja)->create([
            'activo' => true,
            'aves_actuales' => 0,
        ]);

        Livewire::actingAs($admin)
            ->test(EmpresasIndex::class)
            ->call('abrirSoporte', $empresa->id)
            ->set('motivoSoporte', 'Cliente reporta error en resumen operativo.')
            ->call('ingresarSoporte')
            ->assertRedirect(route('avicore.resumen.index'));

        $sesion = SoporteSesion::query()->first();
        $this->assertNotNull($sesion);
        $this->assertSame($empresa->id, $sesion->empresa_id);
        $this->assertSame($admin->id, $sesion->actor_id);
        $this->assertNull($sesion->ended_at);
        $this->assertTrue(app(SoporteEmpresaService::class)->isActive());
        $this->assertSame($empresa->id, app(EmpresaContextService::class)->empresaId());

        $this->actingAs($admin)
            ->get(route('avicore.resumen.index'))
            ->assertOk()
            ->assertSee('Modo soporte')
            ->assertSee('Cliente Soporte')
            ->assertSee('Cliente reporta error en resumen operativo.');

        Livewire::actingAs($admin)
            ->test(ResumenIndex::class)
            ->assertOk();
    }

    public function test_support_requires_minimum_motivo_length(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        Livewire::actingAs($admin)
            ->test(EmpresasIndex::class)
            ->call('abrirSoporte', $empresa->id)
            ->set('motivoSoporte', 'corto')
            ->call('ingresarSoporte')
            ->assertHasErrors(['motivo']);

        $this->assertDatabaseCount('soporte_sesiones', 0);
    }

    public function test_admin_can_exit_support_and_lose_resumen_access(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        Livewire::actingAs($admin)
            ->test(EmpresasIndex::class)
            ->call('abrirSoporte', $empresa->id)
            ->set('motivoSoporte', 'Revisión puntual solicitada por el cliente.')
            ->call('ingresarSoporte');

        $this->actingAs($admin)
            ->post(route('avicore.soporte.finalizar'))
            ->assertRedirect(route('avicore.empresas.index'));

        $this->assertFalse(app(SoporteEmpresaService::class)->isActive());
        $this->assertNotNull(SoporteSesion::query()->first()?->ended_at);

        $this->actingAs($admin)
            ->get(route('avicore.resumen.index'))
            ->assertForbidden();
    }

    public function test_dueno_cannot_enter_support(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->expectException(AuthorizationException::class);

        app(StartSoporteEmpresaAction::class)->execute($dueno, $empresa, [
            'motivo' => 'Intento no autorizado de soporte.',
        ]);
    }

    public function test_exit_support_records_actions_and_clears_session_override(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        app(StartSoporteEmpresaAction::class)->execute($admin, $empresa, [
            'motivo' => 'Revisión de acceso solicitada por el cliente.',
        ]);

        $this->actingAs($admin)
            ->get(route('avicore.resumen.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('avicore.soporte.finalizar'))
            ->assertRedirect(route('avicore.empresas.index'));

        $sesion = SoporteSesion::query()->first();
        $this->assertNotNull($sesion);
        $this->assertSame('manual', $sesion->end_reason);
        $this->assertNull(app(EmpresaContextService::class)->empresaId());

        $tipos = collect($sesion->acciones)->pluck('tipo')->all();
        $this->assertContains('inicio', $tipos);
        $this->assertContains('consulta_resumen', $tipos);
        $this->assertContains('fin', $tipos);
    }

    public function test_exit_support_rejects_invalid_destination_route(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        app(StartSoporteEmpresaAction::class)->execute($admin, $empresa, [
            'motivo' => 'Diagnóstico puntual de resumen operativo.',
        ]);

        $this->actingAs($admin)
            ->post(route('avicore.soporte.finalizar', ['destino' => 'dueno.resumen.index']))
            ->assertRedirect(route('avicore.empresas.index'));
    }

    public function test_switching_support_empresa_does_not_leak_previous_context(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresaA = Empresa::factory()->create([
            'nombre' => 'Empresa Alfa',
            'estado' => EmpresaEstado::Activa,
        ]);

        $empresaB = Empresa::factory()->create([
            'nombre' => 'Empresa Beta',
            'estado' => EmpresaEstado::Activa,
        ]);

        $granjaA = Granja::factory()->create([
            'empresa_id' => $empresaA->id,
            'nombre' => 'Granja Solo Alfa',
            'activa' => true,
        ]);

        $granjaB = Granja::factory()->create([
            'empresa_id' => $empresaB->id,
            'nombre' => 'Granja Solo Beta',
            'activa' => true,
        ]);

        Galpon::factory()->forGranja($granjaA)->create(['activo' => true]);
        Galpon::factory()->forGranja($granjaB)->create(['activo' => true]);

        $this->actingAs($admin);

        app(StartSoporteEmpresaAction::class)->execute($admin, $empresaA, [
            'motivo' => 'Primera revisión de la empresa Alfa.',
        ]);
        $this->assertSame($empresaA->id, app(EmpresaContextService::class)->empresaId());
        $this->assertSame(1, app(AdminResumenService::class)->granjasParaFiltro($admin)->count());

        app(StartSoporteEmpresaAction::class)->execute($admin, $empresaB, [
            'motivo' => 'Segunda revisión de la empresa Beta.',
        ]);

        $this->assertSame($empresaB->id, app(EmpresaContextService::class)->empresaId());
        $this->assertSame(1, app(AdminResumenService::class)->granjasParaFiltro($admin)->count());
        $this->assertSame('Granja Solo Beta', app(AdminResumenService::class)->granjasParaFiltro($admin)->first()?->nombre);

        $sesiones = SoporteSesion::query()->orderBy('id')->get();
        $this->assertCount(2, $sesiones);
        $this->assertSame('replaced', $sesiones[0]->end_reason);
        $this->assertNull($sesiones[1]->ended_at);
        $this->assertSame($empresaB->id, $sesiones[1]->empresa_id);
    }

    public function test_support_mode_blocks_production_mutations_via_gate(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id, 'activa' => true]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['activo' => true]);
        $lote = Lote::factory()->forGalpon($galpon)->create();

        $this->actingAs($admin);

        app(StartSoporteEmpresaAction::class)->execute($admin, $empresa, [
            'motivo' => 'Diagnóstico de resumen sin cambios productivos.',
        ]);

        $this->assertTrue(app(SoporteEmpresaService::class)->blocksProductionMutations($admin));
        $this->assertFalse(Gate::forUser($admin)->allows('create', Lote::class));
        $this->assertFalse(Gate::forUser($admin)->allows('update', $lote));
        $this->assertFalse(app(LotePolicy::class)->create($admin));
        $this->assertFalse(app(LotePolicy::class)->update($admin, $lote));
    }
}
