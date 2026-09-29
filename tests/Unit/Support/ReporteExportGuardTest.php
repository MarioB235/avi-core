<?php

namespace Tests\Unit\Support;

use App\Exceptions\ReporteConsultaNoDisponibleException;
use App\Services\ReporteConsultaService;
use App\Support\ReporteEstadoConsulta;
use App\Support\ReporteExportGuard;
use Tests\TestCase;

class ReporteExportGuardTest extends TestCase
{
    public function test_assert_descargable_lanza_si_no_disponible_rep07(): void
    {
        $this->expectException(ReporteConsultaNoDisponibleException::class);
        $this->expectExceptionMessage('Filtro inválido');

        ReporteExportGuard::assertDescargable([
            'estado_consulta' => ReporteEstadoConsulta::NoDisponible->value,
            'motivo_no_disponible' => 'Filtro inválido',
        ]);
    }

    public function test_assert_descargable_acepta_ok(): void
    {
        ReporteExportGuard::assertDescargable([
            'estado_consulta' => ReporteEstadoConsulta::Ok->value,
        ]);

        $this->assertTrue(true);
    }

    public function test_fila_mensaje_sin_datos_por_reporte_rep07(): void
    {
        $this->assertStringContainsString(
            'ficha del lote',
            ReporteExportGuard::filaMensajeSinDatos([
                'reporte_id' => ReporteConsultaService::REPORTE_ID_HISTORIA_LOTE,
            ]),
        );
        $this->assertStringContainsString(
            'vacunaciones',
            ReporteExportGuard::filaMensajeSinDatos([
                'reporte_id' => ReporteConsultaService::REPORTE_ID_SANIDAD,
            ]),
        );
        $this->assertStringContainsString(
            'movimientos',
            ReporteExportGuard::filaMensajeSinDatos([
                'reporte_id' => ReporteConsultaService::REPORTE_ID_MOVIMIENTOS,
            ]),
        );
        $this->assertSame(
            'Sin registros en el alcance seleccionado.',
            ReporteExportGuard::filaMensajeSinDatos([
                'reporte_id' => ReporteConsultaService::REPORTE_ID_PRODUCCION,
            ]),
        );
    }

    public function test_sin_datos_en_filas_por_estado_o_filas_vacias_rep07(): void
    {
        $this->assertTrue(ReporteExportGuard::sinDatosEnFilas(
            ['estado_consulta' => ReporteEstadoConsulta::SinDatos->value],
            false,
        ));
        $this->assertTrue(ReporteExportGuard::sinDatosEnFilas(
            ['estado_consulta' => ReporteEstadoConsulta::Ok->value],
            true,
        ));
        $this->assertFalse(ReporteExportGuard::sinDatosEnFilas(
            ['estado_consulta' => ReporteEstadoConsulta::Ok->value],
            false,
        ));
    }
}
