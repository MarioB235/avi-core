<?php

namespace Tests\Unit\Support;

use App\Support\AlimentoEntregaSemantica;
use App\Support\ResumenMetricasCatalog;
use Tests\TestCase;

class AlimentoEntregaSemanticaTest extends TestCase
{
    public function test_conversion_prohibida_en_v1(): void
    {
        foreach (AlimentoEntregaSemantica::METRICAS_PROHIBIDAS_V1 as $metrica) {
            $this->assertTrue(AlimentoEntregaSemantica::conversionProhibidaEnV1($metrica));
        }

        $this->assertFalse(AlimentoEntregaSemantica::conversionProhibidaEnV1('alimento_kg_hoy'));
    }

    public function test_presentacion_resumen_sin_eficiencia(): void
    {
        $copy = AlimentoEntregaSemantica::presentacionResumen();

        $this->assertStringContainsString('entregado', strtolower($copy['etiqueta_kpi']));
        $this->assertStringContainsString('consumo', strtolower($copy['hint_kpi']));
        $this->assertStringContainsString('conversión', strtolower($copy['disclaimer']));

        $catalogo = ResumenMetricasCatalog::referenciaAlimentoEntregado();
        $this->assertSame($copy, $catalogo);
    }

    public function test_catalogo_alimento_kg_excluye_conversion_res11(): void
    {
        $def = ResumenMetricasCatalog::definiciones()['alimento_kg_hoy'];

        $this->assertStringContainsString('RES-11', $def['exclusiones']);
        $this->assertStringContainsString('entreg', strtolower($def['unidad']));
    }
}
