<?php

namespace Tests\Unit\Support;

use App\Support\AlimentoValidacion;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AlimentoValidacionTest extends TestCase
{
    #[DataProvider('valoresParseables')]
    public function test_parse_kg_acepta_coma_y_miles(string $entrada, float $esperado): void
    {
        $this->assertSame($esperado, AlimentoValidacion::parseKg($entrada));
    }

    public static function valoresParseables(): array
    {
        return [
            ['1250,5', 1250.5],
            ['8.500,50', 8500.5],
            ['8500', 8500.0],
            ['  12,25  ', 12.25],
        ];
    }

    public function test_parse_kg_rechaza_texto_invalido(): void
    {
        $this->assertNull(AlimentoValidacion::parseKg('abc'));
    }

    public function test_assert_rango_rechaza_por_debajo_del_minimo(): void
    {
        $this->expectException(ValidationException::class);

        AlimentoValidacion::assertRango(0);
    }

    public function test_assert_rango_rechaza_por_encima_del_maximo(): void
    {
        $this->expectException(ValidationException::class);

        AlimentoValidacion::assertRango(AlimentoValidacion::MAX_KG + 1);
    }
}
