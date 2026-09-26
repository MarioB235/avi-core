<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ClientMapComponentTest extends TestCase
{
    public function test_client_map_renders_shell_detail_card_and_map_canvas(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.client-map :clients="[
                [
                    'id' => 'demo-1',
                    'name' => 'Almacén Demo',
                    'zona' => 'Pando',
                    'lat' => -34.717,
                    'lng' => -55.958,
                    'ultima_compra_fecha_label' => '21 ago 2026',
                    'ultima_compra_cantidad_label' => '600 huevos',
                    'ultima_compra_resumen' => '21 ago 2026 · 600 huevos',
                ],
            ]" />
        BLADE);

        $this->assertStringContainsString('data-avicore-client-map', $html);
        $this->assertStringContainsString('data-avicore-client-map-canvas', $html);
        $this->assertStringContainsString('avicore-client-map__shell', $html);
        $this->assertStringContainsString('avicore-client-map-detail', $html);
        $this->assertStringContainsString('Cliente seleccionado', $html);
        $this->assertStringContainsString('Tocá un pin en el mapa', $html);
        $this->assertStringNotContainsString('avicore-comercial-client-list', $html);
    }

    public function test_client_map_renders_empty_state_without_clients(): void
    {
        $html = Blade::render('<x-ui.client-map :clients="[]" />');

        $this->assertStringContainsString('Sin clientes para mostrar en el mapa.', $html);
        $this->assertStringNotContainsString('data-avicore-client-map-canvas', $html);
    }
}
