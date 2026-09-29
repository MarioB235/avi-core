<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PulseExceptionsComponentTest extends TestCase
{
    public function test_pulse_exceptions_renders_nothing_when_empty(): void
    {
        $html = Blade::render('<x-ui.pulse-exceptions :excepciones="[]" />');

        $this->assertStringNotContainsString('avicore-pulse-exceptions', $html);
    }

    public function test_pulse_exceptions_renders_region_links_and_alert_class_res09(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.pulse-exceptions :excepciones="[
                [
                    'tipo' => 'mortalidad_referencia',
                    'titulo' => 'Mortalidad elevada',
                    'detalle' => 'Galpón Norte · referencia 7 días',
                    'accion_url' => '/dueno/resumen?galpon=12',
                    'accion_label' => 'Ver galpón en Resumen',
                ],
                [
                    'tipo' => 'captura_pendiente',
                    'titulo' => 'Captura incompleta',
                    'detalle' => 'Falta huevos hoy',
                    'accion_url' => '/dueno/resumen',
                    'accion_label' => 'Ir a Resumen',
                ],
            ]" />
        BLADE);

        $this->assertStringContainsString('role="region"', $html);
        $this->assertStringContainsString('aria-label="Qué revisar primero"', $html);
        $this->assertStringContainsString('Qué revisar primero', $html);
        $this->assertStringContainsString('Mortalidad elevada', $html);
        $this->assertStringContainsString('avicore-pulse-list__item--alert', $html);
        $this->assertStringContainsString('Ver galpón en Resumen →', $html);
        $this->assertStringContainsString('wire:navigate', $html);
        $this->assertStringContainsString('/dueno/resumen?galpon=12', $html);
        $this->assertSame(1, substr_count($html, 'avicore-pulse-list__item--alert'));
    }
}
