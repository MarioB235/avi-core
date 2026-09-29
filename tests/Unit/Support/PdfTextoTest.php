<?php

namespace Tests\Unit\Support;

use App\Support\PdfTexto;
use Tests\TestCase;

class PdfTextoTest extends TestCase
{
    public function test_usuario_elimina_bytes_de_control(): void
    {
        $texto = "Galpon\x00=alert()";

        $this->assertSame('Galpon=alert()', PdfTexto::usuario($texto, 80));
    }

    public function test_usuario_recorta_texto_largo(): void
    {
        $largo = str_repeat('a', 300);

        $recortado = PdfTexto::usuario($largo, 240);

        $this->assertLessThan(300, strlen($recortado));
    }
}
