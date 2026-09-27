<?php

namespace Tests\Unit\Support;

use App\Support\VacunacionValidacion;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VacunacionValidacionTest extends TestCase
{
    public function test_normalizar_observacion_recorta_y_convierte_vacio_en_null(): void
    {
        $this->assertNull(VacunacionValidacion::normalizarObservacion(null));
        $this->assertNull(VacunacionValidacion::normalizarObservacion(''));
        $this->assertNull(VacunacionValidacion::normalizarObservacion('   '));
        $this->assertSame('Vacuna refuerzo', VacunacionValidacion::normalizarObservacion('  Vacuna refuerzo  '));
    }

    public function test_assert_observacion_acepta_texto_valido(): void
    {
        VacunacionValidacion::assertObservacion('Aplicación sin novedades');

        $this->assertTrue(true);
    }

    public function test_assert_observacion_rechaza_texto_muy_largo(): void
    {
        $this->expectException(ValidationException::class);

        VacunacionValidacion::assertObservacion(str_repeat('a', VacunacionValidacion::OBSERVACION_MAX + 1));
    }
}
