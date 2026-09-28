<?php

namespace Tests\Feature\Movimientos;

use App\Enums\MovimientoAvesTipo;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\MovimientoAvesConciliacionService;
use App\Support\MovimientoAvesValidacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MovimientoAvesConciliacionD01Test extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_con_varios_lotes_no_marca_saldo_como_hecho(): void
    {
        [$galpon, $loteA, $loteB] = $this->galponConDosLotes();

        $snapshot = app(MovimientoAvesConciliacionService::class)->snapshot($galpon);

        $this->assertTrue($snapshot['multiples_lotes']);
        $this->assertCount(2, $snapshot['lotes']);

        foreach ($snapshot['lotes'] as $fila) {
            $this->assertFalse($fila['saldo_es_hecho']);
            $this->assertNull($fila['saldo_atribuible']);
            $this->assertStringContainsString('Imputá muertes', (string) $fila['nota']);
        }
    }

    public function test_snapshot_con_un_solo_lote_usa_saldo_vivo_como_hecho(): void
    {
        [$encargado, $galpon, $lote] = $this->galponConUnLote(avesActuales: 4800);

        $snapshot = app(MovimientoAvesConciliacionService::class)->snapshot($galpon);

        $this->assertFalse($snapshot['multiples_lotes']);
        $this->assertTrue($snapshot['lotes'][0]['saldo_es_hecho']);
        $this->assertSame(4800, $snapshot['lotes'][0]['saldo_atribuible']);
    }

    public function test_traslado_con_varios_lotes_exige_imputacion_de_muertes(): void
    {
        [$encargado, $galpon, $galponB, $loteA] = $this->galponConDosLotesConMuertes(muertes: 120);

        $movimiento = MovimientoAves::factory()->traslado($galpon, $galponB, $encargado, 500, $loteA)->make([
            'lote_id' => $loteA->id,
        ]);

        $this->expectException(ValidationException::class);

        MovimientoAvesValidacion::assertEstructuraMinima($movimiento);
    }

    public function test_traslado_conciliado_no_supera_saldo_declarado(): void
    {
        [$encargado, $galpon, $galponB, $loteA] = $this->galponConDosLotesConMuertes(muertes: 120);

        $movimiento = MovimientoAves::factory()->traslado($galpon, $galponB, $encargado, 500, $loteA)->make([
            'lote_id' => $loteA->id,
            'metadata' => [
                'muertes_imputadas_lote' => 120,
                'descarte_imputado_lote' => 0,
            ],
        ]);

        MovimientoAvesValidacion::assertEstructuraMinima($movimiento);

        $this->assertTrue(true);
    }

    public function test_traslado_conciliado_rechaza_cantidad_mayor_al_saldo_declarado(): void
    {
        [$encargado, $galpon, $galponB, $loteA] = $this->galponConDosLotesConMuertes(muertes: 120);

        $movimiento = MovimientoAves::factory()->traslado($galpon, $galponB, $encargado, 2900, $loteA)->make([
            'lote_id' => $loteA->id,
            'metadata' => [
                'muertes_imputadas_lote' => 120,
                'descarte_imputado_lote' => 0,
            ],
        ]);

        $this->expectException(ValidationException::class);

        MovimientoAvesValidacion::assertEstructuraMinima($movimiento);
    }

    public function test_cierre_con_un_solo_lote_no_exige_metadata(): void
    {
        [$encargado, $galpon, $lote] = $this->galponConUnLote(avesActuales: 1000);

        $movimiento = MovimientoAves::factory()->forEmpresaContext($galpon->empresa, $encargado, $galpon)->make([
            'tipo' => MovimientoAvesTipo::CierreLote,
            'galpon_destino_id' => null,
            'lote_id' => $lote->id,
            'cantidad' => 200,
            'motivo' => 'Cierre parcial',
            'fecha_efectiva' => now(),
        ]);

        MovimientoAvesValidacion::assertEstructuraMinima($movimiento);

        $this->assertTrue(true);
    }

    /**
     * @return array{0: Galpon, 1: Lote, 2: Lote}
     */
    private function galponConDosLotes(): array
    {
        $empresa = Empresa::factory()->create();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 4500]);
        $loteA = Lote::factory()->forGalpon($galpon)->create(['cantidad_inicial' => 3000]);
        $loteB = Lote::factory()->forGalpon($galpon)->create(['cantidad_inicial' => 2000]);

        return [$galpon, $loteA, $loteB];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon, 3: Lote}
     */
    private function galponConDosLotesConMuertes(int $muertes): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 4880]);
        $galponB = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0]);
        $loteA = Lote::factory()->forGalpon($galpon)->create(['cantidad_inicial' => 3000]);
        Lote::factory()->forGalpon($galpon)->create(['cantidad_inicial' => 2000]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $encargado)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'muertes' => $muertes,
            ]);

        return [$encargado, $galpon, $galponB, $loteA];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function galponConUnLote(int $avesActuales): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => $avesActuales]);
        $lote = Lote::factory()->forGalpon($galpon)->create(['cantidad_inicial' => $avesActuales]);

        return [$encargado, $galpon, $lote];
    }
}
