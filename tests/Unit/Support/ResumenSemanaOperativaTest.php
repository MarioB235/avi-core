<?php

namespace Tests\Unit\Support;

use App\Enums\RegistroOperativoTipo;
use App\Models\RegistroOperativo;
use App\Support\CapturaCeroEstado;
use App\Support\ResumenSemanaOperativa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumenSemanaOperativaTest extends TestCase
{
    use RefreshDatabase;

    public function test_celda_huevos_aptos_omision_sin_registro(): void
    {
        $celda = ResumenSemanaOperativa::celdaHuevosAptos((new RegistroOperativo)->newCollection(), [1]);

        $this->assertNull($celda['value']);
        $this->assertSame(CapturaCeroEstado::OMISION, $celda['estado']);
        $this->assertSame('—', $celda['display']);
    }

    public function test_celda_huevos_aptos_cero_confirmado(): void
    {
        $registro = RegistroOperativo::factory()->make([
            'galpon_id' => 5,
            'tipo' => RegistroOperativoTipo::Huevos,
            'huevos' => 0,
            'cero_confirmado' => true,
        ]);

        $celda = ResumenSemanaOperativa::celdaHuevosAptos((new RegistroOperativo)->newCollection([$registro]), [5]);

        $this->assertSame(0, $celda['value']);
        $this->assertSame(CapturaCeroEstado::CERO_CONFIRMADO, $celda['estado']);
        $this->assertSame('0 (confirmado)', $celda['display']);
    }

    public function test_celda_alimento_sin_registro_es_omision(): void
    {
        $celda = ResumenSemanaOperativa::celdaAlimentoKg((new RegistroOperativo)->newCollection(), [3]);

        $this->assertNull($celda['value']);
        $this->assertSame('—', $celda['display']);
    }
}
