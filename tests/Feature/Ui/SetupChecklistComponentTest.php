<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class SetupChecklistComponentTest extends TestCase
{
    public function test_renders_items_with_pending_badge_by_default(): void
    {
        $items = [
            [
                'label' => 'Granjas',
                'description' => 'Registrá las unidades productivas.',
                'icon' => 'building',
            ],
        ];

        $html = Blade::render('<x-ui.setup-checklist :items="$items" />', [
            'items' => $items,
        ]);

        $this->assertStringContainsString('Granjas', $html);
        $this->assertStringContainsString('Registrá las unidades productivas.', $html);
        $this->assertStringContainsString('Pendiente', $html);
        $this->assertStringContainsString('avicore-setup-item', $html);
    }
}
