<?php

namespace Tests\Unit\Support;

use App\Services\AdminResumenService;
use App\Support\ResumenMetricasCatalog;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ResumenMetricasCatalogTest extends TestCase
{
    public function test_definiciones_incluyen_campos_obligatorios_res02(): void
    {
        $requeridos = ['pantalla', 'fuente', 'unidad', 'periodo', 'poblacion', 'exclusiones', 'ausencia', 'implementacion'];

        foreach (ResumenMetricasCatalog::definiciones() as $id => $def) {
            foreach ($requeridos as $campo) {
                $this->assertArrayHasKey($campo, $def, "Métrica {$id} sin {$campo}");
                $this->assertNotSame('', trim($def[$campo]), "Métrica {$id}: {$campo} vacío");
            }
        }
    }

    public function test_umbral_mortalidad_coincide_con_servicio(): void
    {
        $this->assertSame(
            ResumenMetricasCatalog::UMBRAL_MORTALIDAD_REFERENCIA_PCT,
            AdminResumenService::MORTALIDAD_REFERENCIA_PCT,
        );
    }

    public function test_referencia_mortalidad_y_umbral_res06(): void
    {
        $ref = ResumenMetricasCatalog::referenciaMortalidad();

        $this->assertSame(1.1, $ref['umbral_pct']);
        $this->assertStringContainsString('no norma', strtolower($ref['etiqueta_umbral']));
        $this->assertStringContainsString('diagnóstico', strtolower($ref['etiqueta_umbral']));
        $this->assertStringContainsString('responsable', strtolower($ref['disclaimer']));

        $this->assertFalse(ResumenMetricasCatalog::superaReferenciaMortalidad(1.1));
        $this->assertTrue(ResumenMetricasCatalog::superaReferenciaMortalidad(1.11));
    }

    #[DataProvider('casosVerificablesProvider')]
    public function test_caso_verificable_apunta_a_test_existente(string $caso, string $testReference): void
    {
        [$class, $method] = explode('::', $testReference);

        $this->assertTrue(class_exists($class), "Clase inexistente para caso {$caso}: {$class}");
        $this->assertTrue(method_exists($class, $method), "Método inexistente para caso {$caso}: {$method}");
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function casosVerificablesProvider(): array
    {
        $rows = [];

        foreach (ResumenMetricasCatalog::casosVerificables() as $caso => $testReference) {
            $rows[$caso] = [$caso, $testReference];
        }

        return $rows;
    }
}
