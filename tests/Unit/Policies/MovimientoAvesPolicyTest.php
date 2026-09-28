<?php

namespace Tests\Unit\Policies;

use App\Actions\Empresa\StartSoporteEmpresaAction;
use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Policies\MovimientoAvesPolicy;
use App\Services\SoporteEmpresaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovimientoAvesPolicyTest extends TestCase
{
    use RefreshDatabase;

    private MovimientoAvesPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = app(MovimientoAvesPolicy::class);
    }

    public function test_encargado_can_create_movimiento_without_support_mode(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);

        $this->actingAs($encargado);

        $this->assertFalse(app(SoporteEmpresaService::class)->blocksProductionMutations($encargado));
        $this->assertTrue($this->policy->create($encargado));
    }

    public function test_operario_cannot_create_movimiento(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
        ]);

        $this->assertFalse($this->policy->create($operario));
    }

    public function test_admin_in_support_mode_cannot_create_movimiento(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $this->actingAs($admin);

        app(StartSoporteEmpresaAction::class)->execute($admin, $empresa, [
            'motivo' => 'Revisión operativa solicitada por el cliente.',
        ]);

        $this->assertTrue(app(SoporteEmpresaService::class)->blocksProductionMutations($admin));
        $this->assertFalse($this->policy->create($admin));
    }

    public function test_view_is_scoped_to_same_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $encargadoA = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Encargado,
        ]);

        $granjaB = Granja::factory()->create(['empresa_id' => $empresaB->id]);
        $galponB = Galpon::factory()->forGranja($granjaB)->create();
        $loteB = Lote::factory()->forGalpon($galponB)->create();
        $registradorB = User::factory()->create([
            'empresa_id' => $empresaB->id,
            'rol' => UserRole::Encargado,
        ]);

        $movimientoAjeno = MovimientoAves::factory()->entrada($galponB, $registradorB, 100, $loteB)->create();

        $this->assertFalse($this->policy->view($encargadoA, $movimientoAjeno));
        $this->assertTrue($this->policy->view($registradorB, $movimientoAjeno));
    }
}
