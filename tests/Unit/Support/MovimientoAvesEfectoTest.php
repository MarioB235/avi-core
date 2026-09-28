<?php

namespace Tests\Unit\Support;

use App\Enums\MovimientoAvesTipo;
use App\Models\MovimientoAves;
use App\Support\MovimientoAvesEfecto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovimientoAvesEfectoTest extends TestCase
{
    use RefreshDatabase;

    public function test_deltas_por_tipo(): void
    {
        $traslado = MovimientoAves::factory()->make([
            'tipo' => MovimientoAvesTipo::Traslado,
            'galpon_origen_id' => 10,
            'galpon_destino_id' => 20,
            'cantidad' => 75,
        ]);

        $this->assertSame([
            10 => -75,
            20 => 75,
        ], MovimientoAvesEfecto::deltasPorGalpon($traslado));
    }
}
