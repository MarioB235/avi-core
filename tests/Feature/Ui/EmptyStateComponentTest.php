<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class EmptyStateComponentTest extends TestCase
{
    public function test_renders_title_description_and_icon(): void
    {
        $html = Blade::render('<x-ui.empty-state title="Sin registros" description="Todavía no hay datos." icon="clipboard-list" />');

        $this->assertStringContainsString('avicore-empty-state', $html);
        $this->assertStringContainsString('Sin registros', $html);
        $this->assertStringContainsString('Todavía no hay datos.', $html);
        $this->assertStringContainsString('M12 11h4', $html);
    }
}
