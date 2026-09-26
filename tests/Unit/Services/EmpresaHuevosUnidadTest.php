<?php

namespace Tests\Unit\Services;

use App\Models\Empresa;
use App\Services\EmpresaHuevosUnidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaHuevosUnidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_desglose_uses_empresa_unit_configuration(): void
    {
        $empresa = Empresa::factory()->create([
            'configuracion' => [
                'unidades' => [
                    'huevos_por_maple' => 25,
                    'maples_por_cajon' => 10,
                ],
            ],
        ]);

        $desglose = EmpresaHuevosUnidad::for($empresa)->desgloseDesdeHuevos(275);

        $this->assertSame([
            'cajas' => 1,
            'maples' => 1,
            'huevos' => 0,
        ], $desglose);
    }
}
