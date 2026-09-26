<?php

namespace Tests\Unit\Support;

use App\Support\HuevosUnidad;
use PHPUnit\Framework\TestCase;

class HuevosUnidadTest extends TestCase
{
    public function test_maples_from_huevos(): void
    {
        $this->assertSame(0, HuevosUnidad::maplesDesdeHuevos(0));
        $this->assertSame(40, HuevosUnidad::maplesDesdeHuevos(1200));
        $this->assertSame(40, HuevosUnidad::maplesDesdeHuevos(1219));
    }

    public function test_desglose_cajas_maples_huevos(): void
    {
        $this->assertSame(
            ['cajas' => 3, 'maples' => 4, 'huevos' => 0],
            HuevosUnidad::desgloseDesdeHuevos(1200),
        );

        $this->assertSame(
            ['cajas' => 12, 'maples' => 0, 'huevos' => 0],
            HuevosUnidad::desgloseDesdeHuevos(4320),
        );

        $this->assertSame(
            ['cajas' => 0, 'maples' => 2, 'huevos' => 5],
            HuevosUnidad::desgloseDesdeHuevos(65),
        );
    }

    public function test_etiqueta_compacta(): void
    {
        $this->assertSame('0 huevos', HuevosUnidad::etiquetaCompacta(0));
        $this->assertSame(
            '1.200 huevos — 3 cajas · 4 maples',
            HuevosUnidad::etiquetaCompacta(1200),
        );
    }

    public function test_etiqueta_solo_cajas_maples(): void
    {
        $this->assertSame('3 cajas + 4 maples', HuevosUnidad::etiquetaSoloCajasMaples(1200));
        $this->assertSame('12 cajas', HuevosUnidad::etiquetaSoloCajasMaples(4320));
        $this->assertSame('2 maples + 5 huevos sueltos', HuevosUnidad::etiquetaSoloCajasMaples(65));
        $this->assertSame('3 cajas + 4 maples + 15 huevos sueltos', HuevosUnidad::etiquetaSoloCajasMaples(1215));
    }
}
