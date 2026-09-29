<?php

namespace Tests\Unit\Services;

use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Services\MovimientoAvesVistaPreviaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovimientoAvesVistaPreviaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajuste_muestra_delta_firmado(): void
    {
        $galpon = $this->galponConSaldo(500);

        $preview = app(MovimientoAvesVistaPreviaService::class)->ajusteInventario($galpon, 485);

        $this->assertTrue($preview['valido']);
        $this->assertStringContainsString('-15', implode(' ', $preview['lineas']));
    }

    private function galponConSaldo(int $aves): Galpon
    {
        $empresa = Empresa::factory()->create();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);

        return Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => $aves,
            'activo' => true,
        ]);
    }
}
