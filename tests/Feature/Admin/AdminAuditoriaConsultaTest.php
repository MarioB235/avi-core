<?php

namespace Tests\Feature\Admin;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Actions\User\CreateUserAction;
use App\Enums\AuditoriaCategoria;
use App\Enums\UserRole;
use App\Livewire\Admin\Auditoria\Index as AuditoriaIndex;
use App\Models\Auditoria;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuditoriaConsultaTest extends TestCase
{
    use RefreshDatabase;

    public function test_encargado_ve_eventos_de_su_empresa_y_abre_detalle(): void
    {
        [$encargado, $auditoria] = $this->encargadoConAuditoria();

        $this->assertTrue(Gate::forUser($encargado)->allows('admin.viewAuditoria'));

        Livewire::actingAs($encargado)
            ->test(AuditoriaIndex::class)
            ->assertSee('Usuario creado', false)
            ->call('abrirDetalle', $auditoria->id)
            ->assertSet('dialogDetalleAbierto', true)
            ->assertSee('Actor', false)
            ->assertSee('Solo lectura', false)
            ->assertDontSee('Eliminar', false)
            ->assertDontSee('Editar', false);
    }

    public function test_operario_no_accede_a_consulta_auditoria(): void
    {
        $empresa = Empresa::factory()->create();
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->assertFalse(Gate::forUser($operario)->allows('admin.viewAuditoria'));

        Livewire::actingAs($operario)
            ->test(AuditoriaIndex::class)
            ->assertForbidden();
    }

    public function test_empresa_aislada_no_ve_eventos_de_otra(): void
    {
        $empresaA = Empresa::factory()->create(['nombre' => 'Empresa A']);
        $empresaB = Empresa::factory()->create(['nombre' => 'Empresa B']);

        $encargadoA = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $encargadoB = User::factory()->create([
            'empresa_id' => $empresaB->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        app(RegistrarAuditoriaAction::class)->execute(
            $encargadoB,
            AuditoriaCategoria::Operacion,
            'anulado',
            entidadTipo: 'registro_operativo',
            entidadId: 99,
            empresaId: $empresaB->id,
            motivo: 'Evento empresa B',
        );

        Livewire::actingAs($encargadoA)
            ->test(AuditoriaIndex::class)
            ->assertDontSee('Evento empresa B', false)
            ->assertSee('Sin eventos', false);
    }

    public function test_filtros_por_categoria_actor_y_accion(): void
    {
        [$encargado, $auditoriaUsuario] = $this->encargadoConAuditoria();

        app(RegistrarAuditoriaAction::class)->execute(
            $encargado,
            AuditoriaCategoria::Lote,
            'estado_cambiado',
            entidadTipo: 'lote',
            entidadId: 1,
            motivo: 'Cierre de lote',
        );

        Livewire::actingAs($encargado)
            ->test(AuditoriaIndex::class)
            ->set('filtroCategoria', AuditoriaCategoria::Usuario->value)
            ->assertSee('Usuario creado', false)
            ->assertDontSee('Estado cambiado', false)
            ->set('filtroCategoria', '')
            ->set('filtroActorId', (string) $encargado->id)
            ->assertSee('Estado cambiado', false)
            ->assertDontSee('Usuario creado', false)
            ->set('filtroActorId', '')
            ->set('filtroAccion', 'estado_cambiado')
            ->assertSee('Estado cambiado', false)
            ->assertDontSee('Usuario creado', false);
    }

    public function test_encargado_sin_eventos_ve_estado_vacio(): void
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        Livewire::actingAs($encargado)
            ->test(AuditoriaIndex::class)
            ->assertSee('Sin eventos', false)
            ->assertSee('Cuando ocurran acciones críticas en tu empresa, las verás acá.', false);
    }

    public function test_fecha_maxima_usa_dia_operativo_de_la_empresa(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 03:30:00', 'UTC'));

        $empresa = Empresa::factory()->create([
            'configuracion' => [
                'zona_horaria' => 'America/Montevideo',
                'huevos_por_maple' => 30,
                'maples_por_cajon' => 12,
            ],
        ]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        Livewire::actingAs($encargado)
            ->test(AuditoriaIndex::class)
            ->set('fechaDesde', '2026-09-29')
            ->assertHasErrors(['fechaDesde']);

        Carbon::setTestNow();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @return array{0: User, 1: Auditoria}
     */
    private function encargadoConAuditoria(): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        app(CreateUserAction::class)->execute($administrativo, [
            'name' => 'Operario Demo',
            'documento' => '11223344',
            'rol' => UserRole::Operario->value,
        ]);

        $auditoria = Auditoria::query()
            ->where('accion', 'creado')
            ->sole();

        return [$encargado, $auditoria];
    }
}
