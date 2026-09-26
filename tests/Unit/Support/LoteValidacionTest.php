<?php

namespace Tests\Unit\Support;

use App\Enums\TipoHuevo;
use App\Support\LoteValidacion;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoteValidacionTest extends TestCase
{
    public function test_normalize_codigo_sma_trims_and_nullifies_empty(): void
    {
        $this->assertNull(LoteValidacion::normalizeCodigoSma(null));
        $this->assertNull(LoteValidacion::normalizeCodigoSma('   '));
        $this->assertSame('L-2024-089', LoteValidacion::normalizeCodigoSma(' L-2024-089 '));
    }

    public function test_assert_codigo_sma_rejects_invalid_format(): void
    {
        $this->expectException(ValidationException::class);

        LoteValidacion::assertCodigoSma('SMA con espacios');
    }

    public function test_assert_cantidades_por_tipo_requires_at_least_one_tipo(): void
    {
        $this->expectException(ValidationException::class);

        LoteValidacion::assertCantidadesPorTipo([]);
    }

    public function test_assert_fecha_nacimiento_rejects_future_date(): void
    {
        $this->expectException(ValidationException::class);

        LoteValidacion::assertFechaNacimiento(Carbon::tomorrow());
    }

    public function test_assert_cantidades_por_tipo_rejects_invalid_tipo(): void
    {
        $this->expectException(ValidationException::class);

        LoteValidacion::assertCantidadesPorTipo(['invalido' => 100]);
    }

    public function test_assert_cantidades_por_tipo_accepts_valid_tipos(): void
    {
        LoteValidacion::assertCantidadesPorTipo([
            TipoHuevo::Blanco->value => 1000,
            TipoHuevo::Color->value => 500,
        ]);

        $this->assertTrue(true);
    }
}
