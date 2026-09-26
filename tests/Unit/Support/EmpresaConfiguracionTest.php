<?php

namespace Tests\Unit\Support;

use App\Models\Empresa;
use App\Support\EmpresaConfiguracion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaConfiguracionTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_defaults_when_configuracion_is_empty(): void
    {
        $empresa = Empresa::factory()->create(['configuracion' => null]);

        $config = EmpresaConfiguracion::fromEmpresa($empresa);

        $this->assertSame(EmpresaConfiguracion::DEFAULT_ZONA_HORARIA, $config->zonaHoraria);
        $this->assertSame(30, $config->huevosPorMaple);
        $this->assertSame(12, $config->maplesPorCajon);
        $this->assertSame(360, $config->huevosPorCaja());
    }

    public function test_reads_custom_configuracion_values(): void
    {
        $empresa = Empresa::factory()->create([
            'configuracion' => [
                'zona_horaria' => 'America/Argentina/Buenos_Aires',
                'unidades' => [
                    'huevos_por_maple' => 25,
                    'maples_por_cajon' => 10,
                ],
            ],
        ]);

        $config = EmpresaConfiguracion::fromEmpresa($empresa);

        $this->assertSame('America/Argentina/Buenos_Aires', $config->zonaHoraria);
        $this->assertSame(25, $config->huevosPorMaple);
        $this->assertSame(10, $config->maplesPorCajon);
    }
}
