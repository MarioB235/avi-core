<?php

namespace Tests\Unit\Support;

use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\LoteEstado;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Support\GalponValidacion;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GalponValidacionTest extends TestCase
{
    public function test_normalize_trims_and_nullifies_optional_fields(): void
    {
        $normalized = GalponValidacion::normalize([
            'granja_id' => 1,
            'nombre' => '  Galpón Norte  ',
            'codigo' => '  ',
            'capacidad' => ' 1200 ',
            'estado' => GalponEstado::Activo->value,
            'observacion' => '  Nota  ',
        ]);

        $this->assertSame('Galpón Norte', $normalized['nombre']);
        $this->assertNull($normalized['codigo']);
        $this->assertSame(1200, $normalized['capacidad']);
        $this->assertSame('Nota', $normalized['observacion']);
        $this->assertTrue($normalized['activo']);
        $this->assertSame(GalponEstado::Activo, $normalized['estado']);
    }

    public function test_normalize_sets_activo_false_when_estado_blocks_carga(): void
    {
        $normalized = GalponValidacion::normalize([
            'granja_id' => 1,
            'nombre' => 'Galpón pausado',
            'estado' => GalponEstado::EnMantenimiento->value,
        ]);

        $this->assertFalse($normalized['activo']);
        $this->assertSame(GalponEstado::EnMantenimiento, $normalized['estado']);
    }

    public function test_assert_disponible_para_carga_rejects_inactive_granja(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'activa' => false,
        ]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'estado' => GalponEstado::Activo,
            'activo' => true,
        ]);

        $this->expectException(ValidationException::class);

        GalponValidacion::assertDisponibleParaCarga($galpon);
    }

    public function test_assert_lote_activo_para_carga_productiva_requires_active_lote(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 5000]);

        $this->expectException(ValidationException::class);

        GalponValidacion::assertLoteActivoParaCargaProductiva($galpon);
    }

    public function test_assert_ciclo_cerrado_rejects_active_lote(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create();

        $this->expectException(ValidationException::class);

        GalponValidacion::assertCicloCerradoParaNuevoLote($galpon);
    }

    public function test_assert_ciclo_cerrado_rejects_live_birds_without_active_lote(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 100]);
        Lote::factory()->forGalpon($galpon)->create(['estado' => LoteEstado::Cerrado]);

        $this->expectException(ValidationException::class);

        GalponValidacion::assertCicloCerradoParaNuevoLote($galpon);
    }

    public function test_assert_galpon_vacio_para_estado_no_operativo_blocks_transition(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create();

        $this->expectException(ValidationException::class);

        GalponValidacion::assertGalponVacioParaEstadoNoOperativo($galpon, GalponEstado::VacioSanitario);
    }

    public function test_rules_reject_short_name(): void
    {
        $validator = validator(
            GalponValidacion::normalize([
                'granja_id' => 1,
                'nombre' => 'A',
                'estado' => GalponEstado::Activo->value,
            ]),
            GalponValidacion::rules(1),
            GalponValidacion::messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nombre', $validator->errors()->toArray());
    }

    public function test_bloquear_par_ordenado_rechaza_mismo_galpon(): void
    {
        $this->expectException(ValidationException::class);

        GalponValidacion::bloquearParOrdenado(10, 10);
    }

    public function test_bloquear_par_ordenado_preserva_orden_de_ids_solicitados(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galponA = Galpon::factory()->forGranja($granja)->create();
        $galponB = Galpon::factory()->forGranja($granja)->create();

        [$primero, $segundo] = GalponValidacion::bloquearParOrdenado($galponB->id, $galponA->id);

        $this->assertSame($galponB->id, $primero->id);
        $this->assertSame($galponA->id, $segundo->id);
    }
}
