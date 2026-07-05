<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class SelectComponentTest extends TestCase
{
    public function test_select_renders_listbox_trigger_and_options(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.select
                label="Vacuna"
                name="vacuna"
                placeholder="Elegí una vacuna"
                :options="['newcastle' => 'Newcastle (La Sota)', 'gumboro' => 'Gumboro (IBD)']"
            />
        BLADE);

        $this->assertStringContainsString('Vacuna', $html);
        $this->assertStringContainsString('avicore-select-trigger', $html);
        $this->assertStringContainsString('avicore-select-panel', $html);
        $this->assertStringContainsString('avicore-select-option--active', $html);
        $this->assertStringContainsString('role="listbox"', $html);
        $this->assertStringContainsString('Elegí una vacuna', $html);
        $this->assertStringContainsString('Newcastle (La Sota)', $html);
        $this->assertStringContainsString('m6 9 6 6 6-6', $html);
    }

    public function test_select_includes_collision_positioning_hooks(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.select
                label="Vacuna"
                name="vacuna"
                placeholder="Elegí una vacuna"
                :options="['newcastle' => 'Newcastle (La Sota)']"
            />
        BLADE);

        $this->assertStringContainsString('syncPanelPosition', $html);
        $this->assertStringContainsString('avicore-select-panel--below', $html);
        $this->assertStringContainsString('avicore-select-panel--above', $html);
        $this->assertStringContainsString('listMaxHeight', $html);
    }

    public function test_select_renders_error_state(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.select
                label="Lote"
                name="loteId"
                placeholder="Elegí un lote"
                :options="['1' => 'L-1001 · 5.000 aves']"
                error="El lote es obligatorio."
            />
        BLADE);

        $this->assertStringContainsString('L-1001 · 5.000 aves', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('El lote es obligatorio.', $html);
        $this->assertStringContainsString('border-avicore-danger', $html);
    }

    public function test_select_renders_hint_when_no_error(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.select
                label="Lote"
                name="loteId"
                hint="Solo lotes activos del galpón."
                placeholder="Elegí un lote"
            />
        BLADE);

        $this->assertStringContainsString('Solo lotes activos del galpón.', $html);
        $this->assertStringNotContainsString('role="alert"', $html);
    }
}
