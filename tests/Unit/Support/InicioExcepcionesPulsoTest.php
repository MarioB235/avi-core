<?php

namespace Tests\Unit\Support;

use App\Support\InicioExcepcionesPulso;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InicioExcepcionesPulsoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/resumen-test', fn () => 'ok')->name('dueno.resumen.index');
    }

    public function test_prioriza_mortalidad_y_enlaza_galpon(): void
    {
        $items = InicioExcepcionesPulso::construir(
            [
                [
                    'galpon_id' => 10,
                    'nombre' => 'Galpón A',
                    'granja' => 'Granja 1',
                    'mortalidad_pct' => 2.5,
                ],
            ],
            [
                [
                    'id' => 20,
                    'nombre' => 'Galpón B',
                    'granja' => 'Granja 1',
                ],
            ],
            'dueno.resumen.index',
        );

        $this->assertCount(2, $items);
        $this->assertSame('mortalidad_referencia', $items[0]['tipo']);
        $this->assertStringContainsString('galpon=10', $items[0]['accion_url']);
        $this->assertSame('captura_pendiente', $items[1]['tipo']);
        $this->assertStringContainsString('galpon=20', $items[1]['accion_url']);
    }
}
