<?php

namespace Tests\Unit\Support;

use App\Support\ReportesCatalogoV1;
use Tests\TestCase;

class ReportesCatalogoV1Test extends TestCase
{
    public function test_catalogo_v1_tiene_cinco_reportes_operativos_rep01(): void
    {
        $this->assertSame(5, ReportesCatalogoV1::cantidadOperativosV1());

        $ids = ReportesCatalogoV1::idsOperativosV1();
        $this->assertContains('produccion_diaria', $ids);
        $prod = ReportesCatalogoV1::definiciones()['produccion_diaria'];
        $this->assertStringContainsString('ReporteConsultaService', $prod['implementacion']);
        $this->assertContains('movimientos_existencias', $ids);
        $this->assertContains('auditoria_operativa', $ids);
    }

    public function test_cada_reporte_v1_tiene_necesidad_y_destinatario_rep01(): void
    {
        $requeridos = ['titulo', 'necesidad', 'destinatarios', 'filtros', 'contenido', 'fuentes_consulta', 'formatos', 'd05', 'implementacion'];

        foreach (ReportesCatalogoV1::definiciones() as $id => $def) {
            foreach ($requeridos as $campo) {
                $this->assertArrayHasKey($campo, $def, "Reporte {$id} sin {$campo}");
            }

            $this->assertNotSame('', trim($def['necesidad']), "Reporte {$id}: necesidad vacía");
            $this->assertNotEmpty($def['destinatarios'], "Reporte {$id}: sin destinatarios");
            $this->assertContains('pdf', $def['formatos']);
            $this->assertNotSame('', trim($def['d05']), "Reporte {$id}: d05 vacío");
        }
    }

    public function test_exclusiones_normativas_d05_documentadas(): void
    {
        $excl = ReportesCatalogoV1::exclusionesNormativasD05();

        $this->assertArrayHasKey('mgap_anexo2_ponedoras', $excl);
        $this->assertStringContainsString('REP-12', $excl['mgap_anexo2_ponedoras']['estado']);
    }
}
