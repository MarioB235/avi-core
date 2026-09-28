<?php

namespace Tests\Unit\Support;

use App\Enums\MovimientoAvesTipo;
use App\Models\MovimientoAves;
use App\Support\MovimientoAvesLoteSaldo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovimientoAvesLoteSaldoTest extends TestCase
{
    use RefreshDatabase;

    public function test_traslado_atribuye_delta_al_lote_en_origen_y_destino(): void
    {
        $movimiento = MovimientoAves::factory()->make([
            'tipo' => MovimientoAvesTipo::Traslado,
            'lote_id' => 7,
            'galpon_origen_id' => 10,
            'galpon_destino_id' => 20,
            'cantidad' => 50,
        ]);

        $this->assertSame(-50, MovimientoAvesLoteSaldo::deltaLoteEnGalpon($movimiento, 7, 10));
        $this->assertSame(50, MovimientoAvesLoteSaldo::deltaLoteEnGalpon($movimiento, 7, 20));
    }
}
