<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class NavLinkComponentTest extends TestCase
{
    public function test_active_link_renders_href_and_aria_current(): void
    {
        $html = Blade::render(
            '<x-ui.nav-link href="https://example.test/admin" :active="true" icon="home">Inicio</x-ui.nav-link>'
        );

        $this->assertStringContainsString('href="https://example.test/admin"', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('avicore-nav-link--active', $html);
        $this->assertStringContainsString('Inicio', $html);
    }

    public function test_disabled_link_renders_span_with_aria_disabled_and_badge(): void
    {
        $html = Blade::render(
            '<x-ui.nav-link disabled icon="chart" badge="Próximamente">Dashboard</x-ui.nav-link>'
        );

        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertStringContainsString('avicore-nav-link--disabled', $html);
        $this->assertStringContainsString('Próximamente', $html);
        $this->assertStringContainsString('Dashboard', $html);
        $this->assertStringNotContainsString('href=', $html);
    }
}
