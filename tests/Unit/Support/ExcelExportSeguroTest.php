<?php

namespace Tests\Unit\Support;

use App\Support\ExcelExportSeguro;
use Tests\TestCase;

class ExcelExportSeguroTest extends TestCase
{
    public function test_prefija_formulas_en_texto(): void
    {
        $this->assertSame("'=1+1", ExcelExportSeguro::texto('=1+1'));
        $this->assertSame("'+SUM(A1)", ExcelExportSeguro::texto('+SUM(A1)'));
        $this->assertSame("'-resta", ExcelExportSeguro::texto('-resta'));
        $this->assertSame("'@SUM(A1)", ExcelExportSeguro::texto('@SUM(A1)'));
        $this->assertSame("'|cmd", ExcelExportSeguro::texto('|cmd'));
        $this->assertSame('Granja Demo', ExcelExportSeguro::texto('Granja Demo'));
        $this->assertSame('', ExcelExportSeguro::texto(null));
    }
}
