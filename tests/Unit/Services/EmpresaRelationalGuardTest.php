<?php

namespace Tests\Unit\Services;

use App\Enums\LoteEstado;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EmpresaRelationalGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_assert_granja_of_empresa_passes_for_matching_tenant(): void
    {
        $empresa = Empresa::factory()->create();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);

        app(EmpresaRelationalGuard::class)->assertGranjaOfEmpresa($granja, $empresa->id);

        $this->assertTrue(true);
    }

    public function test_assert_granja_of_empresa_rejects_foreign_granja(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $granjaAjena = Granja::factory()->create(['empresa_id' => $empresaB->id]);

        $this->expectException(ValidationException::class);

        app(EmpresaRelationalGuard::class)->assertGranjaOfEmpresa($granjaAjena, $empresaA->id);
    }

    public function test_assert_lote_belongs_to_galpon_rejects_mismatched_parent(): void
    {
        $empresa = Empresa::factory()->create();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galponA = Galpon::factory()->forGranja($granja)->create();
        $galponB = Galpon::factory()->forGranja($granja)->create();
        $lote = Lote::factory()->forGalpon($galponB)->create([
            'estado' => LoteEstado::EnProduccion,
        ]);

        $this->expectException(ValidationException::class);

        app(EmpresaRelationalGuard::class)->assertLoteBelongsToGalpon($lote, $galponA);
    }

    public function test_assert_galpon_of_actor_rejects_foreign_galpon(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $actor = User::factory()->create(['empresa_id' => $empresaA->id]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $empresaB->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create();

        $this->expectException(ValidationException::class);

        app(EmpresaRelationalGuard::class)->assertGalponOfActor($actor, $galponAjeno);
    }
}
