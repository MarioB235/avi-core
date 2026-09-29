<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class LineChartComponentTest extends TestCase
{
    public function test_line_chart_renders_svg_and_labels(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.line-chart :points="[
                ['label' => '1/8', 'value' => 100],
                ['label' => '2/8', 'value' => 250],
            ]" />
        BLADE);

        $this->assertStringContainsString('avicore-line-chart__svg', $html);
        $this->assertStringContainsString('avicore-line-chart__line', $html);
        $this->assertStringContainsString('1/8', $html);
        $this->assertStringContainsString('2/8', $html);
    }

    public function test_line_chart_renders_empty_state_without_points(): void
    {
        $html = Blade::render('<x-ui.line-chart :points="[]" empty-label="Sin producción" />');

        $this->assertStringContainsString('Sin producción', $html);
        $this->assertStringNotContainsString('avicore-line-chart__svg', $html);
    }

    public function test_line_chart_skips_null_values_without_plotting_zero(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.line-chart :points="[
                ['label' => '1/8', 'value' => null, 'display' => '—'],
                ['label' => '2/8', 'value' => 120, 'display' => '120'],
            ]" />
        BLADE);

        $this->assertStringContainsString('avicore-line-chart__svg', $html);
        $this->assertStringContainsString('—', $html);
        $this->assertStringContainsString('120', $html);
    }
}
