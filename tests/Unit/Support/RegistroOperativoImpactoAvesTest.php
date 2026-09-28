<?php

namespace Tests\Unit\Support;

use App\Enums\RegistroOperativoTipo;
use App\Models\RegistroOperativo;
use App\Support\RegistroOperativoImpactoAves;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistroOperativoImpactoAvesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cantidad_a_restaurar_por_tipo(): void
    {
        $muertes = RegistroOperativo::factory()->make([
            'tipo' => RegistroOperativoTipo::Muertes,
            'muertes' => 4,
        ]);
        $combinado = RegistroOperativo::factory()->make([
            'tipo' => RegistroOperativoTipo::Combinado,
            'muertes' => 2,
            'descarte_aves' => 3,
        ]);

        $this->assertSame(4, RegistroOperativoImpactoAves::cantidadARestaurarEnAnulacion($muertes));
        $this->assertSame(5, RegistroOperativoImpactoAves::cantidadARestaurarEnAnulacion($combinado));
        $this->assertTrue(RegistroOperativoImpactoAves::afectaAvesVivasEnAnulacion($combinado));
    }
}
