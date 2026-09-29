<?php

namespace Tests\Unit\Support;

use App\Enums\RegistroOperativoTipo;
use App\Support\CapturaCeroEstado;
use App\Support\CompletitudDiariaD03;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompletitudDiariaD03Test extends TestCase
{
    #[Test]
    public function test_tiene_omision_cuando_falta_algún_tipo_productivo(): void
    {
        $this->assertTrue(CompletitudDiariaD03::tieneOmisionProductiva([]));

        $this->assertTrue(CompletitudDiariaD03::tieneOmisionProductiva([
            'huevos_estado_hoy' => CapturaCeroEstado::REGISTRADO,
            'muertes_estado_hoy' => CapturaCeroEstado::OMISION,
            'descarte_estado_hoy' => CapturaCeroEstado::CERO_CONFIRMADO,
        ]));
    }

    #[Test]
    public function test_sin_omision_cuando_los_tres_tipos_están_resueltos(): void
    {
        $resumen = [
            'huevos_estado_hoy' => CapturaCeroEstado::REGISTRADO,
            'muertes_estado_hoy' => CapturaCeroEstado::CERO_CONFIRMADO,
            'descarte_estado_hoy' => CapturaCeroEstado::REGISTRADO,
        ];

        $this->assertFalse(CompletitudDiariaD03::tieneOmisionProductiva($resumen));
        $this->assertSame([], CompletitudDiariaD03::tiposEnOmision($resumen));
    }

    #[Test]
    public function test_tipos_en_omision_lista_solo_los_pendientes(): void
    {
        $tipos = CompletitudDiariaD03::tiposEnOmision([
            'huevos_estado_hoy' => CapturaCeroEstado::OMISION,
            'muertes_estado_hoy' => CapturaCeroEstado::REGISTRADO,
            'descarte_estado_hoy' => CapturaCeroEstado::OMISION,
        ]);

        $this->assertSame([
            RegistroOperativoTipo::Huevos,
            RegistroOperativoTipo::Descarte,
        ], $tipos);
    }
}
