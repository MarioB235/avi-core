<?php

namespace Tests\Unit\Support;

use App\Support\GranjaValidacion;
use Tests\TestCase;

class GranjaValidacionTest extends TestCase
{
    public function test_normalize_trims_and_nullifies_optional_fields(): void
    {
        $normalized = GranjaValidacion::normalize([
            'nombre' => '  Granja Norte  ',
            'codigo' => '  ',
            'dicose' => ' 020 123 4567 ',
            'ubicacion' => ' Canelones ',
        ]);

        $this->assertSame('Granja Norte', $normalized['nombre']);
        $this->assertNull($normalized['codigo']);
        $this->assertSame('0201234567', $normalized['dicose']);
        $this->assertSame('Canelones', $normalized['ubicacion']);
        $this->assertTrue($normalized['activa']);
    }

    public function test_rules_reject_invalid_dicose_format(): void
    {
        $validator = validator(
            GranjaValidacion::normalize([
                'nombre' => 'Granja Test',
                'dicose' => 'ABC',
            ]),
            GranjaValidacion::rules(1),
            GranjaValidacion::messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dicose', $validator->errors()->toArray());
    }
}
