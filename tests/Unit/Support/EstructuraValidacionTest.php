<?php

namespace Tests\Unit\Support;

use App\Enums\EmpresaEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Support\EstructuraValidacion;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EstructuraValidacionTest extends TestCase
{
    public function test_assert_reasignacion_granja_blocks_galpon_with_lotes(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granjaA)->conLoteActivo()->create();

        $this->expectException(ValidationException::class);

        EstructuraValidacion::assertReasignacionGranjaGalponSegura($galpon, $granjaB->id);
    }

    public function test_assert_reasignacion_granja_allows_galpon_without_history(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granjaA)->create(['aves_actuales' => 0]);

        EstructuraValidacion::assertReasignacionGranjaGalponSegura($galpon, $granjaB->id);

        $this->assertTrue(true);
    }

    public function test_assert_reasignacion_granja_blocks_galpon_with_registros(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granjaA)->create(['aves_actuales' => 0]);
        $operario = User::factory()->create(['empresa_id' => $empresa->id]);

        RegistroOperativo::query()->create([
            'empresa_id' => $empresa->id,
            'galpon_id' => $galpon->id,
            'user_id' => $operario->id,
            'tipo' => RegistroOperativoTipo::Alimento,
            'alimento_kg' => 100,
            'estado' => RegistroOperativoEstado::Activo,
        ]);

        $this->expectException(ValidationException::class);

        EstructuraValidacion::assertReasignacionGranjaGalponSegura($galpon, $granjaB->id);
    }

    public function test_assert_sin_cambio_empresa_rejects_new_empresa(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otra = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);

        $this->expectException(ValidationException::class);

        EstructuraValidacion::assertSinCambioEmpresa($granja, $otra->id);
    }

    public function test_structure_models_prevent_hard_delete(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0]);
        $lote = Lote::factory()->forGalpon($galpon)->create();

        $this->expectException(ValidationException::class);
        $lote->delete();
    }
}
