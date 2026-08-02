<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class RevealComponentTest extends TestCase
{
    public function test_renders_reveal_marker_without_inline_style(): void
    {
        $html = Blade::render('<x-ui.reveal as="section" aria-label="Bloque">Contenido</x-ui.reveal>');

        $this->assertStringContainsString('data-avicore-reveal', $html);
        $this->assertStringContainsString('avicore-reveal', $html);
        $this->assertStringContainsString('<section', $html);
        $this->assertStringContainsString('Contenido', $html);
        $this->assertStringNotContainsString('style=', $html);
        $this->assertStringNotContainsString('data-reveal-delay', $html);
    }

    public function test_renders_delay_via_data_attribute_not_inline_style(): void
    {
        $html = Blade::render('<x-ui.reveal delay="150">Contenido</x-ui.reveal>');

        $this->assertStringContainsString('data-reveal-delay="150"', $html);
        $this->assertStringNotContainsString('style=', $html);
        $this->assertStringNotContainsString('transition-delay', $html);
    }

    public function test_delay_css_contract_uses_data_attribute(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertNotFalse($css);
        $this->assertStringContainsString('[data-reveal-delay]', $css);
        $this->assertStringContainsString('attr(data-reveal-delay', $css);
    }
}
