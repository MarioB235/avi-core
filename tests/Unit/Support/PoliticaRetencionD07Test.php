<?php

namespace Tests\Unit\Support;

use App\Enums\RetencionD07Categoria;
use App\Support\PoliticaRetencionD07;
use Tests\TestCase;

class PoliticaRetencionD07Test extends TestCase
{
    public function test_plazos_y_purge_desde_config(): void
    {
        config([
            'avicore.retencion.d07.operativa_meses' => 48,
            'avicore.retencion.d07.purge_habilitado' => false,
            'avicore.retencion.d07.nota' => 'Nota de prueba',
        ]);

        $politica = new PoliticaRetencionD07;

        $this->assertSame(48, $politica->plazoMeses(RetencionD07Categoria::Operativa));
        $this->assertFalse($politica->purgeHabilitado());
        $this->assertSame('Nota de prueba', $politica->notaAcordada());
        $this->assertSame('local', $politica->documentosStorageDisk());
    }

    public function test_fecha_limite_retencion_resta_meses(): void
    {
        config(['avicore.retencion.d07.auditoria_meses' => 12]);

        $politica = new PoliticaRetencionD07;
        $referencia = now()->startOfDay();

        $this->assertTrue(
            $politica->fechaLimiteRetencion(RetencionD07Categoria::Auditoria, $referencia)
                ->equalTo($referencia->copy()->subMonths(12)),
        );
    }
}
