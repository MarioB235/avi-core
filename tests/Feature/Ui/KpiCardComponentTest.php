<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class KpiCardComponentTest extends TestCase
{
    public function test_renders_label_value_hint_and_icon(): void
    {
        $html = Blade::render(
            '<x-ui.kpi-card label="Producción" value="120" hint="Hoy" icon="trending-up" />'
        );

        $this->assertStringContainsString('avicore-kpi-card', $html);
        $this->assertStringContainsString('avicore-kpi-label', $html);
        $this->assertStringContainsString('avicore-kpi-value', $html);
        $this->assertStringContainsString('Producción', $html);
        $this->assertStringContainsString('120', $html);
        $this->assertStringContainsString('Hoy', $html);
        $this->assertStringContainsString('M16 7h6v6', $html);
    }

    public function test_renders_without_optional_icon_and_hint(): void
    {
        $html = Blade::render('<x-ui.kpi-card label="Alertas" value="0" />');

        $this->assertStringContainsString('Alertas', $html);
        $this->assertStringContainsString('0', $html);
        $this->assertStringNotContainsString('avicore-kpi-card-icon', $html);
    }
}
