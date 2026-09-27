<?php

namespace Tests\Unit\Policies;

use App\Actions\Empresa\StartSoporteEmpresaAction;
use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use App\Policies\LotePolicy;
use App\Services\SoporteEmpresaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LotePolicyTest extends TestCase
{
    use RefreshDatabase;

    private LotePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = app(LotePolicy::class);
    }

    public function test_encargado_can_create_lote_without_support_mode(): void
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

    public function test_admin_in_support_mode_cannot_create_or_update_lote(): void
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
            'motivo' => 'Revisión operativa solicitada por el cliente.',
        ]);

        $this->assertTrue(app(SoporteEmpresaService::class)->blocksProductionMutations($admin));
        $this->assertFalse($this->policy->create($admin));
        $this->assertFalse($this->policy->update($admin, $lote));
    }

    public function test_admin_without_support_session_is_not_blocked_by_production_guard(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($admin);

        $this->assertFalse(app(SoporteEmpresaService::class)->blocksProductionMutations($admin));
    }
}
