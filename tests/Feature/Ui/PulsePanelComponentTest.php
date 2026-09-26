<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PulsePanelComponentTest extends TestCase
{
    public function test_pulse_panel_renders_ok_state_with_compare_line(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.pulse-panel :pulso="[
                'estado' => 'ok',
                'estado_label' => 'Todo en orden',
                'estado_hint' => 'Sin alertas de mortalidad.',
                'huevos_hoy' => 120,
                'huevos_ayer' => 100,
                'delta_label' => '+20 vs ayer',
            ]" />
        BLADE);

        $this->assertStringContainsString('avicore-pulse-status', $html);
        $this->assertStringContainsString('avicore-pulse-status--ok', $html);
        $this->assertStringContainsString('Todo en orden', $html);
        $this->assertStringContainsString('+20 vs ayer', $html);
    }

    public function test_pulse_panel_renders_revision_state_without_compare_when_no_huevos(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.pulse-panel :pulso="[
                'estado' => 'revision',
                'estado_label' => 'Revisar galpones',
                'estado_hint' => 'Hay alertas de mortalidad.',
                'huevos_hoy' => 0,
                'huevos_ayer' => 0,
                'delta_label' => 'Sin cargas de huevos hoy ni ayer',
            ]" />
        BLADE);

        $this->assertStringContainsString('avicore-pulse-status--revision', $html);
        $this->assertStringContainsString('Revisar galpones', $html);
        $this->assertStringNotContainsString('Sin cargas de huevos hoy ni ayer', $html);
    }
}
