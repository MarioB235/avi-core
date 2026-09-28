<?php

namespace Tests\Feature\Movimientos;

use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesTipo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Support\MovimientoAvesEfecto;
use App\Support\MovimientoAvesValidacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MovimientoAvesModeloTest extends TestCase
{
    use RefreshDatabase;

    public function test_escenario_real_reconstruye_saldo_por_galpon(): void
    {
        [$encargado, $galponA, $galponB, $lote] = $this->contextoMovimientos();

        $movimientos = [
            MovimientoAves::factory()->entrada($galponA, $encargado, 5000, $lote)->create(),
            MovimientoAves::factory()->traslado($galponA, $galponB, $encargado, 800, $lote)->create(),
            MovimientoAves::factory()->ajuste($galponA, $encargado, -15)->create(),
            MovimientoAves::factory()->forEmpresaContext($galponA->empresa, $encargado, $galponA)->create([
                'tipo' => MovimientoAvesTipo::Faena,
                'galpon_destino_id' => null,
                'lote_id' => $lote->id,
                'cantidad' => 120,
                'motivo' => 'Salida a faena',
            ]),
        ];

        foreach ($movimientos as $movimiento) {
            MovimientoAvesValidacion::assertEstructuraMinima($movimiento);
        }

        $saldo = MovimientoAvesEfecto::saldoNetoPorGalpon($movimientos);

        $this->assertSame(5000 - 800 - 15 - 120, $saldo[$galponA->id]);
        $this->assertSame(800, $saldo[$galponB->id]);
    }

    public function test_movimiento_reversado_y_reversion_no_cuentan_en_saldo(): void
    {
        [$encargado, $galponA, $galponB] = $this->contextoMovimientos();

        $traslado = MovimientoAves::factory()->traslado($galponA, $galponB, $encargado, 200)->create();

        $reversion = MovimientoAves::factory()->forEmpresaContext($galponA->empresa, $encargado, $galponA, $galponB)->create([
            'tipo' => MovimientoAvesTipo::Reversion,
            'cantidad' => 200,
            'motivo' => 'Error de carga',
            'reversa_de_id' => $traslado->id,
        ]);

        $traslado->update([
            'estado' => MovimientoAvesEstado::Reversado,
            'reversado_por_id' => $reversion->id,
        ]);

        $saldo = MovimientoAvesEfecto::saldoNetoPorGalpon([$traslado->fresh(), $reversion]);

        $this->assertSame(0, $saldo[$galponA->id] ?? 0);
        $this->assertSame(0, $saldo[$galponB->id] ?? 0);
    }

    public function test_no_permite_borrado_fisico(): void
    {
        [$encargado, $galponA] = $this->contextoMovimientos();

        $movimiento = MovimientoAves::factory()->entrada($galponA, $encargado, 100)->create();

        $this->expectException(ValidationException::class);

        $movimiento->delete();
    }

    public function test_validacion_rechaza_traslado_mismo_galpon(): void
    {
        [$encargado, $galponA] = $this->contextoMovimientos();

        $movimiento = MovimientoAves::factory()->make([
            'empresa_id' => $galponA->empresa_id,
            'tipo' => MovimientoAvesTipo::Traslado,
            'galpon_origen_id' => $galponA->id,
            'galpon_destino_id' => $galponA->id,
            'cantidad' => 10,
            'registrado_por' => $encargado->id,
            'motivo' => 'Inválido',
            'fecha_efectiva' => now(),
        ]);

        $this->expectException(ValidationException::class);

        MovimientoAvesValidacion::assertEstructuraMinima($movimiento);
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon, 3: Lote}
     */
    private function contextoMovimientos(): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galponA = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 5000]);
        $galponB = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0]);
        $lote = Lote::factory()->forGalpon($galponA)->create(['cantidad_inicial' => 5000]);

        return [$encargado, $galponA, $galponB, $lote];
    }
}
